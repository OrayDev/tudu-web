<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 1894 2012-05-31 08:02:57Z cutecube $
 */

/**
 * OpenApi验证流程模型虚类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Model_OpenApi_Abstract
{

    /**
     * 执行验证流程
     *
     * @param array $params
     */
    abstract public static function auth(array $params) ;

    /**
     * 获取用户信息
     *
     * @param array $params
     */
    abstract public static function getUserInfo(array $params) ;
}