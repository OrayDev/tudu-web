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
 * @version    $Id: Step.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Step extends Oray_Dao_Abstract
{

    const TYPE_EXECUTE = 0;

    const TYPE_EXAMINE = 1;

    const TYPE_CLAIM   = 2;

    /**
     *
     * @var array
     */
    static $supportTypes = array(
        self::TYPE_EXECUTE,
        self::TYPE_EXAMINE,
        self::TYPE_CLAIM
    );


    /**
     * 获取步骤节点
     *
     * @param $condition
     * @param $filter
     */
    public function getStep(array $condition, $filter = null)
    {

        $table   = 'td_tudu_step st';
        $columns = 'st.org_id AS orgid, st.tudu_id AS tuduid, st.unique_id AS uniqueid, st.step_id AS stepid, prev_step_id AS prevstepid, next_step_id AS nextstepid, '
                 . 'st.type, st.step_status AS stepstatus, st.is_done AS isdone, st.is_show AS isshow, st.percent, st.order_num AS ordernum, st.create_time AS createtime';
        $where   = array();

        if (isset($condition['tuduid'])) {
            $where[] = 'st.tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (isset($condition['stepid'])) {
            $where[] = 'st.step_id = ' . $this->_db->quote($condition['stepid']);
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'st.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['prevstepid'])) {
            $where[] = 'st.prev_step_id = ' . $this->_db->quote($condition['prevstepid']);
        }

        if (!$where) {
            return null;
        }

        if (null != $filter) {
            if (isset($filter['iscurrent'])) {
                $table  .= ' LEFT JOIN td_tudu AS t ON t.tudu_id = st.tudu_id';
                $where[] = 'st.step_id = t.step_id';
            }
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }

        return Oray_Dao::record('Dao_Td_Tudu_Record_Step', $record);
    }

    /**
     * 获取图度步骤节点列表
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getSteps(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_tudu_step';
        $columns = 'org_id AS orgid, tudu_id AS tuduid, unique_id AS uniqueid, step_id AS stepid, prev_step_id AS prevstepid, next_step_id AS nextstepid, '
                 . 'type, step_status AS stepstatus, is_done AS isdone, is_show AS isshow, percent, order_num AS ordernum, create_time AS createtime';
        $where   = array();
        $limit   = '';
        $order   = array();

        $recordClass = 'Dao_Td_Tudu_Record_Step';

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (isset($condition['stepid'])) {
            $where[] = 'step_id = ' . $this->_db->quote($condition['stepid']);
        }

        if (!$where) {
            return null;
        }

        if (is_array($filter) && isset($filter['isshow'])) {
            $where[] = 'is_show = ' . $filter['isshow'] ? 1 : 0;
        }

        $where = implode(' AND ', $where);

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'order_num';
                    break;
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

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, $recordClass);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取下一步骤节点
     *
     * @param $tuduId
     */
    public function getNextStep($tuduId)
    {
        $table   = 'td_tudu AS t '
                 . 'LEFT JOIN td_tudu_step AS st1 ON t.tudu_id = st1.tudu_id AND st1.is_current = 1 '
                 . 'LEFT JOIN td_tudu_step AS st ON t.tudu_id = st.tudu_id AND st.step_id = st1.next_step_id';
        $columns = 'st.org_id AS orgid, st.tudu_id AS tuduid, st.unique_id AS uniqueid, st.step_id AS stepid, st.prev_step_id AS prevstepid, '
                 . 'st.next_step_id AS nextstepid, st.type, st.is_done AS isdone, st.is_show AS isshow, '
                 . 'st.percent, st.to, st.order_num AS ordernum, st.create_time AS createtime';
        $where   = 't.tudu_id = ' . $this->_db->quote($tuduId);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }

        return Oray_Dao::record('Dao_Td_Tudu_Record_Step', $record);
    }

    /**
     * 获取当前上以步骤节点
     *
     * @param $tuduId
     */
    public function getPrevStep($tuduId)
    {

    }

    /**
     *
     * @param array $params
     * @return string | boolean
     */
    public function createStep(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['tuduid'])
            || empty($params['uniqueid'])
            || empty($params['stepid']))
        {
            return false;
        }

        $table = 'td_tudu_step';
        $bind  = array();

        $bind = array(
            'org_id'  => $params['orgid'],
            'tudu_id' => $params['tuduid'],
        	'unique_id'=> $params['uniqueid'],
            'step_id' => $params['stepid']
        );

        if (!empty($params['prevstepid'])) {
            $bind['prev_step_id'] = $params['prevstepid'];
        }

        if (!empty($params['nextstepid'])) {
            $bind['next_step_id'] = $params['nextstepid'];
        }

        if (isset($params['type']) && in_array($params['type'], self::$supportTypes)) {
            $bind['type'] = $params['type'];
        }

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'] ? 1 : 0;
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['step_status'] = $params['status'];
        }

        if (isset($params['percent']) && is_int($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }

        if (!empty($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        if (!empty($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['stepid'];
    }

    /**
     *
     * @param $tuduId
     * @param $stepId
     * @param $params
     */
    public function updateStep($tuduId, $stepId, array $params)
    {
        if (empty($tuduId) || empty($stepId)) {
            return false;
        }

        $table = 'td_tudu_step';
        $bind  = array();
        $where = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND '
               . 'step_id = ' . $this->_db->quote($stepId);

        if (!empty($params['prevstepid'])) {
            $bind['prev_step_id'] = $params['prevstepid'];
        }

        if (!empty($params['nextstepid'])) {
            $bind['next_step_id'] = $params['nextstepid'];
        }

        if (isset($params['type']) && in_array($params['type'], self::$supportTypes)) {
            $bind['type'] = $params['type'];
        }

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'] ? 1 : 0;
        }

        if (isset($params['percent']) && is_int($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['step_status'] = $params['status'];
        }

        if (!empty($params['to'])) {
            $bind['to'] = $params['to'];
        }

        if (!empty($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
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
     * 添加流程链表节点
     *
     * @param $tuduId
     * @param $stepId
     */
    public function addStep($tuduId, $stepId)
    {
        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($stepId);

        $sql = "UPDATE td_tudu_step SET next_step_id = {$stepId} WHERE tudu_id = {$tuduId} AND next_step_id = '^end'";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function deleteStep($tuduId, $stepId)
    {
        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($stepId);

        $sqls = array();
        $sqls[] = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
        $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true ;
    }

    /**
     * 删除所有步骤
     *
     * @param $tuduId
     */
    public function deleteSteps($tuduId)
    {
        $tuduId = $this->_db->quote($tuduId);

        $sqls = array();
        $sqls[] = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId}";
        $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId}";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true ;
    }

    /**
     * 删除未执行的步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function deleteNextSteps($tuduId, $stepId, $isCancel = false)
    {
        if ($stepId == '^end' || $stepId == '^trunk') {
            return true;
        }

        if ($stepId == '^head') {
            $step = $this->getStep(array('tuduid' => $tuduId, 'prevstepid' => '^head'));
        } else {
            $step = $this->getStep(array('tuduid' => $tuduId, 'stepid' => $stepId));
        }

        if (!$step) {
            return true;
        }

        $sqls = array();

        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($step->stepId);

        if ($step->type == self::TYPE_EXAMINE) {
            $sqls[] = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId} AND step_id = {$stepId} AND status <> 2";

            if ($step->prevStepId == '^head') {
                $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
                $sqls[] = "UPDATE td_tudu_step SET prev_step_id = '^head' WHERE tudu_id = {$tuduId} AND step_id = '{$step->nextStepId}'";
            }
            //$sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
        } else {
            if ($isCancel) {
                $sqls[] = "UPDATE td_tudu_step SET is_done = 1, prev_step_id = '^trunk' WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
                $sqls[] = "UPDATE td_tudu_step_user SET status = 4 WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
            } else {
                $sqls[] = "UPDATE td_tudu_step SET is_done = 1 WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
            }

            if ($step->nextStepId && $step->nextStepId != '^end' && $step->nextStepId != '^trunk') {
                $sqls[] = "UPDATE td_tudu_step SET prev_step_id = '{$step->prevStepId}' WHERE tudu_id = {$tuduId} AND step_id = '{$step->nextStepId}'";
            }
            if ($step->prevStepId && $step->prevStepId != '^head' && $step->prevStepId != '^trunk') {
                $sqls[] = "UPDATE td_tudu_step SET next_step_id = '{$step->nextStepId}' WHERE tudu_id = {$tuduId} AND step_id = '{$step->prevStepId}'";
            }
        }

        $sqls[] = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId} AND step_id IN ( "
                . "SELECT step_id FROM td_tudu_step WHERE tudu_id = {$tuduId} AND order_num > {$step->orderNum})";
        $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId} AND order_num > {$step->orderNum}";
        $sqls[] = "UPDATE td_tudu_step SET next_step_id = '^end' WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql);
            }

            return $step;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除后续步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function removeNextSteps($tuduId, $stepId, $orderNum = null)
    {

        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($stepId);

        $sqls = array();
        if ($stepId == '^head') {
            $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId}";

        } else {
            $sqls[] = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId} AND step_id IN ( "
                    . "SELECT step_id FROM td_tudu_step WHERE tudu_id = {$tuduId} AND order_num > {$orderNum})";
            $sqls[] = "DELETE FROM td_tudu_step WHERE tudu_id = {$tuduId} AND order_num > {$orderNum}";
            $sqls[] = "UPDATE td_tudu_step SET next_step_id = '^end' WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
        }

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
     *
     * @param string $tuduId
     * @param int $orderNum
     * @param int $offset
     * @return boolean
     */
    public function updateNextStepsOrder($tuduId, $orderNum, $offset = 0)
    {
        if (!is_int($offset) || $offset == 0 || !is_int($orderNum)) {
            return false;
        }

        $tuduId = $this->_db->quote($tuduId);

        $sql= "UPDATE td_tudu_step SET order_num = order_num + {$offset} WHERE order_num >= {$orderNum}";

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
     * @param $stepId
     */
    public function cancelStep($tuduId, $stepId)
    {
        $step = $this->getStep(array('tuduid' => $tuduId, 'stepid' => $stepId));

        if (!$step) {
            return true;
        }

        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($stepId);

        $sqls[] = "UPDATE td_tudu_step_user SET status = 4 WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";
        $sqls[] = "UPDATE td_tudu_step SET prev_step_id = {$step->prevTuduId} WHERE tudu_id = {$tuduId} AND step_id = {$step->nextStepId}";
        $sqls[] = "UPDATE td_tudu_step SET next_step_id = {$step->prevTuduId} WHERE tudu_id = {$tuduId} AND step_id = {$step->prevStepId}";

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
     * 获取当前用户执行步骤信息
     *
     * @param $tuduId
     * @param $stepId
     * @param $uniqueId
     */
    public function getCurrentStep($tuduId, $stepId, $uniqueId)
    {
        $sql = 'SELECT s.tudu_id AS tuduid, s.step_id AS stepid, su.unique_id as uniqueid, su.user_info as userinfo, '
             . 'su.process_index AS processindex, su.percent, su.status, s.type, s.prev_step_id as prevstepid, '
             . 's.next_step_id as nextstepid, s.order_num AS ordernum '
             . 'FROM td_tudu_step AS s '
             . 'LEFT JOIN td_tudu_step_user AS su ON su.tudu_id = s.tudu_id '
             . 'AND su.step_id = s.step_id AND su.unique_id = ' . $this->_db->quote($uniqueId) . ' '
             . 'WHERE s.tudu_id = ' . $this->_db->quote($tuduId) . ' '
             . 'AND s.step_id = ' . $this->_db->quote($stepId) . ' '
             . 'LIMIT 1';

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return $record;
        } catch(Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 判断是否正在进行的步骤（当前用户）
     *
     * @param string $uniqueId
     * @param string $tuduId
     * @param string $stepId
     */
    public function existsDoingStep($uniqueId, $tuduId, $stepId)
    {
        $sql = 'SELECT COUNT(0) FROM td_tudu_step_user '
             . 'WHERE unique_id = ' . $this->_db->quote($uniqueId)
             . ' AND tudu_id = ' . $this->_db->quote($tuduId)
             . ' AND step_id = ' . $this->_db->quote($stepId)
             . ' AND status = 1';

        $count = (int) $this->_db->fetchOne($sql);

		return $count > 0;
    }

    /**
     *
     * @param $stepId
     * @return array
     */
    public function getUsers($tuduId, $stepId)
    {
        $table   = 'td_tudu_step_user';
        $columns = 'tudu_id AS tuduid, step_id AS stepid, unique_id AS uniqueid, user_info AS userinfo, '
                 . 'process_index AS processindex, percent, status';
        $where   = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND '
                 . 'step_id = ' . $this->_db->quote($stepId);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} ORDER BY process_index ASC";

        try {
            $_records = $this->_db->fetchAll($sql);

            $records = array();
            foreach ($_records as $record) {
                $records[$record['uniqueid']] = $record;
            }

            return $records;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param $stepId
     * @param $uniqueId
     * @return array
     */
    public function getUser($tuduId, $stepId, $uniqueId)
    {
        $table   = 'td_tudu_step_user';
        $columns = 'tudu_id AS tuduid, step_id AS stepid, unique_id AS uniqueid, user_info AS userinfo, '
                 . 'process_index AS processindex, percent, status';
        $where   = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND '
                 . 'step_id = ' . $this->_db->quote($stepId) . ' AND '
                 . 'unique_id = ' . $this->_db->quote($uniqueId);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $row = $this->_db->fetchRow($sql);

            return $row;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param $tuduId
     * @return array
     */
    public function getTuduStepUsers($tuduId)
    {
        $sql = 'SELECT su.tudu_id AS tuduid, su.step_id AS stepid, su.unique_id AS uniqueid, '
             . 'su.user_info AS userinfo, process_index AS processindex, su.percent, su.status, '
             . 's.type, s.is_done AS isdone, s.order_num AS ordernum, s.step_status AS stepstatus '
             . 'FROM td_tudu_step_user AS su '
             . 'INNER JOIN td_tudu_step s ON su.step_id = s.step_id AND su.tudu_id = s.tudu_id '
             . 'WHERE su.tudu_id = ' . $this->_db->quote($tuduId) . ' '
             . 'ORDER BY s.order_num ASC, su.process_index ASC';

        try {
            $records = $this->_db->fetchAll($sql);

            return $records;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param $tuduId
     * @param $stepId
     * @param $uniqueId
     */
    public function deleteUsers($tuduId, $stepId, $uniqueId = null)
    {
        $tuduId = $this->_db->quote($tuduId);
        $stepId = $this->_db->quote($stepId);

        $sql = "DELETE FROM td_tudu_step_user WHERE tudu_id = {$tuduId} AND step_id = {$stepId}";

        if (null !== $uniqueId) {
            if (is_array($uniqueId)) {
                $uniqueId = array_map(array($this->_db, 'quote'), $uniqueId);

                $sql .= ' AND unique_id IN (' . implode(',', $uniqueId) . ')';

            } else {
                $sql .= ' AND unique_id = ' . $this->_db->quote($uniqueId);
            }
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
     *
     * @param array $params
     * @return boolean
     */
    public function addUser(array $params)
    {
        if (empty($params['tuduid'])
            || empty($params['stepid'])
            || empty($params['uniqueid']))
        {
            return false;
        }

        $table = 'td_tudu_step_user';
        $bind  = array(
            'tudu_id' => $params['tuduid'],
            'step_id' => $params['stepid'],
            'unique_id' => $params['uniqueid']
        );

        if (isset($params['userinfo'])) {
            $bind['user_info'] = $params['userinfo'];
        }

        if (isset($params['processindex'])) {
            $bind['process_index'] = $params['processindex'];
        }

        if (isset($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

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
     *
     * @param $tuduId
     * @param $stepId
     * @param $filter
     */
    public function removeUsers($tuduId, $stepId, array $filter)
    {
        $where = array();

        $where[] = 'tudu_id = ' . $this->_db->quote($tuduId);
        $where[] = 'step_id = ' . $this->_db->quote($stepId);

        if (isset($filter['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($filter['uniqueid']);
        }

        if (isset($filter['status'])) {
            $where[] = 'status <= ' . (int) $filter['status'];
        }

        $where = implode(' AND ', $where);

        $sql = "DELETE FROM td_tudu_step_user WHERE {$where}";

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
     * @param array $params
     * @return boolean
     */
    public function updateUser($tuduId, $stepId, $uniqueId, array $params)
    {
        if (empty($tuduId)
            || empty($stepId)
            || empty($uniqueId))
        {
            return false;
        }

        $table = 'td_tudu_step_user';
        $bind  = array();
        $where = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND '
               . 'step_id = ' . $this->_db->quote($stepId) . ' AND '
               . 'unique_id = ' . $this->_db->quote($uniqueId);

        if (isset($params['userinfo'])) {
            $bind['user_info'] = $params['userinfo'];
        }

        if (isset($params['processindex'])) {
            $bind['process_index'] = $params['processindex'];
        }

        if (isset($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
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
     *
     * @param string $to
     */
    public static function formatAddress($to)
    {
        if (!$to) {
            return array();
        }

        $arr = explode("\n", $to);
        $ret = array();

        foreach ($arr as $item) {
            $item = explode(' ' , $item, 3);
            if (count($item) < 3) {
                continue ;
            }

            $index   = (int) $item[0];
            $address = $item[1];
            $name    = $item[2];

            $ret[$index] = array('email' => $address, 'name' => $name);
        }

        return $ret;
    }

    /**
     * 获取步骤ID
     *
     */
    public static function getStepId()
    {
        return 'ST-' . base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}