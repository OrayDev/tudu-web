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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Group extends Oray_Dao_Abstract
{

    /**
     * 节点类型
     *
     * @var string
     */
    const TYPE_ROOT = 'root';
    const TYPE_NODE = 'node';
    const TYPE_LEAF = 'leaf';

    /**
     *
     * @param $tuduId
     */
    public function getNode($tuduId)
    {
        $sql = 'SELECT tudu_id AS tuduid, parent_tudu_id AS parentid, type FROM td_tudu_group '
             . 'WHERE tudu_id = ' . $this->_db->quote($tuduId);

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
     * 获取整个图度组结构
     *
     * @param array $condition
     * return array
     */
    public function getParentTudus(array $condition)
    {
        $table    = 'td_tudu_group AS G LEFT JOIN td_tudu AS T ON G.tudu_id = T.tudu_id';
        $columns  = 'T.tudu_id AS tuduid, T.from, T.to, T.cc, G.parent_tudu_id AS parentid, G.root_tudu_id AS rootid';
        $rootTudu = array();
        $where    = array();

        if (!empty($condition['rootid'])) {
            $where[] = 'root_tudu_id = '. $this->_db->quote($condition['rootid']);

            $sql2 = "SELECT {$columns} FROM {$table} WHERE T.tudu_id = " . $this->_db->quote($condition['rootid']);
            $rootTudu = $this->_db->fetchAll($sql2);
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        $order = 'ORDER BY parent_tudu_id DESC';

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order}";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }
        } catch(Zend_Db_Exception $e) {

            return new Oray_Dao_Recordset();
        }

        return Dao_Td_Tudu_Group::formatData(array_merge($records, $rootTudu));
    }

    /**
     * 创建图度组
     * @param $params
     * @return boolean
     */
    public function createNode(array $params)
    {
        if (empty($params['tuduid'])) {
            return false;
        }

        $table = 'td_tudu_group';
        $bind  = array();

        $bind['tudu_id']   = $params['tuduid'];
        $bind['unique_id'] = $params['uniqueid'];
        if (!empty($params['parentid'])) {
            $bind['parent_tudu_id'] = $params['parentid'];
        }

        if (!empty($params['rootid'])) {
            $bind['root_tudu_id'] = $params['rootid'];
        }

        if (isset($params['type'])) {
            $bind['type'] = $params['type'];
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
     * 更新节点信息
     * @param $tuduId
     * @param $params
     * @return boolean
     */
    public function updateNode($tuduId, array $params)
    {
        if (empty($tuduId)) {
            return false;
        }

        $table = 'td_tudu_group';
        $bind  = array();
        $where = 'tudu_id = ' . $this->_db->quote($tuduId);

        if (array_key_exists('parentid', $params)) {
            $bind['parent_tudu_id'] = $params['parentid'];
        }

        if (array_key_exists('parentid', $params)) {
            $bind['root_tudu_id'] = $params['rootid'];
        }

        if (isset($params['type'])) {
            $bind['type'] = $params['type'];
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
     * 删除分组节点
     * @param string $tuduId
     * @return boolean
     */
    public function deleteNode($tuduId)
    {
        $sqls = array();

        $tuduId = $this->_db->quote($tuduId);
        $sqls[] = 'DELETE FROM td_tudu_group WHERE tudu_id = ' . $tuduId;
        $sqls[] = 'UPDATE td_tudu_group SET parent_tudu_id = NULL WHERE parent_tudu_id = ' . $tuduId;

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

    public function getChildrenCount($tuduId, $uniqueId = null)
    {
        $tuduId = $this->_db->quote($tuduId);
        $sql = "SELECT COUNT(0) FROM td_tudu_group g LEFT JOIN td_tudu t ON g.tudu_id = t.tudu_id
                WHERE t.is_draft = 0 AND g.parent_tudu_id = {$tuduId}";

        if (!empty($uniqueId)) {
            $sql .= 'AND g.unique_id = ' . $this->_db->quote($uniqueId);
        }

        try {
            $count = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $count;
    }

    /**
     * 格式化数组
     *
     * @param array $data
     * return array
     */
    public static function formatData($data)
    {
        $ret = array();

        foreach ($data as $key => $value) {
            $key           = $value['tuduid'];
            $value['from'] = Dao_Td_Tudu_Tudu::formatAddress($value['from'], true);
            $value['to']   = Dao_Td_Tudu_Tudu::formatAddress($value['to']);
            $value['cc']   = Dao_Td_Tudu_Tudu::formatAddress($value['cc']);
            $ret[$key]     = $value;
        }

        return $ret;
    }
}