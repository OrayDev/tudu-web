<?php
/**
 * Attend_Checkin
 * 签到登记
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Checkin.php 2766 2013-03-05 10:16:20Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Checkin extends Oray_Dao_Abstract
{
    const TYPE_CHECKIN  = 0;// 上班签到
    const TYPE_CHECKOUT = 1;// 下班签退

    /**
     * 签到登记类型
     *
     * @var array
     */
    protected $_supportCheckinType = array(
        self::TYPE_CHECKIN,
        self::TYPE_CHECKOUT
    );

    const STATUS_NORMAL = 0;// 正常
    const STATUS_LATE   = 1;// 迟到
    const STATUS_LEAVE  = 2;// 早退
    const STATUS_WORK   = 3;// 旷工

    /**
     * 考勤状况
     *
     * @var array
     */
    protected $_supportCheckinStatus = array(
        self::STATUS_NORMAL,
        self::STATUS_LATE,
        self::STATUS_LEAVE,
        self::STATUS_WORK
    );

    /**
     * 获取多条签到记录
     *
     * @param array $condition
     * @param array filter
     * @return Dao_App_Attend_Record_Checkin
     */
    public function getCheckin(array $condition, $filter = null)
    {
        $table   = 'attend_checkin';
        $columns = 'checkin_id AS checkinid, org_id AS orgid, unique_id AS uniqueid, date, type, status, ip, '
                 . 'address, create_time AS createtime';
        $recordClass = 'Dao_App_Attend_Record_Checkin';
        $where   = array();
        $bind    = array();

        $where[] = 'org_id = :orgid';
        $bind['orgid'] = $condition['orgid'];

        $where[] = 'unique_id = :uniqueid';
        $bind['uniqueid'] = $condition['uniqueid'];

        $where[] = 'date = :date';
        $bind['date'] = $condition['date'];

        $where[] = 'type = :type';
        $bind['type'] = $condition['type'];

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT $columns FROM $table $where LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);
  
            if (!$record) {
                return null;
            }

            return Oray_Dao::record($recordClass, $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取多条签到记录
     *
     * @param array $condition
     * @param array filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getCheckins(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_checkin';
        $columns = 'checkin_id AS checkinid, org_id AS orgid, unique_id AS uniqueid, date, type, status, ip, '
                 . 'address, create_time AS createtime';
        $recordClass = 'Dao_App_Attend_Record_Checkin';
        $where   = array();
        $order   = array();
        $bind    = array();
        $limit   = '';

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
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
                    $key = 'create_time';
                    break;
                case 'type':
                    $key = 'type';
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

        $sql = "SELECT $columns FROM $table $where $order $limit";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取迟到或旷工或早退记录
     *
     * @param array $condition
     * @param array $filter
     * @return array
     */
    public function getViolationRecords(array $condition, $filter = null)
    {
        $table   = 'attend_date AS d '
                 . 'LEFT JOIN attend_checkin AS c ON c.unique_id = d.unique_id AND c.org_id = d.org_id AND c.date = d.date ';
        $columns = 'c.checkin_id AS checkinid, d.org_id AS orgid, d.unique_id AS uniqueid, d.date, c.type, c.status, c.ip, c.address, '
                 . 'd.is_late AS islate, d.is_leave AS isleave, d.is_work AS iswork, c.create_time AS createtime';
        $bind    = array();
        $where   = array();
        $order   = 'ORDER BY c.date ASC';

        if (!empty($condition['orgid'])) {
            $where[] = 'd.org_id = ?';
            $bind[]  = $condition['orgid'];
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'd.unique_id = ?';
            $bind[]  = $condition['uniqueid'];
        }

        if (!empty($condition['islate']) && is_int($condition['islate'])) {
            $where[] = 'd.is_late = ?';
            $bind[]  = $condition['islate'];
        }

        if (!empty($condition['isleave']) && is_int($condition['isleave'])) {
            $where[] = 'd.is_leave = ?';
            $bind[]  = $condition['isleave'];
        }

        if (!empty($condition['iswork']) && is_int($condition['iswork'])) {
            $where[] = 'd.is_work = ?';
            $bind[]  = $condition['iswork'];
        }

        if (isset($condition['date']) && is_array($condition['date'])) {
            $where[] = 'd.date >= ?';
            $where[] = 'd.date < ?';
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
     * 签到、签退登记
     *
     * @param array $params
     * return boolean|string
     */
    public function createCheckin(array $params)
    {
        if (empty($params['checkinid'])
            || empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['date']))
        {
            return false;
        }

        $table = 'attend_checkin';
        $bind  = array(
            'checkin_id'  => $params['checkinid'],
            'org_id'      => $params['orgid'],
            'unique_id'   => $params['uniqueid'],
            'date'        => (int) $params['date'],
            'create_time' => !empty($params['createtime']) ? (int) $params['createtime'] : time()
        );

        if (isset($params['type']) && in_array($params['type'], $this->_supportCheckinType)) {
            $bind['type'] = (int) $params['type'];
        }

        if (isset($params['status']) && in_array($params['status'], $this->_supportCheckinStatus)) {
            $bind['status'] = (int) $params['status'];
        }

        if (!empty($params['ip'])) {
            $bind['ip'] = $params['ip'];
        }

        if (!empty($params['address'])) {
            $bind['address'] = $params['address'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['checkinid'];
    }

    /**
     * 更新签到、签退登记
     *
     * @param array $params
     * return boolean|string
     */
    public function updateCheckin($checkinId, array $params)
    {
        if (empty($checkinId)) {
            return false;
        }

        $table = 'attend_checkin';
        $bind  = array();

        if (isset($params['status']) && in_array($params['status'], $this->_supportCheckinStatus)) {
            $bind['status'] = (int) $params['status'];
        }

        if (!empty($params['ip'])) {
            $bind['ip'] = $params['ip'];
        }

        if (!empty($params['address'])) {
            $bind['address'] = $params['address'];
        }

        if (!empty($params['createtime'])) {
            $bind['create_time'] = (int) $params['createtime'];
        }

        try {
            $where = 'checkin_id = ' . $this->_db->quote($checkinId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $checkinId;
    }

    /**
     *
     * @param array $records
     */
    public static function formatRecords($records)
    {
        $ret = array();
        foreach ($records as $record) {
            $ret[$record['date']][] = array(
                'checkinid'  => $record['checkinid'],
                'orgid'      => $record['orgid'],
                'uniqueid'   => $record['uniqueid'],
                'type'       => (int) $record['type'],
                'status'     => (int) $record['status'],
                'ip'         => long2ip($record['ip']),
                'address'    => $record['address'],
                'islate'     => (int) $record['islate'],
                'isleave'    => (int) $record['isleave'],
                'iswork'     => (int) $record['iswork'],
                'createtime' => (int) $record['createtime']
            );
        }

        ksort($ret);

        return $ret;
    }

    /**
     * 获取签到ID
     *
     * return string
     */
    public static function getCheckinId()
    {
        return base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
    }
}