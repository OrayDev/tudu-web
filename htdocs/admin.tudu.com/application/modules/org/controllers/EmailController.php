<?php
/**
 * 密保邮箱功能控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: EmailController.php 1577 2012-02-16 01:44:33Z cutecube $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Org_EmailController extends TuduX_Controller_Admin
{

    /**
     * 最大重试次数
     *
     * @var int
     */
    const MAX_RETRY = 3;

    /**
     * 验证 key
     *
     * @var string
     */
    const EMAIL_AUTH_KEY = '*+email+*';

    /**
     * 邮箱登陆地址
     *
     * @var array
     */
    protected $_emailLoginUrl = array(
        '163.com' => 'http://mail.163.com',
        '126.com' => 'http://mail.126.com',
        'qq.com'  => 'http://mail.qq.com',
        'hotmail.com' => 'http://www.hotmail.com',
        'gmail.com' => 'http://mail.google.com',
        'sina.com' => 'http://mail.2008.sina.com.cn/',
        'sina.cn' => 'http://mail.sina.com.cn/cnmail/index.html',
        'sohu.com' => 'http://mail.sohu.com/',
        'yahoo.com' => 'http://mail.cn.yahoo.com/',
        '139.com' => 'http://mail.10086.cn/',
        'wo.com.cm' => 'http://mail.wo.com.cn/',
        '189.cn' => 'http://mail.189.cn/webmail/'
    );


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
        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
            $this->referer($this->_request->getBasePath() . '/login/');
        }

        /*$action = strtolower($this->_request->getActionName());
        if (!$this->_user->isOwner()) {
            if (in_array($action, array('save', 'send'))) {
                return $this->json(false, '非超级管理员帐户不能进行该操作');
            } else {
                Oray_Function::alert('非超级管理员帐户不能进行该操作');
            }
        }*/

        if (in_array($this->_orgId, $this->_demoOrg)) {
            if (in_array($action, array('save', 'send'))) {
                return $this->json(false, '体验帐号不能更改后台设置');
            }
        }
    }

    /**
     * 显示当前页面
     */
    public function indexAction()
    {
        $daoEmail = $this->getDao('Dao_Md_User_User');

        $email = $daoEmail->getEmail(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId));

        /*if (null !== $email) {
            $email = $email->toArray();
        }*/

        $this->view->email = $email;
    }

    /**
     * 发送成功页面
     */
    public function sentAction()
    {
        $authId = $this->_request->getQuery('authid');

        if (empty($authId)) {
            return Oray_Function::alert('验证信息错误，发送验证邮件失败');
        }

        /* @var $daoEmailAuth Dao_Reg_Email */
        $daoEmailAuth = Tudu_Dao_Manager::getDao('Dao_Reg_Email', Tudu_Dao_Manager::DB_SITE);

        $auth = $daoEmailAuth->getEmailAuth(array('emailauthid' => $authId));

        if (null === $auth || $auth->orgId != $this->_user->orgId || $auth->userId !== $this->_user->userId
            || $auth->status !== 0 || $auth->expireTime < time())
        {
            return Oray_Function::alert('验证信息错误，发送验证邮件失败');
        }

        list(, $suffix) = explode('@', $auth->email);

        if (isset($this->_emailLoginUrl[$suffix])) {
            $this->view->loginurl = $this->_emailLoginUrl[$suffix];
        }

        $this->view->auth  = $auth->toArray();
    }

    /**
     * 提交保存
     *
     */
    public function saveAction()
    {
        $email = $this->_request->getPost('email');

        $daoEmail = $this->getDao('Dao_Md_User_Email');

        if (!Oray_Function::isEmail($email)) {
            return $this->json(false, '无效的email格式');
        }

        // 密码邮箱是否已被注册/修改
        $daoEmailAuth = Tudu_Dao_Manager::getDao('Dao_Reg_Email', Tudu_Dao_Manager::DB_SITE);
        /* @var $daoEmail Dao_Md_User_Email */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        if ($daoEmailAuth->existsEmail($email, array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId))
            || $daoUser->existsEmail($email))
        {
            return $this->json(false, '该邮箱已被注册');
        }

        $time = time();

        $authId = Dao_Reg_Email::getEmailAuthId($this->_user->orgId, $this->_user->userId);
        $ret = $daoEmailAuth->createEmailAuth(array(
            'emailauthid' => $authId,
            'email'       => $email,
            'orgid'       => $this->_user->orgId,
            'userid'      => $this->_user->userId,
            'createtime'  => $time,
            'expiretime'  => $time + 86400 * 2
        ));

        if (!$ret) {
            return $this->json(false, '验证邮件发送失败，请重试');
        }

        $config  = $this->_options['httpsqs'];

        $key  = md5($this->_user->userId . '@' . $this->_user->orgId . $authId . self::EMAIL_AUTH_KEY);
        $bind = array(
            'url' => $this->_options['sites']['www'] . "/email/?k={$key}&i={$authId}"
        );

        // 通过队列发送邮件
        $httpsqs = new Oray_Httpsqs($this->_options['httpsqs']['host'], $this->_options['httpsqs']['port']);
        $httpsqs->put(implode(' ', array(
            'email',
            $email,
            serialize($bind),
            0
        )), 'reg');

        return $this->json(true, '验证邮件发送成功', array('authid' => $authId));
    }

    /**
     * 发送验证邮件
     *
     */
    public function sendAction()
    {
        $authId = $this->_request->getPost('authid');

        if (empty($authId)) {
            return $this->json(false, '验证信息错误，发送验证邮件失败');
        }

        /* @var $daoEmailAuth Dao_Reg_Email */
        $daoEmailAuth = Tudu_Dao_Manager::getDao('Dao_Reg_Email', Tudu_Dao_Manager::DB_SITE);

        $auth = $daoEmailAuth->getEmailAuth(array('emailauthid' => $authId));

        if (null === $auth || $auth->orgId != $this->_user->orgId || $auth->userId !== $this->_user->userId
            || $auth->status !== 0 || $auth->expireTime < time())
        {
            return $this->json(false, '验证信息错误，发送验证邮件失败');
        }

        if ($auth->retryTimes >= self::MAX_RETRY) {
            return $this->json(false, '验证邮件发送请求已超过上限');
        }

        $config  = $this->_options['httpsqs'];
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['charset'], $config['name']);

        $key  = md5($this->_user->userId . '@' . $this->_user->orgId . $authId . self::EMAIL_AUTH_KEY);
        $bind = array(
            'url' => $this->_options['sites']['www'] . "/email/?k={$key}&i={$authId}"
        );
        $httpsqs->put(implode(' ', array(
            'email',
            $auth->email,
            serialize($bind),
            0
        )), 'reg');

        $daoEmailAuth->updateEmailAuth($authId, array('retrytimes' => $auth->retryTimes + 1));

        return $this->json(true, '验证邮件已发送', array('authid' => $authId));
    }

    /**
     * 验证url并执行修改
     */
    public function authAction()
    {
        $daoEmailAuth = Tudu_Dao_Manager::get('Dao_Reg_Email', Tudu_Dao_Manager::DB_SITE);
    }
}