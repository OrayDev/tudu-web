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
 * @version    $Id: Tudu.php 2196 2012-10-10 01:48:56Z cutecube $
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
class Model_Tudu_Post
{
    /**
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     *
     * @var array
     */
    protected $_attachments = array();

    /**
     *
     * @var array
     */
    protected $_extensions = array();

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
     * @param value $value
     */
    public function setAttribute($name, $value)
    {
        $name = strtolower($name);

        if ($name == 'tuduid') {
            if (!empty($this->_attrs[$name])) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Disabled to change the id of exists tudu');
            }
        }

        $this->_attrs[$name] = $value;

        return $this;
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
     * @param string  $fileId
     * @param boolean $isAttach
     * @param boolean $isNetdisk
     * @return Model_Tudu_Tudu
     */
    public function addAttachment($fileId, $isAttach = true, $isNetdisk = false)
    {
        $this->_attachments[] = array(
            'fileid'    => $fileId,
            'isattach'  => $isAttach,
            'isnetdisk' => $isNetdisk
        );

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     *
     * @return multitype:
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     *
     * @return Ambigous <multitype:, multitype:>
     */
    public function getStorageParams()
    {
        $params = $this->getAttributes();

        foreach (array('to', 'cc', 'bcc') as $key) {
            if (!isset($params[$key])) {
                continue ;
            }

            $arr  = array();
            $item = $params[$key];
            if (!empty($item) && is_array($item)) {
                foreach ($item as $i) {
                    //$arr[] = $i['']
                    if (isset($i['groupid'])) {
                        $arr[] = $i['groupid'] . ' ' . $i['truename'];
                    } else {
                        $arr[] = $i['email'] . ' ' . $i['truename'];
                    }
                }
            }

            $params[$key] = implode("\n", $arr);
        }

        return $params;
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
}