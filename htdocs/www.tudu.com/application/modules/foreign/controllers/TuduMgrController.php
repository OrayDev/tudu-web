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
 * @version    $Id: TuduMgrController.php 2063 2012-08-17 08:22:45Z chenyongfa $
 */

/**
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Foreign_TuduMgrController extends TuduX_Controller_Foreign
{
    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));
        $this->view->LANG = $this->lang;

        if (null === $this->_tudu || null === $this->_user) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
    }

    /**
     * 接受图度
     */
    public function acceptAction()
    {
        // 图度不能是已确定状态
        if ($this->_tudu->isDone) {
            return $this->json(false, $this->lang['tudu_is_done']);
        }

        // 图度不能是“已完成”，“已拒绝”, “已取消”状态
        if ($this->_tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DOING) {
            return $this->json(false, $this->lang['perm_deny_accept_tudu']);
        }

        // 操作人必须为图度执行人
        if (!in_array($this->_user['email'], $this->_tudu->accepter) && $this->_user['role'] != Dao_Td_Tudu_Tudu::ROLE_ACCEPTER) {
            return $this->json(false, $this->lang['perm_deny_accept_tudu']);
        }

        $ret = $this->_manager->acceptTudu($this->_tudu->tuduId, $this->_user['uniqueid'], (int) $this->_tudu->selfPercent);

        if (!$ret) {
            return $this->json(false, $this->lang['accept_failure']);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $this->_tudu->tuduId, 
            Dao_Td_Log_Log::ACTION_TUDU_ACCEPT,
            array('accepttime' => time(), 'status' => Dao_Td_Tudu_Tudu::STATUS_DOING)
        );

        return $this->json(true, $this->lang['accept_success']);
    }

    /**
     * 拒绝
     */
    public function rejectAction()
    {
        // 图度不能是已确定状态
        if ($this->_tudu->isDone) {
            return $this->json(false, $this->lang['tudu_is_done']);
        }

        // 图度不能是“已完成”，“已拒绝”, “已取消”状态
        if ($this->_tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DOING) {
            return $this->json(false, $this->lang['perm_deny_reject_tudu']);
        }
        
        // 操作人必须为图度执行人
        if (!in_array($this->_user['email'], $this->_tudu->accepter) && $this->_user['role'] != Dao_Td_Tudu_Tudu::ROLE_ACCEPTER) {
            return $this->json(false, $this->lang['perm_deny_reject_tudu']);
        }

        $tuduStatus = $this->_manager->rejectTudu($this->_tudu->tuduId, $this->_user['uniqueid']);

        if (false !== $tuduStatus) {
            $this->_manager->deleteLabel($this->_tudu->tuduId, $this->_user['uniqueid'], '^a');

            // 拒绝后任务状态为完成的，生成周期任务
            if ($this->_tudu->cycleId && $tuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $config = $this->_bootstrap->getOption('httpsqs');

                // 插入消息队列
                $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

                $this->_manager->updateTudu($this->_tudu->tuduId, array('cycleid' => null));

                $data = implode(' ', array(
                    'tudu',
                    'cycle',
                    '',
                    http_build_query(array(
                        'tuduid' =>  $this->_tudu->tuduId,
                        'tsid' => $this->_tsId,
                        'cycleid' => $this->_tudu->cycleId
                        ))
                    ));
                $httpsqs->put($data, 'tudu');
            }

            if ($this->_tudu->parentId) {
                $this->_manager->calParentsProgress($this->_tudu->parentId);
            }

            // 添加操作日志
            $this->_writeLog(
                Dao_Td_Log_Log::TYPE_TUDU,
                $this->_tudu->tuduId,
                Dao_Td_Log_Log::ACTION_TUDU_DECLINE,
                array('selfstatus' => Dao_Td_Tudu_Tudu::STATUS_REJECT, 'status' => $tuduStatus)
            );

            return $this->json(true, $this->lang['reject_success']);
        }

        return $this->json(false, $this->lang['reject_failure']);

    }

    /**
     * 认领
     */
    public function claimAction()
    {
        // 获取步骤
        $step = $this->_manager->getStep($this->_tudu->tuduId, $this->_tudu->stepId);
        // 判断当前是否为认领操作
        if ($step->type != Dao_Td_Tudu_Step::TYPE_CLAIM) {
            return $this->json(false, $this->lang['step_not_claim']);
        }
        // 判读图度是否已有user认领
        if ($this->_tudu->acceptTime) {
            return $this->json(false, $this->lang['tudu_has_already_claim']);
        }

        $ret = $this->_manager->claimTudu($this->_tudu->tuduId, $this->_tudu->orgId, $this->_user['uniqueid']);

        if (!$ret) {
             return $this->json(false, $this->lang['tudu_claim_failure']);
        }

        //创建回复
        $content = sprintf($this->lang['claim_accepter_log'], $this->_user['truename']);
        $params = array(
            'orgid'      => $this->_tudu->orgId,
            'boardid'    => $this->_tudu->boardId,
            'tuduid'     => $this->_tudu->tuduId,
            'uniqueid'   => $this->_user['uniqueid'],
            'poster'     => $this->_user['truename'],
            'email'      => $this->_user['email'],
            'postid'     => Dao_Td_Tudu_Post::getPostId($this->_tudu->tuduId),
            'content'    => $content,
            'lastmodify' => implode(chr(9), array($this->_user['uniqueid'], time(), $this->_user['truename']))
        );
        $postId = $this->_manager->createPost($params);
        $this->_manager->sendPost($this->_tudu->tuduId, $postId);

        // 标记为未读 
        $this->_manager->markAllUnRead($this->_tudu->tuduId);

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $this->_tudu->tuduId,
            Dao_Td_Log_Log::ACTION_TUDU_CLAIM,
            array('claimuser' => $this->_user['truename'], 'claimtime' => time(), 'status' => Dao_Td_Tudu_Tudu::STATUS_DOING)
        );

        $notifyTo = array($this->_tudu->sender);
        $notifyTo = array_merge($notifyTo, array_keys($this->_tudu->to));
        if ($this->_tudu->notifyAll) {
            $notifyTo = array_merge($notifyTo, array_keys($this->_tudu->cc));
        }

        // 消息队列
        $config  = $this->_bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        $tpl = <<<HTML
