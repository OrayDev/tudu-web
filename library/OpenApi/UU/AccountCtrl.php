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
 * @version    $Id: AccountCtrl.php 1889 2012-05-29 10:01:14Z cutecube $
 */

/**
 * @see OpenApi_UU_Abstract
 */
require_once 'OpenApi/UU/Abstract.php';

/**
 * UU 社区开放API实现，帐号控制部分
 * 类名与接口请求的 class相同
 * 方法名誉接口请求的method相同
 *
 * @category   OpenApi
 * @package    OpenApi_UU
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_UU_AccountCtrl extends OpenApi_UU_Abstract
{

    /**
     * 用户登录
     *
     * @param string $userName 帐号名
     * @param string $password 登录密码
     * @return OpenApi_UU_Response
     */
    public function login($userName, $password)
    {

    }

    /**
     * 通过用户ID获取信息
     *
     * @param string $uId 用户ID
     * @return OpenApi_UU_Response
     */
    public function getByUid($uId)
    {
        $headers = array_merge($this->_params['header'], array(
            'AppTime' => time(),
            'class'   => 'AccountCtrl',
            'method'  => 'getByUid'
        ));

        $response = $this->request(array('uid' => $uId), $headers);

        return $response;
    }
}