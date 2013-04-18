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
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @category   Oray
 * @package    Oray_Seccode
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Seccode
{
    // 图片验证码
    const TYPE_IMAGE = 1;

    // Flash验证码
    const TYPE_FLASH = 2;

    // 语音验证码
    const TYPE_VOICE = 3;

    // 默认宽度
    const WIDTH_DEFAULT  = 150;

    // 默认高度
    const HEIGHT_DEFAULT = 60;
    
    // 默认字符数
    const LENGTH_DEFAULT = 4;

    // 最大宽度
    const WIDTH_MAX      = 200;

    // 最大高度
    const HEIGHT_MAX     = 80;

    /**
     * Default session namespace
     */
    const NAMESPACE_DEFAULT = 'ORAY';

    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'SECCODE';

    /**
     * Singleton instance
     *
     * @var Auth
     */
    protected static $_instance = null;

    /**
     * Object to proxy $_SESSION storage
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;

    /**
     * 保存所有的验证码
     *
     * @var array
     */
    private $_codes;

    /**
     * 字符编码
     *
     * @var string
     */
    private $_charset = 'utf-8';

    /**
     * 生成验证码的类型
     *
     * @var int
     */
    private $_type;

    /**
     * 生成验证码的种子
     *
     * @var string
     */
    private $_units = 'BCEFGHJKMPQRTVWXY2346789';

    /**
     * 默认验证码名称，支持多个验证码同时显示
     *
     * @var string
     */
    private $_name  = 'default';

    /**
     * 图片验证码生成类
     *
     * @var Oray_Seccode_Image
     */
    private $_image = null;

    /**
     * Flash验证码生成类
     *
     * @var Oray_Seccode_Flash
     */
    private $_flash = null;

    /**
     * 声音验证码生成类
     *
     * @var Oray_Seccode_Voice
     */
    private $_voice = null;

    /**
     * 字体保存目录
     *
     * @var string
     */
    public static $fontPath = '/var/www/data/fonts';

    /**
     * 资源文件保存目录
     *
     * @var string
     */
    public static $dataPath = '/var/www/data/seccode';

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
        $this->_member  = self::MEMBER_DEFAULT;
        $this->_session = new Zend_Session_Namespace(self::NAMESPACE_DEFAULT);
        $this->_codes   = $this->_session->{$this->_member};
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {}

    /**
     * Returns an instance of Oray_Seccode
     *
     * Singleton pattern implementation
     *
     * @return Oray_Seccode Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Enter description here...
     *
     * @return Oray_Seccode
     */
    public function setConfig($config)
    {
        if (isset($config['fontPath'])) {
            self::$fontPath = $config['fontPath'];
        }

        if (isset($config['dataPath'])) {
            self::$dataPath = $config['dataPath'];
        }

        if (isset($config['units'])) {
            $this->_units = $config['units'];
        }
        
        if (isset($config['image'])) {
            unset($config['image']['fontPath']);
            unset($config['image']['dataPath']);
            $this->getImage()->setConfig($config['image']);
        }

        return $this;
    }

    /**
     * 显示验证码
     *
     * @param string $type   验证码显示的类型
     * @param int    $length 字符长度
     * @param string $name   验证码的名称
     */
    public function display($type = self::TYPE_IMAGE, $length = self::LENGTH_DEFAULT, $name = null)
    {
        $code = $this->_creteCode($length);
        $this->_setCode($code, $name);

        if ($type == self::TYPE_IMAGE) {
            $this->getImage()->display($code);
        }

        if ($type == self::TYPE_FLASH) {
            $this->getFlash()->display($code);
        }

        if ($type == self::TYPE_VOICE) {
            $this->getVoice()->display($code);
        }
    }

    /**
     * 获取Oray_Seccode_Image对像
     *
     * @return Oray_Seccode_Image
     */
    public function getImage()
    {
        if (null == $this->_image) {
            /**
             * @see Oray_Seccode_Image
             */
            require_once 'Oray/Seccode/Image.php';
            $this->_image = new Oray_Seccode_Image($this->_charset, array(
                'fontPath' => self::$fontPath,
                'dataPath' => self::$dataPath
            ));
        }
        return $this->_image;
    }

    /**
     * 获取Oray_Seccode_Flash对像
     *
     * @return Oray_Seccode_Flash
     */
    public function getFlash()
    {
        if (null == $this->_flash) {
            /**
             * @see Oray_Seccode_Flash
             */
            require_once 'Oray/Seccode/Flash.php';
            $this->_flash = new Oray_Seccode_Flash($this->_charset);
        }
        return $this->_flash;
    }

    /**
     * 获取Oray_Seccode_Voice对像
     *
     * @return Oray_Seccode_Voice
     */
    public function getVoice()
    {
        if (null == $this->_voice) {
            /**
             * @see Oray_Seccode_Voice
             */
            require_once 'Oray/Seccode/Voice.php';
            $this->_voice = new Oray_Seccode_Voice($this->_charset, array(
                'fontPath' => self::$fontPath,
                'dataPath' => self::$dataPath
            ));
        }
        return $this->_voice;
    }

    /**
     * 生成随机验证字符串
     *
     * @param int $length
     * @return void
     */
    private function _creteCode($length)
    {
        $units = $this->_units;
        $max = mb_strlen($units, $this->_charset) - 1;
        $seccode = '';
        for($i = 0; $i < $length; $i++) {
            $seccode .= mb_substr($units, rand(0, $max), 1, $this->_charset);
        }
        return $seccode;
    }

    /**
     * 获取验证码
     *
     * @param string $name
     */
    public function getCode($name = null)
    {
        if (null === $name) {
            $name = $this->_name;
        }
        if (isset($this->_codes[$name])) {
            return $this->_codes[$name];
        }
        return null;
    }

    /**
     * 保存验证码
     *
     * @param string $name 验证码名称
     * @return Oray_Seccode
     */
    private function _setCode($code, $name = null)
    {
        if (null === $name) {
            $name = $this->_name;
        }
        $this->_codes[$name] = $code;
        $this->_session->{$this->_member} = $this->_codes;
    }

    /**
     * 判断验证码是否有效
     *
     * @param string $value  需要验证的值
     * @param string $name   验证码的名称
     * @param boolean $clear 是否清除
     * @return boolean
     */
    public static function isValid($value, $name = null, $clear = false)
    {
        $code = self::getInstance()->getCode($name);
        if ($clear) {
            self::getInstance()->_clear($name);
        }
        return (strcasecmp($code, $value) === 0);
    }

    /**
     * 清除验证码
     *
     * @param string $name  验证码的名称
     * @return void
     */
    public static function clear($name = null)
    {
        self::getInstance()->_clear($name);
    }

    /**
     * 清除保存的SESSION信息
     *
     * @param string $name 验证码的名称
     */
    private function _clear($name)
    {
        if (null === $name) {
            $this->_codes = null;
            unset($this->_session->{$this->_member});
        } else {
            unset($this->_codes[$name]);
            $this->_session->{$this->_member} = $this->_codes;
        }
    }
}
