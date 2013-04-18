<?php
/**
 * 组织架构管理控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: DepartmentController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class User_DepartmentController extends TuduX_Controller_Admin
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
            if (in_array($action, array('create', 'update', 'delete', 'member', 'moderator', 'sort'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 输出部门列表
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        /* @var @daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_orgId))->toArray();

        $this->view->depts = $depts;
        $this->view->org   = $org->toArray();
    }

    /**
     *创建部门
     */
    public function createAction()
    {
        $name     = $this->_request->getPost('name');
        $parentId = trim($this->_request->getPost('parentid'));
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptname' => $name,
            'parentid' => $parentId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->execute('create', array(&$params));
        } catch (Model_Department_Exception $e) {
            $message = $this->lang['department_create_failure'];
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_INVALID_DEPTNAME:
                    $message = $this->_lang['invalid_param_deptname'];
                    break;
                case Model_Department_Department::CODE_PARENT_NOTEXISTS:
                    $message = $this->lang['parent_department_not_exists'];
                    break;
                case Model_Department_Department::CODE_DEPARTMENT_NOTEXISTS:
                    $message = $this->lang['department_not_exists'];
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = $this->lang['department_create_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->setUpdateCastTime();

        /* @var $daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $departments   = $daoDepartment->getDepartments(array('orgid' => $this->_orgId), null, 'ordernum DESC');

        $this->json(true, $this->lang['department_create_success'], array('deptid' => $params['deptid'], 'depts' => $departments->toArray()));
    }

    /**
     * 更新部门
     */
    public function updateAction()
    {
        $deptId   = $this->_request->getPost('deptid');
        $name     = $this->_request->getPost('name');
        $parentId = trim(str_replace('_', '^', $this->_request->getPost('parentid')));
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        if (!$name) {
            return $this->json(false, $this->lang['invalid_param_deptname']);
        }

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptid'   => $deptId,
            'deptname' => $name,
            'parentid' => $parentId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->doUpdate($params);
        } catch (Model_Department_Exception $e) {
            $message = $this->lang['department_update_failure'];
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_DEPTID:
                    $message = '缺少参数[deptid]';
                    break;
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_INVALID_PARENT:
                    $message = $this->lang['parent_cannot_be_self'];
                    break;
                case Model_Department_Department::CODE_PARENT_NOTEXISTS:
                    $message = $this->lang['parent_department_not_exists'];
                    break;
                case Model_Department_Department::CODE_DEPARTMENT_NOTEXISTS:
                    $message = $this->lang['department_not_exists'];
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = $this->lang['department_update_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->setUpdateCastTime();

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');
        $users  = $daoUser->getUsers(array('orgid' => $this->_orgId, 'deptid' => $deptId))->toArray();

        // 清除影响用户IM缓存
        foreach ($users as $user) {
            $this->_clearUserCache($user['username']);
        }

        /* @var $daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $departments   = $daoDepartment->getDepartments(array('orgid' => $this->_orgId), null, 'ordernum DESC');

        $this->json(true, $this->lang['department_update_success'], array('deptid' => $deptId, 'depts' => $departments->toArray()));
    }

    /**
     * 删除部门
     */
    public function deleteAction()
    {
        $deptId   = $this->_request->getPost('deptid');
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptid'   => $deptId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->doDelete($params);
        } catch (Model_Department_Exception $e) {
            $message = $this->lang['department_delete_failure'];
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_DEPTID:
                    $message = '缺少参数[deptid]';
                    break;
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_DELETE_NOTNULL:
                    $message = $this->lang['department_has_user'];
                    break;
                case Model_Department_Department::CODE_DELETE_PARENT:
                    $message = $this->lang['department_has_child'];
                    break;
                case Model_Department_Department::CODE_DEPARTMENT_NOTEXISTS:
                    $message = $this->lang['department_not_exists'];
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = $this->lang['department_delete_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        $this->setUpdateCastTime();

        /* @var @daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');
        $departments   = $daoDepartment->getDepartments(array('orgid' => $this->_orgId), null, 'ordernum DESC');

        return $this->json(true, $this->lang['department_delete_success'], $departments->toArray());
    }

    /**
     * 部门成员
     */
    public function memberAction()
    {
        $deptId   = $this->_request->get('deptid');
        $userId   = explode(',', $this->_request->get('userid'));
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptid'   => $deptId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->addAction('update', array($modelDept, 'updateMember'), 1, array($this->_orgId, $deptId, $userId, $params), false);
            $modelDept->execute('update', array($params));
        } catch (Model_Department_Exception $e) {
            $message = $this->lang['department_update_user_failure'];
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_DEPTID:
                    $message = '缺少参数[deptid]';
                    break;
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_DEPARTMENT_NOTEXISTS:
                    $message = $this->lang['department_not_exists'];
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = $this->lang['department_update_user_failure'];
                    break;
            }

            return $this->json(false, $message);
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');
        $users   = $daoUser->getUsers(array('orgid' => $this->_orgId, 'deptid' => $deptId))->toArray('userid');

        foreach($users as $user) {
            $this->_clearUserCache($user['username']);
        }

        foreach ($userId as $id) {
            if (!isset($users[$id])) {
                $this->_clearUserCache($id . '@' . $this->_orgId);
            }
        }

        $this->setUpdateCastTime();

        return $this->json(true, $this->lang['department_update_user_success'], $userId);
    }

    /**
     * 负责人
     */
    public function moderatorAction()
    {
        $userIds  = explode(',', $this->_request->getPost('userid'));
        $deptId   = str_replace('_', '^', $this->_request->getPost('deptid'));
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptid'   => $deptId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->addAction('update', array($modelDept, 'updateModerator'), 1, array($this->_orgId, $deptId, $userIds, $params), false);
            $modelDept->execute('update', array($params));
        } catch (Model_Department_Exception $e) {
            $message = '设置部门负责人失败';
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_DEPTID:
                    $message = '缺少参数[deptid]';
                    break;
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_DEPARTMENT_NOTEXISTS:
                    $message = $this->lang['department_not_exists'];
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = '设置部门负责人失败';
                    break;
            }

            return $this->json(false, $message);
        }

        /* @var @daoDepartment Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');

        $departments = $daoDepartment->getDepartments(array('orgid' => $this->_orgId), null, 'ordernum DESC');

        return $this->json(true, '设置部门负责人成功', $departments->toArray());
    }

    /**
     * 排序
     */
    public function sortAction()
    {
        $deptId   = str_replace('_', '^', $this->_request->getPost('deptid'));
        $type     = $this->_request->getPost('type');
        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
        $local    = !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null;

        /* @var $modelDept Model_Department_Department */
        $modelDept = Tudu_Model::factory('Model_Department_Department');

        $params = array(
            'orgid'    => $this->_orgId,
            'deptid'   => $deptId,
            'operator' => $this->_user->userId,
            'clientip' => $clientIp,
            'local'    => $local
        );

        try {
            $modelDept->addAction('update', array($modelDept, 'sort'), 1, array($this->_orgId, $deptId, $type, $params), false);
            $modelDept->execute('update', array($params));
        } catch (Model_Department_Exception $e) {
            $message = '排序失败，请刷新页面后重试';
            switch ($e->getCode()) {
                case Model_Department_Department::CODE_INVALID_DEPTID:
                    $message = '缺少参数[deptid]';
                    break;
                case Model_Department_Department::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Department_Department::CODE_SAVE_FAILED:
                    $message = '排序失败，请刷新页面后重试';
                    break;
            }

            return $this->json(false, $message);
        }

        return $this->json(true);
    }

    /**
     * 获取部门成员
     */
    public function getMemberAction()
    {
        $deptId = $this->_request->get('deptid');
        /* @var @daoOrg Dao_Md_Department_Department */
        $daoDepartment = $this->getDao('Dao_Md_Department_Department');

        $ret = $daoDepartment->getUserIds($this->_orgId, $deptId);

        return $this->json(true, null, array('userid' => $ret));

    }

    /**
     *
     * @param $userId
     */
    private function _clearUserCache($address)
    {
        $arr = explode('@', $address);
        $userId = $arr[0];

        $memcache = $this->_bootstrap->memcache;

        $memcache->delete($address . '_info');
        $memcache->delete('im_' . $this->_orgId . '_' . $userId . '_depts');
        $memcache->delete('im_' . $this->_orgId . '_' . $userId . '_roster');
    }

}