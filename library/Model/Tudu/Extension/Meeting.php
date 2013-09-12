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
 * @version    $Id: Meeting.php 2821 2013-04-11 09:47:02Z chenyongfa $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';


/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Meeting extends Model_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    protected $_handlerClass = 'Model_Tudu_Extension_Handler_Meeting';

    /**
     *
     * @var array
     */
    protected $_attrs = array();


    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = null)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     *
     * @param string $name
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (empty($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $k => $val) {
            $this->setAttribute($k, $val);
        }

        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed  $value
     * @return Model_Tudu_Extension_Flow
     */
    public function setAttribute($key, $value)
    {
        $key = strtolower($key);

        $this->_attrs[$key] = $value;

        return $this;
    }

    /**
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     *
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->_handlerClass;
    }
}