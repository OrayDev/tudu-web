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
 * @version    $Id:$
 */

/**
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class TuduX_Controller_Query extends TuduX_Controller_Base
{

    private $_allow = array(
        'maikliu@oray.com',
        'moliuming@oray.com',
        'heguanfeng@oray.com',
        'hiro@oray.com',
        'moliuming@oray.com',
        'houyongqiang@oray.com',
        'ymojia@oray.com',
        'lijiajie@oray.com',
        'yezi@oray.com',
        'ken@oray.com',
    );

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        if (!in_array($this->_user->email, $this->_allow)) {
            $this->_redirect('http://www.tudu.com/');
            exit();
        }

        $this->_helper->viewRenderer->view->setBasePath(APPLICATION_PATH . '/modules/query/views');
        $this->_helper->viewRenderer->setViewScriptPathSpec(':module#:controller#:action.:suffix');
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getTsDb($tsId)
    {
        return $this->multidb->getDb('ts' . $tsId);
    }
}