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
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Flow_Flow extends Model_Abstract
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
        /**
         * @see Model_Tudu_Extension_Flow
         */
        require_once 'Model/Tudu/Extension/Flow.php';

        $prev = '^head';
        if ($tudu->tuduId && !$tudu->isDraft) {
            /* @var $daoFlow Dao_Td_Tudu_Flow */
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $flowInfo = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

            if (null != $flowInfo) {
                $flow = new Model_Tudu_Extension_Flow($flowInfo->toArray());
                $flow->fromFlow = true;
            }
        }

        if (!isset($flow)) {
            $flow = new Model_Tudu_Extension_Flow(array(
                'orgid'  => $tudu->orgId,
                'tuduid' => $tudu->tuduId
            ));
        }

        if (!$tudu->flowId) {

            // 添加当前编辑提交的人
            $this->renewTuduFlow($tudu, $flow, $prev);

        // 处理工作流任务
        } else {
            $currentStepId = null;
            foreach ($flow->steps as $key => $item) {
                $stepId = $item['stepid'];
                foreach ($item['section'] as $idx => $section) {
                    foreach ($section as $k => $u) {
                        if ($u['status'] > 2) {
                            if ($currentStepId == null) {
                                $currentStepId = $stepId;
                            }

                            $flow->steps[$key]['section'][$idx][$k]['status'] = 1;
                        }
                    }
                }
            }

            if (null !== $currentStepId) {
                $flow->flowTo($currentStepId);
            }
        }

        $tudu->setExtension($flow);
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

        if (!$tudu->flowId) {
            $this->renewTuduFlow($tudu, $flow);
        } else {
            $flow->flowTo();

            $step = $flow->getSteps();
            if ($step['type'] == 1) {
                $tudu->reviewer = $flow->getStepSection($step['stepid']);
            } else {
                $tudu->to       = $flow->getStepSection($step['stepid']);
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
        $prev     = '^head';

        if ($tudu->reviewer || $tudu->to) {
            foreach ($flow->steps as $key => $item) {
                $stepId = $item['stepid'];
                foreach ($item['section'] as $idx => $section) {

                    if ($isExceed) {
                        $flow->deleteStep($stepId);
                        continue ;
                    }

                    foreach ($section as $k => $u) {
                        if (isset($u['status']) && $u['status'] >= 2) {
                            $isExceed = true;
                            $prev = $stepId;
                        }

                        if ($isExceed && $u['status'] < 2) {
                            $flow->removeStepSection($key, $idx);
                        }
                    }

                    if ($isExceed && empty($item['section'])) {
                        $prev = $item['prev'];
                        $flow->deleteStep($stepId);
                    }
                }
            }
        }

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

        $step = $flow->getStep($flow->currentStepId);

        if ($step['type'] == 1) {
            if (!$tudu->reviewer) {
                $tudu->reviewer = $flow->getStepSection($flow->currentStepId);
            }
        } else {
            if (!$tudu->to) {
                $tudu->to = $flow->getStepSection($flow->currentStepId);
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