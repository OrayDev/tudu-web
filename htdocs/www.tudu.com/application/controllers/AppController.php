<?php
/**
 * APP Controller
 *
 * @version $Id: AppController.php 1957 2012-07-02 06:54:25Z web_op $
 */

class AppController extends TuduX_Controller_Base
{

    const DEFAULT_TS_ID = 1;

    /**
     *
     */
    protected $_allowDestroySession = true;

    /**
     * 当前请求应用
     *
     * @var string
     */
    protected $_currAppName = null;

    /**
     * 挡墙请求应用控制器
     *
     * @var string
     */
    protected $_currAppController = null;

    /**
     * 请求当前应用的操作
     *
     * @var string
     */
    protected $_currAppAction = null;

    /**
     *
     * @var Tudu_Admin_Admin
     */
    protected $_admin;


    public $_neverRender;

    /**
     * 初始化
     *
     *
     * 获取APP请求的文件以及操作
     * 判断APP使用
     * 判断当前用户模块权限
     */
    public function init()
    {
        parent::init();

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        $tsId = self::DEFAULT_TS_ID;

        if ($this->_user) {
            $tsId = $this->_user->tsId;
        }

        // 后台用户验证信息
        /*if ($this->session->admin) {

            $this->_admin   = Tudu_Admin_Admin::getInstance();

            $this->_admin->setAttributes($this->session->admin);

            $this->_orgId   = $this->_admin->orgId;

            $tsId = $this->_admin->tsId;
        }*/

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD  => $this->multidb->getDefaultDb(),
            Tudu_Dao_Manager::DB_TS  => $this->multidb->getDb('ts' . $tsId),
            Tudu_Dao_Manager::DB_APP => $this->multidb->getDb('app')
        ));
    }

    /**
     * do nothing
     */
    public function indexAction()
    {}

    /**
     * 获取App列表
     */
    public function listAction()
    {
        $daoApp = Tudu_Dao_Manager::getDao('Dao_App_App_App', Tudu_Dao_Manager::DB_APP);

        $apps = $daoApp->getApps(array(
            'orgid' => $this->_user->orgId,
            'installed' => true
        ), array('status' => 1, 'activetime' => time()), 'createtime DESC');

        return $this->json(true, null, array('apps' => $apps->toArray()));
    }

    /**
     *
     * @param $appId
     */
    public function getApp($appId)
    {
        $key = $this->_user->orgId . '@' . $appId;

        $app = $this->cache->get($key);

        if (!$app) {
            /* @var $daoApp Dao_Md_App_App */
            $daoApp = Tudu_Dao_Manager::getDao('Dao_Md_App_App', Tudu_Dao_Manager::DB_MD);

            $app = $daoApp->getApp(array('orgid' => $this->_user->orgId, 'appid' => $appId));

            if (null === $app || $app->orgId != $this->_user->orgId) {
                return null;
            }

            $this->cache->set($key, $app, null, 86400);
        }

        return $app;
    }

    /**
     * 格式化app类名
     *
     * @param string $unformatted
     */
    public function formatAppClassName($appName, $controller)
    {
        return 'Apps_' . ucfirst($appName) . '_' . ucfirst($controller);
    }

    /**
     *
     * @param $appName
     * @param $controller
     */
    public function formatTplPath($appName)
    {
        return APPLICATION_PATH . '/apps/' . $appName . '/views';
    }

    /**
     *
     */
    public function getCurrentAppAction($action)
    {
        $path = $this->_request->getPathInfo();

        $appName = str_replace('Action', '', $action);

        // 跳过请求到本控制器Action部分，后面是App请求的路径
        $array = explode('/', $path);
        foreach ($array as $name) {
            array_shift($array);
            if ($name == $appName) {
                break;
            }
        }

        $this->_currAppName = $appName;

        if (count($array) == 0) {
            $this->_currAppController = $appName;
            $this->_currAppAction     = 'index';
        } elseif (count($array) == 1) {
            $this->_currAppController = $appName;
            $this->_currAppAction     = $array[0];
        } else {
            $this->_currAppController = $array[0];
            $this->_currAppAction     = $array[1];
        }
    }

    /**
     * 检查登录身份验证
     *
     * @param string $type user | admin,验证前台用户或后台管理员身份
     */
    public function checkAuth($type = 'user')
    {
        if ($type == 'user') {

        } else {
            if (!$this->session->admin) {
                $this->_destroySession();
                $this->jump();
            }
        }
    }

    /**
     *
     * @param boolean $flag
     */
    public function setNeverRender($flag = true)
    {
        $this->_neverRender = ($flag) ? true : false;
        return $this;
    }

    /**
     *
     */
    public function getNeverRender()
    {
        return $this->_neverRender;
    }

    /**
     *
     *
     * @param $name
     * @param $params
     */
    public function __call($name, $params)
    {
        if (strpos($name, 'Action') != strlen($name) - 6) {
            // 404
        }
/*
        $appInfo = $this->getApp();

        if (null === $appInfo) {
            // 404
        }*/

        $this->getCurrentAppAction($name);

        // 需要后台用户
        if ($this->_currAppAction == 'admin') {
            if (null === $this->_admin || !$this->_admin->isLogin()) {
                $this->_destroySession();
                $this->jump();
            }
        } else {
            if (null === $this->_user || !$this->_user->isLogined()) {
                $this->_destroySession();
                $this->jump();
            }
        }

        $className = $this->formatAppClassName($this->_currAppName, $this->_currAppController);

        require_once APPLICATION_PATH . "/apps/{$this->_currAppName}/controllers/" . ucfirst($this->_currAppController) . '.php';
        $app = new $className($this);

        $app->run($this->_currAppAction, $this->_request, array('user' => $this->_user));

        $app->postRun();

        $response = $app->getResponse();

        if ($app->getResponseFormat() == TuduX_App_Abstract::RESPONSE_FORMAT_HTML) {
            $tplPath = $this->formatTplPath($this->_currAppName);

            $this->view->setScriptPath($tplPath);

            $this->view->data     = $response->getData();
            $this->view->code     = $response->getCode();
            $this->view->message  = $response->getMessage();
            //$this->view->basepath = $this->_request->getBasePath();

            if (null != $this->_user) {
                $this->view->user = $this->_user->toArray();
            }

            if (null != $this->_admin) {
                $this->view->admin = $this->_admin->toArray();
            }

            if ($this->getNeverRender()) {
                $this->getHelper('viewRenderer')->setNeverRender();
            } else {
                $this->renderScript($this->_currAppController . '#' . $this->_currAppAction . '.tpl', null, true);
            }

        } else {

            return $this->json(
                $response->getCode() == TuduX_App_Response::CODE_SUCCESS,
                $response->getMessage(),
                $response->getData()
            );
        }
    }
}