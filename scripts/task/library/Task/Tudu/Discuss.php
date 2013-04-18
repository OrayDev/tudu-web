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
 * @version    $Id: Discuss.php 1365 2011-12-08 10:15:18Z cutecube $
 */

/**
 * 计划：每天执行一次
 *
 * 讨论数据处理
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Tudu_Discuss extends Task_Abstract
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
        foreach ($this->_dbs as $key => $db) {
            // 清理过期讨论
            $sql = 'SELECT tudu_id, org_id FROM td_tudu WHERE type = \'discuss\' AND end_time IS NOT NULL '
                 . 'AND is_draft = 0 AND is_done = 0 AND end_time < ' . strtotime('today');

            Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $db, true);

            $manager = Tudu_Tudu_Manager::getInstance();
            $daoLog  = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

            $array = $db->fetchAll($sql);

            foreach ($array as $item) {

                if ($manager->closeTudu($item['tudu_id'], true)) {
                    $this->getLogger()->info("Discuss id:({$item['tudu_id']}) close success");

                    // 成功记录操作日志
                    $daoLog->createLog(array(
                        'orgid'      => $item['org_id'],
                        'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                        'targetid'   => $item['tudu_id'],
                        'uniqueid'   => '^system',
                        'action'     => Dao_Td_Log_Log::ACTION_CLOSE,
                        'detail'     => serialize(array('isdone' => true)),
                        'logtime'    => time()
                    ));

                    continue ;
                }

                $this->getLogger()->warn("Discuss id:{$item['tudu_id']} close failure");
            }

            $this->getLogger()->info("Ts db:({$key}) process complete");
        }
    }
}