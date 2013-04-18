<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md_Log_AdminLog
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Info.php 1031 2011-07-28 10:17:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md_Log_AdminLog
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Ip_Info extends Oray_Dao_Abstract
{

    /**
     * 获取IP地址信息
     *
     * @param array $condition
     */
    public function getInfo(array $condition, $filter = null)
    {

        $table   = 'md_ip_data';
        $columns = 'start_ip AS startip, end_ip AS endip, province, city';
        $where   = array();

        if (isset($condition['ip'])) {
            if (!is_int($condition['ip'])) {
                $condition['ip'] = sprintf('%u', ip2long($condition['ip']));
            }

            $where[] = "end_ip >= {$condition['ip']} AND start_ip <= {$condition['ip']}";
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Md_Ip_Record_Info', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return null;
        }
    }

    /**
     * 获取IP地址信息
     *
     * @param int | string $ip
     */
    public function getInfoByIp($ip)
    {
        return $this->getInfo(array('ip' => $ip));
    }
}