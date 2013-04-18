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
 * @version    $Id: Notice.php 1716 2012-03-16 08:58:16Z cutecube $
 */

/**
 * 计划：每天执行一次
 *
 * 置顶公告数据处理
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Tudu_Notice extends Task_Abstract
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
            // 清理过期置顶公告
            $sql = 'SELECT tudu_id FROM td_tudu WHERE type = \'notice\' AND end_time IS NOT NULL AND is_top = 1 '
                 . 'AND end_time < ' . strtotime('today');

            Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $db, true);

            $manager = Tudu_Tudu_Manager::getInstance();

            $array = $db->fetchAll($sql);

            foreach ($array as $item) {

                if ($manager->updateTudu($item['tudu_id'], array('istop' => 0))) {
                    $users = $manager->getTuduUsers($item['tudu_id']);

                    foreach ($users as $user) {
                        if (!$user['isread']) {
                            continue ;
                        }

                        $manager->deleteLabel($item['tudu_id'], $user['uniqueid'], '^i');
                    }

                    $this->getLogger()->info("Notice id:{$item['tudu_id']} update success");
                    continue ;
                }

                $this->getLogger()->warn("Notice id:{$item['tudu_id']} update failed");
            }

            $this->getLogger()->info("Ts db:({$key}) process complete");
        }
    }
}