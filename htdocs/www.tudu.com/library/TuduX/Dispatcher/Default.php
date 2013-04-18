<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_Dispatcher
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Default.php 1341 2011-12-01 10:38:22Z cutecube $
 */

/**
 *
 * @see Zend_Controller_Dispatcher_Abstract
 */
require_once 'Zend/Controller/Dispatcher/Standard.php';

/**
 *
 * @author CuTe_CuBe
 *
 */
class TuduX_Dispatcher_Default extends Zend_Controller_Dispatcher_Standard
{

    /**
     * 默认应用配置界面控制器
     *
     * @var string
     */
    protected $_defaultAppSettingController = 'setting';

    /**
     * 默认APP控制器入口action
     *
     * @var string
     */
    protected $_defaultAppIndexAction = 'index';

    /**
     * 格式化App控制器类名
     *
     * @param string $unformated
     * @return string
     */
    public function formatAppControllerName($unformatted)
    {

    }

    /**
     * 格式化 App动作方法名
     *
     * @param $unformatted
     * @return boolean
     */
    public function formatAppActionName($unformatted)
    {

    }

    /**
     *
     * @param Zend_Controller_Request_Abstract  $request
     * @param Zend_Controller_Response_Abstract $response
     */
    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {
            $controller = $request->getControllerName();
            if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);

        /**
         * Instantiate controller with request, response, and invocation
         * arguments; throw exception if it's not an action controller
         */
        $controller = new $className($request, $this->getResponse(), $this->getParams());
        if (!($controller instanceof Zend_Controller_Action_Interface) &&
            !($controller instanceof Zend_Controller_Action)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception(
                'Controller "' . $className . '" is not an instance of Zend_Controller_Action_Interface'
            );
        }

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        $obLevel   = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $controller->dispatch($action);
        } catch (Exception $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }
}