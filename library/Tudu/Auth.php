<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Auth.php 1552 2012-02-03 08:25:35Z cutecube $
 */

/**
 * @category   Tudu
 * @package    Tudu_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Auth
{
    /**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;

    /**
     * Persistent storage handler
     *
     * @var Zend_Auth_Storage_Interface
     */
    protected $_storage = null;

    /**
     * Zend_Auth_Adapter_Interface
     *
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_adapter = null;

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
     * Returns an instance of Tudu_Auth
     *
     * Singleton pattern implementation
     *
     * @return Tudu_Auth Provides a fluent interface
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
            $this->setStorage(new Zend_Auth_Storage_Session('AUTH'));
        }

        return $this->_storage;
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Auth_Storage_Interface $storage
     * @return Zend_Auth Provides a fluent interface
     */
    public function setStorage(Zend_Auth_Storage_Interface $storage)
    {
        $this->_storage = $storage;
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
     * Sets adapter
     *
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return Tudu_Auth
     */
    public function setAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
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
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->read();
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
     * Login
     *
     * @param string $username
     * @param string $password
     * @param string $ipCheck
     * @param boolean $cookieCheck
     * @return Zend_Auth_Result
     */
    public function login($username, $password, $ipCheck = null, $cookieCheck = null)
    {
        $result = $this->getAdapter()
                       ->setUsername($username)
                       ->setPassword($password)
                       ->authenticate();

        /**
         * ZF-7546 - prevent multiple succesive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     *
     * @param string $username
     */
    public function autoLogin($username)
    {
        $result = $this->getAdapter()
                       ->setUsername($username)
                       ->setAuto(true)
                       ->authenticate();
        /**
         * ZF-7546 - prevent multiple succesive calls from storing inconsistent results
         * Ensure storage has clean state
         */
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * Logout
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