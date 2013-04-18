<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Extension.php 1292 2011-11-15 10:10:57Z cutecube $
 */

/**
 * 图度扩展功能管理对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension
{

    /**
     *
     * @var array
     */
    protected static $_extensions = array();

    /**
     *
     */
    public static function registerExtension($key, $obj, $override = false)
    {
        if (!is_string($key)) {
            throw new Exception("Extension key must be a string");
        }

        if (self::isRegistered($key) && !$override) {
            throw new Exception("Extension {$key} had been registered");
        }

        /*if (!is_string($obj) || !$obj instanceof Tudu_Extension_Abstract) {
            throw new Exception("Extension must be a string or object implement Tudu_Extension_Abstract");
        }*/

        self::$_extensions[$key] = $obj;
    }

    /**
     *
     */
    public static function unRegisterExtension($key)
    {
        if (!array_key_exists($key, self::$_extensions)) {
            throw new Exception("Extension {$key} had not registered");
        }

        unset(self::$_extensions[$key]);
    }

    /**
     *
     * @param string $key
     * @return Tudu_Extension_Abstract
     */
    public static function getExtension($key)
    {
        if (!array_key_exists($key, self::$_extensions)) {
            throw new Exception("Extension {$key} had not registered");
        }

        $obj = self::$_extensions[$key];

        if (is_string($obj)) {
            self::$_extensions[$key] = new $obj;
        }

        return self::$_extensions[$key];
    }

    /**
     *
     * @return array
     */
    public static function getRegisteredExtensions()
    {
        return array_keys(self::$_extensions);
    }

    /**
     *
     * @param string $key
     */
    public static function isRegistered($key)
    {
        return array_key_exists($key, self::$_extensions);
    }

    /**
     *
     * @param string $key
     */
    public static function unRegister($key)
    {
        if (self::isRegistered($key)) {
            unset(self::$_extensions[$key]);
        }
    }

    /**
     * 注销所有注册扩展
     */
    public static function unRegisterAll()
    {
        foreach (self::$_extensions as $key => $obj) {
            unset(self::$_extensions[$key]);
        }
        self::$_extensions = array();
    }

    /**
     * 注册并取出对象
     * 如果对象已被注册则直接返回
     *
     * @param string $key
     * @param mixed $obj
     * @return Tudu_Tudu_Extension_Abstract
     */
    public static function registerAndGet($key, $obj)
    {
        if (!self::isRegistered($key)) {
            self::registerExtension($key, $obj);
        }

        return self::getExtension($key);
    }
}