<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Board
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Board.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Board
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Board_Board extends Oray_Dao_Abstract
{
    /**
     * 板块状态
     *
     * @var int
     */
    const STATUS_NORMAL = 0;
    const STATUS_HIDDEN = 1;
    const STATUS_CLOSED = 2;

    const FAVOR_WEIGHT_LIMIT = 10000;

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Get record
     *
     * SQL here..
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Board_Record_Board
     */
    public function getBoard(array $condition, $filter = null)
    {
        if (empty($condition['orgid']) || empty($condition['boardid'])) {
            return null;
        }

        $table   = 'td_board';
        $columns = 'org_id AS orgid, board_id AS boardid, type, owner_id AS ownerid, parent_board_id AS parentid, '
                 . 'board_name AS boardname, memo, moderators, groups, status, privacy, protect, '
                 . 'is_classify AS isclassify, order_num AS ordernum, need_confirm AS needconfirm, flow_only AS flowonly';
        $where   = array();

        $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        $where[] = 'board_id = ' . $this->_db->quote($condition['boardid']);

        if (isset($filter['status']) && is_int($filter['status'])) {
            $where[] = 'status = ' . $filter['status'];
        }

        // WHERE
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Board_Record_Board', $record);
    }


    /**
     * Get boards
     *
     * SELECT org_id AS orgid, board_id AS boardid, type, owner_id AS ownerid, parent_board_id AS parentid,
     * board_name AS boardname, memo, moderators, groups, status
     * FROM td_board
     * WHERE org_id = 'oray'
     * ORDER BY type, order_num DESC
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getBoards(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_board AS b ";
        $columns = 'b.org_id AS orgid, b.board_id AS boardid, b.type, owner_id AS ownerid, parent_board_id AS parentid, '
                 . 'board_name AS boardname, memo, moderators, groups, status, privacy, protect, '
                 . 'is_classify AS isclassify, b.order_num AS ordernum, need_confirm AS needconfirm, flow_only AS flowonly';
        $where   = array();
        $order   = array();
        $limit   = '';

        // $condition ...

        if (isset($condition['orgid'])) {
            $where[] = 'b.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type'])) {
            $where[] = 'b.type = ' . $this->_db->quote($condition['type']);
        }

        if (isset($condition['uniqueid'])) {
            $uniqueId = $this->_db->quote($condition['uniqueid']);

            $table   .= ' LEFT JOIN td_board_user AS bu ON b.org_id = bu.org_id AND b.board_id = bu.board_id '
                      . 'AND bu.unique_id = ' . $uniqueId . ' '
                      . 'LEFT JOIN td_board_favor AS bf ON b.org_id = bf.org_id AND b.board_id = bf.board_id '
                      . 'AND bf.unique_id = ' . $uniqueId;
            $columns .= ', bu.unique_id AS uniqueid, bf.weight ';
        }

        if (isset($filter['status'])) {
            $where[] = 'status = ' . (int) $filter['status'];
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        if (isset($filter['weight']) && isset($condition['uniqueid']) && is_int($filter['weight'])) {
            $where[] = 'bf.weight >= ' . $filter['weight'];
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'b.order_num';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY b.type ASC, ' . $order;
        }

        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Board_Record_Board');
    }

    /**
     * Get boards by orgid
     *
     * @param string $orgId
     * @param array  $filter
     * @param array  $sort
     * @param int    $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getBoardsByOrgId($orgId, $filter = null, $sort = 'type ASC, ordernum DESC', $maxCount = null)
    {
        return $this->getBoards(array('orgid' => $orgId), $filter, $sort, $maxCount);
    }

    /**
     * 获取板块数量
     *
     * @param $condition
     * @return int
     */
    public function getBoardCount($condition)
    {
        $where = array();

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type'])) {
            $where[] = 'type = ' . $this->_db->quote($condition['type']);
        }

        if (isset($condition['status'])) {
            $where[] = 'status = ' . (int) $condition['status'];
        }

        if (!empty($condition['parentid'])) {
            $where[] = 'parent_board_id = ' . $this->_db->quote($condition['parentid']);
        }

        if (!$where) {
            return false;
        }

        $sql = "SELECT COUNT(0) FROM td_board WHERE " . implode(' AND ', $where);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 获取排序最大值
     *
     * @param $condition
     * @return int
     */
    public function getBoardMaxOrderNum($condition)
    {
        $where = array();

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type'])) {
            $where[] = 'type = ' . $this->_db->quote($condition['type']);
        }

        if (isset($condition['status'])) {
            $where[] = 'status = ' . (int) $condition['status'];
        }

        if (!empty($condition['parentid'])) {
            $where[] = 'parent_board_id = ' . $this->_db->quote($condition['parentid']);
        }

        if (!$where) {
            return false;
        }
        //SELECT MAX(order_num) FROM td_board where org_id = 'oray' AND `type` = 'zone'
        $sql = "SELECT MAX(order_num) FROM td_board WHERE " . implode(' AND ', $where);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * Create record
     *
     * @param $params
     * @return int|false
     */
    public function createBoard(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['type'])
            || empty($params['boardname'])
            ) {
            return false;
        }

        $table = "td_board";
        $bind  = array(
            'org_id'    => $params['orgid'],
            'board_id'   => $params['boardid'],
            'type'    => $params['type'],
            'board_name' => $params['boardname']
            );
        $orderNum = null;

        if (!empty($params['ownerid'])) {
            $bind['owner_id'] = $params['ownerid'];
        }

        if (!empty($params['parentid'])) {
            $bind['parent_board_id'] = $params['parentid'];
        }

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['moderators'])) {
            $bind['moderators'] = $params['moderators'];
        }

        if (!empty($params['groups'])) {
            $bind['groups'] = $params['groups'];
        }

        if (isset($params['status'])) {
            $bind['status'] = (int) $params['status'];
        }

        if (isset($params['privacy'])) {
            $bind['privacy'] = $params['privacy'] ? 1 : 0;
        }

        if (isset($params['protect'])) {
            $bind['protect'] = $params['protect'] ? 1 : 0;
        }

        if (isset($params['isclassify'])) {
            $bind['is_classify'] = $params['isclassify'] ? 1 : 0;
        }

        if (isset($params['needconfirm'])) {
            $bind['need_confirm'] = $params['needconfirm'] ? 1 : 0;
        }

        if (isset($params['flowonly'])) {
            $bind['flow_only'] = $params['flowonly'] ? 1 : 0;
        }

        if (isset($params['ordernum'])) {
            $bind['order_num'] = (int) $params['ordernum'];
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
     * Update record
     *
     * @param string $orgId
     * @param string $boardId
     * @param array  $params
     * @return boolean
     */
    public function updateBoard($orgId, $boardId, array $params)
    {
        if (!$orgId || !$boardId) {
            return false;
        }

        $table = 'td_board';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND board_id = ' . $this->_db->quote($boardId);

        $orderNum = null;

        if (!empty($params['boardname'])) {
            $bind['board_name'] = $params['boardname'];
        }

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (isset($params['parentid'])) {
            $bind['parent_board_id'] = $params['parentid'];
        }

        if (isset($params['groups'])) {
            $bind['groups'] = $params['groups'];
        }

        if (isset($params['moderators'])) {
            $bind['moderators'] = $params['moderators'];
        }

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        if (isset($params['lastpost'])) {
            $bind['last_post'] = $params['lastpost'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['privacy'])) {
            $bind['privacy'] = $params['privacy'] ? 1 : 0;
        }

        if (isset($params['isclassify'])) {
            $bind['is_classify'] = $params['isclassify'] ? 1 : 0;
        }

        if (isset($params['needconfirm'])) {
            $bind['need_confirm'] = $params['needconfirm'] ? 1 : 0;
        }

        if (isset($params['flowonly'])) {
            $bind['flow_only'] = $params['flowonly'] ? 1 : 0;
        }

        if (isset($params['protect'])) {
            $bind['protect'] = $params['protect'] ? 1 : 0;
        }

        if (!$bind) {
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
     * Delete record
     *
     * @param string $orgId
     * @param string $boardId
     * @return boolean
     */
    public function deleteBoard($orgId, $boardId)
    {
        if (!$orgId || !$boardId) {
            return false;
        }

        $orgId   = $this->_db->quote($orgId);
        $boardId = $this->_db->quote($boardId);

        $sqls = array();
        $sqls[] = "UPDATE td_tudu SET board_id = '^board' WHERE board_id = {$boardId} AND is_draft = 1";
        $sqls[] = "DELETE FROM td_board WHERE org_id = {$orgId} AND board_id = {$boardId}";
        $sqls[] = "DELETE FROM td_board_user WHERE org_id = {$orgId} AND board_id = {$boardId}";
        $sqls[] = "DELETE FROM td_class WHERE org_id = {$orgId} AND board_id = {$boardId}";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 清空版块
     *
     * @param string $orgId
     * @param string $boardId
     * @return boolean
     */
    public function clearBoard($orgId, $boardId)
    {
        if (!$orgId || !$boardId) {
            return false;
        }

        $sql = 'call sp_td_clear_board(' . $this->_db->quote($orgId) . ' , ' . $this->_db->quote($boardId) . ')';

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新部门排序
     *
     * @param string $orgId
     * @param string $parentId
     * @param string $deptId
     * @return boolean
     */
    /*
    public function sortBoard($orgId, $boardId, $orderNum = null)
    {
        if (!$orgId && !$boardId) {
            return false;
        }

        $board = $this->getBoard(array('orgid' => $orgId, 'boardid' => $boardId));

        if (null == $board) {
            return false;
        }

        if ($board->orderNum && $board->orderNum == $orderNum) {
            return true;
        }

        $sql = 'SELECT org_id AS orgid, board_id AS boardid FROM td_board WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND parent_board_id ' . ($board->parentId == null ? ' IS NULL ' : (' = ' . $this->_db->quote($board->parentId))) . ' '
             . 'AND board_id <> ' . $this->_db->quote($board->boardId) . ' '
             . 'ORDER BY order_num DESC';

        $boards = $this->_db->fetchAll($sql);

        if (!$orderNum) {
            $orderNum = 1;
        } elseif ($orderNum > count($boards)) {
            $orderNum = count($boards) + 1;
        } else {
            if ($board->orderNum > $orderNum) {
                $orderNum ++;
            }
        }

        $depts = array();
        $num   = count($boards) + 1;
        for ($index = 0, $count = count($boards); $index < $count; $index++) {
            if ($boards[$index]['boardid'] == $board->boardId) {
                continue;
            }

            if ($num == $orderNum) {
                $num --;
            }

            $bos[$num] = $boards[$index]['boardid'];

            $num --;
        }

        $bos[$orderNum] = $board->boardId;

        $sql = 'UPDATE td_board SET order_num = :ordernum WHERE board_id = :boardid AND org_id = ' . $this->_db->quote($orgId);

        try {
            foreach ($bos as $num => $bId) {
                $this->_db->query($sql, array(
                    'ordernum'  => $num,
                    'boardid'   => $bId
                ));
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }*/

    /**
     * 整理排序
     *
     * @param $uniqueId
     */
    public function tidySort($orgId, $type, $parentId = null)
    {
        $bind = array(
            'orgid' => $orgId,
            'type' => $type
        );

        $sql = 'SELECT order_num, board_id FROM td_board WHERE org_id = :orgid AND `type` = :type';
        if (!empty($parentId)) {
            $sql .= ' AND parent_board_id = :parentid';
            $bind['parentid'] = $parentId;
        }
        $sql .= ' ORDER BY order_num DESC';

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            $count = count($records);
            foreach ($records as $index => $record) {
                $this->_db->update(
                    'td_board',
                    array('order_num' => $count - $index),
                    'org_id = ' . $this->_db->quote($orgId) . ' AND board_id = ' . $this->_db->quote($record['board_id'])
                );
            }

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }


    /**
     * 调整排序
     *
     * @param $orgId
     * @param $userId
     * @param $address
     * @param $type
     * @return boolean
     */

    public function sortBoard($boardId, $orgId, $type, $sort, $parentId = null)
    {
        $board = $this->getBoard(array(
            'boardid'  => $boardId,
            'orgid' => $orgId,
            'type' => $type
        ));

        if (null === $board) {
            return false;
        }

        $sql = 'SELECT order_num, board_id FROM td_board WHERE org_id = ' . $this->_db->quote($orgId);

        $sql .= ' AND `type` = ' . $this->_db->quote($type);
        if (!empty($parentId)) {
            $sql .= ' AND parent_board_id = ' . $this->_db->quote($parentId);
        }

        if ($sort == 'down') {
            $sql .= ' AND order_num < ' . $board->orderNum . ' ORDER BY order_num DESC';
        } else {
            $sql .= ' AND order_num > ' . $board->orderNum . ' ORDER BY order_num ASC';
        }

        $sql .= ' LIMIT 1';

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return false;
            }

            $ret = $this->updateBoard($orgId, $boardId, array('ordernum' => (int) $record['order_num']));

            $this->updateBoard($orgId, $record['board_id'], array('ordernum' => $board->orderNum));

            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }
    }

    /**
     * 合并分区
     *
     * @param $orgId
     * @param $boardId
     * @param $targetId
     */
    public function mergeBoard($orgId, $boardId, $targetId) {

        if (!$orgId || !$boardId || !$targetId) {
            return false;
        }

        $orgId    = $this->_db->quote($orgId);
        $boardId  = $this->_db->quote($boardId);
        $targetId = $this->_db->quote($targetId);

        $sqls = array();
        $sqls[] = 'UPDATE td_board SET parent_board_id = ' . $targetId . ' WHERE org_id = ' . $orgId . ' AND parent_board_id = ' . $boardId;
        $sqls[] = 'DELETE FROM td_board WHERE org_id = ' . $orgId . ' AND board_id = ' . $boardId;

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 板块是否存在
     *
     * @param $orgId
     * @param $boardId
     * @return boolean
     */
    public function existsBoard($orgId, $boardId)
    {
        if (!$orgId || !$boardId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM td_board WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND board_id = ' . $this->_db->quote($boardId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count > 0;
    }

    /**
     * 获取分区/板块的子板块数量
     *
     * @param $orgId
     * @param $boardId
     * @return boolean
     */
    public function getChildCount($orgId, $boardId)
    {
        if (!$orgId || !$boardId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM td_board WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND parent_board_id = ' . $this->_db->quote($boardId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 获取版块的状态，最后发表，统计数等
     *
     * SELECT board_id, last_post, task_num, post_num, today_task_num FROM td_board WHERE org_id = 'oray'
     *
     * last_post: tudu_id   subject last_post_time  last_poster
     *
     * @param $orgId
     * @return array
     */
    public function getBoardStats($orgId)
    {
        $sql = "SELECT board_id, last_post, tudu_num, post_num, today_tudu_num FROM td_board "
             . "WHERE org_id = " . $this->_db->quote($orgId);

        $rows = $this->_db->fetchAll($sql, array(), Zend_Db::FETCH_NUM);
        $stats = array();
        foreach($rows as $row) {
            $stats[$row[0]] = array(
                'boardid' => $row[0],
                'last' => explode(chr(9), $row[1]),
                'tudu' => (int) $row[2],
                'post' => (int) $row[3],
                'today' => (int) $row[4]
                );
        }
        return $stats;
    }

    /**
     * 读取用户版块排序
     *
     * @param string $uniqueId
     * @return array
     */
    public function getBoardSort($uniqueId)
    {
        $sql   = 'SELECT unique_id AS uniqueid, sort FROM td_board_sort WHERE unique_id = :uniqueid';
        $query = array('uniqueid' => $uniqueId);

        try {
            $record = $this->_db->fetchRow($sql, $query);
            if (null === $record) {
                return null;
            }

            $record['sort'] = !empty($record['sort']) ? json_decode($record['sort'], true) : array();
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }

        return $record;
    }

    /**
     * 更新用户版块排序
     *
     * @param string $uniqueId
     * @param json   $sort
     * @return boolean
     */
    public function updateBoardSort($uniqueId, $sort)
    {
        if (empty($uniqueId) || empty($sort)) {
            return false;
        }

        $table = 'td_board_sort';
        $bind  = array(
            'unique_id' => $uniqueId,
            'sort'      => $sort
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
     * 移除用户版块排序
     *
     * @param string $uniqueId
     * @return boolean
     */
    public function removeBoardSort($uniqueId)
    {
        if (empty($uniqueId)) {
            return false;
        }

        $sql  = 'DELETE FROM td_board_sort WHERE unique_id = :uniqueid';
        $bind = array('uniqueid' => $uniqueId);

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加板块搜藏
     *
     * @param $orgId
     * @param $boardId
     * @param $uniqueId
     */
    public function addAttention($orgId, $boardId, $uniqueId, $orderNum)
    {
        if (empty($orgId) || empty($boardId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_board_user';
        $bind  = array(
            'org_id'     => $orgId,
            'board_id'   => $boardId,
            'unique_id'  => $uniqueId,
            'order_num'  => $orderNum
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            if (23000 === $e->getCode()) {
                return true;
            }
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除板块搜藏
     *
     * @param $orgId
     * @param $boardId
     * @param $uniqueId
     */
    public function removeAttention($orgId, $boardId, $uniqueId)
    {
        if (empty($orgId) || empty($boardId) || empty($uniqueId)) {
            return false;
        }

        $sql  = 'DELETE FROM td_board_user WHERE org_id = :orgid AND board_id = :boardid AND unique_id = :uniqueid';
        $bind = array(
            'orgid'     => $orgId,
            'boardid'   => $boardId,
            'uniqueid'  => $uniqueId
        );

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除用户所有快捷版块
     *
     * @param string $orgId
     * @param string $uniqueId
     * @return boolean
     */
    public function removeAllAttention($orgId, $uniqueId)
    {
        if (empty($orgId) || empty($uniqueId)) {
            return false;
        }

        $sql  = 'DELETE FROM td_board_user WHERE org_id = ' . $this->_db->quote($orgId) . ' AND unique_id = '  . $this->_db->quote($uniqueId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 关注分区下板块排序
     *
     * @param string $orgId
     * @param string $uniqueId
     * @param string $boardId
     * @param string $sort
     * @return boolean
     */
    public function sortAttention($orgId, $uniqueId, $boardId, $sort)
    {
        $sql = 'SELECT order_num, board_id FROM td_board_user WHERE org_id = :orgid AND unique_id = :uniqueid AND board_id = :boardid';
        $bind = array('orgid' => $orgId, 'uniqueid' => $uniqueId, 'boardid' => $boardId);

        try {
            $board = $this->_db->fetchRow($sql, $bind);

            if (null === $board) {
                return false;
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        $sql = 'SELECT order_num, board_id FROM td_board_user WHERE org_id = :orgid AND unique_id = :uniqueid';
        if ($sort == 'down') {
            $sql .= ' AND order_num > :ordernum ORDER BY order_num ASC';
        } else {
            $sql .= ' AND order_num < :ordernum ORDER BY order_num DESC';
        }

        $sql .= ' LIMIT 1';
        $query = array('orgid' => $orgId, 'uniqueid' => $uniqueId, 'ordernum' => $board['order_num']);

        try {
            $record = $this->_db->fetchRow($sql, $query);

            if (!$record) {
                return false;
            }

            $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' '
                   . 'AND org_id = ' . $this->_db->quote($orgId) . ' '
                   . 'AND board_id = ' . $this->_db->quote($boardId);
            $this->_db->update('td_board_user', array('order_num' => (int) $record['order_num']), $where);

            $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' '
                   . 'AND org_id = ' . $this->_db->quote($orgId) . ' '
                   . 'AND board_id = ' . $this->_db->quote($record['board_id']);
            $this->_db->update('td_board_user', array('order_num' => (int) $board['order_num']), $where);

            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 获取关注板块列表
     *
     * @param $orgId
     * @param $uniqueId
     */
    public function getAttentionBoards($orgId, $uniqueId)
    {
        $sql = 'SELECT b.org_id AS orgid, b.board_id AS boardid, b.board_name AS boardname, bu.order_num AS ordernum, '
             . 'b.need_confirm as needconfirm, b.is_classify AS isclassify '
             . 'FROM td_board_user AS bu '
             . 'LEFT JOIN td_board AS b ON bu.org_id = b.org_id AND bu.board_id = b.board_id '
             . 'WHERE bu.org_id = :orgid AND bu.unique_id = :uniqueid AND status <> 1 ORDER BY bu.order_num ASC';

        $bind = array(
            'orgid'    => $orgId,
            'uniqueid' => $uniqueId
        );

        $records = $this->_db->fetchAll($sql, $bind);

        return $records;
    }

    /**
     * 获取最大排序索引
     *
     * @param $orgId
     * @param $parentId
     * @return int
     */
    public function getAttentionBoardsMaxOrderNum($orgId, $uniqueId)
    {
        $sql = 'SELECT MAX(order_num) FROM td_board_user WHERE org_id = ' . $this->_db->quote($orgId) . ' AND unique_id = ' . $this->_db->quote($uniqueId);

        try {
            $orderNum = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Execption $e) {
            $this->_catchExpection($e, __METHOD__);
            return false;
        }

        return $orderNum;
    }

    /**
     * 获取常用版块数据
     *
     * @param $orgId
     * @param $boardId
     * @param $uniqueId
     * @return array
     */
    public function getFavor($orgId, $boardId, $uniqueId)
    {
        $sql = 'SELECT org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, weight '
             . 'FROM td_board_favor WHERE org_id = :orgid AND board_id = :boardid AND unique_id = :uniqueid';

        $bind = array(
            'orgid'    => $orgId,
            'boardid'  => $boardId,
            'uniqueid' => $uniqueId
        );

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return $record;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建常用版块记录
     *
     * @param array $params
     * @return boolean
     */
    public function addFavor(array $params)
    {
        if (empty($params['orgid']) || empty($params['boardid']) || empty($params['uniqueid'])) {
            return false;
        }

        $table = 'td_board_favor';
        $bind  = array(
            'board_id'  => $params['boardid'],
            'unique_id' => $params['uniqueid'],
            'org_id'    => $params['orgid']
        );

        if (!empty($params['weight'])) {
            $bind['weight'] = (int) $params['weight'];
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
     * 更新版块常用属性
     *
     * @param $boardId
     * @param $uniqueId
     * @param $params
     * @return boolean
     */
    public function updateFavor($orgId, $boardId, $uniqueId, array $params)
    {
        if (empty($orgId) || empty($boardId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_board_favor';
        $bind  = array();

        if (isset($params['weight'])) {
            $bind['weight'] = (int) $params['weight'];
        }

        if (empty($bind)) {
            return false;
        }

        $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' '
               . 'AND org_id = ' . $this->_db->quote($orgId) . ' '
               . 'AND board_id = ' . $this->_db->quote($boardId);

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除常用版块
     *
     * @param $boardId
     * @param $uniqueId
     * @return boolean
     */
    public function deleteFavor($orgId, $boardId, $uniqueId)
    {
        if (empty($orgId) || empty($boardId) || empty($uniqueId)) {
            return false;
        }

        $bind = array(
            'boardid'  => $boardId,
            'uniqueid' => $uniqueId,
            'orgid'    => $orgId
        );
        $sql  = 'DELETE FROM td_board_favor WHERE unique_id = :uniqueid AND org_id = :orgid AND board_id = :boardid';

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 是否存在常用版块
     *
     * @param string $orgId
     * @param string $boardId
     * @param string $uniqueId
     */
    public function existsFavor($orgId, $boardId, $uniqueId)
    {
        $sql = 'SELECT COUNT(0) FROM td_board_favor WHERE org_id = :orgid AND board_id = :boardid AND unique_id = :uniqueid';

        $bind = array(
            'orgid'    => $orgId,
            'boardid'  => $boardId,
            'uniqueid' => $uniqueId
        );

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 获取最大的常用版块权值
     *
     * @param string $uniqueId
     * @return int
     */
    public function getMaxFavorWeight($uniqueId)
    {
        $sql = 'SELECT MAX(weight) FROM td_board_favor WHERE unique_id = :uniqueid';

        $bind = array(
            'uniqueid' => $uniqueId
        );

        try {
            $weight = (int) $this->_db->fetchOne($sql, $bind);

            return $weight;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 重新统计版块图度数与回复数
     *
     * @param string $orgId
     * @param string $boardId
     */
    public function calBoardNums($orgId, $boardId)
    {
        $orgId   = $this->_db->quote($orgId);
        $boardId = $this->_db->quote($boardId);

        $sql = 'UPDATE td_board SET ';
    }

    /**
     * 格式版主信息
     *
     * 返回格式
     * array(id1 => name1, id2 => name2, id3 => name3)
     *
     * @param $str
     * @return array
     */
    public static function formatModerator($str)
    {
        $ret = array();
        if (empty($str)) return $ret;
        foreach(explode("\n", $str) as $v) {
            $a = explode(' ', $v, 2);
            if (count($a) == 2) {
                $ret[$a[0]] = $a[1];
            }
        }
        return $ret;
    }

    /**
     * 格式化参与人员群组
     *
     * return
     * array(groupoid => name ...)
     *
     * @param string $str
     * @return array
     */
    public static function formatGroups($str)
    {
        $ret = array();

        if (!$str) return $ret;

        return explode("\n", trim($str, "\n"));
    }

    /**
     * 生成板块ID
     *
     * @param string $orgId
     * @return string
     */
    public static function getBoardId($orgId = null)
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}