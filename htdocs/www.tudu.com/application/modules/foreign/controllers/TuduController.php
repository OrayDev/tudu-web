<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: TuduController.php 2063 2012-08-17 08:22:45Z chenyongfa $
 */

/**
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Foreign_TuduController extends TuduX_Controller_Foreign
{
    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));
        $this->view->LANG = $this->lang;

        if (null === $this->_tudu || null === $this->_user) {
            $this->jump('/foreign/index/invalid');
        }

        if (!$this->_isValid()) {
            $this->jump("/foreign/index/?tid={$this->_tudu->tuduId}&fid={$this->_user['uniqueid']}&ts={$this->_tsId}");
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        return $this->viewAction();
    }

    /**
     * 查看图度内容
     *
     */
    public function viewAction()
    {
        $tudu      = $this->_tudu;
        $votes     = array();
        $isForward = (boolean) $this->_request->getQuery('forward');

        $isAccepter = $this->_tudu->role == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER;
        $isCC       = !$isAccepter;

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $access = array(
            'view'     => true,
            'forward'  => $isAccepter,
            'reply'    => $isCC || $isAccepter,
            'accept'   => $isAccepter && !$tudu->selfAcceptTime && $this->_tudu->selfTuduStatus < 2,
            'reject'   => $isAccepter && $this->_tudu->selfTuduStatus < 2,
            'progress' => $isAccepter && $tudu->selfAcceptTime,
            'upload'   => true,
            'claim'    => false,
            'agree'    => false,
            'disagree' => false
        );

        $upload = $this->_options['upload'];
        $upload['cgi']['upload'] .= '?authtype=foreign&' . session_name() . '=' . $this->_sessionId
                                  . '&email=' . $this->_session->auth['address'];
        //var_dump(serialize($_SESSION));
        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));
        $this->view->upload = $upload;

        // 草稿，显示编辑界面
        if ($isForward) {
            $access = array(
                'upload' => true
            );
            $attachments = array();

            if ($tudu->cycleId) {
                $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');

                $cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId));

                $this->view->cycle = $cycle->toArray();
            }

            $type = $tudu->type;
            $tudu = $tudu->toArray();

            if ($isForward) {
                $tudu['to'] = array();
                $tudu['cc'] = array();
                $this->view->action = 'forward';
            }

            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            $classes  = $daoClass->getClassesByBoardId($this->_tudu->orgId, $tudu['boardid'], array('ordernum' => 'ASC'));

            // 处理快捷板块
            $attentions = array('children' => array());
            $boards = array();

            if ($isForward) {
                $daoBoard = $this->getDao('Dao_Td_Board_Board');
                $board    = $daoBoard->getBoard(array('orgid' => $this->_tudu->orgId, 'boardid' => $tudu['boardid']));
                $boards[] = $board->toArray();
            }

            $users = $this->_deliver->getTuduUsers($this->_tudu->tuduId);

            $this->view->classes= $classes->toArray();
            $this->view->users  = $users;
            $this->view->boards = $boards;
            $this->view->tudu   = $tudu;
            $this->view->access = $access;
            $this->view->isforward = $isForward;

            $this->render('modify_' . $type);
            return ;
        }

        // 会议信息
        if ($tudu->type == 'meeting') {
            $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');
            $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu->tuduId));

            if (null !== $meeting) {
                $this->view->meeting = $meeting->toArray();
            }
        }

        // 读取投票
        if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            /**
             * @var Dao_Td_Tudu_Vote
             */
            $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

            $votes = $daoVote->getVotesByTuduId($tudu->tuduId);

            $votes = $votes->toArray();
            $votes = $daoVote->formatVotes($votes);

            foreach ($votes as $voteId => &$vote) {
                // 统计参与人
                $vote['countvoter'] = $daoVote->countVoter($tudu->tuduId, $voteId);
                $isVoted            = $daoVote->hasVote($tudu->tuduId, $voteId, $this->_user['uniqueid']);
                $expired            = !empty($vote['expiretime']) && time() > $vote['expiretime'] + 86400;
                $vote['expired']    = $expired;
                $vote['isvoted']    = $isVoted;
                $vote['enabled']    = !$isVoted && !$expired;
            }
        }

        // 读取周期任务信息
        /* @var $daoCycle Dao_Td_Tudu_Cycle */
        $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');
        if ($cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId))) {
            $this->view->cycleremind = $this->_formatCycleInfo($cycle->toArray(), $tudu);
        }

        // 处理任务相关逻辑
        if ($tudu->type == 'task') {

            // 任务类权限的特殊过滤
            // 已确认完成时，禁止操作的权限
            if ($tudu->isDone) {
                $access['reply']  = false;
            }

            // 已完成（完成、取消、拒绝）时，禁止操作的权限
            if ($isAccepter && $tudu->selfTuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $access['accept']   = false;
                $access['reject']   = false;
                $access['forward']  = false;
                if (count($tudu->accepter) > 1 && $tudu->selfTuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $access['progress'] = false;
                }
            }

            if ($tudu->stepId && strpos($tudu->stepId, '^') !== 0) {
                /* @var $daoStep Dao_Td_Tudu_Step */
                $daoStep = $this->getDao('Dao_Td_Tudu_Step');

                $step = $daoStep->getCurrentStep($tudu->tuduId, $tudu->stepId, $this->_user['uniqueid']);

                if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    if ($step['uniqueid'] == $this->_user['uniqueid'] && $step['status'] == 1 && !$tudu->isDone) {
                        $access['agree']  = true;
                        $access['disagree']  = true;
                    }
                    $access['forward']  = false;
                    $access['accept']   = false;
                    $access['reject']   = false;
                    $access['progress'] = false;
                }
            }

            if ($tudu->status >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $access['progress'] = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['forward']  = false;
            }

            if ($isAccepter && $tudu->acceptMode && !$tudu->acceptTime) {
                $access['claim']    = true;
                $access['forward']  = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['progress'] = false;
                $access['agree']    = false;
                $access['disagree'] = false;
            }

            $remind = '';

            $to = array();
            foreach ($tudu->to as $u) {
                $to[] = $u[0];
            }
            $to = implode($this->lang['comma'], $to);

            if ($tudu->lastForwarder) {
                $remind .= sprintf($this->lang['forward_info'], $tudu->from[0], date('Y-m-d H:i:s', $tudu->lastForwardTime), $tudu->lastForwarder, $to);
            } elseif ($tudu->acceptMode && !$tudu->acceptTime) {
                $remind .= sprintf($this->lang['claim_info'], $tudu->from[0], $to);
            } elseif ($tudu->acceptMode && $tudu->acceptTime) {
                $remind .= sprintf($this->lang['claim_accepter_info'], $tudu->from[0], $to);
            } else {
                $remind .= sprintf($this->lang['send_info'], $tudu->from[0], $to);
            }

            if ($tudu->accepter) {
                if ($isAccepter) {

                    // 已过期
                    if ($tudu->isExpired) {

                        $remind .= sprintf($this->lang['remind_expried'], Oray_Function::dateDiff('d', $tudu->endTime, time()));

                    // 未接受
                    } elseif (!$tudu->acceptMode && !$tudu->selfAcceptTime && $tudu->selfTuduStatus <= Dao_Td_Tudu_Tudu::STATUS_DOING) {

                        if ($tudu->endTime) {
                            $remind .= sprintf($this->lang['remind_time_left'], Oray_Function::dateDiff('d', time(), $tudu->endTime) + 1)
                                     . sprintf($this->lang['remind_unaccepted'], $tudu->from[0]);
                        } else {
                            $remind .= sprintf($this->lang['remind_unaccepted'], $tudu->from[0]);
                        }
                    }
                }

                $this->view->remind = $remind;
            }
        // 会议
        } elseif ($tudu->type == 'meeting') {

            $access['forward'] = false;
            $access['progress'] = false;

            if ($tudu->isDone) {
                $access['reply']  = false;
                $access['modify'] = false;
            }

            // 显示发起人提醒信息
            if ($isAccepter && $meeting) {

                $startTime = $meeting->isAllday ? date('Y-m-d', $tudu->startTime) : date('Y-m-d H:i', $tudu->startTime);

                $remind = sprintf($this->lang['remind_meeting_left'], $tudu->from[0], $startTime);

                // 未接受
                if (!$tudu->selfAcceptTime) {
                    $remind .= $this->lang['remind_meeting_accept'];
                }

                $this->view->remind = $remind;
            }

        } else {
            $access['accept']   = false;
            $access['reject']   = false;
            $access['forward']  = false;
            $access['progress'] = false;
            $access['review']   = false;
            $access['reply']    &= !$tudu->isDone;
        }

        // 增加浏览次数
        $daoTudu->hit($tudu->tuduId);

        // 读取回复列表
        $uniqueId    = $this->_request->getQuery('unid');
        $back        = $this->_request->getQuery('back');
        $recordCount = $tudu->replyNum + 1;
        $pageSize    = 20;
        $isInvert    = $this->_request->getQuery('invert', 0);
        $page        = max(1, (int) $this->_request->getQuery('page', 1));

        $query = array(
            'tid'    => $tudu->tuduId,
            'back'   => $back,
            'invert' => $isInvert
        );

        $condition = array(
            'tuduid' => $tudu->tuduId
        );

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
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
            'modify' => false,
            'delete' => false
        );

        foreach ($posts as  $key => $post) {
            // 公告过滤不可见的回复
            if ($tudu->type == 'notice' && !$post['isfirst'] && !in_array('^v', $tudu->labels) && !in_array('^e', $tudu->labels)) {
                unset($posts[$key]);
                continue;
            }

            // 读取回复的附件信息
            if ($post['attachnum'] > 0) {
                $files = $this->getDao('Dao_Td_Attachment_File')->getFiles(array(
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

        $isDisagreed = false;
        if ($tudu->type == 'task' && $tudu->stepNum > 0) {
            $daoStep = $this->getDao('Dao_Td_Tudu_Step');

            $users = $daoStep->getTuduStepUsers($tudu->tuduId);

            $steps = array();
            $isExceed = false;
            foreach ($users as &$user) {
                $info = explode(' ', $user['userinfo']);
                $user['email']    = $info[0];
                $user['truename'] = $info[1];

                if (!$isExceed && $user['stepid'] == $tudu->stepId) {
                    $isExceed = true;
                }

                if ($isExceed && ($user['stepid'] != $tudu->stepId || ($user['type'] == 1 && $user['status'] < 1))) {
                    $user['future'] = true;
                }

                $steps[$user['ordernum']]['users'][] = $user;
                $steps[$user['ordernum']]['stepid']  = $user['stepid'];
                $steps[$user['ordernum']]['type']    = $user['type'];
                $steps[$user['ordernum']]['future']  = !empty($user['future']);

                if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['status'] > 2) {
                    $access['progress'] = false;
                    $access['divide']   = false;
                    $isDisagreed = true;
                }
            }

            ksort($steps);

            if (!$isDisagreed && count($steps)) {
                $lastStep = end($steps);

                if ($lastStep['type'] == 0) {
                    $arrTo = array();
                    foreach ($lastStep['users'] as $u) {
                        $arrTo[$u['email']] = array($u['truename'], null, null, $u['email']);
                    }

                    $tudu->to = $arrTo;

                    if (!isset($arrTo[$this->_user['email']])) {
                        $access['accept'] = false;
                        $access['reject'] = false;
                    }
                }

                reset($steps);
            }

            if (count($steps) > 1) {
                $this->view->steps = $steps;
            }
        }

        $this->view->tudu      = $tudu->toArray();
        $this->view->posts     = $posts;
        $this->view->pageinfo  = array(
            'currpage'    => $page,
            'pagecount'   => $pageCount,
            'pagesize'    => $pageSize,
            'recordcount' => $recordCount,
            'query'       => $query,
            'url'         => '/tudu/view'
        );
        $this->view->last   = $isLast;
        //$this->view->cookies= serialize($cookies);
        $this->view->votes     = $votes;
        $this->view->query     = $query;
        $this->view->access    = $access;
        $this->view->isinvert  = $isInvert;
        return $this->render('view_' . $tudu->type);
    }

    /**
     * 显示打印页面
     */
    public function printAction()
    {
        $vote = array();
        // 读取投票
        if ($this->_tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            /**
             * @var Dao_Td_Tudu_Vote
             */
            $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

            $vote = $daoVote->getVoteByTuduId($this->_tudu->tuduId);

            $vote->getOptions();

            $isVoted = $vote->isVoted($this->_user['uniqueid']);

            $vote = $vote->toArray();
            $vote['expired'] = $vote['expiretime'] && time() > $vote['expiretime'];
            $vote['isvoted'] = $isVoted;
            $vote['enabled'] = !$vote['isvoted'] && !$vote['expired'];
        }

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));

        /* @var $daoTudu Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        // 获取回复内容
        $posts = $daoPost->getPosts(array('tuduid' => $this->_tudu->tuduId), null, 'createtime ASC')->toArray();

        $this->view->tudu  = $this->_tudu->toArray();
        $this->view->posts = $posts;
        $this->view->vote  = $vote;
    }

    /**
     * 回复编辑页面
     */
    public function postAction()
    {
        $postId = $this->_request->getQuery('pid');

        $post = array();
        $access = array(
            'upload'   => true,
            'progress' => $this->_user['role'] == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER
        );

        $content = $this->_request->getPost('content');

        if (!empty($content)) {
            $post['content'] = $content;
        }

        if ($postId) {
            /* @var $daoPost Dao_Td_Tudu_Post */
            $daoPost = $this->getDao('Dao_Td_Tudu_Post');

            $post = $daoPost->getPost(array(
                'tuduid' => $this->_tudu->tuduId,
                'postid' => $postId
            ));

            if ($post === null) {
                return Oray_Function::alert($this->lang['post_not_exists']);
            }

            // 不是回复者时，读取版主的权限
            if ($post->uniqueId !== $this->_user->uniqueId) {
                Oray_Function::alert($this->lang['perm_deny_update_post']);
            }

            if ($post->attachNum > 0) {

                /* @var $daoFile Dao_Td_Attachment_File */
                $daoFile = $this->getDao('Dao_Td_Attachment_File');
                $attachments = $daoFile->getFiles(array(
                    'tuduid' => $post->tuduId,
                    'postid' => $post->postId
                ));

                $post->attachments = $attachments->toArray();
            }

            $access['progress'] = $post->isLog
                                && in_array($this->_user->email, $tudu->accepter)
                                && $tudu->selfTuduStatus < Dao_Td_Tudu_Tudu::STATUS_DONE;

            $post = $post->toArray();
        }

        $cookies = $this->_request->getCookie();

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));

        $upload = $this->_options['upload'];
        $upload['cgi']['upload'] .= '?authtype=foreign&' . session_name() . '=' . $this->_sessionId
                                  . '&email=' . $this->_user['email'];

        $this->view->upload  = $upload;
        $this->view->cookies = serialize($cookies);
        $this->view->post   = $post;
        $this->view->tudu   = $this->_tudu->toArray();
        $this->view->access = $access;
        $this->render('modify_post');
    }

    /**
     * 显示日志列表
     */
    public function logAction()
    {
        $daoLog = $this->getDao('Dao_Td_Log_Log');
        $logs   = $daoLog->getLogs(array(
            'orgid'      => $this->_tudu->orgId,
            'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
            'targetid'   => $this->_tudu->tuduId,
            'privacy'    => 0
        ))->toArray();

        $this->view->registFunction('format_log_detail', array($this, 'formatLogDetail'));

        $this->view->logs = $logs;
    }

    /**
     * 显示附件列表
     */
    public function attachAction()
    {
        $daoAttach = $this->getDao('Dao_Td_Attachment_File');
        $attachs = $daoAttach->getTuduFiles(array(
            'tuduid' => $this->_tudu->tuduId
        ))->toArray();

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));
        $this->view->attachs = $attachs;
    }

    /**
     * 输出联系人列表
     */
    public function accepterAction()
    {
        $tuduId = $this->_request->getQuery('tid');

        $accepters = $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);

        foreach($accepters as &$accepter) {
            $accepter['accepttime']  = is_numeric($accepter['accepttime']) ? (int) $accepter['accepttime'] : null;
            $accepter['elapsedtime'] = round((float) $accepter['elapsedtime'], 1);

            $info = explode(' ', $accepter['accepterinfo'], 2);
            if (count($info) == 2) {
                $accepter['email']    = $info[0];
                $accepter['truename'] = $info[1];
            }

            $accepter['percent']  = (int) $accepter['percent'];

            if ($accepter['forwardinfo']) {
                $forwardInfo = explode("\n", $accepter['forwardinfo']);
                $accepter['forwardfrom'] = $forwardInfo[0];
                $accepter['forwardtime'] = (int) $forwardInfo[1];
            }

            $accepter['statustext'] = !$accepter['accepttime'] && $accepter['tudustatus'] != Dao_Td_Tudu_Tudu::STATUS_REJECT
                                    ? $this->lang['status_needaccept']
                                    : $this->lang['tudu_status_' . $accepter['tudustatus']];
        }

        $this->json(true, null, $accepters);
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
            $auth  = md5($this->_sessionId . $this->session->logintime);
            foreach ($array as $item) {
                $content = str_replace("AID:{$item}", $this->getAttachmentUrl($item, 'view'), $content);
            }
        }

        return $content;
    }

    /**
     * 处理文件大小
     *
     * @param $size
     */
    public function formatFileSize($size)
    {
        $base = 1024;
        $units = array(pow($base, 3) => 'GB', pow($base, 2) => 'MB', $base => 'KB');

        foreach ($units as $step => $unit) {
            $val = $size / $step;
            if ($val >= 1) {
                return round($val, 2) . $unit;
            }
        }

        return $size . 'B';
    }

    /**
     * 获取附件地址
     * @param $fid
     * @param $act
     */
    public function getAttachmentUrl($fid, $act = null)
    {
        $sid  = $this->_sessionId;
        $auth = md5($sid . $fid . $this->_session->auth['logintime']);

        $url = $this->_options['sites']['file']
             . $this->_options['upload']['cgi']['download']
             . "?sid={$sid}&fid={$fid}&auth={$auth}";

        if ($act) {
            $url .= '&action=' . $act;
        }

        return $url;
    }

    /**
     * 输出日志详细信息
     *
     * @param array $params
     */
    public function formatLogDetail(array $params, &$smarty)
    {
        if (empty($params['action'])) {
            return null;
        }

        $decpts = $this->lang['tudu_log'];
        $detail = $params['detail'];
        $action = $params['action'];

        if ($action == 'create' && $params['detail']['group']) {
            $action = 'create_group';
        }

        $ret = array($decpts['action_' . $action]);

        if (is_array($detail)) {
            foreach ($detail as $key => $val) {
                if (in_array($key, array('to', 'cc', 'reviewer')) && $val) {
                    $arr = explode("\n", $val);
                    $names = array();
                    foreach ($arr as $item) {
                        $item = explode(' ', $item);
                        if (empty($item[1])) continue ;
                        $names[] = $item[1];
                    }
                    if ($names) {
                        $ret[] = $decpts[$key] . $this->lang['cln'] . implode(',', $names);
                    }
                    continue ;
                }
                if (!array_key_exists($key, $decpts)) {
                    continue ;
                }

                switch ($key) {
                    case 'status':
                    case 'selfstatus':
                        $val = $this->lang['tudu_status_' . $val];
                        break;
                    case 'endtime':
                    case 'starttime':
                        if (!$val) {
                            continue ;
                        }
                        $val = date('Y-m-d', $val);
                        break;
                    case 'totaltime':
                        $val = round($val/3600, 1);
                        break;
                    case 'agree':
                        $val = !empty($val) ? $this->lang['agree'] : $this->lang['disagree'];
                        break;
                }

                $str = $decpts[$key] . $this->lang['cln'] . $val;
                if (array_key_exists($key . '_suffix', $decpts)) {
                    $str .= $decpts[$key . '_suffix'];
                }
                $ret[] = $str;
            }
        }

        $ret = implode(';&#13; ', $ret);
        if (!empty($params['assign'])) {
            $smarty->assign($params['assign'], $ret);
        } else {
            return $ret;
        }
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
     *
     * @param cycle $cycle
     */
    private function _formatCycleInfo($cycle, $tudu)
    {
        if (!$cycle) return null;

        $str  = $this->lang['cycle_remind'];
        $mode = $cycle['mode'];

        //$what = $cycle[$mode] . $this->lang[$mode];
        $what = '';

        switch ($mode) {
            case 'day':
                if ($cycle['what'] == 'workday') {
                    $what .= $this->lang['cycle_every'] . $this->lang['work_day'];
                } elseif ($cycle['type'] == 3) {
                    $what .= $this->lang['cycle_every_complete'] . $cycle['day'] . $this->lang['day'];
                } else {
                    $what .= $this->lang['cycle_every'] . $cycle['day'] . $this->lang['day'];
                }
                break;
            case 'week':
                if ($cycle['type'] == 3) {
                    $what = $this->lang['cycle_every_complete'] . $cycle['week'] . $this->lang['week'];
                } else {
                    $weeks = array();
                    foreach ($cycle['weeks'] as $val) {
                        $weeks[] = $this->lang['week_' . $val];
                    }
                    $what = sprintf($this->lang['cycle_repeat_week'], $cycle['week'], implode($this->lang['comma'], $weeks));
                }
                break;
            case 'month':
                if ($cycle['type'] == 1) {
                    $what = $this->lang['cycle_every'] . $cycle['month'] . $this->lang['cycle_month'] . $this->lang['cycle_number'] . $cycle['day'] . $this->lang['day'];
                } elseif ($cycle['type'] == 2) {
                    $what = $this->lang['cycle_every'] . $cycle['month'] . $this->lang['cycle_month'] . $this->lang['cycle_after'] . $this->lang['cycle_at_' . $cycle['at']];
                    if (isset($this->lang[$cycle['what']])) {
                        $what .= $this->lang[$cycle['what']];
                    } else {
                        $what .= $this->lang['date_' . $cycle['what']];
                    }
                } else {
                    $what = $this->lang['cycle_every_complete'] . $cycle['month'] . $this->lang['month'];
                }
                break;
        }

        $validTime = $tudu->startTime ? date('Y-m-d', $tudu->startTime) : date('Y-m-d', $tudu->createTime);
        if ($cycle['enddate'] && $cycle['endtype'] == 2) {
            $validTime .= ' - ' . date('Y-m-d', $cycle['enddate']);
        }

        $str = sprintf($str, $what, $validTime);

        if ($cycle['endcount'] && $cycle['endtype'] == 1) {
            $str .= sprintf($this->lang['cycle_repeat_times'], $cycle['endcount']);
        }

        return $str;
    }
}