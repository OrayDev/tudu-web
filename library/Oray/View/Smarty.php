<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Smarty.php 5679 2011-01-28 06:58:00Z gxx $
 */

/** Zend_View_Interface */
require_once 'Zend/View/Interface.php';

/** Smarty */
require_once 'Smarty/Smarty.class.php';

/**
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_View_Smarty implements Zend_View_Interface
{
    /**
     * Smarty object
     * @var Smarty
     */
    private $_smarty;

    /**
     * Instances of helper objects.
     *
     * @var array
     */
    private $_helper = array();

    /**
     * Plugin loaders
     * @var array
     */
    private $_loaders = array();

    /**
     * Callback for escaping.
     *
     * @var string
     */
    private $_escape = 'htmlspecialchars';

    /**
     * Constructor
     *
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct($tmplPath = null, $extraParams = array())
    {
        $this->_smarty = new Smarty;

        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }

        foreach ($extraParams as $key => $value) {
            $this->_smarty->$key = $value;
        }
        
        $this->_smarty->register_compiler_function('helper', array($this, 'helper'));
        $this->_smarty->assign('_helper', $this);
    }

    /**
     * Smarty Compiler Functions
     * 
     * This function can be called from the template as: 
     * {{helper url(array('controller' => 'login', 'action' => 'logout.do'))}}
     * 
     * @param string $tag_arg
     * @param object $smarty
     */
    public function helper($tag_arg, &$smarty)
    {
        return "echo \$this->_tpl_vars['_helper']->{$tag_arg}";
    }
    
    /**
     * Return the template engine object
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    /**
     * Set the path to the templates
     *
     * @param string $path The directory to set as the path.
     * @return Oray_View_Smarty
     */
    public function setScriptPath($path)
    {
        if (is_readable($path)) {
            $this->_smarty->template_dir = $path;
            return $this;
        }

        throw new Exception('Invalid path provided');
    }

    /**
     * Set the path to the compiled templates
     *
     * @param string $path The directory to set as the path.
     * @return Oray_View_Smarty
     */
    public function setCompilePath($path)
    {
        if (is_readable($path)) {
            $this->_smarty->compile_dir = $path;
            return $this;
        }

        throw new Exception('Invalid path provided');
    }

    /**
     * Retrieve the current template directory
     *
     * @return string
     */
    public function getScriptPaths()
    {
        return array($this->_smarty->template_dir);
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return Oray_View_Smarty
     */
    public function setBasePath($path, $prefix = 'Zend_View')
    {
        return $this->setScriptPath($path);
    }


    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        $type = 'helper';
        if (!array_key_exists($type, $this->_loaders)) {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            /**
             * @see Zend_Loader_PluginLoader
             */
            require_once 'Zend/Loader/PluginLoader.php';
            
            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Zend_Loader_PluginLoader(array(
                        $prefix => $pathPrefix
                    ));
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }
        return $this->_loaders[$type];
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name)
    {
        return $this->_getHelper($name);
    }
    
    /**
     * Registers a helper object, bypassing plugin loader
     *
     * @param  Zend_View_Helper_Abstract|object $helper
     * @param  string $name
     * @return Zend_View_Abstract
     * @throws Zend_View_Exception
     */
    public function registerHelper($helper, $name)
    {
        if (!is_object($helper)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('View helper must be an object');
            $e->setView($this);
            throw $e;
        }

        if (!$helper instanceof Zend_View_Interface) {
            if (!method_exists($helper, $name)) {
                require_once 'Zend/View/Exception.php';
                $e =  new Zend_View_Exception(
                    'View helper must implement Zend_View_Interface or have a method matching the name provided'
                );
                $e->setView($this);
                throw $e;
            }
        }

        if (method_exists($helper, 'setView')) {
            $helper->setView($this);
        }

        $name = ucfirst($name);
        $this->_helper[$name] = $helper;
        return $this;
    }

    /**
     * 注册Smarty模板调用函数
     *
     * @param string function 模板调用函数的名称
     * @param string|array function 调用函数
     * @param boolean function cacheable 是否缓存结果
     * @param mix cacheAttrs
     * @return Oray_View_Smarty
     */
    public function registFunction($function, $functionImpl, $cacheable = true, $cacheAttrs = null)
    {
        $this->_smarty->register_function($function, $functionImpl, $cacheable, $cacheAttrs);
        return $this;
    }
    
    /**
     * 注册变量调节器
     * 
     * @param $name 名称
     * @param $functionImpl 调用函数
     */
    public function registModifier($name, $functionImpl)
    {
        $this->_smarty->register_modifier($name, $functionImpl);
    }

    /**
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return Oray_View_Smarty
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        return $this;
        /**
         * 基于Smarty模板单目录读取的原因，取消ViewRenderer多个目录的设置
         * 如果需要设置不同的目录，请通过setScriptPath直接设置
         */
        return $this->setScriptPath($path);
    }

    /**
     * Retrieve a helper object
     *
     * @param  string $type
     * @param  string $name
     * @return object
     */
    private function _getHelper($name)
    {
        if (!isset($this->_helper[$name])) {
            $class = $this->getPluginLoader()->load($name);
            $this->_helper[$name] = new $class();
            if (method_exists($this->_helper[$name], 'setView')) {
                $this->_helper[$name]->setView($this);
            }
        }
        return $this->_helper[$name];
    }

    /**
     * Assign a variable to the template
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return Oray_View_Smarty
     */
    public function __set($key, $val)
    {
        $this->_smarty->assign($key, $val);
        return $this;
    }

    /**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
         return (null !== $this->_smarty->get_template_vars($key));
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clear_assign($key);
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            array($helper, $name),
            $args
        );
    }

    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing an array
     * of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or array of key
     * => value pairs)
     * @param mixed $value (Optional) If assigning a named variable, use this
     * as the value.
     * @return Oray_View_Smarty
     */
    public function assign($spec, $value = null)
    {
        $this->_smarty->assign($spec, $value);
        return $this;
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or
     * property overloading ({@link __get()}/{@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_smarty->clear_all_assign();
    }

    /**
     * Processes a template and returns the output.
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    public function render($name)
    {
        return $this->_smarty->fetch($name);
    }
    
    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
            return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_encoding);
        }

        return call_user_func($this->_escape, $var);
    }
}