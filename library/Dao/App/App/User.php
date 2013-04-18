<?php
/**
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: User.php 1863 2012-05-16 10:05:22Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_App_User extends Oray_Dao_Abstract
{

    const ROLE_ADMIN = 'admin'; // 最高权限
    const ROLE_SUM   = 'sum'; // 统计人员
    const ROLE_DEF   = 'def'; // 考勤规则定义人员
    const ROLE_SC    = 'sc';  // 排班设计

    /**
     * 获取应用
     *
     * @param array $condition
     * @param unknown_type $filter
     */
    public function getAppUser(array $condition, $filter = null)
    {
        $table   = 'app_user';
        $columns = 'org_id AS orgid, app_id AS appid, item_id AS itemid, role';
        $where   = array();
        $bind    = array();

        if (!empty($condition['orgid'])) {
            $where[]       = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (!empty($condition['appid'])) {
            $where[]       = 'app_id = :appid';
            $bind['appid'] = $condition['appid'];
        }

        if (!empty($condition['itemid'])) {
            $where[]        = 'item_id = :itemid';
            $bind['itemid'] = $condition['itemid'];
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

            return Oray_Dao::record($record, 'Dao_App_App_User');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getAppUsers(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'app_user';
        $columns = 'org_id AS orgid, app_id AS appid, item_id AS itemid, role';
        $where   = array();
        $bind    = array();
        $order   = array();
        $limit   = '';

        $recordClass = 'Dao_App_App_Record_User';

        if (!empty($condition['orgid'])) {
            $where[]       = 'org_id = ?';
            $bind[] = $condition['orgid'];
        }

        if (!empty($condition['appid'])) {
            $where[]       = 'app_id = ?';
            $bind[] = $condition['appid'];
        }

        if (!empty($condition['itemid'])) {
            if (is_string($condition['itemid'])) {
                $where[]        = 'item_id = ?';
                $bind[] = $condition['itemid'];
            } else if (is_array($condition['itemid'])) {
                $where[] = 'item_id IN (' . implode(',', array_fill(0, count($condition['itemid']), '?')) . ')';

                foreach ($condition['itemid'] as $item) {
                    $bind[] = $item;
                }
            }
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'expiredate':
                    $key = 'expire_date';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return new Oray_Dao_Recordset();
        }
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function addUserRole(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['appid'])
            || empty($params['itemid'])
            || empty($params['role']))
        {
            return false;
        }

        $table = 'app_user';
        $bind  = array(
            'org_id'  => $params['orgid'],
            'app_id'  => $params['appid'],
            'item_id' => $params['itemid'],
            'role'    => $params['role']
        );

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['expiredate'])) {
            $bind['expire_date'] = $params['expiredate'];
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
     *
     * @param array $params
     * @return boolean
     */
    public function deleteUserRole($orgId, $appId, $itemId, $role)
    {
        $sql = "DELETE FROM app_user WHERE org_id = :orgid AND app_id = :appid AND item_id = :itemid AND role = :role";

        $bind = array(
            'orgid'  => $orgId,
            'appid'  => $appId,
            'itemid' => $itemId,
            'role'   => $role
        );

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param $orgId
     * @param $appId
     */
    public function deleteUsers($orgId, $appId)
    {
        $sql  = "DELETE FROM app_user WHERE org_id = :orgid AND app_id = :appid";
        $bind = array(
            'orgid' => $orgId,
            'appid' => $appId
        );

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }

        return true;
    }
}
