<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Db
 * @subpackage Paginator
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Mssql.php 9805 2012-04-09 09:17:45Z cutecube $
 */

/**
 * @see Oray_Db_Paginator
 */
require_once 'Oray/Db/Paginator.php';

/**
 * @category   Oray
 * @package    Oray_Db
 * @subpackage Paginator
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Db_Paginator_Mssql extends Oray_Db_Paginator
{
    private $_defaultSort = 'ASC';

    /**
     * 排序的类型
     *
     * @var string
     */
    private $_sort;

    /**
     * 唯一标识表达式
     *
     * 可以是数据表里的主键或排序用的字段或是用于运算的表达式，如
     * CONVERT(varchar, createtime, 21) +
     * REPLICATE('0', 10 - DATALENGTH(CAST(id AS varchar))) + CAST(id AS varchar)
     * 不同排序的条件会影响到此值
     * 必须保证此值在结果中是唯一的，错误的传值会导致查询异常
     *
     * @var string
     */
    private $_identify;

    /**
     * 查询记录
     *
     * @param string $where
     * @return array
     */
    protected function _query($where)
    {
        $order = $this->_getOrder();

        if ($this->_currentPage > 1) {
            $filter = $this->_getFilter($where, $order);
            $where .= ($where ? ' AND' : 'WHERE') . ' (' . $filter . ')';
        }

        $sql = "SELECT TOP {$this->_pageSize} {$this->_columns} "
             . "FROM {$this->_table} $where $order";

        return $this->_db->fetchAll($sql);
    }

    /**
     * 获取唯一标识表达式
     *
     * @return string
     */
    protected function _getIdentity()
    {
        if (!$this->_identify) {

            // 使用排序的字段替换
            if ($this->_order) {
                return $this->_order;
            }

            // 使用主键
            return $this->_primary;
        }
        return $this->_identify;
    }

    /**
     * 获取过滤条件
     *
     * @param string $where
     * @param string $order
     * @return string
     */
    private function _getFilter($where, $order)
    {
        $this->_calTable = $this->_getCalTable();
        $isAsc = ($this->_getSort() == 'ASC');
        $identity = $this->_getIdentity();

        $count = ($this->_pageSize * ($this->_currentPage - 1));

        $sql = "SELECT " . ($isAsc ? 'MAX' : 'MIN') . "([identity]) FROM ("
             . "SELECT TOP {$count} {$identity} [identity] "
             . "FROM {$this->_calTable} $where $order"
             . ") TEMP";

        $sql = $identity . ($isAsc ? ' > ' : ' < ') . '(' . $sql . ')';
        return $sql;
    }

    /**
     * 获取排序条件
     *
     * @return string
     */
    protected function _getOrder()
    {
        $order = array();
        if ($this->_order) {
            $order[] = $this->_order . ' ' . $this->_getSort();
        }
        if (strcasecmp($this->_order, $this->_primary) !== 0) {
            $order[] = $this->_primary . ' ' . $this->_getSort();
        }
        return 'ORDER BY ' . implode(',', $order);
    }

    /**
     * 获取排序类型
     *
     * @return string
     */
    protected function _getSort()
    {
        if (!$this->_sort) {
            return $this->_defaultSort;
        }
        return $this->_sort;
    }

    /**
     * 设置唯一标识表达式
     *
     * @param string $identify
     * @return Oray_Db_Paginator_Mssql
     */
    public function identity($identify)
    {
        $this->_identify = $identify;
        return $this;
    }

    /**
     * 设置排序条件，此类只允许一个字段进行排序 :(
     *
     * @param string $order
     * @return Oray_Db_Paginator_Mssql
     */
    public function order($order)
    {
        $arr = explode(',', trim($order));
        $order = array_shift($arr);

        $arr = explode(' ', $order, 2);
        if (count($arr) == 2) {
            $this->_order = $arr[0];
            $this->_sort = strtoupper($arr[1]);
        } else {
            $this->_order = $order;
            $this->_sort  = $this->_defaultSort;
        }

        return $this;
    }
}