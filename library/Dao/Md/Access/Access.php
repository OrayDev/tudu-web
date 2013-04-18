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
 * @version    $Id: Access.php 806 2011-05-19 01:07:31Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Access_Access extends Oray_Dao_Abstract
{
    /**
     * 
     * @var string
     */
	const VALUETYPE_BOOLEAN = 'B';
	const VALUETYPE_INTEGER = 'I';
	
	/**
	 * 获取权限列表
	 * 
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
    public function getAccesses(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
    	$table   = 'md_access';
    	$columns = 'access_id AS accessid, access_name AS accessname, value_type AS valuetype, form_type AS formtype, default_value AS defaultvalue';
    	$where   = array();
    	$order   = array();
    	
    	$where = implode(' AND ', $where);
    	
    	// WHERE
    	if ($where) {
            $where = ' WHERE ' . $where;
    	}
    	
    	$order = implode(', ', $order);
    	
    	// ORDER
    	if ($order) {
            $order = 'ORDER BY ' . $order;
    	}
    	
    	// SQL
    	$sql = "SELECT {$columns} FROM {$table} {$where} {$order}";
    	
    	$records = $this->_db->fetchAll($sql);
    	
        $records = $this->_formatRecords($records);
    	
    	return new Oray_Dao_Recordset($records, 'Dao_Md_Access_Record_Access');
    }
    
    /**
     * 获取用户访问权限列表
     * 
     * @param string $orgId
     * @param string $userId
     * @return array
     */
    public function getUserAccess($orgId, $userId)
    {
    	if (!$orgId || !$userId) {
    		return array();
    	}
    	
    	$orgId  = $this->_db->quote($orgId);
    	$userId = $this->_db->quote($userId);
    	
    	$sql = 'SELECT * FROM (('
    	     . 'SELECT RA.access_id AS accessid, RA.value, RA.role_id AS roleid, A.value_type AS valuetype FROM md_role_access AS RA '
    	     . 'LEFT JOIN md_user_role AS UR ON RA.org_id = UR.org_id AND RA.role_id = UR.role_id '
    	     . 'LEFT JOIN md_access AS A ON RA.access_id = A.access_id '
    	     . "WHERE RA.org_id = {$orgId} AND UR.user_id = {$userId}"
    	     . ') UNION ALL ('
    	     . 'SELECT UA.access_id AS accessid, UA.value, null AS groupid, A.value_type AS valuetype FROM md_user_access AS UA '
    	     . 'LEFT JOIN md_access AS A ON UA.access_id = A.access_id '
    	     . "WHERE org_id = {$orgId} AND user_id = {$userId} "
    	     . ')) AS T';
    	
    	$records = $this->_db->fetchAll($sql);
    	
    	$records = $this->_formatRecords($records);
    	
    	return $records;
    }
    
    /**
     * 
     * @param string $orgId
     * @param string $groupId
     * @return array
     */
    public function getGroupAccess($orgId, $groupId)
    {
    	if (!$orgId || !$groupId) {
    		return array();
    	}
    	
    	$sql = 'SELECT RA.access_id AS accessid, RA.value, RA.group_id AS groupid, A.value_type AS valuetype '
    	     . 'FROM md_role_access AS RA '
    	     . 'LEFT JOIN md_access AS A ON RA.access_id = A.access_id '
    	     . 'WHERE RA.org_id = ' . $this->_db->quote($orgId) . ' '
    	     . 'AND RA.group_id = ' . $this->_db->quote($groupId);
    	
    	$records = $this->_db->fetchAll($sql);
    	
    	$records = $this->_formatRecords($records);
    	
    	return $records;
    }
    
    /**
     * 格式化值
     * 
     * @param mixed  $value
     * @param string $valueType
     * @return mixed
     */
    private function _formatValue($value, $valueType)
    {
        switch ($valueType) {
            case self::VALUETYPE_BOOLEAN:
                $value = (boolean) $value;
                break;
            case self::VALUETYPE_INTEGER:
                $value = (int) $value;
                break;
        }
        
        return $value;
    }
    
    /**
     * 
     * @param array $records
     * @return array 
     */
    private function _formatRecords(array $records)
    {
        foreach ($records as &$item) {
        	if (isset($item['value'])) {
                $item['value'] = $this->_formatValue($item['value'], $item['valuetype']);
        	}
        	
            if (isset($item['defaultvalue'])) {
                $item['defaultvalue'] = $this->_formatValue($item['defaultvalue'], $item['valuetype']);
            }
        }
        
        return $records;
    }
}