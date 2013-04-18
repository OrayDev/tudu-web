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
 * @version    $Id: Mac.php 13 2010-07-12 10:52:45Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Mac extends Oray_Dao_Abstract
{
    
	/**
	 * 查询相关mac地址记录
	 * 
	 * @param array $condition
	 * @param array $filter
	 * @return 
	 */
	public function getMac(array $condition, $filter = null)
	{
		$table   = 'md_mac';
		$columns = 'mac_id AS macid, mac_addr AS macaddr';
		$where   = array();
		
		if (isset($condition['macid']) && is_int($condition['macid'])) {
			$where[] = 'mac_id = ' . $condition['macid'];
		}
		
		if (!empty($condition['macaddr'])) {
			$where[] = 'mac_addr = ' . $this->_db->quote($condition['macaddr']);
		}
		
		if (!$where) {
			return false;
		}
		
		$where = implode(' AND ', $where);
		
		$sql = "SELECT {$columns} FROM {$table} WHERE {$where}";
		
		$record = $this->_db->fetchRow($sql);
		
		if (!$record) {
			return null;
		}
		
		return Oray_Dao::record('Dao_Md_User_Record_Mac', $record);
	}
	
	/**
	 * 获取用户MAC地址类表
	 * 
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getMacs(array $condition, $filter = null, $sort = null, $maxCount = null)
	{
		$table   = 'md_mac AS M';
		$columns = 'M.mac_id, M.mac_addr';
		$where   = array();
		
		if (!empty($condition['macaddr'])) {
			$where[] = 'M.mac_addr = ' . $this->_db->quote($condition['macaddr']);
		}
		
		if (isset($condition['orgid'])) {
			$table  .= 'md_user_mac AS UM ON M.mac_id = M.mac_id';
			$where[] = 'OM.org_id = ' . $this->_db->quote($condition['orgid']);
		}
		
		if (!$where) {
			return new Oray_Dao_Recordset();
		}
		
		$where = implode(' AND ', $where);
		
		$sql = "SELECT {$columns} FROM {$table} WHERE {$where}";
		
		$records = $this->_db->fecthAll($sql);
		
		return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Mac');
	}
	
	/**
	 * 是否存在mac地址
	 * 
	 * @param string $mac
	 */
    public function existsMac($mac)
    {
    	$sql = 'SELECT COUNT(0) FROM md_mac WHERE mac = ' . $this->_db->quote($mac);
    	
    	$count = (int) $this->_db->fetchOne($sql);
    	
    	return $count > 0;
    }
    
    /**
     * 创建mac地址记录
     * 
     * @param string $params
     * @return int
     */
    public function createMac($params)
    {
    	if (empty($params['mac'])) {
    		return false;
    	}
    	
    	$table = 'md_mac'; 
    	$bind  = array(
            'mac' => $params['mac']
    	);
    	
    	try {
    		$this->_db->insert($table, $bind);
    		$insertId = $this->_db->lastInsertId($table);
    	} catch (Zend_Db_Exception $e) {
    		return false;
    	}
    	
    	return $insertId;
    }
    
    /**
     * 删除mac地址记录
     * 
     * @param int $macId
     */
    public function deleteMac($macId)
    {
    	if (!is_int($macId) || $macId <= 0) {
    		return false;
    	}
    	
    	$sql = "DELETE FROM md_mac WHERE mac_id = {$macId}";
    	
    	try {
    		$this->_db->query($sql);
    	} catch (Zend_Db_Exception $e) {
    		return fasle;
    	}
    	
    	return true;
    }
    
    /**
     * 添加mac地址绑定
     * 
     * @param string $orgId
     * @param string $userId
     * @param string $macId
     * @return boolean
     */
    public function bind($orgId, $userId, $macId)
    {
        if (!$orgId || !$userId || !$macId) {
            return false;
        }
        
        $table = 'md_user_mac';
        $bind  = array(
            'org_id'  => $orgId,
            'user_id' => $userId,
            'mac_id'  => $macId
        );
        
        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 移除mac地址绑定
     * 
     * @param string $orgId
     * @param string $userId
     * @param string $macId
     * @return boolean
     */
    public function unbind($orgId, $userId, $macId)
    {
        if (!$orgId || !$userId || !$macId) {
            return false;
        }
        
        $sql = 'DELETE FROM md_user_mac WHERE org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId) . ' '
             . 'AND mac_id = ' . $this->_db->quote($macId);
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 更新绑定状态
     * 
     * @param string $orgId
     * @param string $userId
     * @param string $macId
     * @param int    $status
     * @return boolean
     */
    public function updateBindStatus($orgId, $userId, $macId, $status)
    {
        if (!$orgId || !$userId || $macId) {
            return false;
        }
        
        if (!is_int($status) || $status < 0) {
        	return false;
        }
        
        $table = 'md_user_mac';
        $bind  = array('status' => $status);
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId) . ' '
               . 'AND mac_id = ' . $this->_db->quote($macId);
        
        try {
        	$this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
        	return false;
        }
        
        return true;
    }
}