<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cast.php 2714 2013-01-23 10:19:48Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Cast extends Oray_Dao_Abstract
{
    /**
     *
     * @var string
     */
    const LABEL_ALL = '^all';

    /**
     * 获取不可见用户
     *
     * @param $orgId
     * @param $ownerId
     */
    public function getHiddenUsers($orgId, $ownerId)
    {
        $orgId   = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);

        $sql = "SELECT u.org_id AS orgid, u.user_id AS userid, ui.true_name AS truename, u.dept_id AS deptid FROM md_cast_disable_user AS cu "
             . "LEFT JOIN md_user AS u ON u.org_id = cu.org_id AND u.user_id = cu.user_id "
             . "LEFT JOIN md_user_info AS ui ON ui.org_id = cu.org_id AND ui.user_id = cu.user_id "
             . "WHERE cu.org_id = {$orgId} AND cu.owner_id = {$ownerId}";

        /*$sql = "SELECT u.org_id AS orgid, u.user_id AS userid, u.dept_id AS deptid FROM md_user AS u "
             . "LEFT JOIN md_user_cast_user cu ON u.org_id = cu.org_id AND u.user_id = cu.user_id AND cu.owner_id = {$ownerId} "
             . "WHERE u.org_id = {$orgId} AND cu.user_id IS NULL";*/

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $record) {
            $ret[$record['userid']] = $record;
        }

        return $ret;
    }

    /**
     * 获取不可见部门列表
     *
     * @param $orgId
     * @param $ownerId
     */
    public function getHiddenDepartments($orgId, $ownerId)
    {
        $orgId   = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);

        $sql = "SELECT d.org_id AS orgid, d.dept_id AS deptid, d.parent_dept_id AS parentid FROM md_cast_disable_dept AS cd "
             . "LEFT JOIN md_department AS d ON d.org_id = cd.org_id AND d.dept_id = cd.dept_id "
             . "WHERE cd.org_id = {$orgId} AND cd.owner_id = {$ownerId}";

        /*$sql = "SELECT d.org_id AS orgid, d.dept_id AS deptid, d.parent_dept_id AS parentid FROM md_department AS d "
             . "LEFT JOIN md_user_cast_dept cd ON d.org_id = cd.org_id AND d.dept_id = cd.dept_id AND cd.owner_id = {$ownerId} "
             . "WHERE d.org_id = {$orgId} AND cd.dept_id IS NULL";*/

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $record) {
            $ret[$record['deptid']] = $record;
        }

        return $ret;
    }

    /**
     * 清空架构视图
     *
     * @param $orgId
     * @param $userId
     */
    public function clear($orgId, $userId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);

        try {
            $this->_db->query("DELETE FROM md_cast_disable_user WHERE org_id = {$orgId} AND (user_id = {$userId} OR owner_id = {$userId})");;
            $this->_db->query("DELETE FROM md_cast_disable_dept WHERE org_id = {$orgId} AND owner_id = {$userId}");
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 设置部门为可见（从隐藏列表中移除）
     *
     * @param string $orgId
     * @param string $ownerId
     * @param string $deptId
     */
    public function showDepartment($orgId, $ownerId, $deptId)
    {
        $orgId  = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);
        $deptId = $this->_db->quote($deptId);

        $sql = "DELETE FROM md_cast_disable_dept WHERE org_id = {$orgId} AND owner_id = {$ownerId} AND dept_id = {$deptId}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 隐藏部门可见
     *
     * @param $orgId
     * @param $ownerId
     * @param $deptId
     */
    public function hideDepartment($orgId, $ownerId, $deptId)
    {
        $orgId  = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);
        $deptId = $this->_db->quote($deptId);

        $sql = "call sp_md_cast_hide_dept({$orgId}, {$ownerId}, {$deptId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param $orgId
     * @param $userId
     * @param $deptId
     */
    public function updateDepartment($orgId, $userId, $deptId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $deptId = $this->_db->quote($deptId);

        $sql = "call sp_md_cast_update_dept({$orgId}, {$userId}, {$deptId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 隐藏用户可见
     *
     * @param $orgId
     * @param $ownerId
     * @param $userId
     */
    public function showUser($orgId, $ownerId, $userId)
    {
        $orgId   = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);
        $userId  = $this->_db->quote($userId);

        $sql = "call sp_md_cast_show_user({$orgId}, {$ownerId}, {$userId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 隐藏用户可见
     *
     * @param $orgId
     * @param $ownerId
     * @param $userId
     */
    public function hideUser($orgId, $ownerId, $userId)
    {
        $orgId   = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);
        $userId  = $this->_db->quote($userId);

        $sql = "call sp_md_cast_hide_user({$orgId}, {$ownerId}, {$userId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加可见用户
     * @param string $orgId
     * @param string $ownerId
     * @param array  $userIds
     * @return boolean
     */
    public function addUser($orgId, $ownerId, $userId)
    {
        if (!$orgId || !$ownerId || !$userId) {
            return false;
        }

        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $ownerId = $this->_db->quote($ownerId);

        $sql = "call sp_md_add_cast_user ({$orgId}, {$ownerId}, {$userId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 为选中部门用户添加关联用户
     *
     * @param $orgId
     * @param $userId
     */
    public function addAssociateUser($orgId, $userId, $deptId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $deptId = $this->_db->quote($deptId);

        $sql = "INSERT IGNORE INTO md_user_cast_user (org_id, owner_id, user_id) "
             . "SELECT org_id, owner_id, {$userId} FROM md_user_cast_dept "
             . "WHERE org_id = {$orgId} AND (dept_id = " . self::LABEL_ALL . " OR dept_id = {$deptId})";

        try {
            $this->_db->quote($sql);
        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 去除选中部门用户的可见权限
     *
     * @param $orgId
     * @param $userId
     * @param $deptId
     */
    public function removeAssociateUser($orgId, $userId, $deptId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $deptId = $this->_db->quote($deptId);

        $sql = "DELETE FROM md_user_cast_user WHERE org_id = {$orgId} AND user_id = {$userId} AND "
             . "owner_id IN (SELECT owner_id FROM md_user_cast_dept WHERE org_id = {$orgId} AND dept_id = {$deptId})";

        try {
            $this->_db->quote($sql);
        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 清空架构视图
     *
     * @param $orgId
     * @param $userId
     */
    public function clearCast($orgId, $userId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);

        try {
            $this->_db->query("DELETE FROM md_user_cast_user WHERE org_id = {$orgId} AND (user_id = {$userId} OR owner_id = {$userId})");;
            $this->_db->query("DELETE FROM md_user_cast_dept WHERE org_id = {$orgId} AND owner_id = {$userId}");
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 添加部门关联
     *
     * @param string $orgId
     * @param string $castId
     * @param string $deptId
     * @return boolean
     */
    public function addDepartment($orgId, $ownerId, $deptId)
    {
        if (!$orgId || !$ownerId || !$deptId) {
            return false;
        }

        $orgId   = $this->_db->quote($orgId);
        $ownerId = $this->_db->quote($ownerId);
        $deptId  = $this->_db->quote($deptId);

        $sql = "call sp_md_add_cast_dept({$orgId}, {$ownerId}, {$deptId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 移除构架用户
     *
     * @param string $orgId
     * @param string $castId
     * @param string $userId
     * @return boolean
     */
    public function removeUser($orgId, $ownerId, $userId = null)
    {
        if (!$orgId || !$ownerId || !$userId) {
            return false;
        }

        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $ownerId = $this->_db->quote($ownerId);

        $sql = "call sp_md_delete_cast_user ({$orgId}, {$ownerId}, {$userId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 移除构架部门
     *
     * @param string $orgId
     * @param string $castId
     * @param string $deptId
     * @return boolean
     */
    public function removeDepartment($orgId, $ownerId, $deptId = null)
    {
        if (!$orgId || !$ownerId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'owner_id = ' . $this->_db->quote($ownerId);

        if ($deptId) {
            $where[] = 'dept_id = ' . $this->_db->quote($deptId);
        }

        $where = implode(' AND ', $where);

        $sql = "DELETE FROM md_user_cast_dept WHERE {$where}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 配置中某用户是否可见
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    public function existsUser($orgId, $ownerId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM md_cast_disable_user WHERE '
             . 'org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'owner_id = ' . $this->_db->quote($ownerId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count <= 0;
    }

    /**
     * 获取构架可见的用户列表
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getCastUsers(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        if (empty($condition['orgid']) || empty($condition['userid'])) {
            return new Oray_Dao_Recordset();
        }

        $orgId  = $this->_db->quote($condition['orgid']);
        $userId = $this->_db->quote($condition['userid']);

        $table   = 'md_user AS U '
                 //. 'LEFT JOIN md_user_cast_user AS CU ON CU.org_id = U.org_id AND CU.user_id = U.user_id '
                 . "LEFT JOIN md_cast_disable_user AS CU ON CU.org_id = U.org_id AND CU.user_id = U.user_id AND CU.owner_id = {$userId} "
                 . 'LEFT JOIN md_user_info AS UI ON U.org_id = UI.org_id AND U.user_id = UI.user_id '
                 . 'LEFT JOIN md_department DE ON U.org_id = DE.org_id AND U.dept_id = DE.dept_id';
        $columns = 'U.org_id AS orgid, UI.true_name AS truename, U.user_id AS userid, UI.mobile, UI.tel, U.last_update_time AS lastupdatetime, UI.gender, '
                 . 'U.dept_id AS deptid, DE.dept_name AS deptname, UI.pinyin, UI.position, U.unique_id AS uniqueid, UI.update_time AS updatetime, ISNULL(UI.avatars_type) AS isavatars';
        $where   = array();
        $order   = array();
        $limit   = '';

        //$where[] = 'CU.org_id =' . $orgId;
        //$where[] = 'CU.owner_id =' . $userId;
        $where[] = 'U.org_id =' . $orgId;
        $where[] = 'CU.user_id IS NULL';
        $where[] = 'U.status <> 0 AND U.is_show = 1';

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%'.$condition['keyword'].'%');

            $like[] = "UI.true_name LIKE {$keyword}";


            if (Oray_Function::isByte($keyword)) {
                $like[] = "UI.pinyin LIKE {$keyword}";
                $like[] = "U.user_id LIKE {$keyword}";
            }

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        if (!empty($condition['pinyin'])) {
            $keyword = $this->_db->quote($condition['pinyin'].'%');
            $where[] = "(U.true_name LIKE {$keyword} OR UI.pinyin LIKE {$keyword})";
        }

        if (!empty($condition['deptid'])) {
            if (is_array($condition['deptid'])) {
                foreach ($condition['deptid'] as $deptId) {
                    $dept[] = 'U.dept_id = ' . $this->_db->quote($deptId);
                }

                $where[] = '(' . implode(' OR ', $dept) . ')';
            } else {
                if ($condition['deptid'] == '^root') {
                    $where[] = '(U.dept_id IS NULL OR U.dept_id = \'\' )';
                } else {
                    $where[] = 'U.dept_id = ' . $this->_db->quote($condition['deptid']);
                }
            }
        }

        if (!empty($condition['userids']) && is_array($condition['userids'])) {
            foreach ($condition['userids'] as $userId) {
                $user[] = 'U.user_id = ' . $this->_db->quote($userId);
            }

            $where[] = '(' . implode(' OR ', $user) . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'U.order_num';
                    break;
                case 'deptid':
                    $key = 'U.dept_id';
                    break;
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Users');
    }

    /**
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getCastUserPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        if (empty($condition['orgid']) || empty($condition['userid'])) {
            return new Oray_Dao_Recordset();
        }

        $orgId  = $this->_db->quote($condition['orgid']);
        $userId = $this->_db->quote($condition['userid']);

        $table   = 'md_user AS U '
                 //. 'LEFT JOIN md_user_cast_user AS CU ON CU.org_id = U.org_id AND CU.user_id = U.user_id '
                 . "LEFT JOIN md_cast_disable_user AS CU ON CU.org_id = U.org_id AND CU.user_id = U.user_id AND CU.owner_id = {$userId} "
                 . 'LEFT JOIN md_user_info AS UI ON U.org_id = UI.org_id AND U.user_id = UI.user_id '
                 . 'LEFT JOIN md_department DE ON U.org_id = DE.org_id AND U.dept_id = DE.dept_id';
        $columns = 'U.org_id AS orgid, UI.true_name AS truename, U.user_id AS userid, mobile, '
                 . 'U.dept_id AS deptid, DE.dept_name AS deptname, UI.pinyin, U.unique_id AS uniqueid, '
                 . 'UI.position, UI.tel';
        $where   = array();
        $order   = array();
        $recordClass = 'Dao_Md_User_Record_Users';
        $primary = 'U.org_id';

        //$where[] = 'CU.org_id =' . $orgId;
        //$where[] = 'CU.owner_id =' . $userId;
        $where[] = 'U.org_id =' . $orgId;
        $where[] = 'CU.user_id IS NULL';
        $where[] = 'U.status <> 0 AND U.is_show = 1';

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%'.$condition['keyword'].'%');

            $like[] = "UI.true_name LIKE {$keyword}";

            if (Oray_Function::isByte($keyword)) {
                $like[] = "UI.pinyin LIKE {$keyword}";
                $like[] = "U.user_id LIKE {$keyword}";
            }

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        if (!empty($condition['pinyin'])) {
            $keyword = $this->_db->quote($condition['pinyin'].'%');
            $where[] = "(U.true_name LIKE {$keyword} OR UI.pinyin LIKE {$keyword})";
        }

        if (!empty($condition['deptid']) && is_array($condition['deptid'])) {
            foreach ($condition['deptid'] as $deptId) {
                $dept[] = 'U.dept_id = ' . $this->_db->quote($deptId);
            }

            $where[] = '(' . implode(' OR ', $dept) . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'U.order_num';
                    break;
                case 'userid':
                    $key = 'U.user_id';
                    break;
                case 'deptid':
                    $key = 'U.dept_id';
                    break;
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        /**
         * @see Oray_Db_Paginator
         */
        require_once 'Oray/Db/Paginator.php';

        $paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER   => $this->_db,
            Oray_Db_Paginator::TABLE     => $table,
            Oray_Db_Paginator::COLUMNS   => $columns,
            Oray_Db_Paginator::ORDER     => $order,
            Oray_Db_Paginator::PRIMARY   => $primary,
            Oray_Db_Paginator::WHERE     => $where,
            Oray_Db_Paginator::PAGE_SIZE => $pageSize,

            Oray_Db_Paginator::RECORD_CLASS => $recordClass
        ));

        return $paginator->query($page);
    }

    /**
     * 获取用户可见的部门
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    public function getCastDepartments($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return new Oray_Dao_Recordset();
        }

        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);

        $table   = 'md_department AS D '
                 //. 'LEFT JOIN md_user_cast_dept AS UD ON UD.org_id = D.org_id AND UD.dept_id = D.dept_id ';
                 . "LEFT JOIN md_cast_disable_dept AS UD ON UD.org_id = D.org_id AND UD.dept_id = D.dept_id AND UD.owner_id = {$userId}";
        $columns = 'D.org_id AS orgid, D.dept_id AS deptid, dept_name AS deptname, parent_dept_id AS parentid, '
                 . 'moderators, order_num as ordernum';

        $where = array();

        //$where[] = 'UD.org_id = ' . $this->_db->quote($orgId);
        //$where[] = 'UD.owner_id = ' . $this->_db->quote($userId);
        $where[] = "D.org_id = {$orgId}";
        $where[] = "UD.dept_id IS NULL";

        $where = implode(' AND ', $where);

        // ORDER
        $order = 'ORDER BY D.org_id, D.parent_dept_id, D.order_num DESC';

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order}";

        $_records = $this->_db->fetchAll($sql);

        if (!$_records) {
            return new Oray_Dao_Recordset();
        }

        if (!class_exists('Dao_Md_Department_Department')) {
            require_once 'Dao/Md/Department/Department.php';
        }

        $records = array();

        $records = Dao_Md_Department_Department::sortRecords($_records);
        $records = Dao_Md_Department_Department::formatRecords($records);

        return new Oray_Dao_Recordset($records, 'Dao_Md_Department_Record_Department');
    }

    /**
     * 获取用户ID
     *
     * @param string $orgId
     * @param string $userId
     */
    public function getCastUserIds($orgId, $userId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);

        $sql = "SELECT user_id FROM md_user_cast_user WHERE org_id = {$orgId} AND owner_id = {$userId}";

        try {
            $records = $this->_db->fetchAll($sql);

            $array = array();
            foreach ($records as $record) {
                $array[] = $record['user_id'];
            }

            return $array;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return array();
        }
    }

    /**
     * 获取可见部门ID
     *
     * @param string $orgId
     * @param string $userId
     */
    public function getCastDeptIds($orgId, $userId)
    {
        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);

        $sql = "SELECT dept_id FROM md_user_cast_dept WHERE org_id = {$orgId} AND owner_id = {$userId}";

        try {
            $records = $this->_db->fetchAll($sql);

            $array = array();
            foreach ($records as $record) {
                $array[] = $record['dept_id'];
            }

            return $array;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return array();
        }
    }

    /**
     * 更新部门可见性
     *
     * @param $orgId
     * @param $userId
     * @param $sdeptId
     * @param $deptId
     */
    public function updateCastDept($orgId, $userId, $deptId)
    {
        if (!$orgId || !$userId || !$deptId) {
            return false;
        }

        $orgId  = $this->_db->quote($orgId);
        $userId = $this->_db->quote($userId);
        $deptId = $this->_db->quote($deptId);

        $sql = "call sp_md_update_cast_dept({$orgId}, {$userId}, {$deptId})";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }
}