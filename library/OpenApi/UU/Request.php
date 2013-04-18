<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   OpenApi
 * @package    OpenApi_UU
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Request.php 1889 2012-05-29 10:01:14Z cutecube $
 */

/**
 * UU 社区用户开放API实现
 *
 * @category   OpenApi
 * @package    OpenApi_UU
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_UU_Request
{

    /**
     * 请求头信息
     * UU社区接口定义，XML <head /> 节以内的数据，大小写敏感
     * AppID    // AppID 从接口方获得
     * AppToken // App令牌 从接口方火的
     * AppCtid  // md5('infobird' . AppToken . AppID . AppTime) 一般不需指定，当前对象会自动算出
     * AppTime  // 请求时间
     * class    // 请求接口对象的类
     * method   // 请求接口对象调用方法
     *
     * @var array
     */
    protected $_header = array();

    /**
     * 参数列表
     *
     * @var array
     */
    protected $_parameter = array();

    /**
     * API Url
     *
     * @var string
     */
    protected static $_defaultUrl = 'http://openapi.yonyou.com/http/httpService.php';

    /**
     * 设置默认API Url
     *
     * @param string $url
     * @return void
     */
    public static function setDefaultUrl($url)
    {
        if (is_string($url)) {
            self::$_defaultUrl = $url;
        }
    }

    /**
     * 设置请求头数据
     *
     * @param mixed  $name
     * @param string $value
     * @return OpenApi_UU_Request
     */
    public function setHeader($name, $value = null)
    {
        if (null === $value && is_array($name)) {
            foreach ($name as $k => $v) {
                $this->_header[$k] = $v;
            }
        } else {
            if (is_string($name)) {
                $this->_header[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * 设置请求参数
     *
     * @param mixed  $name
     * @param string $value
     * @return OpenApi_UU_Request
     */
    public function setParameter($name, $value = null)
    {
        if (null === $value && is_array($name)) {
            foreach ($name as $k => $v) {
                $this->_parameter[$k] = $v;
            }
        } else {
            if (is_string($name)) {
                $this->_parameter[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * 发起请求
     *
     * @param string $url
     * @return OpenApi_UU_Response
     */
    public function request($url = null)
    {
        if (empty($this->_header['AppID'])
            || empty($this->_header['AppToken'])
            || empty($this->_header['AppTime'])
            || empty($this->_header['class'])
            || empty($this->_header['method']))
        {
            require_once 'OpenApi/UU/Exception.php';
            throw new OpenApi_UU_Exception('Missing parameters which API needful');
        }

        if (!isset($this->_header['AppCtid'])) {
            $this->_header['AppCtid'] = md5(
                'infobird' .
                $this->_header['AppToken'] .
                $this->_header['AppID'] .
                $this->_header['AppTime']
            );
        }

        $postData = array('xmlData' => $this->formatRequest());

        $url = !empty($url) ? $url : self::$_defaultUrl;

        $handler = curl_init();
        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handler, CURLOPT_HEADER, 0);
        curl_setopt($handler, CURLOPT_POST, 1);
        curl_setopt($handler, CURLOPT_POSTFIELDS, $postData);

        $data = curl_exec($handler);

        curl_close($handler);

        $xml = simplexml_load_string($data);

        if (false === $xml) {
            require_once 'OpenApi/UU/Exception.php';
            throw new OpenApi_UU_Exception('Invalid format of response data');
        }

        return $this->_formatResponse($xml);
    }

    /**
     * 格式化请求数据
     *
     * @return string
     */
    public function formatRequest()
    {
        $xml = '<?xml version="1.0" ?><msg><Head>';

        foreach ($this->_header as $name => $value) {
            $xml .= $this->_tagWrap($name, $value);
        }

        foreach ($this->_parameter as $name => $value) {
            $attrs = array(
                'name' => $name
            );

            if (is_array($value)) {
                $attrs['type'] = 'array';
            }

            $xml .= $this->_tagWrap('parameter', $value, $attrs);
        }

        $xml .= '</Head></msg>';

        return $xml;
    }

    /**
     * 使用xml标签包裹数据
     *
     * @param string $tagName
     * @param mixed  $value
     * @param array  $attrs
     * @return string
     */
    public function _tagWrap($tagName, $value, $attrs = null)
    {
        $tag = "<{$tagName}";
        if (is_array($attrs) && !empty($attrs)) {
            $array = array();
            foreach ($attrs as $name => $val) {
                $array[] = $name . '="' . $val . '"';
            }
            $tag .= ' ' . implode(' ', $array);
        }
        $tag .= '>' . $this->_formatTagValue($value) . "</{$tagName}>";

        return $tag;
    }

    /**
     * 格式化标签值
     *
     * @param mixed $value
     * @return string
     */
    public function _formatTagValue($value)
    {
        if (is_array($value)) {
            $elements = '<array>';
            foreach ($value as $k => $v) {
                $elements .= $this->_tagWrap('element', $v, array('name' => $k));
            }
            $elements .= '</array>';

            return $elements;
        }

        // should be wrapped by CDATA??!!
        return $value;
    }

    /**
     * 解析返回数据
     *
     * @param SimpleXMLElement $xml
     */
    protected function _formatResponse(SimpleXMLElement $xml)
    {
        $root = $xml->xpath('//Head');
        $body = $xml->xpath('//Head/body');

        $root = get_object_vars($root[0]);
        $body = get_object_vars($body[0]);

        $header = array();
        $data   = array();
        foreach ($root as $key => $value) {
            if ($key == 'body') {
                break;
            }

            $header[$key] = (string) $value;
        }

        foreach ($body as $key => $value) {
            $data[$key] = (string) $value;
        }

        require_once 'OpenApi/UU/Response.php';
        return new OpenApi_UU_Response($header, $data);
    }
}