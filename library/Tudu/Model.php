<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Tudu_Model_Exception
 */
require_once 'Tudu/Model/Exception.php';

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * @category   Tudu
 * @package    Tudu_Access
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model
{
    /**
     *
     * @var string
     */
    const RESOURCE_CONFIG = 'config';

    /**
     * 资源管理对象列表
     *
     * @var string
     */
    protected static $_resourceManagers = array();

    /**
     * 创建业务流程对象工厂方法
     *
     * @param string $className
     * @return Tudu_Model_Abstract
     */
    public static function factory($className)
    {
        Zend_Loader::loadClass($className);

        return new $className();
    }

    /**
     * 从当前环境配置中获取指定的资源
     *
     * @param string $name
     */
    public static function getResource($name)
    {
        foreach (self::$_resourceManagers as $manager) {
            if ($manager->hasResource($name)) {
                return $manager->getResource($name);
            }
        }

        throw new Exception();
    }

    /**
     * 当前环境是否存在指定的资源
     *
     * @param string $name
     */
    public static function hasResource($name)
    {
        foreach (self::$_resourceManagers as $manager) {
            if ($manager->hasResource($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 为当前对象添加资源管理对象
     *
     * @param Tudu_Model_ResourceManager_Abstract $resourceManager
     */
    public static function setResourceManager(Tudu_Model_ResourceManager_Abstract $resourceManager)
    {
        self::$_resourceManagers[] = $resourceManager;
    }
}