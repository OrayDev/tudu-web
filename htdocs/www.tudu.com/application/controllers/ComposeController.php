<?php
/**
 * Compose Controller
 * 图度发送控制器
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: ComposeController.php 2721 2013-01-28 02:01:39Z cutecube $
 */
class ComposeController extends TuduX_Controller_Base
{

    /**
     * 发送操作类型
     * @var int
     */
    const ACTION_SAVE    = 'save';
    const ACTION_SEND    = 'send';
    const ACTION_FORWARD = 'forward';
    const ACTION_APPLY   = 'apply';
    const ACTION_REVIEW  = 'review';
    const ACTION_INVITE  = 'invite';
    const ACTION_DIVIDE  = 'divide';

    /**
     *
     * @var array
     */
    private $_tuduParams = array(
        'boardid'    => 'bid',
        'classid'    => 'classid',
        'flowid'     => 'flowid',
        'subject'    => 'subject',
        'reviewer'   => 'reviewer',
        'to'         => 'to',
        'cc'         => 'cc',
    	'bcc'        => 'bcc',
        'priority'   => 'priority',
        'privacy'    => 'privacy',
        'content'    => 'content',
        'attachment' => 'attach',
        'file'       => 'file',
        'isauth'     => 'isauth',
        'notifyall'  => 'notifyall',
        'password'   => 'password',
        'starttime'  => 'starttime',
        'endtime'    => 'endtime',
        'totaltime'  => 'totaltime',
        'needconfirm'=> 'needconfirm',
        'prevtuduid' => 'prev',
        'acceptmode' => 'acceptmode',
        'parentid'   => 'parentid'
    );

    private $_enableTags = '<p><a><span><label><b><strong><i><u><s><del><img><font><br><table><tbody><thead><tfoot><tr><td><div><ul><ol><li><dl><dt><dd><h1><h2><h3><h4><h5><h6>';

