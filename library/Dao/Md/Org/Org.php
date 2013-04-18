<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Org.php 2794 2013-03-26 06:35:42Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Org extends Oray_Dao_Abstract
{
    /**
     * 企业状态
     *
     * @var int
     */
    const STATUS_NORMAL = 0;
    const STATUS_FORBID = 1;
    const STATUS_LOCK   = 2;

    /**
     * 获取组织信息
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Org_Record_Org
     */
    public function getOrg(array $condition, $filter = null)
    {
        $table   = 'md_organization o '
                 . 'LEFT JOIN md_org_info oi ON o.org_id = oi.org_id ';
        $columns = 'o.org_id AS orgid, org_name AS orgname, oi.entire_name AS entirename, ts_id AS tsid, status, '
                 . 'create_time AS createtime, expire_date AS expiredate, '
                 . 'intro, skin, login_skin AS loginskin, is_active as isactive, '
                 . 'password_level AS passwordlevel, lock_time AS locktime, timezone, date_format AS dateformat, '
                 . 'is_ip_rule AS isiprule, o.default_password AS defaultpassword, o.is_https AS ishttps, o.time_limit AS timelimit';
        $where   = array();
        $bind    = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'o.org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if(!empty($condition['host'])) {
            $where[] = 'o.org_id = (SELECT org_id FROM md_org_host WHERE host = :host)';
            $bind['host'] = $condition['host'];
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 0, 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Md_Org_Record_Org', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 创建组织
     *
     * @param array $params
     * @return boolean
     */
    public function createOrg(array $params)
    {
        $table = 'md_organization';

       if (empty($params['orgid'])
            || empty($params['tsid'])
            || (!isset($params['cosid']) || !is_int($params['cosid']))) {
            return false;
        }

        $bind = array(
            'org_id' => strtolower($params['orgid']),
            'ts_id' => (int) $params['tsid'],
            'cos_id' => (int) $params['cosid']
        );

        if (isset($params['orgname'])) {
            $bind['org_name'] = $params['orgname'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['ishttps'])) {
            $bind['is_https'] = $params['ishttps'];
        }

        if (isset($params['expiredate']) && is_int($params['expiredate'])) {
            $bind['expire_date'] = date('Y-m-d', $params['expiredate']);
        }

        if (!empty($params['defaultpassword'])) {
            $bind['default_password'] = $params['defaultpassword'];
        }

        if (isset($params['passwordlevel']) && is_int($params['passwordlevel'])) {
            $bind['password_level'] = (int) $params['passwordlevel'];
        }

        $bind['create_time'] = date('Y-m-d H:i:s');

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['orgid'];
    }

    /**
     * 更新组织信息
     *
     * @param string $orgId
     * @param array  $params
     * @return boolean
     */
    public function updateOrg($orgId, array $params)
    {
        if (!$orgId) {
            return false;
        }

        $bind  = array();
        $table = 'md_organization';
        $where = 'org_id = ' . $this->_db->quote($orgId);

        if (!empty($params['orgname'])) {
            $bind['org_name'] = $params['orgname'];
        }

        if (isset($params['isactive'])) {
            $bind['is_active'] = $params['isactive'];
        }

        if (isset($params['tsid']) && is_int($params['tsid'])) {
            $bind['ts_id'] = $params['tsid'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['expiredate']) && is_int($params['expiredate'])) {
            $bind['expire_date'] = date('Y-m-d', $params['expiredate']);
        }

        if (array_key_exists('logo', $params)) {
            $bind['logo'] = $params['logo'];
        }

        if (array_key_exists('intro', $params)) {
            $bind['intro'] = $params['intro'];
        }

        if (isset($params['passwordlevel']) && is_int($params['passwordlevel'])) {
            $bind['password_level'] = $params['passwordlevel'];
        }

        if (isset($params['locktime']) && is_int($params['locktime'])) {
            $bind['lock_time'] = $params['locktime'];
        }

        if (!empty($params['timezone'])) {
            $bind['timezone'] = $params['timezone'];
        }

        if (!empty($params['dateformat'])) {
            $bind['date_format'] = $params['dateformat'];
        }

        if (array_key_exists('defaultpassword', $params)) {
            $bind['default_password'] = $params['defaultpassword'];
        }

        if (isset($params['skin'])) {
            $bind['skin'] = $params['skin'];
        }

        if (isset($params['loginskin'])) {
            $bind['login_skin'] = $params['loginskin'];
        }

        if (isset($params['ishttps'])) {
            $bind['is_https'] = $params['ishttps'];
        }

        if (isset($params['isiprule'])) {
            $bind['is_ip_rule'] = $params['isiprule'];
        }

        if (array_key_exists('timelimit', $params)) {
            $bind['time_limit'] = $params['timelimit'];
        }

        if (array_key_exists('memo', $params)) {
            $bind['memo'] = $params['memo'];
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除组织
     *
     * @param string $orgId
     * @return boolean
     */
    public function deleteOrg($orgId)
    {
        /**
        if (!$orgId) {
            return false;
        }

        $sqls   = array();
        $sqls[] = 'DELETE FROM md_organization WHERE org_id = ' . $this->_db->quote($orgId);
        */

        return $this->update($orgId, array('status' => self::STATUS_DELETE));
    }

    /**
     * 获取组织信息
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Org_Record_Org
     */
    public function getOrgById($orgId, $filter = null)
    {
        return $this->getOrg(array('orgid' => $orgId), $filter);
    }

    /**
     * 获取组织信息
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Org_Record_Org
     */
    public function getOrgByHost($host, $filter = null)
    {
        return $this->getOrg(array('host' => $host), $filter);
    }

    /**
     * 获取组织Logo
     *
     * @param string $orgId
     * @return string
     */
    public function getLogo($orgId)
    {
        $sql = 'SELECT logo FROM md_organization WHERE org_id = ' . $this->_db->quote($orgId);
        $logo = $this->_db->fetchOne($sql);
        return $logo;
    }

    /**
     * 获取企业的绑定主机列表
     *
     * @param string $orgId
     * @return array
     */
    public function getHosts($orgId)
    {
        if (empty($orgId)) {
            return array();
        }

        $sql = 'SELECT host '
             . 'FROM md_org_host '
             . 'WHERE org_id = ' . $this->_db->quote($orgId);

         $records = $this->_db->fetchCol($sql);
         return $records;
    }

    /**
     *
     * @param string $orgId
     */
    public function getUsedNetdiskQuota($orgId)
    {
        $orgId = $this->_db->quote($orgId);

        $sql = "SELECT SUM(max_nd_quota) FROM md_user WHERE org_id = {$orgId}";

        try {
            $quota = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $quota;
    }

    /**
     * 用户数
     *
     * @param string $orgId
     */
    public function getUserCount($orgId)
    {
        $sql = 'SELECT COUNT(0) FROM md_user WHERE org_id = ' . $this->_db->quote($orgId);

        try {
            $count = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $count;
    }

    /**
     *
     * @param string $orgId
     * @param string $host
     */
    public function addHost($orgId, $host)
    {
        if (!$orgId || empty($host)) {
            return false;
        }

        $table = 'md_org_host';
        $bind  = array(
            'org_id' => $orgId,
            'host'  => $host
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
     *
     * @param string $orgId
     */
    public function getAdmin($orgId)
    {
        $columns = 'u.org_id AS orgid, u.user_id AS userid, u.status ';
        $table   = 'md_site_admin AS a '
                 . 'LEFT JOIN md_user AS u ON a.org_id = u.org_id AND a.user_id = u.user_id ';

        $sql = "SELECT {$columns} FROM {$table} WHERE a.org_id = " . $this->_db->quote($orgId) . ' LIMIT 1';

        return $this->_db->fetchRow($sql);
    }

    /**
     *
     * @param $orgId
     * @param $userId
     * @param $type
     * @param $level
     */
    public function addAdmin($orgId, $userId, $type, $level = 0)
    {
        $table = 'md_site_admin';

        $bind = array(
            'org_id' => $orgId,
            'user_id' => $userId,
            'admin_type' => $type,
            'admin_level' => (int) $level
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新管理员
     *
     * @param string $orgId
     * @param string $userId
     * @param array $params
     */
    public function updateAdmin($orgId, $userId, array $params)
    {
        if (empty($orgId) || empty($userId)) {
            return false;
        }

        $table = 'md_site_admin';
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' '
               . 'AND user_id = ' . $this->_db->quote($userId);
        $bind  = array();

        if (isset($params['admintype']) && is_int($params['admintype'])) {
            $bind['admin_type'] = $params['admintype'];
        }

        if (isset($params['adminlevel']) && is_int($params['adminlevel'])) {
            $bind['admin_level'] = $params['adminlevel'];
        }

        if (!$bind) {
            return false;
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除管理员
     *
     * @param $orgId
     * @param $userId
     */
    public function deleteAdmin($orgId, $userId)
    {
        $sql = 'DELETE FROM md_site_admin WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 扩容
     *
     * @param array $condition
     * @param array $filter
     */
    public function getQuota(array $condition, $filter = null)
    {
        $table   = 'md_user_quota_update';
        $columns = 'org_id AS orgid, unique_id AS uniqueid, method, status, create_time AS createtime';
        $where   = array();
        $bind    = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
        }

        if (isset($condition['method'])) {
            $where[] = 'method = :method';
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
     * 创建扩容记录
     *
     * @param array $params
     */
    public function createQuota(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || !isset($params['method']))
        {
            return false;
        }

        $table = 'md_user_quota_update';
        $bind  = array(
            'org_id'    => $params['orgid'],
            'unique_id' => $params['uniqueid'],
            'method'    => $params['method']
        );

        if (isset($params['nickname'])) {
            $bind['nickname'] = $params['nickname'];
        }

        if (isset($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
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
     * 格式化登陆页设置
     *
     * @param string $loginSkin
     * @return array
     */
    public static function formatLoginSkin($loginSkin)
    {
        if (empty($loginSkin)) {
            return array();
        }

        $ret = json_decode($loginSkin, true);

        if (is_array($ret)) {
            foreach ($ret as $key => $arr) {
                if ($key == 'selected') {
                    $val = explode(':', $arr['value']);

                    $ret[$key]['issystem']   = $val[0] == 'SYS' ? true : false;
                    $ret[$key][$arr['type']] = $val[1];
                }
            }
        }

        return $ret;
    }
}