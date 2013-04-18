<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */


/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class ClientController extends TuduX_Controller_OpenApi
{
    /**
     * 验证操作
     */
    public function infoAction()
    {
        $this->view->code    = 0;
        $this->view->version = '1.1.1';
    }
}