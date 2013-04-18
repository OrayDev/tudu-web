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
 * @version    $Id: Login.php 1031 2011-07-28 10:17:56Z cutecube $
 */
class Dao_Td_Log_Login extends Oray_Dao_Abstract
{
	/**
	 * 获取多条日志
	 * 
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getLoginLogs($condition, $filter = null, $sort = null, $maxCount = null)
	{
		$table   = 'td_login_log';
		$columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
				 . 'truename, ip, local, isp, `from`, client_info AS clientinfo, create_time AS createtime';
		$where   = array();
        $order   = array();
        $limit   = '';

		if (!empty($condition['loginlogid'])) {
        	$where[] = 'login_log_id = ' . $this->_db->quote($condition['loginlogid']);
        }

		if (!empty($condition['orgid'])) {
        	$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

		if (!empty($condition['uniqueid'])) {
        	$where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

		if (!empty($condition['address'])) {
        	$where[] = 'address = ' . $this->_db->quote($condition['address']);
        }

		if (!empty($condition['truename'])) {
        	$where[] = 'truename = ' . $this->_db->quote($condition['truename']);
        }

		if (!empty($condition['createtime'])) {
        	$where[] = 'create_time = ' . $this->_db->quote($condition['createtime']);
        }
        
	    if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
        	$where[] = 'create_time >= ' . $this->_db->quote($condition['starttime']) . ' AND create_time <= ' . $this->_db->quote($condition['endtime']);
        }

        if (!$where) {
        	return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
            	case 'createtime':
            		$key = 'create_time';
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

        $sql = "SELECT $columns FROM $table $where $order $limit";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Log_Record_Login');
	}
	
	
	/**
     * Get record page 前台登录日志列表
     * 
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getLoginLogPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'td_login_log';
		$columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
				 . 'truename, ip, local, isp, `from`, client_info AS clientinfo, create_time AS createtime';
		$primary  = 'create_time';
        $recordClass = "Dao_Td_Log_Record_Login";
		$where   = array();
        $order   = array();
        
        if (!empty($condition['orgid'])) {
        	$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

		if (!empty($condition['uniqueid'])) {
        	$where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

		if (!empty($condition['address'])) {
        	$where[] = 'address = ' . $this->_db->quote($condition['address']);
        }

		if (!empty($condition['truename'])) {
        	$where[] = 'truename = ' . $this->_db->quote($condition['truename']);
        }

        if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
            $where[] = 'create_time >= UNIX_TIMESTAMP(' . $this->_db->quote($condition['starttime']) . ') AND create_time <= UNIX_TIMESTAMP(' . $this->_db->quote($condition['endtime']) . ')';
        }
        
        if (!empty($condition['starttime']) && empty($condition['endtime'])) {
            $where[] = 'create_time >= UNIX_TIMESTAMP(' . $this->_db->quote($condition['starttime']) . ')';
        }
        
        if (empty($condition['starttime']) && !empty($condition['endtime'])) {
            $where[] = 'create_time <= UNIX_TIMESTAMP(' . $this->_db->quote($condition['endtime']) . ')';
        }
        
        if (!empty($condition['keywords'])) {
        	$keyword = $this->_db->quote('%'.$condition['keywords'].'%');
        	$where[] = "address LIKE {$keyword} OR truename LIKE {$keyword} OR ip LIKE {$keyword}";
        }
        
        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
            	case 'createtime':
                    $key = 'create_time';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }
        
        // ORDER
        $order = implode(', ', $order);

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        /**
         * @see Oray_Db_Paginator
         */
        require_once 'Oray/Db/Paginator.php';

        // 初始化分页器
        $paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER      => $this->_db,
            Oray_Db_Paginator::RECORD_CLASS => $recordClass,
            Oray_Db_Paginator::PAGE_SIZE    => $pageSize,
            Oray_Db_Paginator::TABLE        => $table,
            Oray_Db_Paginator::PRIMARY      => $primary,
            Oray_Db_Paginator::COLUMNS      => $columns,
            Oray_Db_Paginator::WHERE        => $where,
            Oray_Db_Paginator::ORDER        => $order
        ));

        // 返回查询结果
        return $paginator->query($page);
    }
	
	
	/**
     * 获取日志ID
     *
     * @return string
     */
    public static function getLoginLogId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}