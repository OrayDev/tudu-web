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
 * @version    $Id: Meeting.php 775 2011-05-11 09:16:57Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Meeting extends Oray_Dao_Abstract
{
    
    /**
     * 
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getMeeting(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_tudu_meeting';
        $columns = 'org_id AS orgid, tudu_id AS tuduid, notify_time AS notifytime, notify_type AS notifytype, location, '
                 . 'is_allday AS isallday';
        $where   = array();
        $order   = array();
        $limit   = '';
        
        if (!empty($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }
        
        if (!$where) {
            return null;
        }
        
        // WHERE
        $where = implode(' AND ', $where);
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return null;
            }
            
            return Oray_Dao::record('Dao_Td_Tudu_Record_Meeting', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * 
     * @param array $params
     * @return boolean
     */
    public function createMeeting(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['tuduid']))
        {
            return false;
        }
        
        $table = 'td_tudu_meeting';
        $bind  = array(
            'org_id'     => $params['orgid'],
            'tudu_id'    => $params['tuduid']
        );
        
        if (!empty($params['notifytime']) && is_int($params['notifytime'])) {
            $bind['notify_time'] = $params['notifytime'];
        }
        
        if (!empty($params['notifytype']) && is_int($params['notifytype'])) {
            $bind['notify_type'] = $params['notifytype'];
        }
        
        if (!empty($params['location'])) {
            $bind['location'] = $params['location'];
        }
        
        if (!empty($params['isallday'])) {
            $bind['is_allday'] = $params['isallday'] ? 1 : 0;
        }
        
        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 更新分类信息
     * 
     * @param string $orgId
     * @param string $boardId
     * @param string $classId
     * @param array  $params
     */
    public function updateMeeting($tuduId, array $params)
    {
        if (empty($tuduId)) {
            return false;
        }
        
        $table = 'td_tudu_meeting';
        $bind  = array();
        $where = 'tudu_id = ' . $this->_db->quote($tuduId);
        
        if (array_key_exists('notifytype', $params)) {
            $bind['notify_type'] = $params['notifytype'];
        }
        
        if (array_key_exists('notifytime', $params)) {
            $bind['notify_time'] = $params['notifytime'];
        }
        
        if (array_key_exists('location', $params)) {
            $bind['location'] = $params['location'];
        }
        
        if (array_key_exists('isallday', $params)) {
            $bind['is_allday'] = $params['isallday'];
        }
        
        if (!$bind) {
            return false;
        }
        
        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除分类
     * 
     * @param string $tuduId
     * @return boolean
     */
    public function existsMeeting($tuduId)
    {
        if (empty($tuduId)) {
            return false;
        }
        
        $sql = 'SELECT COUNT(0) FROM td_tudu_meeting WHERE tudu_id = ' . $this->_db->quote($tuduId);
        
        try {
            $count = (int) $this->_db->fetchOne($sql);
            
            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }
    
    /**
     * 删除分类
     * 
     * @param string $tuduId
     * @return boolean
     */
    public function deleteMeeting($tuduId)
    {
        if (empty($tuduId)) {
            return false;
        }
        
        $sql = 'DELETE FROM td_tudu_meeting WHERE tudu_id = ' . $this->_db->quote($tuduId);
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 计算提醒时间
     * 
     * @param $startTime
     * @param $notifyType
     */
    public static function calNotifyTime($startTime, $notifyType)
    {
        $notifyTime = null;
        switch ($notifyType) {
            // 5分钟
            case 1:
                $notifyTime = $startTime - 300;
                break;
            // 10分钟
            case 2:
                $notifyTime = $startTime - 600;
                break;
            case 4:
                $notifyTime = $startTime - 900;
                break;
            case 8:
                $notifyTime = $startTime - 1800;
                break;
            case 16:
                $notifyTime = $startTime - 3600;
                break;
            case 32:
                $notifyTime = $startTime - 7200;
                break;
            case 64:
                $notifyTime = strtotime(date('Y-m-d 09:00', $startTime - 86400));
                break;
            case 128:
                $notifyTime = strtotime(date('Y-m-d 14:00', $startTime - 86400));
                break;
            case 256:
                $notifyTime = strtotime(date('Y-m-d 18:00', $startTime - 86400));
                break;
        }
        
        return $notifyTime;
    }
}