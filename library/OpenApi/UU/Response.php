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
 * @version    $Id: Response.php 1887 2012-05-28 10:02:33Z cutecube $
 */

/**
 * UU 社区用户开放API实现
 *
 * @category   OpenApi
 * @package    OpenApi_UU
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_UU_Response
{

    const CODE_FORMAT_ERROR   = 2001; // 格式错误
    const CODE_APPID_ERROR    = 2002; // AppID 错误
    const CODE_APPTOKEN_ERROR = 2003; // AppToken 错误

    const CODE_APPCTID_ERROR = 2004; // AppCtid 错误
    const CODE_APP_TIMEOUT   = 2005; // 请求超时，请求内容时间与服务器时间相差大于 30s

    const CODE_INVALID_CLASS   = 2006; // 请求类不存在
    const CODE_INVALID_METHOD  = 2007; // 方法无效或不存在
    const CODE_INVALID_PARAM   = 2008; // 无效参数
    const CODE_PARAMNAME_ERROR = 2009; // 参数名错误

    const CODE_FOBIDEN         = 2010; // 禁止访问 (IP)

    const CODE_AUTH_PASS       = 200; // 通过验证实

    /**
     * 接口返回头信息
     *
     * @var int
     */
    protected $_header;

    /**
     * 返回数据，/msg/Head/body 节内容
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param array $header
     * @param array $data
     */
    public function __construct($header = null, $data = null)
    {
        if (is_array($header)) {
            $this->_header = $header;
        }

        if (is_array($data)) {
            $this->_data = $data;
        }
    }

    /**
     * 获取响应头信息（指定）数据
     *
     * @param string $name
     * @return mixed
     */
    public function getHeader($name = null)
    {
        if (null === $name) {
            return $this->_header;
        }

        if (!array_key_exists($name, $this->_header)) {
            return null;
        }

        return $this->_header[$name];
    }

    /**
     * 获取相应返回数据（指定）内容
     *
     * @param string $name
     * @return mixed
     */
    public function getData($name = null)
    {
        if (null === $name) {
            return $this->_data;
        }

        if (!array_key_exists($name, $this->_data)) {
            return null;
        }

        return $this->_data[$name];
    }
}