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
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
interface Oray_View_Format_Interface
{
    /**
     * 格式化输出内容
     *
     * @param array $data
     * @param array $options
     * @return string
     */
    public function format($data = array(), $options = array()) ;

    /**
     * 获取当前格式HTTP Content-Type 头信息
     *
     * @return string
     */
    public function getContentType() ;
}