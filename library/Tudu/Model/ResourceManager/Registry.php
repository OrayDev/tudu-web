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
 * @see Tudu_Model_ResouceManager_Abstract
 */
require_once 'Tudu/Model/ResourceManager/Abstract.php';

/**
 * 后台计划任务基类
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_ResourceManager_Registry extends Tudu_Model_ResourceManager_Abstract
{
    /**
     *
     * @var array
     */
    protected $_resources = array();

    /**
     * 构造函数
     */
    public function __construct()
    {}

    /**
     * (non-PHPdoc)
     * @see Tudu_Model_ResourceManager_Abstract::hasResource()
     */
    public function hasResource($name)
    {
        return array_key_exists($name, $this->_resources);
    }

    /**
     * (non-PHPdoc)
     * @see Tudu_Model_ResourceManager_Abstract::getResource()
     */
    public function getResource($name)
    {
        if (!$this->hasResource($name)) {
            require 'Tudu/Model/ResourceManager/Exception.php';
            throw new Tudu_Model_ResourceManager_Exception('Undefined resource named: ' . $name);
        }

        return $this->_resources[$name];
    }

    /**
     *
     * @param string $name
     * @param mixed $object
     */
    public function setResource($name, $object = null)
    {
        if (null === $object && is_array($name)) {
            foreach ($name as $k => $v) {
                $this->_resources[$k] = $v;
            }
        } else {
            $this->_resources[$name] = $object;
        }
    }
}