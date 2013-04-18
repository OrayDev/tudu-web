<?php
/**
 * 群组管理控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: GroupController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class User_GroupController extends TuduX_Controller_Admin
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
            if (in_array($action, array('create', 'update', 'delete', 'update.member', 'add.member', 'sort'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 输出组织架构信息
     */
    public function indexAction()
    {
        /* @var @daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');

        $groups = $daoGroup->getGroups(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));

        $this->view->groups = $groups->toArray();
    }

    /**
     * 创建群组
     */
    public function createAction()
    {
        $groupName = trim($this->_request->getPost('groupname'));

        $userIds = null;
        if (null != $this->_request->getPost('userid')) {
            $userIds = explode(',', trim($this->_request->getPost('userid')));
        }

        if (!$groupName) {
            return $this->json(false, $this->lang['invalid_params_groupname']);
        }

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');

        try {
            $groupId = Dao_Md_User_Group::getGroupId($this->_orgId, $groupName);

            // 添加用户操作
            if (!empty($userIds)) {
                $modelGroup->addAction('create', array($modelGroup, 'addMembers'), 1, array($this->_orgId, $groupId, $userIds), false);
            }

            $modelGroup->doCreate(array(
                'orgid'     => $this->_orgId,
                'groupid'   => $groupId,
                'groupname' => $groupName
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPNAME:
                    $message = $this->lang['invalid_params_groupname'];
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = $this->lang['group_create_failure'];
                    break ;
            }

            return $this->json(false, $message);
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg  = $this->getDao('Dao_Md_Org_Org');

        $message = $this->lang['group_create_success'];
        if (isset($userIds)) {
            foreach ($userIds as $userId) {
                $this->_clearUserCache($userId . '@' . $this->_orgId);
            }

            //$this->_createLog('group', 'create', null, $groupId, array('groupname' => $groupName));

            //return $this->json(true, '已成功创建并添加到新群组', $groupId);

            $message = '已成功创建并添加到新群组';
        }

        $this->_createLog('group', 'create', null, $groupId, array('groupname' => $groupName));

        $this->setUpdateCastTime();

        return $this->json(true, $message, $groupId);
    }

    /**
     * 更新群组
     */
    public function updateAction()
    {
        $groupId = $this->_request->getPost('groupid');
        $groupName = trim($this->_request->getPost('groupname'));

        if (!$groupId) {
            return $this->json(false, $this->lang['invalid_params_groupid']);
        }

        if (!$groupName) {
            return $this->json(false, $this->lang['invalid_params_groupname']);
        }

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');

        try {
            $modelGroup->doUpdate(array(
                'orgid'     => $this->_orgId,
                'groupid'   => $groupId,
                'groupname' => $groupName
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPID:
                    $message = $this->lang['invalid_params_groupid'];
                    break ;
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = '该群组不存在或已被删除';
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                    $message = $this->lang['group_update_failure'];
                    break ;
            }

            return $this->json(false, $message);
        }

        $this->_createLog('group', 'update', null, $groupId, array('groupname' => $groupName));

        $this->setUpdateCastTime();

        return $this->json(true, $this->lang['group_update_success'], $groupId);
    }

    /**
     * 删除群组
     */
    public function deleteAction()
    {
        $groupId = $this->_request->getPost('groupid');

        if (!$groupId) {
            return $this->json(false, $this->lang['invalid_params_groupid']);
        }

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $group    = $daoGroup->getGroup(array('orgid' => $this->_orgId, 'groupid' => $groupId));

        if (null === $group) {
            return $this->json(false, $this->lang['group_not_exists']);
        }

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');

        try {
            $modelGroup->doDelete(array(
                'orgid'   => $this->_orgId,
                'groupid' => $groupId,
                'isverify'=> true //已验证群组是否存在
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPID:
                    $message = $this->lang['invalid_params_groupid'];
                    break ;
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = $this->lang['group_not_exists'];
                    break ;
                case Model_User_Group::CODE_GROUP_USER_NOT_NULL:
                    $message = $this->lang['group_is_not_null'];
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                    $message = $this->lang['group_delete_failure'];
                    break ;
            }

            return $this->json(false, $message);
        }

        $this->_createLog('group', 'delete', null, $groupId, array('groupname' => $group->groupName));

        $this->setUpdateCastTime();

        return $this->json(true, $this->lang['group_delete_success']);
    }

    /**
     * 排序
     */
    public function sortAction()
    {
        $groupId = $this->_request->getPost('groupid');
        $sort    = $this->_request->getPost('sort');
        $message = '';

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');

        try {
            $modelGroup->execute('sort', array(array(
                'orgid'   => $this->_orgId,
                'groupid' => $groupId,
                'sort'    => $sort
            )));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPID:
                    $message = $this->lang['invalid_params_groupid'];
                    break ;
                case Model_User_Group::CODE_INVALID_SORTTYPE:
                    $message = '缺少参数[sort]';
                    break ;
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = '该群组不存在或已被删除';
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                    $message = '排序失败，请刷新后重试';
                    break ;
            }

            return $this->json(false, $message);
        }

        return $this->json(true, null);
    }

    /**
     * 更新群组用户
     */
    public function updateMemberAction()
    {
        /* @var @daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg  = $this->getDao('Dao_Md_Org_Org');
        /* @var $daoUser Dao_Md_Org_Org */
        $daoUser = $this->getDao('Dao_Md_User_User');

        $groupId = $this->_request->getPost('groupid');
        $userIds = (array) $this->_request->getPost('userid');

        if (!$groupId) {
            return $this->json(false, $this->lang['invalid_params_groupid']);
        }

        $group = $daoGroup->getGroup(array('orgid' => $this->_orgId, 'groupid' => $groupId));
        if (null === $group) {
            return $this->json(false, $this->lang['group_not_exists']);
        }

        $users = $daoUser->getUsers(array('orgid' => $this->_orgId, 'groupid' => $groupId))->toArray('userid');

        foreach($users as $user) {
            $this->_clearUserCache($user['address']);
        }

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');
        $message    = '';

        try {
            $modelGroup->execute('updateMember', array(array(
                'orgid'   => $this->_orgId,
                'groupid' => $groupId,
                'users'   => $userIds,
                'isverify'=> true //已验证群组是否存在
            )));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPID:
                    $message = $this->lang['invalid_params_groupid'];
                    break ;
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = '该群组不存在或已被删除';
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                    $message = $this->lang['group_member_failure'];
                    break ;
            }

            return $this->json(false, $message);
        }

        foreach ($userIds as $userId) {
            if (!$userId) {
                continue;
            }

            if (!isset($userId, $users)) {
                $this->_clearUserCache($userId . '@' . $this->_orgId);
            }
        }

        $this->_createLog('group', 'update', 'member', $groupId, array('groupname' => $group->groupName));

        $this->setUpdateCastTime();

        return $this->json(true, $this->lang['group_member_success']);
    }

    /**
     * 获取成员
     */
    public function getMemberAction()
    {
        /* @var @daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');

        $groupId = $this->_request->getParam('groupid');

        $ret = $daoGroup->getUserIds($this->_orgId, $groupId);

        return $this->json(true, null, array('userid' => $ret));
    }

    /**
     * 向群组添加成员
     */
    public function addMemberAction()
    {
        /* @var @daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg  = $this->getDao('Dao_Md_Org_Org');

        $groupId = $this->_request->getParam('groupid');
        $key = (array) $this->_request->getParam('key');

        $message = '';

        /* @var $modelGroup Model_User_Group*/
        $modelGroup = Tudu_Model::factory('Model_User_Group');

        try {
            $modelGroup->doAddUser(array(
                'orgid'   => $this->_orgId,
                'groupid' => $groupId,
                'userid'  => $key
            ));
        } catch (Model_User_Exception $e) {
            switch ($e->getCode()) {
                case Model_User_Group::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break ;
                case Model_User_Group::CODE_INVALID_GROUPID:
                    $message = $this->lang['invalid_params_groupid'];
                    break ;
                case Model_User_Group::CODE_INVALID_UID:
                    $message = '缺少群组成员';
                    break ;
                case Model_User_Group::CODE_GROUP_NOTEXISTS:
                    $message = '该群组不存在或已被删除';
                    break ;
                case Model_User_Group::CODE_SAVE_FAILED:
                    $message = $this->lang['group_member_failure'];
                    break ;
            }

            return $this->json(false, $message);
        }

        foreach ($key as $userId) {
            if (!$userId) {
                continue;
            }

            $this->_clearUserCache($userId . '@' . $this->_orgId);
        }

        $this->setUpdateCastTime();
        return $this->json(true, $this->lang['operate_success']);
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
        $this->_bootstrap->memcache->delete('im_' . $this->_orgId . '_' . $userId . '_roster');
    }
}