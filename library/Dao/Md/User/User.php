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
 * @version    $Id: User.php 2742 2013-02-18 01:13:32Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_User extends Oray_Dao_Abstract
{
    /**
     * 获取用户基本信息
     *
     * SELECT
     * U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, U.dept_id AS deptid, U.cast_id AS castid,
     * U.create_time AS createtime, U.expire_date AS expiredate,
     * DM.domain_name AS domainname, U.status, U.is_show AS isshow, U.order_num AS ordernum,
     * GROUP_CONCAT(UG.group_id) groups, GROUP_CONCAT(UP.product_id) products
     * FROM
     * md_user AS U
     * LEFT JOIN md_user_group UG ON U.org_id = UG.org_id AND U.user_id = UG.user_id
     * LEFT JOIN md_user_product UP ON U.org_id = UP.org_id AND U.user_id = UP.user_id
     * WHERE U.org_id = 'oray'
     * GROUP BY U.org_id, U.user_id;
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_User_Record_User
     */
    public function getUser(array $condition, $filter = null)
    {
        $table   = 'md_user AS U '
                 . 'LEFT JOIN md_user_group UG ON U.org_id = UG.org_id AND U.user_id = UG.user_id '
                 . 'LEFT JOIN md_user_role UR ON U.org_id = UR.org_id AND U.user_id = UR.user_id '
                 . 'LEFT JOIN md_site_admin AD ON U.org_id = AD.org_id AND U.user_id = AD.user_id';
        $columns = 'U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, '
                 . 'U.dept_id AS deptid, U.cast_id AS castid, U.max_nd_quota AS maxndquota, '
                 . 'U.create_time AS createtime, U.expire_date AS expiredate, U.init_password AS initpassword, '
                 . 'U.status, U.is_show AS isshow, U.order_num AS ordernum, '
                 . 'GROUP_CONCAT(DISTINCT(UG.group_id)) groups, GROUP_CONCAT(DISTINCT(UR.role_id)) roles, '
                 . 'AD.admin_type AS admintype, AD.admin_level AS adminlevel, U.unlock_time AS unlocktime';
        $where = array();
        $group = 'GROUP BY U.org_id, U.user_id';

        if (!empty($condition['orgid']) && !empty($condition['userid'])) {
            $where[] = 'U.user_id = ' . $this->_db->quote($condition['userid']);
            $where[] = 'U.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        /*if (!empty($condition['domainname']) && !empty($condition['userid'])) {
            $where[] = 'U.user_id = ' . $this->_db->quote($condition['userid']);
            $where[] = 'D.domain_name = ' . $this->_db->quote($condition['domainname']);
        }*/

        if (!empty($condition['uniqueid'])) {
            $where[] = 'U.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$group} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_User_Record_User', $record);
    }


    /**
     * 获取用户个人信息
     *
     * SELECT
     * U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, U.dept_id AS deptid, U.cast_id AS castid,
     * U.create_time AS createtime, U.expire_date AS expiredate,
     * DM.domain_name AS domainname, U.status, U.is_show AS isshow, U.order_num AS ordernum,
     * UI.true_name AS truename, UI.position, UI.nick, UI.gender, UI.id_number AS idnumber, UI.birthday, UI.mobile, UI.tel
     * FROM
     * md_user AS U
     * LEFT JOIN md_user_info AS UI ON U.org_id =UI.org_id AND U.user_id = UI.user_id
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Tudu_User_Record_UserInfo
     */
    public function getUserInfo(array $condition, $filter = null)
    {
        $table   = 'md_user_info';
        $columns = 'true_name AS truename, position, nick, gender, id_number AS idnumber, birthday, mobile, update_time as updatetime, '
                 . 'email AS mailbox, tel, office_location AS officelocation, sign';
        $where = array();

        if (!empty($condition['orgid']) && !empty($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_User_Record_UserInfo', $record);
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     */
    public function getUserCard(array $condition, $filter = null)
    {
        $table   = 'md_user AS U '
                 . 'LEFT JOIN md_user_info AS UI ON U.org_id = UI.org_id AND U.user_id = UI.user_id '
                 . 'LEFT JOIN md_department AS D ON U.org_id = D.org_id AND U.dept_id = D.dept_id';
        $columns = 'U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, '
                 . 'UI.true_name AS truename, D.dept_name AS deptname, UI.position, UI.mobile, UI.tel';
        $where = array();
        $group = 'GROUP BY U.org_id, U.user_id';

        if (!empty($condition['uniqueid'])) {
            $where[] = 'U.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'U.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['userid'])) {
            $where[] = 'U.user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$group} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return $record;
    }

    /**
     * 获取多条用户记录
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getUsers(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_user AS U LEFT JOIN md_user_info AS UI ON U.org_id = UI.org_id AND U.user_id = UI.user_id ';
        $columns = 'U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, '
                 . 'U.unique_id AS uniqueid, '
                 . 'U.dept_id AS deptid, U.cast_id AS castid, U.status, '
                 . 'UI.true_name AS truename, U.create_time AS createtime, U.unlock_time AS unlocktime';
        $where   = array();
        $order   = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'U.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['userid'])) {
            $where[] = 'U.user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'U.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['deptid'])) {
            if ($condition['deptid'] == '^root') {
                $where[] = '(U.dept_id IS NULL OR U.dept_id = \'\' )';
            } else {
                $where[] = 'U.dept_id = ' . $this->_db->quote($condition['deptid']);
            }
        }

        if (!empty($condition['castid'])) {
            $where[] = 'U.cast_id = ' . $this->_db->quote($condition['castid']);
        }

        if (isset($condition['groupid'])) {
            $table  .= 'LEFT JOIN md_user_group AS G ON U.org_id = G.org_id AND U.user_id = G.user_id ';
            $where[] = 'G.group_id = ' . $this->_db->quote($condition['groupid']);
        }

        if (!empty($condition['domain'])) {
            $where[] = 'DM.domain_name = ' . $this->_db->quote($condition['domain']);
        }

        if (!empty($condition['domainid'])) {
            $where[] = 'U.domain_id = ' . $this->_db->quote($condition['domainid']);
        }

        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 'U.status = ' . $condition['status'];
        }

        if (!empty($filter) && array_key_exists('isnormal', $filter)) {
            if ($filter['isnormal']) {
                $where[] = 'U.status <> 0';
            }
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = ' WHERE ' . implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'userid':
                    $key = 'U.user_id';
                    break;
                case 'createtime':
                    $key = 'U.create_time';
                    break;
                case 'ordernum':
                    $key = 'U.order_num';
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

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Md_User_Record_Users');
    }

    /**
     * 获取用户分页数据
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Dao_User_Record_Users
     */
    public function getUserPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'md_user AS U LEFT JOIN md_user_info AS UI ON U.org_id = UI.org_id AND U.user_id = UI.user_id '
                 . 'LEFT JOIN md_department AS D ON U.org_id = D.org_id AND U.dept_id = D.dept_id '
                 . 'LEFT JOIN md_user_role AS R ON R.org_id = U.org_id AND R.user_id = U.user_id '
                 . 'LEFT JOIN md_user_group AS G ON G.org_id = U.org_id AND G.user_id = U.user_id';
        $columns = 'U.org_id AS orgid, U.user_id AS userid, U.unique_id AS uniqueid, '
                 . 'U.dept_id AS deptid, U.status, UI.gender, GROUP_CONCAT(DISTINCT(G.group_id)) AS groups, '
                 . 'D.dept_name AS deptname, UI.true_name AS truename, U.create_time AS createtime, U.unlock_time AS unlocktime, '
                 . 'GROUP_CONCAT(DISTINCT(R.role_id)) AS roles';
        $where   = array();
        $order   = array();
        $primary = 'U.user_id';

        $recordClass = 'Dao_Md_User_Record_UserPage';

        if (!empty($condition['orgid'])) {
            $where[] = 'U.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['userid'])) {
            $where[] = 'U.user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['domain'])) {
            $where[] = 'DM.domain_name = ' . $this->_db->quote($condition['domain']);
        }

        if (!empty($condition['domainid'])) {
            $where[] = 'U.domain_id = ' . $this->_db->quote($condition['domainid']);
        }

        if (!empty($condition['deptid'])) {
            if (is_array($condition['deptid'])) {
                $condition['deptid'] = array_map(array($this->_db, 'quote'), $condition['deptid']);
                $where[] = 'U.dept_id IN (' . implode(',', $condition['deptid']) . ')';
            } else {
                $where[] = 'U.dept_id = ' . $this->_db->quote($condition['deptid']);
            }
        }

        if (!empty($condition['groupid'])) {
            $table  .= 'LEFT JOIN md_user_group AS G ON U.org_id = G.org_id AND U.user_id = G.user_id ';
            $where[] = 'G.group_id = ' . $this->_db->quote($condition['groupid']);
        }

        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 'U.status = ' . $condition['status'];
        }

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%'.$condition['keyword'].'%');
            $str = "UI.true_name LIKE {$keyword} OR UI.nick LIKE {$keyword}";

            if (Oray_Function::isByte($condition['keyword'])) {
                $str .= " OR U.user_id LIKE {$keyword} OR UI.pinyin LIKE {$keyword}";
            }

            $where[] = '(' . $str . ')';
        }

        if (isset($condition['createtime'])) {
            if (is_array($condition['createtime'])) {
                $w = array();

                if (isset($condition['createtime']['start'])) {
                    $w[] = 'create_time >= ' . $condition['starttime'];
                }

                if (isset($condition['createtime']['end'])) {
                    $w[] = 'create_time <= ' . $condition['endtime'];
                }

                if ($w) {
                    $where[] = '(' . implode(' AND ', $w) . ')';
                }

            } elseif (is_int($condition['createtime'])) {
                $where[] = 'create_time >= ' . $condition['starttime'];
            }
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        //$where = implode(' AND ', $where);
        $where = ' WHERE ' . implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'userid':
                    $key = 'U.user_id';
                    break;
                case 'createtime':
                    $key = 'U.create_time';
                    break;
                case 'ordernum':
                    $key = 'U.order_num';
                    break;
                case 'deptid':
                    $key = 'U.dept_id';
                    break;
                case 'status':
                    $key = 'U.status';
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

        $limit = '';
        if (null !== $page) {
            // 使用默认的分页大小
            if (null === $pageSize) {
                $pageSize = self::$_defaultPageSize;
            }

            $offset = ($page - 1) * $pageSize;

            $limit = "LIMIT {$offset}, {$pageSize}";
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} GROUP BY U.org_id, U.user_id {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);

        /**
         * @see Oray_Db_Paginator
         */
        //require_once 'Oray/Db/Paginator.php';

        /*$paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER   => $this->_db,
            Oray_Db_Paginator::TABLE     => $table,
            Oray_Db_Paginator::COLUMNS   => $columns,
            Oray_Db_Paginator::ORDER     => $order,
            Oray_Db_Paginator::PRIMARY   => $primary,
            Oray_Db_Paginator::WHERE     => $where,
            Oray_Db_Paginator::PAGE_SIZE => $pageSize,

            Oray_Db_Paginator::RECORD_CLASS => $recordClass
        ));*/

        //return $paginator->query($page);
    }

    /**
     * 获取用户数量
     *
     * @param array $condition
     */
    public function getUserCount(array $condition, $groupBy = null)
    {
        $table  = 'md_user';
        $column = 'COUNT(0) AS count';

        $where = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        $where = implode(' AND ', $where);

        if ($where) {
            $where = ' WHERE ' . $where;
        }

        if (null !== $groupBy) {
            $column .= ', ' . $groupBy;
            $sql = "SELECT {$column} FROM {$table} {$where} GROUP BY {$groupBy}";

            $records = $this->_db->fetchAll($sql);
            $ret = array();
            foreach ($records as $item) {
                $ret[$item[$groupBy]] = (int) $item['count'];
            }

            return $ret;
        }

        $sql = "SELECT {$column} FROM {$table} {$where}";

        return (int) $this->_db->fetchOne($sql);
    }

    /**
     * 获取用户基本信息
     *
     * @param string $address
     * @param array $filter
     * @return Dao_Md_User_Record_User
     */
    public function getUserByAddress($address, $filter = null)
    {
        list($userId, $orgId) = explode('@', $address);
        return $this->getUser(array('userid' => $userId, 'orgid' => $orgId), $filter);
    }

    /**
     * 获取用户头像
     *
     * @param string $orgId
     * @param string $userId
     * @return Dao_Md_User_Record_Avatars
     */
    public function getUserAvatars($orgId, $userId)
    {
        $table   = 'md_user_info';
        $columns = 'avatars_type as avatarstype, avatars';

        $where   = array();

        if ($orgId && $userId) {
            $where[] = 'org_id  = ' . $this->_db->quote($orgId);
            $where[] = 'user_id = ' . $this->_db->quote($userId);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_User_Record_Avatars', $record);
    }

    /**
     * 获取用户头
     *
     * @param mixed $condition
     * @return array
     */
    public function getAvatars($condition)
    {
        $columns = 'ui.avatars, ui.avatars_type AS type, ui.gender';
        $table   = 'md_user_info ui '
                 . 'INNER JOIN md_user u ON ui.org_id = u.org_id AND ui.user_id = u.user_id';
        $where   = array();

        if (is_string($condition)) {
            $condition = array('uniqueid' => $condition);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'u.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['userid'])) {
            $where[] = 'u.user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'u.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!$where) {
            return null;
        }
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";
        $avatars = $this->_db->fetchRow($sql);
        if ($avatars && !empty($avatars['avatars'])) {
            $avatars['avatars'] = base64_decode($avatars['avatars']);
        }
        return $avatars;
    }

    /**
     * 是否已存在用户
     *
     * @param string $orgId
     * @param string $userId
     */
    public function existsUser($orgId, $userId)
    {
        $sql = 'SELECT COUNT(0) FROM md_user WHERE org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count > 0;
    }

    /**
     * 创建用户数据
     *
     * @param array $params
     * @return boolean
     */
    public function createUser(array $params)
    {
        if (empty($params['orgid']) || empty($params['userid'])
            || empty($params['uniqueid']))
        {
            return false;
        }

        $table = 'md_user';

        $bind = array();

        $bind['org_id']    = strtolower($params['orgid']);
        $bind['user_id']   = strtolower($params['userid']);
        $bind['unique_id'] = $params['uniqueid'];

        if (!empty($params['deptid'])) {
            $bind['dept_id'] = $params['deptid'];
        }

        if (!empty($params['castid'])) {
            $bind['cast_id'] = $params['castid'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'] ? 1 : 0;
        }

        if (isset($params['expiredate']) && is_int($params['expiredate'])) {
            $bind['expire_date'] = date('Y-m-d', $params['expiredate']);
        }

        $bind['create_time'] = date('Y-m-d H:i:s');

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        if (!empty($params['maxndquota'])) {
            $bind['max_nd_quota'] = (int) $params['maxndquota'];
        }

        if (!empty($params['initpassword'])) {
            $bind['init_password'] = $params['initpassword'] ? 1 : 0;
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function createUserInfo(array $params)
    {
        if (empty($params['orgid']) || empty($params['userid']) || empty($params['truename']))
        {
            return false;
        }

        $table     = 'md_user_info';
        $dataTable = 'md_user_data';
        $bind      = array();

        $bind['org_id']    = $params['orgid'];
        $bind['user_id']   = $params['userid'];
        $bind['true_name'] = $params['truename'];

        if (!empty($params['position'])) {
            $bind['position'] = $params['position'];
        }

        if (!empty($params['pinyin'])) {
            $bind['pinyin'] = $params['pinyin'];
        }

        if (!empty($params['password'])) {
            $bind['password'] = (isset($params['ismd5'])) ? $params['password'] : md5($params['password']);
        }

        if (!empty($params['nick'])) {
            $bind['nick'] = $params['nick'];
        }

        if (!empty($params['avatars'])) {
            $bind['avatars'] = $params['avatars'];
        }

        if (isset($params['avatartype'])) {
            $bind['avatars_type'] = $params['avatartype'];
        }

        if (isset($params['gender'])) {
            $bind['gender'] = (int) $params['gender'];
        }

        if (!empty($params['idnumber'])) {
            $bind['id_number'] = $params['idnumber'];
        }

        if (isset($params['email'])) {
            $bind['email'] = $params['email'];
        }

        if (!empty($params['mobile'])) {
            $bind['mobile'] = $params['mobile'];
        }

        if (!empty($params['tel'])) {
            $bind['tel'] = $params['tel'];
        }

        if (isset($params['birthday']) && is_int($params['birthday'])) {
            $bind['birthday'] = date('Y-m-d', $params['birthday']);
        }

        if (!empty($params['officelocation'])) {
            $bind['office_location'] = $params['officelocation'];
        }

        if (!empty($params['jobtitle'])) {
            $bind['job_title'] = $params['jobtitle'];
        }

        if (!empty($params['sign'])) {
            $bind['sign'] = $params['sign'];
        }

        try {
            $this->_db->insert($table, $bind);
            $this->_db->insert($dataTable, array('org_id' => $params['orgid'], 'user_id' => $params['userid']));
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return $params['userid'];
    }

    /**
     * 更新用户记录
     *
     * @param string $orgId
     * @param string $userId
     * @param array  $params
     * @return boolean
     */
    public function updateUser($orgId, $userId, array $params)
    {
        if (!$orgId || !$userId)
        {
            return false;
        }

        $table = 'md_user';

        $bind = array();

        if (!empty($params['domainid'])) {
            $bind['domain_id'] = $params['domainid'];
        }

        if (isset($params['deptid'])) {
            $bind['dept_id'] = $params['deptid'] ? $params['deptid'] : null;
        }

        if (isset($params['castid'])) {
            $bind['cast_id'] = $params['castid'];
        }

        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }

        if (isset($params['isshow'])) {
            $bind['is_show'] = $params['isshow'] ? 1 : 0;
        }

        if (isset($params['expiredate'])) {
            $bind['expire_date'] = date('Y-m-d', $params['expiredate']);
        }

        if (isset($params['maxndquota'])) {
            $bind['max_nd_quota'] = (int) $params['maxndquota'];
        }

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        if (array_key_exists('initpassword', $params)) {
            $bind['init_password'] = $params['initpassword'];
        }

        if (array_key_exists('lastupdatetime', $params)) {
            $bind['last_update_time'] = $params['lastupdatetime'];
        }

        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 更新用户信息记录
     *
     * @param string $orgId
     * @param string $userId
     * @param array  $params
     * @return boolean
     */
    public function updateUserInfo($orgId, $userId, array $params)
    {
        if (!$orgId || !$userId)
        {
            return false;
        }

        $table = 'md_user_info';
        $bind  = array();

        if (!empty($params['password'])) {
            $bind['password'] = md5($params['password']);
        }

        if (!empty($params['truename'])) {
            $bind['true_name'] = $params['truename'];
        }

        if (isset($params['pinyin'])) {
            $bind['pinyin'] = $params['pinyin'];
        }

        if (isset($params['position'])) {
            $bind['position'] = $params['position'];
        }

        if (isset($params['nick'])) {
            $bind['nick'] = $params['nick'];
        }

        if (isset($params['avatars'])) {
            $bind['avatars'] = $params['avatars'];
        }

        if (isset($params['avatartype'])) {
            $bind['avatars_type'] = $params['avatartype'];
        }

        if (isset($params['gender']) && is_int($params['gender'])) {
            $bind['gender'] = $params['gender'];
        }

        if (isset($params['email'])) {
            $bind['email'] = $params['email'];
        }

        if (isset($params['idnumber'])) {
            $bind['id_number'] = $params['idnumber'];
        }

        if (isset($params['mobile'])) {
            $bind['mobile'] = $params['mobile'];
        }

        if (isset($params['tel'])) {
            $bind['tel'] = $params['tel'];
        }

        if (isset($params['birthday'])) {
            $bind['birthday'] = is_int($params['birthday']) ? date('Y-m-d', $params['birthday']) : null;
        }

        if (isset($params['officelocation'])) {
            $bind['office_location'] = $params['officelocation'];
        }

        //仅当更新头像的时候才更新update_time
        if (array_key_exists('avatars', $bind) || array_key_exists('position', $bind)
            || array_key_exists('tel', $bind) || array_key_exists('mobile', $bind) || array_key_exists('email', $bind))
        {
            $bind['update_time'] = time();
        }

        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 更新用户头像数据
     *
     * @param string $orgId
     * @param string $userId
     * @param binary $avatar
     * @param string $type
     * @return boolean
     */
    public function updateUserAvatar($orgId, $userId, $avatar, $type)
    {
        return $this->updateUserInfo($orgId, $userId, array(
            'avatars'    => $avatar,
            'avatartype' => $type
        ));
    }

    /**
     * 删除用户记录
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    public function deleteUser($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'call sp_md_delete_user(' . $this->_db->quote($orgId) . ', ' . $this->_db->quote($userId) . ')';

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * SELECT org_id AS orgid, user_id AS userid, email
     * FROM md_user_email
     * WHERE
     * org_id = :orgid
     * user_id = :userid
     *
     * @param $condition
     * @param $filter
     */
    public function getEmail(array $condition, $filter = null)
    {
        $table   = 'md_user_email';
        $columns = 'org_id AS orgid, user_id AS userid, email';
        $where   = array();
        $bind    = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
        }

        if (!empty($condition['userid'])) {
            $where[] = 'user_id = :userid';
        }

        if (!empty($condition['email'])) {
            $where[] = 'email = :email';
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $condition);

            if (!$record) {
                return null;
            }

            //return Oray_Dao::record('Dao_Md_User_Record_Email', $record);
            return $record;

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 添加绑定邮箱
     *
     * @param array $params
     */
    public function createEmail(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['userid'])
            || empty($params['email']))
        {
            return false;
        }

        $table = 'md_user_email';
        $bind  = array(
            'org_id'  => $params['orgid'],
            'user_id' => $params['userid'],
            'email'   => $params['email']
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
     * 删除绑定密保邮箱
     *
     * DELETE FROM md_user_email WHERE org_id = :orgid AND user_id = :userid
     *
     * @param string $orgId
     * @param string $userId
     */
    public function deleteEmail($orgId, $userId)
    {
        $sql = "DELETE FROM md_user_email WHERE org_id = :orgid AND user_id = :userid";

        try {
            $this->_db->query($sql, array('orgid' => $orgId, 'userid' => $userId));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 是否已存在绑定邮箱
     *
     * SELECT COUNT(0) AS count FROM md_user_email WHERE email = :email
     *
     * @param string $email
     */
    public function existsEmail($email)
    {
        $sql = "SELECT COUNT(0) AS count FROM md_user_email WHERE email = :email";

        try {
            $row = $this->_db->fetchRow($sql, array('email' => $email));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return (int) $row['count'] > 0;
    }

    /**
     * 是否管理员
     *
     * @param string $orgId
     * @param string $userId
     */
    public function isAdmin($orgId, $userId)
    {
        $sql = 'SELECT COUNT(0) FROM md_site_admin WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND user_id = ' . $this->_db->quote($userId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count > 0;
    }

    /**
     * 添加可用产品
     *
     * @param string $orgId
     * @param string $userId
     * @param string $productId
     * @return boolean
     */
    /*public function addProduct($orgId, $userId, $productId)
    {
        if (!$orgId || !$userId || !$productId) {
            return false;
        }

        $table = 'md_user_product';
        $bind  = array(
            'org_id'     => $orgId,
            'user_id'    => $userId,
            'product_id' => $productId
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }*/

    /**
     * 移除可用产品
     *
     * @param string $orgId
     * @param string $userId
     * @param string $productId
     * @return boolean
     */
    /*public function removeProduct($orgId, $userId, $productId = null)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $where = array();
        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'user_id = ' . $this->_db->quote($userId);

        if ($productId) {
            $where[] = 'product_id = ' . $this->_db->quote($productId);
        }

        $where = implode(' AND ', $where);

        $sql = "DELETE FROM md_user_product WHERE {$where}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }*/

    /**
     * 获取用户可用的产品
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    /*public function getProductIds($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'SELECT product_id AS productid FROM md_user_product WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND user_id = ' . $this->_db->quote($userId);

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $row) {
            $ret[] = $row['productid'];
        }

        return $ret;
    }*/

    /**
     * 获取用户所属的角色ID
     *
     * @param string $orgId
     * @param string $userId
     * @return array
     */
    public function getGroupIds($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'SELECT group_id AS groupid FROM md_user_group WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND user_id = ' . $this->_db->quote($userId);

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $row) {
            $ret[] = $row['groupid'];
        }

        return $ret;
    }

    /**
     * 删除用户角色
     *
     * @param string $orgId
     * @param string $userId
     * @param string $groupId
     * @return boolean
     */
    public function removeGroups($orgId, $userId, $groupId = null)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);

        $where[] = 'user_id = ' . $this->_db->quote($userId);

        if ($groupId) {
            $where[] = 'group_id = ' . $this->_db->quote($groupId);
        }

        $where = implode(' AND ', $where);

        $sql = 'DELETE FROM md_user_group WHERE ' . $where;

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 获取用户所属的角色ID
     *
     * @param string $orgId
     * @param string $userId
     * @return array
     */
    public function getRoleIds($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'SELECT role_id AS roleid FROM md_user_role WHERE org_id = ' . $this->_db->quote($orgId) . ' '
             . 'AND user_id = ' . $this->_db->quote($userId);

        $records = $this->_db->fetchAll($sql);

        $ret = array();
        foreach ($records as $row) {
            $ret[] = $row['roleid'];
        }

        return $ret;
    }

    /**
     * 删除用户角色
     *
     * @param string $orgId
     * @param string $userId
     * @param string $groupId
     * @return boolean
     */
    public function removeRoles($orgId, $userId, $roleId = null)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);

        $where[] = 'user_id = ' . $this->_db->quote($userId);

        if ($roleId) {
            $where[] = 'group_id = ' . $this->_db->quote($roleId);
        }

        $where = implode(' AND ', $where);

        $sql = 'DELETE FROM md_user_role WHERE ' . $where;

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 获取登录失败次数
     *
     * @param string $orgId
     * @param string $userId
     * @return int
     */
    public function getLoginFailCount($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'SELECT login_retry FROM md_user '
             . 'WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 登录失败计数累加
     *
     * @param string $orgId
     * @param string $userId
     * @return boolean
     */
    public function countLoginFail($orgId, $userId)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $sql = 'UPDATE md_user SET login_retry = login_retry + 1 '
             . 'WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 清除用户登录失败计数
     * 解除用户锁定
     *
     * @param string $orgId
     * @param string $userId
     */
    public function clearLoginFail($orgId, $userId)
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
     * 格式化结果
     *
     * @param array $record
     */
    public function formatRecord($record)
    {
        $record['createtime'] = strtotime($record['createtime']);

        return $record;
    }

    /**
     * 生成唯一ID
     *
     * @return string
     */
    public static function getUniqueId($orgId, $userId)
    {
        return substr(md5($userId . '@' . $orgId . '^' . time()), 8, 16);
    }
}