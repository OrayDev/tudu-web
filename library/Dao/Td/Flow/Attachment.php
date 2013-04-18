<?php
/**
 * Attachment Dao
 * 工作流附件
 *
 * LICENSE
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Attachment.php 1846 2012-05-09 01:54:29Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Flow_Attachment extends Oray_Dao_Abstract
{
    /**
     * 
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Flow_Record_Attachment
     */
    public function getAttachment(array $condition, $filter = null)
    {
        $table   = "td_attachment a "
                 . "LEFT JOIN td_attach_flow af ON a.file_id = af.file_id";
        $columns = "a.file_id AS fileid, a.file_name AS filename, a.size, a.type, a.path, "
                 . "af.flow_id AS flowid, a.unique_id as uniqueid, a.create_time AS createtime";
        $where   = array();

        if (isset($condition['fileid'])) {
            $where[] = 'a.file_id = ' . $this->_db->quote($condition['fileid']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Flow_Record_Attachment', $record);
    }

    /**
     * 
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getAttachments(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_attachment a "
                 . "LEFT JOIN td_attach_flow af ON a.file_id = af.file_id";
        $columns = "a.file_id AS fileid, a.file_name AS filename, a.size, a.type, a.path, af.is_attach as isattach, "
                 . "af.flow_id AS flowid, a.unique_id as uniqueid, a.create_time AS createtime";
        $where   = array();
        $order   = array();
        $limit   = '';

        if (isset($condition['flowid'])) {
            $where[] = 'af.flow_id = ' . $this->_db->quote($condition['flowid']);
        }

        if (!empty($condition['fileid'])) {
            if (is_string($condition['fileid'])) {
                $condition['fileid'] = array($condition['fileid']);
            }

            $condition['fileid'] = array_map(array($this->_db, 'quote'), $condition['fileid']);

            $where[] = 'a.file_id IN (' . implode(',', $condition['fileid']) . ')';
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        if (!empty($filter) && array_key_exists('isattach', $filter)) {
            if (null !== $filter['isattach']) {
                $where[] = 'af.is_attach = ' . (int) $filter['isattach'];
            }
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

        return new Oray_Dao_Recordset($records, 'Dao_Td_Flow_Record_Attachment');
    }

    /**
     * 工作流是否有附件
     *
     * @param string $flowId
     * @param int    $isAttach
     * @return boolean
     */
    public function existsAttach($flowId, $isAttach = null)
    {
        $sql = 'SELECT COUNT(0) FROM td_attach_flow WHERE flow_id = ' . $this->_db->quote($flowId);

        if (null !== $isAttach) {
            $sql .= ' AND is_attach = ' . (int) $isAttach;
        }

        $count = (int) $this->_db->fetchOne($sql);

        return $count > 0;
    }

    /**
     * 增加附件关联
     *
     * @param string  $flowId
     * @param string  $fileId
     * @param boolean $isAttach
     * @return boolean
     */
    public function addAttachment($flowId, $fileId, $isAttach = true)
    {
        if (empty($flowId) || empty($fileId)) {
            return false;
        }

        $table = 'td_attach_flow';
        $bind  = array(
            'flow_id' => $flowId,
            'file_id' => $fileId,
            'is_attach' => (int) $isAttach
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除附件关联
     *
     * @param string $flowId
     * @param string $fileId
     * @return boolean
     */
    public function deleteAttachment($flowId, $fileId = null)
    {
        if (empty($flowId)) {
            return false;
        }

        $sql = "DELETE FROM td_attach_flow "
             . "WHERE flow_id = " . $this->_db->quote($flowId);

        if ($fileId) {
            $sql .= " AND file_id = " . $this->_db->quote($fileId);
        }

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }
}