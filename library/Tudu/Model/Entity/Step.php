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
 * @version    $Id: Step.php 1970 2012-07-05 01:41:34Z cutecube $
 */

/**
 * 图度业务数据
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Entity_Step
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
    protected $_columns = array(
        'orgid', 'tuduid' , 'uniqueid', 'prevstepid', 'nextstepid', 'type', 'stepstatus', 'ordernum', 'percent'
    );

    /**
     * 步骤内用户流程
     * 0 => array()
     * 1 => array()
     *
     * @var array
     */
    protected $_sections = array();

    /**
     * Constructor
     *
     * @param $params
     */
    public function __construct(array $params = null)
    {
        $this->_attrs = $params;
    }

    /**
     * 设置当前对象属性值
     *
     * @param string $name
     * @param mixed  $value
     * @return Tudu_Model_Tudu_Entity_Step
     */
    public function setAttribute($name, $value = null)
    {
        if (is_array($name) && null === $value) {
            foreach ($name as $key => $val) {
                $key = strtolower($key);
                $this->_attrs[$key] = $val;
            }
        } else if (is_string($name)) {

            $name = strtolower($name);

            $this->_attrs[$name] = $value;
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
     * 添加步骤节点
     *
     * @return Tudu_Model_Tudu_Entity_Step
     */
    public function addSection(array $users)
    {
        foreach ($users as $userName => $user) {
            if (empty($user['email']) && empty($user['username'])) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception('Inivalid user or format');
            }
        }

        $this->_sections[] = $users;

        return $this;
    }

    /**
     * 获取步骤流程用户列表
     *
     * @return Tudu_Model_Tudu_Entity_Step
     */
    public function getSections()
    {
        return $this->_sections;
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