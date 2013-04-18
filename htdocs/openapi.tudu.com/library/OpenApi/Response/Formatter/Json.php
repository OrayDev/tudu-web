<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @category   OpenAPi
 * @package    OpenApi
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   OpenApi
 * @package    OpenApi
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_Response_Formatter_Json implements OpenApi_Response_Formatter_Interface
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