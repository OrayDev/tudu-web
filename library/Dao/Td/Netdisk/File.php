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
 * @version    $Id: File.php 1328 2011-11-28 03:19:22Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_File extends Oray_Dao_Abstract
{

    /**
     * 获取文件记录
     *
     * @param $condition
     * @param $filter
     */
    public function getFile(array $condition, $filter = null)
    {
        $table   = 'nd_file';

        $columns = 'org_id AS orgid, unique_id AS uniqueid, folder_id AS folderid, file_id AS fileid, is_from_attach as isfromattach, '
                 . 'attach_file_id AS attachfileid, file_name AS filename, size, path, type, is_share AS isshare, create_time AS createtime, '
                 . 'from_unique_id AS fromuniqueid, from_file_id AS fromfileid';

        $where   = array();

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['fileid'])) {
            $where[] = 'file_id = ' . $this->_db->quote($condition['fileid']);
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Netdisk_Record_File', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Oray_Dao_Recordset
     */
    public function getFiles(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'nd_file';

        $columns = 'org_id AS orgid, unique_id AS uniqueid, folder_id AS folderid, file_id AS fileid, is_from_attach as isfromattach, '
                 . 'attach_file_id AS attachfileid, file_name AS filename, size, path, type, is_share AS isshare, create_time AS createtime, '
                 . 'from_unique_id AS fromuniqueid, from_file_id AS fromfileid';

        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['folderid'])) {
            $where[] = 'folder_id = ' . $this->_db->quote($condition['folderid']);
        }

        if (!empty($condition['fileid'])) {
            if (is_array($condition['fileid'])) {
                $fileid = array_map(array($this->_db, 'quote'), $condition['fileid']);
                $where[] = 'file_id in (' . implode(',', $fileid) . ')';
            } else {
                $where[] = 'file_id = ' .$this->_db->quote($condition['fileid']);
            }
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
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

        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_File');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 获取文件数
     * @param array $condition
     */
    public function getFileCount(array $condition)
    {
        $where = array();

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['folderid'])) {
            $where[] = 'folder_id = ' . $this->_db->quote($condition['folderid']);
        }

        if (!$where) {
            return false;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT COUNT(0) FROM nd_file WHERE {$where}";

        try {
            return (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 判断文件是否已存在
     *
     * @param array $condition
     */
    public function existFile(array $condition)
    {
        $where = array();
        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['folderid'])) {
            $where[] = 'folder_id = ' . $this->_db->quote($condition['folderid']);
        }

        if (!empty($condition['fileid'])) {
            $where[] = 'file_id = ' . $this->_db->quote($condition['fileid']);
        }

        if (!empty($condition['fromfileid'])) {
            $where[] = 'from_file_id = ' . $this->_db->quote($condition['fromfileid']);
        }

        if (!empty($condition['attachfileid'])) {
            $where[] = 'attach_file_id = ' . $this->_db->quote($condition['attachfileid']);
        }

        if (!$where) {
            return false;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT COUNT(0) FROM nd_file WHERE {$where}";

        try {
            return (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建网盘文件记录
     *
     * @param array $params
     */
    public function createFile(array $params)
    {
        if (empty($params['fileid']) || empty($params['uniqueid'])) {
            return false;
        }

        $sql = "call sp_nd_add_file("
             . $this->_db->quote($params['orgid']) . ", "
             . $this->_db->quote($params['uniqueid']) . ", "
             . $this->_db->quote($params['fileid']) . ", "
             . $this->_db->quote($params['folderid']) . ", "
             . $this->_db->quote($params['path']) . ", "
             . $this->_db->quote($params['filename']) . ", "
             . $this->_db->quote($params['size']) . ", "
             . $this->_db->quote($params['type']) . ")";

        try {
            $ret = $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            if (23000 === $e->getCode()) {
                return 1;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return (int) $ret;
    }

    /**
     *
     * @param string $uniqueId
     * @param string $fileId
     * @param array  $params
     * @return boolean
     */
    public function updateFile($uniqueId, $fileId, array $params)
    {
        if (empty($uniqueId) || empty($fileId)) {
            return false;
        }

        $table = 'nd_file';
        $bind  = array();
        $where = 'unique_id = ' . $this->_db->quote($uniqueId)
               . ' AND file_id = ' . $this->_db->quote($fileId);

        if (!empty($params['filename'])) {
            $bind['file_name'] = $params['filename'];
        }

        if (isset($params['isfromattach'])) {
            $bind['is_from_attach'] = $params['isfromattach'] ? 1 : 0;
        }

        if (array_key_exists('attachfileid', $params)) {
            $bind['attach_file_id'] = $params['attachfileid'];
        }

        if (isset($params['isshare'])) {
            $bind['is_share'] = $params['isshare'];
        }

        if (array_key_exists('fromuniqueid', $params)) {
            $bind['from_unique_id'] = $params['fromuniqueid'];
        }

        if (array_key_exists('fromfileid', $params)) {
            $bind['from_file_id'] = $params['fromfileid'];
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Execption $e) {
            $this->_catchExecption($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $uniqueId
     * @param string $folderId
     * @return boolean
     */
    public function deleteFile($uniqueId, $fileId)
    {
        $sql = 'call sp_nd_delete_file(' . $this->_db->quote($uniqueId)
             . ',' . $this->_db->quote($fileId) . ')';

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移动文件
     *
     * @param $uniqueId
     * @param $fileId
     * @param $folderId
     */
    public function moveFile($uniqueId, $fileId, $folderId)
    {
        /*$sql = 'call sp_nd_move_file('
             . $this->_db->quote($uniqueId) . ','
             . $this->_db->quote($fileId) . ','
             . $this->_db->quote($folderId) . ');';*/

        $sql = 'UPDATE nd_file SET folder_id = ' . $this->_db->quote($folderId) . ' WHERE '
             . 'unique_id = ' . $this->_db->quote($uniqueId) . ' AND file_id = ' . $this->_db->quote($fileId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
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