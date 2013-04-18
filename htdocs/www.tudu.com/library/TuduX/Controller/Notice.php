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
 * @version    $Id: Notice.php 2756 2013-02-26 02:07:14Z cutecube $
 */

/**
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class TuduX_Controller_Notice extends TuduX_Controller_Base
{
    private $_allow = array(
        'heguanfeng@oray',
        'caijiao@oray',
        'liushuxue@oray'
    );

	/**
     * 初始化
     */
    public function init()
    {
        parent::init();

        if (!in_array($this->_user->userName, $this->_allow)) {
            $this->_redirect('http://www.tudu.com/');
            exit();
        }

        $this->_helper->viewRenderer->view->setBasePath(APPLICATION_PATH . '/modules/notice/views');
        $this->_helper->viewRenderer->setViewScriptPathSpec(':module#:controller#:action.:suffix');
    }
}