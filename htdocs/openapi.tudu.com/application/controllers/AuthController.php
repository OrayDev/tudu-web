<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */


/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class AuthController extends TuduX_Controller_OpenApi
{
    /**
     * 验证操作
     */
    public function authorizeAction()
    {
        $grantType = $this->_request->getParam('grant_type');
        $memcache  = $this->_bootstrap->getResource('memcache');

        try {
            $storage = new TuduX_OAuth_Storage_Session();
            $storage->setMemcache($memcache);

            $oauth = new OpenApi_OAuth_OAuth(array(
                OpenApi_OAuth_OAuth::STORAGE => $storage
            ));

            $oauth->setGrantClass(OpenApi_OAuth_OAuth::GRANT_TYPE_USER_CREDENTIALS, 'TuduX_OAuth_Grant_User');

            $params = array(
                OpenApi_OAuth_OAuth::PARAM_GRANT_TYPE    => $grantType,
                OpenApi_OAuth_OAuth::PARAM_CLIENT_ID     => $this->_request->getParam(OpenApi_OAuth_OAuth::PARAM_CLIENT_ID),
                OpenApi_OAuth_OAuth::PARAM_CLIENT_SECRET => $this->_request->getParam(OpenApi_OAuth_OAuth::PARAM_CLIENT_SECRET),
                OpenApi_OAuth_OAuth::PARAM_SCOPE         => $this->_request->getParam(OpenApi_OAuth_OAuth::PARAM_SCOPE),
            );

            switch ($grantType) {
                case OpenApi_OAuth_OAuth::GRANT_TYPE_USER_CREDENTIALS:
                    $params[OpenApi_OAuth_OAuth::PARAM_USERNAME] = $this->_request->getParam('username');
                    $params[OpenApi_OAuth_OAuth::PARAM_PASSWORD] = $this->_request->getParam('password');
                    break;
                case OpenApi_OAuth_OAuth::GRANT_TYPE_REFRESH_TOKEN:
                    $params[OpenApi_OAuth_OAuth::PARAM_REFRESH_TOKEN] = $this->_request->getParam('refresh_token');
                    break;
            }

            $assign = $oauth->grantAccessToken($params);
            $token  = $oauth->getStorage()->getAccessToken($assign['access_token']);

            // 获取用户设置
            /* @var $daoUser Dao_Md_User_User */
            $daoOption = Tudu_Dao_Manager::getDao('Dao_Md_User_Option', Tudu_Dao_Manager::DB_MD);
            $data = $daoOption->getOption(array('orgid' => $token['auth']['orgid'], 'userid' => $token['auth']['userid']));

            if (!empty($data->settings['ios'])) {
                $assign['setting'] = $data->settings['ios'];
            } else {
                $assign['setting'] = array(
                    'push' => array('task' => 1, 'discuss' => 1, 'notice' => 1, 'meeting' => 1)
                );
            }

            $this->view->assign($assign);

        } catch (OpenApi_OAuth_Exception $e) {

            //$this->getResponse()->setHttpResponseCode(401);

            $this->view->code              = TuduX_OpenApi_ResponseCode::AUTHORIZE_FAILED;
            $this->view->error             = $e->getError();
            $this->view->error_description = $e->getDescription();
        }
    }

    /**
     * /auth/token
     */
    public function tokenAction()
    {
        return $this->authorizeAction();
    }
}