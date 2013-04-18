<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 *  * @subpackage Product
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Product.php 34 2010-07-19 11:09:58Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Product
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Product_Product extends Oray_Dao_Abstract
{    
    /**
     * Get record
     * 
     * SELECT product_id AS productid, product_name AS productname FROM md_product WHERE product_id = ?
     * 
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Product_Record_Product
     */
    public function getProduct(array $condition, $filter = null)
    {
        $table   = 'md_product';
        $columns = 'product_id AS productid, product_name AS productname';
        $where   = array();
        
        if (!empty($condition['productid'])) {
        	$where[] = 'product_id = ' . $this->_db->quote($condition['productid']);
        }
         
        if (empty($where)) {
            return null;
        }
        
        // WHERE
        $where = implode(' AND ', $where);
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        
        $record = $this->_db->fetchRow($sql);
        
        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_Product_Record_Product', $record);
    }
    
    
    /**
     * Get records
     * 
     * SELECT product_id AS productid, product_name AS productname FROM md_product WHERE
     * 
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getProducts(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_product';
        $columns = 'product_id AS productid, product_name AS productname';
        $where   = array();
        $order   = array();
        $limit   = '';
        
        if (isset($condition['all']) && $condition['all']) {
        	$where = array('1 = 1');
        }
        
        if (empty($where)) {
            return new Oray_Dao_Recordset();
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

        return new Oray_Dao_Recordset($records, 'Dao_Md_Product_Record_Product');
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
        
    }
    
    /**
     * Create record
     * 
     * @param $params
     * @return int|false
     */
    public function createRecord(array $params)
    {
        
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
        
    }
    
    /**
     * Delete record
     * 
     * @param int $recordId
     * @return boolean
     */
    public function deleteRecord($recordId)
    {
    	
    }
}