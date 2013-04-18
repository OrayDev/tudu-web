<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Review.php 1957 2012-07-02 06:54:25Z web_op $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 审批流程实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Review extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     * 步骤列表
     *
     * @var array
     */
    protected $_steps = null;

    /**
     * 调用 Tudu_Model_Tudu_Compose中与当前类名相同的方法
     * 实现图度审批的流程
     *
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    public function composeHandler(Tudu_Model_Tudu_Entity_Tudu $tudu)
    {
        $user = Tudu_Model_Tudu_Abstract::getResource(Tudu_Model_Tudu_Abstract::RESOURCE_NAME_USER);

        if (!$tudu->tuduId) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('missing tuduid');
        }

        $error = null;
        do {

            $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

            $fromTudu = $daoTudu->getTuduById($user->uniqueId, $tudu->tuduId);

            if (null === $fromTudu) {
                $error = 'tudu not exists';
                break;
            }

            $daoStep  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
            $reviewer = $daoStep->getCurrentStep($fromTudu->tuduId, $fromTudu->stepId, $user->uniqueId);

            if (!$reviewer || $reviewer['type'] != Dao_Td_Tudu_Step::TYPE_EXAMINE || $reviewer['status'] != 1) {
                $error = 'disable review';
                break;
            }

            $tudu->stepId = $fromTudu->stepId;
            if ($fromTudu->flowId) {
                $tudu->flowId = $fromTudu->flowId;
            }

        } while (false);

        if (null !== $error) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception($error);
        }

        if ($tudu->type == 'notice') {
            // 公告 流程 ...
        }

        $isAgree = $tudu->isAgree;

        // 同意审批
        if ($isAgree) {
            $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 2));
            $daoTudu->addLabel($tudu->tuduId, $tudu->uniqueId, '^v');
            $tudu->currentStepStatus = false;

            // 有修改图度后续执行步骤
            if ($tudu->reviewer || $tudu->to) {
                $this->_updateTuduSteps($tudu);

            } else {
                $this->_agree($tudu);
            }
        } else {
            $this->_disAgree($tudu);
            $daoTudu->addLabel($tudu->tuduId, $tudu->uniqueId, '^v');
        }

        // 更新图度
        $attrs = $tudu->getAttributes();
        if (isset($attrs['to'])) {
            $params['to'] = Tudu_Model_Entity_Tudu::formatReceiver($attrs['to']);
        }
        if (isset($attrs['cc'])) {
            $params['cc'] = Tudu_Model_Entity_Tudu::formatReceiver($attrs['cc']);
        }

        $params['stepid'] = $attrs['stepid'];
        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        if (!$ret) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('tudu save failure');
        }

        $deliver = Tudu_Tudu_Deliver::getInstance();
        $recipients = $deliver->prepareRecipients($user->uniqueId, $user->userId, $tudu);
        $deliver->sendTudu($tudu, $recipients);

        $tudu->setAttribute('recipient', $recipients);

        // 发送回复
        $header = array(
            'action'         => 'review',
            'tudu-act-value' => $isAgree ? 1 : 0,
        );

        $headerKey = $tudu->reviewer ? 'tudu-reviewer' : 'tudu-to';
        $items     = $tudu->reviewer ? $tudu->reviewer : $tudu->to;
        $val       = array();
        if ($tudu->reviewer) {
            $items = $tudu->reviewer;
            $items = array_shift($items);
        } else {
            $items = $tudu->to;
        }

        if ($items) {
            foreach ($items as $item) {
                if (!empty($attrs['samereview'])) {
                    break;
                }
                $val[] = $item['truename'];

                if ($tudu->reviewer) {
                    break;
                }
            }
        }

        $header[$headerKey] = implode(',', $val);
        if ($tudu->type == 'notice' || empty($val)) {
            unset($header[$headerKey]);
        }

        $postParams = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'postid'     => Dao_Td_Tudu_Post::getPostId($tudu->tuduId),
            'header'     => $header,
            'content'    => isset($attrs['content']) ? $attrs['content'] : '',
            'poster'     => $tudu->poster,
            'postinfo'   => $tudu->posterInfo,
            'email'      => $attrs['email'],
            'uniqueid'   => $tudu->uniqueId,
            'attachment' => !empty($attrs['attachment']) ? (array) $attrs['attachment'] : array(),
            'file'       => !empty($attrs['file']) ? (array) $attrs['file'] : array()
        );

        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        $postId = $daoPost->createPost($postParams);

        $daoPost->sendPost($tudu->tuduId, $postId);

        $extensions = $tudu->getExtensions();
        foreach ($extensions as $name => $item) {
            $this->getExtension($item->getHandler())->onReview($tudu, $item);
        }

        return $tudu->tuduId;
    }

    /**
     * 执行同意流程
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    protected function _agree(Tudu_Model_Tudu_Entity_Tudu $tudu)
    {
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        if (!isset($steps[$tudu->stepId])) {
            return false;
        }

        $step = $steps[$tudu->stepId];

        $users = $daoStep->getUsers($tudu->tuduId, $tudu->stepId);

        // 当前审批人
        $nextUser  = false;
        $nextStep  = true;
        $nextIndex = null;
        $currIndex = null;
        $reviewer  = array();
        $tudu->sameReview = false;//var_dump($users);
        foreach ($users as $user) {
            if ($user['uniqueid'] == $tudu->uniqueId) {
                $currentUser = $user;
                $currIndex   = $user['processindex'];

                if (empty($reviewer)) {
                    $nextUser    = true;
                }
                continue ;
            }

            // 还有用户在审核中，跳过流程
            if ($user['status'] == 1) {
                list ($userName, $trueName) = explode(' ', $user['userinfo']);
                $reviewer[] = array('email' => $userName, 'truename' => $trueName);
                $nextStep = false;
                $nextUser = false;
                $tudu->sameReview = true;
            }

            // 转到本步骤后续执行人
            if ($nextUser) {
                if (null === $nextIndex) {
                    $nextIndex = $user['processindex'];
                }

                if (null !== $nextIndex && $user['processindex'] != $nextIndex) {
                    break ;
                }

                if (null !== $currIndex && $currIndex == $user['processindex']) {
                    break ;
                }

                list ($userName, $trueName) = explode(' ', $user['userinfo']);

                $reviewer[] = array('email' => $userName, 'truename' => $trueName);

                $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $user['uniqueid'], array('status' => 1));

                $nextStep = false;
            }
        }

        if (count($reviewer)) {
            $tudu->reviewer = array($reviewer);
        }

        // 下一步骤
        if ($nextStep) {
            if ($step['nextstepid'] == '^end') {
                $tudu->stepId = $step['nextstepid'];
                return false;
            }
            if (!isset($steps[$step['nextstepid']])) {
                return false;
            }

            $tudu->stepId = $step['nextstepid'];
            $tudu->currentStepStatus = true; //当前步骤是否已完结
            $updateStatus = false;
            $nextStep = $steps[$step['nextstepid']];
            $nextIndex = null;

            $nextStepUsers = $daoStep->getUsers($tudu->tuduId, $tudu->stepId);

            // 下一步仍然是审批步骤
            if ($nextStep['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                foreach ($nextStepUsers as $item) {
                    if (null === $nextIndex) {
                        $nextIndex = $item['processindex'];
                    }
                    if (null !== $nextIndex && $item['processindex'] != $nextIndex) {
                        break;
                    }
                    list ($userName, $trueName) = explode(' ', $item['userinfo']);
                    $reviewer[] = array('email' => $userName, 'truename' => $trueName);

                    if ($tudu->flowId && $item['status'] > 1) {
                        $updateStatus = true;
                        $daoStep->updateUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
                    }
                }

                $tudu->reviewer = array($reviewer);
            // 下一步是执行
            } else {
                $users = array();
                foreach ($nextStepUsers as $item) {
                    list($email, $trueName) = explode(' ', $item['userinfo']);
                    $users[$email] = array(
                        'email'    => $email,
                        'truename' => $trueName,
                        'percent'  => (int) $item['percent']
                    );

                    if ($tudu->flowId && $item['status'] > 1) {
                        $updateStatus = true;
                        $manager = Tudu_Tudu_Manager::getInstance();
                        $manager->updateProgress($tudu->tuduId, $item['uniqueid'], 0);
                        if ($tudu->parentId) {
                            $manager->calParentsProgress($tudu->parentId);
                        }
                        $daoStep->updateUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
                    }
                }

                $tudu->reviewer = null;
                $tudu->to       = Tudu_Tudu_Storage::formatRecipients($users);
            }

            if ($tudu->flowId && $updateStatus) {
                $daoStep->updateStep($tudu->tuduId, $nextStep['stepid'], array('status' => 0));
            }

            $tudu->stepId = $step['nextstepid'];
        }
    }

    /**
     * 执行部同意流程
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    protected function _disAgree(Tudu_Model_Tudu_Entity_Tudu $tudu)
    {
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        if ($tudu->flowId) {
            //return $this->flowReviewReject($tudu);
            return ;
        }

        $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 3));

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        if (!isset($steps[$tudu->stepId])) {
            return false;
        }

        $step     = $steps[$tudu->stepId];
        $prevId   = $step['prevstepid'];
        $toStepId = $prevId;
        do {
            if (!isset($steps[$prevId])) {
                $prevId = '^head';
                break ;
            }

            $prev = $steps[$prevId];
            if ($prev['type'] == 0) {
                break ;
            }

            $prevId   = $prev['prevstepid'];
            $toStepId = $prev['stepid'];
            if ($prevId == '^head') {
                break ;
            }

        } while ($prev['type'] == 0);

        $tudu->stepId = $toStepId;

        if ($toStepId == '^head' || $toStepId == '^trunk') {
            $tudu->to = Tudu_Tudu_Storage::formatRecipients($tudu->from);
        } else {
            $users = $daoStep->getUsers($tudu->tuduId, $toStepId);

            $to = array();
            foreach ($users as $item) {
                $to[] = $item['userinfo'];
            }

            $tudu->to = Tudu_Tudu_Storage::formatRecipients(implode("\n", $to));
        }
    }

    /**
     * 更新图度步骤
     * XXX
     *
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    protected function _updateTuduSteps(Tudu_Model_Tudu_Entity_Tudu $tudu)
    {
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        if ($tudu->flowId) {
            return true;
        }

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        $currentStep = $tudu->stepId && false === strpos($tudu->stepId, '^') ? $steps[$tudu->stepId] : array_pop($steps);

        // 当前为审批步骤
        $stepNum  = count($steps);
        $newSteps = array();
        if ($currentStep['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {

            $nextStepId = $currentStep['stepid'];
            $execStepId = Dao_Td_Tudu_Step::getStepId();

            if ($tudu->reviewer) {
                $reviewers  = $tudu->reviewer;
                $users = array();
                $processIndex = 1;

                foreach ($reviewers as $item) {
                    foreach ($item as $reviewer) {
                        $users[] = array(
                            'email'        => $reviewer['email'],
                            'truename'     => $reviewer['truename'],
                            'processindex' => $processIndex,
                            'stepstatus'   => $processIndex == 1 ? 1 : 0
                        );
                    }

                    $processIndex ++;
                }

                // 更新审批步骤审批人
                $this->updateStepUsers($tudu, $currentStep['stepid'], $users);

                $prevStepId   = $currentStep['stepid'];

                $tudu->stepId = $currentStep['stepid'];

            } else {
                // 审批步骤作废
                //$this->cancelStep($tudu, $currentStep['stepid']);

                $this->updateStepUsers($tudu, $currentStep['stepid'], array());

                $prevStepId   = $currentStep['prevstepid'];

                $tudu->stepId = $execStepId;
            }

            $nextStepId = $execStepId;

            $newSteps[] = array(
                'orgid'  => $tudu->orgId,
                'tuduid'     => $tudu->tuduId,
                'stepid'     => $execStepId,
                'uniqueid'   => $tudu->uniqueId,
                'prevstepid' => $prevStepId,
                'nextstepid' => self::NODE_END,
                'type'       => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                'ordernum'   => $currentStep['ordernum'] + 1,
                'createtime' => time(),
                'users'      => $tudu->to
            );

        // 其他
        } else {

            // 步骤作废
            $prevStepId = $currentStep['stepid'];

            $nextStepId = null;
            $prevId     = $prevStepId;
            $orderNum   = $currentStep['ordernum'];
            if ($tudu->reviewer) {
                // 前一步骤作废
                $this->updateStep($tudu->tuduId, $prevId, array('status' => 4));

                $reviewers  = $tudu->reviewer;
                $users = array();
                $processIndex = 1;


                foreach ($reviewers as $item) {
                    foreach ($item as $reviewer) {
                        $users[] = array(
                            'email'        => $reviewer['email'],
                            'truename'     => $reviewer['truename'],
                            'processindex' => $processIndex
                        );
                    }

                    $processIndex ++;
                }
                // 前一步骤作废
                $this->updateStep($tudu->tuduId, $prevId, array('status' => 4));

                $stepId = Dao_Td_Tudu_Step::getStepId();
                $nextStepId = Dao_Td_Tudu_Step::getStepId();

                $newSteps[$stepId] = array(
                    'orgid'  => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'stepid'     => $stepId,
                    'uniqueid'   => $tudu->uniqueId,
                    'prevstepid' => $prevId,
                    'nextstepid' => $nextStepId,
                    'type'       => Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'ordernum'   => ++$orderNum,
                    'createtime' => time(),
                    'users'      => array(0 => array('email' => $tudu->email, 'truename' => $tudu->poster))
                );

                $prevId = $stepId;
                $stepId = $nextStepId;

                $newSteps[$stepId] = array(
                    'orgid'  => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'stepid'     => $stepId,
                    'uniqueid'   => $tudu->uniqueId,
                    'prevstepid' => $prevId,
                    'nextstepid' => '^end',
                    'type'       => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                    'ordernum'   => ++$orderNum,
                    'createtime' => time(),
                    'users'      => $users
                );

                $prevId = $stepId;

                if (!$nextStepId) {
                    $nextStepId = $stepId;
                }
            }

            if ($tudu->isChange('to') || count($newSteps) || $tudu->isChange('acceptmode')) {
                $stepId = Dao_Td_Tudu_Step::getStepId();

                if (isset($newSteps[$prevId])) {
                    $newSteps[$prevId]['nextstepid'] = $stepId;
                }

                $newSteps[$stepId] = array(
                    'orgid'  => $tudu->orgId,
                    'tuduid' => $tudu->tuduId,
                    'stepid' => $stepId,
                    'uniqueid' => $tudu->uniqueId,
                    'prevstepid' => $prevId,
                    'nextstepid' => '^end',
                    'type' => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'ordernum' => ++$orderNum,
                    'createtime' => time(),
                    'users'    => $tudu->to
                );

                if (!$nextStepId) {
                    $nextStepId = $stepId;
                }
            }

            if ($nextStepId) {
                $tudu->stepId = $nextStepId;
            }
        }

        // 移除后随未开始执行的步骤
        foreach ($steps as $step) {
            if ($step['ordernum'] > $currentStep['ordernum']) {
                $this->deleteStep($tudu->tuduId, $step['stepid']);
                $stepNum--;
            }
        }

        foreach ($newSteps as $step) {
            if ($this->createStep($step)) {
                $recipients = $this->prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);

                $stepNum++;
            }
        }

        $this->updateStep($tudu->tuduId, $prevStepId, array('nextstepid' => $nextStepId));

        $tudu->stepNum = $stepNum;
    }

    public function getSteps($tuduId)
    {
        if (null == $this->_steps) {
            $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

            $steps = $daoStep->getSteps(array('tuduid' => $tuduId))->toArray('stepid');
        }

        return $this->_steps;
    }

    /**
     * 更新步骤用户
     *
     * @param $tuduId
     * @param $stepId
     * @param $users
     */
    public function updateStepUsers(&$tudu, $stepId, array $users)
    {
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $currentUsers = $daoStep->getUsers($tudu->tuduId, $stepId);

        $processIndex = null;
        $removes = array();
        foreach ($currentUsers as $item) {
            list($email, $trueName) = explode(' ', $item['userinfo'], 2);
            if ($item['status'] == 2 && !array_key_exists($email, $users)) {
                $processIndex = (int) $item['processindex'];
            } else {
                if (null === $processIndex) {
                    $processIndex = (int) $item['processindex'] - 1;
                }

                $removes[] = $item['uniqueid'];
            }

            // 正在审批的移除待审批标签
            if ($item['status'] == 1) {
                $daoTudu->deleteLabel($tudu->tuduId, $item['uniqueid'], '^e');

                if (!array_key_exists($email, $tudu->to)
                    && !array_key_exists($email, $tudu->cc)
                    && $email != $tudu->sender)
                {
                    $daoTudu->deleteLabel($tudu->tuduId, $item['uniqueid'], '^i');
                }
            }
        }

        foreach ($users as $k => $item) {
            $users[$k]['processindex'] = $users[$k]['processindex'] + $processIndex;
        }

        if ($removes) {
            $this->removeStepUsers($tudu->tuduId, $stepId, $removes);
        }

        // 添加审批人
        $recipients = $this->prepareRecipients($tudu->orgId, $stepId, $users);
        $this->addStepUsers($tudu->tuduId, $stepId, $recipients, $processIndex);
    }

    /**
     * 添加步骤用户
     *
     * @param $tuduId
     * @param $stepId
     * @param $users
     */
    public function addStepUsers($tuduId, $stepId, array $users, $startIndex = null)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = $this->getDao('Dao_Td_Tudu_Step');

        $isOrder = is_int($startIndex);

        $count = 0;
        foreach ($users as $item) {
            $processIndex = isset($item['processindex'])
                          ? $item['processindex']
                          : ($isOrder ? ++$startIndex : 0);

            $status = isset($item['stepstatus'])
                    ? $item['stepstatus']
                    : ($isOrder && $count == 0 ? 1 : 0);

            $params = array(
                'tuduid'       => $tuduId,
                'stepid'       => $stepId,
                'uniqueid'     => $item['uniqueid'],
                'userinfo'     => $item['accepterinfo'],
                'status'       => $status,
                'processindex' => $processIndex
            );

            if (array_key_exists('percent', $item)) {
                $params['percent'] = (int) $item['percent'];
            }

            $daoStep->addUser($params);

            $count ++;
        }

        return $count > 0;
    }

    /**
     *
     * @param $orgId
     * @param $uniqueId
     * @param $users
     */
    public function prepareRecipients($orgId, $uniqueId, array $users)
    {

        $addressBook = Tudu_AddressBook::getInstance();

        $recipients = array();

        foreach ($users as $key => $item) {
            $user = $addressBook->searchUser($orgId, $item['email']);
            if (null === $user) {
                $user = $addressBook->searchContact($uniqueId, $item['email'], $item['truename']);

                if (null === $user) {
                    $user = $addressBook->prepareContact($item['email'], $item['truename']);
                }
            }

            $user['accepterinfo'] = $user['email'] . ' ' . $user['truename'];
            $user['percent']      = isset($item['percent']) ? (int) $item['percent'] : 0;

            if (isset($item['processindex'])) {
                $user['processindex'] = $item['processindex'];
            }

            if (isset($item['stepstatus'])) {
                $user['stepstatus'] = $item['stepstatus'];
            }

            $recipients[$user['uniqueid']] = $user;
        }

        return $recipients;
    }
}