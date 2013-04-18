<?php
/**
 * 用户管理控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: UserController.php 2825 2013-04-15 09:55:11Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class User_UserController extends TuduX_Controller_Admin
{
    public function init()
    {
        if ($cookies = $this->_request->getParam('cookies')) {
            if ($cookies = @unserialize($cookies)) {
                foreach ($cookies as $key => $val) {
                    $_COOKIE[$key] = $val;
                }
            }
        }

        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'user'));
        $this->view->LANG   = $this->lang;

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_TS => $this->getDb('ts1')
        ));
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('create', 'update', 'batch.update', 'delete', 'unlock', 'upload.csv', 'upload', 'updateavatar', 'import'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 输出用户列表
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        /* @var @daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray('deptid');

        // 群组
        /* @var @daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $groups = $daoGroup->getGroups(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));

        // 权限组
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'));

        $this->view->depts  = $depts;
        $this->view->roles  = $roles->toArray();
        $this->view->groups = $groups->toArray();
        $this->view->org    = $org->toArray();
    }

    /**
     * 输出用户列表数据
     */
    public function listAction()
    {
        $page     = null;
        $sort     = $this->_request->getQuery('sort');
        $sortType = $this->_request->getQuery('sorttype');
        $query    = $this->_request->getQuery();
        $param    = array();

        $condition = array(
            'orgid' => $this->_orgId
        );

        if (isset($query['p'])) {
            $page = max(1, (int) $query['p']);
        }

        if (isset($query['keyword'])) {
            $keyword = trim($query['keyword']);

            if ($keyword) {
                $param['keyword'] = $keyword;
                $condition['keyword'] = $keyword;
            }
        }

        if (!empty($query['deptid']) && $query['deptid'] != '^root') {
            $param['deptid'] = $query['deptid'];
            $condition['deptid'] = $query['deptid'];
        }

        // 角色
        if (!empty($query['roleid'])) {
            $param['roleid'] = $query['roleid'];
            $condition['roleid'] = $query['roleid'];
        }

        // 群组ID
        if (!empty($query['groupid'])) {
            $param['groupid'] = $query['groupid'];
            $condition['groupid'] = $query['groupid'];
        }

        // 帐号状态
        if (isset($query['status']) && '' !== $query['status']) {
            $param['status'] = $query['status'];
            $condition['status'] = (int) $query['status'];
        }

        // 性别
        if (!empty($query['gender'])) {
            $param['gender'] = $query['gender'];
            $condition['gender'] = $query['gender'];
        }

        // 创建时间
        if (!empty($query['starttime'])) {
            $st = strtotime($query['starttime']);
            if ($st) {
                $param['starttime'] = $query['starttime'];
                $condition['createtime']['start'] = $st;
            }
        }

        if (!empty($query['endtime'])) {
            $et = strtotime($query['starttime']);
            if ($et) {
                $param['starttime'] = $query['starttime'];
                $condition['createtime']['end'] = $et;
            }
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        if (!in_array($sort, array('ordernum', 'account', 'status', 'deptid', 'createtime'))) {
            $sort = 'ordernum';
        }

        $sortType = $sortType == 'DESC' ? 'DESC' : 'ASC';

        $sort .= ' ' . $sortType;

        $users  = $daoUser->getUserPage($condition, $sort, $page, 30);

        $counts = $daoUser->getUserCount(array('orgid' => $this->_orgId), 'status');

        $nums  = array('total' => 0, 'disabled' => 0, 'temp' => 0, 'normal' => 0);
        $total = 0;
        foreach ($counts as $status => $count) {
            $key = $status == 0 ? 'disabled' : ($status == 2 ? 'temp' : 'normal');
            $nums[$key] = $count;
            $total += $count;
        }
        $nums['total'] = $total;

        // 返回数据
        $data = array(
            'param' => $param,
            'page'  => $page,
            'count' => $nums,
            'records' => $users->toArray()
        );

        return $this->json(true, null, $data);
    }

    /**
     *
     * 输出用户列表
     */
    public function usersAction()
    {
        $deptId = $this->_request->getQuery('deptid');

        if ($deptId == '_root') {
            $deptId = '^root';
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        $users = $daoUser->getUsers(array('orgid' => $this->_orgId, 'deptid' => $deptId), array('isnormal' => true));

        $this->json(true, null, $users->toArray());
    }

    /**
     * 输出组织架构
     */
    public function structLoadAction()
    {
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        /* @var $daoDepartment Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');

        $users = $daoUser->getUsers(array('orgid' => $this->_orgId), array('isnormal' => true))->toArray();

        $departments = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId))->toArray();

        $data = array('org' => $org, 'users' => $users, 'depts' => $departments);

        if ($this->_request->getQuery('group')) {
            /* @var $daoGroup Dao_Md_User_Group */
            $daoGroup = $this->getDao('Dao_Md_User_Group');

            $groups = $daoGroup->getGroups(array('orgid' => $this->_orgId), null, 'ordernum DESC');

            $data['groups'] = $groups->toArray();
        }

        return $this->json(true, null, $data);

    }

    /**
     * 创建用户
     */
    public function createAction()
    {
        $post     = $this->_request->getPost();
        $userId   = isset($post['userid']) ? trim(strtolower($post['userid'])) : null;
        $groups   = !empty($post['groupid']) ? $post['groupid'] : array();
        $roles    = !empty($post['roleid']) ? $post['roleid'] : array();
        $trueName = trim($post['truename']);
        $password = trim($post['password']);
        $address  = $userId . '@' . $this->_orgId;

        // 架构配置
        $castDepts = !empty($post['castdept']) ? (array) str_replace('_', '^', $post['castdept']) : array();
        $castUsers = !empty($post['castuser']) ? (array) $post['castuser'] : array();

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        $params = array(
            'orgid'    => $this->_orgId,
            'userid'   => $userId,
            'groupid'  => $groups,
            'roleid'   => $roles,
            'castdept' => $castDepts,
            'castuser' => $castUsers,
            'deptid'   => isset($post['deptid']) ? $post['deptid'] : null,
            'status'   => isset($post['status']) ? (int) $post['status'] : 1,
            'isshow'   => !empty($post['isshow']) ? 1 : 0,
            'ordernum' => isset($post['ordernum']) ? (int) $post['ordernum'] : 0,
            'truename' => $trueName,
            'password' => $password,
            'position' => $post['position'],
            'gender'   => (int) $post['gender'],
            'tel'      => $post['tel'],
            'email'    => $post['email'],
            'mobile'   => $post['mobile'],
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        if (!empty($post['maxndquota'])) {
            $params['maxndquota'] = $post['maxndquota'];
        }

        if (!empty($post['nick'])) {
            $params['nick'] = $post['nick'];
        }

        if (!empty($post['idnumber'])) {
            $params['idnumber'] = $post['idnumber'];
        }

        if (!empty($post['bir-year']) && !empty($post['bir-month']) && !empty($post['bir-day'])) {
            $birthday = $post['bir-year'] . '-' . $post['bir-month'] . '-' . $post['bir-day'];
            $params['birthday'] = @strtotime($birthday);
        }

        if ($params['deptid'] == '^new') {
            $params['deptname'] = $post['deptname'];
            if (!empty($post['dept-parent'])) {
                $params['deptparentid'] = $post['dept-parent'];
            }
        }

        if (!empty($post['newgroup']) && is_array($post['newgroup'])) {
            $params['newgroup'] = $post['newgroup'];
            foreach ($post['newgroup'] as $index) {
                $params['groupname-' . $index] = trim($post['groupname-' . $index]);
            }
        }

        /* @var $modelUser Model_User_User */
        $modelUser = Tudu_Model::factory('Model_User_User');

        $modelUser->addFilter('create', array($this, 'createGroup'), 1);

        $modelUser->addFilter('create', array($this, 'createDept'), 2);

        try {
            $modelUser->execute('create', array(&$params));
        } catch (Model_User_Exception $e) {
            $message = '用户数据创建失败';
            switch ($e->getCode()) {
                case Model_User_User::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_User::CODE_TOO_MUCH_USER:
                    $message = '本组织用户数已达到上限';
                    break;
                case Model_User_User::CODE_MISSING_UID:
                    $message = '请输入用户名';
                    break;
                case Model_User_User::CODE_MISSING_UNID:
                    $message = '缺少参数[uniqueid]';
                    break;
                case Model_User_User::CODE_INVALID_UID:
                    $message = '请输入英文字母或数字作为用户名';
                    break;
                case Model_User_User::CODE_INVALID_PWD:
                    $message = '请输入用户初始登录密码';
                    break;
                case Model_User_User::CODE_INVALID_USERNAME:
                    $message = '请输入用户真实姓名';
                    break;
                case Model_User_User::CODE_INVALID_EMAIL:
                    $message = '您输入的邮箱格式有误';
                    break;
                case Model_User_User::CODE_USER_EXISTS:
                    $message = '用户已存在';
                    break;
                case Model_User_User::CODE_NOT_ENOUGH_NDSPACE:
                    $message = '没有足够的可分配网盘空间';
                    break;
                case Model_User_User::CODE_INVALID_BIRTHDAY:
                    $message = '无效的出生日期';
                    break;
                case Model_User_User::CODE_INVALID_DEPTNAME:
                    $message = '请输入新部门名称';
                    break;
                case Model_User_User::CODE_DEPT_PARENT_NOTEXISTS:
                    $meaasge = '新建部门的父级部门不存在或已被删除';
                    break;
                case Model_User_User::CODE_SAVE_DEPT_FAILED:
                    $message = '所属部门创建失败';
                    break;
                case Model_User_User::CODE_SAVE_FAILED:
                    $message = '用户数据创建失败';
                    break;
                case Model_User_User::CODE_EXCEED_MAX_NDSPACE:
                    $message = '每个用户最多只能设置1000MB的网盘空间';
                    break;
            }

            return $this->json(false, $message);
        }

        // 清空组织用户列表
        $this->_bootstrap->memcache->delete('TUDU-USER-LIST-' . $this->_orgId);

        // 记录CAST更新时间
        $this->setUpdateCastTime();

        $this->json(true, '创建用户成功');
    }

    /**
     * 更新用户
     *
     */
    public function updateAction()
    {
        $post     = $this->_request->getPost();
        $userId   = isset($post['userid']) ? trim($post['userid']) : null;
        $groups   = !empty($post['groupid']) ? $post['groupid'] : array();
        $roles    = !empty($post['roleid']) ? $post['roleid'] : array();
        $trueName = trim($post['truename']);
        $password = trim($post['password']);
        $address  = $userId . '@' . $this->_orgId;

        // 架构配置
        $castDepts = !empty($post['castdept']) ? (array) str_replace('_', '^', $post['castdept']) : array();
        $castUsers = !empty($post['castuser']) ? (array) $post['castuser'] : array();

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        $params = array(
            'orgid'    => $this->_orgId,
            'userid'   => $userId,
            'groupid'  => $groups,
            'roleid'   => $roles,
            'castdept' => $castDepts,
            'castuser' => $castUsers,
            'deptid'   => isset($post['deptid']) ? $post['deptid'] : null,
            'status'   => isset($post['status']) ? (int) $post['status'] : 1,
            'isshow'   => !empty($post['isshow']) ? 1 : 0,
            'ordernum' => isset($post['ordernum']) ? (int) $post['ordernum'] : 0,
            'truename' => $trueName,
            'position' => $post['position'],
            'gender'   => (int) $post['gender'],
            'tel'      => $post['tel'],
            'email'    => $post['email'],
            'mobile'   => $post['mobile'],
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        if (!empty($post['maxndquota'])) {
            $params['maxndquota'] = $post['maxndquota'];
        }

        if (!empty($post['nick'])) {
            $params['nick'] = $post['nick'];
        }

        if (!empty($post['idnumber'])) {
            $params['idnumber'] = $post['idnumber'];
        }

        if (!empty($post['bir-year']) && !empty($post['bir-month']) && !empty($post['bir-day'])) {
            $birthday = $post['bir-year'] . '-' . $post['bir-month'] . '-' . $post['bir-day'];
            $params['birthday'] = @strtotime($birthday);
        }

        if ($params['deptid'] == '^new') {
            $params['deptname'] = $post['deptname'];
            if (!empty($post['dept-parent'])) {
                $params['deptparentid'] = $post['dept-parent'];
            }
        }

        if (!empty($post['newgroup']) && is_array($post['newgroup'])) {
            $params['newgroup'] = $post['newgroup'];
            foreach ($post['newgroup'] as $index) {
                $params['groupname-' . $index] = trim($post['groupname-' . $index]);
            }
        }

        if (!empty($post['password'])) {
            $params['password'] = $post['password'];
        }

        if (!empty($post['avatars'])) {
            $options = $this->_bootstrap->getOption('upload');
            $fileName = $options['tempdir'] . '/' . $post['avatars'];

            if (file_exists($fileName)) {
                $info = getimagesize($fileName);

                $params['avatartype'] = $info['mime'];
                $params['avatars']    = base64_encode(file_get_contents($fileName));
            }
        }

        /* @var $modelUser Model_User_User */
        $modelUser = Tudu_Model::factory('Model_User_User');

        $modelUser->addFilter('update', array($this, 'createGroup'), 1);

        $modelUser->addFilter('update', array($this, 'createDept'), 2);

        try {
            $modelUser->execute('update', array(&$params));
        } catch (Model_User_Exception $e) {
            $message = '更新用户数据失败';
            switch ($e->getCode()) {
                case Model_User_User::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_User::CODE_NOT_MODIFY_PWD:
                    $message = '不能修改超级管理员帐号密码';
                    break;
                case Model_User_User::CODE_INVALID_USERNAME:
                    $message = '请输入用户真实姓名';
                    break;
                case Model_User_User::CODE_INVALID_EMAIL:
                    $message = '您输入的邮箱格式有误';
                    break;
                case Model_User_User::CODE_USER_NOTEXISTS:
                    $message = '用户不已存在或已被删除';
                    break;
                case Model_User_User::CODE_NOT_ENOUGH_NDSPACE:
                    $message = '没有足够的可分配网盘空间';
                    break;
                case Model_User_User::CODE_LESS_NDSPACE:
                    $message = '网盘空间不能小于当前已使用空间';
                    break;
                case Model_User_User::CODE_INVALID_BIRTHDAY:
                    $message = '无效的出生日期';
                    break;
                case Model_User_User::CODE_INVALID_DEPTNAME:
                    $message = '请输入新部门名称';
                    break;
                case Model_User_User::CODE_DEPT_PARENT_NOTEXISTS:
                    $meaasge = '新建部门的父级部门不存在或已被删除';
                    break;
                case Model_User_User::CODE_SAVE_DEPT_FAILED:
                    $message = '所属部门创建失败';
                    break;
                case Model_User_User::CODE_SAVE_FAILED:
                    $message = '更新用户数据失败';
                    break;
                case Model_User_User::CODE_EXCEED_MAX_NDSPACE:
                    $message = '每个用户最多只能设置1000MB的网盘空间';
                    break;
            }

            return $this->json(false, $message);
        }

        // 清空组织用户列表cache
        $this->_bootstrap->memcache->delete('TUDU-USER-LIST-' . $this->_orgId);

        // 清除IM相关缓存
        $this->_clearUserCache($address);

        $this->setUpdateCastTime();

        $this->json(true, '用户数据更新成功');
    }

    /**
     * 批量更新
     */
    public function batchUpdateAction()
    {
        $userIds = (array) $this->_request->getPost('userid');
        $post    = $this->_request->getPost();

        if (count($userIds) <= 0) {
            return $this->json(false, '没有要更新的用户');
        }

        /* @var $modelUser Model_User_User */
        $modelUser = Tudu_Model::factory('Model_User_User');

        $edit = array(
            'truename' => false,
            'email'    => false,
            'password' => !empty($post['edit-password']),
            'dept'     => !empty($post['edit-department']),
            'status'   => !empty($post['edit-status']),
            'role'     => !empty($post['edit-role']),
            'group'    => !empty($post['edit-group']),
            'cast'     => !empty($post['edit-cast']),
            'netdisk'  => !empty($post['edit-netdisk'])
        );

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        // 准备参数
        $params = array(
            'orgid'    => $this->_orgId,
            'edit'     => $edit,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        // 修改密码
        if ($edit['password']) {
            if (empty($post['password'])) {
                return $this->json(false, '请填写新的用户密码');
            }
            $params['password'] = $post['password'];
        }

        // 账号状态
        if ($edit['status']) {
            $params['status'] = isset($post['status']) ? (int) $post['status'] : null;
        }

        // 网盘空间
        if ($edit['netdisk']) {
            if (!isset($post['ndquota']) || $post['ndquota'] == '') {
                return $this->json(false, '请填写新的网盘空间');
            }
            $params['maxndquota'] = $post['ndquota'];
        }

        // 部门
        if ($edit['dept']) {
            if ($post['deptid'] == '^new') {
                if (empty($post['deptname'])) {
                    return $this->json(false, '请输入新部门名称');
                }
                $params['deptname']    = $post['deptname'];
                $params['deptparentid'] = $post['dept-parent'];
            }
            $params['deptid'] = $post['deptid'];
        }

        // 群组
        if ($edit['group']) {
            $groups = isset($post['groupid']) ? (array) $post['groupid'] : array();
            if (!empty($post['newgroup']) && is_array($post['newgroup'])) {
                $params['newgroup'] = $post['newgroup'];
                foreach ($post['newgroup'] as $index) {
                    $params['groupname-' . $index] = trim($post['groupname-' . $index]);
                }
            }
            $params['groupid'] = $groups;
        }

        // 权限组
        if ($edit['role']) {
            $roles = isset($post['roleid']) ? (array) $post['roleid'] : array();
            $params['roleid'] = $roles;
        }

        // 架构配置
        if ($edit['cast']) {
            $params['castdept'] = !empty($post['castdept']) ? (array) str_replace('_', '^', $post['castdept']) : array();
            $params['castuser'] = !empty($post['castuser']) ? (array) $post['castuser'] : array();
        }

        if ($edit['dept']) {
            $modelUser->addFilter('update', array($this, 'createDept'), 2);
        }

        if ($edit['group']) {
            $modelUser->addFilter('update', array($this, 'createGroup'), 1);
        }

        foreach ($userIds as $userId) {
            $params['userid'] = $userId;

            try {
                $modelUser->execute('update', array(&$params));
            } catch (Model_User_Exception $e) {
                $message = '用户数据批量更新失败';
                switch ($e->getCode()) {
                    case Model_User_User::CODE_INVALID_ORGID:
                        $message = '缺少参数[orgid]';
                        break;
                    case Model_User_User::CODE_NOT_MODIFY_PWD:
                        $message = '不能修改超级管理员帐号密码';
                        break;
                    case Model_User_User::CODE_INVALID_USERNAME:
                        $message = '请输入用户真实姓名';
                        break;
                    case Model_User_User::CODE_USER_NOTEXISTS:
                        $message = '用户不已存在或已被删除';
                        break;
                    case Model_User_User::CODE_NOT_ENOUGH_NDSPACE:
                        $message = '没有足够的可分配网盘空间';
                        break;
                    case  Model_User_User::CODE_LESS_NDSPACE:
                        $message = '网盘空间不能小于当前已使用空间';
                        break;
                    case Model_User_User::CODE_INVALID_BIRTHDAY:
                        $message = '无效的出生日期';
                        break;
                    case Model_User_User::CODE_INVALID_DEPTNAME:
                        $message = '请输入新部门名称';
                        break;
                    case Model_User_User::CODE_DEPT_PARENT_NOTEXISTS:
                        $meaasge = '新建部门的父级部门不存在或已被删除';
                        break;
                    case Model_User_User::CODE_SAVE_DEPT_FAILED:
                        $message = '所属部门创建失败';
                        break;
                    case Model_User_User::CODE_SAVE_FAILED:
                        $message = '用户数据批量更新失败';
                        break;
                    case Model_User_User::CODE_EXCEED_MAX_NDSPACE:
                        $message = '每个用户最多只能设置1000MB的网盘空间';
                        break;
                }

                return $this->json(false, $message);
            }

            // 清除IM相关缓存
            $this->_clearUserCache($userId . '@' . $this->_orgId);
        }

        // 清空组织用户列表cache
        $this->_bootstrap->memcache->delete('TUDU-USER-LIST-' . $this->_orgId);

        $this->setUpdateCastTime();

        $this->json(true, '用户数据批量更新成功');
    }

    /**
     * 创建账号 - 页面显示
     */
    public function addAction()
    {
        /* @var $daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        // 群组
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $groups = $daoGroup->getGroups(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'))->toArray();

        // 权限组
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'))->toArray();

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $usedNetdiskQuota = $daoOrg->getUsedNetdiskQuota($this->_orgId);

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));

        $this->view->avaliblequota = $org->maxNdQuota * 1000000 - $usedNetdiskQuota;
        $this->view->org    = $org->toArray();
        $this->view->roles  = $roles;
        $this->view->depts  = $depts;
        $this->view->groups = $groups;
    }

    /**
     * 修改账号信息 - 页面显示
     */
    public function editAction()
    {
        $userId = $this->_request->getQuery('userid');

        $back = $this->_request->getQuery('back');

        if (empty($userId)) {
            Oray_Function::alert('参数错误[userid]');
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        $user = $daoUser->getUser(array('orgid' => $this->_orgId, 'userid' => $userId));

        if (null === $user) {
            Oray_Function::alert('用户不存在或已被删除');
        }

        $userInfo = $daoUser->getUserInfo(array('orgid' => $this->_orgId, 'userid' => $userId));

        $users = $daoUser->getUsers(array('orgid' => $this->_orgId))->toArray();

        /* @var $daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        // 群组
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $groups = $daoGroup->getGroups(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'))->toArray();

        // 权限组
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'))->toArray();

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $usedNetdiskQuota = $daoOrg->getUsedNetdiskQuota($this->_orgId);

        $daoCast = $this->getDao('Dao_Md_User_Cast');

        $disableUsers = $daoCast->getHiddenUsers($this->_orgId, $user->userId);
        $disableDepts = $daoCast->getHiddenDepartments($this->_orgId, $user->userId);

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder', $this->_multidb->getDb('ts' . $this->_user->tsId));

        $folderRoot = $daoFolder->getFolder(array('uniqueid' => $user->uniqueId, 'folderid' => '^root'));

        $quotaUsed = 0;
        if (null !== $folderRoot) {
            $quotaUsed = $folderRoot->folderSize;
        }

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));

        $userInfo = $userInfo->toArray();
        $birthday = $userInfo['birthday'] ? explode('-', date('Y-m-d', $userInfo['birthday'])) : array(null, null, null);
        $userInfo['birthyear']  = $birthday[0];
        $userInfo['birthmonth'] = $birthday[1];
        $userInfo['birthdate']  = $birthday[2];

        $this->view->avaliblequota = $org->maxNdQuota * 1000000 - $usedNetdiskQuota;
        $this->view->user = $user->toArray();
        $this->view->userinfo = $userInfo;
        $this->view->quotaused = $quotaUsed;
        $this->view->roles  = $roles;
        $this->view->org    = $org->toArray();
        $this->view->depts  = $depts;
        $this->view->groups = $groups;
        $this->view->users  = $users;
        $this->view->cast   = array(
            'users' => $disableUsers,
            'depts' => $disableDepts
        );
        $this->view->back  = $back;
    }

    /**
     * 批量修改账号信息 - 页面显示
     */
    public function batchAction()
    {
        $userId = $this->_request->getQuery('userid');

        $back = $this->_request->getQuery('back');

        if (!$userId) {
            return $this->json(false, $this->lang['invalid_params_userid']);
        }

        $userIds = explode(',', $userId);

        if (count($userIds) <= 0) {
            Oray_Function::alert('没有选择需要编辑的用户');
        }

        /* @var $daoUser Dao_Md_User_User*/
        $daoUser = $this->getDao('Dao_Md_User_User');

        $users = $daoUser->getUsers(array('orgid' => $this->_orgId))->toArray();

        $modifies = array();

        foreach ($users as $item) {
            if (in_array($item['userid'], $userIds)) {
                $modifies[] = $item;
            }
        }

        // 群组
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $groups = $daoGroup->getGroups(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'))->toArray();

        // 权限组
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'))->toArray();

        /* @var $daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $this->view->users  = $users;
        $this->view->userids= $userIds;
        $this->view->depts  = $depts;
        $this->view->roles  = $roles;
        $this->view->groups = $groups;
        $this->view->modifies = $modifies;
        $this->view->back  = $back;
        $this->view->org    = $org->toArray();
    }

    /**
     * 批量导入页面输出
     */
    public function importHtmlAction()
    {
        $back = $this->_request->getQuery('back');
        $cookies = $this->_request->getCookie();

        $this->view->back  = $back;
        $this->view->cookies = serialize($cookies);
    }

    /**
     * 批量导入模板
     */
    public function importCsvAction()
    {
        $data = file_get_contents($this->_options['data']['path'] . '/user-import.zip');

        $this->_response->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + 36000), true);
        $this->_response->setHeader('Content-Type', 'application/zip' . ', charset=utf-8');
        $this->_response->setHeader('Content-Length', strlen($data));
        $this->_response->setHeader('Pragma', 'private', true);
        $this->_response->setHeader('Cache-control', 'private', true);

        // FF Only
        if (false !== strpos(strtolower($this->_request->getServer('HTTP_USER_AGENT')), 'firefox')) {
            $this->_response->setHeader('Content-Disposition', 'attachment' . ';filename*=UTF-8\'\'图度用户数据导入模板.zip');

        // Other
        } else {
            $this->_response->setHeader('Content-Disposition', 'attachment' . ';filename=' . urlencode('图度用户数据导入模板') . '.zip');
        }

        $this->_response->sendHeaders();

        echo $data;
        @flush();
        @ob_flush();

        // 取消输出 - 主要避免再次输出文件头，两种方式，第一种比较直接
        $this->getFrontController()->returnResponse(true);
    }

    /**
     * 批量保存导入的账号数据
     */
    public function importAction()
    {
        $post = $this->_request->getPost();

        if (!$post) {
            return $this->json(false, '批量导入失败 ', null, false);
        }

        /* @var $modelUser Model_User_User */
        $modelUser = Tudu_Model::factory('Model_User_User');

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'))->toArray();

        /* @var $daoDepartment Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        $userNum      = (array) $post['user'];
        $result       = array();
        $isReloadDept = false; //是否需要重新加载组织架构信息
        foreach ($userNum as $num) {
            $params = array(
                'import' => true,
                'orgid'  => $this->_orgId,
                'groupid' => array('^all'),
                'castdept' => array(),
                'castuser' => array(),
                'status'   => isset($post['status-' . $num]) ? (int) $post['status-' . $num] : 1,
                'isshow'   => 1,
                'ordernum' => 0,
                'truename' => $post['truename-' . $num],
                'gender'   => (int) $post['gender-' . $num],
                'operator' => $this->_user->userId,
                'clientip' => $clientIp,
                'local'    => $local
            );

            if ($isReloadDept) {
                $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();
            }

            if ($post['deptname-' . $num] == '-') {
                $deptId = '^root';
            } elseif (strpos($post['deptname-' . $num], '/')) {
                $deptNames = explode('/', $post['deptname-' . $num]);
                $count = count($deptNames);
                $parentId = '^root';
                $error = null;
                for ($i = 0; $i < $count - 1; $i++) {
                    $parentId = $this->getDeptId($depts, $deptNames[$i], $parentId);
                    if (!$parentId) {
                        $error = array('num' => $num, 'success' => false, 'message' => '失败，父级部门不存在');
                        break ;
                    }
                }
                if (!empty($error)) {
                    $result[] = $error;
                    $error = null;
                    continue ;
                }
                $deptName = $deptNames[$count-1];
            } else {
                $parentId = '^root';
                $deptName = $post['deptname-' . $num];
            }

            if (isset($deptName)) {
                $deptId = $this->getDeptId($depts, $deptName, $parentId);
                $params['deptid'] = $deptId;
            }

            // 部门ID不存在，则创建新部门
            if (!$deptId) {
                $params['deptid']       = '^new';
                $params['deptparentid'] = $parentId;
                $params['deptname']     = $deptName;
                $modelUser->addFilter('create', array($this, 'createDept'), 1);

                $isReloadDept = true;
            } else {
                $isReloadDept = false;
            }

            $userId = strtolower($post['userid-' . $num]);
            $params['userid'] = $userId;

            $roleId = $this->getRoleId($roles, $post['rolename-' . $num]);
            if ($roleId) {
                $roleId = '^user';
            }
            $params['roleid'] = (array) $roleId;

            try {
                $modelUser->execute('create', array(&$params));

                $result[] = array('num' => $num, 'success' => true, 'message' => '成功，默认密码为：' . $params['password']);
                // 清除IM相关缓存
                $this->_clearUserCache($userId . '@' . $this->_orgId);
            } catch (Model_User_Exception $e) {
                switch ($e->getCode()) {
                    case Model_User_User::CODE_INVALID_ORGID:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '缺少参数[orgid]');
                        break;
                    case Model_User_User::CODE_INVALID_DOMAINID:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '参数错误[domainid]');
                        break;
                    case Model_User_User::CODE_TOO_MUCH_USER:
                        return $this->json(false, '本组织用户数已达到上限', null, false);
                        break;
                    case Model_User_User::CODE_MISSING_UID:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，缺少用户账号');
                        break;
                    case Model_User_User::CODE_INVALID_UID:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，请输入英文字母或数字作为用户名');
                        break;
                    case Model_User_User::CODE_INVALID_PWD:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，获取组织初始登录密码失败');
                        break;
                    case Model_User_User::CODE_INVALID_USERNAME:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，缺少用户真实姓名');
                        break;
                    case Model_User_User::CODE_USER_EXISTS:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，账号已存在');
                        break;
                    case Model_User_User::CODE_SAVE_FAILED:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，用户数据创建失败');
                        break;
                    case Model_User_User::CODE_DEPT_PARENT_NOTEXISTS:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，父级部门不存在');
                        break;
                    case Model_User_User::CODE_SAVE_DEPT_FAILED:
                        $result[] = array('num' => $num, 'success' => false, 'message' => '失败，新建用户所属部门失败');
                        break;
                }
                continue ;
            }
        }

        // 清空组织用户列表
        $this->_bootstrap->memcache->delete('TUDU-USER-LIST-' . $this->_orgId);

        $this->setUpdateCastTime();

        return $this->json(true, null, $result, false);
    }

    /**
     * 上传CSV文件处理
     */
    public function uploadCsvAction()
    {
        $file = $_FILES['filedata'];
        if ($file['error'] > 0) {
            return $this->json(false, '文件不存在或上传csv文件失败', null, false);
        } else {
            /*if ($file['type'] !== 'application/vnd.ms-excel') {
                return $this->json(false, '文件格式不符合要求， 文件必须是.csv文件', null, false);
            }*/

            $data = file_get_contents($file['tmp_name']);
            $data = $this->formatData($data);

            if (!$data) {
                return $this->json(false, '解析csv文件失败', null, false);
            }
        }
        return $this->json(true, null, $data, false);
    }

    /**
     * 解除锁定
     */
    public function unlockAction()
    {
        /* @var @daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        $userId = $this->_request->getPost('userid');

        if (!$userId) {
            return $this->json(false, $this->lang['invalid_params_userid']);
        }

        $userIds = explode(',', $userId);
        $ret     = false;

        foreach ($userIds as $userId) {
            $ret |= $daoUser->clearLoginFail($this->_orgId, $userId);

            $this->_clearUserCache($userId . '@' . $this->_orgId);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['clear_failure_failure']);
        }

        return $this->json(true, $this->lang['clear_failure_success']);
    }

    /**
     * 删除用户
     */
    public function deleteAction()
    {
        $userId = $this->_request->getPost('userid');

        if (!$userId) {
            return $this->json(false, $this->lang['invalid_params_userid']);
        }

        $userIds  = explode(',', $userId);
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelUser Model_User_User */
        $modelUser = Tudu_Model::factory('Model_User_User');

        $params = array(
            'orgid'    => $this->_orgId,
            'userid'   => $userIds,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelUser->doDelete($params);

            foreach ($userIds as $userId) {
                // 清除相关缓存
                $this->_clearUserCache($userId . '@' . $this->_orgId);
            }

            // 清空组织用户列表cache
            $this->_bootstrap->memcache->delete('TUDU-USER-LIST-' . $this->_orgId);
        } catch (Model_User_Exception $e) {
            $message = $this->lang['user_delete_failure'];
            switch ($e->getCode()) {
                case Model_User_User::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_User::CODE_MISSING_UID:
                    $message = $this->lang['invalid_params_userid'];
                    break;
                case Model_User_User::CODE_DELETE_SUPER_ADMIN:
                    $message = '不能删除超级管理员帐号';
                    break;
                case Model_User_User::CODE_SAVE_FAILED:
                    $message = $this->lang['user_delete_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->json(true, $this->lang['user_delete_success']);
    }

    /**
     * 检查用户是否存在
     */
    public function checkAction()
    {
        $userId = trim($this->_request->getParam('userid'));

        if (!$userId) {
            return $this->_json(true, null);
        }

        $len = strlen($userId);
        if ($len < 2 || $len > 60) {
            return $this->json(false, '无效的帐号名');
        }

        if (!Oray_Function::isDomainStr($userId)) {
            return $this->json(false, '无效的帐号名');
        }

        $ret = $this->getDao('Dao_Md_User_User')->existsUser($this->_orgId, $userId);

        if ($ret) {
            return $this->json(false, '帐号已被使用');
        }

        $this->json(true, '帐号可用');
    }

    /**
     * 头像处理
     *
     */
    public function uploadAction()
    {
        $file = $_FILES['avatar-file'];
        $options = $this->_bootstrap->getOption('upload');

        $mimes = array(
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        );

        $message = null;
        do {
            if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                $message = $this->_lang['avatar_upload_failure'];
            }

            $info = getimagesize($file['tmp_name']);
            if (!in_array($info['mime'], $mimes)) {
                $message = $this->_lang['invalid_img_type'];
            }

            if ($file['size'] > $options['sizelimit']) {
                $message = '';
            }

            $hash = md5_file($file['tmp_name']);
            $uploadName = $options['tempdir'] . '/' . $hash;
        } while (false);

        if ($message) {
            return $this->json(false, $message, null, false);
        }

        $ret = @move_uploaded_file($file['tmp_name'], $uploadName);

        $this->json(true, '文件上传成功', array('hash' => $hash), false);
    }

    /**
     * 更新头像
     */
    public function updateavatarAction()
    {
        $hash = $this->_request->getPost('hash');
        $post = $this->_request->getPost();
        $options = $this->_bootstrap->getOptions();

        $userId = @$post['userid'];
        $x      = (int) $post['x'];
        $y      = (int) $post['y'];
        $width  = (int) $post['width'];
        $height = (int) $post['height'];

        $avatar = null;
        $avatarType = null;

        $mimes = array(
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        );

        if ($userId && !$this->getUserDao()->existsUser($this->_orgId, $userId)) {
            return $this->json(false, $this->_lang['user_not_exists']);
        }

        $fileName = $options['upload']['tempdir'] . '/' . $hash;
        if (!$hash || !file_exists($fileName)) {
            return $this->json(false, '文件上传失败');
        }

        $info = getimagesize($fileName);
        $avatarType = $info['mime'];

        if (!in_array($avatarType, $mimes)) {
            $this->json(false, '无效的头像文件格式');
        }

        $type = array_flip($mimes);
        $func = 'imagecreatefrom' . $type[$avatarType];
        $outputFunc = 'image' . $type[$avatarType];

        // tudutalk 不支持gif，先转成jpg
        if ($outputFunc == 'imagegif') {
            $outputFunc = 'imagejpeg';
        }

        $img = imagecreatetruecolor($options['avatars']['width'], $options['avatars']['height']);
        $src = $func($fileName);

        $width  = $width <= 0 ? $info[0] : $width;
        $height = $height <= 0 ? $info[1] : $height;

        imagecopyresampled($img, $src, 0, 0, $x, $y, $options['avatars']['width'], $options['avatars']['height'], $width, $height);

        $ret = $outputFunc($img, $fileName);

        if (!$ret) {
            $this->json(false, '头像文件更新失败');
        }

        return $this->json(true, '头像文件更新成功', array('avatar' => $hash));
    }

    /**
     * 临时显示头像
     */
    public function avatarAction()
    {
        $userId  = $this->_request->getQuery('userid');
        $hash    = $this->_request->getQuery('hash');
        $content = null;

        $this->_helper->viewRenderer->setNoRender();

        if ($userId) {

            $avatars = $this->getUserDao()->getUserAvatars($this->_orgId, $userId);

            if ($avatars != null) {
                $content = base64_decode($avatars->avatars);

                $this->_response->setHeader('Content-Type: ', $avatars->avatarsType);
            }

        } elseif ($hash) {
            $options = $this->getInvokeArg('bootstrap')->getOption('upload');
            $fileName = $options['tempdir'] . '/' . $hash;

            if (file_exists($fileName)) {
                $info = getimagesize($fileName);

                $this->_response->setHeader('Content-Type: ', $info['mime']);

                $content = file_get_contents($fileName);
            }
        }

        $this->_response->setHeader('Content-Length: ', strlen($content));

        echo $content;
        exit();
    }

    /**
     *
     * @param $size
     */
    public function formatFileSize($size, $base = 1024)
    {
        $units = array(pow($base, 3) => 'GB', pow($base, 2) => 'MB', $base => 'KB');

        foreach ($units as $step => $unit) {
            $val = $size / $step;
            if ($val >= 1) {
                return round($val, 2) . $unit;
            }
        }

        return $size . 'B';
    }

    /**
     * 格式化数据（csv）
     *
     * @param array $data
     * ruturn array
     */
    public function formatData($data)
    {
        $ret = array();
        if (!$data) {
            return $ret;
        }

        $item =  array(
            'truename' => '真实姓名',
            'email'    => '帐号',
            'gender'   => '性别',
            'status'   => '帐号状态',
            'deptname' => '所属部门',
            'rolename' => '所属权限组'
        );

        $data = iconv('gb2312', 'utf-8', $data);
        $data = preg_split('/\r\n|\n|\r/', $data);

        $count = count($data);
        if ($count <= 1) {
            return false;
        }

        $fields = explode(',', $data[0]);
        $rsFields = array();
        for ($i = 0; $i < count($fields); $i++) {
            foreach ($item as $key => $val) {
                if ($fields[$i] == $val) {
                    $rsFields[$key] = $i;
                }
            }
        }

        for ($i = 1; $i < count($data) - 1; $i++) {
            $userVal = explode(',', $data[$i]);
            if ($userVal) {
                for ($j = 0; $j < count($userVal); $j++) {
                    foreach ($rsFields as $key => $val) {
                        if ($j == $val) $rs[$key] = $userVal[$val];
                    }
                }

                if (isset($rs)) {
                    $ret[] = $rs;
                }
            }
        }

        return $ret;
    }

    /**
     * 创建部门
     */
    public function createDept(array &$params)
    {
        if ($params['deptid'] == '^new') {
            $isImport = !empty($params['import']) ? true : false;

            // 部门名称
            if (!$isImport && empty($params['deptname'])) {
                return $this->json(false, '请输入新部门名称');
            }

            /* @var $daoDepartment Dao_Md_Department_Department */
            $daoDepartment = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $departments = $daoDepartment->getDepartments(array('orgid' => $params['orgid']))->toArray('deptid');
            $parentId    = '^root';
            $deptPath    = array();

            if (!empty($params['deptparentid'])) {
                $parentId = $params['deptparentid'];
                // 父级部门不存在
                if (!isset($departments[$parentId])) {
                    if ($isImport) {
                        $params['exist-parentid'] = true;
                        return ;
                    } else {
                        return $this->json(false, '新建部门的父级部门不存在或已被删除');
                    }
                }

                $deptPath = array_merge($departments[$parentId]['path'], array($parentId));
            }

            $deptId = Dao_Md_Department_Department::getDeptId();

            $deptId = $daoDepartment->createDepartment(array(
                'orgid'    => $params['orgid'],
                'deptname' => $params['deptname'],
                'deptid'   => $deptId,
                'parentid' => $parentId,
                'ordernum' => $daoDepartment->getMaxOrderNum($params['orgid'], $parentId) + 1
            ));

            if (!$deptId) {
                if ($isImport) {
                    return ;
                } else {
                    return $this->json(false, '所属部门创建失败');
                }
            }

            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_CREATE,
                null,
                implode(':', array($params['orgid'], $deptId)),
                array('deptname' => $params['deptname'])
            );

            // 插入消息队列
            $options = $this->_bootstrap->getOption('httpsqs');
            $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);
            $data    = implode(' ', array(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_CREATE,
                null,
                implode(':', array($params['orgid'], $deptId))
            ));

            $httpsqs->put($data);

            $params['deptid'] = $deptId;
            unset($params['deptname']);
            unset($params['deptparentid']);
        }
    }

    /**
     *
     * @param array $params
     */
    public function createGroup(array &$params)
    {
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        $groups = !empty($params['groupid']) ? $params['groupid'] : array();

        // 新建群组
        if (!empty($params['newgroup']) && is_array($params['newgroup'])) {
            $groupIndexes = $params['newgroup'];
            foreach ($groupIndexes as $index) {
                $groupName = $params['groupname-' . $index];

                // 没有群组名称，跳过
                if (empty($groupName)) {
                    continue ;
                }

                $groupId   = Dao_Md_User_Group::getGroupId($params['orgid'], $groupName);
                $orderNum  = $daoGroup->getGroupMaxOrderNum(array('orgid' => $params['orgid']));

                $groupParams = array(
                    'orgid'     => $params['orgid'],
                    'groupid'   => $groupId,
                    'groupname' => $groupName,
                    'ordernum'  => $orderNum + 1
                );

                if ($daoGroup->createGroup($groupParams)) {
                    $groups[] = $groupId;
                    $this->_createLog(Dao_Md_Log_Oplog::MODULE_GROUP, Dao_Md_Log_Oplog::OPERATION_CREATE, null, $groupId, array('groupname' => $groupName));
                }
                unset($params['groupname-' . $index]);
            }

            $params['groupid'] = $groups;
            unset($params['newgroup']);
        }
    }

    /**
     *
     * @param array $users
     * return array
     */
    private function getUserIds(array $users)
    {
        $userIds = array();
        if (null === $users) {
            return $userIds;
        }

        foreach ($users as $user) {
            $userIds[] = $user['userid'];
        }

        return $userIds;
    }

    /**
     *
     * @param array $depts
     * return array
     */
    private function getDeptIds(array $depts)
    {
        $deptIds = array();
        if (null === $depts) {
            return $deptIds;
        }

        foreach ($depts as $dept) {
            $deptIds[] = $dept['deptid'];
        }

        return $deptIds;
    }

    /**
     * 通过权限组名称，获取权限组ID
     *
     * @param array $roles
     * @param string $rolename
     * return string $roleId
     */
    private function getRoleId(array $roles, $rolename)
    {
        $roleId = null;
        foreach ($roles as $role) {
            foreach ($role as $key => $val) {
                if ($key == 'rolename' && $rolename == $val) {
                    $roleId = $role['roleid'];
                }
            }
        }
        return $roleId;
    }

    /**
     * 通过部门名称，获取部门ID
     *
     * @param array $depts
     * @param string $deptname
     * @param string $parentId
     * return string $deptId
     */
    private function getDeptId(array $depts, $deptname, $parentId = null)
    {
        $deptId = null;
        foreach ($depts as $dept) {
            foreach ($dept as $key => $val) {
                if ($parentId) {
                    if ($key == 'deptname' && $deptname == $val && $dept['parentid'] == $parentId) {
                        $deptId = $dept['deptid'];
                    }
                } else {
                    if ($key == 'deptname' && $deptname == $val) {
                        $deptId = $dept['deptid'];
                    }
                }
            }
        }
        return $deptId;
    }

    /**
     *
     * @param $userId
     */
    private function _clearUserCache($address)
    {
        $arr = explode('@', $address);
        $userId = $arr[0];

        $this->_bootstrap->memcache->delete($address . '_info');
        $this->_bootstrap->memcache->delete('TUDU-USER-' . $userId . '@' . $this->_orgId);
    }
}