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
 * @version    $Id: Abstract.php 1867 2012-05-17 08:07:44Z cutecube $
 */

/**
 * 图度业务数据基类
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Tudu_Model_Entity_Abstract
{
    /**
     * 属性列表
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     * 字段列表
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Constructor
     *
     * @param $params
     */
    public function __construct(array $params = null)
    {
        $this->setAttribute($params);
    }

    /**
     * 设置当前对象属性值
     *
     * @param string $name
     * @param mixed  $value
     * @return Tudu_Model_Tudu_Entity_Tudu
     */
    public function setAttribute($name, $value = null)
    {
        if (is_array($name) && null === $value) {
            foreach ($name as $key => $val) {
                $key = strtolower($key);
                $this->setAttribute($key, $val);
            }
        } else if (is_string($name)) {
            $name = strtolower($name);

            if (!empty($this->_columns) && isset($this->_columns[$name])) {
                $this->_attrs[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * 获取当前对象属性值
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (!isset($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     * 获取属性类表
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     * 直接访问属性
     *
     * @param $name
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     *
     * @param $name
     * @param $val
     */
    public function __set($name, $val)
    {
        return $this->setAttribute($name, $val);
    }
}