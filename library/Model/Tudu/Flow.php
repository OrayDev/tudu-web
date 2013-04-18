<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Tudu.php 2196 2012-10-10 01:48:56Z cutecube $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 *@see Tudu_User
 */
require_once 'Tudu/User.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Flow
{

    const NODE_HEAD = '^head';
    const NODE_END  = '^end';
    const NODE_BREAK = '^break';

    const STEP_TYPE_EXECUTE = 0;
    const STEP_TYPE_EXAMINE = 1;

    /**
     * 流动到指定（下一步）步骤
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function flowTo(Model_Tudu_Tudu &$tudu, $stepId = null)
    {

        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
        /* @var $daoStep Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        // 默认流动到下一步
        if (null === $stepId) {
            $currentStep = $steps[$tudu->stepId];

            // 是否需要流到下一步
            $users = $daoStep->getUsers($tudu->tuduId, $tudu->stepId);
            $currentUser = $users[$tudu->uniqueId];

            $nextSection = false;
            $nextProcessIndex = null;
            unset($users[$tudu->uniqueId]);

            $nextUsers = array();
            foreach ($users as $u) {
                // 不需要转到下一步
                if ($u['processindex'] == $currentUser['processindex'] && $u['status'] < 2) {
                    return ;
                }

                // 本步骤有下一节点
                if (null === $nextProcessIndex && $u['processindex'] > $currentUser['processindex']) {
                    $nextSection = true;
                    $nextProcessIndex = $u['processindex'];
                }

                if (null !== $nextProcessIndex && $u['processindex'] === $nextProcessIndex) {
                    $arr = explode(' ', $u['userinfo']);

                    $nextUsers[$arr[0]] = array('email' => $arr[0], 'truename' => $arr[1]);
                    $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $u['uniqueid'], array('status' => 1));
                }
            }

            if (!$nextSection) {
                $stepId   = $currentStep['nextstepid'];

                if (isset($steps[$stepId])) {
                    $nextStep = $steps[$stepId];

                    $users = $daoStep->getUsers($tudu->tuduId, $stepId);
                    $procIndex = null;
                    $nextUsers = array();
                    foreach ($users as $u) {
                        if ($procIndex === null) {
                            $procIndex = $u['processindex'];
                        }

                        if (null !== $procIndex && $u['processindex'] !== $procIndex) {
                            break ;
                        }

                        $arr = explode(' ', $u['userinfo']);

                        $nextUsers[$arr[0]] = array('email' => $arr[0], 'truename' => $arr[1]);
                        $daoStep->updateUser($tudu->tuduId, $stepId, $u['uniqueid'], array('status' => 1));
                    }

                    if ($nextStep['type'] == self::STEP_TYPE_EXAMINE) {
                        $tudu->reviewer = array($nextUsers);
                    } else {
                        $tudu->to       = $nextUsers;
                    }
                }

            } else {
                $stepId = $tudu->stepId;

                if ($currentStep['type'] == self::STEP_TYPE_EXAMINE) {
                    $tudu->reviewer = array($nextUsers);
                } else {
                    $tudu->to       = $nextUsers;
                }
            }
        }

        $updateStatus = false;
        if (isset($steps[$stepId]) && empty($nextSection)) {
            $nextStep      = $steps[$stepId];
            $nextStepUsers = $daoStep->getUsers($tudu->tuduId, $nextStep['stepid']);

            $nextIndex = null;
            if ($nextStep['type'] == self::STEP_TYPE_EXAMINE) {

                $reviewer = array();
                foreach ($nextStepUsers as $item) {
                    if (null === $nextIndex) {
                        $nextIndex = $item['processindex'];
                    }
                    if (null !== $nextIndex && $item['processindex'] != $nextIndex) {
                        break;
                    }
                    list ($userName, $trueName) = explode(' ', $item['userinfo']);
                    $reviewer[] = array('email' => $userName, 'truename' => $trueName);

                    // 更新上一步骤用户状态
                    if ($item['status'] > 1) {
                        $updateStatus = true;
                        $daoStep->updateUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
                    }
                }

                $tudu->reviewer = array($reviewer);

            } elseif ($nextStep['type'] == self::STEP_TYPE_EXECUTE) {

                $users = array();
                // 准备执行人
                foreach ($nextStepUsers as $item) {
                    list($email, $trueName) = explode(' ', $item['userinfo']);
                    $users[$email] = array(
                        'email'    => $email,
                        'truename' => $trueName,
                        'percent'  => (int) $item['percent']
                    );

                    // 更新上一步骤用户状态
                    if ($item['status'] > 1) {
                        $updateStatus = true;
                        $daoStep->updateUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
                        $daoTudu->updateProgress($tudu->tuduId, $item['uniqueid'], 0);
                        if ($tudu->parentId) {
                            $daoTudu->calParentsProgress($tudu->parentId);
                        }
                    }
                }

                $tudu->reviewer = null;
                $tudu->to       = Tudu_Tudu_Storage::formatRecipients($users);
                // 更新图度接收人信息
                //$manager->updateTudu($tudu['tuduid'], array('to' => Tudu_Tudu_Storage::formatReceiver($users)));
            }

            if ($updateStatus) {
                $daoStep->updateStep($tudu->tuduId, $nextStep['stepid'], array('status' => 0));
            }

            $stepId = $nextStep['stepid'];
            $sendParam['flowid'] = $tudu->flowId;
            $sendParam['nstepid'] = $stepId;

            $tudu->stepId = $stepId;

        } elseif (!$stepId) {
            $stepId = '^break';
            $tudu->stepId = '^break';
        }

        if ($stepId == '^break') {
            $tudu->to = array('email' => $tudu->from['username'], 'truename' => $tudu->from['truename']);
            // 更新图度接收人信息
            $daoTudu->updateTudu($tudu->tuduId, array('to' => $tudu->from['username'] . ' ' . $tudu->from['truename']));
        }

        // 准备发送
        $updateParams = array();
        if ($stepId != '^end') {
            // 移除原执行人
            if (!$tudu->reviewer) {
                $accepters = $daoTudu->getAccepters($tudu->tuduId);
                $to        = $tudu->to;
                foreach ($accepters as $item) {
                    list($email, ) = explode(' ', $item['accepterinfo'], 2);

                    // 移除执行人角色，我执行标签
                    if (!empty($to) && !array_key_exists($email, $to)
                    && $daoTudu->getChildrenCount($tudu->tuduId, $item['uniqueid']) <= 0)
                    {
                        $daoTudu->removeAccepter($tudu->tuduId, $item['uniqueid']);
                        $daoTudu->deleteLabel($tudu->tuduId, $item['uniqueid'], '^a');
                    }
                }
            }

            require_once 'Model/Tudu/Compose/Send.php';
            $modelSend = new Model_Tudu_Compose_Send();

            $modelSend->send($tudu);

            // 执行人自动接受图度
            $lastUpdateTime = null;
            if ($stepId != '^break' && $stepId != '^head' && isset($steps[$stepId]) && $steps[$stepId]['type'] != Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                if ($steps[$stepId]['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                    $users = $daoStep->getUsers($tudu->tuduId, $stepId);
                    foreach ($nextStepUsers as $item) {
                        $daoTudu->updateTuduUser($tudu->tuduId, $item['uniqueid'], array(
                            'accepttime' => time(),
                            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
                        ));
                        $lastUpdateTime = time();
                    }
                    $tudu->acceptMode = 0;
                    // 认领模式
                } else if ($steps[$stepId]['type'] == Dao_Td_Tudu_Step::TYPE_CLAIM) {
                    $tudu->acceptMode = 0;
                    $tudu->acceptTime = 0;
                }
            }
        }

        $tudu->stepId = $stepId;
        $attrs = $tudu->getStorageParams();
        unset($attrs['from'], $attrs['reviewer']);

        if (!empty($lastUpdateTime)) {
            $attrs['lastupdatetime'] = $lastUpdateTime;
        }

        $daoTudu->updateTudu($tudu->tuduId, $attrs);
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function agree(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 2));

        // 更新图度后续步骤
        $this->updateTuduSteps($tudu, false);

        $this->flowTo($tudu);
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function disagree(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $daoStep->updateUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 3));

        $step = $daoStep->getStep(array('tuduid' => $tudu->tuduId, 'stepid' => $tudu->stepId));

        if ($tudu->flowId) {
            $this->flowTo($tudu, $step->prevStepId);
        } else {
            // 审批拒绝均打回发起人
            $tudu->stepId = '^head';
            $tudu->to = array('username' => $tudu->from[3], 'truename' => $tudu->from[0]);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function updateTuduSteps(Model_Tudu_Tudu &$tudu, $cancelCurrent = true)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        // 草稿
        if ($tudu->isDraft|| $tudu->stepId ==  self::NODE_HEAD || $tudu->stepId == self::NODE_BREAK) {
            $daoStep->deleteSteps($tudu->tuduId);
            if ($tudu->flowId) {
                return $this->createFlowSteps($tudu);
            } else {
                return $this->createTuduSteps($tudu);
            }
        }

        if ($tudu->flowId) {
            return ;
        }

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        $currentStep = $tudu->stepId && false === strpos($tudu->stepId, '^') ? $steps[$tudu->stepId] : array_pop($steps);

        // 不需要更新步骤
        if (!$tudu->to && !$tudu->reviewer) {
            return ;
        }

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
                $this->_updateStepUsers($tudu, $currentStep['stepid'], $users);

                $prevStepId   = $currentStep['stepid'];

                $tudu->stepId = $currentStep['stepid'];

            } else {

                $this->_updateStepUsers($tudu, $currentStep['stepid'], array());

                $prevStepId   = $currentStep['prevstepid'];

                $tudu->stepId = $currentStep['nextstepid'];
            }

            if ($tudu->type != 'notice') {
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

            } else {
                $nextStepId = self::NODE_END;
            }

            // 其他
        } else {

            // 步骤作废
            if ($cancelCurrent) {
                $daoStep->updateStep($tudu->tuduId, $currentStep['stepid'], array('status' => 4));
                $prevStepId = $currentStep['prevstepid'];
            } else {
                $prevStepId = $currentStep['stepid'];
            }

            $nextStepId = null;
            $prevId     = $prevStepId;
            $orderNum   = $currentStep['ordernum'];
            if ($tudu->reviewer) {

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
                if ($cancelCurrent) {
                    $daoStep->updateStep($tudu->tuduId, $prevId, array('status' => 4));
                }

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

            if ($tudu->to || count($newSteps) || $tudu->acceptMode) {
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

            if (!$nextStepId) {
                $nextStepId = $currentStep['nextstepid'];
            }

            $tudu->stepId = $nextStepId;
        }

        // 移除后随未开始执行的步骤
        foreach ($steps as $step) {
            if ($step['ordernum'] > $currentStep['ordernum']) {
                $daoStep->deleteStep($tudu->tuduId, $step['stepid']);
                $stepNum--;
            }
        }

        foreach ($newSteps as $step) {
            if ($daoStep->createStep($step)) {

                $recipients = $this->_prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->_addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);

                $stepNum++;
            }
        }

        $daoStep->updateStep($tudu->tuduId, $prevStepId, array('nextstepid' => $nextStepId));

        $tudu->stepNum = $stepNum;
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @param unknown_type $stepId
     * @param unknown_type $users
     */
    private function _updateStepUsers(Model_Tudu_Tudu &$tudu, $stepId, $users)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

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

                if (($tudu->to && !array_key_exists($email, $tudu->to))
                && ($tudu->cc && !array_key_exists($email, $tudu->cc))
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
            $daoStep->deleteUsers($tudu->tuduId, $stepId, $removes);
        }

        // 添加审批人
        $recipients = $this->_prepareRecipients($tudu->orgId, $stepId, $users);
        $this->_addStepUsers($tudu->tuduId, $stepId, $recipients, $processIndex);
    }

    /**
     *
     * @param string $tuduId
     * @param string $stepId
     * @param array  $recipients
     * @param int    $startIndex
     */
    private function _addStepUsers($tuduId, $stepId, array $recipients, $startIndex = null)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $isOrder = is_int($startIndex);

        $count = 0;
        foreach ($recipients as $item) {
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
    private function _prepareRecipients($orgId, $uniqueId, array $users)
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

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    private function createFlowSteps(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        if ($tudu->flowId && $tudu->steps) {

            $steps = $tudu->steps;

        } else {

            $steps = array();

            $currentStepId = null;
            $orderNum      = 1;
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

                $stepId  = Dao_Td_Tudu_Step::getStepId();
                $steps[$stepId] = array(
                    'orgid' => $tudu->orgId,
                    'tuduid' => $tudu->tuduId,
                    'uniqueid' => $tudu->uniqueId,
                    'stepid'   => $stepId,
                    'type'     => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                    'prevstepid' => self::NODE_HEAD,
                    'nextstepid' => self::NODE_END,
                    'users'      => $users,
                    'ordernum'   => $orderNum ++,
                    'createtime' => time()
                );

                $currentStepId = $stepId;
            }

            if ($tudu->to) {
                $stepId  = Dao_Td_Tudu_Step::getStepId();
                $prevId  = self::NODE_HEAD;

                if ($currentStepId) {
                    $steps[$currentStepId]['nextstepid'] = $stepId;
                    $prevId = $currentStepId;
                } else {
                    $currentStepId = $stepId;
                }

                $steps[$stepId] = array(
                    'orgid' => $tudu->orgId,
                    'tuduid' => $tudu->tuduId,
                    'uniqueid' => $tudu->uniqueId,
                    'stepid'   => $stepId,
                    'type'     => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'prevstepid' => $prevId,
                    'nextstepid' => self::NODE_END,
                    'users'      => $tudu->to,
                    'ordernum'   => $orderNum ++,
                    'createtime' => time()
                );
            }
        }

        foreach ($steps as $step) {
            if ($daoStep->createStep($step)) {
                $recipients = $this->_prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->_addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);
            }
        }

        $tudu->stepNum = count($steps);
    }

    /**
     * 创建图度步骤
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function createTuduSteps(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $steps = array();

        $currentStepId = null;
        $orderNum      = 1;
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

            $stepId  = Dao_Td_Tudu_Step::getStepId();
            $steps[$stepId] = array(
                'orgid' => $tudu->orgId,
                'tuduid' => $tudu->tuduId,
                'uniqueid' => $tudu->uniqueId,
                'stepid'   => $stepId,
                'type'     => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                'prevstepid' => self::NODE_HEAD,
                'nextstepid' => self::NODE_END,
                'users'      => $users,
                'ordernum'   => $orderNum ++,
                'createtime' => time()
            );

            $currentStepId = $stepId;
        }

        if ($tudu->to) {
            $stepId  = Dao_Td_Tudu_Step::getStepId();
            $prevId  = self::NODE_HEAD;

            if ($currentStepId) {
                $steps[$currentStepId]['nextstepid'] = $stepId;
                $prevId = $currentStepId;
            } else {
                $currentStepId = $stepId;
            }

            $steps[$stepId] = array(
                'orgid' => $tudu->orgId,
                'tuduid' => $tudu->tuduId,
                'uniqueid' => $tudu->uniqueId,
                'stepid'   => $stepId,
                'type'     => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                'prevstepid' => $prevId,
                'nextstepid' => self::NODE_END,
                'users'      => $tudu->to,
                'ordernum'   => $orderNum ++,
                'createtime' => time()
            );
        }

        foreach ($steps as $step) {
            if ($this->createStep($step)) {
                $recipients = $this->_prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->_addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);
            }
        }

        $tudu->stepId  = $currentStepId;
        $tudu->stepNum = count($steps);
    }
}