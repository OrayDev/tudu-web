<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Base.php 419 2010-11-03 10:37:59Z cutecube $
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
abstract class TuduX_Controller_Api extends Zend_Controller_Action
{
	
	const API_KEY = 'www.tudu.com';
	
	/**
	 * 
	 * @var string
	 */
	protected $_orgId;
	
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
	 * 初始化
	 */
	public function init()
	{
		$this->_helper->viewRenderer->setNeverRender();
		
		$this->_bootstrap = $this->getInvokeArg('bootstrap');
		$this->_multidb   = $this->_bootstrap->getResource('multidb');
	}
	
	/**
	 * 接口验证
	 */
	protected function _auth()
	{
		$time  = (int) $this->_request->getParam('t');
		$orgId = $this->_request->getParam('orgid');
		
		$key   = $this->_request->getParam('k');
		
		if ($key != md5($orgId . $time . self::API_KEY)) {
			$this->json(false, 'auth_failure');
			return false;
		}
		
		$new = time();
		/*if ($time < $now - 3600 || $time > $now + 3600) {
			$this->json(false, 'timeout');
			return false;
		}*/
		
		$this->_orgId = $orgId;
		
		return true;
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
     * Get dao
     * 
     * @param string $className
     * @return Oray_Dao_Abstract
     */
    public function getDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->_multidb->getDefaultDb()));
        }
        return Zend_Registry::get($className);
    }
    
    /**
     * 
     * @param $className
     * @param $tsId
     */
    public function getTdDao($className, $tsId)
    {
    	if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->getTsDb($tsId)));
        }
        return Zend_Registry::get($className);
    }
    
    /**
     * 
     * @param $tsId
     */
    public function getTsDb($tsId)
    {
    	return $this->_multidb->getDb('ts' . $tsId);
    }
}