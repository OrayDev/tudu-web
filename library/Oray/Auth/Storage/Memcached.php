<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Auth
 * @subpackage Storage
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Memcached.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @see Zend_Auth_Storage_Interface
 */
require_once 'Zend/Auth/Storage/Interface.php';

/**
 * @category   Oray
 * @package    Oray_Auth
 * @subpackage Storage
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Auth_Storage_Memcached implements Zend_Auth_Storage_Interface
{
    /**
     * Automatic serialization
     * 
     * @var boolean
     */
    
    private $_autoSerialization = true;
    
    /**
     * 
     * @var Memcache
     */
    private $_memcache;

    /**
     * Session id
     * 
     * @var int
     */
    private $_sessionId;
    
    /**
     * Session
     * 
     * @var array
     */
    private $_session;
    
    /**
     * Session expire time
     * 
     * @var int
     */
    private $_expire = 1800;

    /**
     * construct
     * 
     * @param $memcache
     * @param $userId
     */
    public function __construct($memcache, $sessionId = null)
    {
        if ($memcache instanceof Oray_Memcache) {
            $this->_autoSerialization = false;
        }
        $this->_memcache = $memcache;

        if (null === $sessionId && isset($_COOKIE[Oray_Auth::COOKIE_SID])) {
            $sessionId = $_COOKIE[Oray_Auth::COOKIE_SID];
        }

        $this->_sessionId = $sessionId;
    }
    
    /**
     * initialize
     * 
     */
    public function init()
    {
        if (null === $this->_session && null !== $this->_sessionId) {
            $value = $this->_memcache->get($this->_sessionId);
            if (false === $value) {
                $this->clear();
            } else {
                $this->_session = $this->_autoSerialization ? unserialize($value) : $value;
                $this->touch();
            }
        }
    }
    
    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return boolean
     */
    public function isEmpty()
    {
        $this->init();
        return empty($this->_session);
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return mixed
     */
    public function read()
    {
        $this->init();
        return $this->_session;
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->_memcache->set($this->_getSessionId(), $contents, 0, $this->_expire);
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return void
     */
    public function clear()
    {
        if (null !== $this->_sessionId) {
            $this->_memcache->delete($this->_sessionId);
            $this->_session = null;
            $this->_sessionId = null;
            setcookie(Oray_Auth::COOKIE_SID, '', time() - 60*60*24, '/');
        }
    }
    
    /**
     * 更新过期时间
     * 
     * @return void
     */
    public function touch()
    {
        if (null !== $this->_sessionId && $this->_expire) {
            // we try replace() first becase set() seems to be slower
            if (!($result = $this->_memcache->replace($this->_sessionId, $this->_session, 0, $this->_expire))) {
                $result = $this->_memcache->set($this->_sessionId, $this->_session, 0, $this->_expire);
            }
        }
    }
    
    /**
     * Get session id
     * 
     * @return string
     */
    private function _getSessionId()
    {
        if (null === $this->_sessionId) {
            $this->_sessionId = Oray_Function::randKeys(32);
            setcookie(Oray_Auth::COOKIE_SID, $this->_sessionId, null, '/', null);
        }
        return $this->_sessionId;
    }
    
    /**
     * Get session id
     * 
     * @return string
     */
    public function getSessionId()
    {
        return $this->_sessionId;
    }
}
