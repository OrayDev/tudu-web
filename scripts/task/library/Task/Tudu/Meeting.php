<?php
/**
 * Job
 *
 * LICENSE
 *
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Meeting.php 1983 2012-07-11 08:11:37Z cutecube $
 */

/**
 * 计划：每分钟执行一次
 *
 * 过期会议处理，会议提醒
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Tudu_Meeting extends Task_Abstract
{

    /**
     *
     * @var array<Zend_Db_Adapter>()
     */
    protected $_dbs = array();

    /**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     */
    public function startUp()
    {
        foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_dbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
            }
        }
    }

    /**
     * 执行讨论数据处理
     */
    public function run()
    {
        $today = date('Y-m-d');

        foreach ($this->_dbs as $key => $db) {

            $tsId = (int) str_replace('ts', '', $key);

            // 会议提醒
            $sql = 'SELECT T.tudu_id, T.org_id, T.subject, T.from, T.start_time, M.is_allday, M.location FROM td_tudu AS T '
                 . 'LEFT JOIN td_tudu_meeting M ON T.tudu_id = M.tudu_id '
                 . 'WHERE type = \'meeting\' '
                 . 'AND T.is_draft = 0 '
                 . 'AND T.is_done = 0 '
                 . 'AND M.notify_time >= UNIX_TIMESTAMP() - 120 '
                 . 'AND M.notify_time <= UNIX_TIMESTAMP() + 60 '
                 . 'AND is_notified = 0';

            Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $db, true);

            $manager  = Tudu_Tudu_Manager::getInstance();

            $meetings = $db->fetchAll($sql);

            $daoLog   = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

            foreach ($meetings as $item) {

                $tuduId = $item['tudu_id'];

                $users = $db->fetchAll("SELECT accepter_info, role, is_foreign FROM td_tudu_user WHERE tudu_id = '{$tuduId}' AND role = 'to' AND tudu_status < 3");

                $flag = true;

                // 记录提醒人员
                $notifyTo = array();
                $mailTo   = array();
                foreach ($users as $row) {
                    list($email, ) = explode(' ', $row['accepter_info']);

                    if (!$row['is_foreign']) {
                        $notifyTo[] = $email;
                    } else if (Oray_Function::isEmail($email)) {
                        $mailTo[] = $email;
                    }
                }

                // 发送talk提醒
                if (count($notifyTo)) {
                    $this->sendNotify($item, $notifyTo);
                }

                // 发送外部提醒(Email)
                if (count($mailTo)) {
                    $this->sendMail($tsId, $item, $mailTo);
                }

                $db->query("UPDATE td_tudu_meeting SET is_notified = 1 WHERE tudu_id = '{$tuduId}'");

                $this->getLogger()->info("Meeting id:({$tuduId}) notify success");
            }

            // 过期会议自动结束
            $sql = 'SELECT T.tudu_id, T.org_id, T.cycle_id, M.is_allday, T.end_time FROM td_tudu AS T '
                 . 'LEFT JOIN td_tudu_meeting M ON T.tudu_id = M.tudu_id '
                 . 'WHERE type = \'meeting\' '
                 . 'AND is_draft = 0 '
                 . 'AND is_done  = 0 '
                 . 'AND (end_time < UNIX_TIMESTAMP() OR end_time IS NULL)';

            $records = $db->fetchAll($sql);

            foreach ($records as $record) {
                $orgId   = $record['org_id'];
                $tuduId  = $record['tudu_id'];
                $endTime = (int) $record['end_time'];

                if ($record['is_allday'] && date('Y-m-d', $endTime) == $today) {
                    continue ;
                }

                if (!$manager->updateTudu($tuduId, array('isdone' => true))) {
                    $this->getLogger()->info("Meeting id:({$tuduId}) done failure");

                    continue ;
                }

                // 成功记录操作日志
                $daoLog->createLog(array(
                    'orgid' => $orgId,
                    'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                    'targetid' => $tuduId,
                    'uniqueid' => '^system',
                    'action'   => Dao_Td_Log_Log::ACTION_TUDU_DONE,
                    'detail'   => serialize(array('isdone' => true)),
                    'logtime'  => time()
                ));

                $users = $manager->getTuduUsers($tuduId);
                foreach ($users as $user) {
                    // 移出图度箱
                    $ret = $manager->deleteLabel($tuduId, $user['uniqueid'], '^i');
                    $ret = $manager->deleteLabel($tuduId, $user['uniqueid'], '^a');
                }

                // 周期任务
                if (!empty($record['cycle_id'])) {
                    if (!$this->cycle($tsId, $tuduId, $record['cycle_id'])) {
                        $this->getLogger()->warn("Meeting id:({$tuduId}) cycle failure");
                    }
                }

                $this->getLogger()->info("Meeting id:({$tuduId}) done success");
            }

            $this->getLogger()->info("Ts db:({$key}) process complete");
        }
    }

    /**
     * 发送提醒
     *
     * @param array $to
     */
    public function sendNotify($meeting, $to)
    {

        list($from, $sender) = explode(' ', $meeting['from']);
        $location  = $meeting['location'];
        $startTime = $meeting['is_allday']
                   ? date('Y-m-d', (int) $meeting['start_time'])
                   : date('Y-m-d H:i', (int) $meeting['start_time']);

        $content = <<<HTML
<strong>会议提醒</strong>
<a href="http://{$meeting['org_id']}.tudu.com/frame#m=view&tid={$meeting['tudu_id']}&page=1" target="_blank" _tid="{$meeting['tudu_id']}">{$meeting['subject']}</a><br />
发起人：{$sender}<br />
会议时间：{$startTime}<br />
会议地点：{$location}
HTML;

        // IM 提醒
        $data = implode(' ', array(
            'tudu',
            'create',
            '',
            http_build_query(array(
                'tuduid'  => $meeting['tudu_id'],
                'from'    => $meeting['from'],
                'to'      => implode(',', $to),
                'content' => $content
                ))
            ));

        return $this->getHttpsqs()->put($data);
    }

    /**
     * 发送邮件提醒
     *
     * @param array $to
     */
    public function sendMail($tsId, $meeting, $to)
    {
        list($from, $sender) = explode(' ', $meeting['from']);
        $location  = $meeting['location'];

        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $tsId,
                'tuduid'   => $meeting['tudu_id'],
                'from'     => $sender,
                'location' => $location,
                'to'     => implode(',', $to),
                'act'    => 'meeting'
            ))
        ));

        return $this->getHttpsqs()->put($data);
    }

    /**
     * 发送周期任务消息
     *
     * @param $tuduId
     * @param $cycleId
     */
    public function cycle($tsId, $tuduId, $cycleId)
    {
        $data = implode(' ', array(
            'tudu',
            'cycle',
            '',
            http_build_query(array(
                'tuduid'  => $tuduId,
                'tsid'    => $tsId,
                'cycleid' => $cycleId
                ))
            ));

        return $this->getHttpsqs()->put($data, $this->_options['httpsqs']['names']['tudu']);
    }

    /**
     * 获取httpsqs对象
     *
     */
    public function getHttpsqs()
    {
        if (null === $this->_httpsqs) {
            $config = $this->_options['httpsqs'];

            $this->_httpsqs = new Oray_Httpsqs(
                $config['host'],
                $config['port'],
                $config['charset'],
                $config['name']
            );
        }

        return $this->_httpsqs;
    }
}