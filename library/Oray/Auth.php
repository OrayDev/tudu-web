<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Auth
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Auth.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Auth
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Auth
{
	/**
     * 明文密码验证标识
     */
	const AUTH_TYPE_CLEAR = 'CLEAR';

    /**
     * MD5密码加密验证标识
     */
	const AUTH_TYPE_MD5   = 'MD5';

    /**
     * RND加密验证标识
     */
	const AUTH_TYPE_AUTH  = 'AUTH';

	/**
	 * 自动登陆标识 - 不需要密码
	 */
	const AUTH_TYPE_AUTO  = 'AUTO';

	/**
	 * COOKIE保存的参数名称
	 *
	 */
	const COOKIE_UID      = '_uid';
	const COOKIE_SID      = '_sid';
	const COOKIE_AUTH     = '_auth';
	
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
    protected $_adapter = 'passport';
    
    /**
     * adapter config
     * 
     * @var string
     */
    protected $_config = null;

    /**
     * Cookie保存路径
     *
     * @var string
     */
	protected $_cookiePath = '/';

	/**
	 * Cookie保存域
	 *
	 * @var string
	 */
	protected $_cookieDomain = null;
	
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
            $this->setStorage(new Zend_Auth_Storage_Session('ORAY', 'AUTH'));
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
    public function authenticate(Zend_Auth_Adapter_Interface $adapter, $operator = null)
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
            if (null !== $operator) {
                $identity['operator'] = $operator;
            }
            $this->getStorage()->write($identity);
            
            // 以操作者方式登陆时，不支持Cookie自动验证登陆（会丢失操作者参数）
            if (null !== $operator) {
                $identity[self::SESSION_AUTH] = null;
            }
            $this->_setCookies($identity);
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

        // Verify cookies
        if ($verifyCookie) {

            if (empty($_COOKIE[self::COOKIE_UID]) || empty($_COOKIE[self::COOKIE_AUTH])) {
                if (null !== $identity) {
                    $this->clearIdentity();
                    $identity = null;
                }
            } else {
                if ($_COOKIE[self::COOKIE_UID] != $identity[self::SESSION_UID]) {
                    $this->clearIdentity();
                    $identity = null;
                }
                
                if (null === $identity) {
                    $result = $this->login(
                        (int) $_COOKIE[self::COOKIE_UID],
                        $_COOKIE[self::COOKIE_AUTH],
                        self::AUTH_TYPE_AUTH
                    );
                    
                    if (!$result->isValid()) {
                        $this->clearCookies();
                    }
                    
                    if (!$storage->isEmpty()) {
                        $identity = $storage->read();
                    }
                }
            }
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
     * 读取需要验证的Cookie信息，并设置到变量中
     *
     * @return Oray_Auth
     */
    private function _setCookies($identity)
    {
        setcookie(self::COOKIE_UID, $identity[self::SESSION_UID], null, $this->_cookiePath, $this->_cookieDomain);
        setcookie(self::COOKIE_AUTH, $identity[self::SESSION_AUTH], null, $this->_cookiePath, $this->_cookieDomain);
    	return $this;
    }

    /**
     * 设置Cookie的路径
     *
     * @param string $path
     * @return Oray_Auth
     */
    public function setCookiePath($path)
    {
        $this->_cookiePath = $path;
        return $this;
    }

    /**
     * 设置Cookie的域
     *
     * @param string $domain
     * @return Oray_Auth
     */
    public function setCookieDomain($domain)
    {
        $this->_cookieDomain = $domain;
        return $this;
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
     * 清除验证用的Cookies
     * 
     * @return void
     */
    public function clearCookies()
    {
		setcookie(self::COOKIE_UID, '', time() - 60*60*24, $this->_cookiePath, $this->_cookieDomain);
		setcookie(self::COOKIE_AUTH, '', time() - 60*60*24, $this->_cookiePath, $this->_cookieDomain);
    }

    /**
     * 处理登陆
     *
     * @param mixed  $username 登陆的用户名，可以是护照名或是邮箱地址
     * @param string $password 验证的密码，可以是明文的密码或是MD5后的密码或是RND值
     * @param string $authType 验证类型，指明是明文或是MD5或是RND
     * @param string $operator 当前操作者，可记录谁登陆了此用户名进行操作
     * @return Zend_Auth_Result
     */
    final public function login($username, $password, $authType = self::AUTH_TYPE_CLEAR, $operator = null)
    {
        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Oray_Auth_Adapter';

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

		return $this->authenticate(new $adapterName($username, $password, $authType, $this->_config), $operator);
    }

    /**
     * 自动登陆
     *
     * @return Zend_Auth_Result
     */
    final public function autoLogin($username, $operator = null)
    {
        return $this->login($username, null, self::AUTH_TYPE_AUTO, $operator = null);
    }

    /**
     * 检测密码是否正确
     * @param mixed  $username 登陆的用户名，可以是护照名或是邮箱地址
     * @param string $password 验证的密码，可以是明文的密码或是MD5后的密码或是RND值
     * @param string $authType 验证类型，指明是明文或是MD5或是RND
     * @param string $adapter
     * @param mixed  $config
     * @return Zend_Auth_Result
     */
    final public function checkPassword($username, $password, $authType = self::AUTH_TYPE_CLEAR, $adapter = null, $config = null)
    {
        if (null == $adapter) {
            $adapter = $this->_adapter;
        }
        
        if (null == $config) {
            $config = $this->_config;
        }
        
        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Oray_Auth_Adapter';

        // Adapter no longer normalized- see http://framework.zend.com/issues/browse/ZF-5606
        $adapterName = $adapterNamespace . '_';
        $adapterName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapter))));

        /*
         * Load the adapter class. This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($adapterName)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($adapterName);
        }
        
        $auth = new $adapterName($username, $password, $authType, $config);
        $result = $auth->authenticate();
        
        return $result;
    }
    
    /**
     * 注销登陆
     *
     * @return void
     */
    public function logout()
    {
        $this->clearIdentity();
        $this->clearCookies();
    }
}
