<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Class.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Class extends Oray_Dao_Abstract
{
	
	/**
	 * 
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getClasses(array $condition, $filter = null, $sort = null, $maxCount = null)
	{
        $table   = 'td_class';
        $columns = 'org_id AS orgid, board_id AS boardid, class_id as classid, class_name AS classname, order_num AS ordernum';
        $where   = array();
        $order   = array();
        $limit   = '';
        
        if (!empty($condition['orgid'])) {
        	$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (!empty($condition['boardid'])) {
        	$where[] = 'board_id = ' . $this->_db->quote($condition['boardid']);
        }
        
        if (!$where) {
        	return new Oray_Dao_Recordset();
        }
        
        // WHERE
        $where = implode(' AND ', $where);
        
        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
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
        
        if (is_int($maxCount) && $maxCount > 0) {
        	$limit = 'LIMIT ' . $maxCount;
        }
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";
        
        $records = $this->_db->fetchAll($sql);
        
        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Class');
	}
	
	/**
	 * 
	 * @param string $orgId
	 * @param string $boardId
	 * @param mixed  $sort
	 * @param int    $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getClassesByBoardId($orgId, $boardId, $sort = null, $maxCount = null)
	{
		return $this->getClasses(array(
	       'orgid'   => $orgId,
	       'boardid' => $boardId
		), null, $sort, $maxCount);
	}
	
	/**
	 * 
	 * @param array $params
	 * @return boolean
	 */
	public function createClass(array $params)
	{
		if (empty($params['orgid'])
		    || empty($params['boardid'])
		    || empty($params['classid'])
		    || empty($params['classname']))
		{
			return false;
		}
		
		$table = 'td_class';
		$bind  = array(
            'org_id'     => $params['orgid'],
            'board_id'   => $params['boardid'],
            'class_id'   => $params['classid'],
            'class_name' => $params['classname']
		);
		
		if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$bind['order_num'] = $params['ordernum'];
		}
		
		try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			$this->_catchExecption($e, __METHOD__);
			return false;
		}
		
		return $params['classid'];
	}
	
	/**
	 * 更新分类信息
	 * 
	 * @param string $orgId
	 * @param string $boardId
	 * @param string $classId
	 * @param array  $params
	 */
	public function updateClass($orgId, $classId, array $params)
	{
		if (empty($orgId) || empty($classId)) {
            return false;
        }
        
        $table = 'td_class';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND class_id = ' . $this->_db->quote($classId);
        
        if (!empty($params['classname'])) {
        	$bind['class_name'] = $params['classname'];
        }
        
        if (!empty($params['boardid'])) {
        	$bind['board_id'] = $params['boardid'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }
        
        if (!$bind) {
        	return false;
        }
        
        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return false;
        }
        
        return true;
	}
	
	/**
	 * 删除分类
	 * 
	 * @param string $orgId
	 * @param string $classId
	 * @return boolean
	 */
	public function deleteClass($orgId, $classId)
	{
        if (empty($orgId) || empty($classId)) {
            return false;
        }
        
        $sql = 'DELETE FROM td_class WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'class_id = ' . $this->_db->quote($classId);
        
        try {
        	$this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
        	$this->_catchException($e, __METHOD__);
        	return false;
        }
        
        return true;
	}
	
    /**
     * 获取分类ID
     * 
     * 生成规则：微秒级时间戳转16位 + 0xfffff最大值随机数转16位
     * 格式如 129fcd77e2043a86，类似gmail生成格式
     * 
     * @return string
     */
    public static function getClassId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}