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
class UserController extends TuduX_Controller_OpenApi
{
    public function preDispatch()
    {
        // 用户未登录
        $actionName = $this->_request->getActionName();
        if (!$this->_user->isLogined() && $actionName != 'unregister-device') {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 获取当前登录用户信息
     */
    public function infoAction()
    {
        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        $avatars = $daoUser->getAvatars(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId));
        $dept    = $daoDept->getDepartment(array('orgid' => $this->_user->orgId, 'deptid' => $this->_user->deptId));

        $userInfo = $this->_user->toArray();

        $userInfo['groups'] = array($userInfo['deptid']);

        // action body
        $this->view->user = array(
            'truename'         => $userInfo['truename'],
            'position'         => $userInfo['position'],
            'gender'           => (int) $userInfo['gender'],
            'deptname'         => $dept->deptName,
            'mobile'           => $userInfo['mobile'],
            'tel'              => $userInfo['tel'],
            'email'            => $userInfo['username'],
            'isavatars'        => !empty($avatars['avatars']),
            'lastupdatetime'   => $userInfo['updatetime'],
            'avatarupdatetime' => $userInfo['updatetime']
        );
        $this->view->code = 0;
    }

    /**
     * 注册设备
     */
    public function registerDeviceAction()
    {
        $deviceToken = $this->_request->getQuery('device_token');

        /* @var $daoDevice Dao_Md_User_Device */
        $daoDevice = Tudu_Dao_Manager::getDao('Dao_Md_User_Device', Tudu_Dao_Manager::DB_MD);

        $deviceToken = str_replace(array('<', '>', ' '), array('', '', ''), $deviceToken);

        $ret = $daoDevice->createDevice(array(
            'orgid'       => $this->_user->orgId,
            'uniqueid'    => $this->_user->uniqueId,
            'devicetype'  => 'iOS',
            'devicetoken' => $deviceToken
        ));

        $this->view->code = 0;
    }

    /**
     *
     */
    public function unregisterDeviceAction()
    {
        $deviceToken = $this->_request->getQuery('device_token');

        /* @var $daoDevice Dao_Md_User_Device */
        $daoDevice = Tudu_Dao_Manager::getDao('Dao_Md_User_Device', Tudu_Dao_Manager::DB_MD);

        $ret = $daoDevice->deleteDevice(array(
            'devicetoken' => $deviceToken
        ));

        $this->view->code = 0;
    }

    /**
     *
     */
    public function saveSettingAction()
    {
        $setting = $this->_request->getParam('setting');

        $setting = json_decode($setting, true);
        if (!$setting) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
            $this->view->message = 'Parameter "setting" is not a valided JSON string';
            return ;
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoOption = Tudu_Dao_Manager::getDao('Dao_Md_User_Option', Tudu_Dao_Manager::DB_MD);

        $data = $daoOption->getOption(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId));

        if (null == $data) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'User had been delete';
            return ;
        }

        $settings = $data->settings;
        $settings['ios'] = $setting;

        $daoOption->updateOption($this->_user->orgId, $this->_user->userId, array('settings' => json_encode($settings)));

        $this->view->code = 0;
    }

}