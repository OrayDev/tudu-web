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
class Dao_Md_User_Group extends Oray_Dao_Abstract
{
    
    /**
     * 获取群组数据
     * 
     * @param array $condition
     * @param array $filter
     * @return Dao_Tudu_User_Record_Group
     */
    public function getGroup(array $condition, $filter = null)
    {
    	if (empty($condition['orgid']) || empty($condition['groupid'])) {
    		return null;
    	}
    	
    	$table   = 'md_group';
    	$columns = 'org_id AS orgid, group_id AS groupid, group_name AS groupname, is_system AS issystem, is_locked AS islocked, admin_level AS adminlevel, order_num as ordernum';
    	
    	$where   = array();
    	
    	$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
    	$where[] = 'group_id = ' . $this->_db->quote($condition['groupid']);
    	
    	$where = implode(' AND ', $where);
    	
    	$sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
    	
    	$record = $this->_db->fetchRow($sql);
    	
    	return Oray_Dao::record('Dao_Md_User_Record_Group', $record);
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
    public function getGroups(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
    	$table   = 'md_group';
        $columns = 'org_id AS orgid, group_id AS groupid, group_name AS groupname, is_system AS issystem, is_locked AS islocked, admin_level AS adminlevel, order_num as ordernum';
        
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
                case 'ordernum' :
                    $key = 'order_num';
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
        
        return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Group');
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
    public function getGroupPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
    	
    }
    
	/**
     * 获取排序最大值 
     * 
     * @param $condition
     * @return int
     */
    public function getGroupMaxOrderNum($condition)
    {
    	$where = array();
    	
        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (isset($condition['issystem'])) {
        	$where[] = 'is_system = ' . $this->_db->quote($condition['issystem']);
        }
        
        if (!$where) {
        	return false;
        }
        //SELECT MAX(order_num) FROM td_board where org_id = 'oray' AND `type` = 'zone'
        $sql = "SELECT MAX(order_num) FROM md_group WHERE " . implode(' AND ', $where);
        
        $count = (int) $this->_db->fetchOne($sql);
        
        return $count;
    }
    
    /**
     * 创建群组数据
     * 
     * @param array $params
     * @return boolean
     */
    public function createGroup(array $params)
    {
    	if (empty($params['orgid']) || empty($params['groupname'])
    	   || empty($params['groupid'])) {
    		return false;
    	}
    	
    	$table   = 'md_group';
    	$bind    = array(
    	   'org_id'     => $params['orgid'],
    	   'group_id'   => $params['groupid'],
    	   'group_name' => $params['groupname']
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
    	
        if (isset($params['ordernum'])) {
        	$bind['order_num'] = (int) $params['ordernum'];
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
     * @param string $groupId
     * @param array  $params
     * @return boolean
     */
    public function updateGroup($orgId, $groupId, array $params)
    {
    	if (!$orgId || !$groupId) {
    		return false;
    	}
    	
    	$table = 'md_group';
    	$bind  = array();
    	$where = 'org_id = ' . $this->_db->quote($orgId) . ' AND group_id = ' . $this->_db->quote($groupId);
    	
    	if (!empty($params['groupname'])) {
    		$bind['group_name'] = $params['groupname'];
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
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
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
     * @param string $deptId
     * @return boolean
     */
    public function deleteGroup($orgId, $groupId)
    {
    	if (!$orgId || !$groupId) {
    		return false;
    	}
    	
    	$sqls = array();
    	
    	$orgId   = $this->_db->quote($orgId);
    	$groupId = $this->_db->quote($groupId);
    	
    	$sqls[] = "DELETE FROM md_group_access WHERE org_id = {$orgId} AND group_id = {$groupId}";
    	$sqls[] = "DELETE FROM md_group WHERE org_id = {$orgId} AND group_id = {$groupId}";
    	
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
     * 调整排序
     * 
     * @param $orgId
     * @return boolean
     */
    
    public function sortGroup($groupId, $orgId, $sort)
    {
        $group = $this->getGroup(array(
            'groupid'  => $groupId,
            'orgid' => $orgId,
        ));
        
        if (null === $group) {
            return false;
        }
        
        $sql = 'SELECT order_num, group_id FROM md_group WHERE org_id = ' . $this->_db->quote($orgId);
        
        if ($sort == 'down') {
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
            
            $ret = $this->updateGroup($orgId, $groupId, array('ordernum' => (int) $record['order_num']));
            
            $this->updateGroup($orgId, $record['group_id'], array('ordernum' => $group->orderNum));
            
            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 群组是否存在
     * 
     * @param string $orgId
     * @param string $groupId
     */
    public function existsGroup($orgId, $groupId)
    {
    	if (!$orgId || !$groupId) {
    		return false;
    	}
    	
    	$sql = 'SELECT COUNT(0) FROM md_group WHERE org_id = ' . $this->_db->quote($orgId) . ' AND group_id = ' . $this->_db->quote($groupId);
    	
    	$count = (int) $this->_db->fetchOne($sql);
    	
    	return $count > 0;
    }

    /**
     * 获取组织群组个数
     *
     * @param string $orgId
     */
    public function getGroupCount($orgId)
    {
        if (!$orgId) {
			return false;
		}

		$sql = 'SELECT COUNT(0) FROM md_group WHERE org_id = ' . $this->_db->quote($orgId);

		$count = (int) $this->_db->fetchOne($sql);

		return $count;
    }
    
    /**
     * 统计用户数量
     * 
     * @param $orgId
     * @param $groupId
     */
    public function countUser($orgId, $groupId)
    {
        if (!$orgId || !$groupId) {
            return false;
        }
        
    	$sql = 'SELECT COUNT(0) FROM md_user_group WHERE org_id = ' . $this->_db->quote($orgId) . ' '
    	     . 'AND group_id = ' . $this->_db->quote($groupId);
    	
    	$count = (int) $this->_db->fetchOne($sql);
    	
    	return $count;
    }
    
    /**
     * 添加用户
     * 
     * @param string $orgId
     * @param string $groupId
     * @param string $userId
     * @return boolean
     */
    public function addUser($orgId, $groupId, $userId)
    {
        if (!$orgId || !$userId || !$groupId) {
            return false;
        }
        
        $table  = 'md_user_group';
        $values = array();
        
        $sql = 'INSERT INTO md_user_group (org_id, group_id, user_id) VALUES ';
        
        $orgId   = $this->_db->quote($orgId);
        $groupId = $this->_db->quote($groupId);
        $userId  = (array) $userId;
        
        foreach ($userId as $id) {
        	$values[] = '(' . $orgId . ', ' . $groupId . ', ' . $this->_db->quote($id) .')';
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
     * @param string $groupId
     * @param string $userId
     * @return boolean
     */
    public function removeUser($orgId, $groupId, $userId = null)
    {
        if (!$orgId || !$groupId) {
            return false;
        }
        
        $where = array();
        
        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        
        $where[] = 'group_id = ' . $this->_db->quote($groupId);
        
        if ($userId) {
        	$where[] = 'user_id = ' . $this->_db->quote($userId); 
        }
        
        $where = implode(' AND ', $where);
        
        $sql = 'DELETE FROM md_user_group WHERE ' . $where;
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }
        
        return true;
    }
    
	/**
     * 获取用户ID
     * 
     * @param string $orgId
     * @param string $groupId
     * @return array
     */
    public function getUserIds($orgId, $groupId)
    {
    	$sql = 'SELECT user_id FROM md_user_group WHERE org_id = ' . $this->_db->quote($orgId) . ' '
    	     . 'AND group_id = ' . $this->_db->quote($groupId);
    	     
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
    public static function getGroupId($orgId, $groupName)
    {
    	return substr(md5($groupName . '@' . $orgId . '^' . time()), 8, 16);
    }
}