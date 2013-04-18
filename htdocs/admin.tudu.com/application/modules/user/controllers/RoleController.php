<?php
/**
 * 权限管理控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: RoleController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class User_RoleController extends TuduX_Controller_Admin
{
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'user'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());

        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('create', 'update', 'delete', 'save.access', 'update.member'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 输出组织权限信息
     */
    public function indexAction()
    {
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');

        $roles = $daoRole->getRoles(array(
            'orgid' => $this->_orgId
        ), null, array('issystem' => 'DESC'));

        $this->view->roles = $roles->toArray();
    }

    /**
     * 创建权限组
     */
    public function createAction()
    {
        $roleName = trim($this->_request->getPost('rolename'));

        if (!$roleName) {
            return $this->json(false, $this->lang['invalid_params_rolename']);
        }

        /* @var $modelRole Model_User_Role*/
        $modelRole = Tudu_Model::factory('Model_User_Role');

        try {
            $roleId = Dao_Md_User_Role::getRoleId($this->_orgId, $roleName);
            $modelRole->execute('create', array(array(
                'orgid'    => $this->_orgId,
                'roleid'   => $roleId,
                'rolename' => $roleName
            )));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Role::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_Role::CODE_INVALID_ROLENAME:
                    $message = $this->lang['invalid_params_rolename'];
                    break;
                case Model_User_Role::CODE_SAVE_FAILED:
                    $message = $this->lang['role_create_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->_createLog('role', 'create', null, $roleId, array('rolename' => $roleName));

        $this->json(true, $this->lang['role_create_success'], array('roleid' => $roleId, 'rolename' => $roleName));

    }

    /**
     *  更新权限组
     */
    public function updateAction()
    {
        $roleId = $this->_request->getPost('roleid');
        $roleName = trim($this->_request->getPost('rolename'));

        if (!$roleId) {
            return $this->json(false, $this->lang['invalid_params_roleid']);
        }

        if (!$roleName) {
            return $this->json(false, $this->lang['invalid_params_rolename']);
        }

        /* @var $modelRole Model_User_Role*/
        $modelRole = Tudu_Model::factory('Model_User_Role');

        try {
            $modelRole->execute('update', array(array(
                'orgid'    => $this->_orgId,
                'roleid'   => $roleId,
                'rolename' => $roleName
            )));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Role::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_Role::CODE_INVALID_ROLEID:
                    $message = '缺少参数[roleid]';
                    break;
                case Model_User_Role::CODE_ROLE_NOTEXISTS:
                    $message = $this->lang['role_not_exists'];
                    break;
                case Model_User_Role::CODE_SAVE_FAILED:
                    $message = $this->lang['role_create_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->_createLog('role', 'update', null, $roleId, array('rolename' => $roleName));

        return $this->json(true, $this->lang['role_update_success'], array('roleid' => $roleId));
    }

    /**
     * 删除权限组
     */
    public function deleteAction()
    {
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');

        $roleId = $this->_request->getPost('roleid');

        if (!$roleId) {
            return $this->json(false, $this->lang['invalid_params_roleid']);
        }

        $role = $daoRole->getRole(array('orgid' => $this->_orgId, 'roleid' => $roleId));
        if (null === $role) {
            return $this->json(false, $this->lang['role_not_exists']);
        }

        /* @var $modelRole Model_User_Role*/
        $modelRole = Tudu_Model::factory('Model_User_Role');

        try {
            $modelRole->doDelete(array(
                'orgid'    => $this->_orgId,
                'roleid'   => $roleId,
                'isverify' => true //已验证权限组是否存在
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Role::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_Role::CODE_INVALID_ROLEID:
                    $message = '缺少参数[roleid]';
                    break;
                case Model_User_Role::CODE_ROLE_NOTEXISTS:
                    $message = $this->lang['role_not_exists'];
                    break;
                case Model_User_Role::CODE_ROLE_USER_NOT_NULL:
                    $message = $this->lang['role_is_not_null'];
                    break;
                case Model_User_Role::CODE_SAVE_FAILED:
                    $message = $this->lang['role_delete_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->_createLog('role', 'delete', null, $roleId, array('rolename' => $role->roleName));

        return $this->json(true, $this->lang['role_delete_success']);
    }

    /**
     * 编辑权限组的用户权限
     * 页面显示
     */
    public function modifyAccessAction()
    {
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');

        $roleId = $this->_request->getQuery('roleid');

        if (!$roleId) {
            Oray_Function::alert($this->lang['invalid_params_roleid']);
        }

        if (!$daoRole->existsRole($this->_orgId, $roleId)) {
            Oray_Function::alert($this->lang['role_not_exists']);
        }

        $role = $daoRole->getRole(array(
            'orgid' => $this->_orgId,
            'roleid' => $roleId
        ));

        $accesses = $daoRole->getAccesses($this->_orgId, $roleId);

        if (!count($accesses)) {
            $accesses = $daoRole->getAccesses($this->_orgId, '^user');
        }

        $this->view->role = $role->toArray();
        $this->view->accesses = $accesses;
    }

    /**
     * 保存权限组的用户权限
     */
    public function saveAccessAction()
    {
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        /* @var @daoAccess Dao_Md_Access_Access */
        $daoAccess = $this->getDao('Dao_Md_Access_Access');

        $roleId = $this->_request->getPost('roleid');
        $post   = $this->_request->getPost();

        if (!$roleId) {
            return $this->json(false, $this->lang['invalid_params_roleid']);
        }

        $role = $daoRole->getRole(array(
            'orgid' => $this->_orgId,
            'roleid' => $roleId
        ));

        if (!$role) {
            return $this->json(false, $this->lang['role_not_exists']);
        }

        $accesses = $daoAccess->getAccesses(array('all' => true))->toArray();
        $roleValue = array();
        foreach ($accesses as $row) {
            $val = isset($post['access-' . $row['accessid']]) ? $post['access-' . $row['accessid']] : $row['defaultvalue'];
            $roleValue[] = array(
                'accessid' => $row['accessid'],
                'value'    => $val
            );
        }

        /* @var $modelRole Model_User_Role*/
        $modelRole = Tudu_Model::factory('Model_User_Role');

        try {
            $modelRole->doUpdateAccess(array(
                'orgid'    => $this->_orgId,
                'roleid'   => $roleId,
                'access'   => $roleValue,
                'isverify' => true //已验证权限组是否存在
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Role::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_Role::CODE_INVALID_ROLEID:
                    $message = '缺少参数[roleid]';
                    break;
                case Model_User_Role::CODE_SAVE_FAILED:
                    $message = sprintf($this->lang['role_update_access_failure'], $role->roleName);
                    break;
            }

            return $this->json(false, $message);
        }

        $userIds = $daoRole->getUserIds($this->_orgId, $roleId);

        $this->_clearCache($userIds);

        $this->_createLog('role', 'update', 'access', $roleId, array('rolename' => $role->roleName));

        return $this->json(true, sprintf($this->lang['role_update_access_success'], $role->roleName));
    }

    /**
     * 更新权限组成员
     */
    public function updateMemberAction()
    {
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg  = $this->getDao('Dao_Md_Org_Org');

        $roleId = $this->_request->getPost('roleid');
        $members = (array) $this->_request->getPost('userid');

        if (!$roleId) {
            return $this->json(false, $this->lang['invalid_params_roleid']);
        }

        $role = $daoRole->getRole(array(
            'orgid' => $this->_orgId,
            'roleid' => $roleId
        ));
        if(null === $role) {
            return $this->json(false, $this->lang['role_not_exists']);
        }

        $users = $daoRole->getUserIds($this->_orgId, $roleId);

        /* @var $modelRole Model_User_Role*/
        $modelRole = Tudu_Model::factory('Model_User_Role');

        try {
            $modelRole->doUpdateMember(array(
                'orgid'    => $this->_orgId,
                'roleid'   => $roleId,
                'users'    => $members,
                'isverify' => true //已验证权限组是否存在
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Role::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_User_Role::CODE_INVALID_ROLEID:
                    $message = '缺少参数[roleid]';
                    break;
                case Model_User_Role::CODE_ROLE_NOTEXISTS:
                    $message = $this->lang['role_not_exists'];
                    break;
                case Model_User_Role::CODE_SAVE_FAILED:
                    $message = '更新权限组成员失败';
                    break;
            }

            return $this->json(false, $message);
        }

        $userIds = array_unique(array_merge($users, $members));
        $this->_clearCache($userIds);

        //if ($roleId == '^admin') {

            //$removeAdmin = array_diff($users, $members);

            /*foreach ($removeAdmin as $userId) {
                $daoOrg->deleteAdmin($this->_orgId, $userId);
            }*/

            /*foreach ($members as $userId) {
                $daoOrg->addAdmin($this->_orgId, $userId, 'SA', 3);
            }*/
        //}

        $this->_createLog('role', 'update', 'member', $roleId, array('rolename' => $role->roleName));

        return $this->json(true, '更新权限组成员成功');
    }

    /**
     * 获取成员
     */
    public function getMemberAction()
    {
        /* @var @daoRole Dao_Md_User_Role */
        $daoRole = $this->getDao('Dao_Md_User_Role');

        $roleId = $this->_request->getParam('roleid');

        $ret = $daoRole->getUserIds($this->_orgId, $roleId);

        return $this->json(true, null, array('userid' => $ret));
    }

    /**
     *
     * @param $userIds
     */
    private function _clearCache(array $userIds)
    {
        if (!$userIds) {
            return ;
        }

        $userIds = array_unique($userIds);

        $memcache = $this->_bootstrap->getResource('memcache');

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $domains = $daoOrg->getDomains($this->_orgId);
        $domain  = $domains[0]['domainname'];

        foreach ($userIds as $userId) {
            $key = 'TUDU-ACCESS-' . $userId . '@' . $this->_orgId;

            $memcache->delete($userId . '@' . $domain . '_info');
            $memcache->delete($key);
        }
    }
}