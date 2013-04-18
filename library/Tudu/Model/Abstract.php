<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 1970 2012-07-05 01:41:34Z cutecube $
 */

/**
 * 业务封装模型基类
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Abstract
{

    /**
     * 流程中使用的资源
     * 通过 Tudu_Model_Abstract::registerResource 注册资源
     * 流程中可通过 Tudu_Model_Abstract::getResource 获取
     *
     * @var array
     */
    protected static $_resources = array();

    /**
     * 注册模型资源
     *
     * @param mixed $name
     * @param mixed $object
     * @return void
     */
    public static function registerResource($name, $object = null, $override = false)
    {
        if (is_string($name) && null != $object) {
            if (isset(self::$_resources[$name]) && !$override) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception("Resource name :{$name} had been registered");
            }

            self::$_resources[$name] = $object;
        } else if (is_array($name)) {
            foreach ($name as $key => $item) {
                self::registerResource($key, $item);
            }
        }
    }

    /**
     * 获取资源对象实例
     *
     * @param string $name
     * @return mixed
     */
    public static function getResource($name)
    {
        if (!array_key_exists($name, self::$_resources)) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception("Resource name :{$name} had not been registered");
        }

        return self::$_resources[$name];
    }
}