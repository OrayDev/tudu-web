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
 * @version    $Id: Tudu.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Tudu extends Oray_Dao_Abstract
{
    const STATUS_UNSTART  = 0;   // 未开始
    const STATUS_DOING    = 1;   // 进行中
    const STATUS_DONE     = 2;   // 已完成
    const STATUS_REJECT   = 3;   // 已拒绝
    const STATUS_CANCEL   = 4;   // 已取消

    const SPECIAL_CYCLE = 1;
    const SPECIAL_VOTE  = 2; // 投票

    const TYPE_TASK    = 'task';
    const TYPE_NOTICE  = 'notice';
    const TYPE_DISCUSS = 'discuss';
    const TYPE_MEETING = 'meeting';

    /**
     * 用户角色
     * @var string
     */
    const ROLE_ACCEPTER = 'to';      // 接收人
    const ROLE_SENDER   = 'from';    // 发送人
    const ROLE_CC       = 'cc';      // 被抄送

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
     * SELECT t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid,
     * t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy,
     * p.post_id AS postid, p.content, p.poster_info AS posterinfo, p.attach_num AS attachnum,
     * t.last_post_time AS lastposttime, t.last_poster AS lastposter,
     * t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum,
     * t.percent, t.score, t.status, t.special,
     * t.start_time AS starttime, t.end_time AS endtime,
     * t.total_time AS totaltime, t.elapsed_time AS elapsedtime,
     * t.accept_time AS accepttime, t.create_time AS create_time,
     * tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels,
     * tu.is_read AS isread, tu.is_forward AS isforward,
     * t.is_draft AS isdraft, t.is_done AS isdone
     * FROM td_tudu t
     * LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1
     * LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = ?
     * WHERE t.tudu_id = ?
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Tudu_Record_Tudu
     */
    public function getTudu(array $condition, $filter = null)
    {
        if (empty($condition['tuduid'])
            || empty($condition['uniqueid'])) {
            return null;
        }

        $table   = "td_tudu t "
                 . "LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1 "
                 . "LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id "
                 . "LEFT JOIN td_class c ON t.class_id = c.class_id AND t.org_id = c.org_id AND t.board_id = c.board_id "
                 . "LEFT JOIN td_flow f ON t.flow_id = f.flow_id AND t.org_id = f.org_id AND t.board_id = f.board_id "
                 . "LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = "
                 . $this->_db->quote($condition['uniqueid']) . " "
                 . "LEFT JOIN td_tudu_flow fl ON fl.tudu_id = t.tudu_id";

        $columns = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid, t.prev_tudu_id AS prevtuduid, "
                 . "t.type, t.subject, t.from, t.to, t.cc, t.bcc, t.priority, t.privacy, t.password, t.is_auth AS isauth, fl.current_step_id AS stepid, "
                 . "p.post_id AS postid, p.content, p.header, p.poster_info AS posterinfo, p.attach_num AS attachnum, t.app_id AS appid, "
                 . "t.last_post_time AS lastposttime, t.last_poster AS lastposter, t.last_forward AS lastforward, "
                 . "t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.cycle_num AS cyclenum, "
                 . "fl.step_num as stepnum, t.percent, t.score, t.status, t.special, t.notify_all AS notifyall, t.accep_mode AS accepmode,"
                 . "t.start_time AS starttime, t.end_time AS endtime, t.need_confirm AS needconfirm, "
                 . "t.total_time AS totaltime, t.elapsed_time AS elapsedtime, "
                 . "t.accept_time AS accepttime, t.create_time AS createtime, "
                 . "tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels, "
                 . "tu.is_read AS isread, tu.is_forward AS isforward, tu.mark, "
                 . "tu.role, tu.percent AS selfpercent, tu.forward_info AS forwardinfo, "
                 . "tu.tudu_status AS selftudustatus, tu.accept_time AS selfaccepttime, tu.auth_code AS authcode, "
                 . "t.is_draft AS isdraft, t.is_done AS isdone, t.is_top AS istop, p.is_send AS issend, "
                 . "t.class_id AS classid, t.flow_id AS flowid, c.class_name AS classname, "
                 . "g.type AS nodetype, g.parent_tudu_id AS parentid, g.root_tudu_id AS rootid";
        $where   = array();

        $where[] = 't.tudu_id = ' . $this->_db->quote($condition['tuduid']);

        if (isset($filter['orgid'])) {
            $where[] = 't.org_id = ' . $this->_db->quote($filter['orgid']);
        }

        // WHERE
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Tudu_Record_Tudu', $record);
    }


    /**
     * Get records
     *
     * SQL here..
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getTudus(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_tudu t LEFT JOIN td_tudu_group g ON t.tudu_id = g.tudu_id";
        $columns = 't.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, `from`, `to`, is_draft AS isdraft, subject, '
                 . 'g.type AS nodetype, g.parent_tudu_id AS parentid';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['tuduids']) && is_array($condition['tuduids'])) {
            $where[] = 't.tudu_id IN(' . implode(',', array_map(array($this->_db, 'quote'), $condition['tuduids'])) . ')';
        }

        if (!empty($condition['parentid'])) {
            $where[] = 'g.parent_tudu_id = ' . $this->_db->quote($condition['parentid']);

            if (!empty($condition['uniqueid'])) {
                $where[] = 'g.unique_id = ' . $this->_db->quote($condition['uniqueid']);
            }
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";
        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Tudus');
    }

    /**
     *
     * @param $condition
     * @param $filter
     */
    public function getUserTudus(array $condition, $filter = null, $offset = null, $count = null)
    {
        $table = "td_tudu t "
               . "LEFT JOIN td_tudu_group g ON g.tudu_id = t.tudu_id "
               . "LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = "
               . $this->_db->quote($condition['uniqueid'])
               . "LEFT JOIN td_post AS p ON p.tudu_id = t.tudu_id AND p.is_first = 1";

        $columns = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.is_done AS isdone, t.percent, t.cycle_id AS cycleid, t.percent, t.reply_num AS replynum, t.accept_time AS lastaccepttime, "
                 . "t.last_post_time AS lastposttime, t.is_done, t.priority, t.privacy, t.password, t.start_time AS starttime, t.end_time AS endtime, p.attach_num AS attachnum, tu.accept_time as accepttime, "
                 . "t.type, t.subject, t.from, t.to, t.cc, t.bcc, t.status, t.is_draft AS isdraft, t.need_confirm AS needconfirm, tu.unique_id AS uniqueid, "
                 . "TRIM(LEADING ',' FROM tu.labels) labels, tu.role, tu.accepter_info AS accepterinfo, tu.tudu_status AS selfstatus, tu.is_read as isread, g.type as nodetype ";
        $where   = array();
        $limit   = '';

        if (empty($condition)) {
            return null;
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'tu.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['labelid'])) {
            $table  .= ' LEFT JOIN td_tudu_label AS tl ON tl.unique_id = tu.unique_id AND tl.tudu_id = tu.tudu_id';
            $where[] = 'tl.label_id = ' . $this->_db->quote($condition['labelid']);
        }

        if (!empty($condition['type'])) {
            $where[] = 't.type = ' . $this->_db->quote($condition['type']);
        }

        // 关键字
        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%' . $condition['keyword'] . '%');
            $where[] = 't.subject LIKE ' . $keyword;
        }

        if (!$filter || !isset($filter['role']) || $filter['role'] == true) {
            $where[] = 'tu.role IS NOT NULL';
        }

        if (!$filter || !isset($filter['isdone']) || $filter['isdone'] == true) {
            $where[] = 't.status <= 2';
            $where[] = 't.is_done = 0';
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        if (null !== $count) {
            $limit = 'LIMIT';
            if (null !== $offset) {
                $limit .= ' ' . (int) $offset . ',';
            }

            $limit .= ' ' . (int) $count;
        }

        $sql = "SELECT $columns FROM $table $where ORDER BY last_post_time DESC $limit";

        $records = $this->_db->fetchAll($sql);

        if (!$records) {
            return array();
        }

        return $records;
    }

    /**
     * 读取图度组
     *
     * @param array $condition
     * @param array $filter
     */
    public function getTuduGroups(array $condition, $filter = null)
    {
        $table   = "td_tudu t "
                 . "LEFT JOIN td_tudu_group g ON g.tudu_id = t.tudu_id "
                 . "INNER JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id";
        $columns = "t.org_id AS orgid, t.board_id AS boardid, tu.unique_id AS uniqueid, t.tudu_id AS tuduid, "
                 . "t.subject, t.type, t.from, t.to, g.type AS nodetype";
        $where   = array();
        $bind    = array();

        if (isset($condition['uniqueid'])) {
            $where[] = "tu.unique_id = :uniqueid";
            $bind['uniqueid'] = $condition['uniqueid'];
        }

        if (isset($condition['orgid'])) {
            $where[] = "t.org_id = :orgid";
            $bind['orgid'] = $condition['orgid'];
        }

        if ($filter && array_key_exists('isdraft', $filter)) {
            if (null !== $filter['isdraft']) {
                $where[] = "t.is_draft = " . ($filter['isdraft'] ? 1 : 0);
            }
        } else {
            $where[] = "t.is_draft = 0";
        }

        if ($filter && array_key_exists('isdone', $filter)) {
            if (null !== $filter['isdone']) {
                $where[] = "t.is_done = " . ($filter['isdone'] ? 1 : 0);
            }
        } else {
            $where[] = "t.is_done = 0";
        }

        $where[] = "(g.type = 'node' OR  g.type = 'root')";

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT $columns FROM $table $where ORDER BY t.last_post_time DESC";

        $records = $this->_db->fetchAll($sql, $bind);

        if (!$records) {
            return new Oray_Dao_Recordset();
        }

        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_TuduGroups');
    }

    /**
     *
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getGroupTudus(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_tudu t "
                 . "LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1 "
                 . "LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id "
                 . "LEFT JOIN td_class c ON t.class_id = c.class_id AND t.org_id = c.org_id AND t.board_id = c.board_id "
                 . "LEFT JOIN td_tudu_flow fl ON fl.tudu_id = t.tudu_id "
                 . "LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = "
                 . $this->_db->quote($condition['uniqueid']);

        $columns = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid, t.prev_tudu_id AS prevtuduid, "
                 . "t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy, t.password, fl.current_step_id AS stepid, "
                 . "p.post_id AS postid, '' AS content, '' AS posterinfo, p.attach_num AS attachnum, "
                 . "t.last_post_time AS lastposttime, t.last_poster AS lastposter, t.last_forward AS lastforward, "
                 . "t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, "
                 . "t.percent, t.score, t.status, t.special, t.notify_all AS notifyall, t.accep_mode AS accepmode, "
                 . "t.start_time AS starttime, t.end_time AS endtime, t.complete_time AS completetime, t.need_confirm AS needconfirm, "
                 . "t.total_time AS totaltime, t.elapsed_time AS elapsedtime, "
                 . "t.accept_time AS accepttime, t.create_time AS createtime, "
                 . "tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels, "
                 . "tu.is_read AS isread, tu.is_forward AS isforward, tu.mark, "
                 . "tu.role, tu.percent AS selfpercent, tu.forward_info AS forwardinfo, "
                 . "tu.tudu_status AS selftudustatus, tu.accept_time AS selfaccepttime, "
                 . "t.is_draft AS isdraft, t.is_done AS isdone, t.is_top AS istop, p.is_send AS issend, "
                 . "t.class_id AS classid, t.flow_id AS flowid, c.class_name AS classname, "
                 . "g.type AS nodetype, g.parent_tudu_id AS parentid";
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['tuduids']) && is_array($condition['tuduids'])) {
            $where[] = 't.tudu_id IN(' . implode(',', array_map(array($this->_db, 'quote'), $condition['tuduids'])) . ')';
        }

        if (!empty($condition['parentid'])) {
            $where[] = 'g.parent_tudu_id = ' . $this->_db->quote($condition['parentid']);
        }

        if (!empty($condition['senderid'])) {
            $where[] = 'g.unique_id = ' . $this->_db->quote($condition['senderid']);
        }

        if (!empty($condition['role'])) {
            $where[] = 'tu.role = ' . $this->_db->quote($condition['role']);
        }

        if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
            $startTime = (int) $condition['starttime'];
            $endTime   = (int) $condition['endtime'];
            $where[] = "(((t.start_time >= {$startTime} AND t.start_time <= {$endTime}) "
                 . "OR (t.end_time >= {$startTime} AND t.end_time <= {$endTime}) "
                 . "OR ((t.start_time <= {$startTime} OR t.start_time IS NULL) "
                 . "AND (t.end_time >= {$endTime} OR t.end_time IS NULL)))"
                 . "AND (((t.complete_time <= {$endTime} AND t.complete_time >= {$startTime}) "
                 . "OR (t.complete_time >= {$endTime} AND t.complete_time >= {$startTime})) "
                 . "OR t.complete_time IS NULL) OR (t.status <= 1 AND DATEDIFF(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d'), FROM_UNIXTIME(t.end_time, '%Y-%m-%d')) > 0 AND t.end_time <= {$endTime}))";

            // 不显示“已取消”，和“已拒绝并确认“的图度
            $where[] = '(t.status <= 2 OR (t.status = 3 AND t.is_done = 0)) ';
        }

        if ($filter && array_key_exists('isdraft', $filter)) {
            if (null !== $filter['isdraft']) {
                $where[] = 't.is_draft = ' . ($filter['isdraft'] ? 1 : 0);
            }
        } else {
            $where[] = 't.is_draft = 0';
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'lastposttime':
                    $key = 'last_post_time';
                    break;
                case 'subject':
                    $key = 'subject';
                    break;
                case 'endtime':
                    $key = 'end_time';
                    break;
                case 'from':
                    $key = '`from`';
                    break;
                case 'to':
                    $key = '`to`';
                    break;
                case 'istop':
                    $key = 'is_top';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Tudu');
    }

    /**
     * 获取用户图度箱的图度列表
     *
     * SELECT t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid,
     * t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy,
     * t.last_post_time AS lastposttime, t.last_poster AS lastposter,
     * t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.percent, t.score, t.status,
     * t.start_time AS starttime, t.end_time AS endtime,
     * t.total_time AS totaltime, t.elapsed_time AS elapsedtime,
     * t.create_time AS create_time, p.post_id AS postid, p.content, '' AS posterinfo, p.attach_num AS attachnum,
     * tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels,
     * tu.is_read AS isread, tu.is_forward AS isforward, is_draft AS isdraft, is_done AS isdone
     * FROM td_tudu t
     * LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1
     * INNER JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id
     * LEFT JOIN td_tudu_label tl ON tl.tudu_id = t.tudu_id AND tl.unique_id = tu.unique_id
     * WHERE tu.unique_id = ? AND tl.label_id = ?
     *
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getTuduPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'td_tudu t '
                 . 'LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1 '
                 . 'LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id '
                 . 'INNER JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id '
                 . "LEFT JOIN td_class c ON t.class_id = c.class_id AND t.org_id = c.org_id AND t.board_id = c.board_id "
                 . 'LEFT JOIN td_tudu_label tl ON tl.tudu_id = t.tudu_id AND tl.unique_id = tu.unique_id '
                 . 'LEFT JOIN td_tudu_cycle tc ON tc.cycle_id = t.cycle_id '
                 . 'LEFT JOIN td_tudu_flow fl ON fl.tudu_id = t.tudu_id ';

        $columns = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid, t.prev_tudu_id AS prevtuduid, "
                 . "t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy, t.password, fl.current_step_id AS stepid, t.app_id AS appid, "
                 . "t.last_post_time AS lastposttime, t.last_poster AS lastposter, t.last_forward AS lastforward, t.flow_id AS flowid, "
                 . "t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.percent, t.score, t.status, "
                 . "t.special, t.start_time AS starttime, t.end_time AS endtime, t.complete_time AS completetime, t.notify_all AS notifyall, "
                 . "t.total_time AS totaltime, t.elapsed_time AS elapsedtime, t.need_confirm AS needconfirm, "
                 . "t.create_time AS createtime, t.accept_time AS accepttime, t.accep_mode AS accepmode, "
                 . "p.post_id AS postid, '' AS content, '' AS posterinfo, p.attach_num AS attachnum, "
                 . "tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels, tu.mark, "
                 . "tu.is_read AS isread, tu.is_forward AS isforward, is_draft AS isdraft, t.is_done AS isdone, t.is_top AS istop, "
                 . "tu.role, tu.percent AS selfpercent, tu.forward_info AS forwardinfo, "
                 . "tu.tudu_status AS selftudustatus, tu.accept_time AS selfaccepttime, "
                 . "t.class_id AS classid, c.class_name AS classname, tc.display_date AS displaydate, "
                 . "g.type AS nodetype, g.parent_tudu_id AS parentid";
        $primary = "t.tudu_id";
        $recordClass = "Dao_Td_Tudu_Record_Tudu";
        $where = array();
        $order = array();

        // $condition...
        if (isset($condition['tuduindexnum'])) {
            if (is_array($condition['tuduindexnum'])) {
                $arr = array_map('intval', $condition['tuduindexnum']);
                $where[] = 't.tudu_index_num IN (' . implode(',', $arr) . ')';
            } else {
                $where[] = 't.tudu_index_num = ' . $condition['tuduindexnum'];
            }
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'tu.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['label'])) {
            $where[] = 'tl.label_id = ' . $this->_db->quote($condition['label']);
        }

        // 板块
        if (isset($condition['boardid'])) {
            $where[] = 't.board_id = ' . $this->_db->quote($condition['boardid']);
        }

        // 主题分类
        if (isset($condition['classid'])) {
            $where[] = 't.class_id = ' . $this->_db->quote($condition['classid']);
        }

        // 查找已读状态
        if (isset($condition['isread'])) {
            $where[] = 'tu.is_read = ' . ($condition['isread'] ? 1 : 0);
        }

        // 关键字
        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%' . $condition['keyword'] . '%');
            $where[] = 't.subject LIKE ' . $keyword;
        }

        // 发送人
        if (!empty($condition['from'])) {
            $from = $this->_db->quote('%' . $condition['from'] . '%');
            $where[] = 't.from LIKE ' . $from;
        }

        // 接收人
        if (!empty($condition['to'])) {
            $to = $this->_db->quote('%' . $condition['to'] . '%');
            $where[] = 't.to LIKE ' . $to;
        }

        // 状态
        if (isset($condition['status'])) {
            if (is_array($condition['status'])) {
                foreach ($condition['status'] as $item) {
                    $status[] = $this->_db->quote($item);
                }
                $where[] = 't.status IN (' . implode(',', $status) . ')';
            } else if (is_int($condition['status'])) {
                $where[] = 't.status = ' . $condition['status'];
            }
        }

        // 类型
        if (!empty($condition['type'])) {
            $where[] = 't.type = ' . $this->_db->quote($condition['type']);
        }

        // 已完成
        if (isset($condition['isdone'])) {
            $where[] = 't.is_done = ' . ($condition['isdone'] ? 1 : 0);
        }

        // 甘特图
        if(!empty($condition['startdate']) && !empty($condition['enddate'])) {
            $startDate = (int) $condition['startdate'];
            $endDate = (int) $condition['enddate'];
             $where[] = "(((t.start_time >= {$startDate} AND t.start_time <= {$endDate}) "
                 . "OR (t.end_time >= {$startDate} AND t.end_time <= {$endDate}) "
                 . "OR ((t.start_time <= {$startDate} OR t.start_time IS NULL) "
                 . "AND (t.end_time >= {$endDate} OR t.end_time IS NULL)))"
                 . "AND (((t.complete_time <= {$endDate} AND t.complete_time >= {$startDate}) "
                 . "OR (t.complete_time >= {$endDate} AND t.complete_time >= {$startDate})) "
                 . "OR t.complete_time IS NULL) OR (t.status <= 1 AND DATEDIFF(FROM_UNIXTIME(UNIX_TIMESTAMP(), '%Y-%m-%d'), FROM_UNIXTIME(t.end_time, '%Y-%m-%d')) > 0 AND t.end_time <= {$endDate}))";
        }

        $array = array(
            'createtime' => 't.create_time',
            'endtime'    => 't.end_time',
            'starttime'  => 't.start_time'
        );

        // createtime, endtime, starttime
        foreach ($array as $key => $col) {
            if (!isset($condition[$key])) {
                continue ;
            }

            if (is_array($condition[$key])) {
                $arr = array();
                if (isset($condition[$key]['start'])) {
                    $arr[] = $col . ' >= ' . (int) $condition[$key]['start'];
                }

                if (isset($condition[$key]['end'])) {
                    $arr[] = $col . ' <= ' . (int) $condition[$key]['end'];
                }

                if ($arr) {
                    $where[] = '(' . $col . ' IS NOT NULL AND ' . implode(' AND ', $arr) . ')';
                }
            } elseif (is_int($condition[$key])) {
                $where[] = '(' . $col . ' IS NOT NULL AND ' . $col . ' >= ' . $condition[$key] . ')';
            }
        }

        // 过期
        if (isset($condition['expiredate']) && is_int($condition['expiredate'])) {
            $where[] = '(tu.is_read = 0 OR t.start_time IS NULL OR t.start_time <= ' . $condition['expiredate'] . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'lastposttime':
                    $key = 'last_post_time';
                    break;
                case 'subject':
                    $key = 'subject';
                    break;
                case 'endtime':
                    $key = 'end_time';
                    break;
                case 'starttime':
                    $key = 'start_time';
                    break;
                case 'from':
                    $key = '`from`';
                    break;
                case 'to':
                    $key = '`to`';
                    break;
                case 'istop':
                    $key = 'is_top';
                    break;
                case 'percent':
                    $key = 'percent';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        if (null === $pageSize && null === $page) {
            $sql = "SELECT $columns FROM $table $where $order";
        } else {

            // 使用默认的分页大小
            if (null === $pageSize) {
                $pageSize = self::$_defaultPageSize;
            }

            if ($page < 1) $page = 1;

            $sql = "SELECT $columns FROM $table $where $order LIMIT " . $pageSize * ($page - 1) . ", " . $pageSize;
        }

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     * 获取版块的图度列表
     *
     * SELECT
     * t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid,
     * t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy,
     * t.last_post_time AS lastposttime, t.last_poster AS lastposter,
     * t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.percent, t.score, t.status,
     * t.special, t.start_time AS starttime, t.end_time AS endtime,
     * t.total_time AS totaltime, t.elapsed_time AS elapsedtime,
     * t.create_time AS createtime, t.accept_time AS accepttime,
     * p.post_id AS postid, '' AS content, '' AS posterinfo, p.attach_num AS attachnum,
     * tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels,
     * tu.is_read AS isread, tu.is_forward AS isforward, is_draft AS isdraft, is_done AS isdone,
     * g.type AS nodetype, g.parent_tudu_id AS parentid
     *
     * FROM td_tudu t
     * LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id
     * LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1
     * LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = ?
     *
     * WHERE t.is_draft = 0
     * AND t.org_id = ?
     * AND t.board_id = ?
     *
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getBoardTuduPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $uniqueId = isset($condition['uniqueid'])
                  ? $this->_db->quote($condition['uniqueid'])
                  : "''";

        $table    = "td_tudu t "
                  . "LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1 "
                  . "LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id "
                  . "LEFT JOIN td_class c ON t.class_id = c.class_id AND t.org_id = c.org_id AND t.board_id = c.board_id "
                  . 'LEFT JOIN td_tudu_cycle tc ON tc.cycle_id = t.cycle_id '
                  . "LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = " . $uniqueId;
        $columns  = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid, "
                  . "t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy, t.password, "
                  . "t.last_post_time AS lastposttime, t.last_poster AS lastposter, t.flow_id AS flowid, "
                  . "t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.percent, t.score, t.status, "
                  . "t.special, t.start_time AS starttime, t.end_time AS endtime, t.need_confirm AS needconfirm, "
                  . "t.total_time AS totaltime, t.elapsed_time AS elapsedtime, t.accep_mode AS accepmode, "
                  . "t.create_time AS createtime, t.accept_time AS accepttime, "
                  . "p.post_id AS postid, '' AS content, '' AS posterinfo, p.attach_num AS attachnum, "
                  . "tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels, tu.mark, "
                  . "tu.is_read AS isread, tu.is_forward AS isforward, is_draft AS isdraft, is_done AS isdone, t.is_top AS istop, "
                  . "t.class_id AS classid, c.class_name AS classname, tc.display_date AS displaydate, "
                  . "g.type AS nodetype, g.parent_tudu_id AS parentid";
        $primary  = "t.tudu_id";
        $recordClass = "Dao_Td_Tudu_Record_Tudu";
        $where = array();
        $order = array();

        if (isset($condition['orgid'])) {
            $where[] = 't.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['boardid'])) {
            if (is_array($condition['boardid'])) {
                $where[] = 't.board_id IN(' . implode(',', array_map(array($this->_db, 'quote'), $condition['boardid'])) . ')';
            } else {
                $where[] = 't.board_id = ' . $this->_db->quote($condition['boardid']);
            }
        }

        // 关键字
        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%' . $condition['keyword'] . '%');
            $where[] = 't.subject LIKE ' . $keyword;
        }

        // 发送人
        if (!empty($condition['from'])) {
            $from = $this->_db->quote('%' . $condition['from'] . '%');
            $where[] = 't.from LIKE ' . $from;
        }

        // 接收人
        if (!empty($condition['to'])) {
            $to = $this->_db->quote('%' . $condition['to'] . '%');
            $where[] = 't.to LIKE ' . $to;
        }

        // 主题分类
        if (isset($condition['classid'])) {
            $where[] = 'c.class_id = ' . $this->_db->quote($condition['classid']);
        }

        // 类型
        if (!empty($condition['type'])) {
            if (is_array($condition['type'])) {
                $where[] = 't.type IN ( ' . implode(',', array_map(array($this->_db, 'quote'), $condition['type'])) . ')';
            } else {
                $where[] = 't.type = ' . $this->_db->quote($condition['type']);
            }
        }

        if (isset($condition['priority']) && is_int($condition['priority'])) {
            $where[] = 't.priority = ' . $condition['priority'];
        }

        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 't.status = ' . $condition['status'];
        }

        // 创建时间
        if (isset($condition['createtime'])) {
            if (is_array($condition['createtime'])) {
                $array = array();
                if (isset($condition['createtime']['start']) && is_int($condition['createtime']['start'])) {
                    $array[] = 't.create_time >= ' . $condition['createtime']['start'];
                }

                if (isset($condition['createtime']['end']) && is_int($condition['createtime']['end'])) {
                    $array[] = 't.create_time <= ' . $condition['createtime']['end'];
                }

                if ($array) {
                    $where[] = '(' . implode(' AND ', $array) . ')';
                }
            } elseif (is_int($condition['createtime'])) {
                $where[] = 't.create_time >= ' . $condition['createtime'];
            }
        }

        // 创建时间
        if (isset($condition['endtime'])) {
            if (is_array($condition['endtime'])) {
                $array = array();
                if (isset($condition['endtime']['start']) && is_int($condition['endtime']['start'])) {
                    $array[] = 't.end_time >= ' . $condition['endtime'];
                }

                if (isset($condition['endtime']['end']) && is_int($condition['endtime']['end'])) {
                    $array[] = 't.end_time <= ' . $condition['endtime'];
                }

                if ($array) {
                    $where[] = '(' . implode(' AND ', $array) . ')';
                }
            } elseif (is_int($condition['endtime'])) {
                $where[] = 't.end_time >= ' . $condition['endtime'];
            }
        }

        // 过期
        if (isset($condition['expiredate']) && is_int($condition['expiredate'])) {
            $where[] = '(t.is_done <> 1 OR t.create_time >= ' . $condition['expiredate'] . ')';
        }

        // 过滤草稿
        $where[] = 't.is_draft = 0';

        if (isset($condition['privacy'])) {
            $where[] = '(t.privacy = 0 OR tu.unique_id = ' . $uniqueId . ')';
        }

        if (isset($condition['self'])) {
            $where[] = 'tu.unique_id = ' . $uniqueId;
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'lastposttime':
                    $key = 'last_post_time';
                    break;
                case 'subject':
                    $key = 'subject';
                    break;
                case 'endtime':
                    $key = 'end_time';
                    break;
                case 'istop':
                    $key = 'is_top';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
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
     * 获取符合条件的图度数量
     *
     * @param $condition
     */
    public function getTuduCount(array $condition)
    {
        $table = 'td_tudu t ';

        if (isset($condition['orgid'])) {
            $where[] = 't.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type'])) {
            $where[] = 't.type = ' . $this->_db->quote($condition['type']);
        }

        if (isset($condition['boardid'])) {
            $where[] = 't.board_id = ' . $this->_db->quote($condition['boardid']);
        }

        if (isset($condition['uniqueid'])) {
            $table  .= 'INNER JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id ';
            $where[] = 'tu.unique_id = ' . $this->_db->quote($condition['uniqueid']);

            if (isset($condition['labelid'])) {
                $table  .= 'LEFT JOIN td_tudu_label tl ON tl.tudu_id = t.tudu_id AND tl.unique_id = tu.unique_id ';
                $where[] = 'tl.label_id = ' . $this->_db->quote($condition['labelid']);
            }
        }

        // 草稿
        if (isset($condition['isdraft'])) {
            $where[] = 't.is_draft = ' . ($condition['isdraft'] ? 1 : 0);
        }

        // 查找已读状态
        if (isset($condition['isread'])) {
            $where[] = 'tu.is_read = ' . ($condition['isread'] ? 1 : 0);
        }

        // 关键字
        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%' . $condition['keyword'] . '%');
            $where[] = 't.subject LIKE ' . $keyword;
        }

        // 发送人
        if (!empty($condition['from'])) {
            $from = $this->_db->quote('%' . $condition['from'] . '%');
            $where[] = 't.from LIKE ' . $from;
        }

        // 接收人
        if (!empty($condition['to'])) {
            $to = $this->_db->quote('%' . $condition['to'] . '%');
            $where[] = 't.to LIKE ' . $to;
        }

        // 状态
        if (isset($condition['status'])) {
            if (is_array($condition['status'])) {
                foreach ($condition['status'] as $item) {
                    $status[] = $this->_db->quote($item);
                }
                $where[] = 't.status IN (' . implode(',', $status) . ')';
            } else if (is_int($condition['status'])) {
                $where[] = 't.status = ' . $condition['status'];
            }
        }

        // 版块
        if (!empty($condition['boardid'])) {
            $where[] = 't.board_id = ' . $this->_db->quote($condition['boardid']);
        }

        // 主题分类
        if (isset($condition['classid'])) {
            $where[] = 't.class_id = ' . $this->_db->quote($condition['classid']);
        }

        // 已完成
        if (isset($condition['isdone'])) {
            $where[] = 't.is_done = ' . ($condition['isdone'] ? 1 : 0);
        }

        $array = array(
            'createtime' => 't.create_time',
            'endtime'    => 't.end_time',
            'starttime'  => 't.start_time'
        );

        // createtime, endtime, starttime
        foreach ($array as $key => $col) {
            if (!isset($condition[$key])) {
                continue ;
            }

            if (is_array($condition[$key])) {
                $arr = array();
                if (isset($condition[$key]['start'])) {
                    $arr[] = $col . ' >= ' . (int) $condition[$key]['start'];
                }

                if (isset($condition[$key]['end'])) {
                    $arr[] = $col . ' <= ' . (int) $condition[$key]['end'];
                }

                if ($arr) {
                    $where[] = '(' . implode(' AND ', $arr) . ')';
                }
            } elseif (is_int($condition[$key])) {
                $where[] = $col . ' >= ' . $condition[$key];
            }
        }

        // 过期
        if (isset($condition['expiredate']) && is_int($condition['expiredate'])) {
            $where[] = '(tu.is_read = 0 OR t.start_time IS NULL OR t.start_time <= ' . $condition['expiredate'] . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT COUNT(0) FROM {$table} {$where}";

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 统计符合条件的图度数量 -- 此方法将丢弃，请使用 getTuduCount 代替
     *
     * @param array $condition
     * @depared
     */
    public function countTudu(array $condition)
    {
        $table    = 'td_tudu t '
                  . 'INNER JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id '
                  . 'LEFT JOIN td_tudu_label tl ON tl.tudu_id = t.tudu_id AND tl.unique_id = tu.unique_id';

        // $condition...
        if (isset($condition['uniqueid'])) {
            $where[] = 'tu.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['label'])) {
            $where[] = 'tl.label_id = ' . $this->_db->quote($condition['label']);
        }

        // 状态
        if (isset($condition['isdraft']) && is_int($condition['isdraft'])) {
            $where[] = 't.is_draft = ' . (int) $condition['isdraft'];
        }

        // 查找已读状态
        if (isset($condition['isread'])) {
            $where[] = 'tu.is_read = ' . ($condition['isread'] ? 1 : 0);
        }

        // 关键字
        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%' . $condition['keyword'] . '%');
            $where[] = 't.subject LIKE ' . $keyword;
        }

        // 发送人
        if (!empty($condition['from'])) {
            $from = $this->_db->quote('%' . $condition['from'] . '%');
            $where[] = 't.from LIKE ' . $from;
        }

        // 接收人
        if (!empty($condition['to'])) {
            $to = $this->_db->quote('%' . $condition['to'] . '%');
            $where[] = 't.to LIKE ' . $to;
        }

        // 状态
        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 't.status = ' . $condition['status'];
        }

        // 版块
        if (!empty($condition['boardid'])) {
            $where[] = 't.board_id = ' . $this->_db->quote($condition['boardid']);
        }

        // 主题分类
        if (isset($condition['classid'])) {
            $where[] = 't.class_id = ' . $this->_db->quote($condition['classid']);
        }

        // 类型
        if (!empty($condition['type'])) {
            $where[] = 't.type = ' . $this->_db->quote($condition['type']);
        }

        // 已完成
        if (isset($condition['isdone'])) {
            $where[] = 't.is_done = ' . ($condition['isdone'] ? 1 : 0);
        }

        $array = array(
            'createtime' => 't.create_time',
            'endtime'    => 't.end_time',
            'starttime'  => 't.start_time'
        );

        // createtime, endtime, starttime
        foreach ($array as $key => $col) {
            if (!isset($condition[$key])) {
                continue ;
            }

            if (is_array($condition[$key])) {
                $arr = array();
                if (isset($condition[$key]['start'])) {
                    $arr[] = $col . ' >= ' . (int) $condition[$key]['start'];
                }

                if (isset($condition[$key]['end'])) {
                    $arr[] = $col . ' <= ' . (int) $condition[$key]['end'];
                }

                if ($arr) {
                    $where[] = '(' . implode(' AND ', $arr) . ')';
                }
            } elseif (is_int($condition[$key])) {
                $where[] = $col . ' >= ' . $condition[$key];
            }
        }

        // 过期
        if (isset($condition['expiredate']) && is_int($condition['expiredate'])) {
            $where[] = '(tu.is_read = 0 OR t.start_time IS NULL OR t.start_time <= ' . $condition['expiredate'] . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT COUNT(0) FROM {$table} {$where}";

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * Create tudu
     *
     * @param $params
     * @return string|false
     */
    public function createTudu(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['tuduid'])
            || empty($params['type'])
            || empty($params['from'])
            || !array_key_exists('subject', $params)) {
            return false;
        }

        $address = self::formatAddress($params['from'], true);

        $createTime = empty($params['createtime']) ? time() : (int) $params['createtime'];

        $table = "td_tudu";
        $bind = array(
            'org_id'         => $params['orgid'],
            'board_id'       => $params['boardid'],
            'tudu_id'        => $params['tuduid'],
            'type'           => $params['type'],
            'subject'        => $params['subject'],
            'from'           => $params['from'],
            'priority'       => empty($params['priority']) ? 0 : (int) $params['priority'],
            'privacy'        => empty($params['privacy']) ? 0 : (int) $params['privacy'],
            'is_draft'       => 1,
            'last_poster'    => $address ? $address[0] : '',
            'last_post_time' => $createTime,
            'create_time'    => $createTime
            );

        if (isset($params['to'])) {
            $bind['to'] = $params['to'];
        }
        if (isset($params['cc'])) {
            $bind['cc'] = $params['cc'];
        }
        if (isset($params['bcc'])) {
            $bind['bcc'] = $params['bcc'];
        }
        if (isset($params['starttime'])) {
            $bind['start_time'] = $params['starttime'];
        }
        if (isset($params['endtime'])) {
            $bind['end_time'] = $params['endtime'];
        }
        if (isset($params['totaltime'])) {
            $bind['total_time'] = (int) $params['totaltime'];
        }
        if (isset($params['elapsedtime'])) {
            $bind['elapsed_time'] = (int) $params['elapsedtime'];
        }
        if (isset($params['accepttime'])) {
            $bind['accept_time'] = (int) $params['accepttime'];
        }
        if (isset($params['percent'])) {
            $bind['percent'] = (int) $params['percent'];
        }
        if (isset($params['status'])) {
            $bind['status'] = (int) $params['status'];
        }
        if (isset($params['viewnum'])) {
            $bind['view_num'] = (int) $params['viewnum'];
        }
        if (isset($params['isdone'])) {
            $bind['is_done'] = $params['isdone'] ? 1 : 0;
        }
        if (isset($params['istop'])) {
            $bind['is_top'] = $params['istop'] ? 1 : 0;
        }
        if (isset($params['notifyall'])) {
            $bind['notify_all'] = $params['notifyall'] ? 1 : 0;
        }
        if (!empty($params['prevtuduid'])) {
            $bind['prev_tudu_id'] = $params['prevtuduid'];
        }

        if (!empty($params['stepid'])) {
            $bind['step_id'] = $params['stepid'];
        }
        if (!empty($params['appid'])) {
            $bind['app_id'] = $params['appid'];
        }
        if (isset($params['special']) && is_int($params['special'])) {
            $bind['special'] = $params['special'];
        }
        if (isset($params['cycleid'])) {
            $bind['cycle_id'] = $params['cycleid'];
        }
        if (isset($params['flowid'])) {
            $bind['flow_id'] = $params['flowid'];
        }
        if (!empty($params['classid'])) {
            $bind['class_id'] = $params['classid'];
        }
        if (isset($params['password'])) {
            $bind['password'] = $params['password'];
        }
        if (isset($params['isauth'])) {
            $bind['is_auth'] = $params['isauth'] ? 1 : 0;
        }
        if (isset($params['needconfirm'])) {
            $bind['need_confirm'] = $params['needconfirm'] ? 1 : 0;
        }
        if (isset($params['cyclenum']) && is_int($params['cyclenum'])) {
            $bind['cycle_num'] = $params['cyclenum'];
        }
        if (isset($params['acceptmode'])) {
            $bind['accep_mode'] = $params['acceptmode'] ? 1 : 0;
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['tuduid'];
    }

    /**
     * Update tudu
     *
     * @param string $tuduId
     * @param array $params
     * @return boolean
     */
    public function updateTudu($tuduId, array $params)
    {
        if (empty($tuduId)) {
            return false;
        }

        $table = "td_tudu";
        $bind  = array();
        $where = "tudu_id = " . $this->_db->quote($tuduId);

        if (isset($params['subject'])) {
            $bind['subject'] = $params['subject'];
        }
        if (!empty($params['boardid'])) {
            $bind['board_id'] = $params['boardid'];
        }
        if (isset($params['from'])) {
            $bind['from'] = $params['from'];
        }
        if (isset($params['to'])) {
            $bind['to'] = $params['to'];
        }
        if (array_key_exists('cc', $params)) {
            $bind['cc'] = $params['cc'];
        }
        if (array_key_exists('bcc', $params)) {
            $bind['bcc'] = $params['bcc'];
        }
        if (isset($params['priority'])) {
            $bind['priority'] = (int) $params['priority'];
        }
        if (isset($params['privacy'])) {
            $bind['privacy'] = (int) $params['privacy'];
        }
        if (isset($params['stepnum']) && is_int($params['stepnum'])) {
            $bind['step_num'] = $params['stepnum'];
        }

        if (array_key_exists('password', $params)) {
            $bind['password'] = $params['password'];
        }

        if (array_key_exists('prevtuduid', $params)) {
            $bind['prev_tudu_id'] = $params['prevtuduid'];
        }
        if (array_key_exists('stepid', $params)) {
            $bind['step_id'] = $params['stepid'];
        }

        if (isset($params['lastposter'])) {
            $bind['last_poster'] = $params['lastposter'];
        }
        if (isset($params['lastposttime'])) {
            $bind['last_post_time'] = (int) $params['lastposttime'];
        }

        if (isset($params['isdraft'])) {
            $bind['is_draft'] = $params['isdraft'] ? 1 : 0;
        }

        if (isset($params['isdone'])) {
            $bind['is_done'] = $params['isdone'] ? 1 : 0;
        }
        if (isset($params['istop'])) {
            $bind['is_top'] = $params['istop'] ? 1 : 0;
        }
        if (isset($params['notifyall'])) {
            $bind['notify_all'] = $params['notifyall'] ? 1 : 0;
        }

        if (array_key_exists('starttime', $params)) {
            $bind['start_time'] = $params['starttime'];
        }
        if (array_key_exists('endtime', $params)) {
            $bind['end_time'] = $params['endtime'];
        }
        if (isset($params['totaltime'])) {
            $bind['total_time'] = $params['totaltime'];
        }
        if (array_key_exists('accepttime', $params)) {
            $bind['accept_time'] = $params['accepttime'];
        }
        if (isset($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }
        if (isset($params['percent'])) {
            $bind['percent'] = (int) $params['percent'];
        }
        if (isset($params['status'])) {
            $bind['status'] = (int) $params['status'];
        }
        if (isset($params['special']) && is_int($params['special'])) {
            $bind['special'] = $params['special'];
        }
        if (isset($params['score'])) {
            $bind['score'] = (int) $params['score'];
        }
        if (array_key_exists('cycleid', $params)) {
            $bind['cycle_id'] = $params['cycleid'];
        }
        if (array_key_exists('classid', $params)) {
            $bind['class_id'] = $params['classid'];
        }
        if (array_key_exists('flowid', $params)) {
            $bind['flow_id'] = $params['flowid'];
        }
        if (!empty($params['lastforward'])) {
            $bind['last_forward'] = $params['lastforward'];
        }
        if (array_key_exists('password', $params)) {
            $bind['password'] = $params['password'];
        }
        if (isset($params['isauth'])) {
            $bind['is_auth'] = $params['isauth'] ? 1 : 0;
        }
        if (isset($params['needconfirm'])) {
            $bind['need_confirm'] = $params['needconfirm'] ? 1 : 0;
        }
        if (isset($params['acceptmode'])) {
            $bind['accep_mode'] = $params['acceptmode'] ? 1 : 0;
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
     * Delete tudu
     *
     * @param string $tuduId
     * @return boolean
     */
    public function deleteTudu($tuduId)
    {
        $sql = "call sp_td_delete_tudu(" . $this->_db->quote($tuduId) . ")";

        try {
            $ret = $this->_db->fetchOne($sql);
            if ($ret == 0) {
                return false;
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * Send tudu
     *
     * @param $tuduId
     * @return boolean
     */
    public function sendTudu($tuduId)
    {
        $sql = "call sp_td_send_tudu(" . $this->_db->quote($tuduId) . ")";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * 图度移动版块
     *
     * @param $tuduId
     * @param $boardId
     * @param $classId
     */
    public function moveTudu($tuduId, $boardId, $classId = null)
    {
        $tuduId  = $this->_db->quote($tuduId);
        $boardId = $this->_db->quote($boardId);
        $classId = $this->_db->quote($classId);

        $sql = "call sp_td_move_tudu({$tuduId}, {$boardId}, {$classId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * 获取图度关联用户
     *
     * @param string $tuduId
     * @param array  $filter
     */
    public function getUsers($tuduId, $filter = null)
    {
        $table   = 'td_tudu_user u ';
        $columns = 'u.unique_id as uniqueid, u.is_read as isread, u.is_forward as isforward, u.labels, u.role, u.is_foreign AS isforeign, '
                 . 'u.accepter_info AS accepterinfo, u.auth_code AS authcode, u.percent';

        $where = array(
            'u.tudu_id = ' . $this->_db->quote($tuduId),
            '(u.labels <> \'\' OR u.is_foreign = 1)'
        );

        if (isset($filter['role'])) {
            $where[] = 'u.role = ' . $this->_db->quote($filter['role']);
        }

        if (isset($filter['isforeign'])) {
            $where[] = 'u.is_foreign = ' . $this->_db->quote($filter['isforeign']);
        }

        if (isset($filter['uniqueid'])) {
            if (is_array($filter['uniqueid'])) {
                $arr = array_map(array($this->_db, 'quote'), $filter['uniqueid']);
                $where[] = 'u.unique_id IN (' . implode(',', $arr) . ')';
            } else {
                $where[] = 'u.unique_id = ' . $this->_db->quote($filter['uniqueid']);
            }
        }

        if (isset($filter['labelid'])) {
            $table .= 'INNER JOIN td_tudu_label l ON u.tudu_id = l.tudu_id AND u.unique_id = l.unique_id AND l.label_id = '
                    . $this->_db->quote($filter['labelid']);
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE " . implode(' AND ', $where);

        $records = $this->_db->fetchAll($sql);

        foreach ($records as &$record) {
            $info = self::formatAddress($record['accepterinfo'], true);
            $record['email']    = !empty($info[3]) ? $info[3] : null;
            $record['truename'] = !empty($info[0]) ? $info[0] : null;
        }

        return $records;
    }

    /**
     * 获取执行人
     *
     * @param string $tuduId
     */
    public function getAccepters($tuduId, $stepId = null)
    {
        $tuduId = $this->_db->quote($tuduId);

        $table   = 'td_tudu_user';
        $columns = 'unique_id AS uniqueid, is_read AS isread, is_forward AS isforward, labels, role, percent, auth_code AS authcode, '
                 . 'accepter_info AS accepterinfo, tudu_status AS tudustatus, forward_info AS forwardinfo, accept_time AS accepttime, '
                 . 'is_foreign AS isforeign, '
                 . 'IF((accept_time IS NOT NULL OR tudu_status >= 2), (SELECT SUM(elapsed_time) FROM td_post WHERE tudu_id = ' . $tuduId . ' AND unique_id = td_tudu_user.unique_id), NULL) AS elapsedtime';
        $where   = 'tudu_id = ' . $tuduId . ' AND role = \'to\'';

        if (null != $stepId) {
            $where .= ' AND step_id = ' . $this->_db->quote($stepId);
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        return $this->_db->fetchAll($sql);
    }

    /**
     * 获取投递的用户信息
     *
     * @param $tuduId
     * @param $uniqueId
     */
    public function getUser($tuduId, $uniqueId)
    {
        $columns = 'unique_id AS uniqueid, tudu_id AS tuduid, is_foreign AS isforeign, is_read AS isread, labels, role, '
                 . 'accepter_info AS accepterinfo, percent, tudu_status, accept_time, forward_info, auth_code AS authcode, '
                 . 'complete_time';
        $table   = 'td_tudu_user';

        $where   = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND unique_id = ' . $this->_db->quote($uniqueId);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            $record['accepterinfo'] = self::formatAddress($record['accepterinfo'], true);
            $record['email']        = !empty($record['accepterinfo'][3]) ? $record['accepterinfo'][3] : null;
            $record['truename']     = !empty($record['accepterinfo'][0]) ? $record['accepterinfo'][0] : null;

            return $record;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 增加图度关联用户
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param array $params
     * @return boolean | string
     */
    public function addUser($tuduId, $uniqueId, $params = null)
    {
        $table = "td_tudu_user";
        $bind  = array(
            'unique_id'  => $uniqueId,
            'tudu_id'    => $tuduId,
            'is_read'    => empty($params['isread']) ? 0 : 1,
            'is_forward' => empty($params['isforward']) ? 0 : 1
            );

        if (isset($params['role'])) {
            $bind['role'] = $params['role'];
        }

        if (isset($params['accepterinfo'])) {
            $bind['accepter_info'] = $params['accepterinfo'];
        }

        if (isset($params['percent'])) {
            $bind['percent'] = (int) $params['percent'];
        }

        if (isset($params['forwardinfo'])) {
            $bind['forward_info'] = $params['forwardinfo'];
        }

        if (isset($params['tudustatus']) && is_int($params['tudustatus'])) {
            $bind['tudu_status'] = $params['tudustatus'];
        }

        if (isset($params['isforeign'])) {
            $bind['is_foreign'] = $params['isforeign'] ? 1 : 0;
        }

        if (isset($params['authcode'])) {
            $bind['auth_code'] = $params['authcode'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                //return null;
                $sql = 'SELECT labels FROM td_tudu_user WHERE unique_id = ' . $this->_db->quote($uniqueId) . ' '
                     . 'AND tudu_id = ' . $this->_db->quote($tuduId);

                $record = $this->_db->fetchRow($sql);

                if (!$record) {
                    return false;
                }

                return (string) $record['labels'];
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 增加图度标签
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function addLabel($tuduId, $uniqueId, $labelId)
    {
        $sql = "call sp_td_add_tudu_label("
             . $this->_db->quote($tuduId) . ","
             . $this->_db->quote($uniqueId) . ","
             . $this->_db->quote($labelId) . ")";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                return null;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新用户图度信息
     *
     * @param $tuduId
     * @param $uniqueId
     * @param $params
     */
    public function updateTuduUser($tuduId, $uniqueId, $params)
    {
        $table = "td_tudu_user";
        $where = "unique_id = " . $this->_db->quote($uniqueId) . " AND tudu_id = " . $this->_db->quote($tuduId);
        $bind  = array();

        if (isset($params['isread'])) {
            $bind['is_read'] = $params['isread'] ? 1 : 0;
        }

        if (isset($params['isforward'])) {
            $bind['is_forward'] = $params['isforward'] ? 1 : 0;
        }

        if (array_key_exists('accepterinfo', $params)) {
            $bind['accepter_info'] = $params['accepterinfo'];
        }

        if (array_key_exists('percent', $params)) {
            $bind['percent'] = $params['percent'];
        }

        if (array_key_exists('tudustatus', $params)) {
            $bind['tudu_status'] = $params['tudustatus'];
        }

        if (array_key_exists('accepttime', $params)) {
            $bind['accept_time'] = $params['accepttime'];
        }

        if (array_key_exists('completetime', $params)) {
            $bind['complete_time'] = $params['completetime'];
        }

        if (array_key_exists('role', $params)) {
            $bind['role'] = $params['role'];
        }

        if (array_key_exists('isforeign', $params)) {
            $bind['is_foreign'] = $params['isforeign'] ? 1 : 0;
        }

        if (isset($params['forwardinfo'])) {
            $bind['forward_info'] = $params['forwardinfo'];
        }

        if (array_key_exists('authcode', $params)) {
            $bind['auth_code'] = $params['authcode'];
        }

        if (isset($params['mark'])) {
            $bind['mark'] = $params['mark'] ? 1 : 0;
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
     * 更新图度的标签标记
     *
     * @param $tuduId
     * @param $uniqueId
     * @param $labels
     */
    public function updateTuduLabels($tuduId, $uniqueId, $labels = null)
    {
        if (null === $labels) {
            $sql = "call sp_td_update_tudu_labels(" . $this->_db->quote($tuduId) . "," . $this->_db->quote($uniqueId) . ")";
        } else {
            $sql = "UPDATE td_tudu_user SET labels = " . $this->_db->quote($labels)
                 . " WHERE unique_id = " . $this->_db->quote($uniqueId)
                 . " AND tudu_id = " . $this->_db->quote($tuduId);
        }
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除图度用户
     *
     * @param string $tuduId
     * @param string $uniqueId
     */
    public function deleteUser($tuduId, $uniqueId)
    {
        $sql = "DELETE FROM td_tudu_user"
             . " WHERE unique_id = " . $this->_db->quote($uniqueId)
             . " AND tudu_id = " . $this->_db->quote($tuduId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除接受人信息
     *
     * @param $tuduId
     * @param $uniqueId
     */
    public function removeAccepter($tuduId, $uniqueId)
    {
        return $this->updateTuduUser($tuduId, $uniqueId, array(
            'role'         => null,
            'percent'      => null,
            'tudustatus'   => null,
            'accepttime'   => null,
            'forwardinfo'  => null
        ));
    }

    /**
     * 删除图度标签
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function deleteLabel($tuduId, $uniqueId, $labelId)
    {
        $sql = "call sp_td_delete_tudu_label("
             . $this->_db->quote($tuduId) . ","
             . $this->_db->quote($uniqueId) . ","
             . $this->_db->quote($labelId) . ")";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 递增浏览次数
     *
     * @param $tuduId
     * @return boolean
     */
    public function hit($tuduId)
    {
        $sql = "UPDATE td_tudu SET view_num = view_num + 1 WHERE tudu_id = " . $this->_db->quote($tuduId);
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param $tuduId
     * @param $uniqueId
     * @return int 返回图度状态
     */
    public function rejectTudu($tuduId, $uniqueId, $isFlow = false)
    {
        if (empty($tuduId) || empty($uniqueId)) {
            return false;
        }

        $tuduId   = $this->_db->quote($tuduId);
        $uniqueId = $this->_db->quote($uniqueId);

        $table = 'td_tudu_user';
        $bind  = array(
            'tudu_status' => self::STATUS_REJECT,
            'accept_time' => null
        );
        $where = "tudu_id = {$tuduId} AND unique_id = {$uniqueId}";

        try {
            $this->_db->update($table, $bind, $where);

            if ($isFlow) {
                $status = self::STATUS_REJECT;
                $this->_db->query("UPDATE td_tudu SET status = {$status} WHERE tudu_id = {$tuduId}");
            } else {
                $sql = "SELECT AVG(tudu_status) AS status, AVG(percent) AS percent FROM td_tudu_user WHERE tudu_id = {$tuduId} AND role = 'to' AND tudu_status <> 3";
                $record = $this->_db->fetchRow($sql);
                $status  = $record['status'];
                $percent = (int) $record['percent'];

                if ('' === trim($status)) {
                    $status = self::STATUS_REJECT;
                } else {
                    $status = (float) $status > 1 ? (int) $status : ($status > 0 ? 1 : 0);
                }

                $this->_db->query("UPDATE td_tudu SET status = {$status}, percent = {$percent} WHERE tudu_id = {$tuduId}");
            }

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $status;
    }

    /**
     * 标志已读状态
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param boolean $isRead
     */
    public function markRead($tuduId, $uniqueId, $isRead = true)
    {
        $tuduId = $this->_db->quote($tuduId);
        $uniqueId = $this->_db->quote($uniqueId);

        if ($isRead) {
            $sql = 'call sp_td_mark_read(' . $tuduId . ',' . $uniqueId . ')';
        } else {
            $sql = 'call sp_td_mark_unread(' . $tuduId . ',' . $uniqueId . ')';
        }

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 设置标签下所有图度为已读
     * 执行后必须重新统计标签的未读数
     *
     * @param string  $labelId
     * @param string  $uniqueId
     * @param boolean $isRead
     */
    public function markLabelRead($labelId, $uniqueId, $isRead = true)
    {
        $labelId  = $this->_db->quote($labelId);
        $uniqueId = $this->_db->quote($uniqueId);
        $isRead   = $isRead ? 1 : 0;

        $sql = "UPDATE td_tudu_user SET is_read = {$isRead} WHERE unique_id = {$uniqueId} "
             . "AND tudu_id IN (SELECT tudu_id FROM td_tudu_label WHERE unique_id = {$uniqueId} AND label_id = {$labelId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 设置所有关联用户为未读状态
     *
     * @param $tuduId
     * @return boolean
     */
    public function markAllUnRead($tuduId)
    {
        $tuduId = $this->_db->quote($tuduId);
        $sql = 'call sp_td_mark_all_unread(' . $tuduId . ')';

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 标志为转发状态
     *
     * @param string $tuduId
     * @param string $uniqueId
     */
    public function markForward($tuduId, $uniqueId)
    {
        $sql = "UPDATE td_tudu_user"
             . " SET is_forward = 1"
             . " WHERE unique_id = " . $this->_db->quote($uniqueId)
             . " AND tudu_id = " . $this->_db->quote($tuduId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 接收人更新任务进度
     *
     * @param $tuduId
     * @param $uniqueId
     * @param $percent
     */
    public function updateProgress($tuduId, $uniqueId, $percent)
    {
        $tuduId   = $this->_db->quote($tuduId);
        $uniqueId = $this->_db->quote($uniqueId);
        $percent  = null !== $percent ? min(100, abs((int) $percent)) : 'NULL';
        $sql = "call sp_td_update_tudu_progress({$tuduId}, {$uniqueId}, {$percent})";

        try {
            $totalPercent = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $totalPercent;
    }

    /**
     * 更新工作流进度
     *
     * @param $tuduId
     * @param $uniqueId
     * @param $percent
     */
    public function updateFlowProgress($tuduId, $uniqueId, $stepId, $percent = null, &$flowPercent = null)
    {
        if ($stepId == '^head' || $stepId == '^break') {
            return true;
        }
        $sql   = "SELECT steps FROM td_tudu_flow WHERE tudu_id = :tuduid";

        $steps = $this->_db->fetchRow($sql, array('tuduid' => $tuduId));

        if (!$steps) {
            return ;
        }
        $steps = json_decode($steps['steps'], true);

        $count = count($steps);
        $currentStep = null;
        $done  = 0;
        $type  = 0;

        foreach ($steps as $step) {
            if ($step['stepid'] == $stepId) {
                $type = $step['type'];

                break;
            }

            $done ++;
        }

        $avg = 100 / $count;
        $base = $avg * $done;

        if (0 == $type) {
            if (null != $uniqueId && is_int($percent)) {
                if ($percent == 100) {
                    $this->_db->query('UPDATE td_tudu_user SET percent = :percent, tudu_status = :status, complete_time = :completetime WHERE tudu_id = :tuduid AND unique_id = :uniqueid', array(
                        'percent' => $percent,
                        'status'  => 2,
                        'completetime' => time(),
                        'tuduid'  => $tuduId,
                        'uniqueid' => $uniqueId
                    ));
                } else {
                    $this->_db->query('UPDATE td_tudu_user SET percent = :percent WHERE tudu_id = :tuduid AND unique_id = :uniqueid', array(
                        'percent' => $percent,
                        'tuduid'  => $tuduId,
                        'uniqueid' => $uniqueId
                    ));
                }
            }

            // 步骤总体进度
            $sql = "SELECT AVG(percent) FROM td_tudu_user WHERE tudu_id = :tuduid AND role = 'to' AND (tudu_status IS NULL OR tudu_status < 3)";

            $percent = (int) $this->_db->fetchOne($sql, array('tuduid' => $tuduId));

            $flowPercent = $base + ($avg * $percent / 100);
        } elseif ($type == 1) {
            $users   = $currentStep['section'][$currentStep['currentSection']];

            if (count($users)) {
                $avgStep = $avg / count($users);

                $acceptCount = 0;
                foreach ($users as $user) {
                    if ($user['status'] == 2) {
                        $acceptCount ++;
                    }
                }
            } else {
                $avgStep = 1;
                $acceptCount = 0;
            }

            $flowPercent = $base + ($avgStep * $acceptCount);
        } else {
            $flowPercent = $base;
        }

        $params = array();
        $params['percent'] = min(100, $flowPercent);
        if ((int) $params['percent'] == 100) {
            $params['status'] = 2;
            $params['complete_time'] = time();
        }
        $this->_db->update('td_tudu', $params, 'tudu_id = ' . $this->_db->quote($tuduId));

        $flowPercent = $params['percent'];
        return $percent;
    }

    /**
     * 更新最后接受时间(更新非接收人看到的图度接受状态)
     *
     * @param string $tuduId
     */
    public function updateLastAcceptTime($tuduId)
    {
        $tuduId = $this->_db->quote($tuduId);

        $sql = "UPDATE td_tudu SET accept_time = (SELECT MAX(accept_time) FROM td_tudu_user WHERE tudu_id = {$tuduId}) "
             . "WHERE tudu_id = {$tuduId}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 计算父级图度的进度
     *
     * @param $tuduId
     */
    public function calParentsProgress($tuduId)
    {
        $tuduId = $this->_db->quote($tuduId);

        $sql = "call sp_td_calculate_parents_progress({$tuduId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 计算图度已耗时
     *
     * @param $tuduId
     * @return boolean
     */
    public function calcElapsedTime($tuduId)
    {
        $tuduId = $this->_db->quote($tuduId);
        $sql = 'call sp_td_calculate_tudu_elapsed_time(' . $tuduId . ')';

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Get tudu by id
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param array $filter
     * @return Dao_Td_Tudu_Record_Tudu
     */
    public function getTuduById($uniqueId, $tuduId, $filter = null)
    {
        return $this->getTudu(array('uniqueid' => $uniqueId, 'tuduid' => $tuduId), $filter);
    }

    /**
     * 格式化地址
     *
     * 地址以 “邮箱[空格]姓名[换行]邮箱[空格]姓名”格式保存
     * 返回格式
     * array(
     *     address1 => array(name1, userid1, domain1, address1, extend1),
     *     address2 => array(name2, userid2, domain2, address2, extend2),
     *     address3 => array(name3, userid3, domain3, address3, extend3)
     * )
     *
     * @param string $address
     * @param boolean $first 是否仅返回第一条记录，如果发件人仅有一个
     * @return array
     */
    public static function formatAddress($address, $first = false)
    {
        $ret = array();

        $pattern = "/(([\^]?[\w-\.\_]+)(?:@([^ ]+))?)? ([^\n]*)/";

        if ($first) {
            preg_match($pattern, $address, $matches);
            if ($matches) {
                $ret = array($matches[4], $matches[2], $matches[3], $matches[1]);
            }
        } else {
            preg_match_all($pattern, $address, $matches);
            for ($i = 0; $i < count($matches[0]); $i++) {
                if ($matches[1][$i]) {
                    $ret[$matches[1][$i]] = array($matches[4][$i], $matches[2][$i], $matches[3][$i], $matches[1][$i]);
                } else {
                    $ret[] = array($matches[4][$i], $matches[2][$i], $matches[3][$i], $matches[1][$i]);
                }
            }
        }
        return $ret;
    }

    /**
     * 获取图度ID
     *
     * 生成规则：微秒级时间戳转16位 + 0xfffff最大值随机数转16位
     * 格式如 129fcd77e2043a86，类似gmail生成格式
     *
     * @return string
     */
    public static function getTuduId()
    {
        $tuduId = base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
        return $tuduId;
    }
}