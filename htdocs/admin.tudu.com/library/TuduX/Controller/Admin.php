<?php
/**
 *
 * LICENSE
 *
 *
 * @category   TuduX_Controller
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: Admin.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @category  TuduX_Controller
 * @package   TuduX_Controller
 */
class TuduX_Controller_Admin extends Zend_Controller_Action
{

    /**
     *
     */
    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     *
     * Application Bootstrap
     * @var Zend_Application_Bootstrap_BootstrapAbstract
     */
    protected $_bootstrap;

    /**
     *
     * @var Oray_Application_Resource_Multidb
     */
    protected $_multidb;

    /**
     *
     * @var Oray_Memcache
     */
    protected $_memcache;

    /**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs;

    /**
     *
     * 管理员用户对象
     * @var Tudu_User
     */
    protected $_user;

    /**
     *
     * 配置项
     * @var array
     */
    protected $_options;

    /**
     *
     * @var Zend_Session
     */
    protected $_session;

    /**
     *
     * @var string
     */
    protected $_sessionId;

    /**
     * 当前操作的组织ID
     *
     * @var string
     */
    protected $_orgId;

    /**
     *
     * @var array
     */
    protected $_lang;

    /**
     *
     * @var int
     */
    protected $_timestamp;

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->_bootstrap = $this->getInvokeArg('bootstrap');
        $this->_multidb   = $this->_bootstrap->getResource('multidb');
        $this->_options   = $this->_bootstrap->getOptions();
        $this->_session   = $this->_bootstrap->getResource('session');
        $this->_user      = Tudu_User::getInstance();

        $this->_timestamp = time();

        if (Zend_Session::sessionExists() || $this->_sessionId) {
            if (null !== $this->_sessionId) {
                Zend_Session::setId($this->_sessionId);
            }

            $this->initUser();
        }
    }

    /**
     *
     */
    public function postDispatch()
    {
        $this->view->admin    = $this->_user->toArray();
        $this->view->options  = $this->_options;
        $this->view->basepath = $this->_request->getBasePath();
    }

    /**
     *
     * Enter description here ...
     * @param string $className
     * @param Zend_Db_Adapter_Abstract $db
     * @return Oray_Dao_Abstract
     */
    public function getDao($className, $db = null)
    {
        if (!Zend_Registry::isRegistered($className)) {

            if (null === $db) {
                $db = $this->_multidb->getDefaultDb();
            }

            Zend_Registry::set($className, Oray_Dao::factory($className, $db));
        }

        return Zend_Registry::get($className);
    }

    /**
     *
     * @param $db
     */
    public function getDb($db)
    {
        return $this->_multidb->getDb($db);
    }

    /**
     * 处理Json输出
     *
     * @param boolean $success    操作是否成功
     * @param mixed   $params     附加参数
     * @param mixed   $data       返回数据
     * @param boolean $sendHeader 是否发送json文件头
     */
    public function json($success = false, $params = null, $data = false, $sendHeader = true)
    {
        if (is_string($params) || is_numeric($params)) {
            $params = array('message' => $params);
        }

        $json = array('success' => (boolean) $success);

        if (is_array($params)) {
            unset($params['success']);
            $json = array_merge($json, $params); // 可以让success优化显示
        }

        if (false !== $data) {
            $json['data'] = $data;
        }

        $content = json_encode($json);

        $response = $this->getResponse();
        if ($sendHeader) {
            $response->setHeader('Content-Type', 'application/json');
        }
        $response->setBody($content);
        $response->sendResponse();
        exit;
    }

    /**
     *
     * 初始化Session
     */
    public function initUser()
    {
        if (null === $this->_session) {
            $this->_session = new Zend_Session_Namespace(self::SESSION_NAMESPACE, true);
        }

        // 登陆信息验证
        $names = $this->_options['cookies'];
        if (!isset($this->_session->auth) || !$this->_request->getCookie($names['username'])) {
            $this->destroySession();
            return ;
        }

        if (isset($this->_session->auth['referer'])) {
            $this->_refererUrl = $this->_session->auth['referer'];
        }

        //var_dump($this->_request->getCookie($names['email']));exit();
        if ($this->_session->auth['username'] != $this->_request->getCookie($names['username'])) {
            $this->destroySession();
            return ;
        }

        $this->_session->auth['lasttime'] = $this->_timestamp;

        $this->_user->init($this->_session->auth);
        if (!$this->_user->isLogined()) {
            $this->destroySession();
        }

        if (isset($this->_session->admin)) {
            $this->_user->initAdmin($this->_session->admin);
        }

        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
        }

        $this->org = $this->getOrg($this->_user->orgId);

        $this->_user->setOptions(array(
            'timezone'      => !empty($this->org['timezone']) ? $this->org['timezone'] : 'Etc/GMT-8',
            'dateformat'    => !empty($this->org['dateformat']) ? $this->org['dateformat'] : '%Y-%m-%d %H:%M:%S',
            'passwordlevel' => $this->org['passwordlevel'],
            'skin'          => $this->org['skin']
        ));

        $this->_sessionId = Zend_Session::getId();

        $this->_orgId = $this->_user->orgId;
    }

    /**
     *
     * 注销Session
     */
    public function destroySession()
    {
        $this->_session->admin = null;
    }

    /**
     * 记录CAST更新时间
     */
    public function setUpdateCastTime()
    {
        $this->_bootstrap->memcache->set('TUDU-CAST-UPDATE-' . $this->_orgId, time(), 0);
    }

    /**
     * 获取组织信息
     *
     * @param $orgId
     */
    public function getOrg($orgId)
    {
        $memcache = $this->_bootstrap->memcache;

        $key = 'TUDU-ORG-' . $orgId;
        $org = $memcache->get($key);
        if (!$org) {
            $daoOrg = Oray_Dao::factory('Dao_Md_Org_Org', $this->_multidb->getDb());
            $org = $daoOrg->getOrgById($orgId);
            if ($org) {
                $org = $org->toArray();
                $memcache->set($key, $org);
            }
        }
        return $org;
    }

    /**
     *
     * @return Oray_Httpsqs
     */
    public function getHttpsqs()
    {

    }

    /**
     * 跳转页面，另一种方式
     *
     * @param string $url
     */
    public function referer($url)
    {
        $response = $this->getResponse();
        $this->view->url = $url;
        $this->view->options = $this->_options;
        $this->_request->setModuleName($module = $this->getFrontController()->getDispatcher()->getDefaultModule());
        $this->render('referer', null, true);
        $response->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                 ->setHeader('Pragma', 'no-cache', true)
                 ->sendResponse();
        exit;
    }

    /**
     * 创建管理日志
     *
     * @param string $module
     * @param string $action
     * @param string $subAction
     * @param string $description
     * @return int
     */
    protected function _createLog($module, $action, $subAction = null, $target = null, array $detail = null)
    {
        if (null !== $detail) {
            $detail = serialize($detail);
        }

        $daoLog = $this->getDao('Dao_Md_Log_Oplog');

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();

        $ret = $daoLog->createAdminLog(array(
            'orgid'     => $this->_orgId,
            'userid'    => $this->_user->userId,
            'ip'        => $clientIp,
            'module'    => $module,
            'action'    => $action,
            'subaction' => $subAction,
            'target'    => $target,
            'local'     => !empty($this->_session->auth['local']) ? $this->_session->auth['local'] : null,
            'detail'    => $detail
        ));
    }
}