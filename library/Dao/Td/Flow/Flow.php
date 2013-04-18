<?php
/**
 * Flow Dao
 * 工作流
 *
 * LICENSE
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Flow.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Flow_Flow extends Oray_Dao_Abstract
{
    /**
     * SELECT
     * flow_id AS flowid, org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, subject, description,
     * avaliable, is_valid AS isvalid, cc, elapsed_time AS elapsedtime, content, steps, create_time AS createtime
     * FROM td_flow
     * WHERE flow_id = :flowid AND unique_id = :uniqueid
     * LIMIT 0, 1
     *
     * @param array $condition
     * @param array $filter
     * @return boolean|NULL
     */
    public function getFlow(array $condition, $filter = null)
    {
        if (empty($condition['flowid'])) {
            return false;
        }

        $table   = 'td_flow';
        $columns = 'flow_id AS flowid, org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, subject, description, class_id as classid, '
                 . 'avaliable, is_valid AS isvalid, cc, elapsed_time AS elapsedtime, content, steps, create_time AS createtime';
        $where   = array();

        $where[] = 'flow_id = ' . $this->_db->quote($condition['flowid']);

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($filter) && array_key_exists('isvalid', $filter)) {
            if (null !== $filter['isvalid']) {
                $where[] = 'is_valid = ' . (int) $filter['isvalid'];
            }
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Flow_Record_Flow', $record);
    }

    /**
     * SELECT
     * f.flow_id AS flowid, f.org_id AS orgid, f.board_id AS boardid, f.unique_id AS uniqueid, f.subject, f.description,
     * f.avaliable, f.is_valid AS isvalid, f.create_time AS createtime, b.parent_board_id AS parentid
     * FROM td_flow f
     * LEFT JOIN td_board b ON f.board_id = b.board_id AND f.org_id = b.org_id
     * WHERE f.org_id = ? [AND f.board_id = ?]
     * ORDER BY ??
     * LIMIT ?
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     */
    public function getFlows(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_flow f '
                 . 'LEFT JOIN td_board b ON f.board_id = b.board_id AND f.org_id = b.org_id ';
        $columns = 'f.flow_id AS flowid, f.org_id AS orgid, f.board_id AS boardid, f.unique_id AS uniqueid, f.subject, f.description, class_id as classid, '
                 . 'f.avaliable, f.is_valid AS isvalid, f.create_time AS createtime, b.parent_board_id AS parentid';
        $recordClass = "Dao_Td_Flow_Record_Flow";
        $where   = array();
        $order   = array();
        $limit   = '';

        if (isset($condition['orgid'])) {
            $where[] = 'f.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['boardid'])) {
            $where[] = 'f.board_id = ' . $this->_db->quote($condition['boardid']);
        }

        if (isset($condition['keyword'])) {
            $keyword = $this->_db->quote("%{$condition['keyword']}%");
            $like = array();
            $like[] = "f.subject LIKE {$keyword}";
            $like[] = "f.description LIKE {$keyword}";

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        if (isset($condition['uniqueid'])) {
            $table   .= 'LEFT JOIN td_flow_favor ff ON ff.flow_id = f.flow_id AND ff.unique_id = ' . $this->_db->quote($condition['uniqueid']);
            $columns .= ', ff.weight';
        }

        if (!empty($filter) && array_key_exists('isvalid', $filter)) {
            if (null !== $filter['isvalid']) {
                $where[] = 'f.is_valid = ' . (int) $filter['isvalid'];
            }
        } else {
            $where[] = 'is_valid = 1';
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
                    $key = 'f.create_time';
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

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     * 此方法目前没有使用
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     */
    public function getFlowPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'td_flow';
        $columns = 'flow_id AS flowid, org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, subject, description, class_id as classid, '
                 . 'is_valid AS isvalid, create_time AS createtime';
        $where   = array();
        $order   = array();
        $recordClass = "Dao_Td_Flow_Record_Flow";

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['boardid'])) {
            $where[] = 'board_id = ' . $this->_db->quote($condition['boardid']);
        }

        if (isset($condition['isvalid'])) {
            $where[] = 'is_valid = ' . $this->_db->quote($condition['isvalid']);
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
     *
     * @param string $flowId
     * @param array  $filter
     * @return boolean
     */
    public function getFlowById($flowId, $filter = null)
    {
        return $this->getFlow(array('flowid' => $flowId), $filter);
    }

    /**
     * 工作流是否已使用
     *
     * @param string $flowId
     */
    public function isValidFlow($flowId, $orgId = null)
    {
        $sql = 'SELECT COUNT(0) FROM td_tudu WHERE flow_id = ' . $this->_db->quote($flowId);

        if (!empty($orgId)) {
            $sql .= ' AND org_id = ' . $this->_db->quote($orgId);
        }

        $count = (int) $this->_db->fetchOne($sql);
        return $count > 0;
    }

    /**
     * 创建工作流
     */
    public function createFlow(array $params)
    {
        if (empty($params['flowid'])
            || empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['uniqueid'])
            || !array_key_exists('subject', $params))
        {
            return false;
        }

        $table = 'td_flow';
        $bind = array(
            'flow_id'   => $params['flowid'],
            'org_id'    => $params['orgid'],
            'board_id'  => $params['boardid'],
            'unique_id' => $params['uniqueid'],
            'subject'   => $params['subject']
        );

        if (!empty($params['description'])) {
            $bind['description'] = $params['description'];
        }

        if (!empty($params['avaliable'])) {
            $bind['avaliable'] = $params['avaliable'];
        }

        if (!empty($params['isvalid'])) {
            $bind['is_valid'] = (int) $params['isvalid'];
        }

        if (!empty($params['cc'])) {
            $bind['cc'] = $params['cc'];
        }

        if (!empty($params['elapsedtime'])) {
            $bind['elapsed_time'] = (int) $params['elapsedtime'];
        }

        if (!empty($params['content'])) {
            $bind['content'] = $params['content'];
        }

        if (isset($params['steps'])) {
            $bind['steps'] = $params['steps'];
        }

        if (!empty($params['createtime'])) {
            $bind['create_time'] = !empty($params['createtime']) ? (int) $params['createtime'] : time();
        }

        if (isset($params['classid'])) {
            $bind['class_id'] = $params['classid'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['flowid'];
    }

    /**
     * 更新工作流
     */
    public function updateFlow($flowId, array $params)
    {
        if (empty($flowId)) {
            return false;
        }

        $table = 'td_flow';
        $bind  = array();
        $where = "flow_id = " . $this->_db->quote($flowId);

        if (isset($params['subject'])) {
            $bind['subject'] = $params['subject'];
        }

        if (array_key_exists('description', $params)) {
            $bind['description'] = $params['description'];
        }

        if (isset($params['avaliable'])) {
            $bind['avaliable'] = $params['avaliable'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = (int) $params['isvalid'];
        }

        if (array_key_exists('cc', $params)) {
            $bind['cc'] = $params['cc'];
        }

        if (isset($params['steps'])) {
            $bind['steps'] = $params['steps'];
        }

        if (array_key_exists('elapsedtime', $params)) {
            $bind['elapsed_time'] = $params['elapsedtime'];
        }

        if (isset($params['content'])) {
            $bind['content'] = $params['content'];
        }

        if (array_key_exists('classid', $params)) {
            $bind['class_id'] = $params['classid'];
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
     * 删除工作流
     */
    public function deleteFlow($orgId, $flowId)
    {
        if (empty($orgId) || empty($flowId)) {
            return false;
        }

        $sql = 'DELETE FROM td_flow WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'flow_id = ' . $this->_db->quote($flowId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加常用
     *
     * @param $orgId
     * @param $flowId
     * @param $uniqueId
     */
    public function addFavor($flowId, $uniqueId)
    {
        if (empty($flowId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_flow_favor';
        $bind  = array(
            'flow_id' => $flowId,
            'unique_id' => $uniqueId,
            'weight' => 1,
            'update_time' => time()
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
     * 添加常用
     *
     * @param $orgId
     * @param $flowId
     * @param $uniqueId
     */
    public function updateFavor($flowId, $uniqueId, array $params)
    {
        if (empty($flowId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_flow_favor';
        $bind  = array();
        $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' '
               . 'flow_id = ' . $this->_db->quote($flowId);

        if (!empty($params['weight'])) {
            $bind['weight'] = $params['weight'];
        }

        if (!empty($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
        }

        try {
            $this->_db->insert($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取常用工作流信息
     *
     * @param $flowId
     * @param $uniqueId
     */
    public function getFavor($flowId, $uniqueId)
    {
        $sql = "SELECT unique_id AS uniqueid, flow_id AS flowid, weight, update_time AS updatetime "
             . "FROM td_flow_favor "
             . "WHERE flow_id = " . $this->_db->quote($flowId) . ' AND '
             . "unique_id = " . $this->_db->quote($flowId);

        try {
            $record = $this->_db->fetchRow($sql);

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
     * 格式化参与人员群组
     *
     * return
     * array(groupoid => name ...)
     *
     * @param string $str
     * @return array
     */
    public static function formatAvaliable($str)
    {
        $ret = array();

        if (!$str) return $ret;

        return explode("\n", trim($str, "\n"));
    }

    /**
     * 生成工作流步骤ID
     */
    public static function getStepId()
    {
        return 'F-' . base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }

    /**
     * 获取工作流ID
     */
    public static function getFlowId()
    {
        return base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
    }
}