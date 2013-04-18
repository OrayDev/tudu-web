<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Note
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Note.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Note
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Note_Note extends Oray_Dao_Abstract
{
    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Note_Record_Note
     */
    public function getNote(array $condition, $filter = null)
    {
        $table   = 'td_note';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, note_id AS noteid, tudu_id AS tuduid, content, color, status, '
                 . 'create_time AS createtime, update_time AS updatetime';
        $where   = array();

        if (isset($condition['noteid'])) {
            $where[] = 'note_id = ' . $this->_db->quote($condition['noteid']);
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (!$where) {
            return null;
        }

        if (is_array($filter) && array_key_exists('status', $filter)) {
            if (null !== $filter['status']) {
                $where[] = 'status = ' . (int) $filter['status'];
            }
        } else {
            $where[] = 'status = 1';
        }

        // WHERE
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Note_Record_Note', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getNotes(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_note AS n '
                 . 'LEFT JOIN td_tudu AS t ON n.tudu_id = t.tudu_id';
        $columns = 'n.org_id AS orgid, n.unique_id AS uniqueid, n.note_id AS noteid, n.content, n.color, n.status, '
                 . 'n.tudu_id AS tuduid, t.subject, n.create_time AS createtime, n.update_time AS updatetime';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (isset($condition['orgid'])) {
            $where[] = 'n.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['noteid'])) {
            $where[] = 'n.note_id = ' . $this->_db->quote($condition['noteid']);
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'n.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!$where) {
            return null;
        }

        if (is_array($filter) && array_key_exists('status', $filter)) {
            if (null !== $filter['status']) {
                $where[] = 'n.status = ' . (int) $filter['status'];
            }
        } else {
            $where[] = 'n.status = 1';
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'updatetime':
                    $key = 'n.update_time';
                    break;
                case 'createtime':
                    $key = 'n.create_time';
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

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";
        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, 'Dao_Td_Note_Record_Note');
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * getNotes 的快捷调用方式
     *
     * @param string $uniqueId
     * @param array  $filter
     * @param mixed  $sort
     * @param int    $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getNotesByUniqueId($uniqueId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getNotes(array('uniqueid' => $uniqueId), $filter, $sort, $maxCount);
    }

    /**
     *
     * @param array $params
     */
    public function createNote(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['noteid']))
        {
            return false;
        }

        $table = 'td_note';
        $bind  = array(
            'org_id'    => $params['orgid'],
            'unique_id' => $params['uniqueid'],
            'note_id'   => $params['noteid']
        );

        if (!empty($params['tuduid'])) {
            $bind['tudu_id'] = $params['tuduid'];
        }

        if (!empty($params['content'])) {
            $bind['content'] = $params['content'];
        }

        if (isset($params['color']) && is_int($params['color'])) {
            $bind['color'] = $params['color'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['createtime']) && is_int($params['createtime'])) {
            $bind['update_time'] = $bind['create_time'] = $params['createtime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['noteid'];
    }

    /**
     * 更新便签数据
     *
     * @param string $noteId
     * @param string $uniqueId
     * @param array  $params
     * @return boolean
     */
    public function updateNote($noteId, $uniqueId, array $params, $tuduId = null)
    {
        if (empty($noteId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_note';
        $bind  = array();

        if (array_key_exists('content', $params)) {
            $bind['content'] = $params['content'];
        }

        if (array_key_exists('color', $params)) {
            $bind['color'] = $params['color'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['updatetime']) && is_int($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
        }

        if (!$bind) {
            return false;
        }

        try {
            $where = 'note_id = ' . $this->_db->quote($noteId) . ' AND unique_id = ' . $this->_db->quote($uniqueId);

            if (!empty($tuduId)) {
                $where .= ' AND tudu_id = ' . $this->_db->quote($tuduId);
            }

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除便签
     *
     * @param string $noteId
     * @param string $uniqueId
     */
    public function deleteNote($noteId, $uniqueId)
    {
        if (empty($noteId) || empty($uniqueId)) {
            return false;
        }

        $sql = 'DELETE FROM td_note WHERE unique_id = ' . $this->_db->quote($uniqueId);

        if (is_array($noteId)) {
            $noteId = array_map(array($this->_db, 'quote'), $noteId);
            $sql .= ' AND note_id IN (' . implode(',', $noteId) . ')';
        } else {
            $sql .= ' AND note_id = ' . $this->_db->quote($noteId);
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
     * 生成便签ID
     *
     * @return string
     */
    public static function getNoteId()
    {
        $noteId = base_convert(substr(microtime(true) * 10000, 0, -1), 10, 16) . str_pad(dechex(mt_rand(0, 0xfffff)), 5, '0', STR_PAD_LEFT);
        return $noteId;
    }
}