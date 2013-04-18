<?php
/**
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: LoginController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class LoginController extends TuduX_Controller_Admin
{

    protected $_errMessages = array(
        'invalid email' => '无效的登录用户名',
        'invalid password' => '无效的登录密码',
        'invalid seccode' => '无效的登录验证码',
        'unvalid seccode' => '无效的登录验证码',
        'org forbid' => '本图度服务不可用或已过期',
        'forbid' => '本帐号已被管理员禁用',
        'failure' => '无效的登录密码'
    );

    protected $_passportMessages = array(
        'timeout'   => '验证失败，请返回我的控制台并刷新页面重试',
        'not found' => '图度服务不存在或已被删除',
        'expired'   => '当前图度服务已经过期',
        'org forbid' => '当前图度服务不可用',
        'invalid service' => '无效的图度服务'
    );

    private $_basePath;


    public function init()
    {
        $this->_sessionId = $this->_request->getQuery('sid');
        $this->_basePath  = $this->_request->getBasePath();

        parent::init();
    }

    /**
     * 登录页面
     */
    public function indexAction()
    {
        $err = $this->_request->getQuery('err');

        $isValid = true;
        do {
            if (Zend_Session::isStarted()) {
                if ($this->_user->isAdminLogined()) {
                    return $this->referer($this->_basePath . '/');
                }
            }

            // 没有传入登录的SessionID
            if (empty($this->_sessionId)) {
                $isValid = false;
            }

            if (empty($this->_session->auth) || empty($this->_session->auth['address'])) {
                $isValid = false;
            }
        } while (false);

        if (!$isValid) {
            $url = $this->_request->getCookie('track');
            if (!$url) {
                $url = base64_decode($url);
            }

            if (!$url || !preg_match('/^https?:\/\//', $url)) {
                $url = $this->_options['sites']['tudu'];
            }

            return $this->referer($url . '/?error=admin');
        }

        if ($err && isset($this->_errMessages[$err])) {
            $err = $this->_errMessages[$err];
        }

        $memcache = $this->_bootstrap->memcache;
        $orgInfo  = $memcache->get('TUDU-HOST-' . $this->_session->auth['orgid'] . '.tudu.com');

        if (!$orgInfo) {
            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg  = Oray_Dao::factory('Dao_Md_Org_Org', $this->_bootstrap->getResource('multidb')->getDefaultDb());
            $orgInfo = $daoOrg->getOrgByHost($this->_session->auth['orgid'] . '.tudu.com');
            $flag = null;
            $memcache->set('TUDU-HOST-' . $this->_session->auth['orgid'] . '.tudu.com', $orgInfo, $flag, 3600);
        }

        if ($orgInfo instanceof Dao_Md_Org_Record_Org) {
            $orgInfo = $orgInfo->toArray();
        }

        $this->view->options = array('sites' => $this->_options['sites']);
        $this->view->address = $this->_session->auth['userid'] . '@' . $this->_session->auth['orgid'];
        $this->view->err     = $err;
        $this->view->org     = $orgInfo;

        // 选择登陆模板
        if (!empty($orgInfo) && !empty($orgInfo['loginskin'])) {
            $loginSkin = $orgInfo['loginskin'];
            if (!empty($loginSkin['selected']) && !empty($loginSkin['selected']['value']) && $loginSkin['selected']['value'] != 'SYS:default') {
                $this->view->loginskin = $orgInfo['loginskin'];
                $this->render('custom');
            }
        }
    }
}