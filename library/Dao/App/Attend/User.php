<?php
/**
 * Attend_User
 * 考勤用户信息表
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: User.php 1851 2012-05-11 10:15:03Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_User extends Oray_Dao_Abstract
{
    /**
     * 获取考勤应用用户信息
     *
     * SELECT org_id AS orgid, unique_id AS uniqueid, dept_id AS deptid, true_name AS truename, dept_name AS deptname
     * FROM attend_user
     * WHERE unique_id = :uniqueid AND org_id = :orgid
     * LIMIT 0, 1
     *
     * @param array $condition
     * @return Dao_App_Attend_Record_User|NULL|boolean
     */
    public function getUser(array $condition)
    {
        if (empty($condition['uniqueid'])) {
            return false;
        }

        $table   = 'attend_user';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, dept_id AS deptid, true_name AS truename, dept_name AS deptname';
        $recordClass = 'Dao_App_Attend_Record_User';
        $where   = array();
        $bind    = array('uniqueid' => $condition['uniqueid']);

        $where[] = 'unique_id = :uniqueid';

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql, $bind);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record($recordClass, $record);
    }

    /**
     * 创建用户信息
     *
     * @param array $params
     * @return boolean
     */
    public function createUser(array $params)
    {
        if (empty($params['uniqueid'])
            || empty($params['orgid'])
            || empty($params['truename']))
        {
            return false;
        }

        $table = 'attend_user';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'unique_id'   => $params['uniqueid'],
            'true_name'   => $params['truename'],
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

        if (isset($params['deptid'])) {
            $bind['dept_id'] = $params['deptid'];
        }

        if (isset($params['deptname'])) {
            $bind['dept_name'] = $params['deptname'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新用户信息
     *
     * @param string $uniqueId
     * @param array  $params
     * @return boolean
     */
    public function updateUser($uniqueId, array $params)
    {
        if (empty($uniqueId)) {
            return false;
        }

        $table = 'attend_user';
        $bind  = array();

        if (isset($params['truename'])) {
            $bind['true_name'] = $params['truename'];
        }

        if (array_key_exists('deptid', $params)) {
            $bind['dept_id'] = $params['deptid'];
        }

        if (array_key_exists('deptname', $params)) {
            $bind['dept_name'] = $params['deptname'];
        }

        if (isset($params['updatetime'])) {
            $bind['update_time'] = (int) $params['updatetime'];
        }

        if (empty($bind)) {
            return false;
        }

        try {
            $where = 'unique_id = ' . $this->_db->quote($uniqueId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 读取用户信息记录是否存在
     *
     * @param string $uniqueId
     * @return boolean
     */
    public function existsUser($uniqueId)
    {
        if (empty($uniqueId)) {
            return false;
        }

        $table = 'attend_user';
        $bind  = array('uniqueid' => $uniqueId);

        $sql = "SELECT COUNT(0) FROM {$table} WHERE unique_id = :uniqueid";

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }
}