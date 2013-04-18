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
 * @version    $Id: Tudu.php 2027 2012-07-25 09:49:02Z chenyongfa $
 */

/**
 * 图度业务数据
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Entity_Tudu extends Tudu_Model_Entity_Abstract
{

    /**
     *
     * @var array
     */
    protected $_recipients = array();

    /**
     *
     * @var array
     */
    protected $_removeAccepter = array();

    /**
     *
     * @var string
     */
    protected $_paramSuffix = '';

    /**
     *
     * @param array $recipients
     */
    public function setRecipients(array $recipients)
    {
        $this->_recipients = $recipients;

        return $this;
    }

    /**
     *
     * @param string $role
     */
    public function getRecipients($role = null)
    {
        return $this->_recipients;
    }

    /**
     *
     * @param array $array
     */
    public function setRemoveAccepters(array $array)
    {
        $this->_removeAccepter = $array;
    }

    /**
     *
     * @return array
     */
    public function getRemoveAccepters()
    {
        return $this->_removeAccepter;
    }

    /**
     *
     * @param $suffix
     */
    public function setParamSuffix($suffix)
    {
        $this->_paramSuffix = $suffix;
    }

    /**
     *
     * @param $suffix
     */
    public function getParamSuffix()
    {
        return $this->_paramSuffix;
    }

    /**
     *
     * @param string $property
     */
    public function isChanged($property)
    {
        if ((empty($this->_record[$property]) && !empty($this->_update[$property]))
            || (!empty($this->_record[$property]) && empty($this->_update[$property])))
        {
            return true;
        }

        if (in_array($property, array('to', 'cc'))) {
            $src = is_array($this->_record[$property]) ? array_keys($this->_record[$property]) : array();
            $dis = is_array($this->_update[$property]) ? array_keys($this->_update[$property]) : array();

            $srcCount = count($src);
            return count($dis) != $srcCount || count(array_uintersect($src, $dis, "strcasecmp")) != $srcCount;
        }

        return $this->_record[$property] != $this->_update[$property];
    }

    /**
     *
     * @param array $recipients
     */
    public static function formatReceiver($recipients)
    {
        if (is_string($recipients)) {
            return $recipients;
        }

        if (!is_array($recipients) || !$recipients) {
            return null;
        }

        $ret = array();
        foreach ($recipients as $key => $item) {
            $ret[] = $key . ' ' . $item['truename'];
        }

        return implode("\n", $ret);
    }
}