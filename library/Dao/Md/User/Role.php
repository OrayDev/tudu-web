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
 * @version    $Id: Role.php 1552 2012-02-03 08:25:35Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Role extends Oray_Dao_Abstract
{

    /**
     * 获取群组数据
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Tudu_User_Record_Group
     */
    public function getRole(array $condition, $filter = null)
    {
        if (empty($condition['orgid']) || empty($condition['roleid'])) {
            return null;
        }

        $table   = 'md_role';
        $columns = 'org_id AS orgid, role_id AS roleid, role_name AS rolename, is_system AS issystem, is_locked AS islocked, admin_level AS adminlevel';

        $where   = array();

        $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        $where[] = 'role_id = ' . $this->_db->quote($condition['roleid']);

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        return Oray_Dao::record('Dao_Md_User_Record_Role', $record);
    }

    /**
     * 获取群组列表
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getRoles(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_role';
        $columns = 'org_id AS orgid, role_id AS roleid, role_name AS rolename, is_system AS issystem, is_locked AS islocked, admin_level AS adminlevel';

        $where   = array();
        $order   = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['issystem'])) {
            $where[] = 'is_system = ' . $condition['issystem'] ? 1 : 0;
        }

        if (isset($condition['islocked'])) {
            $where[] = 'is_locked = ' . $condition['islocked'] ? 1 : 0;
        }

        if (isset($condition['adminlevel']) && is_int($condition['adminlevel'])) {
            $where[] = 'admin_level = ' . $condition['adminlevel'];
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        $where = ' WHERE ' . implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'adminlevel' :
                    $key = 'admin_level';
                    break;
                case 'issystem' :
                    $key = 'is_system';
                    break;
                case 'islocked' :
                    $key = 'is_locked';
                    break;
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);

        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Role');
    }

    /**
     * 分页获取群组数据
     *
     * @param array $condition
     * @param array $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getRolePage(array $condition, $sort = null, $page = null, $pageSize = null)
    {

    }

    /**
     * 创建群组数据
     *
     * @param array $params
     * @return boolean
     */
    public function createRole(array $params)
    {
        if (empty($params['orgid']) || empty($params['rolename'])
           || empty($params['roleid'])) {
            return false;
        }

        $table   = 'md_role';
        $bind    = array(
           'org_id'    => $params['orgid'],
           'role_id'   => $params['roleid'],
           'role_name' => $params['rolename']
        );

        if (isset($params['issystem'])) {
            $bind['is_system'] = $params['issystem'] ? 1 : 0;
        }

        if (isset($params['islocked'])) {
            $bind['is_locked'] = $params['islocked'] ? 1 : 0;
        }

        if (isset($params['adminlevel']) && is_int($params['adminlevel'])) {
            $bind['admin_level'] = $params['adminlevel'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 更新群组数据
     *
     * @param string $orgId
     * @param string $roleId
     * @param array  $params
     * @return boolean
     */
    public function updateRole($orgId, $roleId, array $params)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $table = 'md_role';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND role_id = ' . $this->_db->quote($roleId);

        if (!empty($params['rolename'])) {
            $bind['role_name'] = $params['rolename'];
        }

        if (isset($params['issystem'])) {
            $bind['is_system'] = $params['issysmte'] ? 1 : 0;
        }

        if (isset($params['islocked'])) {
            $bind['is_locked'] = $params['islocked'] ? 1 : 0;
        }

        if (isset($params['adminlevel']) && is_int($params['adminlevel'])) {
            $bind['admin_level'] = $params['adminlevel'];
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 删除群组
     *
     * @param string $orgId
     * @param string $roleId
     * @return boolean
     */
    public function deleteRole($orgId, $roleId)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $sqls = array();

        $orgId   = $this->_db->quote($orgId);
        $roleId = $this->_db->quote($roleId);

        $sqls[] = "DELETE FROM md_role_access WHERE org_id = {$orgId} AND role_id = {$roleId}";
        $sqls[] = "DELETE FROM md_role WHERE org_id = {$orgId} AND role_id = {$roleId}";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 群组是否存在
     *
     * @param string $orgId
     * @param string $roleId
     */
    public function existsRole($orgId, $roleId)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM md_role WHERE org_id = ' . $this->_db->quote($orgId) . ' AND role_id = ' . $this->_db->quote($roleId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count > 0;
    }

    /**
     * 获取组织权限组个数
     *
     * @param string $orgId
     */
    public function getRoleCount($orgId)
    {
        if (!$orgId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM md_role WHERE org_id = ' . $this->_db->quote($orgId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 统计用户数量
     *
     * @param $orgId
     * @param $groupId
     */
    public function countUser($orgId, $roleId)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM md_user_role WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND role_id = ' . $this->_db->quote($roleId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 添加用户
     *
     * @param string $orgId
     * @param string $roleId
     * @param string $userId
     * @return boolean
     */
    public function addUsers($orgId, $roleId, $userId)
    {
        if (!$orgId || !$userId || !$roleId) {
            return false;
        }

        $table  = 'md_user_role';
        $values = array();

        $sql = 'INSERT INTO md_user_role (org_id, role_id, user_id) VALUES ';

        $orgId   = $this->_db->quote($orgId);
        $roleId = $this->_db->quote($roleId);
        $userId  = (array) $userId;

        foreach ($userId as $id) {
            $values[] = '(' . $orgId . ', ' . $roleId . ', ' . $this->_db->quote($id) .')';
        }

        $sql .= implode(',', $values);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 移除用户
     *
     * @param string $orgId
     * @param string $roleId
     * @param string $userId
     * @return boolean
     */
    public function removeUser($orgId, $roleId, $userId = null)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);

        $where[] = 'role_id = ' . $this->_db->quote($roleId);

        if ($userId) {
            $where[] = 'user_id = ' . $this->_db->quote($userId);
        }

        $where = implode(' AND ', $where);

        $sql = 'DELETE FROM md_user_role WHERE ' . $where;

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param $orgId
     * @param $roleId
     * @param $accessId
     */
    public function removeAccess($orgId, $roleId, $accessId = null)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $sql = 'DELETE FROM md_role_access WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND role_id = ' . $this->_db->quote($roleId);

        if ($accessId) {
            $sql .= ' AND access_id = ' . $accessId;
        }

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 添加权限设置
     *
     * @param string $orgId
     * @param string $roleId
     * @param array $access
     * @return boolean
     */
    public function addAccess($orgId, $roleId, array $access)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        if (empty($access['accessid']) || !isset($access['value'])) {
            return false;
        }

        $table = 'md_role_access';
        $bind  = array(
            'org_id'    => $orgId,
            'role_id'   => $roleId,
            'access_id' => (int) $access['accessid'],
            'value'     => $access['value']
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取角色权限设置
     *
     * @param string $orgId
     * @param string $roleId
     * @return boolean
     */
    public function getAccesses($orgId, $roleId)
    {
        if (!$orgId || !$roleId) {
            return false;
        }

        $table   = 'md_role_access';
        $columns = 'org_id AS orgid, role_id AS roleid, access_id as accessid, value';

        $where   = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'role_id = ' . $this->_db->quote($roleId);

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        $_records = $this->_db->fetchAll($sql);

        $record = array();
        foreach ($_records as $item) {
            $record[$item['accessid']] = $item;
        }

        return $record;
    }

    /**
     * 获取用户ID
     *
     * @param string $orgId
     * @param string $roleId
     * @return array
     */
    public function getUserIds($orgId, $roleId)
    {
        $sql = 'SELECT user_id FROM md_user_role WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND role_id = ' . $this->_db->quote($roleId);

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $record) {
            $ret[] = $record['user_id'];
        }

        return $ret;
    }

    /**
     * 生成群组ID
     *
     * @param $name
     * @return string
     */
    public static function getRoleId($orgId, $roleName)
    {
        return substr(md5($roleName . '@' . $orgId . '^' . time()), 8, 16);
    }
}