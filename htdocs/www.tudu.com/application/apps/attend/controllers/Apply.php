<?php
/**
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Apply.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Apply extends Apps_Attend_Abstract
{

    /**
     * 初始化函数
     * 子类继承可通过重写本函数实现自己的初始化流程
     */
    public function init()
    {
        $this->view = $this->_this->view;

        $this->lang       = Tudu_Lang::getInstance()->load(array('common', 'tudu', 'attend'));
        $this->view->LANG = $this->lang;
        $this->checkApp();
    }

    /**
     * 考勤申请页面，当前用户发出的申请列表
     */
    public function indexAction()
    {
        $page     = (int) $this->_request->getQuery('page');
        $query    = $this->_request->getQuery();
        $pageSize = 25;
        $params   = array();

        /* @var $daoApply Dao_App_Attend_Apply */
        $daoApply    = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'uniqueid' => $this->_user->uniqueId
        );

        // 时间查询
        if (!empty($query['year'])) {
            if (!empty($query['month'])) {
                $month = (int) $query['month'] + 1;
                $condition['startdate'] = strtotime($query['year'] . '-' . $query['month'] . '-01');
                $condition['enddate']   = strtotime($query['year'] . '-' . $month . '-01') - 1;
                $params['month'] = $query['month'];
            } else {
                $year = (int) $query['year'] + 1;
                $condition['startdate'] = strtotime($query['year'] . '-01-01');
                $condition['enddate']   = strtotime($year . '-01-01') - 1;
            }

            $params['year'] = $query['year'];
        } else {
            $condition['startdate'] = strtotime(date('Y-m-1'));
            $condition['enddate']   = strtotime('+1 month', $condition['startdate']) - 1;

            $params['year']  = date('Y');
            $params['month'] = date('m');
        }

        if (!empty($query['categoryid'])) {
            $condition['categoryid'] = $query['categoryid'];
            $params['categoryid']    = $query['categoryid'];
        }

        if (isset($query['status']) && '' !== $query['status']) {
            $condition['status'] = $query['status'];
            $params['status']    = $query['status'];
        }

        $applies = $daoApply->getApplyPage($condition, 'createtime DESC', $page, $pageSize);

        $categories = $daoCategory->getCategories(array('orgid' => $this->_user->orgId), null, 'status DESC, issystem DESC, createtime DESC');

        $this->view->applies  = $applies->toArray();
        $this->view->categories = $categories->toArray();
        $this->view->pageinfo = array(
            'currpage'   => $page,
            'recordcount'=> $applies->recordCount(),
            'pagecount'  => $applies->pageCount(),
            'query'      => $params,
            'url'        => '/app/attend/apply/index'
        );
    }

    public function composeAction()
    {
        //
        $this->_this->setNeverRender();

        $post = $this->_request->getPost();

        $action = $post['action'] == 'send' ? 'send' : 'save';

        $attrsApply = array(
            'orgid'      => $this->_user->orgId,
            'categoryid' => isset($post['categoryid']) ? $post['categoryid'] : null,
            'starttime'  => isset($post['starttime']) ? strtotime($post['starttime']) : null,
            'endtime'    => isset($post['endtime']) ? strtotime($post['endtime']) : null,
            'period'     => isset($post['period']) ? (int) $post['period'] : null,
            'target'     => !empty($post['target']) ? $post['target'] : null,
            'checkintype'=> isset($post['checkintype']) ? (int) $post['checkintype'] : 0,
            'isallday'   => isset($post['isallday']) ? (int) $post['isallday'] : 0,
            'senderid'   => $this->_user->uniqueId
        );

        $tudu = new Model_Tudu_Tudu();
        $this->_formatParams($tudu, $post);

        $tudu->operation = $action;
        $tudu->setExtension(new Model_App_Attend_Tudu_Apply($attrsApply));

        $flow = new Model_Tudu_Extension_Flow();
        $tudu->setExtension($flow);

        $time = time();
        $tudu->setAttributes(array(
            'from'    => $this->_user->userName . ' ' . $this->_user->trueName,
            'uniqueid' => $this->_user->uniqueId,
            'email'    => $this->_user->userName,
            'poster'   => $this->_user->trueName,
            'createtime' => $time,
            'lastupdatetime' => $time,
            'operation' => $action
        ));

        $modelClass = 'Model_Tudu_Compose_' . ucfirst($action);

        $model = new $modelClass();

        try {
            $params = array(&$tudu);

            $model->execute('compose', $params);

            // 保存后添加发送操作
            if ($action != 'save') {
                $config  = $this->_this->bootstrap->getOption('httpsqs');

                $modelSend = new Model_Tudu_Send_Common(array('httpsqs' => $config));
                $modelSend->send(&$tudu);
            }

        } catch (Model_Tudu_Exception $e) {
            var_dump($e);exit;
            $error = null;
            switch ($e->getCode()) {
                case Model_Tudu_Exception::TUDU_NOTEXISTS:
                    // 图度不存在
                    $error = $this->lang['tudu_not_exists'];
                    break;
                case Model_Tudu_Exception::BOARD_NOTEXISTS:
                    $error = $this->lang['board_not_exists'];
                    break;
                case Model_Tudu_Exception::FLOW_USER_NOT_EXISTS:
                    $error = $this->lang['missing_flow_steps_receiver'];
                    break;
                case Model_Tudu_Exception::FLOW_NOT_EXISTS:
                    $error = $this->lang['missing_flow_steps'];
                    break;
                case Model_Tudu_Exception::INVALID_USER:
                case Model_Tudu_Exception::PERMISSION_DENIED:
                    $error = $this->lang['permission_denied_for_tudu'];
                    break;
                default:
                    $error = $this->lang['save_failure'];
                    break;
            }

            return $this->json(false, $error);

        } catch (Model_App_Attend_Exception $e) {
            var_dump($e);
            $message = $this->lang['save_failed'];
            switch ($e->getCode()) {
                case Model_App_Attend_Exception::APPLY_MISSING_CATEGORYID:
                    $message = $this->lang['invalid_categoryid'];
                    break ;
                case Model_App_Attend_Exception::APPLY_INVALID_ENDTIME:
                    $message = $this->lang['invalid_endtime'];
                    break ;
                case Model_App_Attend_Exception::APPLY_INVALID_STARTTIME:
                    $message = $this->lang['invalid_starttime'];
                    break ;
                case Model_App_Attend_Exception::CATEGORY_NOT_EXISTS:
                    $this->lang['category_not_exists'];
                    break;
            }

            return $this->json(false, $message);
        }

        $message = $action == 'save' ? 'apply_save_success' : 'apply_send_success';

        $output['tuduid'] = $tudu->tuduId;
        return $this->json(true, $this->lang[$message], $output);
    }

    /**
     * 申请编辑页面
     */
    public function modifyAction()
    {
        $tuduId = trim($this->_request->getQuery('tid'));

        // 申请人修改
        if ($tuduId) {
            $daoTudu  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
            $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

            $tudu  = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
            $apply = $daoApply->getApply(array('tuduid' => $tuduId));

            // 不存在的图度
            if (null === $tudu || null === $apply) {
                return Oray_Function::alert($this->lang['apply_not_exists']);
            }

            // 没有权限
            if ($apply->senderId != $this->_user->uniqueId
                || !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU))
            {
                return Oray_Function::alert($this->lang['un_role_modify_apply']);
            }

            $disabled = false;
            if ($apply->status != 3 && $tudu->setpId != '^end' && $tudu->stepNum > 0) {
                /* @var $daoStep Dao_Td_Tudu_Step */
                $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

                $stepUsers = $daoStep->getTuduStepUsers($tuduId);
                foreach ($stepUsers as $u) {
                    if ($u['status'] > 1) {
                        $disabled = true;
                        break ;
                    }
                }
            }

            $this->view->disabled = $disabled;
            $this->view->tudu     = $tudu->toArray();
            $this->view->apply    = $apply->toArray();
        }

        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        // 选择申请类型列表
        $categories = $daoCategory->getCategories(array('orgid' => $this->_user->orgId), array('status' => 1), 'issystem DESC, createtime DESC');

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));

        // 权限
        $access = $this->_user->getAccess();
        $perm = array(
            'task'      => $access->assertEquals(Tudu_Access::PERM_CREATE_TUDU, true),
            'discuss'   => $access->assertEquals(Tudu_Access::PERM_CREATE_DISCUSS, true),
            'notice'    => $access->assertEquals(Tudu_Access::PERM_CREATE_NOTICE, true),
            'meeting'   => $access->assertEquals(Tudu_Access::PERM_CREATE_MEETING, true),
            'board'     => $access->assertEquals(Tudu_Access::PERM_CREATE_BOARD, true),
            'upload'    => $access->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH),
            'moderator' => false
        );

        $depts = $this->getModerateDepts();
        $dept = reset($depts);

        if (!empty($depts)) {
            $perm['moderator'] = true;
        }

        $cookies = $this->_request->getCookie();
        $upload  = $this->_this->options['upload'];
        $upload['cgi']['upload'] .= '?' . session_name() . '=' . Zend_Session::getId()
                                  . '&email=' . $this->_user->address;

        $deptIds = array();
        $roles   = $this->getRoles();
        if (empty($roles['admin'])) {
            $moderateDepts = $this->getModerateDepts(true);
        } else {
            $moderateDepts = $this->getDepts();
            $deptIds[] = '^root';
        }
        foreach ($moderateDepts as $item) {
            $deptIds[] = $item['deptid'];
            $perm['moderator'] = true;
        }
        foreach ($depts as $item) {
            $deptIds[] = $item['deptid'];
        }

        $this->view->deptids    = array_unique($deptIds);
        $this->view->access     = $perm;
        $this->view->categories = $categories->toArray();
        $this->view->upload     = $upload;
        $this->view->cookies    = serialize($cookies);
        $this->view->back       = $this->_request->getQuery('back');
        $this->view->uploadsizelimit = $this->_this->options['upload']['sizelimit'] / 1024;
    }

    /**
     *
     */
    public function cancelAction()
    {
        $applyId = $this->_request->getPost('applyid');

        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

        $apply = $daoApply->getApply(array('applyid' => $applyId));

        if (null == $apply && $apply->senderId !== $this->_user->uniqueId && $apply->uniqueId !== $this->_user->uniqueId) {
            return $this->json(false, $this->lang['apply_not_exists']);
        }

        $ret = $daoApply->updateApply($applyId, array(
            'status' => 4
        ));

        if (!$ret) {
            return $this->json(fasle, $this->lang['apply_cancel_failure']);
        }

        // 取消图度
        $manager = Tudu_Tudu_Manager::getInstance();

        $tudu = $manager->getTuduById($apply->tuduId, $this->_user->uniqueId);

        if (null !== $tudu) {
            // 执行终止（取消）操作
            $ret = $manager->cancelTudu($apply->tuduId, true, '', $tudu->parentId);

            if ($ret) {
                $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);
                $daoLog->createLog(array(
                    'orgid' => $this->_user->orgId,
                    'uniqueid' => $this->_user->uniqueId,
                    'operator' => $this->_user->userName . ' ' . $this->_user->trueName,
                    'logtime'  => time(),
                    'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                    'targetid' => $apply->tuduId,
                    'action' =>  Dao_Td_Log_Log::ACTION_TUDU_CANCEL,
                    'detail' => serialize(array('accepttime' => null, 'status' => Dao_Td_Tudu_Tudu::STATUS_CANCEL, 'isdone' => true, 'score' => '')),
                    'privacy' => 0
                ));
            }
        }

        return $this->json(true, $this->lang['apply_cancel_success']);
    }

    /**
     * 考勤审批页面，当前用户审批的申请列表
     */
    public function receiveAction()
    {
        $page     = (int) $this->_request->getQuery('page');
        $query    = $this->_request->getQuery();
        $pageSize = 25;
        $params   = array();

        $daoApply    = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);

        $userCondition = array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
        );
        $condition = array(
            'reviewerid' => $this->_user->uniqueId
        );

        $roles = $this->getRoles();

        // 时间查询
        if (!empty($query['year'])) {
            if (!empty($query['month'])) {
                $month = (int) $query['month'] + 1;
                $condition['startdate'] = strtotime($query['year'] . '-' . $query['month'] . '-01');
                $condition['enddate']   = strtotime($query['year'] . '-' . $month . '-01') - 1;
                $params['month'] = $query['month'];
            } else {
                $year = (int) $query['year'] + 1;
                $condition['startdate'] = strtotime($query['year'] . '-01-01');
                $condition['enddate']   = strtotime($year . '-01-01') - 1;
            }

            $params['year'] = $query['year'];
        } else {
            $condition['startdate'] = strtotime(date('Y-m-1'));
            $condition['enddate']   = strtotime('+1 month', $condition['startdate']) - 1;

            $params['year']  = date('Y');
            $params['month'] = date('m');
        }

        if (!empty($query['categoryid'])) {
            $condition['categoryid'] = $query['categoryid'];
            $params['categoryid']    = $query['categoryid'];
        }

        if (isset($query['status']) && '' !== $query['status']) {
            $condition['status'] = $query['status'];
            $params['status']    = $query['status'];
        }

        if (!empty($query['keyword'])) {
            $userCondition['keyword'] = $query['keyword'];
            $params['keyword']    = $query['keyword'];
        } else {

            if (!empty($roles['moderator'])) {
                $depts = $this->getModerateDepts(true, true);

                $userCondition['deptid'] = $depts;
            }
        }

        $users = $daoCast->getCastUsers($userCondition)->toArray('uniqueid');

        if (!empty($users)) {
            $uniqueIds = array();
            if (!empty($query['keyword'])) {
                foreach ($users as $user) {
                    $uniqueIds[] = $user['uniqueid'];

                    $condition['uniqueid'] = $uniqueIds;
                    $condition['associateid'] = $uniqueIds;
                }
            } else if (!empty($roles['moderator']) || !empty($roles['admin'])) {
                foreach ($users as $user) {
                    $uniqueIds[] = $user['uniqueid'];

                    $condition['associateid'] = $uniqueIds;
                }
            }

            $applies = $daoApply->getApplyPage($condition, 'createtime DESC', $page, $pageSize);
            if (null !== $applies) {
                $this->view->pageinfo = array(
                    'currpage'   => $page,
                    'recordcount'=> $applies->recordCount(),
                    'pagecount'  => $applies->pageCount(),
                    'query'      => $params,
                    'url'        => '/app/attend/apply/receive'
                );

                $applies = $applies->toArray();

                foreach ($applies as &$apply) {
                    if (isset($users[$apply['uniqueid']])) {
                        $apply['deptname'] = $users[$apply['uniqueid']]['deptname'];
                    }
                }

                $categories = $daoCategory->getCategories(array('orgid' => $this->_user->orgId), null, 'status DESC, issystem DESC, createtime DESC');

                $this->view->applies  = $applies;
                $this->view->categories = $categories->toArray();
            }
        }

        $this->view->query = $params;
    }

    /**
     * 显示图度内容
     */
    public function viewAction()
    {
        $tuduId = $this->_request->getQuery('tid');
        $newwin = (boolean) $this->_request->getQuery('newwin');

        if (null === $tuduId) {

        }

        $daoTudu  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null === $tudu) {
            return Oray_Function('');
        }

        if ($newwin) {
            $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
            $org = $daoOrg->getOrg(array('orgid' => $this->_user->orgId));
            $this->view->org   = $org->toArray();
        }

        $apply = $daoApply->getApply(array('tuduid' => $tuduId));

        if (null === $apply) {
            Oray_Function::alert('考勤申请不存在或已被删除');
        }

        $isSender = $this->_user->uniqueId == $apply->senderId;
        $isTarget = $this->_user->uniqueId == $apply->uniqueId;

        $access = array(
            // 查看 - 参与人员或版主或超级版主或版块参与者（隐私类图度除外）
            'view'     => $tudu->uniqueId == $this->_user->uniqueId && !empty($tudu->labels),
            // 回复 - 回复权限或参与人员
            'reply'    => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_POST),
            // 编辑 - 编辑权限&发起人或版主
            'modify'   => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU) && $isSender,
            // 删除 - 删除权限&发起人或版主
            'delete'   => false,//$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU) && $isSender,
            // 上传 - 上传权限
            'upload'   => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH),
            // 取消 - 发起人&未确认
            'cancel'   => ($isSender || $isTarget) && !$tudu->isDone,
            // 确认完成 - 发起人&未确认
            'confirm'  => $isSender && !$tudu->isDone,
            // 取消确认 - 发起人&已确认
            'undone'   => $isSender && $tudu->isDone,
            // (取消)忽略 - 相关人员
            'ignore'   => true,
            // 是否接受者
            'target'  => $isTarget,
            // 是否发送者
            'sender'   => $isSender,
            // 同意申请
            'agree'    => false,
            // 不同意申请
            'disagree' => false
        );

        if ($tudu->isDone) {
            $access['reply']  = false;
            $access['modify'] = false;
        }

        // 回复内容
        $page        = (int) $this->_request->getQuery('page');
        $pageSize    = max(20, (int) $this->_user->option['replysize']);
        $uniqueId    = $this->_request->getQuery('unid');
        $back        = $this->_request->getQuery('back');
        $recordCount = $tudu->replyNum + 1;
        $labels      = $this->_this->getLabels();
        $isInvert    = (boolean) $this->_request->getQuery(
                       'invert',
                       (isset($this->_user->option['postsort']) && $this->_user->option['postsort'] == 1)
                     );

        $query = array(
            'tid'  => $tudu->tuduId,
            'back' => $back,
            'invert' => $isInvert ? 1 : 0
        );

        $condition = array(
            'tuduid' => $tudu->tuduId
        );

        // 已关联用户，设置已读状态
        if ($tudu->uniqueId == $this->_user->uniqueId) {
            if (!$tudu->isRead) {
                $daoTudu->markRead($tuduId, $this->_user->uniqueId);
            }

        // 增加到关联用户，解决版块中的已读未读状态问题（!!会导致重新发送的不会投递到用户图度箱）
        } else {
            $daoTudu->addUser($tuduId, $this->_user->uniqueId, array('isread' => true));
        }

        // 增加浏览次数
        $daoTudu->hit($tudu->tuduId);

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        if ($uniqueId) {
            $condition['uniqueid'] = $uniqueId;
            $query['unid'] = $uniqueId;

            $recordCount = $daoPost->getPostCount($tudu->tuduId, $uniqueId);
        }

        $pageCount = intval(($recordCount - 1) / $pageSize) + 1;

        $isLast = false;
        if ($page == 'last') {
            $page = $pageCount;
            $isLast = true;
        } else {
            $page = min($pageCount, max(1, (int) $page));
        }

        $postSort = $isInvert ? 'createtime DESC': 'createtime ASC';

        // 获取回复内容
        $posts = $daoPost->getPostPage($condition, $postSort, $page, $pageSize)->toArray();

        // 回复者的在线状态
        $status = array();

        // 回复的相关权限
        $postAccess = array(
            'modify' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_POST),
            'delete' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_POST)
        );

        foreach ($posts as $key => $post) {
            // 读取回复的附件信息
            if ($post['attachnum'] > 0) {
                $files = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS)->getFiles(array(
                    'tuduid' => $tudu->tuduId,
                    'postid' => $post['postid']
                ));

                $posts[$key]['attachment'] = $files->toArray();
            }

            // 权限
            if (!$post['isfirst'] && !$tudu->isDone) {
                $posts[$key]['access'] = array(
                    'modify' => $postAccess['modify'] && ($post['uniqueid'] == $this->_user->uniqueId),
                    'delete' => $postAccess['delete'] && ($post['uniqueid'] == $this->_user->uniqueId)
                );
            }

            if ($post['header']) {
                $posts[$key]['header'] = $this->formatPostHeader($post['header']);
            }

            if ($post['email']) {
                if (!array_key_exists($post['email'], $status)) {
                    $status[$post['email']] = false;
                }
                $posts[$key]['imstatus'] = &$status[$post['email']];
            }
        }

        if ($tudu->stepId && strpos($tudu->stepId, '^') !== 0) {
            /* @var $daoStep Dao_Td_Tudu_Step */
            $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

            $step = $daoStep->getCurrentStep($tuduId, $tudu->stepId, $this->_user->uniqueId);

            if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                $this->view->isreview = true;
            }

            if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                if ($step['uniqueid'] == $this->_user->uniqueId
                    && $step['status'] == 1
                    && !$tudu->isDone)
                {
                    $access['agree']  = true;
                    $access['disagree']  = true;
                }

                $access['forward']  = false;
                $access['divide']  = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['progress'] = false;
                $access['review']  = false;
            }
        }

        // 获取联系人的IM在线信息
        $config = $this->_this->bootstrap->getOption('im');
        $im = new Oray_Im_Client($config['host'], $config['port']);
        $imStatus = $im->getUserStatus(array_keys($status));

        foreach ($imStatus as $email => $_status) {
            if (isset($status[$email])) {
                $status[$email] = $_status;
            }
        }

        //$daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        $flow = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

        if ($flow) {
            $steps = $flow->steps;

            foreach ($steps as $sid => $step) {
                if ($sid == $flow->currentStepId) {
                    $section = $step['section'][$step['currentSection']];

                    if (count($section) > 1) {
                        $this->view->samereview = true;
                    }

                    foreach ($section as $u) {
                        if ($u['uniqueid'] == $this->_user->uniqueId && $u['status'] == 1) {
                            $access['agree'] = $access['disagree'] = true;
                        }
                    }
                }
            }

            $this->view->steps = $steps;
        }

        /*
        $users   = $daoStep->getTuduStepUsers($tudu->tuduId);
        $accepters = $daoTudu->getAccepters($tuduId);

        $isDisagreed = false;
        $steps = array();
        $isExceed = false;
        $processIndex = null;
        $sameReview = false;
        $currentUser = array();
        $currentIndex = null;
        foreach ($users as &$user) {
            $info = explode(' ', $user['userinfo']);
            $user['email']    = $info[0];
            $user['truename'] = $info[1];

            if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && $user['stepid'] == $tudu->stepId && !empty($accepters)) {
                foreach ($accepters as $accepter) {
                    if ($accepter['uniqueid'] == $user['uniqueid']) {
                        $user['percent'] = $accepter['percent'];
                    }
                }
            }

            $processIndex = $user['processindex'];

            if (!$isExceed && $user['stepid'] == $tudu->stepId) {
                $isExceed = true;
            }

            if ($isExceed && ($user['stepid'] != $tudu->stepId || ($user['type'] == 1 && $user['status'] < 1))) {
                $user['future'] = true;
            }

            $steps[$user['ordernum']]['users'][]    = $user;
            $steps[$user['ordernum']]['stepid']     = $user['stepid'];
            $steps[$user['ordernum']]['type']       = $user['type'];
            $steps[$user['ordernum']]['stepstatus'] = $user['stepstatus'];
            $steps[$user['ordernum']]['future']     = !empty($user['future']);

            if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['status'] > 2) {
                $isDisagreed = true;
            }

            if ($tudu->flowId && $user['stepid'] == $tudu->stepId) {
                if (null === $currentIndex && $user['status'] < 2) {
                    $currentIndex = $user['processindex'];
                }
                if ($currentIndex == $user['processindex']) {
                    $currentUser[] = $user['userinfo'];
                }

                if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    $steptype = 1;
                } else {
                    $steptype = 0;
                }
                $this->view->steptype = $steptype;
            }
        }
        // 判断是否同时审批
        $index = null;
        foreach ($users as $item) {

            if ($item['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $item['stepid'] == $tudu->stepId) {
                if ($index == $item['processindex']) {
                    $sameReview = true;
                }
                $index = $item['processindex'];
            }
        }

        if ($sameReview) {
            foreach ($users as $item) {
                if ($tudu->flowId && $item['stepid'] == $tudu->stepId) {
                    $currentUser[] = $item['userinfo'];
                }
            }
        }

        ksort($steps);

        if (!empty($currentUser)) {
            $tudu->to = Dao_Td_Tudu_Tudu::formatAddress(implode("\n", array_unique($currentUser)));
        }

        if ($isDisagreed && count($steps)) {
            if ($tudu->flowId) {
                $access['accept'] = false;
                if (strpos($tudu->stepId, '^') === 0) {
                    $access['reject'] = false;
                }
            } else {
                $lastStep = end($steps);

                if ($lastStep['type'] == 0) {
                    $arrTo = array();
                    foreach ($lastStep['users'] as $u) {
                        $arrTo[$u['email']] = array($u['truename'], null, null, $u['email']);
                    }

                    $tudu->to = $arrTo;

                    if (!isset($arrTo[$this->_user->userName])) {
                        $access['accept'] = false;
                        $access['reject'] = false;
                    }
                }

                reset($steps);
            }
        }

        if ($sameReview) {
            $this->view->samereview = $sameReview;
        }

        if (count($steps) > 0) {
            $this->view->steps = $steps;
        }*/

        if ($access['upload']) {
            $upload = $this->_this->options['upload'];
            $upload['cgi']['upload'] .= '?' . session_name() . '=' . Zend_Session::getId()
                                  . '&email=' . $this->_user->address;

            $this->view->upload = $upload;
        }

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));
        $this->view->registFunction('format_label', array($this, 'formatLabels'));

        $this->view->access    = $access;
        $this->view->tudu      = $tudu->toArray();
        $this->view->apply     = $apply->toArray();
        $this->view->posts     = $posts;
        $this->view->pageinfo  = array(
            'currpage'    => $page,
            'pagecount'   => $pageCount,
            'pagesize'    => $pageSize,
            'recordcount' => $recordCount,
            'query'       => $query,
            'url'         => '/tudu/view'
        );

        $this->view->cookies= serialize($this->_request->getCookie());
        $this->view->query  = $query;
        $this->view->labels = $labels;
        $this->view->newwin = $newwin;
        $this->view->imstatus  = $imStatus;
        $this->view->isinvert  = $isInvert;
    }

    /**
     * 预览
     */
    public function previewAction()
    {
        $params = $this->_request->getParams();

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $apply      = array();
        if (!empty($params['categoryid'])) {
            $categoryId = $params['categoryid'];
            $condition  = array(
                'categoryid' => $categoryId,
                'orgid'      => $this->_user->orgId
            );
            $category = $daoCategory->getCategory($condition);
            if (null !== $category) {
                $apply['subject']      = $category->categoryName;
                $apply['categoryname'] = $category->categoryName;
            }

            $apply['categoryid'] = $categoryId;
        }

        $apply['period']     = $params['period'];
        $apply['content']    = $params['content'];
        $apply['starttime']  = !empty($params['starttime']) ? strtotime($params['starttime']) : null;
        $apply['endtime']    = !empty($params['endtime']) ? strtotime($params['endtime']) : null;
        $apply['createtime'] = time();
        $apply['from']       = array(
            $this->_user->trueName,
            $this->_user->userName
        );

        $to = $this->_user->userName . ' ' . $this->_user->trueName;
        if (!empty($params['target'])) {
            $to = $params['target'];
        }
        $apply['to'] = Dao_Td_Tudu_Tudu::formatAddress($to);

        if (!empty($params['cc'])) {
            $apply['cc'] = Dao_Td_Tudu_Tudu::formatAddress($params['cc']);
        }

        if (isset($params['checkintype'])) {
            $apply['checkintype'] = $params['checkintype'];
        }
        if (isset($params['isallday'])) {
            $apply['isallday'] = $params['isallday'];
        }
        $apply['attachnum'] = 0;

        // 获取附件
        if (!empty($params['attach'])) {
            $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
            $apply['attachments'] = $daoFile->getFiles(
                array('fileid' => $params['attach']),
                array('isattachment' => null)
            )->toArray();
        }

        if (!empty($params['nd-attach'])) {
            $daoNetdisk = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);

            $files = $daoNetdisk->getFiles(array('fileid' => $params['nd-attach'], 'uniqueid' => $this->_user->uniqueId));

            $apply['attachnum'] += count($files);
            $apply['ndattach'] = $files->toArray();
        }

        $this->view->apply = $apply;
        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
    }

    /**
     * 处理审批流程
     */
    public function reviewAction()
    {
        $tuduId  = $this->_request->getPost('tid');
        $isAgree = (boolean) $this->_request->getPost('agree');
        $content = $this->_request->getPost('content');

        $params = array(
            'orgid'      => $this->_user->orgId,
            'boardid'    => '^app-attend',
            'poster'     => $this->_user->trueName,
            'posterinfo' => $this->_user->posotion,
            'email'      => $this->_user->userName,
            'tuduid'     => $tuduId,
            'agree'      => $isAgree,
            'content'    => $content,
            'uniqueid'   => $this->_user->uniqueId,
        );

        $tudu = new Model_Tudu_Tudu($params);

        $tudu->setExtension(new Model_App_Attend_Tudu_Apply());
        $tudu->setExtension(new Model_Tudu_Extension_Flow());

        $tudu->setAttributes(array(
            'operation' => 'review'
        ));

        try {
            $model = new Model_Tudu_Compose_Review();

            $model->execute('compose', array(&$tudu));

            $config  = $this->_this->bootstrap->getOption('httpsqs');

            $modelSend = new Model_Tudu_Send_Common(array('httpsqs' => $config));
            $modelSend->send(&$tudu);

        } catch (Model_Tudu_Exception $e) {
            $message = $this->lang['review_failure'];
            /*switch ($e->getCode()) {
                case Model_App_Attend_Exception::APPLY_MISSING_CATEGORYID:
                    $message = $this->lang['invalid_categoryid'];
                    break ;
                case Model_App_Attend_Exception::APPLY_INVALID_ENDTIME:
                    $message = $this->lang['invalid_endtime'];
                    break ;
                case Model_App_Attend_Exception::APPLY_INVALID_STARTTIME:
                    $message = $this->lang['invalid_starttime'];
                    break ;
                case Model_App_Attend_Exception::CATEGORY_NOT_EXISTS:
                    $this->lang['category_not_exists'];
                    break;
            }*/

            return $this->json(false, $message);
        }

        return $this->_this->json(true, $this->lang['review_apply_success']);
    }

    public function summaryAction()
    {
        $categoryId = $this->_request->getQuery('categoryid');
        $summary    = 0;

        /* @var $daoTotal Dao_App_Attend_Total */
        $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);

        $total = $daoTotal->getAttendTotal(array(
            'categoryid' => $categoryId,
            'uniqueid' => $this->_user->uniqueId,
            'date' => date('Ym')
        ));

        if ($total !== null) {
            $summary = $total->total;
        }

        return $this->_this->json(true, null, array('summary' => $summary));
    }

    /**
     * 格式化恢复
     *
     * @param $header
     */
    public function formatPostHeader($header)
    {
        if (!empty($header['action']) && $header['action'] == 'review') {
            $ret = array(
                'action' => $header['action'],
            );
            if (isset($header['tudu-act-value'])) {
                $ret['val'] = $header['tudu-act-value'];
            }

            if ($ret['val']) {
                if (isset($header['tudu-reviewer'])) {
                    $ret['text'] = sprintf($this->lang['agree_reply'], $header['tudu-reviewer']);
                } elseif (isset($header['tudu-to'])) {
                    $ret['text'] = sprintf($this->lang['agree_reply_to_exec'], $header['tudu-to']);
                } else {
                    $ret['text'] = $this->lang['agree_reply_no_next'];
                }
            } else {
                $ret['text'] = $this->lang['reject_reply'];
            }

            return $ret;
        }

        return null;
    }

    /**
     * 格式化内容
     *
     */
    public function formatContent($content)
    {
        if (!$content) {
            return $content;
        }

        $matches = array();
        preg_match_all('/AID:([^"]+)/', $content, $matches);


        if (!empty($matches[1])) {
            $array = array_unique($matches[1]);
            $auth  = md5(Zend_Session::getId() . $this->_this->session->logintime);
            foreach ($array as $item) {
                $content = str_replace("AID:{$item}", $this->getAttachmentUrl($item, 'view'), $content);
            }
        }

        return $content;
    }

    public function getAttachmentUrl($fid, $act = null)
    {
        $sid  = Zend_Session::getId();
        $auth = md5($sid . $fid . $this->_this->session->auth['logintime']);

        $url = $this->_this->options['sites']['file']
             . $this->_this->options['upload']['cgi']['download']
             . "?sid={$sid}&fid={$fid}&auth={$auth}";

        if ($act) {
            $url .= '&action=' . $act;
        }

        return $url;
    }

    /**
     * 获取当前用户所在部门及其下级部门子树
     */
    public function getChildDepts()
    {
        $daoDepartment = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        $depts = $daoDepartment->getDepartments(array('orgid' => $this->_user->orgId))->toArray();

        $ret = array();
        $startFlag = false;
        $depth = null;
        foreach ($depts as $dept) {
            if ($dept['deptid'] == $this->_user->deptId) {
                $startFlag = true;
                $depth = $dept['depth'];
                $ret[] = $dept;
                continue ;
            }

            if ($startFlag && $dept['depth'] > $depth) {
                $ret[] = $dept;
            }

            if (null !== $depth && $dept['depth'] <= $depth) {
                break;
            }
        }

        return $ret;
    }

    /**
     *
     */
    private function _formatParams(Model_Tudu_Tudu &$tudu, array $params, $suffix = '')
    {
        $keys = array(
            'tid'    => array('type' => 'string', 'column' => 'tuduid'),
            'ftid'   => array('type' => 'string', 'column' => 'tuduid'),
            'tuduid' => array('type' => 'string'),

            'acceptmode' => array('type' => 'boolean'),
            'subject'    => array('type' => 'string'),
            'content'    => array('type' => 'html'),

            'cc' => array('type' => 'receiver'),
            'bcc' => array('type' => 'receiver')
        );

        $time = time();
        $attributes = array(
            'orgid'   => $this->_user->orgId,
            'type'    => 'task',
            'boardid' => '^app-attend',
            'appid'   => 'attend',
            'classid' => '^attend'
        );

        foreach ($keys as $k => $item) {
            if ($k == 'to' && !empty($suffix)) {
                $val = $params['ch-' . $k . $suffix];
            } else{
                if (!isset($params[$k . $suffix])) {
                    continue ;
                }

                $val = $params[$k . $suffix];
            }

            // 有依赖关系字段
            if (isset($item['depend']) && empty($params[$item['depend'] . $suffix])) {
                continue ;
            }


            $col = isset($item['column']) ? $item['column'] : $k;
            switch ($item['type']) {
                case 'date':
                    $attributes[$col] = is_numeric($val) ? (int) $val : strtotime($val);
                    break;
                case 'boolean':
                    $attributes[$col] = (boolean) $val;
                    break;
                case 'html':
                    $t = strip_tags($val, 'img');
                    $attributes[$col] = empty($t) ? '' : $val;
                    break;
                case 'receiver':
                    if (!empty($val)) {
                        $attributes[$col] = $this->_formatReceiver($val, in_array($col, array('to', 'reviewer')));
                    }
                    break;
                case 'string':
                default:
                    $attributes[$col] = trim($val);
                    break;
            }
        }

        if (isset($attributes['type']) && $attributes['type'] == 'notice') {
            $attributes['istop'] = 0;
            if (!empty($attributes['endtime']) && $attributes['endtime'] >= strtotime('today')) {
                $attributes['istop'] = 1;
            }
        }

        $attachments = array();
        if (!empty($params['nd-attach' . $suffix])) {
            $ret['attachment'] = array_diff($ret['attachment'], $params['nd-attach' . $suffix]);

            $daoNdFile     = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);
            $daoAttachment = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);

            foreach ($params['nd-attach' . $suffix] as $ndfileId) {
                $fileId = $ndfileId;
                $attach = $daoAttachment->getFile(array('fileid' => $fileId));

                if (null !== $attach) {
                    $ret['attachment'][] = $fileId;
                    continue ;
                }

                $file = $daoNdFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $ndfileId));
                if ($file->fromFileId) {
                    $fileId = $file->fromFileId;
                }
                if ($file->attachFileId) {
                    $fileId = $file->attachFileId;
                }

                $fid = $daoAttachment->createFile(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'fileid'   => $fileId,
                    'orgid'    => $this->_user->orgId,
                    'filename' => $file->fileName,
                    'path'     => $file->path,
                    'type'     => $file->type,
                    'size'     => $file->size,
                    'createtime' => time()
                ));

                if ($fid) {
                    $attachments[] = array('fileid' => $fileId, 'isattachment' => true, 'isnetdisk' => true);
                }
            }
        }

        if (isset($params['attach' . $suffix]) && is_array($params['attach' . $suffix])) {
            foreach ($params['attach' . $suffix] as $item) {
                $attachments[] = array('fileid' => $item, 'isattachment' => true, 'isnetdisk' => false);
            }
        }

        if (isset($params['file' . $suffix]) && is_array($params['file' . $suffix])) {
            foreach ($params['file' . $suffix] as $item) {
                $attachments[] = array('fileid' => $item, 'isattachment' => false, 'isnetdisk' => false);
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
    protected function _formatReceiver($receiver, $isFlow = false)
    {
        $arr = explode("\n", $receiver);

        $ret = array();
        $section = array();
        foreach ($arr as $line) {
            $line = trim($line);
            if (empty($line) || $line == '+') {
                continue ;
            }

            if ($line == '>' && $isFlow) {
                $ret[] = $section;
                $section = array();
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

                if ($isFlow) {
                    $section[$pair[0]] = array('username' => $pair[0], 'truename' => $trueName, 'email' => $pair[0]);
                } else {
                    $ret[$pair[0]]     = array('username' => $pair[0], 'truename' => $trueName, 'email' => $pair[0]);
                }

            } else {

                // 流程不允许群组
                if ($isFlow) {
                    continue ;
                }

                $groupName = isset($pair[1]) ? $pair[1] : null;

                if (null === $groupName) {
                    if (0 === strpos($pair[0], 'XG')) {
                        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Group', Tudu_Dao_Manager::DB_MD);
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

        if (!empty($section) && $isFlow) {
            $ret[] = $section;
        }

        return $ret;
    }
}