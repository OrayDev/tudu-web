<?php
/**
 * 超级管理员密码修改控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: PasswordController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Org_PasswordController extends TuduX_Controller_Admin
{
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common'));
        $this->view->LANG   = $this->lang;
    }

	/**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('save'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }

        if (!$this->_user->isOwner()) {
            if (in_array($action, array('save'))) {
                return $this->json(false, '非超级管理员帐户不能进行该操作');
            } else {
                Oray_Function::alert('非超级管理员帐户不能进行该操作');
            }
        }
    }

    /**
     * 超级管理员密码修改页面
     */
    public function indexAction()
    {}

    /**
     * 保存密码
     */
    public function saveAction()
    {
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        // 判读是否为超级管理
        if (!$this->_user->isOwner()) {
            return $this->json(false, '您不是超级管理员');
        }

        $post = $this->_request->getPost();

        $auth = Tudu_Auth::getInstance();
        $adapter = new Tudu_Auth_Adapter_User($this->_multidb->getDefaultDb(), null, null, array(
            'ignorelock' => true,
            'skiplock'   => true
        ));
        $auth->setAdapter($adapter);

        $result = $auth->checkPassword($this->_user->userName, $post['oldpwd']);
        if (!$result->isValid()) {
            return $this->json(false, '当前密码输入错误');
        }

        if (empty($post['pwd'])) {
            return $this->json(false, '新密码不能为空');
        }

        if ($post['pwd'] != $post['repwd']) {
            return $this->json(false, '您输入的新密码与确认密码不一致');
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');
        $ret = $daoUser->updateUserInfo($this->_orgId, $this->_user->userId, array(
            'password' => $post['pwd']
        ));

        if (!$ret) {
            return $this->json(false, '修改密码失败');
        }

        return $this->json(true, '修改密码成功');
    }
}