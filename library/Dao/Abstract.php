<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Temp
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 75 2010-07-29 11:30:34Z gxx $
 */

/**
 * @category   Dao
 * @package    Dao_Temp
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Temp extends Oray_Dao_Abstract
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
     * Get record
     * 
     * SQL here..
     * 
     * @param array $condition
     * @param array $filter
     * @return Oray_Dao_Record
     */
    public function getRecord(array $condition, $filter = null)
    {
        $table   = "";
        $columns = "";
        $where   = array();
        
        // $condition ...
         
        if (empty($where)) {
            return null;
        }
        
        // $filter ...
        
        // WHERE
        $where = implode(' AND ', $where);
        
        $sql = "SELECT TOP 1 {$columns} FROM {$table} WHERE {$where}";
        //$sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        
        $record = $this->_db->fetchRow($sql);
        
        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Oray_Dao_Record', $record);
    }
    
    
    /**
     * Get records
     * 
     * SQL here..
     * 
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getRecords(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = "";
        $columns = "";
        $where   = array();
        $order   = array();
        $limit   = '';
        
        // $condition ...
        
        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }
        
        // $filter ...
        
        
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
            $limit = 'TOP ' . $maxCount;
            //$limit = 'LIMIT ' . $maxCount;
        }
        
        $sql = "SELECT $limit $columns FROM $table $where $order";
        //$sql = "SELECT $columns FROM $table $where $order $limit";
        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Oray_Dao_Record');
    }
    
    /**
     * Get record page
     * 
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getRecordPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table    = "";
        $columns  = "";
        $primary  = "";
        $recordClass = "Oray_Dao_Record";
        $where = array();
        $order = array();
        
        // $condition...
        
    
        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }
        
        // ORDER
        $order = implode(', ', $order);

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        /**
         * @see Oray_Db_Paginator
         */
        require_once 'Oray/Db/Paginator.php';

        // 初始化分页器
        $paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER      => $this->_db,
            Oray_Db_Paginator::RECORD_CLASS => $recordClass,
            Oray_Db_Paginator::PAGE_SIZE    => $pageSize,
            Oray_Db_Paginator::TABLE        => $table,
            Oray_Db_Paginator::PRIMARY      => $primary,
            Oray_Db_Paginator::COLUMNS      => $columns,
            Oray_Db_Paginator::WHERE        => $where,
            Oray_Db_Paginator::ORDER        => $order
        ));

        // 返回查询结果
        return $paginator->query($page);
    }
    
    /**
     * Create record
     * 
     * @param $params
     * @return int|false
     */
    public function createRecord(array $params)
    {
        // $params...
        
        $table = "";
        $bind  = array();
        
        
        // $params....
        
        
        try {
            $this->_db->insert($table, $bind);
            $insertId = (int) $this->_db->lastInsertId();
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return $insertId;
    }
    
    
    /**
     * Update record
     * 
     * @param int $recordId
     * @param array $params
     * @return boolean
     */
    public function updateRecord($recordId, array $params)
    {
        // ....
        
        $table = "";
        $bind  = array();
        $where = "";
        
        
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
     * Delete record
     * 
     * @param int $recordId
     * @return boolean
     */
    public function deleteRecord($recordId)
    {

        $sql = "";
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
}