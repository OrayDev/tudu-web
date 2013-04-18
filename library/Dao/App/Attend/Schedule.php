<?php
/**
 * 排班方案
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Schedule.php 2775 2013-03-13 09:55:13Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule extends Oray_Dao_Abstract
{

    /**
     * 获取指定排班方案数据
     *
     * @param array $condition
     * @param mixed $filter
     * @return Dao_App_Attend_Record_Schedule
     */
    public function getSchedule(array $condition, $filter = null)
    {
        $table   = 'attend_schedule s '
                 . 'LEFT JOIN attend_schedule_rule r ON s.schedule_id = r.schedule_id AND s.org_id = r.org_id';
        $columns = 's.org_id AS orgid, s.unique_id AS uniqueid, s.schedule_id AS scheduleid, s.name, s.is_system AS issystem, r.rule_id AS ruleid, r.week, '
                 . 's.bgcolor, r.checkin_time AS checkintime, r.checkout_time AS checkouttime, '
                 . 'r.late_standard AS latestandard, r.late_checkin AS latecheckin, '
                 . 'r.leave_checkout AS leavecheckout, r.status, s.create_time AS createtime';
        $where   = array();
        $bind    = array();

        $recordClass = 'Dao_App_Attend_Record_Schedule';

        if (!empty($condition['orgid'])) {
            $where[] = 's.org_id = :orgid';
            $bind['orgid']  = $condition['orgid'];
        }

        if (!empty($condition['scheduleid'])) {
            $where[] = 's.schedule_id = :scheduleid';
            $bind['scheduleid']  = $condition['scheduleid'];
        }

        if (isset($condition['week'])) {
            $where[] = 'r.week = :week';
            $bind['week']  = $condition['week'];
        }

        if (isset($condition['status'])) {
            $where[] = 'r.status = :status';
            $bind['status']  = $condition['status'];
        }

        if (!empty($filter) && array_key_exists('isvalid', $filter)) {
            if (null !== $filter['isvalid']) {
                $where[] = 's.is_valid = ' . (int) $filter['isvalid'];
            }
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where}";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record($recordClass, $record);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     */
    public function getScheduleRules(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule_rule';
        $columns = 'rule_id AS ruleid, week, checkin_time AS checkintime, checkout_time AS checkouttime, status';
        $where   = array();
        $bind    = array();

        $recordClass = 'Dao_App_Attend_Record_Rule';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid']  = $condition['orgid'];
        }

        if (!empty($condition['scheduleid'])) {
            $where[] = 'schedule_id = :scheduleid';
            $bind['scheduleid']  = $condition['scheduleid'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取排班方案列表
     *
     * @param array $condition
     * @param mixed $filter
     * @param mixed $sort
     * @param mixed $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getSchedules(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule s '
                 . 'LEFT JOIN attend_schedule_rule r ON s.schedule_id = r.schedule_id AND s.org_id = r.org_id';
        $columns = 's.org_id AS orgid, s.unique_id AS uniqueid, s.schedule_id AS scheduleid, s.name, s.is_system AS issystem, '
                 . 's.bgcolor, r.rule_id AS ruleid, r.checkin_time AS checkintime, r.checkout_time AS checkouttime, '
                 . 'r.late_standard AS latestandard, r.late_checkin AS latecheckin, '
                 . 'r.leave_checkout AS leavecheckout, r.status, s.create_time AS createtime';
        $where   = array();
        $order   = array();
        $bind    = array();
        $limit   = '';

        $recordClass = 'Dao_App_Attend_Record_Schedule';

        if (!empty($condition['orgid'])) {
            $where[] = 's.org_id = ?';
            $bind[]  = $condition['orgid'];
        }

        if (!empty($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                $where[] = 's.unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 's.unique_id = ?';
                $bind[] = $condition['uniqueid'];
            }
        }

        if (!empty($filter)) {
            // 是否系统排班方案
            if (array_key_exists('issystem', $filter) && null !== $filter['issystem']) {
                $where[] = 's.is_system = ?';
                $bind[] = (int) $filter['issystem'];
            }

            if (array_key_exists('isvalid', $filter) && null !== $filter['isvalid']) {
                $where[] = 's.is_valid = ?';
                $bind[] = (int) $filter['isvalid'];
            }
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
                case 'createtime':
                    $key = 's.create_time';
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
     * 是否该颜色已有使用
     *
     * @param string $orgId
     * @param string $color
     * @param string $scheduleId
     */
    public function existsBgcolor($orgId, $color, $scheduleId = null)
    {
        if (empty($orgId) || empty($color)) {
            return false;
        }

        $sql   = 'SELECT COUNT(0) FROM attend_schedule WHERE org_id = :orgid AND bgcolor = :bgcolor AND is_valid = 1';
        $bind  =  array('orgid' => $orgId, 'bgcolor' => $color);

        if (!empty($scheduleId)) {
            $sql  .= ' AND schedule_id <> :id';
            $bind['id'] = $scheduleId;
        }

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 判断记录是否存在
     */
    public function countSchedule($orgId)
    {
        if (empty($orgId)) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM attend_schedule WHERE org_id = :orgid AND is_valid = 1';

        try {
            $bind  =  array('orgid' => $orgId);
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 判断记录是否存在
     */
    public function existsRule($orgId, $scheduleId, $week)
    {
        if (empty($orgId) || empty($scheduleId)) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM attend_schedule_rule WHERE org_id = :orgid AND schedule_id = :scheduleid AND week = :week';

        try {
            $bind  =  array('orgid' => $orgId, 'scheduleid' => $scheduleId, 'week' => $week);
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建排班方案
     *
     * @param array $params
     * @return string|boolean
     */
    public function createSchedule(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['scheduleid'])
            || empty($params['uniqueid'])
            || empty($params['name']))
        {
            return false;
        }

        $table = 'attend_schedule';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'schedule_id' => $params['scheduleid'],
            'unique_id'   => $params['uniqueid'],
            'name'        => $params['name'],
            'create_time' => !empty($params['createtime']) ? (int) $params['createtime'] : time()
        );

        if (isset($params['bgcolor'])) {
            $bind['bgcolor'] = $params['bgcolor'];
        }

        if (isset($params['issystem'])) {
            $bind['is_system'] = (int) $params['issystem'];
        }

        if (isset($params['isvalid'])) {
            $bind['isvalid'] = (int) $params['isvalid'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['scheduleid'];
    }

    /**
     * 更新排班方案
     *
     * @param string $orgId
     * @param string $scheduleId
     * @param array  $params
     * @return boolean
     */
    public function updateSchedule($orgId, $scheduleId, array $params)
    {
        if (empty($orgId) || empty($scheduleId)) {
            return false;
        }

        $table = 'attend_schedule';
        $bind  = array();

        if (isset($params['name'])) {
            $bind['name'] = $params['name'];
        }

        if (isset($params['bgcolor'])) {
            $bind['bgcolor'] = $params['bgcolor'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = $params['isvalid'];
        }

        try {
            if (!empty($bind)) {
                $where = 'schedule_id = ' . $this->_db->quote($scheduleId) . ' AND org_id = ' . $this->_db->quote($orgId);

                $this->_db->update($table, $bind, $where);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 创建排班规则
     *
     * @param array $params
     * @return string|boolean
     */
    public function createScheduleRule(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['scheduleid'])
            || empty($params['ruleid']))
        {
            return false;
        }

        $table = 'attend_schedule_rule';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'schedule_id' => $params['scheduleid'],
            'rule_id'     => $params['ruleid'],
            'create_time' => !empty($params['createtime']) ? (int) $params['createtime'] : time()
        );

        if (isset($params['week'])) {
            $bind['week'] = (int) $params['week'];
        }

        if (isset($params['checkintime'])) {
            $bind['checkin_time'] = (int) $params['checkintime'];
        }

        if (isset($params['checkouttime'])) {
            $bind['checkout_time'] = (int) $params['checkouttime'];
        }

        if (isset($params['latestandard'])) {
            $bind['late_standard'] = (int) $params['latestandard'];
        }

        if (isset($params['latecheckin'])) {
            $bind['late_checkin'] = (int) $params['latecheckin'];
        }

        if (isset($params['leavecheckout'])) {
            $bind['leave_checkout'] = (int) $params['leavecheckout'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['ruleid'];
    }

    /**
     * 更新排班规则
     *
     * @param string $orgId
     * @param string $scheduleId
     * @param string $ruleId
     * @param array  $params
     * @return boolean
     */
    public function updateScheduleRule($orgId, $scheduleId, $ruleId, array $params)
    {
        if (empty($orgId) || empty($scheduleId) || empty($ruleId)) {
            return false;
        }

        $table = 'attend_schedule_rule';
        $bind  = array();

        if (isset($params['week'])) {
            $bind['week'] = (int) $params['week'];
        }

        if (array_key_exists('checkintime', $params)) {
            $bind['checkin_time'] = $params['checkintime'];
        }

        if (array_key_exists('checkouttime', $params)) {
            $bind['checkout_time'] = $params['checkouttime'];
        }

        if (array_key_exists('latestandard', $params)) {
            $bind['late_standard'] = $params['latestandard'];
        }

        if (array_key_exists('latecheckin', $params)) {
            $bind['late_checkin'] = $params['latecheckin'];
        }

        if (array_key_exists('leavecheckout', $params)) {
            $bind['leave_checkout'] = $params['leavecheckout'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        try {
            $where = 'schedule_id = ' . $this->_db->quote($scheduleId) . ' AND org_id = ' . $this->_db->quote($orgId) . ' AND rule_id = ' . $this->_db->quote($ruleId);
            if (!empty($bind)) {
                $this->_db->update($table, $bind, $where);
            }
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 清除排班方案规则状态
     *
     * @param string $orgId
     * @param string $scheduleId
     * @return boolean
     */
    public function clearAllStatus($orgId, $scheduleId)
    {
        if (empty($orgId) || empty($scheduleId)) {
            return false;
        }

        $bind = array('orgid' => $orgId, 'scheduleid' => $scheduleId);
        $sql  = 'UPDATE attend_schedule_rule SET status = 0 WHERE org_id = :orgid AND schedule_id = :scheduleid';

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除排班方案
     */
    public function deleteSchedule($orgId, $scheduleId, $isValid = false)
    {
        if (empty($orgId) || empty($scheduleId)) {
            return false;
        }

        $sqls = array();
        $bind = array('orgid' => $orgId, 'scheduleid' => $scheduleId);
        if ($isValid) {
            $sqls[] = 'UPDATE attend_schedule SET is_valid = 0 WHERE org_id = :orgid AND schedule_id = :scheduleid';
        } else {
            $sqls[] = 'DELETE FROM attend_schedule_rule WHERE org_id = :orgid AND schedule_id = :scheduleid';
            $sqls[] = 'DELETE FROM attend_schedule WHERE org_id = :orgid AND schedule_id = :scheduleid';
        }

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql, $bind);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 格式化时间
     *
     * @param int $time
     * @return int|string
     */
    public static function formatTime($time, $isFormat = false)
    {
        if (null == $time) {
            return null;
        }
        if ($time == 0) {
            return '00:00';
        }
        if (null !== $time && !is_int($time)) {
            $time = (int) $time;
        }

        //除去整天之后剩余的时间
        $time = $time%(3600*24);
        // 小时
        $hour = floor($time/3600);
        //除去整小时之后剩余的时间
        $time = $time%3600;
        // 分钟
        $minute = floor($time/60);
        // 秒
        //$second = $time%60;
        if ($isFormat) {
            $minute = number_format($minute/60, 2, '.', '');
            $t = $hour + $minute;

            return number_format($t, 1, '.', '');
        }

        //返回字符串
        return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minute, 2, '0', STR_PAD_LEFT);
    }


    /**
     * 获取排班方案ID
     *
     * @return string
     */
    public static function getScheduleId()
    {
        return base_convert(strrev(microtime(true)) . rand(0, 999), 10, 32);
    }

    /**
     * 获取排班设置规则ID
     *
     * @return string
     */
    public static function getRuleId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}