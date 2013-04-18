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
 * @version    $Id: Flow.php 2755 2013-02-22 09:50:47Z chenyongfa $
 */

/**
 * 工作流扩展
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension_Flow extends Tudu_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    const NODE_HEAD = '^head';
    const NODE_END  = '^end';
    const NODE_BREAK  = '^break';
    const NODE_UPPER  = '^upper';

    /**
     *
     * @var 部门列表
     */
    protected $_depts;

    /**
     * 创建步骤
     *
     * @param $tuduId
     * @param $params
     */
    public function createStep(array $params)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->createStep($params);
    }

    /**
     * 更新步骤
     *
     * @param $tuduId
     * @param $stepId
     * @param $params
     */
    public function updateStep($tuduId, $stepId, array $params)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->updateStep($tuduId, $stepId, $params);
    }

    /**
     * 获取步骤数据
     *
     * @param $tuduId
     * @param $stepId
     */
    public function getStepById($tuduId, $stepId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getStep(array('tuduid' => $tuduId, 'stepid' => $stepId));
    }

    /**
     * 获取步骤列表
     *
     * @param $tuduId
     */
    public function getSteps($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getSteps(array('tuduid' => $tuduId), null, 'ordernum ASC');
    }

    /**
     * 获取工作流
     *
     * @param $flowId
     */
    public function getFlowById($flowId)
    {
        return $this->getDao('Dao_Td_Flow_Flow')->getFlow(array('flowid' => $flowId));
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
     * @param string $tuduId
     * @param string $stepId
     * @param string $uniqueId
     * @param array  $params
     * @return boolean
     */
    public function updateStepUser($tuduId, $stepId, $uniqueId, array $params)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->updateUser($tuduId, $stepId, $uniqueId, $params);
    }

    /**
     *
     * @param string $tuduId
     */
    public function getTuduStepUsers($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getTuduStepUsers($tuduId);
    }

    /**
     * 获取步骤执行人列表
     *
     * @param string $tuduId
     * @param string $stepId
     */
    public function getStepUsers($tuduId, $stepId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getUsers($tuduId, $stepId);
    }

    /**
     *
     * @param string $tuduId
     * @param string $stepId
     * @param string $uniqueId
     */
    public function getStepUser($tuduId, $stepId, $uniqueId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getCurrentStep($tuduId, $stepId, $uniqueId);
    }

    /**
     * 删除步骤执行人
     *
     * @param $tuduId
     * @param $stepId
     * @param $uniqueId
     */
    public function removeStepUsers($tuduId, $stepId, $uniqueId = null)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->deleteUsers($tuduId, $stepId, $uniqueId);
    }

    /**
     * 更新步骤用户
     *
     * @param $tuduId
     * @param $stepId
     * @param $users
     */
    public function updateStepUsers(Tudu_Tudu_Storage_Tudu &$tudu, $stepId, array $users)
    {
        $currentUsers = $this->getStepUsers($tudu->tuduId, $stepId);

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

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
     * 删除步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function deleteStep($tuduId, $stepId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->deleteStep($tuduId, $stepId);
    }

    /**
     * 删除后续步骤
     *
     * @param string $tuduId
     * @param string $stepId
     */
    public function deleteNextSteps($tuduId, $stepId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->removeNextSteps($tuduId, $stepId);
    }

    /**
     * 删除所有步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function deleteAllSteps($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->deleteSteps($tuduId);
    }

    /**
     * 设置图度当前步骤
     *
     * @param $tuduId
     * @param $stepId
     */
    public function flowToStep($tuduId, $stepId)
    {
        //return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tuduId, array('stepid' => $stepId));
    }

    /**
     *
     * @param $tudu
     * @param $stepId
     * @param $users
     */
    public function cancelStep(Tudu_Tudu_Storage_Tudu &$tudu, $stepId)
    {
        $this->updateStep($tudu->tuduId, $stepId, array('status' => 4));

        $users = $this->getStepUsers($tudu->tuduId, $stepId);

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        foreach ($users as $item) {
            list($email, $trueName) = explode(' ', $item['userinfo'], 2);


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
    }

    /**
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     * @param array                  $params
     * @return array
     */
    public function onPrepare(Tudu_Tudu_Storage_Tudu &$tudu, array $params)
    {
        if ($tudu->flowId && !$tudu->stepId) {
            $this->onPrepareFlow($tudu);
        }

        if ($tudu->reviewer && !$tudu->to && $tudu->type != 'notice') {
            $tudu->to = Tudu_Tudu_Storage::formatRecipients($tudu->from);
        }
    }

    /**
     * 整理工作步骤
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function onPrepareFlow(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        $flow = $this->getFlowById($tudu->flowId);

        if (null !== $flow) {
            $flow = $flow->toArray();

            // 没有步骤
            if (count($flow['steps']) <= 0) {
                require_once 'Tudu/Tudu/Exception.php';
                throw new Tudu_Tudu_Exception('Flow has not any steps', Tudu_Tudu_Exception::CODE_FLOW_STEP_NULL);
            }

            // 整理抄送人
            if (!empty($flow['cc'])) {
                $cc = array();
                foreach ($flow['cc'] as $key => $item) {
                    if (false !== strpos($key, '@')) {
                        $cc[$key] = array('email' => $key, 'truename' => $item[0]);
                    } else {
                        $cc[$key] = array('groupid' => $key, 'truename' => $item[0]);
                    }
                }
                $tudu->cc = array_merge($tudu->cc, $cc);
            }

            // 第一步骤ID
            $steps       = $flow['steps'];
            $prevUsers   = array(array('email' => $tudu->email));
            $prevType    = 0;
            $addressBook = Tudu_AddressBook::getInstance();
            $depts       = $this->_getDepartments($tudu->orgId);
            $tuduSteps   = array();
            $orderNum    = 1;

            foreach ($steps as $key => $step) {
                $stepId = $step['id'];

                if ($step['type'] == 1) {
                    // 上级审批
                    if ($step['users'] == '^upper') {
                        // 上一步是审批
                        $reviewerIds = array();

                        if ($prevType == 1) {
                            foreach ($prevUsers as $item) {
                                foreach ($item as $user) {
                                    $user = $addressBook->searchUser($tudu->orgId, $user['email']);

                                    if (!$user) {
                                        require_once 'Tudu/Tudu/Exception.php';
                                        throw new Tudu_Tudu_Exception('User is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER);
                                    }

                                    $moderatorIds = $this->_getUpper($user['email'], $tudu->orgId, $user['deptid']);
                                    foreach ($moderatorIds as $uid) {
                                        $reviewerIds[] = $uid;
                                    }
                                }
                            }
                        } else {
                            foreach ($prevUsers as $user) {
                                $user = $addressBook->searchUser($tudu->orgId, $user['email']);

                                if (!$user) {
                                    require_once 'Tudu/Tudu/Exception.php';
                                    throw new Tudu_Tudu_Exception('User is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER);
                                }

                                $moderatorIds = $this->_getUpper($user['email'], $tudu->orgId, $user['deptid']);
                                foreach ($moderatorIds as $uid) {
                                    $reviewerIds[] = $uid;
                                }
                            }
                        }

                        if (empty($reviewerIds)) {
                            require_once 'Tudu/Tudu/Exception.php';
                            throw new Tudu_Tudu_Exception('Upper is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_UPPER);
                        }

                        $reviewers   = array();
                        $reviewerIds = array_unique($reviewerIds);
                        foreach ($reviewerIds as $uId) {
                            $user = $addressBook->searchUser($tudu->orgId, $uId . '@' . $tudu->orgId);

                            if (empty($user)) {
                                require_once 'Tudu/Tudu/Exception.php';
                                throw new Tudu_Tudu_Exception('User is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER);
                            }

                            $reviewers[] = $user;
                        }

                        $users     = array($reviewers);
                        $prevUsers = $users;

                        // 指定
                    } else {
                        $prevUsers = $users = Tudu_Tudu_Storage::formatReviewer($step['users']);

                        foreach ($users as $item) {
                            foreach ($item as $u) {
                                $user = $addressBook->searchUser($tudu->orgId, $u['email']);

                                if (!$user) {
                                    require_once 'Tudu/Tudu/Exception.php';
                                    throw new Tudu_Tudu_Exception('User is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER);
                                }
                            }
                        }
                    }

                    $recipients   = array();
                    $processIndex = 1;
                    foreach ($users as $item) {
                        foreach ($item as $user) {
                            $recipients[] = array(
                                'email'        => $user['email'],
                                'truename'     => $user['truename'],
                                'processindex' => $processIndex,
                                'stepstatus'   => $processIndex == 1 ? 1 : 0
                            );
                        }
                        $processIndex ++;
                    }

                    $tuduSteps[$stepId] = array(
                        'orgid'      => $tudu->orgId,
                        'tuduid'     => $tudu->tuduId,
                        'uniqueid'   => $tudu->uniqueId,
                        'stepid'     => $stepId,
                        'type'       => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                        'prevstepid' => $step['prev'],
                        'nextstepid' => $step['next'],
                        'users'      => $recipients,
                        'ordernum'   => $orderNum ++,
                        'createtime' => time()
                    );

                } else {
                    $prevUsers = $users = Tudu_Tudu_Storage::formatRecipients($step['users']);

                    foreach ($prevUsers as $u) {
                        $user = $addressBook->searchUser($tudu->orgId, $u['email']);

                        if (!$user) {
                            require_once 'Tudu/Tudu/Exception.php';
                            throw new Tudu_Tudu_Exception('User is not exists', Tudu_Tudu_Exception::CODE_NOT_EXISTS_USER);
                        }
                    }

                    $tuduSteps[$stepId] = array(
                        'orgid'      => $tudu->orgId,
                        'tuduid'     => $tudu->tuduId,
                        'uniqueid'   => $tudu->uniqueId,
                        'stepid'     => $stepId,
                        'type'       => $step['type'] == 2 ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                        'prevstepid' => $step['prev'],
                        'nextstepid' => $step['next'],
                        'users'      => $users,
                        'ordernum'   => $orderNum ++,
                        'createtime' => time()
                    );
                }

                $prevType  = $step['type'];
            }

            $tudu->steps = $tuduSteps;
            // 第一步骤ID
            $firstStep    = reset($tuduSteps);
            $tudu->stepId = $firstStep['stepid'];
            if ($firstStep['type'] == 1) {
                $users = $firstStep['users'];
                $reviewers = array();
                foreach ($users as $user) {
                    if ($user['processindex'] == 1) {
                        $reviewers[] = $user;
                    }
                }

                $tudu->reviewer = array($reviewers);
            } else {
                $tudu->to = $firstStep['users'];
            }
            $tudu->status = Dao_Td_Tudu_Tudu::STATUS_DOING;
        }
    }

    /**
     * 根据工作流创建图度步骤
     *
     * @param $tudu
     */
    public function createFlowSteps(Tudu_Tudu_Storage_Tudu &$tudu)
    {

        if ($tudu->flowId && $tudu->steps) {

            $steps    = $tudu->steps;

            // 第一步骤ID
            /*$firstStep    = reset($steps);
            $tudu->stepId = $firstStep['stepid'];
            if ($firstStep['type'] == 1) {
                $users = $firstStep['users'];
                $reviewers = array();
                foreach ($users as $user) {
                    if ($user['processindex'] == 1) {
                        $reviewers[] = $user;
                    }
                }

                $tudu->reviewer = array($reviewers);
            } else {
                $tudu->to = $firstStep['users'];
            }*/

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
/*
        foreach ($tudu->steps as $key => $step) {
            $stepId = $step['id'];

            if ($step['type'] == 1) {
                $users = array();
                $processIndex = 1;
                $reviewers = array();

                if ($step['users'] == self::NODE_UPPER) {
                    // 取上一步骤
                    $prevStep = $tudu->steps[$key - 1];
                    if (isset($prevStep)) {
                        $addressBook = Tudu_AddressBook::getInstance();
                        $reviewerIds = array();
                        if ($prevStep['type'] == 1) {
                            $prevUsers = Tudu_Tudu_Storage::formatReviewer($prevStep['users']);
                            foreach ($prevUsers as $item) {
                                foreach ($item as $user) {
                                    $user = $addressBook->searchUser($tudu->orgId, $user['email']);
                                    if ($user) {
                                        $dept = $this->getDepartment($tudu->orgId, $user['deptid']);
                                        if (empty($dept->moderators)) {
                                            continue;
                                        }
                                        foreach ($dept->moderators as $m) {
                                            $reviewerIds[] = $m;
                                        }
                                    }
                                }
                            }
                        } else {
                            $prevUsers = Tudu_Tudu_Storage::formatRecipients($prevStep['users']);
                            foreach ($prevUsers as $user) {
                                $user = $addressBook->searchUser($tudu->orgId, $user['email']);
                                if ($user) {
                                    $dept = $this->getDepartment($tudu->orgId, $user['deptid']);
                                    if (empty($dept->moderators)) {
                                        continue;
                                    }
                                    foreach ($dept->moderators as $m) {
                                        $reviewerIds[] = $m;
                                    }
                                }
                            }
                        }

                        if (empty($reviewerIds)) {
                            //throw new Zend_Controller_Action_Exception('', 404);
                        }

                        $reviewerIds = array_unique($reviewerIds);
                        foreach ($reviewerIds as $userId) {
                            $user = $addressBook->searchUser($tudu->orgId, $userId . '@' . $tudu->orgId);
                            if ($user) {
                                end($reviewers);
                                $last = key($reviewers);
                                $reviewers[$last][] = array('email' => $user['email'], 'truename' => $user['truename']);
                            }
                        }
                    }
                } else {
                    $reviewers    = Tudu_Tudu_Storage:: _formatReviewer($step['users']);
                }

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

                $steps[$stepId] = array(
                    'orgid'      => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'uniqueid'   => $tudu->uniqueId,
                    'stepid'     => $stepId,
                    'type'       => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                    'prevstepid' => $step['prev'],
                    'nextstepid' => $step['next'],
                    'users'      => $users,
                    'ordernum'   => $orderNum ++
                );

            } else {
                $recipients = Tudu_Tudu_Storage::formatRecipients($step['users']);

                $steps[$stepId] = array(
                    'orgid'      => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'uniqueid'   => $tudu->uniqueId,
                    'stepid'     => $stepId,
                    'type'       => $step['type'] == 2 ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'prevstepid' => $step['prev'],
                    'nextstepid' => $step['next'],
                    'users'      => $recipients,
                    'ordernum'   => $orderNum ++
                );
            }
        }
*/
        foreach ($steps as $step) {
            if ($this->createStep($step)) {
                $recipients = $this->prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);
            }
        }

        $tudu->stepNum = count($steps);

        return true;
    }

    /**
     * 更新图度工作流后续步骤
     *
     * @param $tudu
     */
    public function updateFlowSteps(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /*if ($tudu->stepId != self::NODE_HEAD && $tudu->stepId != self::NODE_BREAK && !$tudu->isDraft()) {
            return false;
        }*/

        $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

        $currentStep = $tudu->stepId && false === strpos($tudu->stepId, '^') ? $steps[$tudu->stepId] : array_pop($steps);

        $stepNum   = count($steps);
        $newSteps  = array();

        // 当前为审批步骤
        if ($currentStep['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
            $stepId   = $currentStep['stepid'];
            $reviewer = array();
            $pidx     = null;

            // 获取步骤审批人
            $users = $this->getStepUsers($tudu->tuduId, $stepId);//var_dump($users);exit;
            foreach ($users as &$user) {
                if ($user['status'] == 2) {
                    continue;
                }
                $userInfo = explode(' ', $user['userinfo']);

                if ($pidx == $user['processindex']) {
                    end($reviewer);
                    $last = key($reviewer);
                    $reviewer[$last][] = array('email' => $userInfo[0], 'truename' => $userInfo[1]);
                } else {
                    $reviewer[] = array(array('email' => $userInfo[0], 'truename' => $userInfo[1]));
                }
                $pidx = $user['processindex'];
            }

            $tudu->reviewer = $reviewer;
            $tudu->stepId   = $stepId;
            var_dump($tudu->reviewer);
        // 其他
        } else {

        }
        exit;
    }

    /**
     * 创建图度步骤
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function createTuduSteps(Tudu_Tudu_Storage_Tudu &$tudu)
    {
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

            if ($tudu->stepto) {
                $countTo = count($tudu->stepto);
                $i = 0;
                foreach ($tudu->stepto as $item) {
                    $i++;
                    $users = array();
                    foreach ($item as $to) {
                        $users[] = array(
                            'email'        => $to['email'],
                            'truename'     => $to['truename']
                        );
                    }

                    $nextStepId = Dao_Td_Tudu_Step::getStepId();

                    $steps[$stepId] = array(
                        'orgid' => $tudu->orgId,
                        'tuduid' => $tudu->tuduId,
                        'uniqueid' => $tudu->uniqueId,
                        'stepid'   => $stepId,
                        'type'     => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                        'prevstepid' => $prevId,
                        'nextstepid' => $countTo == $i ? self::NODE_END : $nextStepId,
                        'users'      => $users,
                        'ordernum'   => $orderNum ++,
                        'createtime' => time()
                    );

                    $prevId = $stepId;
                    $stepId  = $nextStepId;
                }
            }
        }

        foreach ($steps as $step) {
            if ($this->createStep($step)) {
                $recipients = $this->prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);
            }
        }

        $tudu->stepId  = $currentStepId;
        $tudu->stepNum = count($steps);

        return true;
    }

    /**
     * 更新图度后续步骤
     *
     * @param $tudu
     */
    public function updateTuduSteps(Tudu_Tudu_Storage_Tudu &$tudu, $cancelCurrent = true)
    {
        // 草稿
        if ($tudu->isDraft() || $tudu->stepId ==  self::NODE_HEAD || $tudu->stepId == self::NODE_BREAK) {
            $this->deleteAllSteps($tudu->tuduId);
            if ($tudu->flowId) {
                $this->onPrepareFlow($tudu);
                if ($tudu->isChange('to')) {
                    $manager = Tudu_Tudu_Manager::getInstance();
                    $to = $tudu->getAttribute('to');
                    $to = isset($to) ? Tudu_Tudu_Storage::formatReceiver($to) : null;
                    $manager->updateTudu($tudu->tuduId, array('to' => $to));
                }
                return $this->createFlowSteps($tudu);
            } else {
                return $this->createTuduSteps($tudu);
            }
        }

        if ($tudu->flowId) {
            return true;
            //return $this->updateFlowSteps($tudu);
        }

        $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

        if ($tudu->stepId && false === strpos($tudu->stepId, '^')) {
            $currentStep = $steps[$tudu->stepId];
        } else {
            foreach ($steps as $step) {
                if ($step['stepstatus'] != 4) {
                    $currentStep = $step;
                    break;
                }
            }
        }
        //$currentStep = $tudu->stepId && false === strpos($tudu->stepId, '^') ? $steps[$tudu->stepId] : array_pop($steps);

        // 当前为审批步骤
        $stepNum  = count($steps);
        $newSteps = array();
        $updatePrevStepId = null;
        if (isset($currentStep) && $currentStep['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {

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

                $updatePrevStepId = $prevStepId = $currentStep['stepid'];

                $tudu->stepId = $currentStep['stepid'];

            } else {
                // 审批步骤作废
                //$this->cancelStep($tudu, $currentStep['stepid']);

                $this->updateStepUsers($tudu, $currentStep['stepid'], array());

                $updatePrevStepId = $prevStepId = $currentStep['prevstepid'];

                $tudu->stepId = $execStepId;
            }

            if ($tudu->type != 'notice') {
                $updateNextStepId = $stepId = $execStepId;
                if ($tudu->stepto) {
                    $countTo = count($tudu->stepto);
                    $i = 0;
                    $orderNum = $currentStep['ordernum'];
                    foreach ($tudu->stepto as $item) {
                        $i++;
                        $users = array();
                        foreach ($item as $to) {
                            $users[] = array(
                                'email'        => $to['email'],
                                'truename'     => $to['truename']
                            );
                        }

                        $nextStepId = Dao_Td_Tudu_Step::getStepId();

                        $newSteps[] = array(
                            'orgid' => $tudu->orgId,
                            'tuduid' => $tudu->tuduId,
                            'uniqueid' => $tudu->uniqueId,
                            'stepid'   => $stepId,
                            'type'     => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                            'prevstepid' => $prevStepId,
                            'nextstepid' => $countTo == $i ? self::NODE_END : $nextStepId,
                            'users'      => $users,
                            'ordernum'   => ++$orderNum,
                            'createtime' => time()
                        );

                        $prevStepId = $stepId;
                        $stepId  = $nextStepId;
                    }
                }
                $nextStepId = $stepId;

            } else {
                $updateNextStepId = $nextStepId = self::NODE_END;
            }

        // 其他
        } else {
            $isChangeTo = false;

            // 比较图度执行人
            $stepUsers = $this->getTuduStepUsers($tudu->tuduId);
            $stepTo = array(); //修改前的步骤执行人
            $stepSize = 0; //修改前的步骤个数
            $tempStepId = null;
            foreach ($stepUsers as &$u) {
                if ($tudu->stepId == $u['stepid'] && $u['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && $u['stepstatus'] != 4) {
                    if ($tempStepId != $u['stepid']) {
                        $stepSize++;
                    }
                    $tempStepId = $u['stepid'];
                    $info = explode(' ', $u['userinfo']);
                    $u['email']    = $info[0];
                    $u['truename'] = $info[1];
                    $stepTo[$u['email']] = $u;
                }
            }

            $currentTo = array(); //当前修改后的执行人
            $currentStepSize = 0; //当前修改后步骤个数
            foreach ($tudu->stepto as $item) {
                foreach ($item as $to) {
                    $currentTo[$to['email']] = $to;
                }
                $currentStepSize++;
            }
            $src = array_keys($stepTo);
            $cur = array_keys($currentTo);
            $srcCount = count($src);

            $isChangeTo = $stepSize != $currentStepSize || count($cur) != $srcCount || count(array_uintersect($src, $cur, "strcasecmp")) != $srcCount;

            // 步骤作废
            if ($isChangeTo || $tudu->isChange('acceptmode')) {
                if ($cancelCurrent) {
                    $this->updateStep($tudu->tuduId, $currentStep['stepid'], array('status' => 4));
                    $updatePrevStepId = $prevStepId = $currentStep['prevstepid'];
                } else {
                    $updatePrevStepId = $prevStepId = $currentStep['stepid'];
                }
            } else {
                $updatePrevStepId = $prevStepId = $currentStep['stepid'];
                $tudu->stepId = $prevStepId;
            }

            $updateNextStepId = $nextStepId = null;
            $prevId     = $prevStepId;
            $orderNum   = $currentStep['ordernum'];
            if ($tudu->reviewer) {
                // 前一步骤作废
                if ($cancelCurrent) {
                    $this->updateStep($tudu->tuduId, $prevId, array('status' => 4));
                }

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

                $stepId = Dao_Td_Tudu_Step::getStepId();
/*插入修改人
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
*/

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
                    $updateNextStepId = $nextStepId = $stepId;
                }
            }

            if ($isChangeTo || count($newSteps) || $tudu->isChange('acceptmode')) {
                $stepId = Dao_Td_Tudu_Step::getStepId();
                if (!$nextStepId) {
                    $updateNextStepId = $nextStepId = $stepId;
                }

                if (isset($newSteps[$prevId])) {
                    $newSteps[$prevId]['nextstepid'] = $stepId;
                }

                // 转发
                if ($tudu->action == 'forward') {
                    $newSteps[$stepId] = array(
                        'orgid'      => $tudu->orgId,
                        'tuduid'     => $tudu->tuduId,
                        'stepid'     => $stepId,
                        'uniqueid'   => $tudu->uniqueId,
                        'prevstepid' => $prevId,
                        'nextstepid' => '^end',
                        'type'       => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                        'ordernum'   => ++$orderNum,
                        'createtime' => time(),
                        'users'      => $tudu->to
                    );

                } else {
                    // 修改
                    if ($tudu->stepto) {
                        $countTo = count($tudu->stepto);
                        $i = 0;
                        foreach ($tudu->stepto as $item) {
                            $i++;
                            $users = array();
                            foreach ($item as $to) {
                                $users[] = array(
                                    'email'        => $to['email'],
                                    'truename'     => $to['truename']
                                );
                            }

                            $nextId = Dao_Td_Tudu_Step::getStepId();

                            $newSteps[$stepId] = array(
                                'orgid' => $tudu->orgId,
                                'tuduid' => $tudu->tuduId,
                                'uniqueid' => $tudu->uniqueId,
                                'stepid'   => $stepId,
                                'type'     => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                                'prevstepid' => $prevId,
                                'nextstepid' => $countTo == $i ? self::NODE_END : $nextId,
                                'users'      => $users,
                                'ordernum'   => ++$orderNum,
                                'createtime' => time()
                            );

                            $prevId = $stepId;
                            $stepId  = $nextId;
                        }
                    }
                }
            }

            if ($nextStepId) {
                $tudu->stepId = $updateNextStepId;
            }
        }

        if (!empty($newSteps)) {
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

            $this->updateStep($tudu->tuduId, $updatePrevStepId, array('nextstepid' => $updateNextStepId));
        }

        $tudu->stepNum = $stepNum;
        /*return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
            array(
                'stepid'  => $tudu->stepId,
                'stepnum' => $stepNum
            )
        );*/
    }

    /**
     * 更新图度后执行事件
     */
    public function postUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($this->updateTuduSteps($tudu)) {
            return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
                array(
                    'stepid'  => $tudu->stepId,
                    'stepnum' => $tudu->stepNum
                )
            );
        }

        return true;
    }

    /**
     *
     */
    public function postCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->flowId) {
            $ret = $this->createFlowSteps($tudu);
        } else {
            $ret = $this->createTuduSteps($tudu);
        }
        if ($ret) {
            return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
                array(
                    'stepid'  => $tudu->stepId,
                    'stepnum' => $tudu->stepNum
                )
            );
        }

        return true;
    }

    /**
     * 申请审批
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function onApply(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if (!$tudu->reviewer) {
            return false;
        }

        if ($this->updateTuduSteps($tudu)) {
            return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
                array(
                    'stepid'  => $tudu->stepId,
                    'stepnum' => $tudu->stepNum
                )
            );
        }
    }

    /**
     * 公告审批
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     * @param boolean $isAgree
     */
    public function noticeReview(Tudu_Tudu_Storage_Tudu &$tudu, $isAgree)
    {
        // 同意
        if ($isAgree) {
            // 更新当前用户状态
            $this->updateStepUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 2));

            $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

            if (!isset($steps[$tudu->stepId])) {
                return false;
            }

            $step = $steps[$tudu->stepId];

            $users = $this->getStepUsers($tudu->tuduId, $tudu->stepId);

            // 当前审批人
            $nextUser  = false;
            $nextStep  = true;
            $nextIndex = null;
            $currIndex = null;
            $reviewer  = array();
            $tudu->sameReview = false;
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
                    if ($user['status'] == 2) {
                        continue ;
                    }
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

                    $this->updateStepUser($tudu->tuduId, $tudu->stepId, $user['uniqueid'], array('status' => 1));

                    $nextStep = false;
                }
            }

            if (count($reviewer)) {
                $tudu->reviewer = array($reviewer);
            }

            // 下一步即发送公告
            if ($nextStep) {
                // 清空审批人
                $tudu->reviewer = null;
                // 发送公告到接收人
                $tudu->cc      = Tudu_Tudu_Storage::formatRecipients($tudu->cc);
                $tudu->stepId  = $step['nextstepid'];
            }

        // 不同意
        } else {
            // 更新当前用户状态
            $this->updateStepUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 3));

            $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

            if (!isset($steps[$tudu->stepId])) {
                return false;
            }

            $step         = $steps[$tudu->stepId];
            $tudu->stepId = $step['prevstepid'];
        }

        return true;

    }

    /**
     * 审批
     *
     * @param $tudu
     */
    public function onReview(Tudu_Tudu_Storage_Tudu &$tudu, $isAgree)
    {
        // 公告审批处理
        if ($tudu->type == 'notice') {
            return $this->noticeReview($tudu, $isAgree);
        }

        // 更新当前用户状态
        if ($isAgree) {

            $this->updateStepUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 2));
            $tudu->currentStepStatus = false;

            // 更新图度后续步骤
            if ($tudu->reviewer || $tudu->to) {

                $this->updateTuduSteps($tudu);

            // 直接同意
            } else {

                $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

                if (!isset($steps[$tudu->stepId])) {
                    return false;
                }

                $step = $steps[$tudu->stepId];

                $users = $this->getStepUsers($tudu->tuduId, $tudu->stepId);

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
                    } elseif (null !== $currIndex && $user['processindex'] == $currIndex && $user['status'] > 1) {
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

                        $this->updateStepUser($tudu->tuduId, $tudu->stepId, $user['uniqueid'], array('status' => 1));

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

                    $tudu->currentStepStatus = true; //当前步骤是否已完结
                    $updateStatus = false;
                    $nextStep = $steps[$step['nextstepid']];
                    $nextIndex = null;

                    $nextStepUsers = $this->getStepUsers($tudu->tuduId, $nextStep['stepid']);

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
                                $this->updateStepUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
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
                                $this->updateStepUser($tudu->tuduId, $nextStep['stepid'], $item['uniqueid'], array('status' => 1));
                            }
                        }

                        $tudu->reviewer = null;
                        $tudu->to       = Tudu_Tudu_Storage::formatRecipients($users);
                    }

                    if ($tudu->flowId && $updateStatus) {
                        $this->updateStep($tudu->tuduId, $nextStep['stepid'], array('status' => 0));
                    }

                    $tudu->stepId = $step['nextstepid'];
                }
            }

        } else {
            if ($tudu->flowId) {
                return $this->flowReviewReject($tudu);
            }

            $this->updateStepUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 3));

            // 审批拒绝均打回发起人
            $tudu->stepId = '^head';
            $tudu->to = Tudu_Tudu_Storage::formatRecipients($tudu->from);
