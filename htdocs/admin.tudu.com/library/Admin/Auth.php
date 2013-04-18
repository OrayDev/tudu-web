<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Auth.php 1547 2012-02-03 07:22:07Z web_op $
 */

/**
 * @category   Oray
 * @package    Oray_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Admin_Auth
{
    
    const SESSION_UID     = 'userid';
    const SESSION_SID     = 'sessionid';
    const SESSION_AUTH    = 'auth';
    const SESSION_ACCOUNT = 'account';

    /**
     * Singleton instance
     *
     * @var Auth
     */
    protected static $_instance = null;

    /**
     * String name of base adapter class
     *
     * @var string
     */
    protected $_adapter = 'admin';
    
    /**
     * adapter config
     * 
     * @var string
     */
    protected $_config = null;
    
    /**
     * Session ID
     * 
     * @var string
     */
    protected $_sessionId = null;

    /**
     * Persistent storage handler
     *
     * @var Zend_Auth_Storage_Interface
     */
    protected $_storage = null;

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
     * Returns an instance of Auth
     *
     * Singleton pattern implementation
     *
     * @return Oray_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Zend_Auth_Storage_Interface
     */
    public function getStorage()
    {
        if (null === $this->_storage) {
            /**
             * @see Zend_Auth_Storage_Session
             */
            require_once 'Zend/Auth/Storage/Session.php';
            $this->setStorage(new Zend_Auth_Storage_Session('TUDU', 'ADMIN'));
        }

        return $this->_storage;
    }

    /**
     * Get session Id
     * 
     * @return string
     */
    public function getSessionId()
    {
        if (null === $this->_sessionId) {
            if (method_exists($this->getStorage(), 'getSessionId')) {
                $this->_sessionId = $this->getStorage()->getSessionId();
            }
        }
        return $this->_sessionId;
    }
    
    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Auth_Storage_Interface $storage
     * @return Oray_Auth Provides a fluent interface
     */
    public function setStorage(Zend_Auth_Storage_Interface $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Zend_Auth_Adapter_Interface $adapter
     * @param  string $operator
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter)
    {
        $result = $adapter->authenticate();

        /**
         * ZF-7546 - prevent multiple succesive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $identity = $result->getIdentity();

            $this->getStorage()->write($identity);
        }

        return $result;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Set the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity($verifyCookie = false)
    {
        $storage = $this->getStorage();
        $identity = null;

        if (!$storage->isEmpty()) {
            $identity = $storage->read();
        }

        return $identity;
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }

    /**
     * 设置适配器名称
     *
     * @param string $adapter
     * @return Oray_Auth
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }
    
	/**
     * @return Zend_Auth_Adapter_Interface
     */
    public function getAdapter()
    {
    	if (null === $this->_adapter) {
    		/**
    		 * @see Tudu_Auth_Exception
    		 */
    		require_once 'Tudu/Auth/Exception.php';
    		throw new Tudu_Auth_Exception('Undefined adapter.');
    	}
    	return $this->_adapter;
    }
    
    /**
     * 设置适配器配置
     * 
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * 处理登陆
     * 
     * @param mixed  $username   登陆的用户名，可以是护照名或是邮箱地址
     * @param string $password   验证的密码，可以是明文的密码或是MD5后的密码或是RND值
     * @return Zend_Auth_Result
     */
    final public function login($username, $password)
    {
        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Admin_Auth_Adapter';

        // Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($this->_adapter))));

        /*
         * Load the adapter class. This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($adapterName)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($adapterName);
        }

        return $this->authenticate(new $adapterName($this->_config['db'], $username, $password));
    }
    
    /**
     * 注销登陆
     *
     * @return void
     */
    public function logout()
    {
        $this->clearIdentity();
    }
    
	/**
     * 检测密码是否正确
     * 
     * @param mixed  $username 登陆的用户名，可以是护照名或是邮箱地址
     * @param string $password 验证的密码，可以是明文的密码或是MD5后的密码或是RND值
     * @return Zend_Auth_Result
     */
    final public function checkPassword($username, $password)
    {
        return $this->getAdapter()
        	        ->setUsername($username)
                    ->setPassword($password)
                    ->authenticate();
    }
}
