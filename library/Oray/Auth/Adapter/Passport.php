<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Auth
 * @subpackage Oray_Auth_Adapter_Passport
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Passport.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * Oray护照验证适配器
 *
 * 通过Oray护照进行验证，支持多种验证方式，成功返回护照的基本信息
 *
 * @category   Oray
 * @package    Oray_Auth
 * @subpackage Oray_Auth_Adapter_Passport
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Auth_Adapter_Passport implements Zend_Auth_Adapter_Interface
{
    /**
     * Database Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db = null;
    
    /**
     * Db Name
     * 
     * @var string
     */
    protected $_dbName = null;

    /**
     * $_identity - Identity value
     *
     * @var mixed
     */
    protected $_identity = null;

    /**
     * Result info
     *
     * @var array
     */
    protected $_resultInfo = null;

    /**
     * The username of the account being authenticated.
     *
     * @var string
     */
    protected $_username = null;

    /**
     * The password of the account being authenticated.
     *
     * @var string
     */
    protected $_password = null;

    /**
     * The auth type of password.
     *
     * @var string
     */
    protected $_authType = null;

    /**
     * Sets username and password etc for authentication
     *
     * @return void
     */
    public function __construct($username, $password, $authType, $config = null)
    {
    	$this->setUsername($username)
    	     ->setPassword($password)
    	     ->setAuthType($authType)
    	     ->setConfig($config);
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot
     *                                     be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        // 数据库读取操作
		$sql = 'SELECT U.id, U.account, U.password, U.rnd, U.isenable '
		     . 'FROM ' . $this->_dbName . '.dbo.Users U '
		     . 'WHERE ';

		if (is_int($this->_username)) {
		    $where = 'U.id = ?';
		} elseif (strpos($this->_username, '@') > 0) {
		    $where = 'U.account = (SELECT account FROM ' . $this->_dbName . '.dbo.EmailAccount WHERE email = ?)';
		} else {
		    $where = 'U.account = ?';
		}

        $sql .= $this->_db->quoteInto($where, $this->_username);
        $row = $this->_db->fetchRow($sql);
        
        do {
            if (!$row) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                $this->_resultInfo['message'][] = 'not found';
                break;
            }
            
            if ($row['isenable'] != 1) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'forbid';
                break;
            }
            
            $row['password'] = bin2hex($row['password']);
            $row['account'] = rtrim($row['account']);
            $valid = false;
            
                    /**
             * @see Oray_Coder_Passport
             */
            require_once 'Oray/Coder/Passport.php';

            switch ($this->_authType) {
                case Oray_Auth::AUTH_TYPE_CLEAR:
                    $valid = Oray_Coder_Passport::checkCode($row['password'],
                                                            $this->_password,
                                                            $row['account']);
                    break;
                case Oray_Auth::AUTH_TYPE_MD5:
                    $valid = Oray_Coder_Passport::checkCode($row['password'],
                                                            $this->_password,
                                                            $row['account'],
                                                            true);
                    break;
                case Oray_Auth::AUTH_TYPE_AUTH:
                    $valid = (!empty($row['rnd']) && $this->_password == $row['rnd']);
                    break;
                case Oray_Auth::AUTH_TYPE_AUTO:
                    $valid = true;
                default:
                    break;
            }

            if (!$valid) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'failure';
                break;
            }
            
            $this->setIdentity($row);
            $this->_resultInfo['code'] = Zend_Auth_Result::SUCCESS;
            $this->_resultInfo['message'][] = 'success';
            
        } while (false);
       
        return new Zend_Auth_Result(
            $this->_resultInfo['code'],
            $this->_identity,
            $this->_resultInfo['message']
            );
    }

    /**
     * setIdentity() - set the value to be used as the identity
     *
     * @param  array $row
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setIdentity($row)
    {
        $identity = array(
            Oray_Auth::SESSION_UID     => (int) $row['id'],
            Oray_Auth::SESSION_ACCOUNT => $row['account'],
            Oray_Auth::SESSION_AUTH	   => $row['rnd']
        );

        $this->_identity = $identity;
        return $this;
    }

    /**
     * Sets the db for query
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }
    
    /**
     * Set the config
     * 
     * @param array $config
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setConfig($config)
    {
        if (!is_array($config)) {
            return $this;
        }
        
        foreach($config as $name => $value) {
            switch ($name) {
                case 'db':
                    $this->setDb($value);
                    break;
                case 'dbname':
                    $this->_dbName = $value;
                    break;
                default:
                    break;
            }
        }
        
        return $this;
    }

    /**
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Oray_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setAuthType($authType)
    {
        $this->_authType = (string) $authType;
        return $this;
    }
}