/*
            $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

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
                $users = $this->getStepUsers($tudu->tuduId, $toStepId);

                $to = array();
                foreach ($users as $item) {
                    $to[] = $item['userinfo'];
                }

                $tudu->to = Tudu_Tudu_Storage::formatRecipients(implode("\n", $to));
            }
*/
        }
    }

    /**
     * 工作流审批拒绝操作
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function flowReviewReject(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        // 取得步骤
        $steps = $this->getSteps($tudu->tuduId)->toArray('stepid');

        $this->updateStepUser($tudu->tuduId, $tudu->stepId, $tudu->uniqueId, array('status' => 3));

        // 当前步骤不存在，则终止流程
        if (!isset($steps[$tudu->stepId])) {
            $tudu->stepId = self::NODE_BREAK;
            $this->preflowRejectUsers($tudu);

            return true;
        }

        $step     = $steps[$tudu->stepId];
        $prevId   = $step['prevstepid'];

        // 当前步骤的上一步骤是终止流程
        if ($prevId == self::NODE_BREAK) {
            $tudu->stepId = self::NODE_BREAK;
            $this->preflowRejectUsers($tudu);

            return true;
        }

        // 打回到开始
        if ($prevId == self::NODE_HEAD) {
            $tudu->stepId = self::NODE_HEAD;
            $this->preflowRejectUsers($tudu);

            return true;
        }

        // 打回， 步骤不存在，则终止流程
        if (!isset($steps[$prevId])) {
            $tudu->stepId = self::NODE_BREAK;
            $this->preflowRejectUsers($tudu);

            return true;
        }

        // 取得打回步骤信息
        $prev = $steps[$prevId];
        $updateStatus = false;
        $nextIndex    = null;

        $users = $this->getStepUsers($tudu->tuduId, $prev['stepid']);
        if ($prev['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
            $reviewer = array();
            foreach ($users as $item) {
                if (null === $nextIndex) {
                    $nextIndex = $item['processindex'];
                }
                if (null !== $nextIndex && $item['processindex'] != $nextIndex) {
                    break;
                }
                list ($userName, $trueName) = explode(' ', $item['userinfo']);
                $reviewer[] = array('email' => $userName, 'truename' => $trueName);

                if ($item['status'] > 1) {
                    $updateStatus = true;
                    $this->updateStepUser($tudu->tuduId, $prev['stepid'], $item['uniqueid'], array('status' => 1));
                }
            }

            $tudu->reviewer = array($reviewer);
        } else {
            $to = array();
            foreach ($users as $item) {
                list($email, $trueName) = explode(' ', $item['userinfo']);
                $to[$email] = array(
                    'email'    => $email,
                    'truename' => $trueName,
                    'percent'  => (int) $item['percent']
                );

                if ($item['status'] > 1) {
                    $this->updateStepUser($tudu->tuduId, $prev['stepid'], $item['uniqueid'], array('status' => 1));
                    $manager = Tudu_Tudu_Manager::getInstance();
                    $manager->updateProgress($tudu->tuduId, $item['uniqueid'], 0);
                    if ($tudu->parentId) {
                        $manager->calParentsProgress($tudu->parentId);
                    }
                    $updateStatus = true;
                }
            }

            $tudu->reviewer = null;
            $tudu->to       = Tudu_Tudu_Storage::formatRecipients($to);
        }

        if ($updateStatus) {
            $this->updateStep($tudu->tuduId, $prev['stepid'], array('status' => 0));
        }

        $tudu->stepId = $prev['stepid'];

        $this->preflowRejectUsers($tudu);

        return true;
    }

    /**
     *
     * @param $tudu
     */
    public function preflowRejectUsers(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->stepId == '^head' || $tudu->stepId == '^break') {
            $tudu->to = Tudu_Tudu_Storage::formatRecipients($tudu->from);
        } else {
            $users = $this->getStepUsers($tudu->tuduId, $tudu->stepId);

            $to = array();
            foreach ($users as $item) {
                $to[] = $item['userinfo'];
            }

            $tudu->to = Tudu_Tudu_Storage::formatRecipients(implode("\n", $to));
        }

        return true;
    }

    /**
     *
     * @param $tudu
     */
    public function onSend(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        //$this->flowToStep($tudu->tuduId, $tudu->stepId);
        return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
            array(
                'stepid'  => $tudu->stepId,
                'stepnum' => $tudu->stepNum
            )
        );
    }

    /**
     * 转发图度
     *
     * @param $tudu
     */
    public function onForward(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->flowId) {
            return $this->forwardTuduFlow($tudu);
        }

        if ($this->updateTuduSteps($tudu, false)) {
            return $this->getDao('Dao_Td_Tudu_Tudu')->updateTudu($tudu->tuduId,
                array(
                    'stepid'  => $tudu->stepId,
                    'stepnum' => $tudu->stepNum
                )
            );
        }
    }

    /**
     * 处理工作流任务的转发
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function forwardTuduFlow(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /* @var $daoStep Dao_Td_Tudu_Flow */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');

        if (!isset($steps[$tudu->stepId])) {
            return ;
        }

        $currentStep = $steps[$tudu->stepId];
        $nextStep    = isset($steps[$currentStep['nextstepid']]) ? $steps[$currentStep['nextstepid']] : null;

        $to = $tudu->to;
        $fistStepId = null;
        $lastStepId = null;
        $count      = 0;
        $steps      = array();

        $to = array($to);

        foreach ($to as $arr) {
            $stepId = Dao_Td_Tudu_Step::getStepId();
            $step = array(
                'orgid'      => $tudu->orgId,
                'tuduid'     => $tudu->tuduId,
                'uniqueid'   => $tudu->uniqueId,
                'stepid'     => $stepId,
                'prevstepid' => $tudu->stepId,
                'nextstepid' => $currentStep['nextstepid'],
                'type'       => Dao_Td_Tudu_Step::TYPE_EXECUTE,
                'ordernum'   => $currentStep['ordernum'] + $count + 1,
                'createtime' => time()
            );

            $users = array();
            foreach ($arr as $userName => $u) {
                $users[] = array(
                    'email'    => $u['email'],
                    'truename' => $u['truename']
                );
            }
            $step['users'] = $users;

            if ($count == 0) {
                $firstStepId = $stepId;
            }

            $count ++;

            $steps[] = $step;
        }

        $lastStepId = $stepId;

        $daoStep->updateStep($tudu->tuduId, $currentStep['stepid'], array('nextstepid' => $stepId));
        $daoStep->updateStep($tudu->tuduId, $currentStep['nextstepid'], array('prevstepid' => $stepId));
        if (null != $nextStep) {
            $daoStep->updateNextStepsOrder($tudu->tuduId, $nextStep['ordernum'], $count);
        }

        foreach ($steps as $step) {
            if ($this->createStep($step)) {
                $recipients = $this->prepareRecipients($tudu->orgId, $tudu->uniqueId, $step['users']);

                $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                $this->addStepUsers($tudu->tuduId, $step['stepid'], $recipients, $processIndex);
            }
        }
        /* @var $daoStep Dao_Td_Tudu_Tudu */
        //$daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        //$daoTudu->updateTudu($tudu->tuduId, array('stepid' => $firstStepId));

        $tudu->stepId = $firstStepId;
    }

    /**
     * 获取部门列表
     *
     * @param string $orgId
     */
    protected function _getDepartments($orgId)
    {
        if (null === $this->_depts) {
            /* @var Dao_Md_Department_Department */
            $daoDepts = $this->getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $this->_depts = $daoDepts->getDepartments(array(
                'orgid'  => $orgId
            ))->toArray('deptid');
        }

        return $this->_depts;
    }

    /**
     *
     * @param string $userId
     * @param string $deptId
     */
    protected function _getUpper($email, $orgId, $deptId)
    {
        list($userId, ) = explode('@', $email);
        $depts = $this->_getDepartments($orgId);

        if (empty($depts[$deptId])) {
            return null;
        }

        $dept = $depts[$deptId];

        if (empty($dept['moderators'])) {
            return null;
        }

        $ret = array();
        // 是当前部门负责人
        if (in_array($userId, $dept['moderators']) && $deptId != '^root') {
            $dept = $depts[$dept['parentid']];
        }

        foreach ($dept['moderators'] as $m) {
            $ret[] = $m;
        }

        return $ret;
    }
}