    public function init()
    {
        parent::init();

        $this->_helper->viewRenderer->setNeverRender();
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));

        if (!$this->_user->isLogined()) {
            return $this->json(false, $this->lang['login_timeout']);
        }

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD => $this->multidb->getDefaultDb(),
            Tudu_Dao_Manager::DB_TS => $this->getTsDb()
        ));
    }

    /**
     * do nothing
     */
    public function indexAction()
    {}

    /**
     * /compose/send
     *
     * 图度发送统一接口
     * 接管保存，发送，更新，转发，申请审批等操作
     *
     */
    public function sendAction()
    {
        $post   = $this->_request->getPost();

        // 当前操作类型
        $action = self::ACTION_SEND;

        // 图度类型
        $type   = isset($post['type']) ? $post['type'] : 'task';

        // 操作的保存图度列表
        // array('tuduid' => $params ...)
        $tuduList = array();

        // 提交主任务ID
        $tuduId  = isset($post['ftid']) ? $post['ftid'] : null;

        // 图度组根任务ID
        $rootId  = null;

        // 是否包含分工
        $hasDivide = isset($post['chidx']) && is_array($post['chidx']);

        // 日志详细信息
        $logDetails = array();

        // 返回数据
        $returnData = array();

        // 是否重开讨论
        $isReopen = isset($post['isclose']) && $type == 'discuss';

        if (!empty($post['action']) && $post['action'] == 'save') {
            $action = self::ACTION_SAVE;
        }

        if (!empty($post['forward'])) {
            $action = self::ACTION_FORWARD;
        } elseif (!empty($post['invite'])) {
            $action = self::ACTION_INVITE;
        } elseif (!empty($post['divide'])) {
            $action = self::ACTION_DIVIDE;
        } elseif (!empty($post['review'])) {
            $action = self::ACTION_REVIEW;
        } elseif (!empty($post['apply'])) {
            $action = self::ACTION_APPLY;
        }

        /* @var $manager Tudu_Tudu_Manager */
        $manager = Tudu_Tudu_Manager::getInstance();
        /* @var $storage Tudu_Tudu_Storage */
        $storage = Tudu_Tudu_Storage::getInstance();

        $Indexes = array('');
        if ($type == 'task' && $hasDivide
            && $action != self::ACTION_FORWARD
            && $action != self::ACTION_APPLY)
        {
            $Indexes = array_merge($Indexes, $post['chidx']);
        }

        // 周期任务
        if (($type == 'task' || $type == 'meeting') && $tuduId && !Tudu_Tudu_Extension::isRegistered('cycle')) {
            Tudu_Tudu_Extension::registerExtension('cycle', 'Tudu_Tudu_Extension_Cycle');
        }

        // 需要图度组
        if ($type == 'task') {
            Tudu_Tudu_Extension::registerExtension('group', 'Tudu_Tudu_Extension_Group');
        }

        // 需要流程
        if (($type == 'task' || $type == 'notice') && !Tudu_Tudu_Extension::isRegistered('flow')) {
            Tudu_Tudu_Extension::registerExtension('flow', 'Tudu_Tudu_Extension_Flow');
        }

        // 版块列表
        $boards = $this->getBoards(false);

        // 遍历提交图度参数，填充图度列表
        $children = array();
        foreach ($Indexes as $suffix) {

            if ('' !==$suffix) {
                $suffix = '-' . $suffix;
            }

            // 获取已存在图度数据
            $fromTudu = null;
            if (!empty($post['ftid' . $suffix])) {
                $tid = $post['ftid' . $suffix];

                $fromTudu = $manager->getTuduById($tid, $this->_user->uniqueId);

                if (null === $fromTudu) {
                    return $this->json(false, $this->lang['tudu_not_exists']);
                }
            }

            // 创建图度
            if (null === $fromTudu) {
                // 转发、分工等图度不存在
                if (($action != self::ACTION_SEND && $action != self::ACTION_SAVE && $action != self::ACTION_DIVIDE)
                    || ($action == self::ACTION_DIVIDE && '' === $suffix))
                {
                    return $this->json(false, $this->lang['tudu_not_exists']);
                }

                // 创建权限
                if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
                    return $this->json(false, $this->lang['perm_deny_create_tudu']);
                }

            } else {

                // 非草稿状态下的分工，没有修改则不进行后续更新操作
                if (!empty($suffix) && empty($post['ismodified' . $suffix]) && !($fromTudu->isDraft && $action != self::ACTION_SAVE)) {
                    $idx = str_replace('-', '', $suffix);
                    $children[$idx] = $fromTudu->tuduId;
                    continue ;
                }

                // 保存草稿，图度必须为草稿状态
                if ($action == self::ACTION_SAVE) {
                    if (!$fromTudu->isDraft) {
                        return $this->json(false, $this->lang['forbid_save_sent']);
                    }

                } else {
                    $isSender   = true;
                    $isAccepter = in_array($this->_user->address, $fromTudu->accepter, true)
                                || in_array($this->_user->userName, $fromTudu->accepter, true);

                    switch ($action) {
                        // 更新，权限，发起人/版主/分区负责人
                        case self::ACTION_SEND:

                            // 更新权限
                            if ($fromTudu && !$fromTudu->isDraft && !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)) {
                                return $this->json(false, $this->lang['perm_deny_update_tudu']);
                            }

                            // 权限
                            $board = $boards[$fromTudu->boardId];
                            $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
                            $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

                            if (!$isSender && !$isModerator && !$isSuperModerator) {
                                return $this->json(false, $this->lang['perm_deny_update_tudu']);
                            }

                            break;

                        // 转发，权限，任务，已发送，执行人，非图度组
                        case self::ACTION_FORWARD:

                            // 转发权限
                            if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
                                return $this->json(false, $this->lang['perm_deny_forward_tudu']);
                            }

                            // 图度组
                            if ($fromTudu->isTuduGroup) {
                                return $this->json(false, $this->lang['deny_forward_tudugroup']);
                            }

                            // 不是执行人
                            if (!$isAccepter) {
                                return $this->json(false, $this->lang['forbid_non_accepter_forward']);
                            }

                            $isSender = $fromTudu->sender == $this->_user->userName;

                            break;

                        // 分工，任务，已发送，执行人，有分工
                        case self::ACTION_DIVIDE:
                            if (empty($post['chidx'])) {
                                return $this->json(false, $this->lang['no_divide_tudu']);
                            }

                            if ($fromTudu->isDraft) {
                                return $this->json(false, $this->lang['tudu_not_exists']);
                            }

                            $isSender = $fromTudu->sender == $this->_user->userName;

                            break;

                        // 邀请，必须为会议，已经发送
                        case self::ACTION_INVITE:

                            if ($fromTudu->type != 'meeting') {
                                return $this->json(false, null);
                            }

                            if ($fromTudu->isDraft) {
                                return $this->json(false, $this->lang['tudu_not_exists']);
                            }

                            $isSender = $fromTudu->sender == $this->_user->userName;

                            break;

                        // 申请审批，必须为任务，已发送，执行人，非图度组
                        case self::ACTION_APPLY:
                            // 图度组不能参与审批
                            if ($fromTudu->isTuduGroup) {
                                return $this->json(false, $this->lang['tudu_group_review']);
                            }

                            // 非图度执行人不能进行申请审批操作
                            if (!$isAccepter) {
                                return $this->json(false, $this->lang['no_accepter_apply']);
                            }

                            // 审批人为空
                            if (empty($post['reviewer' . $suffix])) {
                                return $this->json(false, $this->lang['no_reviewer']);
                            }

                            $isSender = $fromTudu->sender == $this->_user->userName;

                            break;

                        // 审批，审批步骤，审批人是当前用户
                        case self::ACTION_REVIEW:

                            if (!$fromTudu->stepId || false !== strpos('^', $fromTudu->stepId)) {
                                return $this->json(false, $this->lang['disable_review']);
                            }

                            $flow = Tudu_Tudu_Extension::getExtension('flow');

                            $reviewer = $flow->getStepUser($fromTudu->tuduId, $fromTudu->stepId, $this->_user->uniqueId);

                            if (!$reviewer || $reviewer['type'] != Dao_Td_Tudu_Step::TYPE_EXAMINE || $reviewer['status'] != 1) {
                                return $this->json(false, $this->lang['disable_review']);
                            }

                            $isSender = $fromTudu->sender == $this->_user->userName;

                            break;
                    }

                    if ($action != self::ACTION_SEND && $fromTudu->isDraft) {
                        return $this->json(false, $this->lang['forbid_save_sent']);
                    }
                }
            }

            $params = $this->_formatTuduParams($post, $suffix);
            $params['action'] = $action;

            if (null === $fromTudu) {
                // 发起人参数
                $params['from']       = $this->_user->userName . ' ' . $this->_user->trueName;
                $params['email']      = $this->_user->userName;
                $params['sender']     = $this->_user->userName;
            } else {
                if (!empty($params['flowid']) && $action != self::ACTION_REVIEW && $action != self::ACTION_FORWARD) {
                    unset($params['to']);
                }

                if (!$fromTudu->isDraft) {
                    $params['lastmodify'] = implode(chr(9), array($this->_user->uniqueId, time(), $this->_user->trueName));
                }
            }

            // 创建时间
            if (null === $fromTudu || $fromTudu->isDraft) {
                $params['createtime'] = time();
            }

            // 转发，没有编辑权限，去除保存参数
            $isClearModify = false;
            if ($action == self::ACTION_FORWARD) {
                // 权限
                $board = $boards[$fromTudu->boardId];
                $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
                $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

                if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)
                    || (!$isSender && !$isModerator && !$isSuperModerator))
                {
                    $isClearModify = true;
                }
            }

            if ($action == self::ACTION_REVIEW || $action == self::ACTION_APPLY) {
                $isClearModify = true;
            }

            if ($isClearModify) {
                unset(
                    $params['classid'],
                    $params['subject'],
                    $params['privacy'],
                    $params['password'],
                    $params['priority'],
                    $params['isauth'],
                    $params['needconfirm'],
                    $params['notifyall']
                );
            }

            try {
                $tudu = $storage->prepareTudu($params, $fromTudu);
            } catch (Tudu_Tudu_Exception $e) {
                switch ($e->getCode()) {
                    case Tudu_Tudu_Exception::CODE_FLOW_STEP_NULL:
                        $this->json(false, $this->lang['missing_flow_steps']);
                        break;
                    case Tudu_Tudu_Exception::CODE_NOT_EXISTS_UPPER:
                        $this->json(false, $this->lang['missing_flow_steps_upper_reviewer']);
                        break;
                    case Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER:
                        $this->json(false, $this->lang['missing_flow_steps_receiver']);
                        break;
                        /*
                    case Tudu_Tudu_Exception::MISSING_VOTE_TITLE:
                        $this->json(false, $this->lang['missing_vote_title']);
                        break;
                    case Tudu_Tudu_Exception::MISSING_VOTE_OPTIONS:
                        $this->json(false, $this->lang['missing_vote_option']);
                        break;
                        */
                }
            }

            if ('' === $suffix) {
                $tuduId = $tudu->tuduId;
                $rootId = $tudu->rootId ? $tudu->rootId : $tudu->tuduId;
            }

            // 返回投票数据参数
            if ($tudu->type == 'discuss' && $tudu->vote) {
                $vote = $tudu->vote;
                if ($vote && !empty($vote['newoptions'])) {
                    foreach ($vote['newoptions'] as $item) {
                        $returnData['votes'][$item['index']] = $item['optionid'];
                    }
                }
            }

            // 部分操作不需要继承原文内容
            if ('' == $suffix && $action != self::ACTION_REVIEW && $action != self::ACTION_FORWARD && $action != self::ACTION_INVITE) {
                if ($fromTudu && !$fromTudu->isDraft) {
                    $tudu->boardId  = $tudu->boardId   ? $tudu->boardId : $fromTudu->boardId;
                    $tudu->content  = $tudu->content   ? $tudu->content : $fromTudu->content;
                    $tudu->startTime= isset($post['starttime' . $suffix]) ? $tudu->startTime : $fromTudu->startTime;
                    $tudu->endTime  = isset($post['endtime' . $suffix]) ? $tudu->endTime : $fromTudu->endTime;
                }
            }

            // 设置子任务的父级ID
            if ('' !== $suffix && $type == 'task') {
                $tudu->parentId = $tuduId;
                $tudu->rootId   = $rootId;
                $tudu->nodeType = $tudu->nodeType ? $tudu->nodeType : Dao_Td_Tudu_Group::TYPE_LEAF;

                $parent = $tuduList[$tuduId];

                if (!$fromTudu) {
                    $tudu->boardId  = $tudu->boardId ? $tudu->boardId : $parent->boardId;
                    $tudu->classId  = $tudu->classId ? $tudu->classId : $parent->classId;
                    //$tudu->content  = $tudu->content ? $tudu->content : $parent->content;
                    $tudu->startTime= $tudu->startTime ? $tudu->startTime : $parent->startTime;
                    $tudu->endTime  = $tudu->endTime ? $tudu->endTime : $parent->endTime;

                    $content = trim(strip_tags($tudu->content, 'img'));
                    if (!$content) {
                        $tudu->content = $parent->content;
                    }

                } else {
                    $tudu->boardId  = $tudu->boardId   ? $tudu->boardId : $fromTudu->boardId;
                    $tudu->content  = $tudu->content   ? $tudu->content : $fromTudu->content;
                    $tudu->startTime= $tudu->startTime ? $tudu->startTime : $fromTudu->startTime;
                    $tudu->endTime  = $tudu->endTime   ? $tudu->endTime : $fromTudu->endTime;
                }

                $returnData['children'][(string) str_replace('-', '', $suffix)] = $tudu->tuduId;

                $idx = str_replace('-', '', $suffix);
                $children[$idx] = $tudu->tuduId;
            }

            if ('' === $suffix && $hasDivide && $type == 'task') {
                $tudu->rootId   = $rootId;
                $tudu->nodeType = Dao_Td_Tudu_Group::TYPE_NODE;
            }

            // 没有分工
            if (!$hasDivide && $tuduId == $tudu->tuduId && $fromTudu && $fromTudu->nodeType
                && $manager->getChildrenCount($tudu->tuduId) <= 0)
            {
                $tudu->nodeType = Dao_Td_Tudu_Group::TYPE_LEAF;
            }

            // 未发送，更新创建时间
            /*if (!$fromTudu || $fromTudu->isDraft) {
                $tudu->createTime = time();
            }*/

            // 空内容
            if ($action != self::ACTION_REVIEW && $action != self::ACTION_SAVE && !$tudu->content) {
                return $this->json(false, $this->lang['params_invalid_content']);
            }

            // 转发
            if ($action == self::ACTION_FORWARD || $action == self::ACTION_INVITE) {
                // 输入用户已经是执行人
                $to = $tudu->to;

                foreach ($to as $k => $item) {
                    /*if (is_string($item['email']) && in_array($item['email'], $fromTudu->accepter, true)) {
                        return $this->json(false, sprintf($this->lang['user_is_accepter'], $item['truename']));
                    }*/

                    if ($action == self::ACTION_FORWARD && $fromTudu->selfPercent < 100) {
                        $to[$k]['percent'] = $fromTudu->selfPercent;
                    }
                }

                $tudu->to = $to;

                $to = array();
                foreach ($fromTudu->to as $k => $item) {
                    if ($k != $this->_user->userName) {
                        $to[] = $k . ' ' . $item[0];
                    }
                }

                $tudu->to = array_merge($tudu->to, Tudu_Tudu_Storage::formatRecipients(implode("\n", $to)));
            }

            // 邀请 || 100% 转发 加上自己
            if ($action == self::ACTION_INVITE || ($action == self::ACTION_FORWARD && $fromTudu->selfPercent >= 100)) {
                $tudu->to = array_merge($tudu->to, array(
                    $this->_user->userName => array('email' => $this->_user->userName, 'truename' => $this->_user->trueName, 'percent' => $fromTudu->selfPercent)
                ));
            }

            // 执行人 -- 自己
            if ($action == self::ACTION_APPLY) {
                $tudu->to = Tudu_Tudu_Storage::formatRecordRecipients($fromTudu->to);
            }

            // 暂时不能输入自己 - 界面交互有问题不能支持
            if ($action == self::ACTION_REVIEW && $tudu->reviewer) {
                if (array_key_exists($this->_user->address, $tudu->reviewer)
                    || array_key_exists($this->_user->userName, $tudu->reviewer)) {
                    return $this->json(false, $this->lang['add_reviewer_self']);
                }
            }

            $tuduList[$tudu->tuduId] = $tudu;

            $act = $tudu->tuduId == $tuduId
                 ? $action
                 : 'send';

            if ($act == 'send') {
                $act = $fromTudu && !$fromTudu->isDraft ? 'update' : 'send';
            }

            $detail = $this->_getLogDetail($params, $fromTudu);

            if (in_array($action, array(self::ACTION_REVIEW, self::ACTION_APPLY, self::ACTION_FORWARD, self::ACTION_INVITE))) {
                unset($detail['content']);
            }

            $logDetails[$tudu->tuduId] = array(
                'action' => $act,
                'detail' => $detail
            );
        }

        foreach ($tuduList as $tid => $item) {
            $prevId = $item->prevTuduId;

            if ($prevId && strpos($prevId, 'child-') !== false) {
                $prevId = (int) str_replace('child-', '', $prevId);

                if (isset($children[$prevId])) {
                    $item->prevTuduId = $children[$prevId];
                }
            }
        }

        // 遍历图度列表保存
        foreach ($tuduList as $tid => $tudu) {
            // 主任务按照操作处理

            // 处理审批流程
            if ($action == self::ACTION_REVIEW) {
                $agree = $this->_request->getPost('agree');

                $storage->reviewTudu($tudu, $agree);

            // 其他操作
            } else {
                if ($tid == $tuduId) {
                    $func  = $action == 'send' ? 'save' : $action;
                    $func .= 'Tudu';
                } else {
                    $func  = 'saveTudu';
                }

                $ret = $storage->{$func}($tudu);

                if (!$ret) {
                    return $this->json(false, $this->lang['save_failure']);
                }
            }

            $returnData['tuduid'] = $tuduId;
        }

        //Tudu_Tudu_Deliver::initAddressBook($this->multidb->getDefaultDb());
        $deliver = Tudu_Tudu_Deliver::getInstance();

        // 遍历图度列表发送图度
        if ($action !== self::ACTION_SAVE) {
            $config  = $this->bootstrap->getOption('httpsqs');
            $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

            foreach ($tuduList as $tid => $tudu) {

                // 发送到接收人
                $recipients = $deliver->prepareRecipients($this->_user->uniqueId, $this->_user->userId, $tudu);

                if ($action == self::ACTION_REVIEW && $tudu->type == 'notice' && !$this->_request->getPost('agree')) {
                    $recipients = array();
                    $addressBook = $deliver->getAddressBook();
                    $fromSender = $addressBook->searchUser($tudu->orgId, $tudu->sender);
                    if (!empty($fromSender)) {
                        $fromSender['accepterinfo'] = $fromSender['email'] . ' ' . $fromSender['truename'];
                        $fromSender['issender']     = $fromSender['email'] == $tudu->sender;
                        $recipients[$fromSender['uniqueid']] = $fromSender;
                    }
                }

                // 公告（含审批时），过滤接收人
                if ($tudu->type == 'notice' && $tudu->reviewer) {
                    $users = array();
                    foreach ($tudu->reviewer as $item) {
                        foreach ($item as $reviewer) {
                            $users[] = $reviewer['email'];
                        }
                    }

                    // 过滤非审批人的接收用户
                    foreach ($recipients as $uniqueId => $recipient) {
                        if (!in_array($recipient['email'], $users)) {
                            unset($recipients[$uniqueId]);
                        }
                    }
                }

                // 移除原执行人
                if (($tudu->type == 'meeting' || ($tudu->type == 'task' && !$tudu->reviewer)) && !$tudu->isDraft()) {
                    $accepters = $manager->getTuduAccepters($tudu->tuduId);
                    $to        = $tudu->to;
                    foreach ($accepters as $item) {
                        list($email, ) = explode(' ', $item['accepterinfo'], 2);

                        // 移除执行人角色，我执行标签
                        if (!empty($to) && !array_key_exists($email, $to)
                            && $manager->getChildrenCount($tudu->tuduId, $item['uniqueid']) <= 0)
                        {
                            $deliver->removeAccepter($tudu->tuduId, $item['uniqueid']);

                            $manager->deleteLabel($tudu->tuduId, $item['uniqueid'], '^a');
                            $manager->deleteLabel($tudu->tuduId, $item['uniqueid'], '^td');
                        }

                        // 过滤外发执行人 避免重复发送
                        if ($action != self::ACTION_SEND) {
                            foreach ($recipients as $uniqueId => $recipient) {
                                if (!empty($recipient['isforeign']) && !empty($to) && array_key_exists($recipient['email'], $to)) {
                                    unset($recipients[$uniqueId]);
                                }
                            }
                        }

                        // 转发，继续之前的用户进度
                        if ($action == self::ACTION_FORWARD && isset($recipients[$item['uniqueid']])) {
                            $recipients[$item['uniqueid']]['tudustatus'] = (int) $item['tudustatus'];
                        }

                        // 审批，继续之前的用户进度
                        if ($action == self::ACTION_REVIEW && isset($recipients[$item['uniqueid']])) {
                            $recipients[$item['uniqueid']]['percent'] = (int) $item['percent'];
                        }
                    }
                }

                foreach ($recipients as $key => $recipient) {
                    // 需要验证
                    if (!empty($recipient['isforeign'])) {
                        $recipients[$key]['authcode'] = $tudu->isAuth ? Oray_Function::randKeys(4) : null;
                    }

                    // 标记转发
                    // 进度小于 100%是继承进度，100%时为0
                    if ($action == self::ACTION_FORWARD || $action == self::ACTION_INVITE) {
                        $newAccepter = $this->_getReceiver($post, 'to');
                        $fromTudu    = $tudu->getFromTudu();
                        if (isset($recipient['role']) && $recipient['role'] == 'to' && array_key_exists($recipient['email'], $newAccepter)) {
                            $recipients[$key]['forwardinfo'] = $this->_user->trueName . "\n" . time();
                            $recipients[$key]['percent']     = $fromTudu->selfPercent < 100 ? $fromTudu->selfPercent : 0;
                            $recipients[$key]['tudustatus']  = $fromTudu->selfTuduStatus < 2 ? $fromTudu->selfTuduStatus : 0;
                        }
                    }
                    if ($tudu->flowId && isset($recipient['role']) && $recipient['role'] == 'to') {
                        $recipients[$key]['tudustatus'] = 1;
                        $recipients[$key]['percent']    = 0;
                    }
                }

                // 过滤外发人 避免重复发送
                /*if ($action != self::ACTION_SEND) {
                    $fromTudu = $tudu->getFromTudu();
                    foreach ($recipients as $uniqueId => $recipient) {
                        if (!empty($recipient['isforeign'])
                            && (($fromTudu->to && array_key_exists($recipient['email'], $fromTudu->to))
                                || ($fromTudu->cc && array_key_exists($recipient['email'], $fromTudu->cc))
                                || ($fromTudu->bcc && array_key_exists($recipient['email'], $fromTudu->bcc))))
                        {
                            unset($recipients[$uniqueId]);
                        }
                    }
                }*/

                // 标记当前用户已转发
                if ($action == self::ACTION_FORWARD) {
                    $manager->markForward($tudu->tuduId, $this->_user->uniqueId);
                }

                // 加上当前用户（发起人）
                if ((!$tudu->isFromTudu() || $tudu->isDraft()) && !isset($recipients[$this->_user->uniqueId])) {
                    $recipients[$this->_user->uniqueId] = array(
                        'uniqueid' => $this->_user->uniqueId,
                        'role'     => 'from',
                        'issender' => true
                    );
                }

                // 发送图度到接收人
                $deliver->sendTudu($tudu, $recipients);

                $flowPercent = null;
                // 计算进度
                if ($tudu->flowId) {
                    if ($tudu->isChange('stepid')) {
                        $progress = $manager->updateFlowProgress($tudu->tuduId, null, $tudu->stepId, null, $flowPercent);
                    }
                } else {
                    $progress = $manager->updateProgress($tudu->tuduId, null, null);
                }

                // 需要计算父任务进度
                if ($tudu->parentId) {
                    $manager->calParentsProgress($tudu->tuduId);

                    // 小于100%的分工从图度箱移除
                    if (!$tudu->flowId) {
                        if ($progress < 100 && !array_key_exists($this->_user->address, $tudu->to)) {
                            $manager->deleteLabel($tudu->tuduId, $this->_user->uniqueId, '^i');
                            $manager->deleteLabel($tudu->tuduId, $this->_user->uniqueId, '^td');
                            $manager->deleteLabel($tudu->tuduId, $this->_user->uniqueId, '^c');
                        }
                    }
                }

                // 自己接受当前任务
                if ($action == self::ACTION_SEND || $action == self::ACTION_DIVIDE) {
                    if (($tudu->type == 'task' || $tudu->type == 'meeting') && !$tudu->flowId) {
                        if (($tudu->to && array_key_exists($this->_user->userName, $tudu->to))
                            || ($recipients && array_key_exists($this->_user->uniqueId, $recipients))
                            && !$tudu->acceptMode)
                        {
                            $manager->acceptTudu($tudu->tuduId, $this->_user->uniqueId, null);
                        }
                    }
                }

                // 转发操作，添加我转发标签，其他则添加到已发送
                if ($action == self::ACTION_FORWARD) {
                    $manager->addLabel($tuduId, $this->_user->uniqueId, '^w');
                } else if ($action != self::ACTION_REVIEW) {
                    $manager->addLabel($tuduId, $this->_user->uniqueId, '^f');
                }

                // 审批标签
                if ($action == self::ACTION_REVIEW) {
                    $manager->deleteLabel($tuduId, $this->_user->uniqueId, '^e');
                    $manager->deleteLabel($tuduId, $this->_user->uniqueId, '^td');
                    $manager->addLabel($tuduId, $this->_user->uniqueId, '^v');

                    $fromTudu = $tudu->getFromTudu();
                    if (null != $fromTudu) {
                        if (is_array($tudu->to) && array_key_exists($fromTudu->sender, $tudu->to) && $tudu->stepId != '^head' && $tudu->stepId != '^break' && !$tudu->flowId && !$tudu->acceptMode) {
                            $addressBook = Tudu_AddressBook::getInstance();
                            $user = $addressBook->searchUser($this->_user->orgId, $fromTudu->sender);

                            if (null !== $user) {
                                $manager->acceptTudu($tudu->tuduId, $user['uniqueid'], null);
                            }
                        }
                    }
                }

                // 移除草稿
                if ($tudu->isDraft()) {
                    $manager->deleteLabel($tuduId, $this->_user->uniqueId, '^r');
                }

                if (isset($logDetails[$tid])) {
                    // 图度日志
                    $this->_writeLog(Dao_Td_Log_Log::TYPE_TUDU, $tid, $logDetails[$tid]['action'], $logDetails[$tid]['detail'], 0);
                }

                // 记录私密密码
                if ($tudu->password) {
                    $this->session->privacy[$tudu->tuduId] = $tudu->password;
                }

                // 标记所有人未读状态
                $manager->markAllUnRead($tudu->tuduId);

                // 重开讨论
                if ($isReopen) {
                    $manager->closeTudu($tudu->tuduId, 0);
                }

                // 工作流执行人自动接受任务
                if ($tudu->flowId) {
                    $daoStep = $this->getDao('Dao_Td_Tudu_Step');

                    $step = $daoStep->getStep(array('tuduid' => $tudu->tuduId, 'stepid' => $tudu->stepId));
                    if (null !== $step) {
                        $stepUsers = $daoStep->getUsers($tudu->tuduId, $tudu->stepId);
                        if ($step->type == Dao_Td_Tudu_Step::TYPE_EXECUTE && !empty($stepUsers) && $tudu->stepId != '^head' && $tudu->stepId != '^break') {
                            foreach ($stepUsers as $item) {
                                $manager->acceptTudu($tudu->tuduId, $item['uniqueid'], null);
                            }
                            $manager->updateTudu($tudu->tuduId, array('acceptmode' => 0));
                        // 认领模式
                        } else if ($step->type == Dao_Td_Tudu_Step::TYPE_CLAIM) {
                            $manager->updateTudu($tudu->tuduId, array('acceptmode' => 1, 'accepttime' => null));
                        }
                    }

                    if ($flowPercent == 100 && !$tudu->needConfirm) {
                        $manager->doneTudu($tudu->tuduId, true, 0);

                        // 添加操作日志
                        $this->_writeLog(
                            Dao_Td_Log_Log::TYPE_TUDU,
                            $tudu->tuduId,
                            Dao_Td_Log_Log::ACTION_TUDU_DONE,
                            array('percent' => $flowPercent),
                            false,
                            true
                        );
                    }
                }

                // 记录外发人员
                $contacts = array();
                foreach ($recipients as $item) {
                    if (!empty($item['isforeign'])) {
                        $contacts[] = $item['uniqueid'];
                    }
                }
                if ($contacts) {
                    $this->session->tuduContact[$tudu->tuduId] = $contacts;
                }

                $sqsAction   = (!$tudu->isFromTudu() || $tudu->isDraft()) ? 'create' : 'update';
                $isChangedCc = $tudu->isChange('cc') || $tudu->isChange('bcc');

                $sqsParam = array(
                    'tsid'     => $this->_user->tsId,
                    'tuduid'   => $tid,
                    'from'     => $this->_user->userName,
                    'uniqueid' => $this->_user->uniqueId,
                    'server'   => $this->_request->getServer('HTTP_HOST'),
                    'type'     => $type,
                    'ischangedCc' => ($sqsAction == 'update' && $isChangedCc) ? $isChangedCc : false
                );

                if ($action == self::ACTION_SEND && $tudu->flowId && $sqsAction == 'create') {
                    $sqsParam['nstepid'] = $tudu->stepId;
                    $sqsParam['flowid']  = $tudu->flowId;
                }

                if ($action == self::ACTION_REVIEW) {
                    $sqsAction = 'review';
                    $sqsParam['stepid'] = $tudu->getFromTudu()->stepId;
                    $sqsParam['agree'] = $this->_request->getPost('agree');

                    if ($tudu->flowId) {
                        $sqsParam['nstepid'] = $tudu->stepId;
                        $sqsParam['flowid']  = $tudu->flowId;
                        $sqsParam['stepstatus']  = $tudu->currentStepStatus;
                    }

                    if ($tudu->type == 'notice' && $tudu->stepId = '^end') {
                        $sqsAction = 'create';
                    }
                }

                $httpsqs->put(implode(' ', array(
                    'tudu',
                    $sqsAction,
                    '',
                    http_build_query($sqsParam)
                )), 'tudu');
            }

        // 保存到发起人草稿箱
        } else {

            foreach ($tuduList as $tid => $tudu) {
                if (!$tudu->parentId) {
                    $deliver->saveDraft($tudu);
                }
            }
        }

        $message = $action !== self::ACTION_SAVE
                 ? $this->lang['send_success']
                 : $this->lang['save_success'];

        Tudu_Tudu_Extension::unRegisterAll();

        return $this->json(true, $message, $returnData);
    }

    /**
     * /compose/reply
     *
     * 回覆
     */
    public function replyAction()
    {
        $tuduId = $this->_request->getPost('tid');
        $action = $this->_request->getPost('action');
        $type   = $this->_request->getPost('type');
        $post   = $this->_request->getPost();

        $uniqueId = $this->_user->uniqueId;

        $fromPost = null;

        $fromPostId = trim($this->_request->getPost('fpid'));

        $isDoneTudu = false;

        /* @var $manager Tudu_Tudu_Manager */
        $manager = Tudu_Tudu_Manager::getInstance();
        /* @var $storage Tudu_Tudu_Storage */
        $storage = Tudu_Tudu_Storage::getInstance();

        $tudu = $manager->getTuduById($tuduId, $uniqueId);

        if (null === $tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }

        // 编辑回复的权限判断
        if ($this->_user->orgId != $tudu->orgId) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }

        // 已确认图度
        if ($tudu->isDone) {
            return $this->json(false, $this->lang['tudu_is_done']);
        }

        if ($fromPostId) {
            $fromPost = $manager->getPostById($tuduId, $fromPostId);
        }

        $isReceiver   = ($this->_user->uniqueId == $tudu->uniqueId) && count($tudu->labels);
        $isAccepter   = in_array($this->_user->userName, $tudu->accepter, true);
        $isSender     = in_array($tudu->sender, array($this->_user->address, $this->_user->account));

        $sendParam = array();

        if ('modify' == $action) {

            if (null == $fromPost) {
                return $this->json(false, $this->lang['post_not_exists']);
            }

            // 编辑回复的权限判断
            if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_POST)
            && $fromPost->isSend)
            {
                return $this->json(false, $this->lang['perm_deny_update_post']);
            }

            // 非回复者时，需要判断版主的权限
            if ($fromPost->uniqueId != $this->_user->uniqueId) {

                $boards = $this->getBoards(false);
                $board = $boards[$tudu->boardId];
                $isModerators = array_key_exists($this->_user->userId, $board['moderators']);

                if (!$isModerators) {
                    return $this->json(false, $this->lang['perm_deny_update_post']);
                }
            }

            if (!$fromPost->isSend) {
                $sendParam['remind'] = true;
            }

        } else {
            // 发表回复的权限判断 - 参与人员或回复权限
            if (!$isReceiver
            && !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_POST)) {
                return $this->json(false, $this->lang['perm_deny_create_post']);
            }

            // 需要发送提醒
            $sendParam['remind'] = true;
        }

        // 空内容
        if ($type != 'save' && empty($post['content'])) {
            return $this->json(false, $this->lang['missing_content']);
        }

        // 构造参数
        $params = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'uniqueid'   => $uniqueId,
            'email'      => $this->_user->userName,
            'poster'     => $this->_user->trueName,
            'posterinfo' => $this->_user->position,
            'content'    => $post['content'],
            'attachment' => !empty($post['attach']) ? array_unique((array) $post['attach']) : array(),
            'file'       => !empty($post['file']) ? array_unique((array) $post['file']) : array()
        );

        $elapsedtime = round((float) $this->_request->getPost('elapsedtime'), 2) * 3600;
        $percent = min(100, (int) $this->_request->getPost('percent'));

        if ($fromPost && $fromPost->isSend) {
            $isLog = $fromPost->isLog;
            $params['elapsedtime'] = $fromPost->elapsedtime;
            $params['percent']     = $fromPost->percent;
        } else {
            if (isset($post['percent'])) {
                $isLog = $percent != $tudu->selfPercent || $elapsedtime > 0;
            } else {
                $isLog = $elapsedtime > 0;
            }
        }

        $params['islog'] = $isLog;
        if ($isLog && $tudu->selfPercent < 100) {
            $params['elapsedtime'] = $elapsedtime;
            $params['percent']     = $percent;
        }

        // 处理网盘附件
        if (!empty($post['nd-attach'])) {
            $params['attachment'] = array_diff($params['attachment'], $post['nd-attach']);

            $daoNdFile = $this->getDao('Dao_Td_Netdisk_File');
            $daoAttachment = $this->getDao('Dao_Td_Attachment_File');

            foreach ($post['nd-attach'] as $ndfileId) {
                $fileId = $ndfileId;
                $attach = $daoAttachment->getFile(array('fileid' => $fileId));

                if (null !== $attach) {
                    $params['attachment'][] = $fileId;
                    continue ;
                }

                $file = $daoNdFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $ndfileId));
                if ($file->fromFileId) {
                    $fileId = $file->fromFileId;
                }
                if ($file->attachFileId) {
                    $fileId = $file->attachFileId;
                }

                $ret = $daoAttachment->createFile(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'fileid'   => $fileId,
                    'orgid'    => $this->_user->orgId,
                    'filename' => $file->fileName,
                    'path'     => $file->path,
                    'type'     => $file->type,
                    'size'     => $file->size,
                    'createtime' => time()
                ));

                if ($ret) {
                    $params['attachment'][] = $fileId;
                }
            }
        }

        if (!$fromPost) {
            $postId = $storage->createPost($params);

            if (!$postId) {
                return $this->json(false, $this->lang['post_send_failure']);
            }

            // 添加操作日志
            $this->_writeLog(
            Dao_Td_Log_Log::TYPE_POST,
            $postId,
            Dao_Td_Log_Log::ACTION_CREATE,
            $params
            );

        } else {
            $postId = $fromPost->postId;

            // 增加最后编辑信息
            if ($fromPost->isSend) {
                $params['lastmodify'] = implode(chr(9), array($uniqueId, $this->_timestamp, $this->_user->trueName));
            } else {
                $params['createtime'] = time();
            }

            $storage->updatePost($tuduId, $postId, $params);

            // 记录更新内容
            $arrFromPost = $fromPost->toArray();
            $updates = array();
            foreach ($params as $key => $val) {
                if (in_array($key, array('file', 'attachment'))) {
                    continue ;
                }

                if ($val != $arrFromPost[$key]) {
                    $updates[$key] = $val;
                }
            }

            // 添加操作日志
            $this->_writeLog(
            Dao_Td_Log_Log::TYPE_POST,
            $postId,
            Dao_Td_Log_Log::ACTION_CREATE,
            $updates
            );
        }

        if ($type != 'save') {
            // 未读
            if (!$fromPost || !$fromPost->isSend) {
                $manager->markAllUnread($tudu->tuduId);
            }

            // 标记已经读
            // 加入了批量更新和回复,所以在更新时就需要标示已读
            if ($tudu->isRead) {
                $manager->markRead($tudu->tuduId, $this->_user->uniqueId, true);
            }

            $config = $this->bootstrap->getOption('httpsqs');

            // 插入消息队列
            $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

            $tuduPercent = $tudu->percent;
            $flowPercent = null;
            if ($isLog && $tudu->selfPercent < 100) {

                if ($tudu->flowId) {
                    $tuduPercent = $manager->updateFlowProgress($tudu->tuduId, $tudu->uniqueId, $tudu->stepId, (int) $params['percent'], $flowPercent);
                } else {
                    $tuduPercent = $manager->updateProgress($tudu->tuduId, $tudu->uniqueId, (int) $params['percent']);
                }

                if (!$fromPost || !$fromPost->isSend) {
                    // 添加操作日志
                    $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tudu->tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_PROGRESS,
                    array('percent' => $params['percent'], 'elapsedtime' => $tudu->elapsedTime + (int) $post['elapsedtime'])
                    );
                }
            }

            // 自动确认
            if (($isLog && $tuduPercent == 100 && null === $flowPercent) || ($isLog && $flowPercent == 100)) {
                if (($isSender && $isAccepter) || !$tudu->needConfirm) {
                    $isDoneTudu = true;
                    $doneParams = array(
                    'tuduid' => $tudu->tuduId,
                    'percent' => $params['percent'],
                    'elapsedtime' => $tudu->elapsedTime + (int) $post['elapsedtime']
                    );

                    // 添加到发起人图度箱 -- 待确认中
                } else {
                    /* @var $addressBook Tudu_AddressBook */
                    $addressBook = Tudu_AddressBook::getInstance();
                    $sender = $addressBook->searchUser($this->_user->orgId, $tudu->sender);
                    $manager->addLabel($tudu->tuduId, $sender['uniqueid'], '^i');
                }
            }

            // 计算父级图度进度  及 图度组达到100%时，确认
            if ($tudu->parentId) {
                $parentPercent = $manager->calParentsProgress($tudu->parentId);

                if ($parentPercent >= 100) {
                    $sendParam['confirm'] = true;
                }
            }

            // 发送回复
            $manager->sendPost($tuduId, $postId);

            // 统计时间
            if ($isLog) {
                $manager->calcElapsedTime($tuduId);
            }

            // 周期任务
            if ($isLog && $tudu->cycleId && $tuduPercent >= 100) {
                $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');
                $cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId));

                if ($cycle->count == $tudu->cycleNum) {
                    $sendParam['cycle'] = true;
                }
            }

            $sendParam['tuduid'] = $tudu->tuduId;
            $sendParam['from']   = $this->_user->userName;
            $sendParam['sender'] = $this->_user->trueName;
            $sendParam['uniqueid'] = $this->_user->uniqueId;
            $sendParam['postid'] = $postId;
            $sendParam['tsid']   = $this->_user->tsId;
            $sendParam['server'] = $this->_request->getServer('HTTP_HOST');

            // 处理工作流
            // 处理流程发送过程
            if ($tudu->type == 'task' && $percent >= 100) {
                /* @var $daoFlow Dao_Td_Tudu_Flow */
                $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

                $flowData = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

                if (null !== $flowData) {
                    /* @var $flow Model_Tudu_Extension_Flow */
                    $flow = new Model_Tudu_Extension_Flow($flowData->toArray());

                    $isCurrentUser = $flow->isCurrentUser($this->_user->uniqueId);

                    $isComplete = false;
                    if ($isCurrentUser) {
                        $flow->complete($this->_user->uniqueId);

                        if ($flow->isStepComplete()) {
                            $isComplete = true;
                            $flow->flowTo();
                        }
                    }
                    $modelFlow = $flow->getHandler($flow->getHandlerClass());
                    $modelFlow->updateFlow($flow);

                    if ($flow->currentStepId != '^end') {
                        $isDoneTudu = false;
                    }

                    // 发送下一步
                    if (false === strpos($flow->currentStepId, '^') && $isComplete) {

                        $section    = $flow->getStepSection($flow->currentStepId);
                        $tuduParams = array('tuduid' => $tudu->tuduId, 'type' => $tudu->type, 'fromtudu' => $tudu);
                        $users      = array();

                        foreach ($section as $sec) {
                            $users[$sec['username']] = array(
                                'uniqueid' => $sec['uniqueid'],
                                'truename' => $sec['truename'],
                                'username' => $sec['username'],
                                'email'    => $sec['username']
                            );
                        }

                        $step = $flow->getStep($flow->currentStepId);
                        if ($step['type'] == 1) {
                            $tuduParams['reviewer'] = $users;
                        } else {
                            $tuduParams['to'] = $users;
                            if ($step['type'] == 2) {
                                $tuduParams['acceptmode'] = 1;
                            }
                        }

                        $sendTudu  = new Model_Tudu_Tudu($tuduParams);
                        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

                        $params = $sendTudu->getStorageParams();

                        if (!empty($params['to'])) {
                            $accepters = $daoTudu->getAccepters($tudu->tuduId);
                            foreach ($accepters as $item) {
                                $daoTudu->removeAccepter($tudu->tuduId, $item['uniqueid']);
                            }
                        }

                        $daoTudu->updateTudu($tudu->tuduId, $params);
                        $modelTudu = new Model_Tudu_Send_Common();
                        $modelTudu->send($sendTudu);

                        // 更新进度
                        $manager->updateProgress($tudu->tuduId, $this->_user->uniqueId);
                        if ($tudu->parentId) {
                            $manager->calParentsProgress($tudu->parentId);
                        }
                    }
                }
            }

            // 自动确认
            if ($isDoneTudu && isset($doneParams)) {
                $manager->doneTudu($doneParams['tuduid'], true, 0);

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $doneParams['tuduid'],
                    Dao_Td_Log_Log::ACTION_TUDU_DONE,
                    array('percent' => $doneParams['percent'], 'elapsedtime' => $doneParams['elapsedtime']),
                    false,
                    true
                );
            }

            // 回复消息
            $data = implode(' ', array(
            'tudu',
            'reply',
            '',
            http_build_query($sendParam)
            ));

            $httpsqs->put($data, 'tudu');
        }

        return $this->json(true, $this->lang['post_send_success'], array('postid' => $postId));
    }

    /**
     * /compose/send-out
     *
     * 添加外发人员
     */
    public function sendOutAction()
    {

    }

    /**
     * 获取日志信息
     *
     * @param $params
     * @param $fromTudu
     */
    private function _getLogDetail($params, $fromTudu)
    {
        if (null === $fromTudu || $fromTudu->isDraft) {
            $to = array();
            if ($params['type'] == 'task' && !empty($params['to'])) {
                foreach ($params['to'] as $item) {
                    foreach ($item as $val) {
                        $to[] = array(
                            'email'        => $val['email'],
                            'truename'     => $val['truename'],
                        );
                    }
                }
                $params['to'] = !empty($to) ? Tudu_Tudu_Storage::formatReceiver($to) : null;
            } else {
                $params['to'] = !empty($params['to']) ? Tudu_Tudu_Storage::formatReceiver($params['to']) : null;
            }
            $params['cc'] = !empty($params['cc']) ? Tudu_Tudu_Storage::formatReceiver($params['cc']) : null;
            $params['bcc'] = !empty($params['bcc']) ? Tudu_Tudu_Storage::formatReceiver($params['bcc']) : null;
            //$params['reviewer'] = !empty($params['reviewer']) ? Tudu_Tudu_Storage::formatReceiver($params['reviewer']) : null;

            foreach ($params as $key => $val) {
                if (null === $val || '' === $val || $key == 'attach') {
                    unset($params[$key]);
                }
            }

            return $params;
        }

        $excepts = array('attach', 'uniqueid', 'status', 'poster', 'posterinfo', 'lastposter', 'issend');

        $tudu = $fromTudu->toArray();
        $ret  = array();
        foreach ($params as $key => $val) {
            if (in_array($key, $excepts) || empty($val)) {
                continue ;
            }

            if ($key == 'to') {
                if ($tudu['type'] == 'task') {
                    if (strpos($tudu['stepid'], '^') === false) {
                        $users = $this->getDao('Dao_Td_Tudu_Step')->getUsers($tudu['tuduid'], $tudu['stepid']);
                        $accepter = array();
                        foreach ($users as $user) {
                            list($email, $name) = explode(' ', $user['userinfo'], 2);
                            $accepter[] = array('email' => $email);
                        }
                        $tudu['accepter'] = $accepter;
                    }
                    $to = array();
                    foreach ($params['to'] as $item) {
                        foreach ($item as $val) {
                            $to[$val['email']] = array(
                                'email'        => $val['email'],
                                'truename'     => $val['truename'],
                            );
                        }
                    }
                    $params[$key] = $to;
                }

                if (count($params[$key]) != count($tudu['accepter'])) {
                    $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                } else {
                    foreach ($params[$key] as $k => $val) {
                        if (!in_array($k, $tudu['accepter'])) {
                            $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                        }
                    }
                }
                continue ;
            }

            if ($key == 'cc' || $key == 'bcc'/* || $key == 'reviewer'*/) {
                $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
            }

            if (array_key_exists($key, $tudu) && $params[$key] != $tudu[$key]) {
                $ret[$key] = $val;
            }
        }

        return $ret;
    }

    /**
     * 获取图度参数
     */
    private function _formatTuduParams(array $set, $suffix = '')
    {
        $ret = array();
        $type = !empty($set['type' . $suffix]) ? $set['type' . $suffix] : 'task';

        foreach ($this->_tuduParams as $col => $name) {
            $key = $name . $suffix;
            switch ($col) {
                case 'subject':
                case 'boardid':
                case 'classid':
                case 'flowid':
                case 'content':
                case 'parentid':
                case 'prevtuduid':
                case 'file':
                case 'attachment':
                    if (isset($set[$key])) {
                        $ret[$col] = $set[$key];
                    }
                    break;
                case 'starttime':
                case 'endtime':
                    $ret[$col] = !empty($set[$key]) ? strtotime($set[$key]) : null;
                    break;
                case 'priority':
                case 'privacy':
                case 'notifyall':
                case 'cycle':
                case 'isauth':
                case 'needconfirm':
                case 'istop':
                case 'acceptmode':
                    $ret[$col] = !empty($set[$key]) ? 1 : 0;
                    break;
                case 'password':
                    if (empty($set['privacy' . $suffix]) || empty($set['open_pwd' . $suffix])) {
                        $ret[$col] = null;
                    } else {
                        $ret[$col] = !empty($set[$key]) ? $set[$key] : null;
                    }
                    break;
                case 'to':
                    if ($type == 'task') {
                        if ($suffix) {
                            $key = 'ch-' . $key;
                        }
                        if (!empty($set[$key])) {
                            $ret[$col] = $this->_formatReviewer($set[$key]);
                        }

                        if (isset($set[$name . 'idx' . $suffix]) && is_array($set[$name . 'idx' . $suffix])) {
                            $recIdx = $set[$name . 'idx' . $suffix];
                            $rec = array();
                            foreach ($recIdx as $index) {
                                $info = $set[$name . '-' . $index . $suffix];
                                $arr  = explode(' ', $info, 2);
                                $item = array('truename' => $arr[1], 'email' => $arr[0]);

                                if (isset($set[$name . '-percent-' . $index . $suffix])) {
                                    $item['percent'] = (int) $set[$name . '-percent-' . $index . $suffix];
                                }

                                if (!$item['email']) {
                                    $rec[] = $item;
                                } else {
                                    $rec[$item['email']] = $item;
                                }
                            }
                        }

                        if (!empty($rec) && !empty($ret[$col])) {
                            $recKeys = array_keys($rec);
                            foreach ($ret[$col] as &$item) {
                                foreach ($item as &$to) {
                                    if (in_array($to['email'], $recKeys) && isset($rec[$to['email']]['percent'])) {
                                        $to['percent'] = $rec[$to['email']]['percent'];
                                    }
                                }
                            }
                        }
                    } else {
                        $ret[$col] = $this->_getReceiver($set, $name, $suffix);
                    }
                    break;
                case 'cc':
                case 'bcc':
                    $ret[$col] = $this->_getReceiver($set, $name, $suffix);
                    break;
                case 'reviewer':
                    if (!empty($set[$key])) {
                        $ret[$col] = $this->_formatReviewer($set[$key]);
                    }
                    break;
            }
        }

        if (!empty($set['nd-attach' . $suffix])) {
            $ret['attachment'] = array_diff($ret['attachment'], $set['nd-attach' . $suffix]);

            $daoNdFile     = $this->getDao('Dao_Td_Netdisk_File');
            $daoAttachment = $this->getDao('Dao_Td_Attachment_File');

            foreach ($set['nd-attach' . $suffix] as $ndfileId) {
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
                    $ret['attachment'][] = $fileId;
                }
            }
        }

        if ($type == 'notice') {
            $ret['istop'] = 0;
            if (!empty($ret['endtime']) && $ret['endtime'] >= strtotime('today')) {
                $ret['istop'] = 1;
            }
        }

        // 讨论，投票
        if ($type == 'discuss' && !empty($set['vote' . $suffix])) {
            $extVote = new Tudu_Tudu_Extension_Vote();
            if (!Tudu_Tudu_Extension::isRegistered('vote')) {
                $extVote = new Tudu_Tudu_Extension_Vote();
                Tudu_Tudu_Extension::registerExtension('vote', $extVote);
            }

            $ret['vote']    = Tudu_Tudu_Extension::getExtension('vote')->formatParams($set, $suffix);

            $ret['special'] = Dao_Td_Tudu_Tudu::SPECIAL_VOTE;
        }

        // 会议
        if ($type == 'meeting') {
            if (!Tudu_Tudu_Extension::isRegistered('meeting')) {
                $extMeeting = new Tudu_Tudu_Extension_Meeting();
                Tudu_Tudu_Extension::registerExtension('meeting', $extMeeting);
            }
            $extMeeting = new Tudu_Tudu_Extension_Meeting();

            $ret['meeting'] = Tudu_Tudu_Extension::getExtension('meeting')->formatParams($set, $suffix);
        }

        // 周期
        if (($type == 'task' || $type == 'meeting') && isset($set['cycle'])) {

            if (!Tudu_Tudu_Extension::isRegistered('cycle')) {
                $extCycle = new Tudu_Tudu_Extension_Cycle();
                Tudu_Tudu_Extension::registerExtension('cycle', $extCycle);
            }

            $ret['cycle'] = Tudu_Tudu_Extension::getExtension('cycle')->formatParams($set, $suffix);

            if ($ret['cycle']['endtype'] == Dao_Td_Tudu_Cycle::END_TYPE_COUNT && $ret['cycle']['endcount'] <= 0) {
                return $this->json(false, $this->lang['invalid_endcount']);
            }

            if ($ret['cycle']['endtype'] == Dao_Td_Tudu_Cycle::END_TYPE_DATE && !$ret['cycle']['enddate']) {
                return $this->json(false, $this->lang['invalid_enddate']);
            }

            $ret['special'] = Dao_Td_Tudu_Tudu::SPECIAL_CYCLE;
            $ret['cycleid'] = $ret['cycle']['cycleid'];
        }

        $ret['type']       = $type;
        $ret['uniqueid']   = $this->_user->uniqueId;
        $ret['orgid']      = $this->_user->orgId;
        $ret['status']     = Dao_Td_Tudu_Tudu::STATUS_UNSTART;
        $ret['poster']     = $this->_user->trueName;
        $ret['email']      = $this->_user->userName;
        $ret['posterinfo'] = $this->_user->position;
        $ret['lastposter'] = $this->_user->trueName;
        $ret['issend']     = 1;

        return $ret;
    }

    /**
     * 格式化接收人（抄送，接收，审批等）参数
     *
     * @param $key
     * @param $suffix
     */
    private function _getReceiver($params, $name, $suffix = '')
    {
        $ret = array();
        if (isset($params[$name . 'idx' . $suffix]) && is_array($params[$name . 'idx' . $suffix])) {
            $recIdx = $params[$name . 'idx' . $suffix];
            $rec = array();
            foreach ($recIdx as $index) {
                $info = $params[$name . '-' . $index . $suffix];
                $arr  = explode(' ', $info, 2);
                $item = array('truename' => $arr[1], 'email' => $arr[0]);

                if (isset($params[$name . '-percent-' . $index . $suffix])) {
                    $item['percent'] = (int) $params[$name . '-percent-' . $index . $suffix];
                }

                if (!$item['email']) {
                    $ret[] = $item;
                } else {
                    $ret[$item['email']] = $item;
                }
            }

        } else {
            if ($name == 'to' && $suffix) {
                $name = 'ch-' . $name;
            }

            if (isset($params[$name . $suffix])) {
                $ret = Tudu_Tudu_Storage::formatRecipients($params[$name . $suffix]);
            }
        }

        return $ret;
    }

    /**
     * 格式化审批人
     * @param string $reviewer
     */
    private function _formatReviewer($reviewer)
    {
        $arr  = explode("\n", $reviewer);
        $asyn = 1;
        $ret  = array();
        foreach ($arr as $item) {
            $item = trim($item);

            if (!$item || 0 === strpos($item, '>') || 0 === strpos($item, '+')) {
                $asyn = $item == '+';
                continue ;
            }

            list ($userName, $trueName) = explode(' ', $item);
            if (!$asyn) {
                $ret[] = array(array('truename' => $trueName, 'email' => $userName));
            } else {
                end($ret);
                $last = key($ret);

                $ret[$last][] = array('truename' => $trueName, 'email' => $userName);
            }
        }

        return $ret;
    }
}