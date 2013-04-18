<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Rule
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Rule.php 2605 2013-01-05 10:01:22Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Rule
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Rule_Rule extends Oray_Dao_Abstract
{
    public function init()
    {
        Dao_Td_Rule_Record_Rule::setDao($this);
    }

    /**
     * 读取图度规则
     *
     * @param $condition
     * @param $filter
     */
    public function getRule(array $condition, $filter = null)
    {
        $table   = 'td_rule';
        $columns = 'rule_id AS ruleid, unique_id AS uniqueid, description, operation, mail_remind AS mailremind, value, is_valid AS isvalid';
        $where   = array();

        if (!empty($condition['ruleid'])) {
            $where[] = 'rule_id = ' . $this->_db->quote($condition['ruleid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (empty($where)) {
            return null;
        }

        if (isset($filter['isvalid'])) {
            $where[] = 'is_valid = ' . ($filter['isvalid'] ? 1 : 0);
        }

        $where = implode(' AND ' , $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Rule_Record_Rule', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return null;
        }
    }

    /**
     * 读取图度规则（多条）
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getRules(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_rule';
        $columns = 'rule_id AS ruleid, unique_id AS uniqueid, description, operation, mail_remind AS mailremind, value, is_valid AS isvalid';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        if (isset($filter['isvalid'])) {
            $where[] = 'is_valid = ' . ($filter['isvalid'] ? 1 : 0);
        }

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

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, 'Dao_Td_Rule_Record_Rule');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 创建图度规则
     *
     * @param array $params
     * @return boolean
     */
    public function createRule(array $params)
    {
        if (empty($params['ruleid']) || empty($params['uniqueid'])) {
            return false;
        }

        $table = 'td_rule';
        $bind  = array();

        $bind['rule_id']   = $params['ruleid'];
        $bind['unique_id'] = $params['uniqueid'];

        if (!empty($params['description'])) {
            $bind['description'] = $params['description'];
        }

        if (!empty($params['operation'])) {
            $bind['operation'] = $params['operation'];
        }

        if (!empty($params['mailremind'])) {
            $bind['mail_remind'] = $params['mailremind'];
        }

        if (!empty($params['value'])) {
            $bind['value'] = $params['value'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = ($params['isvalid'] ? 1 : 0);
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }

        return $bind['rule_id'];
    }

    /**
     * 更新图度规则
     *
     * @param string $ruleId
     * @param string $params
     * @return boolean
     */
    public function updateRule($ruleId, array $params)
    {
        if (empty($ruleId)) {
            return false;
        }

        $table = 'td_rule';
        $where = 'rule_id = ' . $this->_db->quote($ruleId);
        $bind  = array();

        if (!empty($params['description'])) {
            $bind['description'] = $params['description'];
        }

        if (!empty($params['operation'])) {
            $bind['operation'] = $params['operation'];
        }

        if (isset($params['mailremind'])) {
            $bind['mail_remind'] = $params['mailremind'];
        }

        if (isset($params['value'])) {
            $bind['value'] = $params['value'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = ($params['isvalid'] ? 1 : 0);
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
     * 删除图度规则
     *
     * @param $ruleId
     * @return boolean
     */
    public function deleteRule($ruleId)
    {
        $ruleId = $this->_db->quote($ruleId);

        $sqls = array();
        $sqls[] = "DELETE FROM td_rule_filter WHERE rule_id = {$ruleId}";
        $sqls[] = "DELETE FROM td_rule WHERE rule_id = {$ruleId}";

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
     * 读取图度规则过滤条件
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     * @return array
     */
    public function getFilters(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_rule_filter';
        $columns = 'filter_id AS filterid, rule_id AS ruleid, what, type, value, is_valid AS isvalid';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['ruleid'])) {
            $where[] = 'rule_id = ' . $this->_db->quote($condition['ruleid']);
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        if (isset($filter['isvalid'])) {
            $where[] = 'is_valid = ' . ($filter['isvalid'] ? 1 : 0);
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
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

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, 'Dao_Td_Rule_Record_Filter');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 创建图度规则过滤条件
     *
     * @param array $params
     * @return boolean|Ambigous <>
     */
    public function addFilter(array $params)
    {
        if (empty($params['ruleid']) || empty($params['filterid']) || empty($params['value'])) {
            return false;
        }

        $table = 'td_rule_filter';
        $bind  = array();

        $bind['rule_id']   = $params['ruleid'];
        $bind['filter_id'] = $params['filterid'];
        $bind['value']     = $params['value'];
        if (!empty($params['what'])) {
            $bind['what'] = $params['what'];
        }

        if (!empty($params['type'])) {
            $bind['type'] = $params['type'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = ($params['isvalid'] ? 1 : 0);
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }

        return $params['filterid'];
    }

    /**
     * 更新图度规则过滤条件
     *
     * @param string $filterId
     * @param array  $params
     * @return boolean
     */
    public function updateFilter($filterId, array $params)
    {
        if (!$filterId) {
            return false;
        }

        $table = 'td_rule_filter';
        $bind  = array();
        $where = 'filter_id = ' . $this->_db->quote($filterId);

        if (!empty($params['what'])) {
            $bind['what'] = $params['what'];
        }

        if (!empty($params['type'])) {
            $bind['type'] = $params['type'];
        }

        if (isset($params['value'])) {
            $bind['value'] = $params['value'];
        }

        if (isset($params['isvalid'])) {
            $bind['is_valid'] = ($params['isvalid'] ? 1 : 0);
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
     * 删除图度规则过滤条件
     *
     * @param string $ruleId
     * @param string $filterId
     * @return boolean
     */
    public function removeFilter($ruleId, $filterId)
    {
        $sql = 'DELETE FROM td_rule_filter WHERE rule_id = ' . $this->_db->quote($ruleId) . ' AND '
             . 'filter_id = ' . $this->_db->quote($filterId);

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
     * @param $ruleId
     * @param $filter
     */
    public function getRuleById($ruleId, $filter = null)
    {
        return $this->getRule(array('ruleid' => $ruleId), $filter);
    }

    /**
     *
     * @param $ruleId
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getFiltersByRuleId($ruleId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getFilters(array('ruleid' => $ruleId), $filter, $sort, $maxCount);
    }

    /**
     *
     * @param $uniqueId
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getRulesByUniqueId($uniqueId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getRules(array('uniqueid' => $uniqueId), $filter, $sort, $maxCount);
    }

    /**
     * 生成规则ID
     *
     * @return string
     */
    public static function getRuleId()
    {
        $ruleId = base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
        return $ruleId;
    }

    /**
     *
     * @return string
     */
    public static function getFilterId()
    {
        $filterId = base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
        return $filterId;
    }
}