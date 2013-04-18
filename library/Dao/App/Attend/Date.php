<?php
/**
 * Attend_Date
 * 签到日期统计
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Date.php 2775 2013-03-13 09:55:13Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Date extends Oray_Dao_Abstract
{
    /**
     * 获取签到日期的签到统计记录
     *
     * @param array $condition
     * @param array $filter
     * @return boolean|NULL|Dao_App_Attend_Record_Date
     */
    public function getAttendDate(array $condition, $filter = null)
    {
        if (empty($condition['uniqueid']) || empty($condition['date'])) {
            return false;
        }

        $table   = 'attend_date d '
                 . 'LEFT JOIN attend_date_apply ad ON ad.org_id = d.org_id AND d.unique_id = ad.unique_id AND d.date = ad.date';
        $columns = 'd.org_id AS orgid, d.unique_id AS uniqueid, d.date, d.is_late AS islate, d.is_leave AS isleave, '
                 . 'd.is_work AS iswork, d.checkin_status AS checkinstatus, d.work_time AS worktime, d.update_time AS updatetime, '
                 . 'GROUP_CONCAT(DISTINCT(ad.memo)) AS memo, GROUP_CONCAT(DISTINCT(ad.category_id)) AS categories';
        $recordClass = 'Dao_App_Attend_Record_Date';
        $where   = array();
        $bind    = array(
            'uniqueid' => $condition['uniqueid'],
            'date'     => $condition['date']
        );

        $where[] = 'd.unique_id = :uniqueid AND d.date = :date';

        if (isset($condition['orgid'])) {
            $where[] = 'd.org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where} GROUP BY d.unique_id, d.date LIMIT 0, 1";
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
     * 获取多条记录
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     */
    public function getAttendDates(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_date d '
                 . 'LEFT JOIN attend_date_apply ad ON ad.org_id = d.org_id AND d.unique_id = ad.unique_id AND d.date = ad.date';
        $columns = 'd.org_id AS orgid, d.unique_id AS uniqueid, d.date, d.is_late AS islate, d.is_leave AS isleave, '
                 . 'd.is_work AS iswork, d.checkin_status AS checkinstatus, d.work_time AS worktime, d.update_time AS updatetime, '
                 . 'GROUP_CONCAT(DISTINCT(ad.memo)) AS memo, GROUP_CONCAT(DISTINCT(ad.category_id)) AS categories';
        $where   = array();
        $limit   = '';
        $order   = array();
        $bind    = array();

        $recordClass = 'Dao_App_Attend_Record_Date';

        if (isset($condition['uniqueid'])) {
            $where[] = 'd.unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (isset($condition['orgid'])) {
            $where[] = 'd.org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (isset($condition['date'])) {
            if (is_array($condition['date'])) {
                $where[] = 'd.date >= :start';
                $where[] = 'd.date < :end';
                $bind['start'] = $condition['date']['start'];
                $bind['end']   = $condition['date']['end'];
            } else {
                $where[] = 'd.date = :date';
                $bind['date'] = $condition['date'];
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

        if (null !== $maxCount) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} GROUP BY d.unique_id, d.date {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e);

            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 签到状况列表
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     */
    public function getAttendDatePage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'attend_date d '
                 . 'LEFT JOIN attend_date_apply ad ON ad.org_id = d.org_id AND d.unique_id = ad.unique_id AND d.date = ad.date';
        $columns = 'd.org_id AS orgid, d.unique_id AS uniqueid, d.date, d.is_late AS islate, d.is_leave AS isleave, '
                 . 'd.is_work AS iswork, d.is_abnormal_ip AS isabnormalip, d.checkin_status AS checkinstatus, d.work_time AS worktime, '
                 . 'd.update_time AS updatetime, GROUP_CONCAT(DISTINCT(ad.memo)) AS memo, GROUP_CONCAT(DISTINCT(ad.category_id)) AS categories';
        $recordClass = 'Dao_App_Attend_Record_Date';
        $where = array();
        $order = array();
        $bind  = array();
        $limit = '';

        if (isset($condition['uniqueid'])) {
            $where[] = 'd.unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (isset($condition['orgid'])) {
            $where[] = 'd.org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (isset($condition['islate'])) {
            $where[] = 'd.is_late = 1';
        }

        if (isset($condition['isleave'])) {
            $where[] = 'd.is_leave = 1';
        }

        if (!empty($condition['iswork'])) {
            $where[] = 'd.is_work = 1';
        }

        if (!empty($condition['uncheckin'])) {
            $where[] = '((d.checkin_status & 1) = 1 OR (d.checkin_status & 4) = 4)';
        }

        if (!empty($condition['uncheckout'])) {
            $where[] = '((d.checkin_status & 2) = 2 OR (d.checkin_status & 8) = 8)';
        }

        if (isset($condition['date'])) {
            if (is_array($condition['date'])) {
                $where[] = 'd.date >= :start';
                $where[] = 'd.date < :end';
                $bind['start'] = $condition['date']['start'];
                $bind['end']   = $condition['date']['end'];
            } else {
                $where[] = 'd.date = :date';
                $bind['date'] = $condition['date'];
            }
        }

        if (isset($condition['categoryid'])) {
            $where[] = 'ad.category_id = :categoryid';
            $bind['categoryid'] = $condition['categoryid'];
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

        $groupBy = 'GROUP BY d.org_id, d.unique_id, d.date';

        if (null === $pageSize && null === $page) {
            $sql = "SELECT $columns FROM $table $where $order $groupBy";
        } else {

            // 使用默认的分页大小
            if (null === $pageSize) {
                $pageSize = self::$_defaultPageSize;
            }

            if ($page < 1) $page = 1;

            $sql = "SELECT $columns FROM $table $where $order $groupBy LIMIT " . $pageSize * ($page - 1) . ", " . $pageSize;
        }

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
     */
    public function countDate(array $condition)
    {
        $table   = 'attend_date';
        $where   = array();
        $bind    = array();

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (isset($condition['date'])) {
            if (is_array($condition['date'])) {
                $where[] = 'date >= :start';
                $where[] = 'date < :end';
                $bind['start'] = $condition['date']['start'];
                $bind['end']   = $condition['date']['end'];
            } else {
                $where[] = 'date = :date';
                $bind['date'] = $condition['date'];
            }
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT COUNT(0) FROM {$table} {$where}";

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return 0;
        }
    }

    /**
     * 创建签到记录
     *
     * @param array $params
     * @return boolean|string
     */
    public function create(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['date']))
        {
            return false;
        }

        $table   = 'attend_date';
        $bind    = array(
            'org_id'      => $params['orgid'],
            'unique_id'   => $params['uniqueid'],
            'date'        => $params['date'],
            'is_late'     => empty($params['islate']) ? 0 : 1,
            'is_leave'    => empty($params['isleave']) ? 0 : 1,
            'is_work'     => empty($params['iswork']) ? 0 : 1,
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

        if (isset($params['worktime']) && is_int($params['worktime'])) {
            $bind['work_time'] = $params['worktime'];
        }

        if (isset($params['checkinstatus'])) {
            $bind['checkin_status'] = $params['checkinstatus'];
        }

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
     * 更新签到记录
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

        $table   = 'attend_date';
        $where   = array();
        $bind    = array(
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

        if (isset($params['islate'])) {
            $bind['is_late'] = !empty($params['islate']) ? 1 : 0;
        }

        if (isset($params['isleave'])) {
            $bind['is_leave'] = !empty($params['isleave']) ? 1 : 0;
        }

        if (isset($params['iswork'])) {
            $bind['is_work'] = !empty($params['iswork']) ? 1 : 0;
        }

        if (isset($params['worktime']) && is_int($params['worktime'])) {
            $bind['work_time'] = $params['worktime'];
        }

        if (isset($params['checkinstatus'])) {
            $bind['checkin_status'] = $params['checkinstatus'];
        }

        if (isset($params['isabnormalip'])) {
            $bind['is_abnormal_ip'] = $params['isabnormalip'];
        }

        try {
            $where[] = 'unique_id = ' . $this->_db->quote($uniqueId);
            $where[] = 'date = ' . $this->_db->quote($date);
            $where   = implode(' AND ', $where);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param array $condition
     * @return boolean|array
     */
    public function dateSum(array $condition)
    {
        if (empty($condition['uniqueid'])
            || empty($condition['startdate'])
            || empty($condition['enddate']))
        {
            return false;
        }

        $table = 'attend_date';
        $bind  = array(
            'uniqueid'  => $condition['uniqueid'],
            'startdate' => $condition['startdate'],
            'enddate'   => $condition['enddate']
        );

        $sql = "SELECT SUM(is_late) AS late, SUM(is_leave) AS `leave`, SUM(is_work) AS unwork FROM {$table} WHERE unique_id = :uniqueid AND `date` >= :startdate AND `date` < :enddate";

        try {
            $sum = $this->_db->fetchRow($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $sum;
    }

    /**
     * 读取当天签到记录是否存在
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

        $table = 'attend_date';
        $bind  = array(
            'uniqueid' => $uniqueId,
            'date'     => $date
        );

        $sql = "SELECT COUNT(0) FROM {$table} WHERE unique_id = :uniqueid AND date = :date";

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $count > 0;
    }

    /**
     * 当天用户是否有考勤申请
     *
     * @param string $uniqueId
     * @param string $date
     */
    public function isApply($uniqueId, $date)
    {
        if (empty($uniqueId) || empty($date)) {
            return false;
        }

        $table = 'attend_date_apply';
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

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return array
     */
    public function getMonthApply(array $condition, $filter = null)
    {
        $table   = 'attend_date_apply';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, category_id AS categoryid, date, memo';
        $bind    = array();
        $where   = array();
        $order   = 'ORDER BY date ASC';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ?';
            $bind[]  = $condition['orgid'];
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ?';
            $bind[]  = $condition['uniqueid'];
        }

        if (!empty($condition['categoryid'])) {
            $where[] = 'category_id = ?';
            $bind[]  = $condition['categoryid'];
        }

        if (isset($condition['date']) && is_array($condition['date'])) {
            $where[] = 'date >= ?';
            $where[] = 'date < ?';
            $bind[] = $condition['date']['start'];
            $bind[]   = $condition['date']['end'];
        }

        if (empty($where)) {
            return array();
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);
            $records = self::formatRecords($records);

            return $records;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e);

            return array();
        }
    }

    /**
     * 
     * @param array $params
     */
    public function addApply(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['date'])
            || empty($params['uniqueid'])
            || empty($params['categoryid']))
        {
            return false;
        }

        $table = 'attend_date_apply';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'date'        => $params['date'],
            'unique_id'   => $params['uniqueid'],
            'category_id' => $params['categoryid']
        );

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
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
     * @param array $records
     */
    public static function formatRecords($records)
    {
        $ret = array();
        foreach ($records as $item) {
            $arr = explode('|', $item['memo']);
            $memo = array(
                'categoryname' => !empty($arr[0]) ? $arr[0] : null,
                'start' => date('Y-m-d H:i', $arr[1]),
                'end' => date('Y-m-d H:i', $arr[2]),
                'period' => $arr[3]
            );
            $ret[] = array_merge($item, $memo);
        }

        return $ret;
    }

    /**
     * 格式化备注信息
     *
     * @param string $memo
     */
    public static function formatMemo($memo)
    {
        $remarks  = explode(',', $memo);
        $result   = array();

        foreach ($remarks as $remark) {
            $arr = explode('|', $remark);
            if (count($arr) == 3) {
                $result[] = array(
                    'ischeckin'    => 1,
                    'categoryname' => !empty($arr[0]) ? $arr[0] : null,
                    'type'         => $arr[1],
                    'checkintime'  => date('Y-m-d H:i', $arr[2])
                );
            } else {
                $result[] = array(
                    'ischeckin'    => 0,
                    'categoryname' => !empty($arr[0]) ? $arr[0] : null,
                    'start'        => date('Y-m-d H:i', $arr[1]),
                    'end'          => date('Y-m-d H:i', $arr[2]),
                    'period'       => $arr[3]
                );
            }
        }

        return $result;
    }

    /**
     * 格式化签到签退状态
     *
     * @param int $status
     * @return array
     */
    public static function formatCheckinStatus($status)
    {
        if ($status == 0) {
            return null;
        }

        $ret = array();
        // 未签到
        if (($status & 1) == 1) {
            $ret[] = 0;
        }
        // 未签退
        if (($status & 2) == 2) {
            $ret[] = 1;
        }
        // 有假单
        if (($status & 4) == 4) {
            $ret[] = 2;
        }

        return $ret;
    }
}