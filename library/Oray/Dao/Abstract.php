<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Oray_Dao_Abstract
{
    /**
     * Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Default Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected static $_defaultDb;

    /**
     * 默认分页大小
     *
     * @var int
     */
    protected static $_defaultPageSize = 20;
    
    /**
     * 日志操作对象
     * 
     * @var Zend_Log
     */
    protected static $_logger;
    
    /**
     * @var callback
     */
    protected static $_errorHandler;

    /**
     * Oray_Dao_Record
     *
     * @var string
     */
    private $_recordClass = 'Oray_Dao_Record';

    /**
     * Construct
     *
     * @param mixed $db
     */
    public function __construct($db = null)
    {
        $this->_setAdapter($db)
             ->_setupDatabaseAdapter();
        $this->init();
    }

    /**
     * Sets the default Zend_Db_Adapter_Abstract for all Oray_Dao_Abstract objects.
     *
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return void
     */
    public static function setDefaultAdapter($db = null)
    {
        self::$_defaultDb = self::_setupAdapter($db);
    }

    /**
     * Gets the default Zend_Db_Adapter_Abstract for all Oray_Dao_Abstract objects.
     *
     * @return Zend_Db_Adapter_Abstract or null
     */
    public static function getDefaultAdapter()
    {
        return self::$_defaultDb;
    }

    /**
     * Sets the default page size for all Oray_Dao_Abstract objects.
     *
     * @param int $size
     * @return void
     */
    public static function setDefaultPageSize($size)
    {
        self::$_defaultPageSize = $size;
    }
    
    /**
     * 
     * @param callback $callback
     */
    public static function registerErrorHandler($callback)
    {
        self::$_errorHandler = $callback;
    }
    
    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Oray_Dao_Abstract Provides a fluent interface
     */
    protected function _setAdapter($db)
    {
        $this->_db = self::_setupAdapter($db);
        return $this;
    }

    /**
     * Gets the Zend_Db_Adapter_Abstract for this particular Oray_Dao_Abstract object.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_db;
    }

    /**
     * @param  mixed $db Either an Adapter object, or a string naming a Registry key
     * @return Zend_Db_Adapter_Abstract
     * @throws Oray_Dao_Exception
     */
    protected static function _setupAdapter($db)
    {
        if ($db === null) {
            return null;
        }
        if (is_string($db)) {
            require_once 'Oray/Db/Helper.php';
            $db = Oray_Db_Helper::get($db);
        }
        if (!$db instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Oray/Dao/Exception.php';
            throw new Oray_Dao_Exception('Argument must be of type Zend_Db_Adapter_Abstract, or a Registry key where a Oray_Db_Helper object is stored');
        }
        return $db;
    }

    /**
     * Initialize database adapter.
     *
     * @return void
     */
    protected function _setupDatabaseAdapter()
    {
        if (!$this->_db) {
            $this->_db = self::getDefaultAdapter();
            if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
                require_once 'Oray/Dao/Exception.php';
                throw new Oray_Dao_Exception('No adapter found for ' . __CLASS__);
            }
        }
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * 格式化排序参数
     *
     * 返回格式如 array('name' => 'ASC', 'name' => 'DESC') 的数组
     *
     * @param mixed $sort
     * @return array
     */
    protected function _formatSort($sort)
    {
        $result = array();
        if (is_string($sort)) {
            $sort = explode(',', $sort);
        }
        if (!is_array($sort)) {
            return $result;
        }
        foreach ($sort as $key => $val) {
            if (is_integer($key)) {
                $val = explode(' ', trim($val));
                if (count($val) == 1) {
                    $val[1] = 'ASC';
                }
            } else {
                $val = array($key, $val);
            }
            $result[$val[0]] = (strtoupper($val[1]) == 'ASC') ? 'ASC' : 'DESC';
        }

        return $result;
    }
    
    /**
     * 捕获例外
     * 
     * @param Exception $e
     * @param string $method
     */
    protected function _catchException(Exception $e, $method)
    {
        if (null !== self::$_errorHandler && is_callable(self::$_errorHandler)) {
            call_user_func(self::$_errorHandler, $e, $method);
        }
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
        if (is_string($timestamp)) {
            return $timestamp;
        }
        $date = date($format, $timestamp);
        return $date;
    }
}