<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Seccode
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Oray
 * @package    Oray_Seccode
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Oray_Seccode_Abstract
{
    /**
     * 图片宽度，单位像素
     *
     * @var int
     */
    public $width       = Oray_Seccode::WIDTH_DEFAULT;

    /**
     * 图片高度，单位像素
     *
     * @var int
     */
    public $height      = Oray_Seccode::HEIGHT_DEFAULT;
    
    public $fontPath    = '';	 // TTF 字库目录
    public $dataPath    = '';    // 图片、声音、Flash 等数据目录
    
    /**
     * 验证的CODE
     *
     * @var string
     */
    protected $_code;

    /**
     * 编码
     *
     * @var string
     */
    protected $_charset;
    
    public function __construct($charset = 'utf-8', $config = null)
    {
        $this->_charset	= $charset;
        if (is_array($config)) {
            $this->setConfig($config);
        }
    }
    
    /**
     * 设置配置信息
     *
     * @param array $config
     * @return Oray_Seccode_Abstract
     */
    public function setConfig(array $config = array())
    {
        foreach ($config as $key => $val) {
            $this->{$key} = $val;
        }
        return $this;
    }
    
    /**
     * 显示验证码
     *
     * @param string $code
     * @return void
     */
    public function display($code) {
        $this->_code = $code;
        echo $this->_code;
    }
}