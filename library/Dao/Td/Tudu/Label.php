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
 * @version    $Id: Label.php 2590 2012-12-31 10:04:53Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Label extends Oray_Dao_Abstract
{
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
     * Get labels
     *
     * SEELCT unique_id AS uniqueid, label_id AS labelid, label_alias AS labelalias,
     * total_num AS total_num, unread_num AS unreadnum,
     * is_system AS issystem, color, bgcolor, sync_time AS synctime
     * FORM td_label
     * WHERE unique_id = ?
     * ORDER BY label_name ASC
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getLabels(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_label";
        $columns = "unique_id AS uniqueid, label_id AS labelid, label_alias AS labelalias, "
                 . "total_num AS totalnum, unread_num AS unreadnum, is_show AS isshow, display, "
                 . "is_system AS issystem, color, bgcolor, sync_time AS synctime, order_num AS ordernum";
        $where   = array();
        $order   = array();
        $limit   = "";

        // $condition ...

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($filter['issystem'])) {
            $where[] = 'is_system = ' . $filter['issystem'] ? 1 : 0;
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        // $filter ...

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'alias':
                    $key = 'label_alias';
                    break;
                case 'issystem':
                    $key = 'is_system';
                    break;
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

        $sql = "SELECT $columns FROM $table $where $order $limit";
        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Label');
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getLabel(array $condition, $filter = null)
    {
        $table   = "td_label";
        $columns = "unique_id AS uniqueid, label_id AS labelid, label_alias AS labelalias, "
                 . "total_num AS totalnum, unread_num AS unreadnum, is_show AS isshow, "
                 . "is_system AS issystem, color, bgcolor, sync_time AS synctime, order_num AS ordernum";
        $where   = array();

        // $condition ...

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($filter['labelid'])) {
            $where[] = 'label_id = ' . $this->_db->quote($filter['labelid']);
        }

        if (empty($where)) {
            return null;
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT $columns FROM $table $where LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Tudu_Record_Label', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * Get labels
     *
     * @param string $uniqueId
     * @param array  $filter
     * @param array  $sort
     * @param int    $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getLabelsByUniqueId($uniqueId, $filter = null, $sort = 'alias ASC', $maxCount = null)
    {
        return $this->getLabels(array('uniqueid' => $uniqueId), $filter, $sort, $maxCount);
    }

    /**
     *
     * @param array $condition
     * @return int
     */
    public function getLabelCount(array $condition)
    {
        $table = 'td_label';
        $where = array();

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['issystem'])) {
            $where[] = 'is_system = ' . ($condition['issystem'] ? 1 : 0);
        }

        if (!$where) {
            return false;
        }

        $where = implode(' AND ', $where);

        $sql   = "SELECT COUNT(0) FROM {$table} WHERE {$where}";
        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * Create label
     *
     * @param $params
     * @return int|false
     */
    public function createLabel(array $params)
    {
        if (empty($params['uniqueid'])
            || !isset($params['labelid'])
            || !isset($params['labelalias'])) {
            return false;
        }

        $table = "td_label";
        $bind  = array(
            'unique_id' => $params['uniqueid'],
            'label_id' => $params['labelid'],
            'label_alias' => $params['labelalias'],
            'is_system' => empty($params['issystem']) ? 0 : 1
            );

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'];
        }
        if (isset($params['color'])) {
            $bind['color'] = $params['color'];
        }
        if (isset($params['bgcolor'])) {
            $bind['bgcolor'] = $params['bgcolor'];
        }
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }
        if (isset($params['display']) && is_int($params['display'])) {
            $bind['display'] = $params['display'];
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
     * Update label
     *
     * @param string $uniqueId
     * @param string $labelId
     * @param array $params
     * @return boolean
     */
    public function updateLabel($uniqueId, $labelId, array $params)
    {
        // 系统标签禁止修改
        if (!is_string($labelId) || $labelId[0] === '^') {
            return false;
        }

        $table = "td_label";
        $bind  = array();
        $where = "unique_id = " . $this->_db->quote($uniqueId)
               . " AND label_id = " . $this->_db->quote($labelId);

        if (isset($params['labelalias'])) {
            $bind['label_alias'] = $params['labelalias'];
        }

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'];
        }

        if (isset($params['color'])) {
            $bind['color'] = $params['color'];
        }

        if (isset($params['bgcolor'])) {
            $bind['bgcolor'] = $params['bgcolor'];
        }

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
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
     * 更新标签显示方式
     *
     * @param string $uniqueId
     * @param string $labelId
     * @param string $type
     * @return boolean
     */
    public function showLabel($uniqueId, $labelId, $type)
    {
        //所有图度、图度箱不允许此操作
        if ($labelId == '^all' || $labelId == '^i') {
            return false;
        }

        $table = "td_label";
        $bind  = array('is_show' => $type);
        $where = "unique_id = " . $this->_db->quote($uniqueId)
               . " AND label_id = " . $this->_db->quote($labelId);

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
     * @param string $uniqueId
     * @param string $labelId
     * @param string $type
     * @return boolean
     */
    public function sortLabel($uniqueId, $labelId, $type, $issytem = false)
    {
        $labels = $this->getLabelsByUniqueId($uniqueId, array('issystem' => $issytem), array('ordernum' => 'DESC'))->toArray();

        $count = count($labels);
        $index = 0;
        $target = null;
        $exchange = null;

        if ($count <= 1) {
            return false;
        }

        foreach ($labels as $idx => $label) {
            if ($issytem) {
                if ($label['labelalias'] == $labelId) {
                    $index  = $idx;
                    $target = $label;
                    break;
                }
            } else {
                if ($label['labelid'] == $labelId) {
                    $index  = $idx;
                    $target = $label;
                    break;
                }
            }
        }

        if (($type == 'up' && $index == 0) || ($type == 'down' && $index == $count - 1)) {
            return false;
        }

        $exchangeIndex = $type == 'up' ? $index - 1 : $index + 1;
        $exchange = $labels[$exchangeIndex];

        try {
            if ($issytem) {
                $this->_db->update(
                    'td_label',
                    array('order_num' => $exchange['ordernum']),
                    'unique_id = ' . $this->_db->quote($uniqueId) . ' AND label_alias = ' . $this->_db->quote($labelId)
                );

                $this->_db->update(
                    'td_label',
                    array('order_num' => $target['ordernum']),
                    'unique_id = ' . $this->_db->quote($uniqueId) . ' AND label_alias = ' . $this->_db->quote($exchange['labelalias'])
                );
            } else {
                $this->updateLabel($uniqueId, $labelId, array(
                    'ordernum' => $exchange['ordernum']
                ));

                $this->updateLabel($uniqueId, $exchange['labelid'], array(
                    'ordernum' => $target['ordernum']
                ));
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * Delete label
     *
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function deleteLabel($uniqueId, $labelId)
    {
        // 系统标签禁止删除
        if (!is_string($labelId) || $labelId[0] === '^') {
            return false;
        }

        $uniqueId = $this->_db->quote($uniqueId);
        $labelId  = $this->_db->quote($labelId);

        $sqls[] = "DELETE FROM td_tudu_label WHERE unique_id = $uniqueId AND label_id = $labelId";
        $sqls[] = "DELETE FROM td_label WHERE unique_id = $uniqueId AND label_id = $labelId";

        try {
            foreach($sqls as $sql) {
                $this->_db->query($sql);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 整理标签排序
     *
     * @param $uniqueId
     */
    public function tidyLabelSort($uniqueId)
    {
        $uniqueId = $this->_db->quote($uniqueId);
        $sql = 'SELECT label_id AS labelid, order_num AS ordernum FROM td_label '
             . 'WHERE unique_id = ' . $uniqueId . ' AND is_system = 0 ORDER BY order_num DESC';

        $records = $this->_db->fetchAll($sql);

        $count = count($records);
        try {
            foreach ($records as $index => $record) {
                $this->_db->update(
                    'td_label',
                    array('order_num' => $count - $index),
                    'unique_id = ' . $uniqueId . ' AND label_id = ' . $this->_db->quote($record['labelid'])
                );
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }

        return true;
    }

    /**
     * 图度数自增
     *
     * @param $uniqueId
     * @param $labelId
     * @param $value
     * @return boolean
     */
    public function increment($uniqueId, $labelId, $value = 1)
    {
        if ($value < 1) return false;
        $sql = 'UPDATE td_label SET total_num = total_num + ' . (int) $value
             . ' WHERE unique_id = ' . $this->_db->quote($uniqueId)
             . ' AND label_id = ' . $this->_db->quote($labelId);
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * 图度数自减
     *
     * @param $uniqueId
     * @param $labelId
     * @param $value
     * @return boolean
     */
    public function decrement($uniqueId, $labelId, $value = 1)
    {
        if ($value < 1) return false;
        $sql = 'UPDATE td_label SET total_num = total_num - ' . (int) $value
             . ' WHERE unique_id = ' . $this->_db->quote($uniqueId)
             . ' AND label_id = ' . $this->_db->quote($labelId);
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }


    /**
     * 统计标签图度数
     *
     * @param string $uniqueId
     * @param string $labelId
     */
    public function calculateLabel($uniqueId, $labelId)
    {
        $sql = "call sp_td_calculate_label(" . $this->_db->quote($uniqueId) . "," . $this->_db->quote($labelId) . ")";
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取标签ID
     *
     *
     * @return string
     */
    public static function getLabelId()
    {
        $labelId = base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
        return $labelId;
    }
}