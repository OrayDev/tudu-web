<?php
/**
 * App
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: App.php 1351 2011-12-05 10:22:58Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_App_App extends Oray_Dao_Abstract
{
    const TYPE_INNER = 'inner';

    const TYPE_OUTER = 'outer';

    /**
     * 应用类型
     *
     * @var array
     */
    static $supportTypes = array(
        self::TYPE_INNER,
        self::TYPE_OUTER
    );

    const STATUS_NORMAL = 0;

    const STATUS_STOPPED = 1;

    /**
     * 应用状态
     *
     * @var array
     */
    static $supportStatus = array(
        self::STATUS_NORMAL,
        self::STATUS_STOPPED
    );

    const ATTACH_TYPE_VIDEO = 'video';

    const ATTACH_TYPE_PHOTO = 'photo';

    const ATTACH_TYPE_AUDIO = 'audio';

    /**
     * 应用介绍使用的附近类型
     *
     * @var array
     */
    static $supportAttachTypes = array(
        self::ATTACH_TYPE_VIDEO,
        self::ATTACH_TYPE_PHOTO,
        self::ATTACH_TYPE_AUDIO
    );

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_App_Record_App
     */
    public function getApp(array $condition, $filter = null)
    {
        if (empty($condition['orgid']) || empty($condition['appid'])) {
    		return null;
    	}

        $table = 'app_app a '
               . 'LEFT JOIN app_info ai ON a.app_id = ai.app_id '
               . 'LEFT JOIN app_user oa ON a.app_id = oa.app_id AND oa.org_id = ' . $this->_db->quote($condition['orgid']) . ' ';
        $columns = 'a.app_id AS appid, a.app_name AS appname, a.type, a.version, a.url, ai.author, ai.description, ai.logo, ai.languages, '
                 . 'ai.content, ai.score, ai.comment_num AS commentnum, ai.create_time AS createtime, ai.last_update_time AS lastupdatetime, '
                 . 'oa.status, oa.org_id AS orgid, oa.users, oa.expire_date AS expiredate';
        $where = array();

        $recordClass = 'Dao_Md_App_Record_App';

        $where[] = 'a.app_id = ' . $this->_db->quote($condition['appid']);

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}  LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record($recordClass, $record);
    }

    /**
     *
     * @param array $condition
     * @param array $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getAppPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        if (empty($condition['orgid'])) {
    		return null;
    	}

        $table = 'app_app a '
               . 'LEFT JOIN app_info ai ON a.app_id = ai.app_id '
               . 'LEFT JOIN app_user oa ON a.app_id = oa.app_id AND oa.org_id = ' . $this->_db->quote($condition['orgid']) . ' ';
        $columns = 'a.app_id AS appid, a.app_name AS appname, a.url, ai.description, ai.logo, '
                 . 'ai.create_time AS createtime, ai.last_update_time AS lastupdatetime, '
                 . 'oa.status, oa.org_id AS orgid';
        $where = array();
        $order = array();
        $limit = '';

        $recordClass = 'Dao_Md_App_Record_App';

        if (!empty($condition['installed'])) {
            $where[] = 'oa.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // 排序
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
            	case 'lastupdatetime':
            		$key = 'last_update_time';
            		break;
            	case 'createtime':
            	    $key = 'create_time';
            		break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        $order = implode(', ', $order);
        if ($order) {
        	$order = 'ORDER BY ' . $order;
        }

        if (null !== $page) {
            // 使用默认的分页大小
            if (null === $pageSize) {
                $pageSize = self::$_defaultPageSize;
            }

            $offset = ($page - 1) * $pageSize;

            $limit = "LIMIT {$offset}, {$pageSize}";
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

	/**
     * 获取应用介绍的附件
     *
     * @param array $condition
     * @return array
     */
    public function getAppAttachs(array $condition)
    {
        $table = 'app_info_attach';
        $columns = 'app_id AS appid, type, url, order_num AS ordernum';
        $where = array();

        if (isset($condition['appid'])) {
            $where[] = 'app_id = ' . $this->_db->quote($condition['appid']);
        }

        if (isset($condition['type']) && in_array($condition['type'], self::$supportAttachTypes)) {
            $where[] = 'type = ' . $this->_db->quote($condition['type']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} ORDER BY order_num ASC";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $records;
    }

    /**
     * 权限（可调用数据）
     *
     * @param string $appId
     * @return array
     */
    public function getAppPermissions($appId)
    {
        $table = 'app_permission';
        $columns = 'app_id AS appid, permission, order_num AS ordernum';

        $sql = "SELECT {$columns} FROM {$table} WHERE app_id = " . $this->_db->quote($appId) . " ORDER BY order_num ASC";

        try {
            $records = $this->_db->fetchAll($sql);

            if (!$records) {
                return null;
            }

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $records;
    }

    /**
     * 安装应用
     *
     * @param array $params
     * @return boolean
     */
    public function installApp(array $params)
    {
        if (empty($params['orgid']) || empty($params['appid'])) {
            return false;
        }

        $table = 'app_user';
        $bind  = array();

        if (!empty($params['orgid'])) {
            $bind['org_id'] = $params['orgid'];
        }

        if (!empty($params['appid'])) {
            $bind['app_id'] = $params['appid'];
        }

        if (!empty($params['status']) && in_array($params['status'], self::$supportStatus)) {
            $bind['status'] = $params['status'];
        }

        if (!empty($params['users'])) {
            $bind['users'] = $params['users'];
        }

        if (!empty($params['expiredate'])) {
            $bind['expire_date'] = $params['expiredate'];
        }

        if (!$bind) {
            return false;
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['appid'];
    }

    /**
     * 更新应用设置
     *
     * @param string $orgId
     * @param string $appId
     * @param array $params
     * @return boolean
     */
    public function updateApp($orgId, $appId, array $params)
    {
        if (empty($orgId) || empty($appId)) {
            return false;
        }

        $table = 'app_user';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND app_id = ' . $this->_db->quote($appId);

        if (isset($params['status']) && in_array($params['status'], self::$supportStatus)) {
            $bind['status'] = $params['status'];
        }

        if (!empty($params['users'])) {
            $bind['users'] = $params['users'];
        }

        if (!empty($params['expiredate'])) {
            $bind['expire_date'] = $params['expiredate'];
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
     * 删除应用
     *
     * @param string $orgId
     * @param string $appId
     * @return boolean
     */
    public function deleteApp($orgId, $appId)
    {
        if (empty($orgId) || empty($appId)) {
            return false;
        }

        $sql = 'DELETE FROM app_user WHERE org_id = ' . $this->_db->quote($orgId) . ' AND app_id = ' . $this->_db->quote($appId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true ;
    }
}