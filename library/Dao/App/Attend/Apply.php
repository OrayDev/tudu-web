<?php
/**
 * Attend_Apply
 * 考勤分类
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Apply.php 2773 2013-03-12 10:17:40Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Apply extends Oray_Dao_Abstract
{

    /**
     * 获取考勤申请详细信息
     *
     * @param array $conditions
     * @param mixed $filter
     * @return Dao_Attend_Record_Apply
     */
    public function getApply(array $condition, $filter = null)
    {
        $table   = 'attend_apply AS a LEFT JOIN attend_category AS c ON a.category_id = c.category_id';
        $columns = 'a.apply_id AS applyid, a.org_id AS orgid, a.tudu_id AS tuduid, a.category_id AS categoryid, a.unique_id AS uniqueid, a.period, '
                 . 'a.sender_id AS senderid, a.user_info AS userinfo, a.is_allday AS isallday, a.checkin_type AS checkintype, a.status, '
                 . 'a.start_time AS starttime, a.end_time AS endtime, a.create_time AS createtime, c.category_name as categoryname';
        $where   = array();
        $bind    = array();

        if (!empty($condition['applyid'])) {
            $where[] = 'a.apply_id = :applyid';
            $bind['applyid'] = $condition['applyid'];
        }

        if (!empty($condition['tuduid'])) {
            $where[] = 'a.tudu_id = :tuduid';
            $bind['tuduid'] = $condition['tuduid'];
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_App_Attend_Record_Apply', $record);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 获取申请列表
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getApplies(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_apply AS A '
                 . 'LEFT JOIN attend_category AS C ON A.category_id = C.category_id';
        $columns = 'A.apply_id AS applyid, A.org_id AS orgid, A.tudu_id AS tuduid, A.category_id AS categoryid, A.unique_id AS uniqueid, A.period, '
                 . 'A.sender_id AS senderid, A.user_info AS userinfo, A.start_time AS starttime, A.end_time AS endtime, A.status, '
                 . 'A.create_time AS createtime, C.category_name as categoryname, A.is_allday AS isallday';
        $where   = array();
        $bind    = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['categoryid'])) {
            $where[] = 'A.category_id = :categoryid';
            $bind['categoryid'] = $condition['categoryid'];
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'A.unique_id = :uniqueid';
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (!empty($condition['senderid'])) {
            $where[] = 'A.sender_id = :senderid';
            $bind['senderid'] = $condition['senderid'];
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'A.org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (!empty($condition['date'])) {
            $where[] = 'A.date = :date';
            $bind['date'] = $condition['date'];
        }

        if (isset($condition['status'])) {
            $where[] = 'A.status = :status';
            $bind['status'] = $condition['status'];
        }

        if (!empty($condition['datetime'])) {
            $where[] = 'A.start_time <= :datetime';
            $where[] = 'A.end_time >= :datetime';
            $bind['datetime'] = $condition['datetime'];
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
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";
        try {

            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Record_Apply');

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取审批分页列表
     *
     * @param $condition
     * @param $sort
     * @param $page
     * @param $pageSize
     */
    public function getApplyPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'attend_apply AS A LEFT JOIN attend_category AS C ON A.org_id = C.org_id AND A.category_id = C.category_id ';
        $columns = 'A.apply_id AS applyid, A.org_id AS orgid, A.tudu_id AS tuduid, A.category_id AS categoryid, A.unique_id AS uniqueid, A.period, '
                 . 'A.sender_id AS senderid, A.user_info AS userinfo, A.start_time AS starttime, A.end_time AS endtime, A.status, '
                 . 'A.is_allday AS isallday, A.create_time AS createtime, C.category_name as categoryname';
        $where   = array();
        $bind    = array();
        $primary = 'A.apply_id';

        $recordClass = 'Dao_App_Attend_Record_Apply';

        if (!empty($condition['uniqueid'])) {
            if (is_array($condition['uniqueid'])) {
                foreach ($condition['uniqueid'] as $item) {
                    $uniqueIds[] = $this->_db->quote($item);
                }
                $where[] = 'A.unique_id IN (' . implode(',', $uniqueIds) . ')';
            } else {
                $where[] = 'A.unique_id = ' . $this->_db->quote($condition['uniqueid']);
            }
        }

        if (!empty($condition['senderid'])) {
            $where[] = 'A.sender_id = ' . $this->_db->quote($condition['senderid']);
        }

        if (!empty($condition['categoryid'])) {
            $where[] = 'A.category_id = ' . $this->_db->quote($condition['categoryid']);
        }

        if (!empty($condition['starttime'])) {
            $where[] = 'A.start_time >= ' . $this->_db->quote($condition['starttime']);
        }

        if (!empty($condition['endtime'])) {
            $where[] = 'A.end_time <= ' . $this->_db->quote($condition['endtime']);
        }

        if (!empty($condition['startdate']) && !empty($condition['enddate'])) {
            $start = (int) $condition['startdate'];
            $end = (int) $condition['enddate'];
            $where[] = "(A.start_time >= {$start} AND A.end_time <= {$end} "
                     . "OR (A.end_time IS NULL AND A.start_time >= {$start} AND A.start_time <= {$end}) "
                     . "OR (A.start_time IS NULL AND A.end_time <= {$end} AND A.end_time >= {$start}))";
        }

        if (isset($condition['status'])) {
            $where[] = 'A.status = ' . $this->_db->quote($condition['status']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'A.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['reviewerid'])) {
            $reviewerId = $this->_db->quote($condition['reviewerid']);
            $table   .= 'LEFT JOIN attend_apply_reviewer AS R ON A.apply_id = R.apply_id AND R.unique_id = ' . $reviewerId;
            $columns .= ', R.review_status AS reviewstatus';

            $cond  = 'R.unique_id = ' . $reviewerId;

            if (!empty($condition['associateid'])) {
                $condition['associateid'] = array_map(array($this->_db, 'quote'), $condition['associateid']);
                $cond .= ' OR A.unique_id IN (' . implode(',', $condition['associateid']) . ')';
            }

            $where[] = '(' . $cond . ')';
        }

        $where[] = 'A.status >= 0';

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ' , $where);

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'A.create_time';
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
     *
     * @param array $condition
     * @param array $filter
     * @return array
     */
    public function getMonthApplies(array $condition, $filter = null)
    {
        $table   = 'attend_apply a '
                 . 'LEFT JOIN `oray-tudu-ts`.td_post p ON p.tudu_id = a.tudu_id AND p.is_first = 1';
        $columns = 'a.org_id AS orgid, a.category_id AS categoryid, p.content, a.period, a.is_allday AS isallday, '
                 . 'a.checkin_type AS checkintype, a.start_time AS starttime, a.end_time AS endtime';
        $where   = array();
        $bind    = array();
        $order   = 'ORDER BY a.start_time ASC';

        $where[] = 'status = 2';

        if (!empty($condition['orgid'])) {
            $where[] = 'a.org_id = ?';
            $bind[]  = $condition['orgid'];
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'a.unique_id = ?';
            $bind[]  = $condition['uniqueid'];
        }

        if (!empty($condition['categoryid'])) {
            $where[] = 'a.category_id = ?';
            $bind[]  = $condition['categoryid'];
        }

        if (!empty($condition['starttime'])) {
            $where[] = '(a.start_time IS NULL OR a.start_time >= ?)';
            $bind[]  = $condition['starttime'];
        }

        if (!empty($condition['endtime'])) {
            $where[] = '(a.end_time IS NULL OR a.end_time < ?)';
            $bind[]  = $condition['endtime'];
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
     * 创建靠请审批记录
     *
     * @param array $params
     * @return string
     */
    public function createApply(array $params)
    {
        if (empty($params['applyid'])
            || empty($params['orgid'])
            || empty($params['tuduid'])
            || empty($params['categoryid'])
            || empty($params['uniqueid'])
            || empty($params['senderid']))
        {
            return false;
        }

        $table = 'attend_apply';
        $bind  = array(
            'apply_id' => $params['applyid'],
            'org_id'   => $params['orgid'],
            'tudu_id'  => $params['tuduid'],
            'category_id' => $params['categoryid'],
            'unique_id'   => $params['uniqueid'],
            'sender_id'   => $params['senderid']
        );

        if (!empty($params['starttime'])) {
            $bind['start_time'] = $params['starttime'];
        }

        if (!empty($params['endtime'])) {
            $bind['end_time'] = $params['endtime'];
        }

        if (isset($params['period'])) {
            $bind['period'] = $params['period'];
        }

        if (array_key_exists('isallday', $params)) {
            $bind['is_allday'] = $params['isallday'];
        }

        if (array_key_exists('checkintype', $params)) {
            $bind['checkin_type'] = $params['checkintype'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (!empty($params['userinfo'])) {
            $bind['user_info'] = $params['userinfo'];
        }

        if (!empty($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['applyid'];
    }

    /**
     * 更新考请申请信息
     *
     * @param string $applyId
     * @param array $params
     * @return boolean
     */
    public function updateApply($applyId, array $params)
    {
        $table = 'attend_apply';
        $bind  = array();

        if (isset($params['categoryid'])) {
            $bind['category_id'] = $params['categoryid'];
        }

        if (array_key_exists('starttime', $params)) {
            $bind['start_time'] = $params['starttime'];
        }

        if (array_key_exists('endtime', $params)) {
            $bind['end_time'] = $params['endtime'];
        }

        if (array_key_exists('isallday', $params)) {
            $bind['is_allday'] = $params['isallday'];
        }

        if (array_key_exists('checkintype', $params)) {
            $bind['checkin_type'] = $params['checkintype'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['period'])) {
            $bind['period'] = $params['period'];
        }

        try {
            $where = 'apply_id = ' . $this->_db->quote($applyId);

            $this->_db->update($table, $bind, $where);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    public function getSummary($uniqueId, $categoryId, $startTime, $endTime)
    {
        $sql = 'SELECT start_time starttime, end_time AS endtime FROM attend_apply '
             . 'WHERE unique_id = :uniqueid AND category_id = :categoryid '
             . 'AND (start_time > :starttime OR end_time < :endtime)';

        $bind = array(
            'uniqueid'   => $uniqueId,
            'categoryid' => $categoryId,
            'starttime'  => $startTime,
            'endtime'    => $endTime
        );
        try {
            $records = $this->_db->fetchAll($sql, $bind);

            $sum = 0;
            foreach ($records as $record) {
                $sum += $record['endtime'] - $record['starttime'];
            }

            return $sum;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return 0;
        }
    }

    /**
     * 删除申请信息
     *
     * @param string $applyId
     * @return boolean
     */
    public function deleteApply($applyId)
    {
        $sql = "DELETE FROM attend_apply WHERE apply_id = :applyid";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加流程审批人关联
     *
     * @param $applyId
     * @param $uniqueId
     * @param $status
     */
    public function addReviewer($applyId, $uniqueId, $status)
    {
        $bind  = array(
            'applyid'      => $applyId,
            'uniqueid'     => $uniqueId,
            'reviewstatus' => $status
        );

        $sql = "INSERT IGNORE INTO attend_apply_reviewer (apply_id, unique_id, review_status) VALUES (:applyid, :uniqueid, :reviewstatus)";

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加流程审批人关联
     *
     * @param $applyId
     * @param $uniqueId
     * @param $params
     */
    public function updateReviewer($applyId, $uniqueId, array $params)
    {
        $table = 'attend_apply_reviewer';

        if (isset($params['status'])) {
            $bind['review_status'] = $params['status'];
        }

        if (empty($bind)) {
            return false;
        }

        try {
            $where = 'apply_id = ' . $this->_db->quote($applyId) . ' '
                   . 'AND unique_id = ' . $this->_db->quote($uniqueId);

            $this->_db->update($table, $bind, $where);
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
            if ($item['categoryid'] == '^checkin') {
                if ($item['checkintype'] == 0) {
                    $checkinTime = date('Y-m-d H:i', $item['starttime']);
                } elseif ($item['checkintype'] == 1) {
                    $checkinTime = date('Y-m-d H:i', $item['endtime']);
                }
                $ret[] = array(
                    'checkintime' => $checkinTime,
                    'type'        => $item['checkintype'] == 0 ? 0 : 1,
                    'content'     => $item['content']
                );
            } else {
                $ret[] = array(
                    'start'   => date('Y-m-d H:i', $item['starttime']),
                    'end'     => date('Y-m-d H:i', $item['endtime']),
                    'period'  => $item['period'],
                    'content' => $item['content']
                );
            }
        }

        return $ret;
    }

    /**
     * 生成考勤申请ID
     */
    public static function getApplyId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}