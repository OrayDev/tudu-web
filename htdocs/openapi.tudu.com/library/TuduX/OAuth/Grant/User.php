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
 * @see Tudu_Auth_Adapter_User
 */
require_once 'Tudu/Auth/Adapter/User.php';

/**
 * @see OpenApi_OAuth_OAuth
 */
require_once 'OpenApi/OAuth/OAuth.php';

/**
 * @category   TuduX
 * @package    TuduX_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class TuduX_OAuth_Grant_User extends OpenApi_OAuth_Grant_Abstract
{

    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     * 用户名密码验证流程
     *
     * @param array $inputData
     */
    public function grant(array $inputData)
    {
        if (empty($inputData[OpenApi_OAuth_OAuth::PARAM_USERNAME])
            || empty($inputData[OpenApi_OAuth_OAuth::PARAM_PASSWORD]))
        {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid parameter for grant accessToken by type \"password\"", OpenApi_OAuth_OAuth::ERROR_INVALID_REQUEST);
        }

        try {
            $auth =new Tudu_Auth_Adapter_User(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD));
            $auth->setUsername($inputData[OpenApi_OAuth_OAuth::PARAM_USERNAME])
                 ->setPassword($inputData[OpenApi_OAuth_OAuth::PARAM_PASSWORD]);

            $result = $auth->authenticate();
        } catch (Tudu_Auth_Adapter_Exception $e) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Authorize failed", OpenApi_OAuth_OAuth::ERROR_INVALID_REQUEST);
        }

        if (!$result->isValid()) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Authorize failed", OpenApi_OAuth_OAuth::ERROR_INVALID_REQUEST);
        }

        $identity = $result->getIdentity();
        $identity['logintime'] = time();

        return array(
            OpenApi_OAuth_OAuth::PARAM_USER_ID => $inputData[OpenApi_OAuth_OAuth::PARAM_USERNAME],
            OpenApi_OAuth_OAuth::PARAM_SCOPE   => null,
            'auth' => $identity
        );
    }
}