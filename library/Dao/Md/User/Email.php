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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Email extends Oray_Dao_Abstract
{
    
    /**
     * 
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_User_Record_Email
     */
    public function getEmail(array $condition, $filter = null)
    {
        $table   = 'md_email';
        $columns = 'org_id AS orgid, user_id AS userid, address, password, protocol, imap_host AS host, port, '
                 . 'is_ssl AS isssl, type, last_check_info AS lastcheckinfo, last_check_time AS lastchecktime, '
                 . 'order_num AS ordernum';
        $where   = array();
        
        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (!empty($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
        }
        
        if (!empty($condition['address'])) {
            $where[] = 'address = ' . $this->_db->quote($condition['address']);
        }
        
        if (!$where) {
            return null;
        }
        
        $where = implode(' AND ', $where);
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return null;
            }
            
            return Oray_Dao::record('Dao_Md_User_Record_Email', $record);
            
        } catch (Zend_Db_Exception $e) {
            
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * 
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getEmails(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_email';
        $columns = 'org_id AS orgid, user_id AS userid, address, password, protocol, imap_host AS host, port, '
                 . 'is_ssl AS isssl, type, last_check_info AS lastcheckinfo, order_num AS ordernum';
        $where   = array();
        $limit   = '';
        $order   = array();
        
        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (!empty($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
        }
        
        if (!empty($condition['address'])) {
            $where[] = 'address = ' . $this->_db->quote($condition['address']);
        }
        
        if (!$where) {
            return new Oray_Dao_Recordset();
        }
        
        $where = implode(' AND ', $where);
        
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'lastchecktime':
                    $key = 'last_check_time';
                    break;
                case 'ordernum':
                    $key = 'order_num';
                    break;
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }
        
        // ORDER
        $order = implode(', ', $order);
        
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }
        
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order}";
        
        try {
            $records = $this->_db->fetchAll($sql);
            
            return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Email');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return new Oray_Dao_Recordset();
        }
    }
    
    /**
     * 
     * @param $condition
     * @return boolean
     */
    public function countEmail(array $condition)
    {
        $where = array();
        
        if (isset($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
        }
        
        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (empty($where)) {
            return false;
        }
        
        $where = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(0) FROM md_email WHERE {$where}";
        
        try {
            return (int) $this->_db->fetchOne($sql);
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
    public function createEmail(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['address']))
        {
            return false;
        }
        
        $table = 'md_email';
        $bind  = array();
        
        $bind['org_id']  = $params['orgid'];
        $bind['user_id'] = $params['userid'];
        $bind['address'] = $params['address'];
        
        if (!empty($params['password'])) {
            $bind['password'] = $params['password'];
        }
        
        if (!empty($params['protocol'])) {
            $bind['protocol'] = $params['protocol'];
        }
        
        if (!empty($params['host'])) {
            $bind['imap_host'] = $params['host'];
        }
        
        if (!empty($params['port']) && is_int($params['port'])) {
            $bind['port'] = $params['port'];
        }
        
        if (isset($params['type']) && is_int($params['type'])) {
            $bind['type'] = $params['type'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }
        
        if (isset($params['isssl'])) {
            $bind['is_ssl'] = $params['isssl'] ? 1 : 0;
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
     * 
     * @param string $orgId
     * @param string $userId
     * @param string $address
     * @param array  $params
     * @return boolean
     */
    public function updateEmail($orgId, $userId, $address, $params)
    {
        if (empty($orgId)
            || empty($userId)
            || empty($address))
        {
            return false;
        }
        
        $table = 'md_email';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId) . ' AND '
               . 'address = ' . $this->_db->quote($address);

        if (!empty($params['address'])) {
            $bind['address'] = $params['address'];
        }
        
        if (!empty($params['password'])) {
            $bind['password'] = $params['password'];
        }
        
        if (isset($params['host'])) {
            $bind['imap_host'] = $params['host'];
        }
        
        if (!empty($params['protocol'])) {
            $bind['protocol'] = $params['protocol'];
        }
        
        if (array_key_exists('port', $params)) {
            $bind['port'] = $params['port'];
        }
        
        if (isset($params['type']) && is_int($params['type'])) {
            $bind['type'] = $params['type'];
        }
        
        if (isset($params['isssl'])) {
            $bind['is_ssl'] = $params['isssl'] ? 1 : 0;
        }
        
        if (array_key_exists('lastcheckinfo', $params)) {
            $bind['last_check_info'] = $params['lastcheckinfo'];
        }
        
        if (array_key_exists('lastchecktime', $params)) {
            $bind['last_check_time'] = $params['lastchecktime'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
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
     * 
     * @param $orgId
     * @param $userId
     * @param $address
     * @return boolean
     */
    public function deleteEmail($orgId, $userId, $address)
    {
        if (empty($orgId)
            || empty($userId)
            || empty($address))
        {
            return false;
        }
        
        $orgId   = $this->_db->quote($orgId);
        $userId  = $this->_db->quote($userId);
        $address = $this->_db->quote($address);
        
        $sql = "DELETE FROM md_email WHERE org_id = {$orgId} AND user_id = {$userId} AND address = {$address}";
        
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
     * @param $orgId
     * @param $userId
     * @return int
     */
    public function getMaxOrderNum($orgId, $userId)
    {
        $sql = 'SELECT MAX(order_num) FROM md_email WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);
        
        try {
            return (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 调整排序
     * 
     * @param $orgId
     * @param $userId
     * @param $address
     * @param $type
     * @return boolean
     */
    public function sortEmail($orgId, $userId, $address, $type)
    {
        $email = $this->getEmailByAddress($orgId, $userId, $address);
        
        if (null === $email) {
            return false;
        }
        
        $sql = 'SELECT order_num, address FROM md_email WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);
        
        if ($type == 'down') {
            $sql .= ' AND order_num < ' . $email->orderNum . ' ORDER BY order_num DESC';
        } else {
            $sql .= ' AND order_num > ' . $email->orderNum . ' ORDER BY order_num ASC';
        }
        
        $sql .= ' LIMIT 1';
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return false;
            }
            
            $this->updateEmail($orgId, $userId, $address, array('ordernum' => (int) $record['order_num']));
            
            $this->updateEmail($orgId, $userId, $record['address'], array('ordernum' => $email->orderNum));
            
            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 整理用户Email排序索引（排序不变）
     * 
     * @param $orgId
     * @param $userId
     */
    public function tidyEmailSort($orgId, $userId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        
        $sql = "SELECT address, order_num FROM md_email WHERE org_id = {$orgId} AND user_id = {$userId} ORDER BY order_num DESC";
        
        $records = $this->_db->fetchAll($sql);
        
        $count = count($records);
        
        try {
            for ($i = 0; $i < $count; $i++) {
                $this->_db->update(
                    'md_email',
                    array('order_num' => $count - $i),
                    "org_id = {$orgId} AND user_id = {$userId} AND address = " . $this->_db->quote($records[$i]['address'])
                );
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            
            return false;
        }
    }
    
    /**
     * 
     * @param $orgId
     * @param $userId
     * @param $address
     */
    public function getEmailByAddress($orgId, $userId, $address)
    {
        return $this->getEmail(array(
            'orgid'   => $orgId,
            'userid'  => $userId,
            'address' => $address
        ));
    }
}