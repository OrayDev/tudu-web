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
 * @version    $Id: File.php 1998 2012-07-17 02:41:07Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Attachment_File extends Oray_Dao_Abstract
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
     *
     *
     * @param $condition
     * @param $filter
     * @return Dao_Td_Attachment_Record_File
     */
    public function getFile(array $condition, $filter = null)
    {
    	$table   = "td_attachment a "
                 . "LEFT JOIN td_attach_post ap ON a.file_id = ap.file_id";
        $columns = "a.file_id AS fileid, a.file_name AS filename, a.size, a.type, a.path, "
                 . "ap.tudu_id AS tuduid, ap.post_id AS postid, a.unique_id as uniqueid, "
                 . "a.create_time AS createtime";
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

        return Oray_Dao::record('Dao_Td_Attachment_Record_File', $record);
    }

    /**
     *
     * @param array $condition
     * @param array $sort
     */
    public function getTuduFiles(array $condition, $filter = null, $sort = null)
    {
        $table   = 'td_attach_post ap '
                 . 'LEFT JOIN td_attachment a ON ap.file_id = a.file_id '
                 . 'LEFT JOIN td_post p ON ap.post_id = p.post_id AND ap.tudu_id = p.tudu_id';
        $columns = 'a.file_id AS fileid, a.file_name AS filename, a.size, a.create_time AS createtime, '
                 . 'p.poster, ap.tudu_id AS tuduid, ap.post_id AS postid, a.unique_id as uniqueid, '
                 . 'ap.is_attach AS isattach';
        $where   = array();
        $order   = array();

        if (isset($condition['tuduid'])) {
            $where[] = 'ap.tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (!empty($filter) && isset($filter['isattach'])) {
            $where[] = 'ap.is_attach = 1';
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
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

        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        $sql = "SELECT $columns FROM $table $where $order";
        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Td_Attachment_Record_File');
    }

    /**
     * Get records
     *
     * SELECT
     * a.file_id AS fileid, a.file_name AS filename, a.size, a.type, a.path,
     * ap.tudu_id, ap.post_id,
     * a.create_time AS createtime FROM td_attachment a
     * LEFT JOIN td_attach_post ap ON a.file_id = ap.file_id
     * WHERE ap.tudu_id = ? AND ap.post_id = ?
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getFiles(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "td_attachment a "
                 . "LEFT JOIN td_attach_post ap ON a.file_id = ap.file_id";
        $columns = "a.file_id AS fileid, a.file_name AS filename, a.size, a.type, a.path, ap.is_attach as isattach, "
                 . "ap.tudu_id AS tuduid, ap.post_id AS postid, a.unique_id as uniqueid, "
                 . "a.create_time AS createtime";
        $where   = array();
        $order   = array();
        $limit   = '';

        if (isset($condition['tuduid'])) {
            $where[] = 'ap.tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (isset($condition['postid'])) {
            $where[] = 'ap.post_id = ' . $this->_db->quote($condition['postid']);
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

        if (!empty($filter) && array_key_exists('isattachment', $filter)) {
            if (null !== $filter['isattachment']) {
                $where[] = 'ap.is_attach = ' . (int) $filter['isattachment'];
            }
        } else {
        	$where[] = 'ap.is_attach = 1';
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

        return new Oray_Dao_Recordset($records, 'Dao_Td_Attachment_Record_File');
    }

    /**
     * Create file
     *
     * @param $params
     * @return string|false
     */
    public function createFile(array $params)
    {
        if (empty($params['fileid'])) {
            return false;
        }

        $createTime = empty($params['createtime']) ? time() : (int) $params['createtime'];

        $table = "td_attachment";
        $bind  = array(
            //'org_id' => $params['orgid'],
            'file_id' => $params['fileid'],
            'file_name' => $params['filename'],
            'size' => (int) $params['size'],
            'type' => $params['type'],
            'path' => $params['path'],
            'create_time' => $createTime
        );

        if (isset($params['uniqueid'])) {
            $bind['unique_id'] = $params['uniqueid'];
        }

        if (isset($params['isnetdisk'])) {
            $bind['is_netdisk'] = $params['isnetdisk'] ? 1 : 0;
        }

        if (!empty($params['orgid'])) {
            $bind['org_id'] = $params['orgid'];
        }

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

        return $params['fileid'];
    }

    /**
     * 增加文件关联
     *
     * @param string $tuduId
     * @param string $postId
     * @param string $fileId
     * @param boolean $isAttachment
     * @return boolean
     */
    public function addPost($tuduId, $postId, $fileId, $isAttachment = true)
    {
        $table = "td_attach_post";
        $bind  = array(
            'tudu_id' => $tuduId,
            'post_id' => $postId,
            'file_id' => $fileId,
            'is_attach' => (int) $isAttachment
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            //$this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除文件关联
     *
     * @param string $tuduId
     * @param string $postId
     * @param string $fileId
     * @return boolean
     */
    public function deletePost($tuduId, $postId, $fileId = null)
    {
        $sql = "DELETE FROM td_attach_post"
             . " WHERE tudu_id = " . $this->_db->quote($tuduId)
             . " AND post_id = " . $this->_db->quote($postId);

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

    /**
     *
     */
    public function getQuotaUsed($orgId)
    {
    	$sql = 'SELECT SUM(size) FROM td_attachment WHERE org_id = ' . $this->_db->quote($orgId);

    	$size = (int) $this->_db->fetchOne($sql);

    	return $size;
    }

    /**
     * 获取文件ID
     *
     * @return string
     */
    public static function getFileId()
    {
    	$fileId = md5(microtime(true) . (mt_rand(0, 0xfffff)));
    	return $fileId;
    }
}