<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Response.php 1351 2011-12-05 10:22:58Z cutecube $
 */

/**
 *
 * @author     CuTe_CuBe
 * @category   TuduX
 * @package    TuduX_App
 */
class TuduX_App_Response
{

    /**
     * 成功返回
     *
     * @var int
     */
    const CODE_SUCCESS = 0;

    /**
     * 未知问题
     * 应用未设置请求成功类型
     *
     * @var int
     */
    const CODE_UNKNOWN = -1;

    /**
     * 操作失败返回，（应用正常流程返回失败流程）
     *
     * @var int
     */
    const CODE_FAILURE = 1;

    /**
     * 丢失请求，（请求指向的处理方法不存在）
     *
     * @var int
     */
    const CODE_MISSING = 2;

    /**
     * 应用响应返回结果代码
     *
     * @var int
     */
    protected $_code = self::CODE_UNKNOWN;

    /**
     * 返回消息
     *
     * @var string
     */
    protected $_message = null;

    /**
     * 返回数据
     *
     * @var mixed
     */
    protected $_data = null;

    /**
     * 写入返回数据
     *
     * @param $key
     * @param $value
     * @return TuduX_App_Response
     */
    public function setData($key, $value = null)
    {
        if (is_string($key) && null !== $value) {
            $this->_data[$key] = $value;
        } else {
            $this->_data = $key;
        }

        return $this;
    }

    /**
     * 获取返回数据
     *
     * @param $key
     * @param $value
     */
    public function getData($key = null)
    {
        if ($key == null) {
            return $this->_data;
        }

        if (!isset($this->_data[$key])) {
            return null;
        }

        return $this->_data[$key];
    }

    /**
     * 设置返回代码
     *
     * @param int $code
     * @return TuduX_App_Response
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

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
     * 设置返回消息
     *
     * @param string $message
     * @return TuduX_App_Response
     */
    public function setMessage($message)
    {
        $this->_message = $message;
        return $this;
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
}