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
 * @version    $Id: Tudu.php 1970 2012-07-05 01:41:34Z cutecube $
 */

/**
 * 图度业务数据
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Entity_Tudu
{

    /**
     * 属性列表
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     * 图度附件列表
     *
     * @var array
     */
    protected $_attachments = array();

    /**
     * 图度扩展属性
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Constructor
     *
     * @param $params
     */
    public function __construct(array $params = null)
    {
        $this->_attrs = $params;
    }

    /**
     * 设置当前对象属性值
     *
     * @param string $name
     * @param mixed  $value
     * @return Tudu_Model_Tudu_Entity_Tudu
     */
    public function setAttribute($name, $value = null)
    {
        if (is_array($name) && null === $value) {
            foreach ($name as $key => $val) {
                $key = strtolower($key);
                $this->_attrs[$key] = $val;
            }
        } else if (is_string($name)) {

            $name = strtolower($name);

            $this->_attrs[$name] = $value;
        }

        return $this;
    }

    /**
     * 获取当前对象属性值
     *
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (!isset($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     * 获取属性类表
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     * 添加附件
     *
     * @param $fileId
     * @param $isAttachment
     */
    public function addAttachment($fileId, $isAttachment = true)
    {
        $this->_attachments[] = array(
            'fileid'   => $fileId,
            'isattach' => $isAttachment
        );

        if ($isAttachment) {
            if (!isset($this->_attrs['attachnum'])) {
                $this->_attrs['attachnum'] = 0;
            }

            $this->_attrs['attachnum'] += 1;
        }

        return $this;
    }

    /**
     * 获取附件列表
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     *
     * @param Tudu_Model_Tudu_Entity_Extension_Abstract $extension
     */
    public function addExtension(Tudu_Model_Tudu_Entity_Extension_Abstract $extension)
    {
        $this->_extensions[] = $extension;
        $extension->init($this);
    }

    /**
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensions;
    }

    /**
     * 直接访问属性
     *
     * @param $name
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     *
     * @param $name
     * @param $val
     */
    public function __set($name, $val)
    {
        return $this->setAttribute($name, $val);
    }

    /**
     *
     * @param $name
     * @param $val
     */
    public function __isset($name)
    {
        return isset($this->_attrs[$name]);
    }
}