<?php
/**
 * Attend_Mont
 * 签到月份统计
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Month.php 2735 2013-01-31 10:11:41Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Month extends Oray_Dao_Abstract
{
    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_App_Attend_Record_Month|NULL|boolean
     */
    public function getAttendMonth(array $condition, $filter = null)
    {
        if (empty($condition['uniqueid']) || empty($condition['date'])) {
            return false;
        }

        $table   = 'attend_month am';
        $columns = 'am.org_id AS orgid, am.unique_id AS uniqueid, '
                 . 'am.date, am.late, am.leave, am.unwork, am.update_time AS updatetime';
        $where   = array();
        $bind    = array(
            'uniqueid' => $condition['uniqueid'],
            'date'     => $condition['date']
        );

        $where[] = 'am.unique_id = :uniqueid';
        $where[] = 'am.date = :date';

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where} LIMIT 0, 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_App_Attend_Record_Month', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getAttendMonthList(array $condition, $sort = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_month am';
        $columns = 'am.org_id AS orgid, am.unique_id AS uniqueid, '
                 . 'am.date, am.late, am.leave, am.unwork, am.is_abnormal_ip AS isabnormalip, am.update_time AS updatetime';
        $where   = array();
        $order   = array();
        $bind    = array();
        $limit   = '';

        if (isset($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                $where[] = 'am.unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 'am.unique_id = ?';
                $bind[] = $condition['uniqueid'];
            }
        }

        if (isset($condition['orgid'])) {
            $where[] = 'am.org_id = ?';
            $bind[] = $condition['orgid'];
        }

        if (isset($condition['date'])) {
            $where[] = 'am.date = ?';
            $bind[] = $condition['date'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                default:
                    continue 2;
                    break;
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

        $sql = "SELECT $columns FROM $table $where $order $limit";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Record_Month');
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
    public function create(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['date']))
        {
            return false;
        }

        $table = 'attend_month';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'unique_id'   => $params['uniqueid'],
            'date'        => (int) $params['date'],
            'late'        => empty($params['late']) ? 0 : (int) $params['late'],
            'leave'       => empty($params['leave']) ? 0 : (int) $params['leave'],
            'unwork'      => empty($params['unwork']) ? 0 : (int) $params['unwork'],
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

        if (isset($params['isabnormalip'])) {
            $bind['is_abnormal_ip'] = $params['isabnormalip'];
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
     * @param string $uniqueId
     * @param string $date
     * @param array  $params
     * @return boolean
     */
    public function update($uniqueId, $date, array $params)
    {
        if (empty($uniqueId) || empty($date)) {
            return false;
        }

        $table = 'attend_month';
        $bind  = array();

        if (isset($params['late']) && is_int($params['late'])) {
            $bind['late'] = $params['late'];
        }

        if (isset($params['leave']) && is_int($params['leave'])) {
            $bind['leave'] = $params['leave'];
        }

        if (isset($params['unwork']) && is_int($params['unwork'])) {
            $bind['unwork'] = $params['unwork'];
        }

        if (isset($params['isabnormalip'])) {
            $bind['is_abnormal_ip'] = $params['isabnormalip'];
        }

        if (isset($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
        }

        try {
            $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' AND date = ' . $this->_db->quote($date);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 读取记录是否存在
     *
     * @param string $uniqueId
     * @param string $date
     * @return boolean
     */
    public function existsRecord($uniqueId, $date)
    {
        if (empty($uniqueId) || empty($date)) {
            return false;
        }

        $table = 'attend_month';
        $bind  = array(
            'uniqueid' => $uniqueId,
            'date'     => $date
        );

        $sql = "SELECT COUNT(0) FROM {$table} WHERE unique_id = :uniqueid AND date = :date";

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }
}