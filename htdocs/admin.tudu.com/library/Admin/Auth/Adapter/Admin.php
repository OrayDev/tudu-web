<?php
/**
 * 后台用户登录验证处理类
 *
 * LICENSE
 *
 *
 * @category   TuduX_Auth
 * @package    TuduX_Auth
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: Admin.php 1166 2011-09-28 04:02:24Z cutecube $
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
class Admin_Auth_Adapter_Admin implements Zend_Auth_Adapter_Interface
{
    /**
     * Database Connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     *
     * @var mixed
     */
    protected $_identity;

    /**
     *
     * @var string
     */
    protected $_username;

    /**
     *
     * @var string
     */
    protected $_password;

    /**
     *
     * @var string
     */
    protected $_config;

    /**
     *
     * @var string
     */
    protected $_userId;

    /**
     *
     * @var string
     */
    protected $_orgId;

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
                case 'username':
                    $this->setUsername($item);
                    break;
                case 'password';
                    $this->setPassword($item);
                    break;
            }
        }
    }

	/**
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $sql = "SELECT u.org_id AS orgid, u.user_id AS userid, ui.true_name AS truename, "
             . "ui.password, u.status, u.expire_date AS expiredate, o.ts_id AS tsid, "
             . "a.admin_level AS adminlevel, a.admin_type AS admintype, o.status AS orgstatus , ud.skin "
             . "FROM md_user u "
             . "LEFT JOIN md_organization o ON u.org_id = o.org_id "
             . "LEFT JOIN md_user_info ui ON u.org_id = ui.org_id AND u.user_id = ui.user_id "
             . "LEFT JOIN md_user_data ud ON u.org_id = ud.org_id AND u.user_id = ud.user_id "
             . "INNER JOIN md_site_admin a ON u.user_id = a.user_id "
             . "WHERE u.user_id = " . $this->_db->quote($this->_userId) . " "
             . "AND u.org_id = " . $this->_db->quote($this->_orgId);

        $row = $this->_db->fetchRow($sql);

        do {
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

            if ($row['orgstatus'] == 1) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'org forbid';
                break;
            }

            if ($row['status'] == 0) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'forbid';
                break;
            }

            if (md5($this->_password) != $row['password']) {
                $this->_resultInfo['code'] = Zend_Auth_Result::FAILURE;
                $this->_resultInfo['message'][] = 'failure';
                break;
            }

            $row['address'] = $row['userid'] . '@' . $row['domainname'];

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
     * Sets the username for binding
     *
     * @param  string $username The username for binding
     * @return Admin_Auth_Adapter_Admin Provides a fluent interface
     */
    public function setUsername($username)
    {
        list($this->_userId, $this->_orgId) = explode('@', $username . '@');
        $this->_username = $username;
        return $this;
    }

    /**
     * Sets the passwort for the account
     *
     * @param  string $password The password of the account being authenticated
     * @return Admin_Auth_Adapter_Admin Provides a fluent interface
     */
    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }

    /**
     *
     * @param $db
     * @return Admin_Auth_Adapter_Admin Provides a fluent interface
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