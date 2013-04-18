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
 * @version    $Id: Winic.php 9934 2012-04-25 01:12:51Z chenxujian $
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
class Oray_Sms_Winic extends Oray_Sms_Abstract
{
    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::send()
     */
    public function send(array $mobiles, $content)
    {
        $this->_setType(self::TYPE_SEND);

        $url = $this->_config['api'] . '/sys_port/gateway/';
        $param = array(
            'id'      => $this->_config['id'],
            'pwd'     => $this->_config['password'],
            'to'      => implode(',', $mobiles),
            'content' => iconv('UTF-8', "GB2312", $content),
            'time'    => ''
        );

        $this->_request($url, $param);

        return $this->isSuccess();
    }

    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::balance()
     */
    public function balance()
    {
        $this->_setType(self::TYPE_QUERY);

        $url = $this->_config['api'] . '/webservice/public/remoney.asp';
        $param = array(
            'uid' => $this->_config['id'],
            'pwd' => $this->_config['password']
        );

        $this->_request($url, $param);

        if ($this->isSuccess()) {
            return floor($this->_result[0] / 0.08);
        }

        return false;
    }

    /* (non-PHPdoc)
     * @see Oray_Sms_Abstract::isSuccess()
     */
    public function isSuccess()
    {
        return ($this->_code === '000');
    }

    /**
     * 发送请求
     *
     * @param string $url
     * @param array $data
     */
    private function _request($url, array $data)
    {
        $ch = curl_init();
        $data = http_build_query($data);
        $this->_request = $url . '?' . $data;

        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $header = array(
            'Accept: */*',
            //'Accept-Encoding: gzip, deflate',
            //'Connection: Keep-Alive'
        );
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $this->_responseText = (string) curl_exec($ch);
        curl_close($ch);

        $this->_parseResponse();
    }

    /**
     * 解析返回结果
     */
    private function _parseResponse()
    {
        $this->_message = $this->_responseText;
        $_result = explode('/', $this->_responseText);
        array_filter($_result);

        switch ($this->_type) {
            case self::TYPE_SEND:
                $this->_code = $_result[0];
                break;

            case self::TYPE_QUERY:
                $this->_code = ($_result[0] >= 0) ? '000' : $_result[0];
                break;

            default:
                break;
        }

        $this->_result[0] = array_shift($_result);
        foreach ($_result as $key => $value) {

            $arr = explode(':', $value, 2);

            if (empty($arr[0])) {
                continue ;
            }

            $k = $arr[0];
            $v = isset($arr[1]) ? $arr[1] : null;

            $this->_result[$k] = $v;
        }
    }
}
