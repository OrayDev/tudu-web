<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md_Log_AdminLog
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Oplog.php 2182 2012-09-29 06:22:43Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md_Log_AdminLog
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Log_Oplog extends Oray_Dao_Abstract
{

	/**
	 * 模块
	 *
	 * @var string
	 */
	const MODULE_USER  = 'user';
	const MODULE_DEPT  = 'dept';
	const MODULE_GROUP = 'group';
	const MODULE_CAST  = 'cast';
	const MODULE_BOARD = 'board';
	const MODULE_LOGIN = 'login';

	/**
	 * 操作
	 *
	 * @var string
	 */
	const OPERATION_CREATE = 'create';
	const OPERATION_UPDATE = 'update';
	const OPERATION_DELETE = 'delete';

	const OPERATION_LOGIN  = 'login';
	const OPERATION_LOGOUT = 'logout';
    /**
     * Get record
     *
     * SQL here..
     *
     * @param array $condition
     * @param array $filter
     * @return Oray_Dao_Record
     */
    public function getAdminLog(array $condition, $filter = null)
    {
        $table   = 'md_op_log';
        $columns = 'org_id AS orgid, user_id AS userid, module, action, sub_action AS subaction, '
                 . 'description, ip, local, create_time AS createtime';
        $where   = array();

        if (empty($where)) {
            return null;
        }

        if (!empty($filter['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($filter['orgid']);
        }

        // WHERE
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_Log_Record_Oplog', $record);
    }

    /**
     * Get records
     *
     * SQL here..
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getAdminLogs(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_op_log';
        $columns = 'org_id AS orgid, user_id AS userid, module, action, sub_action AS subaction, '
                 . 'target, ip, local, create_time AS createtime';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['userid'])) {
        	$where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['module'])) {
        	$where[] = 'module = ' . $this->_db->quote($condition['module']);
        }

        if (!empty($condition['operation'])) {
        	$where[] = 'operation = ' . $this->_db->quote($condition['operation']);
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

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

        return new Oray_Dao_Recordset($records, 'Oray_Dao_Record');
    }

    /**
     * Get record page
     *
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getAdminLogPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'md_op_log';
        $columns = 'org_id AS orgid, user_id AS userid, module, action, sub_action AS subaction, '
                 . 'target, ip, local, create_time AS createtime, detail';
        $primary  = 'create_time';
        $recordClass = "Dao_Md_Log_Record_Oplog";
        $where = array();
        $order = array();

        if (!empty($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['module'])) {
            $where[] = 'module = ' . $this->_db->quote($condition['module']);
        }

        /*if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
            $where[] = 'create_time >= UNIX_TIMESTAMP(' . $this->_db->quote($condition['starttime']) . ') AND create_time <= UNIX_TIMESTAMP(' . $this->_db->quote($condition['endtime']) . ')';
        }

        if (!empty($condition['starttime']) && empty($condition['endtime'])) {
            $where[] = 'create_time >= UNIX_TIMESTAMP(' . $this->_db->quote($condition['starttime']) . ')';
        }

        if (empty($condition['starttime']) && !empty($condition['endtime'])) {
            $where[] = 'create_time <= UNIX_TIMESTAMP(' . $this->_db->quote($condition['endtime']) . ')';
        }*/
        if (isset($condition['createtime'])) {
            if (is_int($condition['createtime'])) {
                $where[] = 'create_time = ' . $condition['createtime'];
            } elseif (is_array($condition['createtime'])) {
                $arr = $condition['createtime'];
                if (isset($arr[0]) && is_int($arr[0])) {
                    $where[] = 'create_time >= ' . $arr[0];
                }

                if (isset($arr[1]) && is_int($arr[1])) {
                    $where[] = 'create_time <=' . $arr[1];
                }
            }
        }

        if (!empty($condition['keywords']) && Oray_Function::isByte($condition['keywords'])) {
        	$keyword = $this->_db->quote('%'.$condition['keywords'].'%');
        	$where[] = "(user_id LIKE {$keyword} OR ip LIKE {$keyword})";
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
     * Create record
     *
     * @param $params
     * @return int|false
     */
    public function createAdminLog(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['module'])
            || empty($params['action']))
        {
            return false;
        }

        $table = 'md_op_log';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'user_id'     => $params['userid'],
            'module'      => $params['module'],
            'action'      => $params['action'],
            'target'      => $params['target'],
            'create_time' => time()
        );

        if (!empty($params['subaction'])) {
        	$bind['sub_action'] = $params['subaction'];
        }

        if (!empty($params['ip'])) {
        	$bind['ip'] = $params['ip'];
        }

        if (!empty($params['local'])) {
            $bind['local'] = $params['local'];
        }

        if (!empty($params['detail'])) {
			$bind['detail'] = $params['detail'];
		}

        try {
            $this->_db->insert($table, $bind);
            $insertId = (int) $this->_db->lastInsertId();
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $insertId;
    }
}