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
 * @version    $Id: Paginator.php 9805 2012-04-09 09:17:45Z cutecube $
 */

/**
 * @see Oray_Db
 */
require_once 'Oray/Db.php';

/**
 * @see Oray_Dao_Recordset
 */
require_once 'Oray/Dao/Recordset.php';

/**
 * @category   Oray
 * @package    Oray_Db
 * @subpackage Paginator
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Db_Paginator
{
    const ADAPTER      = 'db';
    const PRIMARY      = 'primary';
    const IDENTITY     = 'identity';
    const TABLE        = 'table';
    const CAL_TABLE    = 'calTable';
    const COLUMNS      = 'columns';
    const WHERE        = 'where';
    const ORDER        = 'order';
    const PAGE_SIZE    = 'pageSize';
    const CURRENT_PAGE = 'currentPage';
    const RECORD_CLASS = 'recordClass';
    
    /**
     * 针对数据库取出的原始数据(数组)处理的回调函数
     */
    const RECORD_FILTER = 'recordFilter';

    const DEFAULT_DB = 'defaultDb';
    const DEFAULT_PAGE_SIZE = 'defaultPageSize';
    
    // TODO 下个版本取消
    const CALL_BACK_FUNC = 'callback';

    /**
     * 默认的数据库类
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected static $_defaultDb = null;

    /**
     * 默认分页大小
     *
     * @var integer
     */
    protected static $_defaultPageSize = 10;

    /**
     * 数据库类
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Oray_Dao_Record名称
     *
     * @var string
     */
    protected $_recordClass;

    /**
     * 记录总数
     *
     * @var integer
     */
    protected $_recordCount;

    /**
     * 分页大小
     *
     * @var integer
     */
    protected $_pageSize;

    /**
     * 分页数
     *
     * @var integer
     */
    protected $_pageCount;

    /**
     * 当前页码
     *
     * @var integer
     */
    protected $_currentPage;

    /**
     * 数据表
     *
     * @var string
     */
    protected $_table;

    /**
     * 统计表，用于统计记录数、获取唯一标识用的数据表
     *
     * 一般情况下等同于$_table，但一在些复杂关联表操作时，为了效率考虑，
     * 会从$_table中删除一些跟计算统计无关的表，从而提交执行效率。
     * 统计表涉及到的表有两方面，一个是生成identity需要的表，一个是where条件里用到的表
     * 过多的表用于统计时，开销是很大的，所以尽量删除不需要用到的表，
     * 但需要确保删除前跟删除后两者的查询结果是一致的，否则会带来统计上的错误
     *
     * @var string
     */
    private $_calTable;

    /**
     * 主键
     *
     * @var string
     */
    protected $_primary;

    /**
     * 查询的字段
     *
     * @var string
     */
    protected $_columns = '*';

    /**
     * 查询的条件，不带WHERE
     *
     * @var string
     */
    protected $_where;

    /**
     * 排序条件，不带ORDER
     *
     * @var string
     */
    protected $_order;
    
    /**
     * 原始数据回调处理
     * 
     * @var callbak
     */
    protected $_callBackImpl;

    /**
     * Construct
     *
     * @param mixed $config
     */
    public function __construct($config)
    {
        /**
         * Allow a scalar argument to be the Adapter object or Registry key.
         */
        if (!is_array($config)) {
            $config = array(self::ADAPTER => $config);
        }

        if ($config) {
            $this->setOptions($config);
        }

        if (!$this->_db) {
            $this->_db = self::getDefaultDb();
            if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
                require_once 'Oray/Db/Exception.php';
                throw new Oray_Db_Exception('No db found for ' . get_class($this));
            }
        }
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Oray_Db_Paginator
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case self::ADAPTER:
                    $this->_setDb($value);
                    break;
                case self::PAGE_SIZE:
                    $this->_pageSize = (int) $value;
                    break;
                case self::CURRENT_PAGE:
                    $this->_currentPage = (int) $value;
                    break;
                case self::RECORD_CLASS:
                    $this->_recordClass = $value;
                    break;
                case self::RECORD_FILTER:
                case self::CALL_BACK_FUNC:
                    $this->_callBackImpl = $value;
                    break;
                case self::DEFAULT_DB:
                    self::setDefaultDb($value);
                    break;
                case self::DEFAULT_PAGE_SIZE:
                    self::setDefaultPageSize($value);
                    break;
                default:
                    if ($key[0] !== '_' && method_exists($this, $key)) {
                        $this->$key($value);
                    }
                    break;
            }
        }
        
        return $this;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Zend_Db_Adapter_Abstract
     * @throws Oray_Db_Exception
     */
    protected static function _setupDb($db)
    {
        if ($db === null) {
            return null;
        }
        if (is_string($db)) {
            $db = Oray_Db::get($db);
        }
        if (!$db instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Oray/Db/Exception.php';
            throw new Oray_Db_Exception('Argument must be of type Zend_Db_Adapter_Abstract, or a Registry key where a Zend_Db_Adapter_Abstract object is stored');
        }
        return $db;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Oray_Db_Paginator Provides a fluent interface
     */
    protected function _setDb($db)
    {
        $this->_db = self::_setupDb($db);
        return $this;
    }

    /**
     * Sets the default Zend_Db_Adapter_Abstract for all Oray_Db_Paginator objects.
     *
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultDb($db = null)
    {
        self::$_defaultDb = self::_setupDb($db);
    }

    /**
     * Gets the default Zend_Db_Adapter_Abstract for all Oray_Db_Paginator objects.
     *
     * @return Zend_Db_Adapter_Abstract or null
     */
    public static function getDefaultDb()
    {
        return self::$_defaultDb;
    }

    /**
     * Set the default page size
     *
     * @param int $size
     */
    public static function setDefaultPageSize($size)
    {
        self::$_defaultPageSize = (int) $size;
    }

    /**
     * 执行查询
     *
     * @param int $page 查询的页码
     * @return Oray_Dao_Recordset
     */
    public function query($page = null)
    {
        if (empty($this->_primary)) {
            require_once 'Oray/Db/Paginator/Exception.php';
            throw new Oray_Db_Paginator_Exception('Missing primary parameter');
        }

        if (empty($this->_table)) {
            require_once 'Oray/Db/Paginator/Exception.php';
            throw new Oray_Db_Paginator_Exception('Missing table parameter');
        }

        // 设置页码
        if (is_int($page)) {
            $this->page($page);
        }

        // 查询数据结果
        $data = array();

        $where = $this->_getWhere();

        $this->_recordCount = $this->_getRecordCount($where);
        $this->_pageSize    = $this->_getPageSize();
        $this->_pageCount   = intval(($this->_recordCount - 1) / $this->_pageSize) + 1;

        if ($this->_currentPage < 1
            || $this->_currentPage > $this->_pageCount) {
            $this->_currentPage = 1;
        }

        if ($this->_recordCount > 0) {

            // 分页查询
            $data = $this->_query($where);
            
            if (null !== $this->_callBackImpl && is_callable($this->_callBackImpl)) {
                $data = call_user_func($this->_callBackImpl, $data, true);
            }
        }

        $config = array(
            Oray_Dao_Recordset::RECORD_CLASS => $this->_recordClass,
            Oray_Dao_Recordset::RECORD_COUNT => $this->_recordCount,
            Oray_Dao_Recordset::CURRENT_PAGE => $this->_currentPage,
            Oray_Dao_Recordset::PAGE_SIZE    => $this->_pageSize,
            Oray_Dao_Recordset::PAGE_COUNT   => $this->_pageCount
        );

        return new Oray_Dao_Recordset($data, $config);
    }

    /**
     * 获取记录总数
     *
     * @param string $where
     * @return int
     */
    protected function _getRecordCount($where)
    {
        $this->_calTable = $this->_getCalTable();
        $sql = "SELECT COUNT({$this->_primary}) FROM {$this->_calTable} $where";
        return (int) $this->_db->fetchOne($sql);
    }

    /**
     * 查询记录
     *
     * @param string $where
     * @return array
     */
    protected function _query($where)
    {
        $order = $this->_getOrder();
        $sql = "SELECT {$this->_columns} FROM {$this->_table} $where $order";
        $offset = $this->_pageSize * ($this->_currentPage - 1);
        $sql = $this->_db->limit($sql, $this->_pageSize, $offset);
        return $this->_db->fetchAll($sql);
    }

    /**
     * 获取查询条件
     *
     * @return string
     */
    protected function _getWhere()
    {
        if (!empty($this->_where)) {
            return 'WHERE (' . $this->_where . ')';
        }
        return '';
    }

    /**
     * 获取排序条件
     *
     * @return string
     */
    protected function _getOrder()
    {
        if (!empty($this->_order)) {
            return 'ORDER BY ' . $this->_order;
        }
        return '';
    }

    /**
     * 获取分页大小
     *
     * @return int
     */
    private function _getPageSize()
    {
        if (!$this->_pageSize) {
            return self::$_defaultPageSize;
        }
        return $this->_pageSize;
    }

    /**
     * 获取统计用的数据表
     *
     * @return string
     */
    protected function _getCalTable()
    {
        if (!$this->_calTable) {
            $this->_calTable = $this->_table;
        }
        return $this->_calTable;
    }

    /**
     * 设置数据表
     *
     * @param string $table
     * @return Oray_Db_Paginator
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * 设置字段
     *
     * @param string $columns
     * @return Oray_Db_Paginator
     */
    public function columns($columns)
    {
        $this->_columns = $columns;
        return $this;
    }

    /**
     * 设置查询条件
     *
     * @param string $where
     * @return Oray_Db_Paginator
     */
    public function where($where)
    {
        $this->_where = $where;
        return $this;
    }

    /**
     * 设置排序条件
     *
     * @param string $order
     * @return Oray_Db_Paginator
     */
    public function order($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * 设置主键
     *
     * @param string $primary
     * @return Oray_Db_Paginator
     */
    public function primary($primary)
    {
        $this->_primary = $primary;
        return $this;
    }

    /**
     * 设置分页大小
     *
     * @param int $size
     * @return Oray_Db_Paginator
     */
    public function size($size)
    {
        $this->_pageSize = (int) $size;
        return $this;
    }

    /**
     * 设置当前页码
     *
     * @param int $page
     * @return Oray_Db_Paginator
     */
    public function page($page)
    {
        $this->_currentPage = (int) $page;
        return $this;
    }

    /**
     * 设置统计表
     *
     * @param string $calTable
     * @return Oray_Db_Paginator
     */
    public function calTable($calTable)
    {
        $this->_calTable = $calTable;
        return $this;
    }
}