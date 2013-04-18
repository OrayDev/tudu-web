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
 * @version    $Id: Abstract.php 1889 2012-05-29 10:01:14Z cutecube $
 */

/**
 * UU 社区开放API虚类
 *
 * @category   OpenApi
 * @package    OpenApi_UU
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_UU_Abstract
{

    /**
     *
     * @var array
     */
    protected $_params;

    /**
     * Construct
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        if (is_array($params) || !empty($params)) {
            $this->_params = $params;
        }
    }

    /**
     * 获取请求对象
     *
     * @param array $params
     * @param array $headers
     */
    public function request($params = null, $headers = null)
    {
        require_once 'OpenApi/UU/Request.php';

        $request = new OpenApi_UU_Request();

        $response = $request->setHeader($headers)
                            ->setParameter($params)
                            ->request();

        return $response;
    }
}