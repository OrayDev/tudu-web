<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Auth
 * @subpackage Tudu_Auth_Adapter_Passport
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: User.php 1461 2012-01-05 10:51:09Z cutecube $
 */

/**
 * @see Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @category   Tudu
 * @package    Tudu_Auth
 * @subpackage Tudu_Auth_Adapter_Passport
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Auth_Adapter_User implements Zend_Auth_Adapter_Interface
{

    /**
     * Database Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db = null;

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
     *
     * @var string
     */
    protected $_orgId = null;

    /**
     *
     * @var array
     */
    protected $_config = array();

    /**
     *
     * @var string
     */
    private $_userId;

    /**
     *
     * @var string
     */
    private $_domainName;

    /**
     *
     * @var boolean
     */
    private $_auto = false;

    /**
     *
     * @var string
     */
    private $_authId = null;

    /**
     * __construct() - Sets configuration options
     *
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string                   $username
     * @param string                   $password
     * @param array                    $config
     * @return void
     */
    public function __construct(Zend_Db_Adapter_Abstract $db, $username = null, $password = null, $config = null)
    {
        $this->_db = $db;

        if (null !== $username) {
            $this->setUsername($username);
        }

        if (null !== $password) {
            $this->setPassword($password);
        }

        if (null != $config) {
            $this->_config = $config;
        }
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
        $this->_authenticateSetup();

        $columns = "u.org_id, u.user_id, u.unique_id, u.login_retry, u.unlock_time, ui.true_name, "
                 . "ui.password, u.status, u.expire_date, o.ts_id, o.lock_time, o.is_https, o.is_ip_rule, "
                 . "o.time_limit, o.timezone, GROUP_CONCAT(ug.group_id) AS groups ";
        $table   = "md_user u "
                 . "LEFT JOIN md_organization o ON u.org_id = o.org_id "
                 . "LEFT JOIN md_user_info ui ON u.org_id = ui.org_id AND u.user_id = ui.user_id "
                 . "LEFT JOIN md_user_group ug ON u.org_id = ug.org_id AND u.user_id = ug.user_id ";
        $where   = array('u.user_id = :userid');
        $bind    = array('userid' => $this->_userId);

        if (!empty($this->_orgId)) {
            $bind['orgid'] = $this->_orgId;
            $where[]= ' u.org_id = :orgid ';
        }

        if (!empty($this->_domainName)) {
            $bind['domainname'] = $this->_domainName;
            $where[] = 'd.domain_name = :domainname ' ;
        }

        if (!empty($this->_authId)) {
            $table   .= "LEFT JOIN md_user_session s ON s.org_id = u.org_id AND s.user_id = u.user_id ";
            $columns .= ', s.session_id, s.login_time, s.expire_time ';

            $where[] = 's.session_id = :sessionid';
            $bind['sessionid'] = $this->_authId;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE " . implode(' AND ', $where) . " GROUP BY u.org_id, u.user_id";

        $row = $this->_db->fetchRow($sql, $bind);

        do {
            if (!$row) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                $this->_resultInfo['message'][] = 'notexist';
                break;
            }

            if ($row['status'] == 0) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'forbid';
                break;
            }

            // 是否忽略锁定状态
            $ignoreLock = isset($this->_config['ignorelock']) ? $this->_config['ignorelock'] : false;
            // 是否跳过锁定计数
            $skipLock   = isset($this->_config['skiplock']) ? $this->_config['skiplock'] : false;
            $unlockTime = null;
            $retryTimes = null;

            if (!$ignoreLock) {
                // 已被锁定
                $unlockTime = (int) $row['unlock_time'];
                if ($unlockTime > 0 && $unlockTime > time()) {
                    $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                    $this->_resultInfo['message'][] = 'locked';
                    break;
                }

                // 重试次数
                $lockTime  = (int) $row['lock_time'];
                $retryTimes= (int) $row['login_retry'];
                if ($lockTime > 0 && intval($row['login_retry']) >= $lockTime) {
                    $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                    $this->_resultInfo['message'][] = 'locked';
                    $this->_lockUser($row['org_id'], $this->_userId);
                    break;
                }
            }

            if (!$this->_auto && !$this->_authId && (md5($this->_password) != $row['password'])) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'failure';

                if (!$skipLock && $lockTime > 0) {
                    $this->_countFailure($row['org_id'], $this->_userId);
                }
                break;
            }

            if ($this->_authId) {
                // 不存在或已经过期
                if (empty($row['session_id'])
                    || (!empty($row['expire_time']) && intval($row['expire_time']) < time()))
                {
                    $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                    $this->_resultInfo['message'][] = 'failure';

                    break;
                }
            }

            $this->setIdentity($row);
            $this->_resultInfo['code'] = Zend_Auth_Result::SUCCESS;
            $this->_resultInfo['message'][] = 'success';

            // 登录成功，清除失败信息
            if (!$this->_auto && ($unlockTime || $retryTimes)) {
                $this->_clearFailure($row['org_id'], $this->_userId);
            }

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
            'orgid'      => $row['org_id'],
            'userid'     => $row['user_id'],
            'uniqueid'   => $row['unique_id'],
            'address'    => $row['user_id'] . '@' . $row['org_id'],
            'truename'   => $row['true_name'],
            'username'   => $row['user_id'] . '@' . $row['org_id'],
            'ishttps'    => !empty($row['is_https']) ? true : false,
            'iprule'     => !empty($row['is_ip_rule']) ? true : false,
            'groups'     => explode(',', $row['groups']),
            'tsid'       => $row['ts_id']
        );

        if (!empty($row['time_limit'])) {
            $identity['timezone']  = $row['timezone'];
            $timeLimit = explode("\n", $row['time_limit']);
            foreach ($timeLimit as &$item) {
                $item = (int) base_convert($item, 16, 10);
            }
            $identity['timelimit'] = $timeLimit;
        }

        $this->_identity = $identity;
        return $this;
    }

    /**
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Tudu_Auth_Adapter_User Provides a fluent interface
     */
    public function setUsername($username)
    {
        /*list($this->_userId, $this->_domainName) = explode('@', $username . '@');
        $this->_username = $username;
        return $this;*/

        list ($userId, $suffix) = explode('@', $username . '@');

        $this->_username = $username;
        $this->_userId   = $userId;

        if (Oray_Function::isDomainName($suffix)) {
            $this->_domainName = $suffix;
        } else {
            $this->_orgId      = $suffix;
        }

        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Tudu_Auth_Adapter_User Provides a fluent interface
     */
    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }

    /**
     *
     * @param boolean $auto
     */
    public function setAuto($auto) {
        $this->_auto = (boolean) $auto;
        return $this;
    }

    /**
     *
     * @param string $orgId
     */
    public function setOrgId($orgId)
    {
        $this->_orgId = $orgId;
        return $this;
    }

    /**
     * 验证ID
     *
     * @param string $authId
     */
    public function setAuthId($authId)
    {
        $this->_authId = $authId;

        return $this;
    }

    /**
     * Count login failure times
     */
    private function _countFailure($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'UPDATE md_user SET login_retry = login_retry + 1 WHERE '
             . 'org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    private function _lockUser($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $table = 'md_user';
        $bind  = array(
            'login_retry' => 0,
            'unlock_time' => time() + 3600
        );
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND '
               . 'user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    private function _clearFailure($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $table = 'md_user';
        $bind  = array(
            'login_retry' => 0,
            'unlock_time' => null
        );
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND '
               . 'user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws Tudu_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_username == '') {
            $exception = 'A usernme must be supplied for the Tudu_Auth_Adapter_User authentication adapter.';
        } elseif ($this->_orgId == '' && $this->_domainName == '') {
            $exception = 'domain or orgid must be supplied for the Tudu_Auth_Adapter_User authentication adapter.';
        } elseif ($this->_password == '' && !$this->_auto && !$this->_authId) {
            $exception = 'An password must be supplied for the Tudu_Auth_Adapter_User authentication adapter.';
        }

        if (null !== $exception) {
            /**
             * @see Tudu_Auth_Adapter_Exception
             */
            require_once 'Tudu/Auth/Adapter/Exception.php';
            throw new Tudu_Auth_Adapter_Exception($exception);
        }

        return true;
    }

}