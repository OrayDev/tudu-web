<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Oray_View_Format_Interface
 */
require_once 'Oray/View/Format/Interface.php';

/**
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_View_Format_Json implements Oray_View_Format_Interface
{
    /**
     * 格式化输出内容
     *
     * @param unknown_type $data
     * @param unknown_type $options
     */
    public function format($data = array(), $options = array())
    {
        return json_encode($data);
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_Response_Formatter_Interface::getContentType()
     */
    public function getContentType()
    {
        return 'application/json;charset=utf-8';
    }
}