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
class Dao_Td_Log_Log extends Oray_Dao_Abstract
{
	/**
	 * 对象类型
	 * @var string
	 */
    const TYPE_TUDU  = 'tudu';
    const TYPE_BOARD = 'board';
    const TYPE_POST  = 'post';

    /**
     * 公用操作类型
     * @var string
     */
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_CLOSE  = 'close';
    const ACTION_OPEN   = 'open';

    /**
     * 图度操作
     * @var string
     */
    const ACTION_TUDU_SEND     = 'send';    // 发送
    const ACTION_TUDU_DONE     = 'done';    // 确认
    const ACTION_TUDU_DIVIDE   = 'divide';  // 分工
    const ACTION_TUDU_UNDONE   = 'undone';  // 取消确认
    const ACTION_TUDU_PROGRESS = 'progress';// 更新进度
    const ACTION_TUDU_ACCEPT   = 'accept';  // 接受
    const ACTION_TUDU_CANCEL   = 'cancel';  // 取消
    const ACTION_TUDU_DECLINE  = 'decline'; // 拒绝
    const ACTION_TUDU_FORWARD  = 'forward'; // 转发
    const ACTION_TUDU_LABEL    = 'label';   // 标签操作
    const ACTION_TUDU_REVIEW   = 'review';  // 申请审批
    const ACTION_TUDU_EXAMINE  = 'examine'; // 审批操作
    const ACTION_TUDU_CLAIM    = 'claim';   // 认领操作

	/**
	 * 创建日志
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function createLog(array $params)
	{
		if (empty($params['orgid'])
		    || empty($params['targettype'])
		    || empty($params['targetid'])
		    || empty($params['uniqueid'])) {
            return false;
		}

		$table = 'td_log';
		$bind  = array(
            'org_id' => $params['orgid'],
            'target_type' => $params['targettype'],
            'target_id' => $params['targetid'],
            'unique_id' => $params['uniqueid'],
		);

		if (!empty($params['operator'])) {
			$bind['operator'] = $params['operator'];
		}

		if (!empty($params['action'])) {
			$bind['action'] = $params['action'];
		}

		if (!empty($params['detail'])) {
			$bind['detail'] = $params['detail'];
		}

		if (isset($params['logtime']) && is_int($params['logtime'])) {
			$bind['log_time'] = $params['logtime'];
		}
        if (isset($params['privacy'])) {
            $bind['privacy'] = ($params['privacy'] ? 1 : 0);
        }

		try {
			$this->_db->insert($table, $bind);
			$logId = $this->_db->lastInsertId($table);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		return $logId;
	}

	/**
	 * 获取日志列表
	 *
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getLogs($condition, $filter = null, $sort = null, $maxCount = null)
	{
        $table   = 'td_log';
        $columns = 'log_time AS logtime, org_id AS orgid, target_type AS targettype, target_id AS targetid, '
                 . 'operator, unique_id AS uniqueid, privacy, action, detail';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['orgid'])) {
        	$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['targettype'])) {
        	$where[] = 'target_type = ' . $this->_db->quote($condition['targettype']);
        }

        if (!empty($condition['targetid'])) {
        	$where[] = 'target_id = ' . $this->_db->quote($condition['targetid']);
        }

        if (!empty($condition['action'])) {
        	$where[] = 'action = ' . $this->_db->quote($condition['action']);
        }

        if (isset($condition['privacy'])) {
        	$where[] = 'privacy = ' . ($condition['privacy'] ? 1 : 0);
        }

        if (!$where) {
        	return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
            	case 'logtime':
            		$key = 'log_time';
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

        return new Oray_Dao_Recordset($records, 'Dao_Td_Log_Record_Log');
	}
}