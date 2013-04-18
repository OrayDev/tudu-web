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
 * @link       http://www.tudu.com/
 * @version    $Id: Mobile.php 2206 2012-10-11 07:06:15Z web_op $
 */
class Dao_Md_User_Mobile extends Oray_Dao_Abstract
{
    /**
     * 有效的
     *
     * @var int
     */
    const STATUS_VALID   = 0;

    /**
     * 已使用的
     *
     * @var int
     */
    const STATUS_USED    = 1;

    /**
     * 无效的
     *
     * @var int
     */
    const STATUS_INVALID = 2;

    /**
     * 验证码默认长度
     *
     * @var int
     */
    const DEFAULT_LENGTH = 6;

    /**
     * 获取用户绑定手机号
     */
    public function getBind(array $condition, $filter = null)
    {
        $table   = 'md_user_mobile';
        $columns = 'org_id AS orgid, user_id AS userid, mobile';
        $where   = array();
        $bind    = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
        }

        if (!empty($condition['userid'])) {
            $where[] = 'user_id = :userid';
        }

        if (!empty($condition['mobile'])) {
            $where[] = 'mobile = :mobile';
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $condition);

            if (!$record) {
                return null;
            }

            return $record;

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 创建绑定手机
     *
     * @param array $params
     */
    public function createBind(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['mobile']))
        {
            return false;
        }

        $table = 'md_user_mobile';
        $bind  = array(
            'org_id'  => $params['orgid'],
            'user_id' => $params['userid'],
            'mobile'  => $params['mobile']
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除绑定手机
     *
     * DELETE FROM md_user_mobile WHERE org_id = :orgid AND user_id = :userid
     *
     * @param string $orgId
     * @param string $userId
     */
    public function deleteBind($orgId, $userId)
    {
        $sql = "DELETE FROM md_user_mobile WHERE org_id = :orgid AND user_id = :userid";

        try {
            $this->_db->query($sql, array('orgid' => $orgId, 'userid' => $userId));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 是否已经绑定
     *
     * @param string $mobile
     * @return boolean
     */
    public function existBind($mobile)
    {
        if (empty($mobile)) {
            return false;
        }

        $sql = "SELECT COUNT(*) FROM md_user_mobile WHERE mobile = :mobile";

        try {
            $count = (int) $this->_db->fetchOne($sql, array('mobile' => $mobile));
            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建验证码记录
     */
    public function createCode(array $params, $expireTime = 3600)
    {
        if (empty($params['mobile'])
            || empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['code']))
        {
            return false;
        }

        $table = 'md_user_mobile_auth';
        $bind  = array(
            'org_id'  => $params['orgid'],
            'user_id' => $params['userid'],
            'mobile'  => $params['mobile'],
            'code'    => $params['code'],
            'expire_time' => time() + $expireTime
        );

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        // 把未使用的验证码设置为无效
        if (!$this->updateCode($params['orgid'], $params['userid'], $params['mobile'], self::STATUS_INVALID)) {
            return false;
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新验证码记录
     */
    public function updateCode($orgId, $userId, $mobile, $status = self::STATUS_USED)
    {
        if (empty($orgId) || empty($userId) || empty($mobile) || !is_int($status)) {
            return false;
        }

        $sql = "UPDATE md_user_mobile_auth SET status = {$status} "
             . "WHERE status = 0 AND mobile = :mobile AND org_id = :orgid AND user_id = :userid";

        try {
            $this->_db->query($sql, array('mobile' => $mobile, 'orgid' => $orgId, 'userid' => $userId));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 判断验证码是否正确
     *
     * @param string $orgId
     * @param string $userId
     * @param string $mobile
     * @param string $code
     */
    public function checkCode($orgId, $userId, $mobile, $code)
    {
        if (empty($orgId) || empty($userId) || empty($mobile) || empty($code)) {
            return false;
        }

        $sql = 'SELECT code FROM md_user_mobile_auth WHERE org_id = :orgid AND user_id = :userid '
             . 'AND mobile = :mobile AND expire_time > unix_timestamp() AND status = 0 '
             . 'ORDER BY mobile_auth_id DESC limit 1';

        try {
            $value = $this->_db->fetchOne($sql, array('mobile' => $mobile, 'orgid' => $orgId, 'userid' => $userId));

            if ($value && strcasecmp($code, $value) === 0) {
                return true;
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return false;
    }

    /**
     * 获取验证码
     *
     * @param int $length
     * @return string
     */
    public static function getCode($length = self::DEFAULT_LENGTH)
    {
        return Oray_Function::randKeys($length, '0123456789');
    }
}