<?php
/**
 * 图度操作管理
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: TuduMgrController.php 2185 2012-09-29 07:02:46Z chenyongfa $
 */

/**
 * @see Tudu_AddressBook
 */
require_once 'Tudu/AddressBook.php';

class ComposeController extends TuduX_Controller_OpenApi
{

    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     *
     */
    public function saveAction()
    {
        $post = $this->_request->getPost();

        require_once 'Model/Tudu/Tudu.php';
        $tudu = new Model_Tudu_Tudu();

        $this->_formatParams($tudu, $post);

        $tudu->setAttributes(array(
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'email'    => $this->_user->userName,
            'from'     => $this->_user->userName . ' ' . $this->_user->trueName,
            'poster'   => $this->_user->trueName
        ));

        try {
            /* @var $modelCompose Model_Tudu_Compose_Save */
            $modelCompose = Tudu_Model::factory('Model_Tudu_Compose_Save');

            $params = array(&$tudu);

            $modelCompose->execute('compose', $params);

        } catch (Model_Tudu_Exception $e) {
            throw new TuduX_OpenApi_Exception('Tudu save failed', TuduX_OpenApi_ResponseCode::TUDU_SAVE_FAILED);
        }

        $this->view->tuduid = $tudu->tuduId;
        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     *
     */
    public function sendAction()
    {
        $post = $this->_request->getPost();

        require_once 'Model/Tudu/Tudu.php';
        $tudu = new Model_Tudu_Tudu();

        $this->_formatParams($tudu, $post);

        $tudu->setAttributes(array(
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'email'    => $this->_user->userName,
            'from'     => $this->_user->userName . ' ' . $this->_user->trueName,
            'poster'   => $this->_user->trueName,
            'header'   => array(
                'client-type' => 'iOS'
            ),

            'operation' => 'send'
        ));

        try {
            /* @var $modelCompose Model_Tudu_Compose_Send */
            $modelCompose = Tudu_Model::factory('Model_Tudu_Compose_Send');

            $params = array(&$tudu);

            $modelCompose->execute('compose', $params);

            $config  = $this->_bootstrap->getOption('httpsqs');
            $tuduconf = $this->_bootstrap->getOption('tudu');

            $sendType  = isset($tuduconf['send']) ? ucfirst($tuduconf['send']['class']) : 'Common';
            $sendClass = 'Model_Tudu_Send_' . $sendType;

            $modelSend = new $sendClass(array('httpsqs' => $config));
            $modelSend->send(&$tudu);

            /*$httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

            $action = !$tudu->fromTudu || !$tudu->fromTudu->isDraft ? 'create' : 'update';
            $sqsParam = array(
                'tsid'        => $this->_user->tsId,
                'tuduid'      => $tudu->tuduId,
                'from'        => $this->_user->userName,
                'uniqueid'    => $this->_user->uniqueId,
                'server'      => $this->_request->getServer('HTTP_HOST'),
                'type'        => $tudu->type,
                'ischangedCc' => ($action == 'update' && $tudu->cc) ? (boolean) $tudu->cc : false
            );

            if ($tudu->flowId && $action == 'create') {
                $sqsParam['nstepid'] = $tudu->stepId;
                $sqsParam['flowid']  = $tudu->flowId;
            }

            $httpsqs->put(implode(' ', array(
                'tudu',
                $action,
                '',
                http_build_query($sqsParam)
            )), 'tudu');*/

        } catch (Model_Tudu_Exception $e) {
            $code = TuduX_OpenApi_ResponseCode::TUDU_SEND_FAILED;

            switch ($e->getCode()) {
                case Model_Tudu_Exception::TUDU_NOTEXISTS:
                case Model_Tudu_Exception::BOARD_NOTEXISTS:
                    $code = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
                    break;
                case Model_Tudu_Exception::PERMISSION_DENIED:
                    $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                    break;
            }
            throw new TuduX_OpenApi_Exception('Tudu send failed', $code);
        }

        $this->view->tuduid = $tudu->tuduId;
        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 转发图度
     */
    public function forwardAction()
    {
        $post = $this->_request->getPost();

        require_once 'Model/Tudu/Tudu.php';
        $tudu = new Model_Tudu_Tudu();

        $this->_formatParams($tudu, $post);

        $toArr = array();
        foreach ($tudu->to as $item) {
            $toArr[] = $item['truename'];
        }
        $tudu->content = '<p _name="forward"><strong>转发：</strong><span style="color:#aaa;">由 ' . $this->_user->trueName . ' 转发给  ' . implode(',', $toArr) . '</span></p>' . $tudu->content;

        $tudu->setAttributes(array(
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'poster'   => $this->_user->trueName,
            'header'   => array(
                'client-type' => 'iOS'
            ),
            'operation' => 'forward'
        ));

        try {
            /* @var $modelCompose Model_Tudu_Compose_Forward */
            $modelCompose = Tudu_Model::factory('Model_Tudu_Compose_Forward');

            $params = array(&$tudu);

            $modelCompose->execute('compose', $params);

            $config  = $this->_bootstrap->getOption('httpsqs');
            $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

            $config  = $this->_bootstrap->getOption('httpsqs');
            $tuduconf = $this->_bootstrap->getOption('tudu');

            $sendType  = isset($tuduconf['send']) ? ucfirst($tuduconf['send']['class']) : 'Common';
            $sendClass = 'Model_Tudu_Send_' . $sendType;

            $modelSend = new $sendClass(array('httpsqs' => $config));
            $modelSend->send(&$tudu);

            /*$action = !$tudu->fromTudu || !$tudu->fromTudu->isDraft ? 'create' : 'update';
            $sqsParam = array(
                'tsid'        => $this->_user->tsId,
                'tuduid'      => $tudu->tuduId,
                'from'        => $this->_user->userName,
                'uniqueid'    => $this->_user->uniqueId,
                'server'      => $this->_request->getServer('HTTP_HOST'),
                'type'        => $tudu->type,
                'ischangedCc' => ($action == 'update' && $tudu->cc) ? (boolean) $tudu->cc : false
            );

            $httpsqs->put(implode(' ', array(
                'tudu',
                $action,
                '',
                http_build_query($sqsParam)
            )), 'tudu');*/

        } catch (Model_Tudu_Exception $e) {
            $code = TuduX_OpenApi_ResponseCode::TUDU_SEND_FAILED;

            switch ($e->getCode()) {
                case Model_Tudu_Exception::TUDU_NOTEXISTS:
                case Model_Tudu_Exception::BOARD_NOTEXISTS:
                    $code = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
                    break;
                case Model_Tudu_Exception::PERMISSION_DENIED:
                    $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                    break;

            }
            throw new TuduX_OpenApi_Exception('Tudu send failed', $code);
        }

        $this->view->tuduid = $tudu->tuduId;
        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 审批
     */
    public function reviewAction()
    {
        $post = $this->_request->getParams();

        require_once 'Model/Tudu/Tudu.php';
        $tudu = new Model_Tudu_Tudu();

        $this->_formatParams($tudu, $post);

        $tudu->setAttributes(array(
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'poster'   => $this->_user->trueName,
            'isagree'  => $this->_request->getParam('agree', true),
            'operation' => 'review'
        ));

        try {
            /* @var $modelCompose Model_Tudu_Compose_Forward */
            $modelCompose = Tudu_Model::factory('Model_Tudu_Compose_Review');

            $params = array(&$tudu);

            $modelCompose->execute('compose', $params);

            // 考勤流程
            if ($tudu->fromTudu->appId == 'attend' && $tudu->stepId == '^end') {
                $mtudu = new Tudu_Model_Tudu_Entity_Tudu($tudu->getAttributes());

                Tudu_Dao_Manager::setDbs(array(
                Tudu_Dao_Manager::DB_APP => $this->_bootstrap->multidb->getDb('app')
                ));

                $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);
                $apply    = $daoApply->getApply(array('tuduid' => $tudu->tuduId));

                if (null !== $apply) {
                    $mapply = new Tudu_Model_App_Attend_Tudu_Apply($apply->toArray());

                    $model = new Tudu_Model_App_Attend_Tudu_Extension_Apply();
                    $model->onReview($mtudu, $mapply);
                }
            }

            $config  = $this->_bootstrap->getOption('httpsqs');
            $tuduconf = $this->_bootstrap->getOption('tudu');

            $sendType  = isset($tuduconf['send']) ? ucfirst($tuduconf['send']['class']) : 'Common';
            $sendClass = 'Model_Tudu_Send_' . $sendType;

            $modelSend = new $sendClass(array('httpsqs' => $config));
            $modelSend->send(&$tudu);

            /*$config  = $this->_bootstrap->getOption('httpsqs');
            $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

            $action = 'review';
            $sqsParam = array(
                'tsid'        => $this->_user->tsId,
                'tuduid'      => $tudu->tuduId,
                'from'        => $this->_user->userName,
                'uniqueid'    => $this->_user->uniqueId,
                'server'      => $this->_request->getServer('HTTP_HOST'),
                'type'        => $tudu->type,
                'stepid'      => $tudu->fromTudu->stepId,
                'stepstatus'  => $tudu->stepId  && $tudu->fromTudu->stepId != $tudu->stepId && 0 !== strpos($tudu->stepId, '^'),
                'nstepid'     => $tudu->stepId,
                'flowid'      => $tudu->flowId,
                'agree'       => $this->_request->getParam('agree', true),
                'ischangedCc' => ($action == 'update' && $tudu->cc) ? (boolean) $tudu->cc : false
            );

            $httpsqs->put(implode(' ', array(
                'tudu',
                $action,
                '',
                http_build_query($sqsParam)
            )), 'tudu');*/

        } catch (Model_Tudu_Exception $e) {
            throw new TuduX_OpenApi_Exception('Tudu review failed', TuduX_OpenApi_ResponseCode::TUDU_SEND_FAILED);
        }

        $this->view->tuduid = $tudu->tuduId;
        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 同意
     */
    public function agreeAction()
    {
        $this->_request->setParam('agree', true);

        return $this->reviewAction();
    }

    /**
     * 不同意
     */
    public function disagreeAction()
    {
        $this->_request->setParam('agree', false);

        return $this->reviewAction();
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @param array $params
     */
    protected function _formatParams(Model_Tudu_Tudu &$tudu, array $params)
    {
        $attributes = array();

        //$attachment = array();
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'starttime':
                case 'endtime':
                    $attributes[$key] = is_numeric($value) ? (int) $value : strtotime($value);
                    break;
                case 'priority':
                case 'privacy':
                case 'notifyall':
                case 'cycle':
                case 'isauth':
                case 'needconfirm':
                case 'istop':
                case 'acceptmode':
                    $attributes[$key] = (boolean) $value;
                    break;
                case 'to':
                case 'cc':
                case 'bcc':
                    if (!empty($value)) {
                        $attributes[$key] = $this->_formatReceiver($value, $key == 'to');
                    }
                    break;
                case 'reviewer':

                    break;
                case 'image':

                    break ;
                case 'subject':
                case 'classid':
                case 'tuduid':
                case 'location':
                default:
                    $attributes[$key] = $value;
            }
        }

        // 处理附件图片
        $attributes['content'] = !empty($attributes['content']) ? nl2br($attributes['content']) : '';
        $attachments = array();
        if (isset($params['image'])) {
            $images = $params['image'];

            if (!is_array($params['image'])) {
                $images = explode(',', $images);
            }

            foreach ($images as $fileId) {
                if (!$fileId) {
                    continue ;
                }

                if (false !== strpos($fileId, ',')) {
                    $arr = explode(',', $fileId);
                    foreach ($arr as $fid) {
                        $attachments[] = array('fileid' => $fid, 'isattachment' => false, 'isnetdisk' => false);
                        $fid = str_replace('AID:', '', $fid);
                        $attributes['content'] .= '<br /><img src="AID:' . $fid . '" _aid="' . $fid . '" />';
                    }

                    continue ;
                }

                $attachments[] = array('fileid' => $fileId, 'isattachment' => false, 'isnetdisk' => false);
                $fileId = str_replace('AID:', '', $fileId);
                $attributes['content'] .= '<br /><img src="AID:' . $fileId . '" _aid="' . $fileId . '" />';
            }
        }

        $tudu->setAttributes($attributes);

        if (!empty($attachments)) {
            foreach ($attachments as $item) {
                $tudu->addAttachment($item['fileid'], $item['isattachment'], $item['isnetdisk']);
            }
        }
    }

    /**
     *
     * @param string $receiver
     */
    protected function _formatReceiver($receiver, $expandGroup = false)
    {
        $arr = explode("\n", $receiver);
        $ret = array();
        foreach ($arr as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue ;
            }

            $pair = explode(' ', $line, 2);

            if (false !== strpos($pair[0], '@')) {
                $trueName = isset($pair[1]) ? $pair[1] : null;

                if (null === $trueName) {
                    list(, $suffix) = explode('@', $pair[0]);
                    $addressbook = Tudu_Addressbook::getInstance();
                    if (false === strpos($suffix, '.')) {
                        $info = $addressbook->searchUser($this->_user->orgId, $pair[0]);

                        if (!$info) {
                            continue ;
                        }

                        $trueName = $info['truename'];
                    } else {
                        $info = $addressbook->searchContact($this->_user->uniqueId, $pair[0], null);

                        if (null === $info) {
                            list($trueName, ) = explode('@', $pair[0]);
                        } else {
                            $trueName = $info['truename'];
                        }
                    }
                }

                $ret[$pair[0]] = array('email' => $pair[0], 'truename' => $trueName);
            } else {
                if ($expandGroup) {

                    if (0 === strpos($pair[0], 'XG')) {
                        $dao = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

                        $users = $dao->getContacts(array('uniqueid' => $this->_user->uniqueId, 'groupid' => $pair[0]))->toArray();
                        foreach ($users as $user) {
                            $ret[$user['contactid']] = array(
                                'email'    => $user['email'],
                                'username' => $user['email'],
                                'truename' => $user['truename']
                            );
                        }
                    } else {
                        $dao = Tudu_Dao_Manager::getDao('Dao_Td_Md_User', Tudu_Dao_Manager::DB_MD);

                        $users = $dao->getUsers(array('orgid' => $this->_user->orgId, 'groupid' => $pair[0]))->toArray();
                        foreach ($users as $user) {
                            $ret[$user['userid'] . '@' . $user['orgid']] = array(
                                'email'    => $user['userid'] . '@' .$user['orgid'],
                                'username' => $user['userid'] . '@' .$user['orgid'],
                                'truename' => $user['truename']
                            );
                        }
                    }

                } else {

                    $groupName = isset($pair[1]) ? $pair[1] : null;

                    if (null === $groupName) {
                        if (0 === strpos($pair[0], 'XG')) {
                            $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Group', Tudu_Dao_Manager::DB_TS);
                            $group = $daoGroup->getGroup(array('uniqueid' => $this->_user->uniqueId, 'groupid' => $pair[0]));

                            if (null === $group) {
                                continue ;
                            }

                            $groupName = $group->groupName;
                        } else {
                            $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);
                            $group = $daoGroup->getGroup(array('orgid' => $this->_user->orgId, 'groupid' => $pair[0]));

                            if (null === $group) {
                                continue ;
                            }

                            $groupName = $group->groupName;
                        }
                    }

                    $ret[$pair[0]] = array('groupid' => $pair[0], 'truename' => $groupName);
                }
            }
        }

        return $ret;
    }
}