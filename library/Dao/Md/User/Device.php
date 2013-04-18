<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Group.php 1552 2012-02-03 08:25:35Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Device extends Oray_Dao_Abstract
{
    /**
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $order
     * @param int   $maxCount
     */
    public function getDevices(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_user_device';
        $columns = 'org_id as orgid, unique_id as uniqueid, device_type as devicetype, device_token as devicetoken';
        $where   = array();
        $bind    = array();
        $limit   = '';
        $order   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (!empty($condition['devicetype'])) {
            $where[] = 'device_type = :devicetype';
            $bind['devicetype'] = $condition['devicetype'];
        }

        if (empty($where)) {
            return array();
        }

        $where = implode(' AND ', $where);

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);

            return $records;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return array();
        }
    }

    /**
     *
     * @param array $params
     */
    public function createDevice(array $params)
    {
        if (empty($params['orgid']) || empty($params['uniqueid']) || empty($params['devicetoken'])) {
            return false;
        }

        $table = 'md_user_device';
        $bind  = array(
            'org_id' => $params['orgid'],
            'unique_id' => $params['uniqueid'],
            'device_token' => $params['devicetoken']
        );

        try {
            $this->_db->query("DELETE FROM {$table} WHERE device_token = :devicetoken", array('devicetoken' => $params['devicetoken']));
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param array $condition
     */
    public function deleteDevice(array $condition)
    {
        $table = 'md_user_device';
        $where = array();
        $bind  = array();

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (isset($condition['devicetoken'])) {
            $where[] = 'device_token = :devicetoken';
            $bind['devicetoken'] = $condition['devicetoken'];
        }

        if (empty($where)) {
            return false;
        }

        $where = implode(' AND ', $where);

        try {
            $this->_db->query("DELETE FROM {$table} WHERE {$where}", $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }
}