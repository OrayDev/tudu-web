<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Exception.php 1894 2012-05-31 08:02:57Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Handler_Abstract
 */
require_once 'Model/Tudu/Extension/Handler/Abstract.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Handler_Flow extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @var string
     */
    const NODE_START = '^start';
    const NODE_BREAK = '^break';
    const NODE_END   = '^end';

    /**
     *
     * @var int
     */
    const STEP_TYPE_EXECUTE = 0;
    const STEP_TYPE_EXAMINE = 1;
    const STEP_TYPE_CLAIM   = 2;

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        if ($tudu->operation == 'review') {
            $this->prepareTuduReview($tudu);
        } elseif ($tudu->operation == 'forward') {
            $this->prepareTuduFlow($tudu);
        } else {
            $this->updateTuduFlow($tudu);
        }
    }

    /**
     *
     * @param Model_Tudu_TUdu $tudu
     */
    public function action(Model_Tudu_TUdu &$tudu)
    {
        $this->saveTuduFlow($tudu);
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function flowTo(Model_Tudu_Tudu &$tudu, $stepId = null)
    {
        /* @var $daoFlow Dao_Td_Tudu_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareTuduFlow(Model_Tudu_Tudu &$tudu)
    {
        /* @var $flow Model_Tudu_Extension_Flow */
        $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');
        $prev = '^head';

        if (null === $flow) {
            /**
             * @see Model_Tudu_Extension_Flow
             */
            require_once 'Model/Tudu/Extension/Flow.php';
            $flow = new Model_Tudu_Extension_Flow();
        }

        if ($flow->isPrepared) {
            return ;
        }

        $isCreate = true;
        if ($tudu->tuduId) {
            /* @var $daoFlow Dao_Td_Tudu_Flow */
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $savedFlow = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

            $isCreate = false;
            if (null != $savedFlow) {
                $flow->fromFlow = true;
            }

            if (!$tudu->isDraft) {
                $flowInfo = $savedFlow->toArray();
            } else {
                $isCreate = true;
            }
        }

        if (!isset($flowInfo)) {
            $flowInfo = array(
                'orgid'  => $tudu->orgId,
                'tuduid' => $tudu->tuduId
            );
        }

        $flow->setAttributes($flowInfo);

        // 创建固定的工作流模版
        if ($tudu->flowId && $isCreate) {

            if ($isCreate) {
                /* @var $daoFlowTpl Dao_Td_Flow_Flow */
                $daoFlowTpl = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);

                $flowTpl = $daoFlowTpl->getFlow(array('flowid' => $tudu->flowId), array('isvalid' => 1));

                if (null == $flowTpl) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Tudu flow not exists', Model_Tudu_Exception::FLOW_NOT_EXISTS);
                }

                $steps = $flowTpl->steps;
                $firstStep = reset($steps);
                foreach ($steps as $item) {
                    $stepId = $item['stepid'];

                    $flow->addStep(array(
                        'stepid' => $stepId,
                        'prev'   => $item['prev'],
                        'next'   => $item['next'],
                        'type'   => $item['type'],
                        'description' => $item['description'],
                        'subject' => $item['subject']
                    ));

                    if (is_array($item['sections'])) {
                        foreach ($item['sections'] as $section) {
                            $flow->addStepSection($stepId, $section, $tudu);
                        }
                    } else {
                        $flow->addStepSection($stepId, $item['sections'], $tudu);
                    }
                }

                // 更新图度执行人
                $users = reset($firstStep['sections']);
                $flow->flowTo($firstStep['stepid']);

                //XXX
                foreach ($users as $k => $u) {
                    $users[$k]['email'] = $users[$k]['username'];
                }

                if ($firstStep['type'] == 1) {
                    $tudu->reviewer = $users;
                } else  {
                    $tudu->to = $users;

                    if ($firstStep == 2) {
                        $tudu->acceptMode = 1;
                    }
                }
            }

            // 取消拒绝的步骤
            $currentStepId = null;
            foreach ($flow->steps as $key => $item) {
                $stepId = $item['stepid'];
                foreach ($item['section'] as $idx => $section) {
                    foreach ($section as $k => $u) {
                        if (isset($u['status']) && $u['status'] > 2) {
                            if ($currentStepId == null) {
                                $currentStepId = $stepId;
                            }

                            $flow->steps[$key]['section'][$idx][$k]['status'] = 1;
                        }
                    }
                }
            }

            if (null !== $currentStepId && $tudu->operation !== 'forward') {
                $flow->flowTo($currentStepId);
            }
        }

        if (!$tudu->flowId || $tudu->operation == 'forward') {
            // 去掉前面的步骤
            $isExceed = false;

            foreach ($flow->steps as $sid => $step) {
                if ($sid == $flow->currentStepId) {
                    foreach ($step['section'] as $i => $sec) {
                        if ($i > $step['currentSection']) {
                            $flow->removeStepSection($sid, $i);
                        }
                    }

                    $isExceed = true;
                    continue ;
                }

                if ($isExceed) {
                    $flow->deleteStep($sid);
                }
            }

            // 添加当前编辑提交的人
            $this->renewTuduFlow($tudu, $flow, $prev);
        }

        $tudu->setExtension($flow);
    }

    /**
     * 编辑图度时进行的流程
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function updateTuduFlow(Model_Tudu_Tudu &$tudu)
    {
        /* @var $flow Model_Tudu_Extension_Flow */
        $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');

        if (null === $flow) {
            /**
             * @see Model_Tudu_Extension_Flow
             */
            require_once 'Model/Tudu/Extension/Flow.php';
            $flow = new Model_Tudu_Extension_Flow();
        }

        if ($flow->isPrepared) {
            return ;
        }

        $isCreate = true;
        if ($tudu->tuduId) {
            /* @var $daoFlow Dao_Td_Tudu_Flow */
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $savedFlow = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

            $isCreate = false;
            if (null != $savedFlow) {
                $flow->fromFlow = true;
            }

            if (!$tudu->isDraft) {
                $flowInfo = $savedFlow->toArray();

                // 工作流，检查是否有拒绝或不同意项目，然后重发
                if ($tudu->flowId || ($tudu->fromTudu && $tudu->fromTudu->appId == 'attend')) {
                    $isBreak = false;
                    $flowTo  = $flowInfo['currentstepid'];
                    foreach ($flowInfo['steps'] as $sid => $st) {
                        foreach ($st['section'] as $idx => $sec) {
                            foreach ($sec as $i => $u) {
                                if (isset($u['status']) && $u['status'] > 2) {
                                    $isTo = (0 === strpos($flowTo, '^') || null === $flowTo);
                                    $flowInfo['steps'][$sid]['section'][$idx][$i]['status'] = $isTo ? 1 : 0;
                                    if ($isTo) {
                                        $flowTo  = $st['stepid'];
                                    }
                                }
                            }
                        }
                    }

                    $flow->setAttributes($flowInfo);
                    $flow->flowTo($flowTo);

                    //var_dump($flow->steps);exit;
                    if (0 !== strpos($flow->currentStepId, '^')) {
                        $step = $flow->getStep($flow->currentStepId);

                        $users = $flow->getCurrentUser();

                        $receiver = array();
                        foreach ($users as $u) {
                            $receiver[$u['username']] = array(
                                'uniqueid' => $u['uniqueid'],
                                'email'    => $u['username'],
                                'username' => $u['username'],
                                'truename' => $u['truename']
                            );
                        }

                        if ($step['type'] == 2) {
                            $tudu->acceptMode = 1;
                        }

                        if ($step['type'] == 1) {
                            $tudu->reviewer = $receiver;
                        } else {
                            $tudu->to = $receiver;
                        }
                    }

                    return ;
                }

            } else {
                $isCreate = true;
            }
        }

        if (!isset($flowInfo)) {
            $flowInfo = array(
                'orgid'  => $tudu->orgId,
                'tuduid' => $tudu->tuduId
            );
        }

        $flow->setAttributes($flowInfo);

        // 已经存在流程
        // 1. 定位当前步骤
        // 2. 对比当前执行和审批是否与输入一致
        // 3. 不一致则标记原有,一样则维持原状
        if ($isCreate) {
            if ($tudu->flowId) {
                $this->prepareFlowFromTemplate($tudu, $flow);
            } else {
                $this->renewTuduFlow($tudu, $flow);
            }

        } else {
        	
        	$steps         = $flow->steps;
        	$lastStep      = end($steps);
        	$currentStepId = $flow->currentStepId == '^end' ? $lastStep['stepid'] : $flow->currentStepId;

            $step = isset($flow->steps[$currentStepId]) ? $flow->steps[$currentStepId] : null;
            
            $isDisagree = false;

            foreach ($flow->steps as $item) {
                foreach ($item['section'] as $sec) {
                    foreach ($sec as $u) {
                        if ($item['type'] == 1 && isset($u['status']) && $u['status'] > 2) {
                            if (null === $step && 0 === strpos($flow->currentStepId, '^')) {
                                $step = $item;
                                $flow->currentStepId = $item['stepid'];
                            }
                            $isDisagree = $item['type'] == 1;
                            break 3;
                        // 拒绝的
                        } elseif ($item['type'] == 0 && ($u['status'] <= 1 || $u['status'] == 3)) {

                        	if (null === $step && 0 === strpos($flow->currentStepId, '^')) {
                        		$step = $item;
                        		$flow->currentStepId = $item['stepid'];
                        	}

                        	break 3;
                        }
                    }
                }
            }

            if (null === $step) {
                return ;
            }

            $isModified = false;
            $isReview   = $step['type'] == 1;

            $users  = $isReview ? (array) $tudu->reviewer : (array) $tudu->to;

            $isPass = true;
            $base   = 0;
            $mBase  = 0;
            $secIdx = 0;

            // 处理输入
            $section = $step['section'][$step['currentSection']];
            $currentSection = array();
            $completeUsers  = array();
            $currentUsers   = array();
            $currentStepId  = $flow->currentStepId;

            $prev      = $flow->currentStepId;
            $flowTo    = null;
            $flowToSec = null;

            // 保持当前已经完成或审批通过的帐号信息
            foreach ($section as $u) {
                $currentUsers[] = $u['username'];
                if ($u['status'] == 2) {
                    $currentSection[] = $u;
                    $completeUsers[]  = $u['username'];
                }
            }

            // 当前步骤为执行，把非完成的步骤设为取消
            if (!$isReview) {
                $section = $step['section'][$step['currentSection']];
                $to      = reset($users);

                $isCancel = count($section) != count($to) && $tudu->operation == 'send' && !$isDisagree;

                if (!$isCancel) {
                    $toArr = array();
                    foreach ($to as $item) {
                        $toArr[] = $item['username'];
                    }

                    foreach ($section as $u) {
                        if (!in_array($u['username'], $toArr)) {
                            $isCancel = !$isDisagree;
                            break ;
                        }
                    }
                }

                if ($isCancel) {
                    $flow->cancelStepSection($step['stepid'], $step['currentSection']);
                    $prev = $step['prev'];
                }
            }

            // 移除当前步骤后续
            //if (!$isDisagree) {
            /*for ($i = $step['currentSection'] + 1, $l = count($step['section']); $i < $l; $i++) {
                $flow->removeStepSection($step['stepid'], $i);
            }*/
            //}

            // 移除后续的所有步骤
            $steps = $flow->getSteps();
            $isExceed = false;
            foreach ($steps as $id => $item) {
                if ($id == $flow->currentStepId) {
                    $isExceedSection = false;
                    foreach ($item['section'] as $idx => $sec) {
                        if ($idx == $item['currentSection']) {
                            $isExceedSection = true;
                            
                            if ($isReview) {
                            	continue ;
                            }
                        }

                        if ($isExceedSection) {
                            $flow->removeStepSection($id, $idx);
                        }
                    }
                    $isExceed = true;
                    continue ;
                }

                if ($isExceed) {
                    $flow->deleteStep($id);
                    if ($id == $prev) {
                        $prev = $item['prev'];
                    }
                }
            }

            if (empty($flow->steps[$flow->currentStepId]['section'])) {
                $flow->deleteStep($step['stepid']);
                if ($isReview) {
                    $isReview = false;
                }
            }

            if (!isset($flow->steps[$prev])) {
                $prev = '^head';
            }

            // 添加审批人
            if ($tudu->reviewer) {
                $reviewer = $tudu->reviewer;

                if (!$isReview) {
                    $stepId = $flow->addStep(array(
                        'type' => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                        'prev' => $prev
                    ));

                    if (!$tudu->flowId) {
                        $flow->updateStep($prev, array('next' => $stepId));
                    }

                    $prev = $stepId;

                } else {
                    $stepId = $flow->currentStepId;
                }

                $flowTo = $stepId;

                foreach ($reviewer as $idx => $sec) {
                    $users = $isReview && $idx == 0 ? $currentSection : array();

                    if ($isReview && $idx == 0 && isset($flow->steps[$currentStepId]['currentSection'])) {
                        $secIdx  = $flow->steps[$currentStepId]['currentSection'];
                        $section = $flow->steps[$currentStepId]['section'][$secIdx];
                        foreach ($section as $i => $cu) {
                            if (!isset($sec[$cu['username']]) && $cu['status'] != 2) {
                                $flow->removeStepSectionUser($currentStepId, $secIdx, $cu['username']);
                            } elseif ($cu['status'] == 3) {
                                $flow->updateStepSectionUser($currentStepId, $secIdx, $cu['username'], array('status' => 1));
                                $completeUsers[] = $cu['username'];
                            }
                        }
                    }

                    foreach ($sec as $u) {
                        // 跳过已经完成审批的用户
                        if ($idx == 0 && $isReview) {
                            if (!in_array($u['username'], $completeUsers)) {
                                $u['status'] = 1;
                                $flow->addStepSectionUser($currentStepId, $flow->steps[$currentStepId]['currentSection'], $u);
                            }

                            continue ;
                        }

                        $users[] = $u;
                    }

                    if (!empty($users) && !($isReview && $idx == 0)) {
                        $flow->addStepSection($stepId, $users);
                    }
                }

                $tudu->reviewer = reset($reviewer);

            // 移除当前审批人
            } elseif ($isReview) {
                $secIdx  = $flow->steps[$currentStepId]['currentSection'];
                $section = $flow->steps[$currentStepId]['section'][$secIdx];
                foreach ($section as $i => $cu) {
                    if ($cu['status'] != 2) {
                        $flow->removeStepSectionUser($currentStepId, $secIdx, $cu['username']);
                    }
                }

                if (empty($flow->steps[$currentStepId]['section'])) {
                    $flow->deleteStep($step['stepid']);
                }
            }

            if ($prev && 0 !== strpos($prev, '^head')) {
                $step = $flow->getStep($prev);
                if (empty($step['section'])) {
                    $flow->deleteStep($prev);
                    $prev = '^head';
                }
            }

            // 添加当前步骤
            if ($tudu->to) {
                $to = $tudu->to;
                $executeStepId = $flow->addStep(array(
                    'type' => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'prev' => $prev,
                	'next' => '^end'
                ));

                if (null === $flowTo) {
                    $flowTo = $executeStepId;
                }

                foreach ($to as $idx => $sec) {
                    $users = !$isReview && $idx == 0 ? $currentSection : array();
                    foreach ($sec as $u) {
                        if ($idx == 0 && !$isReview && in_array($u['username'], $completeUsers)) {
                            continue ;
                        }
                        $users[] = $u;
                    }

                    $flow->addStepSection($executeStepId, $users);
                }

                $tudu->to = reset($to);
            }

            $flow->flowTo($flowTo);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareTuduReview(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoFlow Dao_Td_Tudu_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        $flowData = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

        if (null === $flowData) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to review tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        $flow = new Model_Tudu_Extension_Flow($flowData->toArray());
        $flow->fromFlow = true;

        if (!$flow->isCurrentUser($tudu->uniqueId)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to review tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        // 处理同意流程
        $flow->review($tudu->uniqueId, $tudu->agree);

        if ($tudu->agree) {
            $this->renewTuduFlow($tudu, $flow);

        } else {

            $step = $flow->getStep($flow->currentStepId);
            $next = $tudu->agree ? null : $step['prev'];

            if (null !== $next && 0 !== strpos($next, '^')) {
                $flow->flowTo($next);
                $nextStep = $flow->getStep($flow->currentStepId);

                $su   = $flow->getStepSection($flow->currentStepId);

                // XXX :-(
                $users = array();
                foreach ($su as $k => $u) {
                    $users[$u['username']] = array(
                        'uniqueid' => $u['uniqueid'],
                        'email'    => $u['username'],
                        'username' => $u['username'],
                        'truename' => $u['truename']
                    );
                }

                if ($nextStep && $nextStep['type'] == 1) {
                    $tudu->reviewer = $users;
                } else {
                    if ($nextStep['type'] == 2) {
                        $tudu->acceptMode = 1;
                    }

                    $tudu->to       = $users;
                }
            }
        }

        $tudu->setExtension($flow);
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @param Model_Tudu_Extension_Flow $flow
     */
    public function renewTuduFlow(Model_Tudu_Tudu &$tudu, Model_Tudu_Extension_Flow &$flow)
    {
        // 手动工作流，可修改当前步骤或不同意的后续步骤
        $isExceed = false;
        $prev     = $flow->currentStepId ? $flow->currentStepId : '^head';
        $next     = '^end';

        if ($tudu->operation != 'forward' && ($tudu->reviewer || $tudu->to)) {

            foreach ($flow->steps as $key => $item) {
                $stepId = $item['stepid'];
                foreach ($item['section'] as $idx => $section) {

                    if ($isExceed) {
                        $flow->removeStepSection($stepId, $idx);
                        if (empty($flow->steps[$stepId]['section'])) {
                            $flow->deleteStep($stepId);
                        }
                        continue ;
                    }

                    foreach ($section as $k => $u) {
                        if (isset($u['status']) && $u['status'] >= 2 && $item['type'] == 1) {
                            $isExceed = true;
                            $prev = $stepId;
                        }

                        if ($isExceed && $u['status'] < 2) {
                            $flow->removeStepSection($key, $idx);
                        }
                    }
                }
            }
        }

        if ($tudu->operation == 'forward') {
            if (isset($flow->steps[$flow->currentStepId])) {
                $next = $flow->steps[$flow->currentStepId]['next'];
            }
        }

        $flowTo = null;

        if ($tudu->reviewer) {
            $reviewer = $tudu->reviewer;

            $stepId = $flow->addStep(array(
                'type' => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                'prev' => $prev,
                'next' => $next
            ));

            $flowTo = $prev = $stepId;
            $prev   = $stepId;

            foreach ($reviewer as $item) {
                $flow->addStepSection($stepId, $item);
            }

            $tudu->reviewer = $flow->getStepSection($stepId, 0);
        }

        if ($tudu->to) {
            $to = $tudu->to;

            $stepId = $flow->addStep(array(
                'type' => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                'prev' => $prev,
                'next' => $next
            ));

            foreach ($to as $item) {
                $flow->addStepSection($stepId, $item);
            }

            $tudu->to = $flow->getStepSection($stepId, 0);

            if (0 !== strpos($prev, '^')) {
                $flow->updateStep($prev, array('next' => $stepId));
            }

            if (null === $flowTo) {
                $flowTo = $stepId;
            }
        }

        $isComplete = true;
        if ($tudu->operation == 'forward') {
            $flow->complete();
        }

        if ($flow->currentStepId && 0 !== strpos($flow->currentStepId, '^')) {
            $step = $flow->getStep($flow->currentStepId);
            $sec  = $flow->getStepSection($step['stepid'], $step['currentSection']);
            if ($sec) {
                foreach ($sec as $u) {
                    if (!empty($u['status']) && $u['status'] < 2) {
                        $isComplete = false;
                        break;
                    }
                }
            }
        }

        if ($isComplete) {
            $flow->flowTo($flowTo);
        }

        // 到头了
        if (0 === strpos($flow->currentStepId, '^') || !$flow->currentStepId) {
            return ;
        }

        $step  = $flow->getStep($flow->currentStepId);

        $su    = $flow->getStepSection($flow->currentStepId);
        $users = array();

        foreach ($su as $k => $u) {
            $users[$u['username']] = array(
                'uniqueid' => $u['uniqueid'],
                'username' => $u['username'],
                'truename' => $u['truename'],
                'email'    => $u['username']
            );
        }

        if ($step['type'] == 1) {
            if (!$tudu->reviewer) {
                $tudu->reviewer = $users;
            }
        } else {
            if ($step['type'] == 2) {
                $tudu->acceptMode = 1;
            }

            if (!$tudu->to) {
                $tudu->to = $users;
            }
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @param Model_Tudu_Extension_Flow $flow
     */
    public function appendTuduFlow(Model_Tudu_Tudu &$tudu, Model_Tudu_Extension_Flow &$flow)
    {
        // 手动工作流，可修改当前步骤或不同意的后续步骤
        $isExceed = false;
        $prev     = $flow->currentStepId ? $flow->currentStepId : '^head';
        $flowTo = null;

        if ($tudu->reviewer) {
            $reviewer = $tudu->reviewer;

            $stepId = $flow->addStep(array(
            'type' => Dao_Td_Tudu_Step::TYPE_EXAMINE,
            'prev' => $prev
            ));

            $flowTo = $prev = $stepId;

            foreach ($reviewer as $item) {
                $flow->addStepSection($stepId, $item);
            }

            $tudu->reviewer = $flow->getStepSection($stepId, 0);
        }

        if ($tudu->to) {
            $to = $tudu->to;

            $stepId = $flow->addStep(array(
            'type' => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
            'prev' => $prev
            ));

            foreach ($to as $item) {
                $flow->addStepSection($stepId, $item);
            }

            $tudu->to = $flow->getStepSection($stepId, 0);

            if (0 !== strpos($prev, '^')) {
                $flow->updateStep($prev, array('next' => $stepId));
            }

            if (null === $flowTo) {
                $flowTo = $stepId;
            }
        }

        $flow->flowTo($flowTo);

        // 到头了
        if (0 == strpos($flow->currentStepId, '^')) {
            return ;
        }

        $step = $flow->getStep($flow->currentStepId);

        $su    = $flow->getStepSection($flow->currentStepId);
        $users = array();

        foreach ($su as $k => $u) {
            $users[$u['username']] = array(
            'uniqueid' => $u['uniqueid'],
            'username' => $u['username'],
            'truename' => $u['truename'],
            'email'    => $u['username']
            );
        }

        if ($step['type'] == 1) {
            if (!$tudu->reviewer) {
                $tudu->reviewer = $users;
            }
        } else {
            if (!$tudu->to) {
                $tudu->to = $users;
            }
        }
    }

    /**
     * 从自动工作流中套用模板
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareFlowFromTemplate(Model_Tudu_Tudu &$tudu, Model_Tudu_Extension_Flow &$flow)
    {
        if (!$tudu->flowId) {
            return ;
        }

        /* @var $daoFlowTpl Dao_Td_Flow_Flow */
        $daoFlowTpl = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);

        $flowTpl = $daoFlowTpl->getFlow(array('flowid' => $tudu->flowId), array('isvalid' => 1));

        if (null == $flowTpl) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu flow not exists', Model_Tudu_Exception::FLOW_NOT_EXISTS);
        }

        $flow->flowId = $flowTpl->flowId;
        $steps = $flowTpl->steps;

        $firstStep = reset($steps);
        foreach ($steps as $item) {
            $stepId = $item['stepid'];

            $flow->addStep(array(
                'stepid' => $stepId,
                'prev'   => $item['prev'],
                'next'   => $item['next'],
                'type'   => $item['type'],
                'description' => $item['description'],
                'subject' => $item['subject']
            ));

            if (is_array($item['sections'])) {
                foreach ($item['sections'] as $section) {
                    $flow->addStepSection($stepId, $section);
                }
            } else {
                $flow->addStepSection($stepId, $item['sections']);
            }
        }

        // 更新图度执行人
        $users = reset($firstStep['sections']);
        $flow->flowTo($firstStep['stepid']);

        //XXX
        foreach ($users as $k => $u) {
            $users[$k]['email'] = $users[$k]['username'];
        }

        if ($firstStep['type'] == 1) {
            $tudu->reviewer = $users;
        } else  {
            $tudu->to = $users;

            if ($firstStep == 2) {
                $tudu->acceptMode = 1;
            }
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function saveTuduFlow(Model_Tudu_Tudu &$tudu)
    {
        $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');
        if (null === $flow) {
            return ;
        }

        if (!$flow->tuduId) {
            $flow->tuduId = $tudu->tuduId;
        }

        if ($flow->fromFlow) {
            $this->updateFlow($flow);
        } else {
            $this->createFlow($flow);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function updateFlow(Model_Tudu_Extension_Flow $flow, $cancelCurrent = false)
    {
        /* @var $daoFlow Dao_Td_Tudu_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        if (!$daoFlow->updateFlow($flow->tuduId, $flow->toArray())) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu flow save failed', Model_Tudu_Exception::SAVE_FAILED);
        }
    }

    /**
     *
     * @param MOdel_Tudu_Tudu $tudu
     */
    public function createFlow(Model_Tudu_Extension_Flow $flow)
    {
        /* @var $daoFlow Dao_Td_Tudu_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        if (!$daoFlow->createFlow($flow->toArray())) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu flow save failed', Model_Tudu_Exception::SAVE_FAILED);
        }
    }

}