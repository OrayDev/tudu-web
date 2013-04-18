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
 * @version    $Id: Share.php 1998 2012-07-17 02:41:07Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_Share extends Oray_Dao_Abstract
{
    /**
     * 获取单个共享信息
     * 
     * @param $condition
     * @param $filter
     */
    public function getShare(array $condition, $filter = null)
    {
        $table = 'nd_share';
        $columns = 'object_id AS objectid, owner_id AS ownerid, org_id AS orgid, target_id AS targetid, object_type AS objecttype, owner_info AS ownerinfo';
        $where   = array();

        if (!empty($condition['ownerid'])) {
            $where[] = 'owner_id = ' . $this->_db->quote($condition['ownerid']);
        }

        if (!empty($condition['objectid'])) {
            $where[] = 'object_id = ' . $this->_db->quote($condition['objectid']);
        }

        if (!empty($condition['targetid'])) {
            $where[] = 'target_id = ' . $this->_db->quote($condition['targetid']);
        }

        if (!empty($condition['objecttype'])) {
            $where[] = 'object_type = ' . $this->_db->quote($condition['objecttype']);
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $records = $this->_db->fetchAll($sql);
            
            if (!$records) {
                return null;
            }

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_Share');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * 获取共享用户信息
     * 
     * @param $condition
     * @param $filter
     */
    public function getShareUsers(array $condition, $filter = null) 
    {
        $table = 'nd_share';
        $columns = 'object_id AS objectid, owner_id AS ownerid, org_id AS orgid, target_id AS targetid, object_type AS objecttype, owner_info AS ownerinfo';
        $where   = array();

        if (!empty($condition['targetid'])) {
            if (is_array($condition['targetid'])) {
                foreach ($condition['targetid'] as $item) {
                    $targetIds[] = $this->_db->quote($item);
                }
                $where[] = 'target_id IN (' . implode(',', $targetIds) . ')';
            } else {
                $where[] = 'target_id = ' . $this->_db->quote($condition['targetid']);
            }
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} GROUP BY owner_id";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_Share');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getShareFolders(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table = 'nd_share S '
               . 'LEFT JOIN nd_folder F ON S.object_id = F.folder_id AND S.owner_id = F.unique_id';
        $columns = 'S.object_id AS objectid, S.owner_id AS ownerid, S.target_id AS targetid, S.object_type AS objecttype, '
                 . 'S.owner_info AS ownerinfo, F.folder_name AS foldername, F.is_system AS issystem, F.create_time AS createtime';
        $where = array();

        if (!empty($condition['ownerid'])) {
            $where[] = 'owner_id = ' . $this->_db->quote($condition['ownerid']);
        }

        if (!empty($condition['targetid'])) {
            $where[] = 'target_id = ' . $this->_db->quote($condition['targetid']);
        }

        if (!empty($condition['objecttype'])) {
            $where[] = 'object_type = ' . $this->_db->quote($condition['objecttype']);
        }

        if (!$where) {
        	return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_Share');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getShareFiles(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table = 'nd_share S '
               . 'LEFT JOIN nd_file F ON S.object_id = F.file_id AND S.owner_id = F.unique_id AND F.status = 1';
        $columns = 'S.object_id AS objectid, S.owner_id AS ownerid, S.target_id AS targetid, S.object_type AS objecttype, '
                 . 'S.owner_info AS ownerinfo, F.file_name AS filename, F.size, F.create_time AS createtime, '
                 . 'F.is_from_attach AS isfromattach, F.attach_file_id AS attachfileid, F.from_unique_id AS fromuniqueid, F.from_file_id AS fromfileid';;
        $where = array();

        if (!empty($condition['ownerid'])) {
            $where[] = 'owner_id = ' . $this->_db->quote($condition['ownerid']);
        }

        if (!empty($condition['targetid'])) {
            $where[] = 'target_id = ' . $this->_db->quote($condition['targetid']);
        }

        if (!empty($condition['objecttype'])) {
            $where[] = 'object_type = ' . $this->_db->quote($condition['objecttype']);
        }

        if (!$where) {
        	return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }

            return new Oray_Dao_Recordset($records, 'Dao_Td_Netdisk_Record_Share');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 是否有共享存在
     * 
     * @param array $targetId
     */
    public function existShare($targetId, $orgId)
    {
        if (empty($targetId) || empty($orgId)) {
            return false;
        }

        $bind  = array(
            'targetid' => $targetId,
            'orgid'    => $orgId
        );

        $sql = "SELECT COUNT(0) FROM nd_share WHERE target_id = :targetid AND org_id = :orgid";

        try {
            return (int) $this->_db->fetchOne($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 设置共享
     * 
     * @param array $params
     */
    public function createShare(array $params)
    {
        if (empty($params['objectid'])
           || empty($params['ownerid'])
           || empty($params['objecttype'])
           || empty($params['orgid']))
        {
            return false;
        }

        $table = 'nd_share';
        $bind = array(
            'object_id'   => $params['objectid'],
            'owner_id'    => $params['ownerid'],
            'object_type' => $params['objecttype'],
            'org_id'      => $params['orgid']
        );

        if (isset($params['targetid'])) {
            $bind['target_id'] = $params['targetid'];
        }

        if (isset($params['ownerinfo'])) {
            $bind['owner_info'] = $params['ownerinfo'];
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
     * 更新共享
     * 
     * @param string $objectId
     * @param string $ownerId
     * @param array $params
     */
    public function updateShare($objectId, $ownerId, array $params)
    {
        if (empty($objectId) || empty($ownerId)) {
            return false;
        }

        $table = 'nd_share';
        $bind = array();
        $where = 'object_id = ' . $this->_db->quote($objectId)
               . ' AND owner_id = ' . $this->_db->quote($ownerId);

        if (isset($params['targetid'])) {
            $bind['target_id'] = $params['targetid'];
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
     * 取消共享
     * 
     * @param string $objectId
     * @param string $ownerId
     */
    public function deleteShare($objectId, $ownerId)
    {
        if (empty($objectId) || empty($ownerId)) {
            return false;
        }

        $sql = 'DELETE FROM nd_share WHERE object_id = ' . $this->_db->quote($objectId) . ' AND owner_id = ' . $this->_db->quote($ownerId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }
}