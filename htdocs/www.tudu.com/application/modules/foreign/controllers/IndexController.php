<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Foreign_IndexController extends TuduX_Controller_Foreign
{
    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));
        $this->view->LANG = $this->lang;
    }
    
    /**
     * 
     */
    public function indexAction()
    {
        $this->view->tudu = $this->_tudu->toArray();
    }
    
    /**
     * 验证
     */
    public function authAction()
    {
        if (null === $this->_tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
        
        $password = $this->_request->getPost('password');
        $authCode = $this->_request->getPost('authcode');

        $authInfo = array();
        if($this->_tudu->password) {
            if ($this->_tudu->password != $password) {
                return $this->json(false, $this->lang['password_error']);
            }
            
            $authInfo['password'] = $password;
        }
        
        if ($this->_tudu->authCode) {
            if ($this->_tudu->authCode != $authCode) {
                return $this->json(false, $this->lang['invalid_authcode']);
            }
            
            $authInfo['authcode'] = $authCode;
        }
        
        $this->_session->foreign[$this->_tudu->tuduId] = $authInfo;
        
        return $this->json(true);
    }
    
    /**
     * 显示失效页面
     */
    public function invalidAction()
    {
        $this->render('invalid');
    }
}