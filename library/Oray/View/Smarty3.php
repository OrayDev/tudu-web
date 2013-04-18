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
 * @version    $Id: Smarty3.php 9929 2012-04-24 09:53:22Z yiqinfei $
 */

/** Zend_View_Interface */
require_once 'Zend/View/Interface.php';

/** Smarty */
require_once 'Smarty3/Smarty.class.php';

/**
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_View_Smarty3 implements Zend_View_Interface
{
    /**
     * Smarty object
     * @var Smarty
     */
    private $_smarty;

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
        $this->_smarty->error_reporting = E_ALL & ~ E_NOTICE;

        if (null !== $tmplPath) {
            $this->setScriptPath($tmplPath);
        }

        foreach ($extraParams as $key => $value) {
            switch ($key) {

                case "template_dir ":
                case "templateDir ":

                    $this->setScriptPath($value);
                    break;

                case "compile_dir":
                case "compileDir":

                    $this->setCompilePath($value);
                    break;

                default:

                    $this->_smarty->$key = $value;
                    break;

            }
        }

    }

    /**
     * 注册Smarty模板调用函数
     *
     * @param string function 模板调用函数的名称
     * @param string|array function 调用函数
     * @return Oray_View_Smarty
     */
    public function registFunction($function, $functionImpl)
    {
        $this->_smarty->registerPlugin('function', $function, $functionImpl);
        return $this;
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
            $this->_smarty->setTemplateDir($path);
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
            $this->_smarty->setCompileDir($path);
            return $this;
        }

        throw new Exception('Invalid path provided');
    }

    /**
     * Retrieve the current template directory
     *
     * @return array
     */
    public function getScriptPaths()
    {
        return $this->_smarty->getTemplateDir();
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
     * Alias for setScriptPath
     *
     * @param string $path
     * @param string $prefix Unused
     * @return Oray_View_Smarty
     */
    public function addBasePath($path, $prefix = 'Zend_View')
    {
        $this->_smarty->addTemplateDir($path);
        return $this;
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
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
         return (null !== $this->_smarty->getTemplateVars($key));
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
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
        $this->_smarty->clearAllAssign();
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

}