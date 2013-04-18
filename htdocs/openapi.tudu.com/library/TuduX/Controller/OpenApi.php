<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 * @category   TuduX
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Action
 */
require_once "Zend/Controller/Action.php";

/**
 * @category   TuduX
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class TuduX_Controller_OpenApi extends Zend_Controller_Action
{
    /**
     * 当前请求的访问令牌
     *
     * @var string
     */
    protected $_accessToken = null;

    /**
     *
     * @var mixed
     */
    protected $_bootstrap   = null;

    /**
     *
     * @var Tudu_User
     */
    protected $_user        = null;

    /**
     *
     * @var array
     */
    protected $_token       = null;

    /**
     * 当前请求的客户端ID
     *
     * @var string
     */
    protected $_clientId    = null;

    /**
     *
     * @var array
     */
    protected $_responseFormats = array(
        'xml', 'json'
    );

    /**
     *
     * @var array
     */
    protected $_officalClients = array(
        '91156429331037a8b0df54d6f5b95e27'
    );

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        parent::init();

        $this->_bootstrap = $this->getInvokeArg('bootstrap');

        $accessToken = $this->_request->getParam('access_token', $this->_request->getHeader('OAuth-AccessToken'));

        $memcache = $this->_bootstrap->getResource('memcache');
        Tudu_User::setMemcache($memcache);
        $this->_user = Tudu_User::getInstance();

        // 提供访问令牌
        if (!empty($accessToken)) {
            $storage  = new TuduX_OAuth_Storage_Session();
            $storage->setMemcache($memcache);

            $oauth = new OpenApi_OAuth_OAuth(array(OpenApi_OAuth_OAuth::STORAGE => $storage));

            $scope = $this->_request->getParam('client_id', $this->_request->getHeader('OAuth-Scope'));
            try {
                $token = $oauth->verifyAccessToken($accessToken, $scope);

                $this->_user->init($token['auth']);

                // 用户被禁用或已被退出登录
                if (!$this->_user->isLogined()) {
                    $oauth->destroyAccessToken($accessToken);
                    throw new OpenApi_OAuth_Exception("Invalid access token provided", OpenApi_OAuth_OAuth::ERROR_INVALID_ACCESSTOKEN);
                }

                // ts服务器
                $tsServer = 'ts' . $this->_user->tsId;
                Tudu_Dao_Manager::setDbs(array(
                    Tudu_Dao_Manager::DB_TS => $this->_bootstrap->multidb->getDb($tsServer)
                ));

                $this->_clientId    = $token[OpenApi_OAuth_OAuth::PARAM_CLIENT_ID];
                $this->_accessToken = $accessToken;
                $this->_token       = $token;

            // 验证失败
            } catch (OpenApi_OAuth_Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::postDispatch()
     */
    public function postDispatch()
    {
        // 设置返回格式
        $format = $this->_request->getParam('responseformat');
        $format = $format ? $format : OpenApi_Response_Response::FORMAT_JSON;

        if ($this->view instanceof OpenApi_Response_Response) {
            $this->view->setResponseFormat($format);

            $contentType = $this->view->getContentType();
            $this->_response->setHeader('Content-Type', $contentType);
        }

        $this->view->category = $this->_request->getControllerName();
        $this->view->action   = array_shift(explode('.', $this->_request->getActionName()));

        parent::postDispatch();
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::dispatch()
     */
    public function dispatch($action)
    {
        $action = substr($action, 0, -6);

        $format = '';
        for ($i = strlen($action) - 1; $i >= 0; $i--) {
            $format = $action[$i] . $format;

            $code = ord($action[$i]);
            if ($code >= 65 && $code <= 90) {
                break;
            }
        }

        $format = strtolower($format);
        if (in_array($format, $this->_responseFormats)) {
            $action = substr($action, 0, -1 * strlen($format));
        } else {
            $format = 'json';
        }

        $this->_setParam('responseformat', $format);

        parent::dispatch($action . 'Action');
    }
}