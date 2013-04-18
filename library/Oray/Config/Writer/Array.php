<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Config
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Array.php 2793 2013-03-25 10:15:35Z chenyongfa $
 */

/**
 * @see Zend_Config_Writer_Array
 */
require_once 'Zend/Config/Writer/Array.php';

/**
 * @category   Oray
 * @package    Oray_Config
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Config_Writer_Array extends Zend_Config_Writer_Array
{
    /**
     * Keys of const
     *
     * @var array
     */
    private $_consts = array();

    /**
     * Set consts
     *
     * @param $const
     */
    public function setConst($const)
    {
        if (is_array($const)) {
            $this->_consts = array_merge($this->_consts, $const);
        } elseif (is_string($const)) {
            $this->_consts[] = $const;
        }
    }

    /**
     * Render a Zend_Config into a PHP Array config string.
     *
     * @since 1.10
     * @return string
     */
    public function render()
    {
        $data        = $this->_config->toArray();
        $sectionName = $this->_config->getSectionName();

        if (is_string($sectionName)) {
            $data = array($sectionName => $data);
        }

        $string = "<?php\n"
                . "return " . var_export($data, true) . ";\n";

        foreach ($this->_consts as $const) {
            if (false !== stripos($string, $const)) {
                $string = str_replace($const, "' . {$const} . '", $string);
            }
        }

        return $string;
    }

    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_integer($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (strpos($value, '"') === false) {
            $value = '"' . $value .  '"';
            foreach ($this->_consts as $const) {
                if (false !== stripos($value, $const)) {
                    $arr = explode($const, $value);
                    $value = implode('" ' . $const . ' "', $arr);
                    $value = str_replace('"" ', '', $value);
                }
            }
            return $value;

        } else {
            /** @see Zend_Config_Exception */
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('Value can not contain double quotes "');
        }
    }
}