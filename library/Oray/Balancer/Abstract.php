<?php
/**
 * Oray Framework
 * 任务负载均衡调节器接口虚类
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Balancer
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 8939 2012-01-04 09:03:14Z cutecube $
 */

/**
 * @category   Oray
 * @package    Oray_Balancer
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Oray_Balancer_Abstract
{

    /**
     * 分配对象列表
     *
     * @var array <mixed>()
     */
    protected $_items = array();

    /**
     * 目标对象数
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * 当前命中
     *
     * @var mixed
     */
    protected $_current = null;

    /**
     * 处理返回的工厂方法
     *
     * @var mixed
     */
    protected $_factoryMethod = null;

    /**
     * 添加分配对象
     *
     * @param mixed  $item
     * @param string $idx
     * @return Oray_Balancer_Abstract
     */
    public function addItem($item, $idx = null)
    {
        if (is_string($idx)) {
            $this->_items[$idx] = $item;
        } else {
            $this->_items[] = $item;
        }
        $this->_count ++;
    }

    /**
     * 返回当前指向
     *
     * @return mixed
     */
    public function getCurrent()
    {
        $this->_current = key($this->_items);

        return $this->_current;
    }

    /**
     * 设置当前指向
     *
     * @param $index
     */
    public function setCurrent($idx)
    {
        if (!array_key_exists($idx, $this->_items)) {
            require_once 'Oray/Balancer/Exception.php';
            throw new Oray_Balancer_Exception("Undefined index {$idx}");
        }

        $this->_current = $idx;

        return $this;
    }

    /**
     * 设置工厂方法
     *
     * @param mixed $func
     * @return Oray_Balancer_Abstract
     */
    public function setFactoryMethod($func)
    {
        $this->_factoryMethod = $func;

        return $this;
    }

    /**
     * 获取自动分配（指定）对象
     *
     * @param string $idx
     */
    public function select($idx = null)
    {
        if (!count($this->_items)) {
            require_once 'Oray/Balancer/Exception.php';
            throw new Oray_Balancer_Exception("Item list is empty");
        }

        if (is_string($idx)) {
            if (!array_key_exists($idx, $this->_items)) {
                require_once 'Oray/Balancer/Exception.php';
                throw new Oray_Balancer_Exception("Unregistered item indexed \"{$idx}\"");
            }

            $ret = $this->_items[$idx];

        } else {
            $ret = $this->_select();
            $idx = $this->getCurrent();
        }

        $ret = $this->_factory($ret);

        $this->_items[$idx] = $ret;

        return $ret;
    }

    /**
     * 执行自动选择操作，子类实现
     *
     *
     */
    protected abstract function _select() ;

    /**
     * 通过设定工厂方法生成最终返回值
     *
     * @param mixed $item
     * @return mixed
     */
    protected function _factory($item)
    {
        if (null === $this->_factoryMethod) {
            return $item;
        }

        $method = $this->_factoryMethod;
        if (is_array($method)) {
            if (!method_exists($method[0], $method[1])) {
                require_once 'Oray/Balancer/Exception.php';
                throw new Oray_Balancer_Exception("Factory method is not a function");
            }

            return $method[0]->{$method[1]}($item);

        // 字符串
        } elseif (is_string($method)) {
            if (!function_exists($method)) {
                require_once 'Oray/Balancer/Exception.php';
                throw new Oray_Balancer_Exception("Factory method is not a function");
            }

            return $method($item);
        }

        return $item;
    }

    /**
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $name = '_' . $name;
        if (!property_exists(self, $name)) {
            require_once 'Oray/Balancer/Exception.php';
            throw new Oray_Balancer_Exception("Undefined property name {$name} in class " . __CLASS__);
        }

        $this->{$name} = $value;
    }

    /**
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __get($name)
    {
        $name = '_' . $name;
        if (!property_exists($this, $name)) {
            require_once 'Oray/Balancer/Exception.php';
            throw new Oray_Balancer_Exception("Undefined property name {$name} in class " . __CLASS__);
        }

        return $this->{$name};
    }
}