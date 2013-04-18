<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 * @category   TuduX
 * @package    TuduX_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see OpenApi_OAuth_Grant_Abstract
 */
require_once 'OpenApi/OAuth/Grant/Abstract.php';

/**
 * @see OpenApi_OAuth_OAuth
 */
require_once 'OpenApi/OAuth/OAuth.php';

/**
 * @category   TuduX
 * @package    TuduX_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_OAuth_Grant_RefreshToken extends OpenApi_OAuth_Grant_Abstract
{

    /**
     * 用户名密码验证流程
     *
     * @param array $inputData
     */
    public function grant(array $inputData)
    {
        if (empty($inputData[OpenApi_OAuth_OAuth::PARAM_REFRESH_TOKEN]))
        {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid parameter \"refresh_token\" for grant accessToken", "refresh_access_token_failed");
        }

        $oauth = $this->getOAuthInstance();

        $token = $oauth->getStorage()->getRefreshToken($inputData[OpenApi_OAuth_OAuth::PARAM_REFRESH_TOKEN]);

        // 找不到令牌信息 ， 客户端标识不一致，刷新令牌过期
        if (empty($token)
            || $token[OpenAPI_OAuth_OAuth::PARAM_CLIENT_ID] != $inputData[OpenAPI_OAuth_OAuth::PARAM_CLIENT_ID]
            || $token[OpenAPI_OAuth_OAuth::PARAM_EXPIRES] < time())
        {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Refresh accessToken failed", "refresh_access_token_failed");
        }

        // 删除原刷新令牌
        $oauth->getStorage()->unsetRefreshToken($inputData[OpenApi_OAuth_OAuth::PARAM_REFRESH_TOKEN]);

        return $token;
    }
}