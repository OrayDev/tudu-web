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
 * @version    $Id: Tudu.php 2070 2012-08-22 09:37:26Z cutecube $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @see Dao_Td_Tudu_Flow
 */
require_once 'Dao/Td/Tudu/Flow.php';

/**
 * @see Model_Tudu_Flow_Flow
 */
require_once 'Model/Tudu/Flow/Flow.php';

/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Flow extends Model_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    protected $_handlerClass = 'Model_Tudu_Extension_Handler_Flow';

    /**
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     *
     * @var array
     */
    protected $_steps = array();

    /**
     *
     * @var array
     */
    private $_depts;

    /**
     *
     * @var array
     */
    private $_addressBook;

    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = null)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     *
     * @param array $params
     * @return string
     */
    public function addStep(array $params, $fixPos = true)
    {
        if (!isset($params['type'])) {
            return false;
        }

        $stepId = isset($params['stepid']) ? $params['stepid'] : Dao_Td_Tudu_Flow::getStepId($this->_attrs['tuduid']);
        $prev   = end($this->_steps);
        $prevId = !$prev ? Model_Tudu_Flow_Flow::NODE_START : $prev['stepid'];

        $step = array(
            'stepid' => $stepId,
            'type'   => $params['type'],
            'section'=> array(),
            'prev'   => isset($params['prev']) ? $params['prev'] : $prevId,
            'next'   => isset($params['next']) ? $params['next'] : Model_Tudu_Flow_Flow::NODE_END
        );

        if (!empty($params['subject'])) {
            $step['subject'] = $params['subject'];
        }

        if (!empty($params['description'])) {
            $step['description'] = $params['description'];
        }

        // 上一步
        if (!$this->flowId) {
            if (isset($this->_steps[$step['prev']])) {
                $next = $this->_steps[$step['prev']]['next'];
                if ($this->_steps[$step['prev']]['next'] != Model_Tudu_Flow_Flow::NODE_BREAK) {
                    $this->_steps[$step['prev']]['next'] = $stepId;
                }

                if (empty($params['next'])) {
                	$step['next'] = $next;
                }
                if (0 != strpos($next, '^') && 0 !== strpos($this->_steps[$next]['prev'], '^')) {
                    $this->_steps[$next]['prev'] = $stepId;
                }
            }
        }

        // 调整排序
        if (isset($step['next']) && isset($this->_steps[$step['next']])) {
            $steps = array();

            if (0 !== strpos($this->_steps[$step['next']]['prev'], '^')) {
                $this->_steps[$step['next']]['prev'] = $stepId;
            }

            foreach ($this->_steps as $item) {
                if ($item['stepid'] == $step['next']) {
                    $steps[$step['stepid']] = $step;
                }

                $steps[$item['stepid']] = $item;
            }

            $this->_steps = $steps;

        } else {
            $this->_steps[$step['stepid']] = $step;
        }


        return $stepId;
    }

    /**
     *
     * @param string $stepId
     * @param array  $params
     */
    public function updateStep($stepId, array $params)
    {
        foreach ($this->_steps as &$item) {
            if ($item['stepid'] == $stepId) {
                foreach ($params as $k => $val) {
                    $item[$k] = $val;
                }

                return $this;
            }
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param array  $params
     */
    public function resetStep($stepId)
    {
        if (isset($this->_steps[$stepId])) {
            foreach ($this->_steps[$stepId]['section'] as $idx => $sec) {
                foreach ($sec as $i => $u) {
                    $this->_steps[$stepId]['section'][$idx][$i]['status'] = 0;
                }
            }
        }


        return $this;
    }

    /**
     *
     * @param array $section
     */
    public function addStepSection($stepId, $users, $tudu = null)
    {
        $step = null;
        foreach ($this->_steps as &$st) {
            if ($st['stepid'] == $stepId) {
                $step = &$st;
            }
        }

        if (null === $step) {
            return false;
        }

        $orgId = $this->_attrs['orgid'];
        $sectionUsers = array();
        if (is_array($users)) {
            foreach ($users as $item) {
                $userName = isset($item['username']) ? $item['username'] : $item['email'];

                if (Oray_Function::isEmail($userName)) {
                    require_once 'Dao/Td/Contact/Contact.php';
                    $u = array(
                        'uniqueid' => Dao_Td_Contact_Contact::getContactId(),
                        'truename' => isset($item['truename']) ? $item['truename'] : substr($userName, 0, strpos($userName, '@')),
                        'email'    => $userName,
                        'username' => $userName
                    );
                } else {
                    if (empty($item['uniqueid'])) {
                        $u = $this->_getAddressBook()->searchUser($orgId, $userName);
                    } else {
                        $u = array(
                            'uniqueid' => $item['uniqueid'],
                            'truename' => $item['truename'],
                            'email'    => isset($item['email']) ? $item['email'] : $item['username'],
                            'username' => $item['username']
                        );
                    }
                }

                if (!$u) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('User in Tudu flow was not exists: ' . $item['username'], Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
                }

                $user = array(
                    'uniqueid' => $u['uniqueid'],
                    'truename' => $u['truename'],
                    'username' => $u['email'],
                    'deptid'   => !empty($u['deptid']) ? $u['deptid'] : '^root'
                );

                if (isset($item['status'])) {
                    $user['status'] = $item['status'];
                }

                $sectionUsers[] = $user;
            }

            $step['section'][] = $sectionUsers;

        // 上级/逐级
        } elseif (is_string($users)) {
            $prevUsers = array();
            // 上一步

            if (empty($step['section'])) {

                if ($stepId == '^head' && isset($tudu)) {

                    $prevUsers = array(array(
                        'uniqueid' => $tudu->target ? $tudu->target : $tudu->uniqueId,
                        'username' => $tudu->targetUserName ? $tudu->targetUserName : $tudu->email,
                        'truename' => $tudu->targetTrueName ? $tudu->targetTrueName : $tudu->poster
                    ));
                } else {

                    // 指定的前
                    reset($this->_steps);
                    do {
                        $item = next($this->_steps);

                        if ($item['stepid'] == $stepId) {
                            break ;
                        }
                    } while (null != $item);

                    $prevStep  = prev($this->_steps);

                    if (false === $prevStep) {
                        $prevUsers = array(array(
                            'uniqueid' => $tudu->target ? $tudu->target : $tudu->uniqueId,
                            'username' => $tudu->targetUserName ? $tudu->targetUserName : $tudu->email,
                            'truename' => $tudu->targetTrueName ? $tudu->targetTrueName : $tudu->poster
                        ));
                    } else {
                        $prevUsers = is_array($prevStep['section']) ? end($prevStep['section']) : array();
                    }
                }
            } else {
                $prevUsers = end($step['section']);
            }

            if (!empty($prevUsers)) {
                foreach ($prevUsers as $u) {
                    $sectionUsers = $this->_getHeigherUsers($u['username'], !empty($u['deptid']) ? $u['deptid'] : null, $step['section'] == '^uppers');
                }
            }

            $step['section'] = array_merge($step['section'], $sectionUsers);
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @return void
     */
    public function removeStepSection($stepId, $sectionIndex)
    {
        $sections = array();
        foreach ($this->_steps as $index => $item) {
            if ($item['stepid'] == $stepId) {
                $sections = array();

                foreach ($item['section'] as $secIdx => $item) {

                    if ($secIdx < $sectionIndex) {
                        $sections[] = $item;
                        continue ;
                    }

                    $users = array();
                    foreach ($item as $idx => $user) {
                        if (!isset($user['status']) || ($user['status'] != 2 && $user['status'] != 4)) {
                            //unset($this->_steps[$index]['section'][$sectionIndex][$idx]);
                            continue ;
                        }
                        $users[] = $user;
                    }

                    if (!empty($users)) {
                        $sections[] = $users;
                    }
                }

                $this->_steps[$index]['section'] = $sections;

                break ;
            }
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @param string $userName
     */
    public function addStepSectionUser($stepId, $sectionIndex, $user)
    {
        foreach ($this->_steps as $index => $item) {
            if ($item['stepid'] == $stepId) {
                if (isset($item['section'][$sectionIndex])) {
                    $users = array();
                    foreach ($item['section'][$sectionIndex] as $u) {
                        if ($u['username'] == $user['username']) {
                            return $this;
                        }
                    }

                    $orgId    = $this->_attrs['orgid'];
                    $userName = isset($user['username']) ? $user['username'] : $user['email'];
                    $trueName = $user['truename'];
                    if (Oray_Function::isEmail($userName)) {
                        require_once 'Dao/Td/Contact/Contact.php';
                        $u = array(
                            'uniqueid' => Dao_Td_Contact_Contact::getContactId(),
                            'truename' => isset($item['truename']) ? $item['truename'] : substr($userName, 0, strpos($userName, '@')),
                            'email'    => $userName,
                            'username' => $userName
                        );
                    } else {
                        if (empty($item['uniqueid'])) {
                            $u = $this->_getAddressBook()->searchUser($orgId, $userName);
                        } else {
                            $u = array(
                                'uniqueid' => $item['uniqueid'],
                                'truename' => $item['truename'],
                                'email'    => isset($item['email']) ? $item['email'] : $item['username'],
                                'username' => $item['username']
                            );
                        }
                    }

                    if (!$u) {
                        require_once 'Model/Tudu/Exception.php';
                        throw new Model_Tudu_Exception('User in Tudu flow was not exists: ' . $item['username'], Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
                    }

                    $item = array(
                        'uniqueid' => $u['uniqueid'],
                        'truename' => $u['truename'],
                        'username' => $u['email'],
                        'deptid'   => !empty($u['deptid']) ? $u['deptid'] : '^root'
                    );

                    if (isset($user['status'])) {
                        $item['status'] = $user['status'];
                    }

                    $this->_steps[$index]['section'][$sectionIndex][] = $item;
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @param string $userName
     * @param array  $params
     */
    public function updateStepSectionUser($stepId, $sectionIndex, $userName, array $params)
    {
        foreach ($this->_steps as $index => $item) {
            if ($item['stepid'] == $stepId) {
                if (isset($item['section'][$sectionIndex])) {
                    foreach ($item['section'][$sectionIndex] as $i => $u) {
                        if ($u['username'] == $userName) {
                            $this->_steps[$index]['section'][$sectionIndex][$i] = array_merge($u, $params);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @param string $userName
     */
    public function removeStepSectionUser($stepId, $sectionIndex, $userName)
    {
        foreach ($this->_steps as $index => $item) {
            if ($item['stepid'] == $stepId) {
                if (isset($item['section'][$sectionIndex])) {
                    $users = array();
                    foreach ($item['section'][$sectionIndex] as $u) {
                        if ($u['username'] == $userName) {
                            continue ;
                        }

                        $users[] = $u;
                    }

                    if (empty($users)) {
                        $this->removeStepSection($stepId, $sectionIndex);
                    } else {
                        $this->_steps[$index]['section'][$sectionIndex] = $users;
                    }
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @return void
     */
    public function cancelStepSection($stepId, $sectionIndex) {
        if (!isset($this->_steps[$stepId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error: step id: "' . $stepId . '" was not exists', Model_Tudu_Exception::SAVE_FAILED);
        }

        if (!isset($this->_steps[$stepId]['section'][$sectionIndex])) {
            return ;
        }

        foreach ($this->_steps[$stepId]['section'][$sectionIndex] as $idx => $user) {
            if (isset($user['status']) && $user['status'] > 1) {
                continue ;
            }

            $this->_steps[$stepId]['section'][$sectionIndex][$idx]['status'] = 4;
        }
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @param array  $users
     */
    public function updateStepSection($stepId, $sectionIndex, $users)
    {
        if (!isset($this->_steps[$stepId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error: step id: "' . $stepId . '" was not exists', Model_Tudu_Exception::SAVE_FAILED);
        }

        if (!isset($this->_steps[$stepId]['section'][$sectionIndex])) {
            return $this->addStepSection($stepId, $users);
        }

        $users       = array();
        $existsUsers = array();
        foreach ($this->_steps[$stepId]['section'][$sectionIndex] as $user) {
            if (isset($user['status']) && $user['status'] > 1) {
                $users[$user['username']] = $user;
                $existsUser[] = $user['username'];
            }
        }

        foreach ($users as $user) {
            if (in_array($user['username'], $existsUser)) {
                continue ;
            }

            $u = $this->_getAddressBook()->searchUser($this->orgId, $user['username']);

            if (!$u) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
            }

            $users[] = array(
                'uniqueid' => $u['uniqueid'],
                'truename' => $u['truename'],
                'username' => $u['email'],
                'deptid'   => !empty($u['deptid']) ? $u['deptid'] : '^root'
            );
        }

        $this->_steps[$stepId]['section'][$sectionIndex] = $users;
    }

    /**
     *
     * @param string $stepId
     * @param int    $sectionIndex
     * @return array
     */
    public function getStepSection($stepId, $sectionIndex = null)
    {
        if (!isset($this->_steps[$stepId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error', Model_Tudu_Exception::SAVE_FAILED);
        }

        $step = $this->_steps[$stepId];
        $currentSection = isset($step['currentSection']) ? $step['currentSection'] : 0;

        if ($currentSection >= count($step['section'])) {
            $currentSection = count($step['section']) - 1;
        }

        if (null === $sectionIndex) {
            $sectionIndex = $currentSection;
        }

        if ($sectionIndex >= count($step['section'])) {
            $sectionIndex = count($step['section']) - 1;
        }

        if (!isset($step['section'][$sectionIndex])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error', Model_Tudu_Exception::SAVE_FAILED);
        }

        return $step['section'][$sectionIndex];
    }

    /**
     *
     * @return int
     */
    public function getStepCount()
    {
        return count($this->_steps);
    }

    /**
     *
     * @return array:
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     *
     * @param string $stepId
     */
    public function getStep($stepId)
    {
        if (!isset($this->_steps[$stepId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error step "' . $stepId . '" not exists', Model_Tudu_Exception::SAVE_FAILED);
        }

        return $this->_steps[$stepId];
    }

    /**
     *
     * @param string $name
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (empty($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $k => $val) {
            if ($k == 'steps') {
                $this->_steps = $val;
                continue ;
            }

            $this->setAttribute($k, $val);
        }

        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed  $value
     * @return Model_Tudu_Extension_Flow
     */
    public function setAttribute($key, $value)
    {
        $key = strtolower($key);

        if ($key == 'steps') {
            $this->_steps = $value;

            return $this;
        }

        $this->_attrs[$key] = $value;

        return $this;
    }

    /**
     *
     * @param string $stepId
     */
    public function flowTo($stepId = null, $section = null)
    {
        if (0 === strpos($stepId, '^')) {
            $this->currentStepId = $stepId;
            return ;
        }

        if (!empty($stepId) && !isset($this->_steps[$stepId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Flow error, step not exists', Model_Tudu_Exception::SAVE_FAILED);
        }

        // 不指定步骤，自动流到下一步
        if (empty($stepId)) {

            $step         = &$this->_steps[$this->currentStepId];
            $sections     = $step['section'];
            $sectionIndex = $step['currentSection'];

            do {

                // 还有下一段
                if (isset($sections[++$sectionIndex])) {

                    $step['currentSection'] = $sectionIndex;

                    $isCancel = true;
                    foreach ($step['section'][$sectionIndex] as &$item) {
                        if (!isset($item['status']) || $item['status'] != 4) {
                            $item['status'] = isset($item['status']) && $item['status'] == 2 ? 2 : 1;
                            $isCancel = false;
                        }
                    }

                    // 当前所有执行/审批人已被取消
                    if ($isCancel) {
                        continue ;
                    }

                    break ;

                // 下一步骤
                } else {
                    $nextStepId = $step['next'];
                    $this->currentStepId = $nextStepId;

                    if (isset($this->_steps[$nextStepId])) {
                        $nextStep = &$this->_steps[$nextStepId];

                        $nextStep['currentSection'] = 0;

                        $isCancel = true;
                        foreach ($nextStep['section'][$nextStep['currentSection']] as &$item) {
                            if (!isset($item['status']) || $item['status'] != 4) {
                                $item['status'] = isset($item['status']) && $item['status'] == 2 ? 2 : 1;
                                $isCancel = false;
                            }
                        }

                        if ($isCancel) {
                            $step = &$this->_steps[$nextStepId];
                            continue ;
                        }
                    }

                    break ;
                }
            } while (true);
        } else {

            $this->currentStepId = $stepId;
            $nextStep            = &$this->_steps[$this->currentStepId];
            $nextStep['currentSection'] = null;

            foreach ($nextStep['section'] as $idx => $sec) {
                $completeUser = 0;
                foreach ($sec as $u) {
                    if (isset($u['status']) && ($u['status'] == 2 || $u['status'] == 4)) {
                        $completeUser ++;
                    }

                    if ($completeUser < count($sec)) {
                        $nextStep['currentSection'] = $idx;
                        break 2;
                    }
                }
            }

            $nextStep['currentSection'] = null != $nextStep['currentSection'] ? $nextStep['currentSection'] : 0;

            if (is_array($nextStep['section'][$nextStep['currentSection']])) {
                foreach ($nextStep['section'][$nextStep['currentSection']] as &$item) {
                    $item['status'] = $nextStep['type'] == 1 && isset($item['status']) && $item['status'] == 2 ? 2 : 1;
                }
            }
        }
    }

    /**
     *
     * @param string $stepId
     */
    public function deleteStep($stepId)
    {
        foreach ($this->_steps as $step) {
            if ($step['stepid'] == $stepId) {
                $delete = $step;
                break ;
            }
        }

        if (null === $delete) {
            return ;
        }

        $steps  = array();
        foreach ($this->_steps as $item) {
            if ($item['stepid'] == $stepId) {
                continue ;
            }

            if ($item['prev'] == $stepId) {
                $item['prev'] = $delete['prev'];
            }

            if ($item['next'] == $stepId) {
                $item['next'] = $delete['next'];
            }

            $steps[$item['stepid']] = $item;
        }

        $this->_steps = $steps;
    }

    /**
     *
     * @return multitype:
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * 获取当前执行人
     */
    public function getCurrentUser()
    {
        $stepId = $this->currentStepId;
        $currentStep = null;
        foreach ($this->_steps as $step) {
            if ($step['stepid'] == $stepId) {
                $currentStep = $step;
                break ;
            }
        }

        $currentSection = $currentStep['currentSection'] >= count($currentStep['section']) ? count($currentStep['section']) - 1 : $currentStep['currentSection'];

        if (!$currentStep || !isset($currentStep['section'][$currentSection])) {
            return array();
        }

        return $currentStep['section'][$currentSection];
    }

    /**
     *
     */
    public function isCurrentUser($uniqueId)
    {
        $users = $this->getCurrentUser();

        foreach ($users as $item) {
            if ($item['uniqueid'] == $uniqueId) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $stepId
     */
    public function isStepComplete($stepId = null)
    {
        if (null === $stepId) {
            $stepId = $this->currentStepId;
        }

        $section = $this->getStepSection($stepId);

        foreach ($section as $user) {
            if (empty($user['status']) || $user['status'] < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param string $uniqueId
     */
    public function review($uniqueId, $agree)
    {
        if (!isset($this->_steps[$this->currentStepId])) {
            return ;
        }
        $currentStep = &$this->_steps[$this->currentStepId];

        if (!isset($currentStep['section'][$currentStep['currentSection']])) {
            return ;
        }

        $statusCode = $agree ? 2 : 3;

        foreach ($currentStep['section'][$currentStep['currentSection']] as $idx => $user) {
            if ($user['uniqueid'] == $uniqueId) {
                $this->_steps[$this->currentStepId]['section'][$currentStep['currentSection']][$idx]['status'] = $statusCode;
            }
        }
    }

    /**
     * 用于执行步骤，完成
     *
     * @param string $uniqueId
     */
    public function complete($uniqueId = null, $stepId = null)
    {
        if (null == $stepId) {
            $stepId = $this->currentStepId;
        }

        if (!isset($this->_steps[$stepId])) {
            return ;
        }
        $currentStep = &$this->_steps[$stepId];

        if (!isset($currentStep['section'][$currentStep['currentSection']])) {
            return ;
        }

        $statusCode = 2;

        foreach ($currentStep['section'][$currentStep['currentSection']] as $idx => $user) {
            if (null === $uniqueId || $user['uniqueid'] == $uniqueId) {
                $this->_steps[$this->currentStepId]['section'][$currentStep['currentSection']][$idx]['status'] = $statusCode;
            }
        }
    }

    /**
     *
     * @param string $stepId
     */
    public function reject($stepId, $userName)
    {
        if (null == $stepId) {
            $stepId = $this->currentStepId;
        }

        if (!isset($this->_steps[$stepId])) {
            return ;
        }
        $currentStep = &$this->_steps[$stepId];

        if (!isset($currentStep['section'][$currentStep['currentSection']])) {
            return ;
        }

        $statusCode = 3;

        foreach ($currentStep['section'][$currentStep['currentSection']] as $idx => $user) {
            if ($user['username'] != $userName) {
                continue ;
            }

            $this->_steps[$this->currentStepId]['section'][$currentStep['currentSection']][$idx]['status'] = $statusCode;
        }

        $this->_steps[$this->currentStepId]['status'] = 3;
    }

    /**
     *
     * @param string $stepId
     */
    public function cancel($stepId = null)
    {
        if (null == $stepId) {
            $stepId = $this->currentStepId;
        }

        if (!isset($this->_steps[$stepId])) {
            return ;
        }
        $currentStep = &$this->_steps[$stepId];

        if (!isset($currentStep['section'][$currentStep['currentSection']])) {
            return ;
        }

        $statusCode = 4;

        foreach ($currentStep['section'][$currentStep['currentSection']] as $idx => $user) {
            $this->_steps[$this->currentStepId]['section'][$currentStep['currentSection']][$idx]['status'] = $statusCode;
        }
    }

    /**
     *
     */
    public function toArray()
    {
        $ret = $this->_attrs;
        $ret['steps']   = $this->_steps;
        $ret['stepnum'] = count($this->_steps);

        return $ret;
    }

    /**
     *
     * @param string $name
     */
    public function __get($name)
    {
        if ($name == 'steps') {
            return $this->_steps;
        }
        return $this->getAttribute($name);
    }

    /**
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        if ($name == 'steps') {
            return $this->_steps = $value;
        }
        $this->setAttribute($name, $value);
    }

    /**
     *
     * @param string $userName
     */
    private function _getDepts($orgId)
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
     *
     * @return Tudu_AddressBook
     */
    private function _getAddressBook()
    {
        if (null === $this->_addressBook) {
            $this->_addressBook = Tudu_AddressBook::getInstance();
        }

        return $this->_addressBook;
    }

    /**
     *
     * @param string  $userName
     * @param string  $orgId
     * @param boolean $isDeep
     * @return array
     */
    private function _getHeigherUsers($userName, $deptId = null, $isDeep = false)
    {
        list($userId, $orgId) = explode('@', $userName);

        if (null === $deptId) {
            $user = $this->_getAddressBook()->searchUser($orgId, $userName);

            if (null === $user) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
            }

            $deptId = $user['deptid'];
        }

        if (empty($deptId)) {
            $deptId = '^root';
        }

        $depts = $this->_getDepts($orgId);

        if (empty($depts[$deptId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
        }

        $dept = $depts[$deptId];

        if (empty($dept['moderators'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
        }

        $ret = array();
        $sec = array();
        // 是当前部门负责人
        if (in_array($userId, $dept['moderators']) && $deptId != '^root' && $deptId !== NULL) {
            $dept = $depts[$dept['parentid']];
        }

        foreach ($dept['moderators'] as $m) {
            $user  = $this->_getAddressBook()->searchUser($orgId, $m . '@' . $orgId);
            if (null == $user) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
            }

            $sec[] = array('uniqueid' => $user['uniqueid'], 'username' => $user['email'], 'truename' => $user['truename'], 'deptid' => $user['deptid']);
        }
        $ret[] = $sec;

        // 递归上级
        if ($isDeep) {
            while (!empty($dept['parentid']) && isset($depts[$dept['parentid']])) {
                $dept = $depts[$dept['parentid']];

                $sec = array();
                foreach ($dept['moderators'] as $m) {
                    $user  = $this->_getAddressBook()->searchUser($orgId, $m . '@' . $orgId);
                    if (null == $user) {
                        require_once 'Model/Tudu/Exception.php';
                        throw new Model_Tudu_Exception('User in Tudu flow was not exists', Model_Tudu_Exception::FLOW_USER_NOT_EXISTS);
                    }

                    $sec[] = array('uniqueid' => $user['uniqueid'], 'username' => $user['email'], 'truename' => $user['truename'], 'deptid' => $user['deptid']);
                }
                $ret[] = $sec;
            }
        }

        return $ret;
    }

    /**
     *
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->_handlerClass;
    }
}