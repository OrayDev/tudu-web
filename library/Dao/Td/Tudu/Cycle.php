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
 * @version    $Id: Cycle.php 2288 2012-10-25 09:47:58Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Cycle extends Oray_Dao_Abstract
{
    const MODE_DAY   = 'day';
    const MODE_WEEK  = 'week';
    const MODE_MONTH = 'month';
    const MODE_YEAR  = 'year';

    const END_TYPE_NONE  = 0;
    const END_TYPE_COUNT = 1;
    const END_TYPE_DATE  = 2;

    /**
     * 所有星期数值
     *
     * 0（表示星期天）到 6（表示星期六）
     *
     * @var array
     */
    private $_weeks = array(0, 1, 2, 3, 4, 5, 6);

    /**
     * 工作日
     *
     * @var array
     */
    private $_workdays = array(1, 2, 3, 4, 5);

	/**
	 * 获取周期
	 *
	 * SELECT
	 * cycle_id AS cycleid, mode, type, day, week, month, weeks,
	 * at, what, period, count,
	 * end_type AS endtype, end_count AS endcount, end_date AS enddate
	 * FROM td_tudu_cycle
	 * WHERE cycle_id = ?
	 *
	 * @param array $condition
	 * @param array $filter
	 * @return Dao_Td_Tudu_Record_Cycle
	 */
	public function getCycle(array $condition, $filter = null)
	{
		$table   = 'td_tudu_cycle';
		$columns = 'cycle_id AS cycleid, mode, type, day, week, month, weeks, '
		         . 'at, what, period, count, display_date AS displaydate, is_valid AS isvalid, is_keep_attach as iskeepattach, '
		         . 'end_type AS endtype, end_count AS endcount, end_date AS enddate';
		$where   = array();

		if (!empty($condition['cycleid'])) {
			$where[] = 'cycle_id = ' . $this->_db->quote($condition['cycleid']);
		}

		if (!$where) {
			return null;
		}

		$where = implode(' AND ', $where);

		$sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

		$record = $this->_db->fetchRow($sql);

		if (!$record) {
			return null;
		}

		return Oray_Dao::record('Dao_Td_Tudu_Record_Cycle', $record);
	}

	/**
	 * 创建投票
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function createCycle(array $params)
	{
        if (empty($params['cycleid'])
        	|| empty($params['mode'])
        	|| empty($params['type'])
        	|| !isset($params['day'])
        	|| !isset($params['week'])
        	|| !isset($params['month'])
        	|| !isset($params['endtype'])) {
        	return false;
        }

	    if (self::END_TYPE_COUNT == $params['endtype'] && !isset($params['endcount'])) {
	        return false;
	    }

	    if (self::END_TYPE_DATE == $params['endtype'] && !isset($params['enddate'])) {
	        return false;
	    }

		$table = 'td_tudu_cycle';
		$bind  = array(
			'cycle_id' => $params['cycleid'],
		    'mode'     => $params['mode'],
		    'type'     => (int) $params['type'],
		    'day'      => (int) $params['day'],
			'week'     => (int) $params['week'],
			'month'    => (int) $params['month'],
		    'end_type' => (int) $params['endtype'],
			'display_date' => isset($params['displaydate']) ? (int) $params['displaydate'] : 0,
		);

	    if (isset($params['weeks'])) {
	        $bind['weeks'] = $params['weeks'];
	    }

	    if (isset($params['at'])) {
	        $bind['at'] = (int) $params['at'];
	    }

	    if (isset($params['what'])) {
	        $bind['what'] = $params['what'];
	    }

	    if (isset($params['period'])) {
	        $bind['period'] = (int) $params['period'];
	    }

	    if (isset($params['endcount'])) {
	        $bind['end_count'] = (int) $params['endcount'];
	    }

	    if (isset($params['enddate'])) {
	        $bind['end_date'] = (int) $params['enddate'];
	    }

	    if (isset($params['count'])) {
	        $bind['count'] = (int) $params['count'];
	    }

	    if (isset($params['iskeepattach'])) {
	        $bind['is_keep_attach'] = (int) $params['iskeepattach'];
	    }

		try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		return $params['cycleid'];
	}

	/**
	 * 更新周期设置
	 *
	 * @param string $cycleId
	 * @param array  $params
	 * @return boolean
	 */
	public function updateCycle($cycleId, array $params)
	{
		if (!$cycleId) {
			return false;
		}

        $table = 'td_tudu_cycle';
        $bind  = array();
        $where = 'cycle_id = ' . $this->_db->quote($cycleId);

        foreach ($params as $key => $val) {
            switch ($key) {
                case 'mode':
                case 'weeks':
                case 'what':
                    $bind[$key] = $val;
                    break;
                case 'type':
                case 'day':
                case 'week':
                case 'month':
                case 'at':
                case 'count':
                    $bind[$key] = (int) $val;
                    break;
                case 'endtype':
                	$bind['end_type'] = (int) $val;
                	break;
                case 'endcount':
                    $bind['end_count'] = (int) $val;
                    break;
                case 'enddate':
                    $bind['end_date'] = (int) $val;
                    break;
                case 'displaydate':
                    $bind['display_date'] = (int) $val;
                    break;
                case 'iskeepattach':
                    $bind['is_keep_attach'] = (int) $val;
                    break;
                default:
                    break;
            }
        }

        if (empty($bind)) {
            return false;
        }

        try {
        	$this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
        	$this->_catchException($e, __METHOD__);
        	return false;
        }

        return true;
	}

	/**
	 * 统计数自增加
	 *
	 * @param $cycleId
	 * @return boolean
	 */
	public function increment($cycleId)
	{
	    $sql = "UPDATE td_tudu_cycle SET count = count + 1 WHERE cycle_id = " . $this->_db->quote($cycleId);

	    try {
            $this->_db->query($sql);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
	}

	/**
	 * 删除周期
	 *
	 * @param string $cycleId
	 */
	public function deleteCycle($cycleId)
	{
	    $sql = "call sp_td_delete_cycle(" . $this->_db->quote($cycleId) . ")";

        try {
            $this->_db->query($sql);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
	}

	/**
	 * 获取周期的时间
	 *
	 * 如果开始日期为空，获取截止日期
	 *
	 * @param Dao_Td_Tudu_Record_Cycle $cycle
	 * @param int $startTime 当前任务开始时间
	 * @param int $endTime 当前任务截止时间
	 * @return array
	 */
	public function getCycleTime(Dao_Td_Tudu_Record_Cycle $cycle, $startTime, $endTime)
	{
	    $today = strtotime('today');

	    $time = (null === $startTime) ? $endTime : $startTime;

	    if (!is_int($endTime)) {
	        $time = $today;
	    }

	    switch ($cycle->mode) {
	        case self::MODE_DAY:
	            switch ($cycle->type) {

	                // 每D天
	                case 1:
	                    $time += 86400 * $cycle->day;
	                    break;

                    // 每个工作日
	                case 2:
	                    $time = $this->_getNextWeekDay($time, $this->_workdays);
	                    break;

                    // 每当任务完成后的第D天
	                case 3:
	                    $time = $today + 86400 * $cycle->day;
	                    break;
	            }
	            break;
	        case self::MODE_WEEK:
	            switch ($cycle->type) {

	                // 重复间隔为W周后的WEEKS
	                case 1:
	                case 2:
	                    $time = $this->_getNextWeekDay($time, $cycle->weeks, $cycle->week);
	                    break;

                    // 每当任务完成后的第W周重新生成任务
	                case 3:
	                    $time = $today + 86400 * 7 * $cycle->week;
	                    break;
	            }

	            break;
	        case self::MODE_MONTH:
	            switch ($cycle->type) {

	                // 每M个月的第D天，如果当月少于D天，取最后一天
	                case 1:
	                    $time = $this->_getNextMothDay($time, $cycle->month, $cycle->day);
	                    break;

                    // 每M个月的第N个DAY\WORKDAY\WEEKEND\WEEK
	                case 2:
	                    $time = $this->_getNextMothDayAtWhat($time, $cycle->month, $cycle->at, $cycle->what);
	                    break;

                    // 每当任务完成后的第M个月重新生成任务
	                case 3:
	                    $time = $this->_getNextMothDay($today, $cycle->month);
	                    break;
	            }

	            break;
	    }

	    if (null === $startTime) {
	        $endTime = $time;
	    } else {
	        $startTime = $time;
	        $endTime = $time + 86400 * $cycle->period;
	    }

	    return array($startTime, $endTime);
	}

	/**
	 * 获取某一个月的第几个日子
	 *
	 * @param int $time 时间
	 * @param $interval 月份间隔
	 * @param int $at 第几个
	 * @param string $what 什么日子
	 * @return int
	 */
	private function _getNextMothDayAtWhat($time, $interval, $at, $what)
	{
	    $date = getdate($time);
	    $date['mon'] += $interval;

	    // 目标月的第一天
	    $firstDate = mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], 1, $date['year']);

	    // 目标月的最后一天
	    $lastDate = mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'] + 1, 0, $date['year']);

	    switch ($what) {
	        case 'day':
                $weeks = $this->_weeks;
	            break;
	        case 'workday':
	            $weeks = $this->_workdays;
	            break;
	        case 'weekend':
	            $weeks = array_udiff($this->_weeks, $this->_workdays, 'strcmp');
	            break;
	        case 'sun':
	            $weeks = array(0);
	            break;
	        case 'mon':
	            $weeks = array(1);
	            break;
	        case 'tue':
	            $weeks = array(2);
	            break;
	        case 'wed':
	            $weeks = array(3);
	            break;
	        case 'thu':
	            $weeks = array(4);
	            break;
	        case 'fri':
	            $weeks = array(5);
	            break;
	        case 'sat':
	            $weeks = array(6);
	            break;
            default:
                $weeks = array(0,1,2,3,4,5,6);
                break;
	    }

	    $count = $last = 0;
	    for ($i = 0; $i < date('j', $lastDate); $i++) {
	        $week = date('w', $firstDate + 86400 * $i);
	        if (in_array($week, $weeks)) {
	            $last = $i;
	            if (++$count == $at) {
	                break;
	            }
	        }
        }

	    return $firstDate + 86400 * min($last, $i);
	}

	/**
	 * 获取下一个星期的日期
	 *
	 * 如果同一周内没有了就获取下一个指定周的第一个星期
	 *
	 * @param int $time
	 * @param array $workdays
	 * @param int $interval
	 * @return int
	 */
	private function _getNextWeekDay($time, array $workdays, $interval = 1)
	{
	    if (!count($workdays)) {
	        return $time;
	    }

	    $week = date('w', $time);
	    $diff = null;

	    foreach ($workdays as $workday) {
	        if ($workday > $week) {
	            $diff = $workday - $week;
	            break;
	        }
	    }

	    if (null === $diff) {
	        $diff = $workdays[0] + 7 * $interval - $week;
	    }

	    return $time + 86400 * $diff;
	}

	/**
	 * 获取下一个月的日期
	 *
	 * @param int $time
	 * @param int $interval
	 * @param int $day
	 * @return int
	 */
	private function _getNextMothDay($time, $interval = 1, $day = null)
	{
	    $date = getdate($time);
	    $date['mon'] += $interval;

	    if (null !== $day) {
	        $date['mday'] = $day;
	    }

	    // 实际的值
	    $month = ($date['mon'] - 1) % 12 + 1;

	    $time = mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], $date['mday'], $date['year']);

	    $date = getdate($time);

	    // 超出当月天数最大值时（变成下个月了），取当月最后一天（下个月的第“0”天）
	    if ($date['mon'] != $month) {
	        $time = mktime($date['hours'], $date['minutes'], $date['seconds'], $date['mon'], 0, $date['year']);
	    }

	    return $time;
	}

	/**
	 * 获取周期ID
	 *
	 * @return string
	 */
	public static function getCycleId()
	{
		return base_convert(strrev(time()) . rand(0, 999), 10, 32);
	}
}