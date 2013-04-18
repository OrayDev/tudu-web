<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Contact_Group extends Oray_Dao_Abstract
{
    
    /**
     * 获取指定单个联系人群组数据，返回联系人群组记录对象，若群组不存在则返回null
     * 
     * SELECT group_id AS groupid, unique_id AS uniqueid, is_system AS issystem, name, bgcolor
     * FROM td_contact_group WHERE group_id = ? AND unique_id = ?
     * 
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Contact_Group
     */
    public function getGroup(array $condition, $filter = null)
    {
        $table   = 'td_contact_group';
        $columns = 'group_id AS groupid, unique_id AS uniqueid, is_system AS issystem, name AS groupname, bgcolor, order_num AS ordernum ';
        $where   = array();
        
        if (!empty($condition['groupid'])) {
            $where[] = 'group_id = ' . $this->_db->quote($condition['groupid']);
        }
        
        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }
        
        if (!$where) {
            return null;
        }
        
        if (isset($filter['issystem'])) {
            $where[] = 'is_system = ' . ($filter['issystem'] ? 1 : 0);
        }
        
        $where = implode(' AND ' , $where);
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return null;
            }
            
            return Oray_Dao::record('Dao_Td_Contact_Record_Group', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e);
            return null;
        }
    }
    
    /**
     * 获取用户群组列表
     * SELECT group_id AS groupid, unique_id AS uniqueid, is_system AS issystem, name, bgcolor
     * FROM td_contact_group WHERE ... 
     * ORDER BY 
     * 
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getGroups(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_contact_group';
        $columns = 'group_id AS groupid, unique_id AS uniqueid, is_system AS issystem, name AS groupname, bgcolor, order_num AS ordernum ';
        $where   = array();
        $limit   = '';
        $order   = array();
        
        if (!empty($condition['groupid'])) {
            $where[] = 'group_id = ' . $this->_db->quote($condition['groupid']);
        }
        
        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }
        
        if (!$where) {
            return new Oray_Dao_Recordset();
        }
        
        if (isset($filter['issystem'])) {
            $where[] = 'is_system = ' . ($filter['issystem'] ? 1 : 0);
        }
        
        $where = implode(' AND ' , $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }
        
        $order[] = 'is_system DESC';
        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'order_num';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }
        
        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }
        
        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }
        
        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";
        
        try {
            $records = $this->_db->fetchAll($sql);
            
            return new Oray_Dao_Recordset($records, 'Dao_Td_Contact_Record_Group');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return new Oray_Dao_Recordset();
        }
    }
    
    /**
     * 
     * @param $uniqueId
     * @param $filter
     * @param $sort
     * @param $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getGroupsByUniqueId($uniqueId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getGroups(array('uniqueid' => $uniqueId), $filter, $sort, $maxCount);
    }
    
    /**
     * 获取用户群组数量
     * 
     * @param $uniqueId
     * @return int
     */
    public function getGroupCount($uniqueId)
    {
        $sql = 'SELECT COUNT(0) FROM td_contact_group WHERE unique_id = ' . $this->_db->quote($uniqueId);
        
        try {
            $count = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return 0;
        }
        
        return $count;
    }
    
    /**
     * 创建群组
     * 
     * @param array $params
     * @return boolean
     */
    public function createGroup(array $params)
    {
        if (empty($params['groupid'])
            || empty($params['uniqueid'])
            || empty($params['groupname']))
        {
            return false;
        }
        
        $table = 'td_contact_group';
        $bind  = array(
            'group_id'  => $params['groupid'],
            'unique_id' => $params['uniqueid'],
            'name'      => $params['groupname']
        );
        
        if (isset($params['issystem'])) {
            $bind['is_system'] = ($params['issystem'] ? 1 : 0);
        }
        
        if (isset($params['bgcolor'])) {
            $bind['bgcolor'] = $params['bgcolor'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }
        
        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return $params['groupid'];
    }
    
    /**
     * 更新群组信息
     * 
     * @param string $groupId
     * @param string $uniqueId
     * @param $params
     */
    public function updateGroup($groupId, $uniqueId, array $params)
    {
        if (!$groupId || !$uniqueId || !count($params)) {
            return false;
        }
        
        $table = 'td_contact_group';
        $bind  = array();
        $where = 'group_id = ' . $this->_db->quote($groupId) . ' AND '
               . 'unique_id = ' . $this->_db->quote($uniqueId);
               
        if (!empty($params['groupname'])) {
            $bind['name'] = $params['groupname'];
        }
        
        if (array_key_exists('bgcolor', $params)) {
            $bind['bgcolor'] = $params['bgcolor'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }
        
        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除用户群组
     * 
     * @param string $groupId
     * @param string $uniqueId
     * @return boolean
     */
    public function deleteGroup($groupId, $uniqueId)
    {
        $sql = 'call sp_td_delete_contact_group(' . $this->_db->quote($groupId) . ', ' . $this->_db->quote($uniqueId) . ')';
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 调整排序
     * 
     * @param $orgId
     * @param $userId
     * @param $address
     * @param $type
     * @return boolean
     */
    public function sortGroup($groupId, $uniqueId, $type)
    {
        $group = $this->getGroup(array(
            'groupid'  => $groupId,
            'uniqueid' => $uniqueId
        ));
        
        if (null === $group) {
            return false;
        }
        
        $sql = 'SELECT order_num, group_id FROM td_contact_group WHERE unique_id = ' . $this->_db->quote($uniqueId);
        
        if ($type == 'down') {
            $sql .= ' AND order_num < ' . $group->orderNum . ' ORDER BY order_num DESC';
        } else {
            $sql .= ' AND order_num > ' . $group->orderNum . ' ORDER BY order_num ASC';
        }
        
        $sql .= ' LIMIT 1';
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return false;
            }
            
            $ret = $this->updateGroup($groupId, $uniqueId, array('ordernum' => (int) $record['order_num']));
            
            $this->updateGroup($record['group_id'], $uniqueId, array('ordernum' => $group->orderNum));
            
            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 整理用户分组排序索引（排序不变）
     * 
     * @param $orgId
     * @param $userId
     */
    public function tidyGroupSort($uniqueId)
    {
        $uniqueId  = $this->_db->quote($$uniqueId);
        
        $sql = "SELECT group_id, order_num FROM td_contact_group WHERE unique_id = {$uniqueId} ORDER BY order_num DESC";
        
        $records = $this->_db->fetchAll($sql);
        
        $count = count($records);
        
        try {
            for ($i = 0; $i < $count; $i++) {
                $this->_db->update(
                    'td_contact_group',
                    array('order_num' => $count - $i),
                    "unique_id = {$uniqueId} AND group_id = " . $this->_db->quote($records[$i]['group_id'])
                );
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 
     * @param $uniqueId
     * @return int
     */
    public function getMaxOrderNum($uniqueId)
    {
        $sql = 'SELECT MAX(order_num) FROM td_contact_group WHERE unique_id = ' . $this->_db->quote($uniqueId);
        
        try {
            return (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 添加成员
     * 
     * @param $contactId
     * @return boolean
     */
    public function addMember($groupId, $uniqueId, $contactId)
    {
        $sql = 'call sp_td_add_group_member(' . $this->_db->quote($groupId) . ', ' . $this->_db->quote($uniqueId) . ', '
             . $this->_db->quote($contactId) . ')';
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
        	if (23000 === $e->getCode()) {
        		return true;
        	}
        	
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 移除成员
     * 
     * @param $groupId
     * @param $uniqueId
     * @param $contactId
     * @return boolean
     */
    public function removeMember($groupId, $uniqueId, $contactId)
    {
        $sql = 'call sp_td_delete_group_member(' . $this->_db->quote($groupId) . ', ' . $this->_db->quote($uniqueId) . ', '
             . $this->_db->quote($contactId) . ')';
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取群组ID
     * 
     * @return string
     */
    public static function getGroupId()
    {
        return 'XG' . base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}