<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 1866 2012-05-17 07:50:31Z web_op $
 */

/**
 *
 * @author     CuTe_CuBe
 * @category   TuduX
 * @package    TuduX_App
 */
class TuduX_App_Abstract
{

    /**
     * 操作返回格式
     *
     * @var string
     */
    const RESPONSE_FORMAT_HTML = 'html';
    const RESPONSE_FORMAT_JSON = 'json';

    /**
     * 可支持返回格式
     *
     * @var array
     */
    protected static $_responseFormats = array(self::RESPONSE_FORMAT_HTML, self::RESPONSE_FORMAT_JSON);

    /**
     * 当前操作返回格式
     *
     * @var string
     */
    protected $_responseFormat = self::RESPONSE_FORMAT_HTML;

    /**
     *
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request;

    /**
     *
     * @var TuduX_App_Response
     */
    protected $_response;

    /**
     *
     * @var string;
     */
    protected $_action;

    /**
     *
     * @var Oray_View_Smarty
     */
    protected $view;

    /**
     *
     * @var array
     */
    public $_user;

    /**
     *
     * @var array
     */
    public $_options;

    /**
     *
     * @var AppController
     */
    public $_this;

    /**
     * 语言
     *
     * @var array
     */
    public $lang;

    /**
     *
     */
    public final function __construct($controller)
    {
        $this->_this = $controller;
    }

    /**
     * 初始化函数
     * 子类继承可通过重写本函数实现自己的初始化流程
     */
    public function init()
    {}

    /**
     * 设置当前操作的返回格式
     *
     * @param string $format
     * @return TuduX_App_Abstract
     */
    public final function setResponseFormat($format)
    {
        if (!in_array($format, self::$_responseFormats)) {
            require_once 'TuduX/App/Exception.php';
            throw new TuduX_App_Exception('Unsupport response format');
        }

        $this->_responseFormat = $format;

        return $this;
    }

    /**
     * 获取当前操作返回格式
     *
     * @param string $format
     */
    public final function getResponseFormat()
    {
        return $this->_responseFormat;
    }

    /**
     * 设置当前请求
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return TuduX_App_Abstract
     */
    public final function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * 返回当前处理请求
     *
     * @return Zend_Controller_Request_Abstract
     */
    public final function getRequest()
    {
        return $this->_request;
    }

    /**
     * 获取应用返回
     *
     * @return TuduX_App_Response
     */
    public final function getResponse()
    {
        if (null === $this->_response) {
            $this->_response = new TuduX_App_Response();
        }

        return $this->_response;
    }

    /**
     * 从控制器中获取资源
     *
     * @param string $name
     * @return mixed
     */
    public final function getResource($name)
    {
        return $this->_this->bootstrap->getResource($name);
    }

    /**
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            if ($key == 'user') {
                $this->_user = $value;
                continue ;
            }
            $this->_options[$key] = $value;
        }
    }

    /**
     * 执行图度APP请求处理
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return TuduX_App_Response
     */
    public final function run($action, Zend_Controller_Request_Abstract $request, $params)
    {
        $this->setRequest($request);
        $this->setParams($params);

        $this->init();

        //$response = $this->getResponse();

        $this->_action = $action;

        $func = $action . 'Action';

        $this->{$func}();
    }

    /**
     * 完成执行后调用函数
     *
     * @return void;
     */
    public function postRun()
    {}

    /**
     *
     * @param unknown_type $success
     * @param unknown_type $params
     * @param unknown_type $data
     * @param unknown_type $sendHeader
     */
    public final function json($success = false, $params = null, $data = null, $sendHeader = true)
    {
        return $this->_this->json($success, $params, $data, $sendHeader);
    }
}