<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * 图度业务流程模型基类
 *
 * @category   Model
 * @package    Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Model_Abstract
{
    /**
     *
     * @var int
     */
    const HOOK_WEIGHT_MAX = 999;

    /**
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * @var array
    */
    protected $_hooks = array();

    /**
     * 设置选项
     *
     * @param $key
     * @param $value
    */
    public function setOptions($key, $value = null)
    {
        if (null !== $value && is_string($key)) {
            $this->_options[$key] = $value;
        } elseif (is_array($key)) {
            $this->_options = array_merge($this->_options, $key);
        }

        return $this;
    }

    /**
     * 添加流程前处理过程
     *
     * @param string  $action
     * @param string  $func
     * @param int     $weight
     * @param array   $params
     * @param boolean $defaultParams
     */
    public function addFilter($action, $func, $weight = 1, $params = null, $defaultParams = true)
    {
        if ($weight >= self::HOOK_WEIGHT_MAX) {
            $weight = self::HOOK_WEIGHT_MAX;
        }

        if (!isset($this->_hooks[$action])) {
            $this->_initHooks($action);
        }

        $arr = explode('-', $action);
        foreach ($arr as $idx => $item) {
            if ($idx > 0) {
                $arr[$idx] = ucfirst($arr[$idx]);
            }
        }
        $action     = implode('', $arr);

        $this->_hooks[$action]['filter'][] = array(
            'func'   => $func,
            'weight' => $weight,
            'params' => $params,
            'defaultParams' => $defaultParams
        );

        return $this;
    }

    /**
     * 添加流程后处理过程
     *
     * @param string  $action          追加流程方法名
     * @param string  $func            追加流程函数
     * @param int     $weight          权重，数值大的优先执行
     * @param array   $params          追加方法的参数列表
     * @param boolean $defaultParams   是否继承煮流程方法的参数，如果继承，主流程的参数会被加到追加方法的前面，默认为true
     */
    public function addAction($action, $func, $weight = 1, $params = null, $defaultParams = true)
    {
        if ($weight >= self::HOOK_WEIGHT_MAX) {
            $weight = self::HOOK_WEIGHT_MAX;
        }

        if (!isset($this->_hooks[$action])) {
            $this->_initHooks($action);
        }

        $arr = explode('-', $action);
        foreach ($arr as $idx => $item) {
            if ($idx > 0) {
                $arr[$idx] = ucfirst($arr[$idx]);
            }
        }
        $action     = implode('', $arr);

        $this->_hooks[$action]['action'][] = array(
            'func'   => $func,
            'weight' => $weight,
            'params' => $params,
            'defaultParams' => $defaultParams
        );

        return $this;
    }

    /**
     * 添加异常处理
     *
     * @param $action
     * @param $exception
     * @param $func
     * @param $weight
     * @param $params
     * @param $defaultParams
     */
    public function addExceptionHandler($action, $exception, $func, $weight = 1, $params = null, $defaultParams = true)
    {
        if ($weight >= self::HOOK_WEIGHT_MAX) {
            $weight = self::HOOK_WEIGHT_MAX;
        }

        if (!isset($this->_hooks[$action])) {
            $this->_initHooks($action);
        }

        $arr = explode('-', $action);
        foreach ($arr as $idx => $item) {
            if ($idx > 0) {
                $arr[$idx] = ucfirst($arr[$idx]);
            }
        }
        $action     = implode('', $arr);

        $this->_hooks[$action]['exception'][$exception][] = array(
            'func'   => $func,
            'weight' => $weight,
            'params' => $params,
            'defaultParams' => $defaultParams
        );

        return $this;
    }

    /**
     *
     * @param $type
     */
    public function applyHooksFunc($action, $type, &$params)
    {
        if (empty($this->_hooks[$action]) || empty($this->_hooks[$action][$type])) {
            return ;
        }

        $funcs = $this->_hooks[$action][$type];

        usort($funcs, array($this, '_sortHooks'));

        foreach ($funcs as $func) {
            $p = !empty($func['params']) ? $func['params'] : array();

            if ($func['defaultParams']) {
                //$p = array_merge($params, $p);
                foreach ($params as $k => $v) {
                    $p[$k] = &$params[$k];
                }
            }

            if (!empty($func['params'])) {
                foreach ($func['params'] as $name => $val) {
                    $p[$name] = $val;
                }
            }

            call_user_func_array($func['func'], $p);
        }
    }

    /**
     * 获取资源
     *
     * @param  string $name
     * @return mixed
     */
    public function getResource($name)
    {
        return Tudu_Model::getResource($name);
    }

    /**
     *
     * @param $action
     * @param $params
     */
    public function execute($action, array $params)
    {
        $arr = explode('-', $action);
        foreach ($arr as $idx => $item) {
            if ($idx > 0) {
                $arr[$idx] = ucfirst($arr[$idx]);
            }
        }
        $action = implode('', $arr);

        if (!method_exists($this, $action)) {
            require_once 'Tudu/Model/Exception.php';
            throw new Tudu_Model_Exception("Undefined action named: {$action}");
        }

        try {

            $this->applyHooksFunc($action, 'filter', $params);

            call_user_func_array(array($this, $action), $params);

            $this->applyHooksFunc($action, 'action', $params);

        } catch (Exception $e) {

            $this->_catchException($action, $e, $params);

        }
    }

    /**
     *
     */
    public function reset()
    {
        foreach ($this->_hooks as $idx => $item) {
            unset($this->_hooks[$idx]);
        }

        $this->_hooks = array();

        return $this;
    }

    /**
     * 钩子函数排序
     *
     * @param
     */
    protected function _sortHooks($left, $right)
    {
        return $left['weight'] <= $right['weight'];
    }

    /**
     *
     * @param string $action
     */
    protected function _initHooks($action)
    {
        $this->_hooks[$action] = array(
            'filter'    => array(),
            'action'    => array(),
            'exception' => array()
        );
    }

    /**
     *
     * @param Exception $e
     */
    protected function _catchException($action, Exception $exception, $params)
    {
        $className = get_class($exception);

        if (!empty($this->_hooks[$action]['exception'])
        && !empty($this->_hooks[$action]['exception'][$className]))
        {
            $funcs = $this->_hooks[$action]['exception'][$className];

            usort($funcs, array($this, '_sortHooks'));

            foreach ($funcs as $func) {
                $p = !empty($func['params']) ? $func['params'] : array();

                if ($func['defaultParams']) {
                    $p = array_merge($params, $p);
                }

                call_user_func_array($func['func'], $p);
            }

            return ;
        }

        throw $exception;
    }

    /**
     * 重载方法
     *
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        if (0 === strpos($name, 'do')) {
            $action = strtolower(substr($name, 2, 1)) . substr($name, 3);

            return $this->execute($action, $arguments);
        }

        require_once 'Tudu/Model/Exception.php';
        throw new Tudu_Model_Exception("Undefined method named: {$name}");
    }
}