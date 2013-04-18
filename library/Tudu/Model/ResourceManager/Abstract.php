<?php
/**
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id$
 */

/**
 * 后台计划任务基类
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Tudu_Model_ResourceManager_Abstract
{

    /**
     * 从当前对象中获取资源，当不存在时抛出异常
     *
     * @throws Tudu_Model_ResourceManager_Exception
     * @param string $name
     * @return mixed
     */
    abstract public function getResource($name) ;

    /**
     * 当前对象中是否存在指定的资源
     *
     * @param string $name
     * @return mixed
     */
    abstract public function hasResource($name) ;
}