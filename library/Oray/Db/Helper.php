<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Db
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Helper.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * @category   Oray
 * @package    Oray_Db
 * @subpackage Helper
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Db_Helper
{
    /**
     * 保存数据库名称数据
     *
     * @var array
     */
    private static $_dbNames = array();

    /**
     * 保存Zend Db的数据
     *
     * @var boolean
     */
    private static $_dbAdapters = array();

    /**
     * 是否在类摧毁时自动关闭已设置的数据库连接
     *
     * @var boolean
     */
    private $_autoClose = true;

    /**
     * Singleton instance
     *
     * @var Oray_Db_Helper
     */
    protected static $_instance = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {}

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_autoClose) {
            self::closeConnection();
        }
    }

    /**
     * Returns an instance of Oray_Db_Helper
     *
     * Singleton pattern implementation
     *
     * @return Oray_Db_Helper Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Zend_Db::factory的封装，主要进行密码转换
     * 
     * 非密码加密形式的，请直接使用Zend_Db::factory
     * 
     * @param array $params
     * @return Zend_Db_Adapter_Abstract
     */
    public static function factory(array $params)
    {
        $adapter = $params['adapter'];
        $config = $params['params'];
        $config['password'] = Oray_Function::decryptString($config['password']);
        return Zend_Db::factory($adapter, $config); 
    }

    /**
     * 格式化数据表名称，返回一个完整的数据表名称
     *
     * @param  string $table
     * @param  string $name
     * @return string
     */
    public static function formatTable($table, $name, $owner = 'dbo')
    {
        $trueDbname = self::getDbName($name);
        if (!empty($owner)) {
            $table = "$owner.$table";  
        }
        if (!empty($trueDbname)) {
            $table = "$trueDbname.$table";
        }
        return $table;
    }

    /**
     * 设置多个数据库名称变量
     *
     * @param  array $dbnames
     * @return Oray_Db_Helper
     */
    public function setDbNames(array $dbNames)
    {
        foreach ($dbNames as $name => $value) {
            $this->setDbName($name, $value);
        }
        return $this;
    }

    /**
     * 设置单个数据库名称变量
     *
     * @param  string $name
     * @param  string $value
     * @return Oray_Db_Helper
     */
    public function setDbName($name, $value)
    {
        if (!empty($name) && !empty($value)) {
            self::$_dbNames[$name] = $value;
        }
        return $this;
    }
    
    /**
     * 是否自动关闭数据库
     * 
     * @param boolean $autoClose
     * @return Oray_Db_Helper
     */
    public function setAutoClose($autoClose)
    {
        $this->_autoClose = (boolean) $autoClose;
        return $this;
    }

    /**
     * 获取数据库实际名称
     *
     * @param  string $name
     * @return string
     */
    public static function getDbName($name)
    {
        if (!isset(self::$_dbNames[$name])) {
            return null;
        }
        return self::$_dbNames[$name];
    }

    /**
     * 获取数据库对象
     *
     * @param  string $db
     * @return Zend_Db_Adapter_Abstract
     */
    private static function _get($db)
    {
        return isset(self::$_dbAdapters[$db]) ? self::$_dbAdapters[$db] : null;
    }

    /**
     * 获取数据库对象
     *
     * @param  string $db
     * @throws Oray_Db_Exception
     * @return Zend_Db_Adapter_Abstract
     */
    public static function get($db)
    {
        $dbAdapter = self::_get($db);
        if (!$dbAdapter instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Oray/Db/Exception.php';
            throw new Oray_Db_Exception('No adapter found for ' . __CLASS__);
        }
        return $dbAdapter;
    }

    /**
     * 设置数据库对象
     *
     * 当指定的参数$db值存在时，会被忽略
     *
     * @param  string $db
     * @param  Zend_Db_Adapter_Abstract $dbAdapter
     * @param  boolean $overwrite
     * @return Oray_Db_Helper
     */
    public function set($db, Zend_Db_Adapter_Abstract $dbAdapter, $overwrite = false)
    {
        if (!isset(self::$_dbAdapters[$db]) || $overwrite) {
            self::$_dbAdapters[$db] = $dbAdapter;
        }
        return $this;
    }

    /**
     * 修正NULL值，如果是NULL值返回Zend_Db_Expr对象
     *
     * @param mixed $value
     * @param mixed
     */
    public static function fixNullValue($value)
    {
        if (null === $value) {
            return new Zend_Db_Expr('NULL');
        }
        return $value;
    }
    
    /**
     * 格式化时间，如果是NULL值返回Zend_Db_Expr对象
     * 
     * @param int $timestamp
     * @param string $format
     */
    public static function formatDateTime($timestamp, $format = 'Y-m-d H:i:s')
    {
        if (null === $timestamp) {
            return new Zend_Db_Expr('NULL');
        }
        $date = date($format, $timestamp);
        return $date;
    }
    
    /**
     * 关闭数据库对象
     *
     * 当参数$db为null时，关闭所有已注册的数据库连接
     *
     * @param  string $db
     * @return void
     */
    public static function closeConnection($db = null)
    {
        foreach (self::$_dbAdapters as $key => $dbAdapter) {
            if (null == $db || $key == $db) {
                if ($dbAdapter instanceof Zend_Db_Adapter_Abstract) {
                    $dbAdapter->closeConnection();
                }
            }
        }
    }
}