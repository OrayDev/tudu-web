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
 * @version    $Id: Folder.php 1251 2011-11-07 03:24:44Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_Folder extends Oray_Dao_Abstract
{

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Oray_Dao_Recordset
     */
    public function getFolder(array $condition, $filter = null)
    {
        $table   = 'nd_folder';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, folder_id AS folderid, parent_folder_id AS parentfolderid, '
                 . 'folder_name AS foldername, max_quota AS maxquota, folder_size AS foldersize, create_time AS createtime, '
                 . 'is_system AS issystem, is_share AS isshare';
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

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Netdisk_Record_Folder', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return fasle;
        }
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Oray_Dao_Recordset
     */
    public function getFolders(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'nd_folder';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, folder_id AS folderid, parent_folder_id AS parentfolderid, '
                 . 'folder_name AS foldername, max_quota AS maxquota, folder_size AS foldersize, create_time AS createtime, '
                 . 'is_system AS issystem, is_share AS isshare';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
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
                case 'issystem':
                    $key = 'is_system';
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

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_Folder');
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     *
     * @param $condition
     * @return boolean
     */
    public function existsFolder(array $condition)
    {
        $where = array();

        if (!empty($condition['foldername'])) {
            $where[] = 'folder_name = ' . $this->_db->quote($condition['foldername']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['folderid'])) {
            $where[] = 'folder_id = ' . $this->_db->quote($condition['folderid']);
        }

        if (empty($where)) {
            return false;
        }

        $where = implode(' AND ', $where);

        $sql   = "SELECT COUNT(0) FROM nd_folder WHERE {$where}";

        try {
            $count = (int) $this->_db->fetchOne($sql);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function createFolder(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['folderid'])
            || empty($params['foldername']))
        {
            return false;
        }

        $table = 'nd_folder';
        $bind  = array(
            'org_id'    => $params['orgid'],
            'unique_id'  => $params['uniqueid'],
            'folder_id' => $params['folderid'],
            'folder_name' => $params['foldername']
        );

        if (!empty($params['parentfolderid'])) {
            $bind['parent_folder_id'] = $params['parentfolderid'];
        }

        if (array_key_exists('issystem', $params)) {
            $bind['is_system'] = $params['issystem'] ? 1 : 0;
        }

        if (!empty($params['createtime']) && is_int($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }

        if (!empty($params['maxquota']) && is_int($params['maxquota'])) {
            $bind['max_quota'] = $params['maxquota'];
        }

        try {
            $this->_db->insert($table, $bind);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $bind['folder_id'];
    }

    /**
     *
     * @param string $uniqueId
     * @param array  $params
     * @return boolean
     */
    public function updateFolder($uniqueId, $folderId, array $params)
    {
        if (empty($uniqueId) || empty($folderId)) {
            return false;
        }

        $table = 'nd_folder';
        $bind  = array();
        $where = 'unique_id = ' . $this->_db->quote($uniqueId)
               . ' AND folder_id = ' . $this->_db->quote($folderId);

        if (!empty($params['foldername'])) {
            $bind['folder_name'] = $params['foldername'];
        }

        if (array_key_exists('parentfolderid', $params)) {
            $bind['parent_fodler_id'] = $params['parentfolderid'];
        }

        if (isset($params['maxquota'])) {
            $bind['max_quota'] = (int) $params['maxquota'];
        }

        if (isset($params['foldersize']) && is_int($params['foldersize'])) {
            $bind['folder_size'] = $params['foldersize'];
        }

        if (isset($params['isshare'])) {
            $bind['is_share'] = $params['isshare'];
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
    public function deleteFolder($uniqueId, $folderId)
    {
        $sql = 'DELETE FROM nd_folder WHERE unique_id = ' . $this->_db->quote($uniqueId)
             . ' AND folder_id = ' . $this->_db->quote($folderId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public static function getFolderId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}