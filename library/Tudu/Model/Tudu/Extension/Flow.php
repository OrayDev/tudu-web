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
 * @version    $Id: Flow.php 1973 2012-07-06 05:33:16Z chenyongfa $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 会议扩展数据维护实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Flow extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    const NODE_HEAD = '^head';
    const NODE_END  = '^end';
    const NODE_BREAK  = '^break';

    /**
     * 部门列表
     *
     * @var array
     */
    protected $_depts = array();

    /**
     * 保存会议数据
     *
     * @param $data
     */
    public function onSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract $data)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        // 重建流程
        if ($tudu->isDraft || $tudu->stepId == self::NODE_HEAD || $tudu->stepId == self::NODE_BREAK) {
            return $this->rebuildFlow($tudu, $data);
        }

        // 自动工作流 跳过 步骤更新操作
        if ($tudu->flowId) {
            // do something ...
            return ;
        }
    }
    
    public function prevSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract &$data)
    {
        $steps         = $data->getSteps();
        $currentStepId = null;

        // 没有预订流程，按照图度参数生成流程步骤
        if (empty($steps)) {
            $steps = array();
            $orderNum = 1;

            if ($tudu->reviewer) {
                $stepId = Dao_Td_Tudu_Step::getStepId();

                $reviewStep = new Tudu_Model_Tudu_Entity_Step(array(
                    'orgid'      => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'uniqueid'   => $tudu->uniqueId,
                    'stepid'     => $stepId,
                    'type'       => Dao_Td_Tudu_Step::TYPE_EXAMINE,
                    'prevstepid' => self::NODE_HEAD,
                    'nextstepid' => self::NODE_END,
                    //'users'      => $tudu->reviewer,
                    'ordernum'   => $orderNum ++
                ));

                foreach ($tudu->reviewer as $sec) {
                    $reviewStep->addSection($sec);
                }

                $steps[$stepId] = $reviewStep;
                $currentStepId  = $stepId;
            }

            if ($tudu->to) {
                $stepId  = Dao_Td_Tudu_Step::getStepId();
                $prevId  = self::NODE_HEAD;

                if ($currentStepId) {
                    $steps[$currentStepId]->nextStepId = $stepId;
                    $prevId = $currentStepId;
                } else {
                    $currentStepId = $stepId;
                }

                $step = new Tudu_Model_Tudu_Entity_Step(array(
                    'orgid'      => $tudu->orgId,
                    'tuduid'     => $tudu->tuduId,
                    'uniqueid'   => $tudu->uniqueId,
                    'stepid'     => $stepId,
                    'type'       => $tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                    'prevstepid' => $prevId,
                    'nextstepid' => self::NODE_END,
                    'ordernum'   => $orderNum ++
                ));

                $sec = array();
                foreach ($tudu->to as $user) {
                    $sec[] = $user;
                }
                $step->addSection($sec);

                $steps[$stepId] = $step;
            }
        } else {
            $from = explode(' ', $tudu->target);
            $prevUsers = array(array('email' => $from[0], 'truename' => $from[1]));

            $addressBook = Tudu_AddressBook::getInstance();

            foreach ($steps as $step) {
                $users = $step->getAttribute('users');

                // 解析步骤人员
                if (is_string($users)) {
                    if (0 === strpos($users, '^')) {
                        foreach ($prevUsers as $u) {
                            $u = $addressBook->searchUser($tudu->orgId, $u['email']);

                            $sections = $this->_getHeigherUsers($u['email'], $tudu->orgId, $u['deptid'] === null ? '^root' : $u['deptid'], $users == '^uppers');

                            if (null === $sections) {
                                require_once 'Tudu/Model/Tudu/Exception.php';
                                throw new Tudu_Model_Tudu_Exception('Missing flow steps upper reviewer');
                            }
                        }
                    } else {
                        $sections = $this->parseStepUsers($steps, $users);
                    }
                }

                $step->setAttribute('tuduid', $tudu->tuduId);
                $step->setAttribute('orgid', $tudu->orgId);

                foreach ($sections as $section) {
                    $step->addSection($section);
                }

                $prevUsers = array_pop($sections);
            }
        }

        $data->setAttribute('flowsteps', $steps);
    }

    /**
     * 重建图度流程
     *
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    private function rebuildFlow(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Flow $data)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $daoStep->deleteSteps($tudu->tuduId);

        $steps = $data->getAttribute('flowsteps');

        foreach ($steps as $step) {
            $this->createStep($step);
        }

        $firstStep = reset($steps);
        $currentStepId = $firstStep->getAttribute('stepid');

        if ($firstStep->type == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
            $tudu->reviewer = $firstStep->getSections();
        }

        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $daoTudu->updateTudu($tudu->tuduid, array(
            'stepnum' => count($steps),
            'stepid'  => $currentStepId
        ));
    }

    /**
     *
     * @param Tudu_Model_Tudu_Entity_Step $step
     */
    public function createStep(Tudu_Model_Tudu_Entity_Step $step)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep     = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
        $addressBook = Tudu_AddressBook::getInstance();

        $attrs = $step->getAttributes();

        if (!$daoStep->createStep($attrs)) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Exception('Save tudu step failure');
        }

        $sec = $step->getSections();
        $processIndex = 1;

        foreach ($sec as $users) {
            $recipients = array();

            foreach ($users as $key => $item) {
                $user = $addressBook->searchUser($step->orgId, $item['email']);
                if (null === $user) {
                    $user = $addressBook->searchContact($step->uniqueId, $item['email'], $item['truename']);

                    if (null === $user) {
                        $user = $addressBook->prepareContact($item['email'], $item['truename']);
                    }
                }

                $daoStep->addUser(array(
                    'tuduid'       => $step->tuduId,
                    'stepid'       => $step->stepId,
                    'uniqueid'     => $user['uniqueid'],
                    'userinfo'     => $user['email'] . ' ' . $user['truename'],
                    'status'       => $processIndex == 1 ? 1 : 0,
                    'processindex' => $processIndex
                ));
            }

            $processIndex ++;
        }
    }

    /**
     * 解析步骤执行人
     *
     * @param $users
     */
    public function parseStepUsers($tudu, $users)
    {
        $arr  = explode("\n", $users);
        $asyn = true;
        $ret  = array();
        foreach ($arr as $item) {
            $item = trim($item);

            if (0 === strpos($item, '>') || 0 === strpos($item, '+')) {
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

    /**
     * 读取部门数据
     */
    protected function _getDepts($orgId)
    {
        if (empty($this->_depts)) {
            /* @var Dao_Md_Department_Department */
            $daoDepts = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $this->_depts = $daoDepts->getDepartments(array(
                'orgid'  => $orgId
            ))->toArray('deptid');
        }

        return $this->_depts;
    }

    /**
     * 获取上级用户
     *
     * @param string $email
     * @param string $orgId
     * @param string $deptId
     * @return array
     */
    protected function _getHeigherUsers($email, $orgId, $deptId, $isDeep = false)
    {
        list($userId, ) = explode('@', $email);
        $depts = $this->_getDepts($orgId);

        if (empty($depts[$deptId])) {
            return null;
        }

        $dept = $depts[$deptId];

        if (empty($dept['moderators'])) {
            return null;
        }

        $ret = array();
        $sec = array();
        // 是当前部门负责人
        if (in_array($userId, $dept['moderators']) && $deptId != '^root' && $deptId !== NULL) {
            $dept = $depts[$dept['parentid']];
        }

        foreach ($dept['moderators'] as $m) {
            $sec[] = array('email' => $m . '@' . $orgId);
        }
        $ret[] = $sec;

        // 递归上级
        if ($isDeep) {
            while (!empty($dept['parentid']) && isset($depts[$dept['parentid']])) {
                $dept = $depts[$dept['parentid']];

                $sec = array();
                foreach ($dept['moderators'] as $m) {
                    $sec[] = array('email' => $m . '@' . $orgId);
                }
                $ret[] = $sec;
            }
        }

        return $ret;
    }
}