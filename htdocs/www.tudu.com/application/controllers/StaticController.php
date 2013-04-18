<?php
/**
 * Static Controller
 * 输出静态内容
 * 
 * @version $Id: StaticController.php 426 2010-11-05 02:49:34Z cutecube $
 */
class StaticController extends Zend_Controller_Action
{
	/**
	 * 
	 * @var Tudu_User
	 */
	private $_user;
	
    /**
     * 初始化
     */
    public function init()
    {
        $this->_helper->viewRenderer->setNeverRender();
    }
	
	/**
	 * css
	 */
	public function cssAction()
	{}
	
	/**
	 * js
	 */
	public function jsAction()
	{
		$f = $this->_request->getQuery('f');
		$ret = null;

		switch ($f) {
			case 'lang':
				$lang = $this->_request->getQuery('lang');
        		
				$lang = $lang ? $lang : 'zh_CN';
				$arr  = require_once LANG_PATH . '/' . $lang . '/js.inc';
				$ret .= 'var TEXT=' . json_encode($arr) . ';window.TEXT=TEXT;window.LANG=TEXT;';
				break;
			default:
				break;
		}
		
		$this->_response->setHeader('Content-Type', 'application/x-javascript charset=utf-8');
		$this->_response->setHeader('Content-Length', strlen($ret));
		$this->_response->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + 36000), true);
        $this->_response->setHeader('Cache-Control', 'private', true);
        $this->_response->setHeader('Pragma', 'private', true);
        
        $this->_response->sendHeaders();
        
        echo $ret;
	}
	
	/**
	 * 空内容
	 */
	private function _responseNull()
	{
		$this->_response->setHeader('Expires', gmdate('D, d M Y H:i:s T', time() + 36000), true);
        $this->_response->setHeader('Cache-Control', 'private', true);
        $this->_response->setHeader('Pragma', 'private', true);
            
        $this->_response->sendHeaders();
            
        exit();
	}
}