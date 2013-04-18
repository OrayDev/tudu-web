<?php
/**
 * 排班计划数据操作
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Plan.php 2734 2013-01-31 08:07:34Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Plan extends Oray_Dao_Abstract
{

    /**
     * 获取考勤计划信息
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getPlanList(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule_plan_month';
        $columns = 'org_id AS orgid, date, memo, update_time AS updatetime';
        $where   = array();
        $bind    = array();
        $order   = array();
        $limit   = '';

        $recordClass = 'Dao_App_Attend_Schedule_Record_Plan';

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ?';
            $bind[] = $condition['orgid'];
        }

        if (isset($condition['uniqueid'])) {
            $columns .= ', unique_id AS uniqueid';

            if (is_array($condition['uniqueid'])) {
                $where[] = 'unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 'unique_id = ?';
                $bind[] = $condition['uniqueid'];
            }
        }

        if (isset($condition['date'])) {
            $where[] = 'date = ?';
            $bind[]  = $condition['date'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'updatetime':
                    $key = 'update_time';
                    break;
                default:
                    continue 2;
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

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getMonthPlan(array $condition, $filter = null)
    {
        if (empty($condition['uniqueid']) || empty($condition['date'])) {
            return null;
        }

        $table   = 'attend_schedule_plan_month';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, date, plan';
        $where   = 'unique_id = ? AND date = ?';
        $bind    = array($condition['uniqueid'], $condition['date']);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return  Oray_Dao::record('Dao_App_Attend_Schedule_Record_PlanMonth', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getMonthPlans(array $condition, $filter = null)
    {
        $table   = 'attend_schedule_plan_month';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, date, plan';
        $where   = array();
        $bind    = array();

        if (isset($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                $where[] = 'unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 'unique_id = ?';
                $bind[]  = $condition['uniqueid'];
            }
        }

        if (isset($condition['date'])) {
            $where[] = 'date = ?';
            $bind[]  = $condition['date'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Schedule_Record_PlanMonth');

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     *
     * @param array $params
     * @return bool
     */
    public function createMonthPlan(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['date'])
            || empty($params['plan']))
        {
            return false;
        }

        $table = 'attend_schedule_plan_month';
        $bind  = array(
            'org_id'    => $params['orgid'],
            'unique_id' => $params['uniqueid'],
            'plan'      => $params['plan'],
            'date'      => $params['date']
        );

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
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
     * @param string $unqiueId
     * @param string $date
     * @param array  $params
     * @return boolean
     */
    public function updateMonthPlan($unqiueId, $date, array $params)
    {
        if (empty($unqiueId) || empty($date)) {
            return false;
        }

        $table = 'attend_schedule_plan_month';
        $bind  = array();

        if (isset($params['plan'])) {
            $bind['plan'] = $params['plan'];
        }

        if (array_key_exists('memo', $params)) {
            $bind['memo'] = $params['memo'];
        }

        if (isset($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
        }

        try {
            $where = 'unique_id = ' . $this->_db->quote($unqiueId) . ' AND date = ' . $this->_db->quote($date);
            if (!empty($bind)) {
                $this->_db->update($table, $bind, $where);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $unqiueId
     * @param string $date
     * @return boolean
     */
    public function existsMonthPlan($unqiueId, $date)
    {
        if (empty($unqiueId) || empty($date)) {
            return false;
        }

        $sql   = 'SELECT COUNT(0) FROM attend_schedule_plan_month WHERE unique_id = :uniqueid AND date = :date';
        $bind  =  array('uniqueid' => $unqiueId, 'date' => $date);

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function updatePlanForMonth(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['date'])
            || empty($params['plan']))
        {
            return false;
        }

        $isUpdate = $this->existsMonthPlan($params['uniqueid'], $params['date']);
        if ($isUpdate) {
            return $this->updateMonthPlan($params['uniqueid'], $params['date'], $params);
        } else {
            return $this->createMonthPlan($params);
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getWeekPlan(array $condition, $filter = null)
    {
        if (empty($condition['uniqueid'])) {
            return null;
        }

        $table   = 'attend_schedule_plan_week';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, plan, cycle_num AS cyclenum, memo, effect_date AS effectdate';
        $where   = 'unique_id = ?';
        $bind    = array($condition['uniqueid']);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return  Oray_Dao::record('Dao_App_Attend_Schedule_Record_PlanWeek', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getWeekPlans(array $condition, $filter = null)
    {
        $table   = 'attend_schedule_plan_week';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, plan, cycle_num AS cyclenum, effect_date AS effectdate';
        $where   = array();
        $bind    = array();

        if (isset($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                $where[] = 'unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 'unique_id = ?';
                $bind[]  = $condition['uniqueid'];
            }
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Schedule_Record_PlanWeek');

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
    public function createWeekPlan(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['plan']))
        {
            return false;
        }

        $table = 'attend_schedule_plan_week';
        $bind  = array(
            'org_id'    => $params['orgid'],
            'unique_id' => $params['uniqueid'],
            'plan'      => $params['plan']
        );

        if (!empty($params['cyclenum'])) {
            $bind['cycle_num'] = $params['cyclenum'];
        }

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['effectdate'])) {
            $bind['effect_date'] = $params['effectdate'];
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
     * @param string $unqiueId
     * @param array  $params
     * @return boolean
     */
    public function updateWeekPlan($unqiueId, array $params)
    {
        if (empty($unqiueId)) {
            return false;
        }

        $table = 'attend_schedule_plan_week';
        $bind  = array();

        if (isset($params['plan'])) {
            $bind['plan'] = $params['plan'];
        }

        if (isset($params['cyclenum'])) {
            $bind['cycle_num'] = $params['cyclenum'];
        }

        if (array_key_exists('memo', $params)) {
            $bind['memo'] = $params['memo'];
        }

        if (isset($params['effectdate'])) {
            $bind['effect_date'] = $params['effectdate'];
        }

        try {
            $where = 'unique_id = ' . $this->_db->quote($unqiueId);
            if (!empty($bind)) {
                $this->_db->update($table, $bind, $where);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $unqiueId
     * @return boolean
     */
    public function existsWeekPlan($unqiueId)
    {
        if (empty($unqiueId)) {
            return false;
        }

        $sql   = 'SELECT COUNT(0) FROM attend_schedule_plan_week WHERE unique_id = :uniqueid';
        $bind  =  array('uniqueid' => $unqiueId);

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function updatePlanForWeek(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['plan']))
        {
            return false;
        }

        $isUpdate = $this->existsWeekPlan($params['uniqueid']);
        if ($isUpdate) {
            return $this->updateWeekPlan($params['uniqueid'], $params);
        } else {
            return $this->createWeekPlan($params);
        }
    }
}