<strong>您刚收到一个新的回复</strong><br />
<a href="http://{$this->_request->getServer('HTTP_HOST')}/frame#m=view&tid=%s&page=1" target="_blank" _tid="{$this->_tudu->tuduId}">%s</a><br />
发起人：{$this->_user['truename']}<br />
更新日期：%s<br />
$content
HTML;
        $data = implode(' ', array(
            'tudu',
            'reply',
            '',
            http_build_query(array(
                'tuduid' =>  $this->_tudu->tuduId,
                'from' => $this->_user['email'],
                'to' => implode(',', $notifyTo),
                'content' => sprintf($tpl, $this->_tudu->tuduId, $this->_tudu->subject, date('Y-m-d H:i:s', time()))
            ))
        ));

        $httpsqs->put($data);

        return $this->json(true, $this->lang['tudu_claim_success']);
    }
 
    /**
     * 审批
     */
    public function reviewAction()
    {
        $isAllow = $this->_request->getParam('type');
        
        
    }

    /**
     * 投票
     */
    public function voteAction()
    {
        $tuduId = $this->_request->getPost('tid');
        $voteId = $this->_request->getPost('voteid');
        $option = (array) $this->_request->getPost('option-' . $voteId);

        if (!$tuduId) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        if (!$voteId) {
            return $this->json(false, 'invalid_voteid');
        }

        if (empty($option)) {
            return $this->json(false, $this->lang['no_selected_option']);
        }

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));

        if (!$vote || (!empty($vote->expireTime) && $vote->expireTime + 86400 < time())) {
            return $this->json(false, $this->lang['vote_is_invalid']);
        }

        if ($vote->maxChoices != 0 && count($option) > $vote->maxChoices) {
            return $this->json(false, sprintf($this->lang['more_vote_option'], $vote->maxChoices));
        }

        $tudu = $this->_tudu;
        if($tudu->isDone) {
            return $this->json(false, $this->lang['vote_is_close']);
        }

        $voter = $this->_user['email'] . ' ' . $this->_user['truename'];
        $ret = $daoVote->vote($tuduId, $voteId, $option, $this->_user['uniqueid'], $voter);

        if (!$ret) {
            return $this->json(false, $this->lang['vote_failure']);
        }

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));
        $vote->getOptions();
        $vote->countVoter();

        return $this->json(true, $this->lang['vote_success'], $vote->toArray());
    }
}