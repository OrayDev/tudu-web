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
 * @version    $Id: Montnets.php 9934 2012-04-25 01:12:51Z chenxujian $
 */

/**
 * @see Oray_Sms_Abstract
 */
require_once 'Oray/Sms/Abstract.php';

/**
 * @category   Oray
 * @package    Oray_Sms
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Sms_Montnets extends Oray_Sms_Abstract
{
    /**
     * default config
     *
     * @var array
     */
    protected $_config = array(
        'uri' => 'http://tempuri.org/'
    );

    /**
     * soap client
     *
     * @var SoapClient
     */
    private $_soapClient;

    /**
     * 状态
     *
     * @var boolean;
     */
    private $_status;


    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::send()
     */
    public function send(array $mobiles, $content)
    {
        $this->_setType(self::TYPE_SEND);

        $params = array(
            'userId'     => $this->_config['userid'],
            'password'   => $this->_config['password'],
            'pszMobis'   => implode(',', $mobiles), //iconv('UTF-8', "GB2312", $content),
            'pszMsg'     => $content,
            'iMobiCount' => count($mobiles),
            'pszSubPort' => '*'
        );

        $this->_request('MongateCsSpSendSmsNew', $params);

        return $this->isSuccess();
    }


    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::balance()
     */
    public function balance()
    {
        $this->_setType(self::TYPE_QUERY);

        $params = array(
            'userId'     => $this->_config['userid'],
            'password'   => $this->_config['password']
        );

        $result = $this->_request('MongateQueryBalance', $params);

        if ($this->isSuccess()) {
            return (int) $this->_result;
        }

        return false;
    }

    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::isSuccess()
     */
    public function isSuccess()
    {
        return $this->_status;
    }

    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::_setType()
     */
    protected function _setType($type = null)
    {
        parent::_setType($type);

        $this->_status = null;
    }


    /**
     * 发送请求
     *
     * @param string $method
     * @param array $params
     * @return string
     */
    private function _request($method, array $params)
    {
        $client = $this->_getSoapClient();

        $this->_result = $client->__soapCall($method, $this->_toSoapParam($params));

        $this->_request = $client->__getLastRequest();
        $this->_responseText = $client->__getLastResponse();

        $this->_parseResponse();
    }

    /**
     * 解析返回结果
     */
    private function _parseResponse()
    {
        $this->_code = $this->_result;

        switch ($this->_type) {
            case self::TYPE_SEND:
                $this->_status = (strlen($this->_result) >= 20 && abs(intval($this->_result)) > 999);
                break;

            case self::TYPE_QUERY:
                $this->_status = ($this->_result >= 0);
                break;

            default:
                $this->_status = false;
                break;
        }
    }

    /**
     * 获取Soap客户端
     *
     * @return SoapClient
     */
    private function _getSoapClient()
    {
        if (!$this->_soapClient) {

            $this->_soapClient = new SoapClient(null, array(
                'location' => $this->_config['wsdl'],
                'uri'      => $this->_config['uri'],
                'trace'    => true,
            ));
        }

        return $this->_soapClient;
    }

    /**
     * 转换参数为Soap参数
     *
     * @param array $params
     * @return array
     */
    private function _toSoapParam(array $params)
    {
        $ret = array();
        foreach ($params as $key => $value) {
            $ret[] = new SoapParam($value, $key);
        }
        return $ret;
    }
}
