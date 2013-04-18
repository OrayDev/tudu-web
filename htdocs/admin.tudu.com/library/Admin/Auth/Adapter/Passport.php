<?php
/**
 * Oray护照跳转登录流程处理
 *
 * LICENSE
 *
 *
 * @category   TuduX_Auth
 * @package    TuduX_Auth
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: Passport.php 1479 2012-01-10 08:31:46Z cutecube $
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @category  TuduX_Auth
 * @package   TuduX_Auth
 */
class Admin_Auth_Adapter_Passport implements Zend_Auth_Adapter_Interface
{
    /**
     * Database Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Oray Database Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbOray;

    /**
     *
     * @var mixed
     */
    protected $_identity;

    /**
     *
     * @var string
     */
    protected $_account;

    /**
     *
     * @var string
     */
    protected $_auth;

    /**
     *
     * @var int
     */
    protected $_timeStamp;

    /**
     *
     * @var string
     */
    protected $_authKey;

    /**
     *
     * @var string
     *
     */
    protected $_orgId;

    /**
     *
     * @var string
     */
    protected $_config;

    /**
     *
     * @var array
     */
    protected $_resultInfo = array();

    /**
     * __construct() - Sets configuration options
     *
     *
     * @param array                    $config
     * @return void
     */
    public function __construct(array $config = null)
    {
        foreach ($config as $key => $item) {
            switch ($key) {
                case 'db':
                    $this->setDb($item);
                    break;
                case 'account':
                    $this->setAccount($item);
                    break;
                case 'auth';
                    $this->setAuth($item);
                    break;
                case 'time';
                    $this->setTime($item);
                    break;
                case 'authkey':
                case 'key':
                    $this->setAuthKey($item);
                    break;
                case 'dboray':
                    $this->_dbOray = $item;
                    break;
            }
        }
    }

    /**
     * @return Zend_Auth_Result
     */
    public function authenticate ()
    {
        do {
            // 验证串超时
            if (abs(time() - $this->_timeStamp) > 1800) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'timeout';
                break;
            }

            // 验证串有效性
            if (md5($this->_account . $this->_orgId . $this->_timeStamp . $this->_authKey) != $this->_auth) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'invalid';
                break;
            }

            $orgId = $this->_orgId;

            // 读取图度组织信息
            $sql = "SELECT org_id AS orgid, ts_id AS tsid, expire_date AS expiredate, status AS orgstatus, 'PASSPORT' AS admin_type, "
                 . "3 AS admintype, ".$this->_db->quote('ACCOUNT^'.$this->_account)." AS userid, '{$orgId}.tudu.com' AS domainname, 1 AS ispassport, "
                 . $this->_db->quote($this->_account) . ' AS truename '
                 . "FROM md_organization "
                 . "WHERE org_id = " . $this->_db->quote($orgId);
            //echo $sql;exit();
            $row = $this->_db->fetchRow($sql);

            if (!$row) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                $this->_resultInfo['message'][] = 'not found';
                break;
            }

            if (!empty($row['expiredate']) && strtotime($row['expiredate']) < time()) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'expired';
                break;
            }

            $row['truename'] = $this->_account;

            $this->_identity = $row;
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
     * Sets the username for binding
     *
     * @param  string $account Oray护照
     * @return Admin_Auth_Adapter_Passport
     */
    public function setAccount($account)
    {
        $this->_account = $account;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $auth 登录验证串
     * @return Admin_Auth_Adapter_Passport
     */
    public function setAuth($auth)
    {
        $this->_auth = $auth;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $orgId 登录指定的组织
     * @return Admin_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setOrgId($orgId)
    {
        $this->_orgId = $orgId;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $timeStamp 登录请求时间
     * @return Admin_Auth_Adapter_Passport
     */
    public function setTime($timeStamp)
    {
        $this->_timeStamp = (int) $timeStamp;
        return $this;
    }

    /**
     *
     * @param string $authKey 验证串公钥
     * @return Admin_Auth_Adapter_Passport
     */
    public function setAuthKey($authKey)
    {
        $this->_authKey = $authKey;
        return $this;
    }

    /**
     *
     * @param $db
     * @return Admin_Auth_Adapter_Passport Provides a fluent interface
     */
    public function setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }

    /**
     *
     * @param $identity
     */
    public function setIdentity(array $identity)
    {
        $this->_identity = $identity;
        return $this;
    }
}