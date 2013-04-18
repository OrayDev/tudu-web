<?php
/**
 * Attend_Total
 * 签到月份统计
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Total.php 2765 2013-03-05 01:15:21Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Total extends Oray_Dao_Abstract
{
    /**
     * 获取单个考勤分类的统计结果
     *
     * SELECT at.category_id AS categoryid, at.category_name AS categoryname, at.org_id AS orgid, 
     * at.unique_id AS uniqueid, at.date, at.total, at.update_time AS updatetime
     * FROM attend_total
     * WHERE category_id = :categoryid AND unique_id = uniqueid AND date = :date
     * LIMIT 0, 1
     *
     * @param array $condition
     * @return Dao_App_Attend_Record_Total|NULL|boolean
     */
    public function getAttendTotal(array $condition)
    {
        if (empty($condition['categoryid'])
            || empty($condition['uniqueid'])
            || empty($condition['date']))
        {
            return false;
        }

        $table   = 'attend_total at '
                 . 'LEFT JOIN attend_category ac ON at.org_id = ac.org_id AND at.category_id = ac.category_id';
        $columns = 'at.category_id AS categoryid, ac.category_name AS categoryname, at.org_id AS orgid, at.unique_id AS uniqueid, '
                 . 'at.date, at.total, at.update_time AS updatetime';
        $where   = array();
        $recordClass = 'Dao_App_Attend_Record_Total';

        $bind = array(
            'categoryid' => $condition['categoryid'],
            'uniqueid'   => $condition['uniqueid'],
            'date'       => $condition['date']
        );

        $where[] = 'at.category_id = :categoryid';
        $where[] = 'at.unique_id = :uniqueid';
        $where[] = 'at.date = :date';

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} {$where} LIMIT 0, 1";

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
    public function getAttendTotals(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_total at '
                 . 'LEFT JOIN attend_category ac ON at.org_id = ac.org_id AND at.category_id = ac.category_id';
        $columns = 'at.category_id AS categoryid, ac.category_name AS categoryname, at.org_id AS orgid, at.unique_id AS uniqueid, '
                 . 'at.date, at.total, at.update_time AS updatetime';
        $where   = array();
        $order   = array();
        $bind    = array();
        $limit   = '';
        $recordClass = 'Dao_App_Attend_Record_Total';

        if (isset($condition['orgid'])) {
            $where[] = 'at.org_id = ?';
            $bind[] = $condition['orgid'];
        }

        if (isset($condition['categoryid'])) {
            $where[] = 'at.category_id = ?';
            $bind[] = $condition['categoryid'];
        }

        if (isset($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                $where[] = 'at.unique_id IN (' . implode(',', array_fill(0, count($condition['uniqueid']), '?')) . ')';

                foreach ($condition['uniqueid'] as $item) {
                    $bind[] = $item;
                }
            } else {
                $where[] = 'at.unique_id = ?';
                $bind[] = $condition['uniqueid'];
            }
        }

        if (isset($condition['date'])) {
            $where[] = 'at.date = ?';
            $bind[] = $condition['date'];
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
     * 
     * @param array $params
     * @return boolean
     */
    public function create(array $params)
    {
        if (empty($params['categoryid'])
            || empty($params['uniqueid'])
            || empty($params['orgid'])
            || empty($params['date'])
            || !isset($params['total']))
        {
            return false;
        }

        $table = 'attend_total';
        $bind  = array(
            'category_id' => $params['categoryid'],
            'unique_id'   => $params['uniqueid'],
            'org_id'      => $params['orgid'],
            'date'        => (int) $params['date'],
            'total'       => empty($params['total']) ? 0 : $params['total'],
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

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
     * @param string $categoryId
     * @param string $uniqueId
     * @param string $date
     * @param array  $params
     * @return boolean
     */
    public function update($categoryId, $uniqueId, $date, array $params)
    {
        if (empty($categoryId)
            || empty($uniqueId)
            || empty($date))
        {
            return false;
        }

        $table = 'attend_total';
        $where = array();
        $bind  = array(
            'total'       => empty($params['total']) ? 0 : $params['total'],
            'update_time' => !empty($params['updatetime']) ? (int) $params['updatetime'] : time()
        );

        try {
            $where[] = 'category_id = ' . $this->_db->quote($categoryId);
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
     * @param string $categoryId
     * @param string $uniqueId
     * @param int    $date
     * @param int    $total
     */
    public function updateTotal($categoryId, $uniqueId, $date, $total = 1)
    {
        if ($categoryId == '^checkin') {
            if (empty($categoryId)
                || empty($uniqueId)
                || empty($date))
            {
                return false;
            }

            $bind = array(
                'categoryid' => $categoryId,
                'uniqueid' => $uniqueId,
                'date' => $date,
                'updatetime' => time()
            );

            $sql = 'UPDATE attend_total SET total = total + 1, update_time = :updatetime WHERE category_id = :categoryid AND unique_id = :uniqueid AND date = :date';
        } else {
            if (empty($categoryId)
                || empty($uniqueId)
                || empty($date)
                || empty($total))
            {
                return false;
            }

            $bind = array(
                'categoryid' => $categoryId,
                'uniqueid' => $uniqueId,
                'date' => $date,
                'period' => $total,
                'updatetime' => time()
            );
            $sql = 'UPDATE attend_total SET total = total + :period, update_time = :updatetime WHERE category_id = :categoryid AND unique_id = :uniqueid AND date = :date';
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
     * 读取当月签到记录是否存在
     *
     * @param string $uniqueId
     * @param string $date
     * @return boolean
     */
    public function existsRecord($categoryId, $uniqueId, $date)
    {
        if (empty($categoryId) || empty($uniqueId) || empty($date)) {
            return false;
        }

        $table = 'attend_total';
        $bind  = array(
            'categoryid' => $categoryId,
            'uniqueid'   => $uniqueId,
            'date'       => $date
        );

        $sql = "SELECT COUNT(0) FROM {$table} WHERE category_id = :categoryid AND unique_id = :uniqueid AND date = :date";

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }
}