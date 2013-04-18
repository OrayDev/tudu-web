<?php
/**
 * TuduX Library
 * 外部人员访问图度任务模块控制器基类
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Action
 */
require_once 'Zend/Controller/Action.php';

/**
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class TuduX_Controller_Foreign extends Zend_Controller_Action
{

    /**
     *
     * @var string
     */
    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     *
     * @var string
     */
    protected $_tsId;

    /**
     *
     * @var unknown_type
     */
    protected $_multidb;

    /**
     *
     * @var unknown_type
     */
    protected $_bootstrap;

    /**
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     *
     * @var string
     */
    protected $_sessionId;

    /**
     *
     * @var Dao_Td_Tudu_Record_Tudu
     */
    protected $_tudu;

    /**
     *
     * @var Tudu_Deliver
     */
    protected $_deliver;

    /**
     *
     * @var Tudu_Tudu_Manager
     */
    protected $_manager;

    /**
     *
     * @var array
     */
    protected $_lang;

    /**
     *
     * @var array
     */
    protected $_options;

    /**
     *
     * @var array
     */
    protected $_user;

    /**
     *
     * @var int
     */
    protected $_timestamp;

    /**
     * 初始化
     */
    public function init()
    {
        $this->_bootstrap = $this->getInvokeArg('bootstrap');
        $this->_multidb   = $this->_bootstrap->getResource('multidb');
        $this->_options   = $this->_bootstrap->getOptions();

        $this->_helper->viewRenderer->view->setBasePath(APPLICATION_PATH . '/modules/foreign/views');
        $this->_helper->viewRenderer->setViewScriptPathSpec(':module#:controller#:action.:suffix');

        $this->_tsId = $this->_request->getParam('ts');
        $tuduId = $this->_request->getParam('tid');
        $unId   = $this->_request->getParam('fid');

        if (!$this->_tsId || !$tuduId || !$unId) {
            $this->getResponse()->setHttpResponseCode(404);
            $this->getResponse()->sendResponse();
            return ;
        }

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_TS => $this->getTsDb($this->_tsId)
        ));

        $this->_manager = Tudu_Tudu_Manager::getInstance();

        $this->_deliver = new Tudu_Deliver($this->getTsDb($this->_tsId));

        $this->_tudu = $this->_manager->getTuduById($tuduId, $unId);
        $this->_user = $this->_manager->getUser($tuduId, $unId);

        if (null !== $this->_user) {
            // 用户请求语言
            $language = $this->_request->getHeader('ACCEPT_LANGUAGE');
            if (strpos($language, 'zh') !== false) {
                if (strpos($language, 'hk') !== false || strpos($language, 'tw') !== false) {
                    $language = 'zh_TW';
                } else {
                    $language = 'zh_CN';
                }
            } else {
                $language = 'en_US';
            }

            $this->_user['option'] = array(
                'language' => $language
            );

            if (null !== $this->_tudu) {
                $this->_session    = new Zend_Session_Namespace(self::SESSION_NAMESPACE, true);
                $this->_sessionId  = Zend_Session::getId();

                //
                /*if (isset($this->_session->foreign['uniqueid']) && $this->_session->foreign['uniqueid'] != $this->_user['uniqueid']) {
                    $this->_destroySession();
                }*/

                $this->_session->foreign['uniqueid']  = $this->_user['uniqueid'];
                $this->_session->foreign['address']   = $this->_user['email'] ? $this->_user['email'] : $this->_user['uniqueid'];
                $this->_session->foreign['truename']  = $this->_user['truename'];
                $this->_session->foreign['logintime'] = time();
                $this->_session->foreign['orgid']     = $this->_tudu->orgId;
                $this->_session->foreign['tsid']      = $this->_tsId;

                $this->_session->foreign['lasttime'] = time();

                if (empty($this->_session->auth)) {
                    $this->_session->auth = array(
                        'uniqueid'  => $this->_user['uniqueid'],
                        'address'   => $this->_session->foreign['address'],
                        'logintime' => $this->_session->foreign['logintime']
                    );
                }
            }

            $this->_timestamp = time();

            $this->view->options = $this->_options;
            $this->view->tsid    = $this->_tsId;
            $this->view->user    = $this->_user;
        }
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
        $response->setBody($content);
        $response->sendResponse();
        exit;
    }

    /**
     * 跳转页面
     *
     * 默认跳转到登陆页面
     *
     * @param string $url
     */
    public function jump($url = null, array $params = array())
    {
        $response = $this->getResponse();

        if (null === $url) {
            $url = $this->getLoginUrl();
            $url .= '?redirect=%referer';
            $this->view->referer = true;
        }

        $this->view->url = $url
                         . (!empty($params) ? (false === strpos($url, '?') ? '?' : '&') . http_build_query($params) : '');

        $this->render('foreign_jump', null, true);

        $response->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                 ->setHeader('Pragma', 'no-cache', true)
                 ->sendResponse();
        exit;
    }

    /**
     * 当前访问是否有效
     */
    protected function _isValid()
    {
        if (($this->_tudu->password || $this->_tudu->authCode) && !isset($this->_session->foreign)) {
            return false;
        }

        if ($this->_tudu->password) {
            if (!isset($this->_session->foreign[$this->_tudu->tuduId]['password'])
                || $this->_session->foreign[$this->_tudu->tuduId]['password'] != $this->_tudu->password)
            {
                return false;
            }
        }

        if ($this->_tudu->authCode)
        {
            if (!isset($this->_session->foreign[$this->_tudu->tuduId]['authcode'])
                || $this->_session->foreign[$this->_tudu->tuduId]['authcode'] != $this->_tudu->authCode)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * 写入操作日志
     *
     * @param string  $targetType 操作对象类型
     * @param string  $targetId 对象ID
     * @param string  $action 操作
     * @param array   $detail 修改内容
     * @param boolean $privacy
     * @return boolean
     */
    protected function _writeLog($targetType, $targetId, $action, array $detail = null, $privacy = false)
    {
        if (null !== $detail) {
            $detail = serialize($detail);
        }

        $daoLog = $this->getDao('Dao_Td_Log_Log');
        return $daoLog->createLog(array(
            'orgid' => $this->_tudu->orgId,
            'uniqueid' => $this->_user['uniqueid'],
            'operator' => $this->_user['email'] . ' ' . $this->_user['truename'],
            'logtime'  => time(),
            'targettype' => $targetType,
            'targetid' => $targetId,
            'action' => $action,
            'detail' => $detail,
            'privacy' => $privacy ? 1 : 0
        ));
    }

    /**
     * 设置Cookies
     *
     * 315554400 = strtotime('1980-01-01'),
     *
     * @param array $cookies
     * @param int $lifetime
     */
    protected function _setCookies(array $cookies, $lifetime = 315554400)
    {
        $cookieParams = session_get_cookie_params();
        if (null === $lifetime) {
            $lifetime = $cookieParams['lifetime'];
        }
        foreach ($cookies as $key => $value) {
            setcookie(
                $key,
                $value,
                $lifetime,
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure']
                );
        }
    }

    /**
     * 摧毁Session
     */
    protected function _destroySession()
    {
        $names = $this->_options['cookies'];
        Zend_Session::destroy();
        $this->_setCookies(array(
            $names['email'] => false
            ));
    }

    /**
     *
     * @param $className
     * @param $tsId
     */
    public function getDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->getTsDb($this->_tsId)));
        }
        return Zend_Registry::get($className);
    }

	/**
     * Get dao
     *
     * @param string $className
     * @return Oray_Dao_Abstract
     */
    public function getMdDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->_multidb->getDefaultDb()));
        }
        return Zend_Registry::get($className);
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getTsDb($tsId)
    {
        return $this->_multidb->getDb('ts' . $tsId);
    }
}