<?php
/**
 * Index Controller
 *
 * @author Hiro
 * @version $Id: IndexController.php 2721 2013-01-28 02:01:39Z cutecube $
 */

class IndexController extends Zend_Controller_Action
{


    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     * 来源主机头
     *
     * @var string
     */
    private $_host;

    /**
     *
     * @var Zend_Application_Bootstrap_Bootstrap
     */
    public $bootstrap;

    /**
     *
     * @var array
     */
    public $options;

    /**
     *
     * @var Zend_Session
     */
    public $session;

    /**
     *
     * @var Tudu_User
     */
    protected $_user;

    public function init()
    {
        $this->bootstrap = $this->getInvokeArg('bootstrap');
        $this->options = $this->bootstrap->getOptions();
        $this->_host = $this->_request->getServer('HTTP_HOST');

        if (Zend_Session::sessionExists()) {

            if (!$this->session) {
                $singleton = !empty($this->options['unittest']) ? false : true;
                $this->session = new Zend_Session_Namespace(self::SESSION_NAMESPACE, $singleton);
            }
            $this->_sessionId = Zend_Session::getId();

            do {
                // 登陆信息验证
                $names = $this->options['cookies'];

                if (!isset($this->session->auth) || !$this->_request->getCookie($names['username'])) {
                    break;
                }

                //var_dump($this->_request->getCookie($names['email']));exit();
                if ($this->session->auth['username'] != $this->_request->getCookie($names['username'])) {
                    break;
                }

                $this->session->auth['lasttime'] = time();

                $this->_user = Tudu_User::getInstance();

                $this->_user->init($this->session->auth);

            } while (false);
        } else {

            $authId = $this->_request->getCookie($this->options['cookies']['auth']);

            if (!empty($authId)) {
                $referer = PROTOCOL . '//'
                         . $this->_request->getServer('HTTP_HOST')
                         . '/frame';

                return $this->_redirect($this->options['sites']['www'] . '/login/auto?referer=' . urlencode($referer));
            }
        }
    }

    public function indexAction()
    {
        $error = $this->_request->getQuery('error');
        $redirect = $this->_request->getQuery('redirect');
        $lang = Tudu_Lang::getInstance()->load('login');
        $orgInfo = array();

        // 使用SSL登陆
        if ('http:' == PROTOCOL && strpos($this->options['sites']['www'], 'https:') === 0) {
            if (preg_replace('/^https:\/\//', '', $this->options['sites']['www']) == $this->_host) {
                $this->_redirect($this->options['sites']['www'] . $this->_request->getServer('REQUEST_URI'));
            } else {
                $this->_redirect('https://' . $this->_host . $this->_request->getServer('REQUEST_URI'));
            }
        }

        $memcache = $this->getInvokeArg('bootstrap')->getResource('memcache');
        $orgInfo  = $memcache->get('TUDU-HOST-' . $this->_host);

        if (!empty($this->session->auth['appinvoker'])) {
            return ;
        }

        if (!$orgInfo) {
            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg  = Oray_Dao::factory('Dao_Md_Org_Org', $this->bootstrap->getResource('multidb')->getDefaultDb());
            $orgInfo = $daoOrg->getOrgByHost($this->_host);
            $flag = null;
            $memcache->set('TUDU-HOST-' . $this->_host, $orgInfo, $flag, 3600);
        }

        if ($this->_user && $this->_user->isLogined() && $this->_user->orgId == $orgInfo->orgId) {
            return $this->_redirect(PROTOCOL . '//' . $this->_request->getServer('HTTP_HOST') . '/frame');
        }

        if ($orgInfo instanceof Dao_Md_Org_Record_Org) {
            $orgInfo = $orgInfo->toArray();

            if (!empty($this->options['tudu']['customdomain'])) {
                $this->options['sites']['tudu'] = PROTOCOL . '//' . $orgInfo['orgid'] . '.' . $this->options['tudu']['domain'];
            }
        }

        if (in_array($error, array('params', 'failure', 'locked', 'unsupport', 'timeout', 'notexist', 'seccode', 'forbid')) && array_key_exists($error, $lang)) {
            $this->view->error = $error;
        }

        if ($error == 'admin') {
            $this->view->fromadmin = true;
        }

        $this->view->org  = $orgInfo;
        $this->view->lang = $lang;
        $this->view->redirect = $redirect;
        $this->view->options = array('sites' => $this->options['sites'], 'tudu' => $this->options['tudu']);

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

