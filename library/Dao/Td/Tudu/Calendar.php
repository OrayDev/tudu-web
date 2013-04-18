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
 * @version    $Id: Calendar.php 2376 2012-11-13 03:13:48Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Calendar extends Oray_Dao_Abstract
{

    /**
     * 获取日程（甘特度）模式期限内图度
     * condition中必须传入开始时间 starttime 和结束时间 endtime
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
    public function getCalendarTudus(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        if (empty($condition['uniqueid'])) {
            return new Oray_Dao_Recordset();
        }

        $table   = 'td_tudu t '
                 . 'LEFT JOIN td_post p ON p.tudu_id = t.tudu_id AND p.is_first = 1 '
                 . 'LEFT JOIN td_tudu_group g ON g.tudu_id = p.tudu_id '
                 . 'INNER JOIN td_tudu_user tg ON tg.tudu_id = t.tudu_id '
                 . 'LEFT JOIN td_tudu_user tu ON tu.tudu_id = t.tudu_id AND tu.unique_id = ' . $this->_db->quote($condition['uniqueid']) . ' '
                 . "LEFT JOIN td_class c ON t.class_id = c.class_id AND t.org_id = c.org_id AND t.board_id = c.board_id ";
        $columns = "t.org_id AS orgid, t.board_id AS boardid, t.tudu_id AS tuduid, t.cycle_id AS cycleid, t.prev_tudu_id AS prevtuduid, "
                 . "t.type, t.subject, t.from, t.to, t.cc, t.priority, t.privacy, t.password, "
                 . "t.last_post_time AS lastposttime, t.last_poster AS lastposter, t.last_forward AS lastforward, "
                 . "t.view_num AS viewnum, t.reply_num AS replynum, t.log_num AS lognum, t.percent, t.score, t.status, "
                 . "t.special, t.start_time AS starttime, t.end_time AS endtime, t.complete_time AS completetime, t.notify_all AS notifyall, "
                 . "t.total_time AS totaltime, t.elapsed_time AS elapsedtime, "
                 . "t.create_time AS createtime, t.accept_time AS accepttime, "
                 . "p.post_id AS postid, '' AS content, '' AS posterinfo, p.attach_num AS attachnum, "
                 . "tu.unique_id AS uniqueid, TRIM(LEADING ',' FROM tu.labels) labels, "
                 . "tu.is_read AS isread, tu.is_forward AS isforward, is_draft AS isdraft, is_done AS isdone, t.is_top AS istop, "
                 . "tu.role, tu.percent AS selfpercent, tu.forward_info AS forwardinfo, "
                 . "tu.tudu_status AS selftudustatus, tu.accept_time AS selfaccepttime, "
                 . "t.class_id AS classid, c.class_name AS classname, "
                 . "g.type AS nodetype, g.parent_tudu_id AS parentid";
        $primary = "t.tudu_id";
        $recordClass = "Dao_Td_Tudu_Record_Tudu";
        $where = array();
        $order = array();

        if (empty($condition['starttime']) || empty($condition['endtime'])) {
            return new Oray_Dao_Recordset();
        }

        if (isset($condition['target'])) {
            $where[] = 'tg.unique_id = ' . $this->_db->quote($condition['target']);
        }

        // 标签
        if (isset($condition['label'])) {
            $table  .= 'LEFT JOIN td_tudu_label tl ON tl.tudu_id = t.tudu_id AND tl.unique_id = tu.unique_id';
            $where[] = 'tl.label_id = ' . $this->_db->quote($condition['label']);
        }

        // 主题分类
        if (isset($condition['classid'])) {
            $where[] = 't.class_id = ' . $this->_db->quote($condition['classid']);
        }

        // 查找已读状态
        if (isset($condition['isread'])) {
            $where[] = 'tu.is_read = ' . ($condition['isread'] ? 1 : 0);
        }

        if (isset($condition['role'])) {
            $where[] = 'tg.role = ' . $this->_db->quote($condition['role']);
        }

        if (!empty($condition['date'])) {
            $where[] = "((t.create_time >= {$condition['date']['start']} AND t.create_time <={$condition['date']['end']}) "
                     . "OR ((t.start_time <= {$condition['date']['start']} AND t.end_time >= {$condition['date']['end']}) "
                     . "OR (t.end_time IS NULL AND t.start_time <= {$condition['date']['start']}) "
                     . "OR (t.start_time IS NULL AND t.end_time >= {$condition['date']['end']})) "
                     . "AND (t.complete_time >= {$condition['date']['end']} OR t.complete_time IS NULL))";
        }

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

        // 已经结束会议
        $where[] = '(t.type = \'task\' OR t.is_done = 0)';

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
                case 'status':
                    $key = 'status';
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

        // Query
        $sql = "SELECT $columns FROM $table $where $order";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }
}