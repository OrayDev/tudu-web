<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Db
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Db.php 9326 2012-02-22 07:42:08Z gxx $
 */

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * @category   Oray
 * @package    Oray_Db
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Db
{
    /**
     * Factory for Zend_Db_Adapter_Abstract classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'Mysqli' corresponds to class Zend_Db_Adapter_Mysqli.  This
     * name is currently case-insensitive, but is not ideal to rely on this behavior.
     * If your class is named 'My_Company_Pdo_Mysql', where 'My_Company' is the namespace
     * and 'Pdo_Mysql' is the adapter name, it is best to use the name exactly as it
     * is defined in the class.  This will ensure proper use of the factory API.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The adapter class base name is read from the 'adapter' property.
     * The adapter config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the adapter constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  string $adapter String name of base adapter class
     * @param  array  $config  An array with adapter parameters.
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Db_Exception
     */
    public static function factory($adapter, array $config)
    {
        if (!empty($config['encode']) && isset($config['password'])) {
            $config['password'] = 'x';
            unset($config['encode']);
        }

        return Zend_Db::factory($adapter, $config);
    }

    /**
     * 简单的加密
     *
     * @param string $str
     * @return string
     */
    public static function encrypt($str)
    {
        return base64_encode($str);
    }

    /**
     * 简单的解密
     *
     * @param string $str
     * @return string
     */
    public static function decrypt($str)
    {
        return base64_decode($str);
    }

}