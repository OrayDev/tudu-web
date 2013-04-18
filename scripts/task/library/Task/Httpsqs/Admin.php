<?php
/**
 * Task_Httpsqs
 *
 * LICENSE
 *
 *
 * @category   Task_Httpsqs_Admin
 * @package    Task_Httpsqs_Admin
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Admin.php 2024 2012-07-25 05:31:08Z cutecube $
 */

/**
 * 图度后台脚本
 * * * 新手任务
 * * * 系统欢迎公告
 * * * 删除用户（图度执行人待定，即是发起人又是发起人的终止图度任务，100%确认图度）
 *
 * @category   Task_Httpsqs_Admin
 * @package    Task_Httpsqs_Admin
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Httpsqs_Admin extends Task_Abstract
{
	/**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     * @var array
     */
    protected $_tsDbs = array();

    /**
     *
     * @var string
     */
    protected $_unId = '^system';

    /**
     *
     * @var string
     */
    protected $_unName = '图度系统';

    /**
     *
     */
    public function startUp()
    {
        foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_tsDbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
                continue ;
            }
            Tudu_Dao_Manager::setDb($key, Zend_Db::factory($item['adapter'], $item['params']));
        }

        $this->_httpsqs = new Oray_Httpsqs(
            $this->_options['httpsqs']['host'],
            $this->_options['httpsqs']['port'],
            $this->_options['httpsqs']['charset'],
            $this->_options['httpsqs']['names']['admin']
        );
    }

    /**
     *
     */
    public function shutDown()
    {
        $this->_httpsqs->closeConnection();
    }

    /**
     * 执行
     */
    public function run()
    {
    	do {
    	    $data = $this->_httpsqs->get($this->_options['httpsqs']['names']['admin']);

            if (!$data || $data == 'HTTPSQS_GET_END') {
                break ;
            }

            list($module, $action, $sub, $query) = explode(' ', $data);

    	    if ($module != 'user') {
                $this->getLogger()->warn("Invalid param \"module\" values {$module}");
            }

    	    switch ($action) {
                case 'create':
                    $this->createUser($query);
                    break;
                case 'delete':
                    $this->deleteUser($query);
                    break ;
                default:
                    $this->getLogger()->info("Invalid action values {$action}");
                    break;
            }
    	} while (true);
    }

    /**
     *
     * @param string $params
     */
    public function createUser($params)
    {
    	list($orgId, $address, $uniqueId, $truename) = explode(':', $params);

    	$daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
    	$user    = $daoUser->getUser(array('uniqueid' => $uniqueId));

    	$defaultTime = mktime(0, 0, 0, 11, 23, 2011);

    	if ($user->createTime > $defaultTime) {
    	    $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
    	    $org = $daoOrg->getOrg(array('orgid' => $orgId));

    	    $tsId = $org->tsid;
    	    Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $this->_tsDbs['ts' . $tsId]);

    	    $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

    	    // 系统欢迎公告ID
    	    $tuduId = md5($orgId . '-welcome');

    	    // 查看系统欢迎公告是否存在
            $tudu = $manager->getTuduById($tuduId, $uniqueId);
            if (null === $tudu) {
                $manager->addRecipient($tuduId, $uniqueId);
                $manager->addLabel($tuduId,  $uniqueId, '^all');
                $manager->addLabel($tuduId,  $uniqueId, '^i');
                $manager->addLabel($tuduId,  $uniqueId, '^n');
                $this->getLogger()->debug("System Welcome Notice Tudu id:{$tuduId} done");
            }

            // 查看图度新手任务是否存在
            $newbieTuduId = 'newbie-' . $uniqueId;
            $newbieTudu = $manager->getTuduById($newbieTuduId, $uniqueId);

    	    if (null === $newbieTudu) {
                $content = file_get_contents($this->_options['data']['path'] . '/templates/tudu/newbie_tudu_task.tpl');
                if ($content) {
                    $tudu = array(
                        'orgid'        => $orgId,
                        'tuduid'       => $newbieTuduId,
                        'boardid'      => '^system',
                        'uniqueid'     => $this->_unId,
                        'type'         => 'task',
                        'subject'      => '图度新手任务',
                        'email'        => 'robot@oray.com',
                        'from'         => $this->_unId . ' ' . $this->_unName,
                        'to'           => $address . ' ' . $truename,
                        'cc'           => null,
                        'priority'     => 0,
                        'privacy'      => 0,
                        'issend'       => 1,
                        'needconfirm'  => 0,
                        'status'       => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
                        'content'      => $content,
                        'starttime'    => strtotime(date('Y-m-d', time())),
                        'endtime'      => null,
                        'totaltime'    => 3 * 3600,
                        'poster'       => $this->_unName,
                        'posterinfo'   => '',
                        'lastposter'   => $this->_unName,
                        'lastposttime' => time(),
                        'createtime'   => time(),
                        'attachment'   => array()
                    );

                    $tudu    = new Tudu_Tudu_Storage_Tudu($tudu);
                    $storage = Tudu_Tudu_Storage::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));
                    $deliver = Tudu_Tudu_Deliver::getInstance();

                    // 创建新手任务
                    $tuduId  = $storage->createTudu($tudu);
                    if (!$tuduId) {
                        $this->getLogger()->warn("Create newbie Tudu failed id:{$newbieTuduId}");
                        return ;
                    }

                    // 发送新手任务
                    $ret = $deliver->sendTudu($tudu, array());
                    if (!$ret) {
                        $this->getLogger()->warn("Send newbie Tudu failed id:{$newbieTuduId}");
                        return ;
                    }

                    $manager->addRecipient($tuduId, $uniqueId, array('role' => 'to'));
                    $manager->addLabel($tuduId,  $uniqueId, '^all');
                    $manager->addLabel($tuduId,  $uniqueId, '^i');
                    $manager->addLabel($tuduId,  $uniqueId, '^a');
                    $this->getLogger()->debug("Newbie Tudu id:{$tuduId} done");
                }
            }
    	}
    }

    /**
     *
     * @param string $params
     */
    public function deleteUser($params)
    {
        list($orgId, $uniqueIds) = explode(':', $params);
        $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
        $org = $daoOrg->getOrg(array('orgid' => $orgId));

        $tsId = $org->tsid;
        Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $this->_tsDbs['ts' . $tsId]);

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $uniqueIds = explode(',', $uniqueIds);
        foreach ($uniqueIds as $uniqueId) {
        	$tudus = $daoTudu->getUserTudus(array('uniqueid' => $uniqueId, 'type' => 'task'));

            if (count($tudus)) {
                foreach ($tudus as $tudu) {
                    // 确认图度
                    if ($tudu['role'] == 'from' && $tudu['needconfirm'] && $tudu['percent'] == 100) {
                        $ret = $manager->doneTudu($tudu['tuduid'], true, 0);
                        if (!$ret) {
                            $this->getLogger()->warn("Done Tudu failed id:{$tudu['tuduid']}");
                        }
                    }

                    // 取消（终止）
                    if ($tudu['role'] == 'from' && $tudu['from'] == $tudu['to']) {
                        $params = array(
                            'status' => Dao_Td_Tudu_Tudu::STATUS_CANCEL,
                            'isdone' => 1
                        );
                        // 更新图度
                        $ret = $manager->updateTudu($tudu['tuduid'], $params);
                        if (!$ret) {
                        	$data = serialize($params);
                            $this->getLogger()->warn("Update Tudu failed id:{$tudu['tuduid']} data:{$data}");
                        }

                        // 完结图度
                        $ret = $manager->doneTudu($tudu['tuduid'], true, 0);
                        if (!$ret) {
                            $this->getLogger()->warn("Done Tudu failed id:{$tudu['tuduid']}");
                        }
                    }

                    // 图度执行人待定
                    if ($tudu['role'] == 'to') {
                        // 移除执行信息
                        $ret = $daoTudu->removeAccepter($tudu['tuduid'], $uniqueId);
                        if (!$ret) {
                            $this->getLogger()->warn("Remove Accepter failed Tudu id:{$tudu['tuduid']} uid:{$uniqueId}");
                        }

                        if ($tudu['to'] == $tudu['accepterinfo']) {
                            // 更新to字段
                            $ret = $manager->updateTudu($tudu['tuduid'], array('to' => ''));
                            if (!$ret) {
                            	$this->getLogger()->warn("Update Tudu failed id:{$tudu['tuduid']} to:[clear]");
                            }
                        } else {
                            $newto = array();
                            $to = explode("\n", $tudu['to']);
                            for ($i=0; $i<count($to); $i++) {
                                if ($to[$i] != $tudu['accepterinfo']) {
                                    $newto[] = $to[$i];
                                }
                            }
                            // 更新to字段
                            $ret = $manager->updateTudu($tudu['tuduid'], array('to' => implode("\n", $newto)));
                            if (!$ret) {
                                $this->getLogger()->warn("Update Tudu failed id:{$tudu['tuduid']} to:[clear-{$tudu['accepterinfo']}]");
                            }
                        }
                    }
                }
            }

            $this->getLogger()->debug("Delete User Update Tudu On Uniqueid:{$uniqueId} done");
        }
    }
}