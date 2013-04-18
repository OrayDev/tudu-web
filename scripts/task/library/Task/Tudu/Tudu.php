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
 * @version    $Id: Tudu.php 2024 2012-07-25 05:31:08Z cutecube $
 */

/**
 * 计划：每天执行一次
 *
 * 确认7天内已完成未确认图度
 * 清除过期回复
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Tudu_Tudu extends Task_Abstract
{
    /**
     *
     * @var array<Zend_Db_Adapter>()
     */
    public $_dbs = array();

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
        foreach ($this->_dbs as $db) {
            $this->cleanAutoSavePost($db);
            $this->markDone($db);
        }
    }

    /**
     * 过期任务自动确认
     */
    public function markDone($db) {
        $labelId = '^o';
        $sql = "SELECT tudu_id, org_id FROM td_tudu WHERE type='task' AND status > 1 AND is_done = 0 AND last_post_time < (UNIX_TIMESTAMP() - 86400 * 7 - UNIX_TIMESTAMP() % 86400) ORDER BY last_post_time ASC";

        $records = $db->fetchAll($sql, array(), Zend_Db::FETCH_NUM);

        Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $db, true);
        $manager  = Tudu_Tudu_Manager::getInstance();
        $daoLog   = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

        foreach ($records as $record) {
            $tuduId = $record[0];
            $orgId  = $record[1];

            $rows = $db->fetchAll("SELECT tudu_id, unique_id FROM td_tudu_user WHERE tudu_id = '{$tuduId}' AND labels LIKE '%^all%' AND labels NOT LIKE '%{$labelId}%'");

            $flag = true;

            foreach ($rows as $row) {
                // 移除“图度箱”标签
                $ret = $manager->deleteLabel($row['tudu_id'], $row['unique_id'], '^i');
                // 移除“我执行”标签
                $ret = $manager->deleteLabel($row['tudu_id'], $row['unique_id'], '^a');

                if ($ret) {
                    // 添加“已完成”标签
                    $ret = $manager->addLabel($row['tudu_id'], $row['unique_id'], $labelId);
                }
                if (false === $ret) {
                    $flag = false;
                    $this->getLogger()->warn("Move Label:[{$row['tudu_id']}][{$row['unique_id']}] Failured");
                    continue ;
                }
            }

            if ($flag) {
                // 标记图度完结
                $flag = $manager->updateTudu($tuduId, array('isdone' => true));

                if (!$flag) {
                    $this->getLogger()->warn("Done tudu:[{$tuduId}] Failured");
                    continue ;
                } else {
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
                }
            }
            $this->getLogger()->info("Done tudu:[{$tuduId}] success");
        }
    }

    /**
     * 清除过期回复
     */
    public function cleanAutoSavePost($db) {
        $cleanDays = 7;
        $date = $cleanDays > 0 ? strtotime('-' . $cleanDays . ' days') : strtotime('today');

        $sql = 'SELECT post_id, tudu_id FROM td_post WHERE is_first = 0 AND is_send = 0 AND create_time < ' . $date;
        $posts = $db->fetchAll($sql);

        Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $db, true);
        $manager  = Tudu_Tudu_Manager::getInstance();

        foreach ($posts as $post) {
            $ret = $manager->deletePost($post['tudu_id'], $post['post_id']);

            if (!$ret) {
                $this->getLogger()->warn("Notice id:({$post['post_id']}) Delete Failured");
                continue ;
            }
            $this->getLogger()->info("Post id:({$post['post_id']}) Delete success");
        }
    }
}