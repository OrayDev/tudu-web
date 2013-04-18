<?php
/**
 * 排班计划数据操作
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Adjust.php 2720 2013-01-28 02:00:32Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Adjust extends Oray_Dao_Abstract
{
    /**
     * 获取调整记录内容
     *
     * @param array $condition
     * @param mixed $filter
     * @return Dao_App_Attend_Schedule_Record_Adjust
     */
    public function getAdjust(array $condition, $filter = null)
    {
        $table   = 'attend_schedule_adjust';
        $columns = 'org_id AS orgid, adjust_id AS adjustid, subject, start_time AS starttime, end_time AS endtime, type, '
                 . 'create_time AS createtime';
        $where   = array();
        $bind    = array();

        if (!empty($condition['adjustid'])) {
            $where[] = 'adjust_id = :adjustid';
            $bind['adjustid'] = $condition['adjustid'];
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ' , $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_App_Attend_Schedule_Record_Adjust', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 获取排班调整记录分页列表
     *
     * @param $condition
     * @param $sort
     * @param $pageSize
     * @param $page
     * @return Oray_Dao_Recordset
     */
    public function getAdjusts(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule_adjust';
        $columns = 'org_id AS orgid, adjust_id AS adjustid, subject, start_time AS starttime, end_time AS endtime, type, '
                 . 'create_time AS createtime';
        $where   = array();
        $bind    = array();
        $order   = array();
        $limit   = '';

        $primary = 'adjust_id';

        $recordClass = 'Dao_App_Attend_Schedule_Record_Adjust';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
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

        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // 使用默认的分页大小
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, $recordClass);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __MEHOTD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取排班调整记录分页列表
     *
     * @param $condition
     * @param $sort
     * @param $pageSize
     * @param $page
     * @return Oray_Dao_Recordset
     */
    public function getAdjustPage(array $condition, $sort = null, $pageSize = null, $page = null)
    {
        $table   = 'attend_schedule_adjust';
        $columns = 'org_id AS orgid, adjust_id AS adjustid, subject, start_time AS starttime, end_time AS endtime, type, '
                 . 'create_time AS createtime';
        $where   = array();
        $bind    = array();
        $order   = '';

        $primary = 'adjust_id';

        $recordClass = 'Dao_App_Attend_Schedule_Record_Adjust';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
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

        $order = implode(', ', $order);

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        /**
         * @see Oray_Db_Paginator
         */
        require_once 'Oray/Db/Paginator.php';

        try {
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
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __MEHOTD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param mixed $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getUserAdjusts(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule_adjust_user au '
                 . 'LEFT JOIN attend_schedule_adjust a ON a.org_id = au.org_id AND a.adjust_id = au.adjust_id';
        $columns = 'au.org_id AS orgid, au.adjust_id AS adjustid, a.subject, a.start_time AS starttime, a.end_time AS endtime, a.type, '
                 . 'au.create_time AS createtime';
        $where   = array();
        $bind    = array();

        if (!empty($condition['uniqueid'])) {
            $where[] = 'au.unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (!empty($condition['datetime']) && is_array($condition['datetime'])) {
            $where[] = 'a.start_time >= :start';
            $where[] = 'a.end_time <= :end';
            $bind['start'] = $condition['datetime']['start'];
            $bind['end']   = $condition['datetime']['end'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ' , $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} ORDER BY au.create_time DESC";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            if (!$records) {
                return new Oray_Dao_Recordset();
            }

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Schedule_Record_Adjust');
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
    public function getUserAdjust(array $condition, $filter = null)
    {
        $table   = 'attend_schedule_adjust_user au '
                 . 'LEFT JOIN attend_schedule_adjust a ON a.org_id = au.org_id AND a.adjust_id = au.adjust_id';
        $columns = 'au.org_id AS orgid, au.adjust_id AS adjustid, a.subject, a.start_time AS starttime, a.end_time AS endtime, a.type, '
                 . 'au.create_time AS createtime';
        $where   = array();
        $bind    = array();

        if (!empty($condition['uniqueid'])) {
            $where[] = 'au.unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (!empty($condition['datetime'])) {
            $where[] = 'a.start_time <= :datetime';
            $where[] = 'a.end_time >= :datetime';
            $bind['datetime'] = $condition['datetime'];
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ' , $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_App_Attend_Schedule_Record_Adjust', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建排班调整
     *
     * @param array $params
     * @return boolean
     */
    public function createAdjust(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['adjustid'])
            || empty($params['subject'])
            || empty($params['starttime'])
            || empty($params['endtime'])
            || empty($params['createtime']))
        {
            return false;
        }

        $table = 'attend_schedule_adjust';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'adjust_id'   => $params['adjustid'],
            'subject'     => $params['subject'],
            'start_time'  => $params['starttime'],
            'end_time'    => $params['endtime'],
            'create_time' => $params['createtime']
        );

        if (!empty($params['type'])) {
            $bind['type'] = $params['type'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['adjustid'];
    }

    /**
     * 更新排班调整记录
     *
     * @param array $params
     * @return boolean
     */
    public function updateAdjust($adjustId, array $params)
    {
        if (empty($adjustId)) {
            return false;
        }

        $table = 'attend_schedule_adjust';
        $bind  = array();

        if (!empty($params['subject'])) {
            $bind['subject'] = $params['subject'];
        }

        if (!empty($params['starttime'])) {
            $bind['start_time'] = $params['starttime'];
        }

        if (!empty($params['endtime'])) {
            $bind['end_time'] = $params['endtime'];
        }

        if (isset($params['type'])) {
            $bind['type'] = $params['type'];
        }

        try {
            $where = 'adjust_id = ' . $this->_db->quote($adjustId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除排班调整记录ID
     *
     * @param $adjustId
     * @return boolean
     */
    public function deleteAdjust($adjustId)
    {
        if (empty($adjustId)) {
            return false;
        }

        $bind = array('adjustid' => $adjustId);

        try {
            $this->_db->query('DELETE FROM attend_schedule_adjust_user WHERE adjust_id = :adjustid', $bind);
            $this->_db->query('DELETE FROM attend_schedule_adjust WHERE adjust_id = :adjustid', $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取用户列表
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getUsers(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_schedule_adjust_user';
        $columns = 'org_id AS orgid, adjust_id AS adjustid, unique_id AS uniqueid, create_time AS createtime';
        $where   = array();
        $bind    = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['adjustid'])) {
            $where[] = 'adjust_id = :adjustid';
            $bind['adjustid'] = $condition['adjustid'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        /*$sort = $this->_formatSort($sort);
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
        */
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // 使用默认的分页大小
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return $records;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __MEHOTD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 添加用户关联
     *
     * @param array $params
     */
    public function addUser(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['adjustid'])
            || empty($params['uniqueid'])
            || empty($params['createtime']))
        {
            return false;
        }

        $table = 'attend_schedule_adjust_user';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'adjust_id'   => $params['adjustid'],
            'unique_id'   => $params['uniqueid'],
            'create_time' => $params['createtime']
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /*
     * 移除用户关联
     */
    public function removeUser($adjustId, $uniqueId = null)
    {
        $sql  = 'DELETE FROM attend_schedule_adjust_user WHERE adjust_id = :adjustid';
        $bind = array('adjustid' => $adjustId);

        if (null !== $uniqueId) {
            $sql .= ' AND unique_id = :uniqueid';
            $bind['uniqueid'] = $uniqueId;
        }

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 生成排班计划ID
     *
     * @return string
     */
    public static function getAdjustId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}