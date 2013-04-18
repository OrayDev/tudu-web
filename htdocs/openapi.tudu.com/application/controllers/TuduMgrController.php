<?php
/**
 * 图度操作管理
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: TuduMgrController.php 2590 2012-12-31 10:04:53Z cutecube $
 */
class TuduMgrController extends TuduX_Controller_OpenApi
{
    /**
     *
     * @var Model_Tudu_Manager_Tudu
     */
    protected $_model;

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }

        $this->_model = Tudu_Model::factory('Model_Tudu_Manager_Tudu');
    }

    /**
     * 删除图度
     */
    public function deleteAction()
    {
        $tuduIds  = explode(',', $this->_request->getParam('tuduid'));
        $isDetail = (boolean) $this->_request->getParam('detail', 0);

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tuduid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $modelManage = Tudu_Model::factory('Model_Tudu_Manage');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudus = $daoTudu->getTudus(array('tuduids' => $tuduIds));

        $result = array();
        $success = 0;
        foreach ($tudus as $tudu) {
            try {
                $modelManage->execute('delete', array(&$tudu));
            } catch (Model_Tudu_Exception $e) {

                $code = TuduX_OpenApi_ResponseCode::SUCCESS;
                switch ($e->getCode()) {
                    case Model_Tudu_Manage::CODE_PERMISSION_DENY:
                        $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                        break;
                    case Model_Tudu_Manage::TUDU_DELETE_GROUP:
                        $code = TuduX_OpenApi_ResponseCode::TUDU_DELETE_GROUP;
                        break;
                    default:
                        $code = TuduX_OpenApi_ResponseCode::TUDU_DELETE_FAILED;
                        break;
                }

                $result[$tudu->tuduId] = $code;
                continue ;
            }

            $result[$tudu->tuduId] = TuduX_OpenApi_ResponseCode::SUCCESS;
            $success ++;
        }

        // 不存在的
        $diff = array_diff($tuduIds, array_keys($result));
        foreach ($diff as $id) {
            $result[$id] = TuduX_OpenApi_ResponseCode::SUCCESS;
            $success ++;
        }

        $this->view->code = $success > 0 ? TuduX_OpenApi_ResponseCode::SUCCESS : TuduX_OpenApi_ResponseCode::TUDU_DELETE_FAILED;
        if ($isDetail) {
            $this->view->detail = $result;
        }
    }

    /**
     * @deprecated
     * 统一使用接口 delete
     *
     * 删除草稿操作
     */
    public function discardAction()
    {
        /*$tuduIds = explode(',', $this->_request->getParam('tid'));
        $isBack  = (boolean) $this->_request->getParam('backid');

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', 20102);
        }

        $params  = array(
            'tuduid'   => $tuduIds,
            'username' => $this->_user->username
        );

        try {
            $this->_model->discard(&$params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
        if ($isBack) {
            $this->view->tuduids = $params['del-tuduids'];
        }*/

        return $this->deleteAction();
    }

    /**
     * 标记为
     */
    public function markReadAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));
        $labelId = $this->_request->getParam('labelid');
        $fun     = $this->_request->getParam('fun');

        $params = array(
            'fun'      => $fun
        );

        if (!empty($tuduIds)) {
            $params['tuduid'] = $tuduIds;
        }
        /*
        if (!empty($labelId)) {
            $params['labelid'] = $labelId;
        }*/

        try {
            $this->_model->mark($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 标签操作
     */
    public function labelAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));
        //$fun     = $this->_request->getParam('fun');
        //$labelId = explode(',', $this->_request->getParam('labelid'));
        $addLabels = explode(',', $this->_request->getParam('add'));
        $delLabels = explode(',', $this->_request->getParam('del'));

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        if (empty($addLabels) || empty($delLabels)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "lid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $labels = array();
        if (!empty($addLabels)) {
            foreach ($addLabels as $item) {
                $labels[$item] = 'add';
            }
        }

        if (!empty($delLabels)) {
            foreach ($delLabels as $item) {
                $labels[$item] = 'delete';
            }
        }

        $tuduManager = Tudu_Model::factory('Model_Tudu_Manage');

        foreach ($tuduIds as $tuduId) {
            try {
                $tuduManager->label($tuduId, $labels);
            } catch (Model_Tudu_Exception $e) {
                $exception = $this->getException($e);
                throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
            }
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 星标操作
     */
    public function starAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid', $this->_request->getParam('tid')));
        $fun     = $this->_request->getParam('fun');

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduIds,
            'fun'      => $fun
        );

        try {
            $this->_model->star($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 添加到图度箱
     */
    public function inboxAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduIds
        );

        try {
            $this->_model->inbox($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 忽略操作
     */
    public function ignoreAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));
        $fun     = $this->_request->getParam('fun');

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        if (!in_array($fun, array('add', 'remove'))) {
            $fun = 'add';
        }

        $params = array(
            'tuduid'   => $tuduIds,
            'fun'      => $fun
        );

        try {
            $this->_model->ignore($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     *
     */
    public function confirmAction()
    {
        $tuduId = $this->_request->getParam('tuduid');
        $isDone = (boolean) $this->_request->getParam('isdone', true);

        if (empty($tuduId)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'  => $tuduId,
            'isdone'  => $isDone
        );

        try {
            $this->_model->done($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 取消确认
     */
    public function disconfirmAction()
    {
        $this->_request->setParam('isdone', false);

        $this->confirmAction();
    }

    /**
     * 关闭/重开图度（讨论）
     */
    public function closeAction()
    {
        $tuduId  = $this->_request->getParam('tuduid');
        $isClose = (boolean) $this->_request->getParam('isclose', true);

        if (empty($tuduId)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduId,
            'isclose'  => $isClose
        );

        try {
            $this->_model->close($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 重开讨论
     */
    public function reopenAction()
    {
        $this->_request->setParam('isclose', false);

        $this->closeAction();
    }

    /**
     * 标签操作
     */
    public function acceptAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduIds
        );

        try {
            $this->_model->accept($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 标签操作
     */
    public function rejectAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tuduid'));

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduIds
        );

        try {
            $this->_model->reject($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 标签操作
     */
    public function cancelAction()
    {
        $tuduIds = $this->_request->getParam('tuduid');

        if (empty($tuduIds)) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        $params = array(
            'tuduid'   => $tuduIds
        );

        try {
            $this->_model->cancel($params);
        } catch (Model_Tudu_Exception $e) {
            $exception = $this->getException($e);
            throw new TuduX_OpenApi_Exception($exception['message'], $exception['code']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     *
     * @param Model_Tudu_Exception $e
     * @return array
     */
    public function getException($e)
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        switch($code) {
            case Model_Tudu_Exception::INVALID_USER:
                $code = TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE;
                break;
            case Model_Tudu_Manager_Tudu::CODE_SAVE_FAILED:
                $code  = TuduX_OpenApi_ResponseCode::OPERATE_FAILED;
                break;
            case Model_Tudu_Manager_Tudu::CODE_INVALID_TUDUID:
                $code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
                break;
            case Model_Tudu_Manager_Tudu::CODE_DELETE_TUDUGROUP_CHILD:
                $code = TuduX_OpenApi_ResponseCode::TUDU_DELETE_GROUP;
                break;
            case Model_Tudu_Manager_Tudu::CODE_INVALID_POSTID:
                $code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
                break;
            case Model_Tudu_Manager_Tudu::CODE_POST_NOTEXISTS:
            case Model_Tudu_Manager_Tudu::CODE_TUDU_NOTEXISTS:
                $code = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
                break;
            case Model_Tudu_Manager_Tudu::CODE_DENY_ROLE:
                $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                break;
            case Model_Tudu_Manager_Tudu::CODE_POST_FIRST:
                $code = TuduX_OpenApi_ResponseCode::CONTENT_POST_FIRST;
                break;
            case Model_Tudu_Manager_Tudu::CODE_INVALID_LABELID:
                $code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
                break;
            case Model_Tudu_Manager_Tudu::CODE_TUDU_CLOSED:
                $code = TuduX_OpenApi_ResponseCode::TUDU_CLOSED;
                break;
        }

        return array('code' => $code, 'message' => $message);
    }
}