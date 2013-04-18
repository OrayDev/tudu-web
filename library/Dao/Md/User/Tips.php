<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Tips.php 2689 2013-01-17 09:59:32Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Tips extends Oray_Dao_Abstract
{
    /**
     * 获取用户有记录的提示
     *
     * @param string $uniqueId
     * @param string $tipsId
     * @return array
     */
    public function getUserTip($uniqueId, $tipsId)
    {
        if (empty($uniqueId) || empty($tipsId)) {
            return null;
        }

        $sql = 'SELECT tips_id AS tipsid, `status` '
             . 'FROM md_user_tips WHERE unique_id = :uniqueid AND tips_id = :tipsid';

        $bind = array(
            'uniqueid' => $uniqueId,
            'tipsid'   => $tipsId
        );

        try {
            $record = $this->_db->fetchRow($sql, $bind);
            if (!$record) {
                return null;
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }

        return $record;
    }

    /**
     * 获取用户有记录的提示（多条）
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getUserTips($uniqueId)
    {
        if (empty($uniqueId)) {
            return null;
        }

        $sql = 'SELECT tips_id AS tipsid, `status` '
             . 'FROM md_user_tips WHERE unique_id = ' . $this->_db->quote($uniqueId);

        $_records = $this->_db->fetchAll($sql);

        $records = array();
        foreach ($_records as $record) {
            $records[$record['tipsid']] = $record;
        }

        return $records;
    }

    /**
     *
     * @param $uniqueId
     * @param $tips
     */
    public function addTips($uniqueId, array $tips)
    {
        $table = 'md_user_tips';

        $sql = 'INSERT INTO ' . $table . ' (unique_id, tips_id) VALUES ';

        $uniqueId = $this->_db->quote($uniqueId);
        $inserts = array();
        foreach ($tips as $tipId) {
            $inserts[] = '(' . $uniqueId . ',' . $this->_db->quote($tipId) . ')';
        }
        $sql .= implode(',', $inserts);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新提示状态
     *
     * @param $uniqueId
     * @param $tipsId
     */
    public function updateTips($uniqueId, $tipsId, array $params)
    {
        $table = 'md_user_tips';
        $bind  = array();
        $where = 'unique_id = ' . $this->_db->quote($uniqueId) . ' AND '
               . 'tips_id = ' . $this->_db->quote($tipsId);

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
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
}