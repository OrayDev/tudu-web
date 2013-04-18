<?php
/**
 * Oray_View
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Zend_View_Interface
 */
require_once "Zend/View/Interface.php";

/**
 * @see Oray_View_Formatter_Interface
 */
require_once "Oray/View/Format/Interface.php";

/**
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_View_Format implements Zend_View_Interface
{
    const OPT_SCRIPT_PATH = 'script_path';
    const OPT_BASE_PATH   = 'base_path';

    const FORMAT_JSON = 'json';
    const FORMAT_XML  = 'xml';

    /**
     * 注入的返回数据
     *
     * @var array
     */
    protected $_assigned = array();

    /**
     *
     * @var Oray_View_Format_Interface
     */
    protected $_formatter = null;

    /**
     * 当前接口返回数据格式
     *
     * @var string
     */
    protected $_format = self::FORMAT_JSON;

    /**
     * 当前对象设置项目
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return mixed
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Set the path to find the view script used by render()
     *
     * @param string|array The directory (-ies) to set as the path. Note that
     * the concrete view implentation may not necessarily support multiple
     * directories.
     * @return void
     */
    public function setScriptPath($path)
    {
        $this->_options[self::OPT_SCRIPT_PATH] = $path;

        return $this;
    }

    /**
     * Retrieve all view script paths
     *
     * @return array
     */
    public function getScriptPaths()
    {
        return array();
    }

    /**
     * Set a base path to all view resources
     *
     * @param  string $path
     * @param  string $classPrefix
     * @return void
     */
    public function setBasePath($path, $classPrefix = 'Captcha_View')
    {
        $this->_options[self::OPT_BASE_PATH] = array(
            array('path' => $path, 'classpfx' => $classPrefix)
        );

        return $this;
    }

    /**
     * Add an additional path to view resources
     *
     * @param  string $path
     * @param  string $classPrefix
     * @return void
     */
    public function addBasePath($path, $classPrefix = 'Oray_View')
    {
        $this->_options[self::OPT_BASE_PATH][] = array(
            'path' => $path, 'classpfx' => $classPrefix
        );

        return $this;
    }

    /**
     * Assign a variable to the view
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        $this->assign($key, $val);
    }

    /**
     * Allows testing with empty() and isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_assigned[$key]);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->_assigned[$key]);
    }

    /**
     * Assign variables to the view script via differing strategies.
     *
     * Suggested implementation is to allow setting a specific key to the
     * specified value, OR passing an array of key => value pairs to set en
     * masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or array of key
     * => value pairs)
     * @param mixed $value (Optional) If assigning a named variable, use this
     * as the value.
     * @return void
     */
    public function assign($spec, $value = null)
    {
        if (is_array($spec) && null === $value) {
            foreach ($spec as $key => $val) {
                $this->_assigned[$key] = $val;
            }

            return $this;
        }

        if (is_string($spec)) {
            $this->_assigned[$spec] = $value;
            return $this;
        }

        /**
         * @see Oray_View_Exception
         */
        require_once 'Oray/Exception.php';
        throw new Oray_Exception("Invalid params of spec for assign method");
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
        $this->_assigned = array();
    }

    /**
     * 设置当前接口返回的数个格式
     *
     * @param string $format
     */
    public function setResponseFormat($format)
    {
        $this->_format = $format;

        return $this;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        $formatter = $this->getFormatter($this->_format);

        $options = array_merge($this->_options, array(
            'scriptname' => $name
        ));

        echo $formatter->format($this->_assigned, $options);
    }

    /**
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->getFormatter($this->_format)->getContentType();
    }

    /**
     * 返回对应格式的格式化对象
     *
     * @param unknown_type $format
     */
    protected function getFormatter($format = self::FORMAT_JSON)
    {
        $className = 'Oray_View_Format_' . ucfirst($format);

        if (null === $this->_formatter || get_class($this->_formatter) != $className) {
            Zend_Loader::loadClass($className);

            $this->_formatter = new $className();
        }

        return $this->_formatter;
    }
}