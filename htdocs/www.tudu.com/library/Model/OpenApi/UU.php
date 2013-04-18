<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: UU.php 1911 2012-06-07 09:59:31Z cutecube $
 */

/**
 * @see Model_OpenApi_Abstract
 */
require_once 'Model/OpenApi/Abstract.php';

/**
 * OpenApi验证流程模型虚类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_OpenApi_UU extends Model_OpenApi_Abstract
{

    /**
     * 执行验证流程
     *
     * @param array $params
     */
    public static function auth(array $params)
    {
        if (empty($params['uu_id'])
            || empty($params['uu_sign'])
            || empty($params['license']))
        {
            require_once 'Model/OpenApi/Exception.php';
            throw new Model_OpenApi_Exception('Invalid params needed by auth api');
        }

        $uId     = $params['uu_id'];
        $sign    = $params['uu_sign'];
        $license = $params['license'];

        if ($sign != md5($uId . '&' . $license)) {
            require_once 'Model/OpenApi/Exception.php';
            throw new Model_OpenApi_Exception('Api parameters auth failed');
        }

        return true;
    }

    /**
     * 获取用户信息
     *
     * @param array $params
     * @return array
     */
    public static function getUserInfo(array $params)
    {
        if (empty($params['header'])
            || empty($params['uid']))
        {
            require_once 'Model/OpenApi/Exception.php';
            throw new Model_OpenApi_Exception('Invalid params needed by userinfo api');
        }

        require_once 'OpenApi/UU/AccountCtrl.php';

        /* @var $accountCtrl OpenApi_UU_AccountCtrl */
        $accountCtrl = new OpenApi_UU_AccountCtrl($params);

        $response = $accountCtrl->getByUid($params['uid']);

        if ($response->getHeader('status') != OpenApi_UU_Response::CODE_AUTH_PASS) {
            require_once 'Model/OpenApi/Exception.php';
            throw new Model_OpenApi_Exception('Api failure responsed');
        }

        //return $response;
        $data = $response->getData();
        return array(
            'uid'      => $params['uid'],
            'truename' => $data['name'],
            'orgname'  => !empty($data['comname']) ? $data['comname'] : $data['name'],
            'email'    => !empty($data['email']) ? $data['email'] : $data['mailaddress'],
            'mobile'   => $data['mobile'],
            'tel'      => $data['phone'],
            'username' => $data['username'],
            'nick'     => $data['nickname']
        );
    }
}