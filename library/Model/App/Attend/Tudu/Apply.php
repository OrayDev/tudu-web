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
 * @version    $Id: Tudu.php 2070 2012-08-22 09:37:26Z cutecube $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Model/Tudu/Extension/Abstract.php';


/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_App_Attend_Tudu_Apply extends Model_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    protected $_handlerClass = 'Model_App_Attend_Tudu_Handler_Apply';

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
            if ($k == 'steps') {
                $this->_steps = $val;
                continue ;
            }

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

        if ($key == 'steps') {
            $this->_steps = $value;

            return $this;
        }

        $this->_attrs[$key] = $value;

        return $this;
    }

    /**
     *
     * @param string $name
     */
    public function __get($name)
    {
        if ($name == 'steps') {
            return $this->_steps;
        }
        return $this->getAttribute($name);
    }

    /**
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        if ($name == 'steps') {
            return $this->_steps = $value;
        }
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