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
 * @version    $Id: Session.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @see Zend_Auth_Storage_Interface
 */
require_once 'Zend/Auth/Storage/Interface.php';

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @category   Oray
 * @package    Oray_Auth
 * @subpackage Storage
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Auth_Storage_Session implements Zend_Auth_Storage_Interface
{
    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'Oray_Auth';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'storage';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Session namespace
     *
     * @var mixed
     */
    protected $_namespace;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;

    /**
     * Sets session storage options and initializes session namespace object
     *
     * @param  mixed  $namespace
     * @param  mixed  $member
     * @param  string $sessionId
     * @return void
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT, $sessionId = null)
    {
        $this->_namespace = $namespace;
        $this->_member    = $member;
        if (null !== $sessionId && !Zend_Session::sessionExists()) {
        	Zend_Session::setId($sessionId);
        }
        $this->_session   = new Zend_Session_Namespace($this->_namespace);
    }

    /**
     * Returns the session namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->_member;
    }

    /**
     * Returns the id of the session
     * 
     * @return string
     */
    public function getSessionId()
    {
        return Zend_Session::getId();
    }
    
    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !isset($this->_session->{$this->_member});
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return mixed
     */
    public function read()
    {
        return $this->_session->{$this->_member};
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->_session->{$this->_member} = $contents;
    }

    /**
     * Defined by Zend_Auth_Storage_Interface
     *
     * @return void
     */
    public function clear()
    {
        unset($this->_session->{$this->_member});
    }
}
