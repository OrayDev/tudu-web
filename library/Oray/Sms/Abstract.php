<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Sms
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 11519 2012-08-28 03:33:34Z chenxujian $
 */

/**
 * @category   Oray
 * @package    Oray_Sms
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Oray_Sms_Abstract
{
    const TYPE_SEND  = 'send';
    const TYPE_QUERY = 'query';

    /**
     * 配置
     *
     * @var array
     */
    protected $_config = array();

    /**
     * 不可以修改配置项
     *
     * @var array
     */
    protected $_skipConfig = array();

    /**
     * 请求信息
     *
     * @var string
     */
    protected $_request;

    /**
     * 响应信息
     *
     * @var string
     */
    protected $_responseText;

    /**
     * 查询类型
     *
     * @var string
     */
    protected $_type;

    /**
     * 返回代码
     *
     * @var int
     */
    protected $_code;

    /**
     * 返回信息
     *
     * @var string
     */
    protected $_message;

    /**
     * 格式化后的结果
     *
     * @var mixed
     */
    protected $_result;

    /**
     * sms factory
     *
     * @throws Oray_Sms_Exception
     *
     * @param string $adapter
     * @param array $config
     * @return Oray_Sms_Abstract
     */
    public static function factory($adapter, array $config)
    {
        switch ($adapter) {

            case 'winic':
                require_once 'Oray/Sms/Winic.php';
                return new Oray_Sms_Winic($config);
                break;

            case 'montnets':
                require_once 'Oray/Sms/Montnets.php';
                return new Oray_Sms_Montnets($config);
                break;

            default:
                throw new Oray_Sms_Exception("Undefine Oray Sms Adapter [{$adapter}]");
                break;
        }
    }

    /**
     * construct
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * 设置
     *
     * @param array $config
     * @return Oray_Registrar_Abstract
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (in_array(strtolower($key), $this->_skipConfig)) {
                continue;
            }

            $method = 'set' . strtolower($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        $this->_config = array_merge($this->_config, $config);

        return $this;
    }

    /**
     * 发送信息
     *
     * @abstract
     * @param array $mobiles
     * @param string $content
     * @return boolean
     */
    abstract public function send(array $mobiles, $content);

    /**
     * 查询账户信息
     *
     * @abstract
     * @return int|false
     */
    abstract public function balance();

    /**
     * 操作是否成功
     *
     * @abstract
     * @return boolean
     */
    abstract public function isSuccess();


    /**
     * 获取返回代码
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * 获取返回信息
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * 返回格式化后的结果
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * 获取请求信息
     *
     * @return string
     */
    public function getRquestText()
    {
        return $this->_request;
    }

    /**
     * 获取响应信息
     *
     * @return string
     */
    public function getResponseText()
    {
        return $this->_responseText;
    }

    /**
     * 设置操作类型 (清空上次操作数据)
     *
     * @param string $type
     */
    protected function _setType($type = null)
    {
        $this->_type = $type;

        $this->_code    = null;
        $this->_message = null;
        $this->_result  = null;
        $this->_request = null;
        $this->_responseText = null;
    }
}