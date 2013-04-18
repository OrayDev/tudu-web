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
 * @version    $Id: Tudu.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * 图度数据储存对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Storage_Tudu
{

    /**
     *
     * @var array
     */
    private $_attrs = array();

    /**
     *
     * @var array
     */
    private $_ext = array();

    /**
     *
     * @var boolean
     */
    private $_isFromTudu = false;

    /**
     *
     * @var Dao_Td_Tudu_Record_Tudu
     */
    private $_fromTudu = null;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct(array $params, & $fromTudu = null)
    {
        $this->setAttributes($params);

        $this->_fromTudu = $fromTudu;
    }

    /**
     * 是否来自已有记录
     *
     * @return boolean
     */
    public function isFromTudu()
    {
        return null !== $this->_fromTudu;
    }

    /**
     *
     */
    public function getFromTudu()
    {
        return $this->_fromTudu;
    }

    /**
     *
     * @return boolean
     */
    public function isDraft()
    {
        return null === $this->_fromTudu || $this->_fromTudu->isDraft;
    }

    /**
     * 某字段是否变更
     */
    public function isChange($key)
    {
        if (!$this->isFromTudu()) {
            return true;
        }

        if (!isset($this->_attrs[$key])) {
            return false;
        }

        if (in_array($key, array('to', 'cc', 'bcc'))) {
            $dis = is_array($this->_attrs[$key]) ? array_keys($this->_attrs[$key]) : array();
            $src = is_array($this->_fromTudu->{$key}) ? array_keys($this->_fromTudu->{$key}) : array();

            $srcCount = count($src);
            return count($dis) != $srcCount || count(array_uintersect($src, $dis, "strcasecmp")) != $srcCount;
        }

        return $this->_attrs[$key] != $this->_fromTudu->{$key};
    }

    /**
     * 设置字段数据
     *
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value)
    {
        $name = strtolower($name);

        if (in_array($name, array('to', 'cc', 'bcc', 'reviewer')) && is_string($value)) {
            $value = Tudu_Tudu_Storage::formatRecipients($value);
        }

        $this->_attrs[$name] = $value;
    }

    /**
     *
     * @param $params
     */
    public function setAttributes(array $params)
    {
        foreach ($params as $key => $val) {
            $this->setAttribute($key, $val);
        }

        return $this;
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
     * @return mixed
     */
    public function getAttribute($name)
    {
        if (!isset($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $name = strtolower($name);

        return $this->getAttribute($name);
    }
}