<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Session.php 1573 2012-02-13 10:37:27Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Session extends Oray_Dao_Abstract
{

    /**
     * 创建用户验证信息记录
     *
     * @param $condition
     * @param $filter
     */
    public function createSession(array $params)
    {
        $table = 'md_user_session';
        $bind  = array();

        if (empty($params['sessionid'])
            || empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['logintime']))
        {
            return false;
        }

        $bind = array(
            'session_id' => $params['sessionid'],
            'org_id'     => $params['orgid'],
            'user_id'    => $params['userid'],
            'login_time' => $params['logintime'],
        );

        if (!empty($params['expiretime']) || $params['expiretime'] < 0) {
            $bind['expire_time'] = $params['expiretime'];
        }

        if (!empty($params['loginip']) || $params['loginip'] < 0) {
            $bind['login_ip'] = $params['loginip'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['sessionid'];
    }

    /**
     * 删除用户验证记录信息
     *
     * @param string sessionid
     */
    public function deleteSession($sessionId)
    {
        if (empty($sessionId)) {
            return false;
        }

        $sql = "DELETE FROM md_user_session WHERE session_id = :sessionid";

        try {
            $this->_db->query($sql, array('sessionid' => $sessionId));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return fasle;
        }

        return true;
    }

    /**
     * 生成验证ID
     *
     * @param string $account
     */
    public static function getSessionId($account)
    {
        return md5($account . time() . rand(10000, 99999));
    }
}