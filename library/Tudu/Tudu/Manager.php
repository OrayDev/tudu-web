<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Manager.php 2590 2012-12-31 10:04:53Z cutecube $
 */

/**
 * 图度管理业务流程封装
 * 本对象中不接管任何创建、发送流程
 * 不接管数据流程意外的操作，权限、参数过滤、状态判断等
 *
 * @category   Tudu
 * @package    Tudu_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Manager
{

    const ACTION_SAVE    = 'save';
    const ACTION_SEND    = 'send';
    const ACTION_FORWARD = 'forward';
    const ACTION_DIVIDE  = 'divide';
    const ACTION_APPLY   = 'apply';
    const ACTION_INVITE  = 'invite';

    /**
     *
     * @var Tudu_Tudu_Manager
     */
    protected static $_instance;

    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     *
     * @var array
     */
    private $_arrDao = array();

    /**
     * 单例模式，隐藏构造函数
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function __construct($db = null)
    {
        if ($db instanceof Zend_Db_Adapter_Abstract) {
            $this->setDb($db);
        }
    }

    /**
     * 获取对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function getInstance($db = null)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($db);
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function newInstance($db = null)
    {
        return new self($db);
    }

    /**
     *
     * @param $db
     */
    public function setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
    }

    /**
     * 获取图度
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return Dao_Td_Tudu_Record_Tudu
     */
    public function getTuduById($tuduId, $uniqueId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu = $daoTudu->getTuduById($uniqueId, $tuduId);

        return $tudu;
    }

    /**
     * 获取多条图度数据（BY 图度ID）
     *
     * @param string $tuduId
     * @return Oray_Dao_Recordset
     */
    public function getTudusByIds($tuduIds)
    {
        $tudus = $this->getTudus(array('tuduids' => $tuduIds));

        return $tudus;
    }

    /**
     * 获取多条图度数据
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getTudus(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudus = $daoTudu->getTudus($condition, $filter, $sort, $maxCount);

        return $tudus;
    }

    /**
     * 获取回复信息
     *
     * @param string $tuduId
     * @param string $postId
     * @return Dao_Td_Tudu_Record_Post
     */
    public function getPostById($tuduId, $postId)
    {
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        $post = $daoPost->getPost(array('tuduid' => $tuduId, 'postid' => $postId));

        return $post;
    }

    /**
     * 获取步骤
     *
     * @param string $tuduId
     * @param string $stepId
     * @return Dao_Td_Tudu_Record_Step
     */
    public function getStep($tuduId, $stepId)
    {
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = $this->getDao('Dao_Td_Tudu_Step');
        $step = $daoStep->getStep(array('tuduid' => $tuduId, 'stepid' => $stepId));

        return $step;
    }

    /**
     * 获取步骤列表
     *
     * @param $tuduId
     */
    public function getSteps($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Step')->getSteps(array('tuduid' => $tuduId));
    }

    /**
     * 计算图度组子图度的个数
     *
     * @param string $tuduId
     * @return int
     */
    public function getChildrenCount($tuduId, $uniqueId = null)
    {
        /* @var $daoTuduGroup Dao_Td_Tudu_Group */
        $daoTuduGroup = $this->getDao('Dao_Td_Tudu_Group');
        $count = $daoTuduGroup->getChildrenCount($tuduId, $uniqueId);

        return $count;
    }

    /**
     * 获取用户
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return array
     */
    public function getUser($tuduId, $uniqueId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $user = $daoTudu->getUser($tuduId, $uniqueId);

        return $user;
    }

    /**
     * 获取相关用户列表
     *
     * @param $tuduId
     */
    public function getTuduUsers($tuduId, $filter = null)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->getUsers($tuduId, $filter);
    }

    /**
     * 获取执行人（会议参与人）
     *
     * @param $tuduId
     */
    public function getTuduAccepters($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);
    }

    /**
     * 删除用户
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return boolean
     */
    public function deleteUser($tuduId, $uniqueId)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->deleteUser($tuduId, $uniqueId);
    }

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
     * 创建联系人
     *
     * @param $params
     */
    public function createContact($uniqueId, $email, $name)
    {
        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = $this->getDao('Dao_Td_Contact_Contact');
        $contactId = $daoContact->createContact(array(
            'contactid' => Dao_Td_Contact_Contact::getContactId(),
            'uniqueid'  => $uniqueId,
            'truename'  => $name,
            'email'     => $email
        ));

        return $contactId;
    }

    /**
     * 更新节点信息
     *
     * @param string $tuduId
     * @param array $params
     * @return boolean
     */
    public function updateNode($tuduId, array $params)
    {
        /* @var $daoTuduGroup Dao_Td_Tudu_Group */
        $daoTuduGroup = $this->getDao('Dao_Td_Tudu_Group');
        $ret = $daoTuduGroup->updateNode($tuduId, $params);

        return $ret;
    }

    /**
     * 更新图度数据
     *
     * @param string $tuduId
     * @param array $params
     * @return boolean
     */
    public function updateTudu($tuduId, array $params)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->updateTudu($tuduId, $params);

        return $ret;
    }

    /**
     * 工作流认领图度
     *
     * @param Dao_Td_Tudu_Record_Tudu $tudu
     * @param string $orgId
     * @param string $uniqueId
     */
    public function flowClaimTudu($tudu, $orgId, $uniqueId)
    {
        if (null === $tudu) {
            return false;
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = $this->getDao('Dao_Td_Tudu_Step');

        // 取得流步骤
        $steps = $daoStep->getSteps(array('tuduid' => $tudu->tuduId))->toArray('stepid');
        // 当前认领步骤
        $currentStep    = $steps[$tudu->stepId];
        $orderNum       = $currentStep['ordernum'];// 当前认领步骤排序好号
        $claimAccepter  = array(); // 记录认领图度的用户信息
        $removeAccepter = array(); // 记录非认领图度的用户信息
        $accepters      = $daoTudu->getAccepters($tudu->tuduId); // 获取图度接收人
        $tuduStepNum    = $tudu->stepNum;
        $nextStepId     = $currentStep['nextstepid'];
        $updateOrder    = true;

        foreach ($accepters as $accepter) {
            if ($uniqueId == $accepter['uniqueid']) {
                $claimAccepter = $accepter;
            } else {
                $removeAccepter[] = $accepter;
            }
        }

        if (strstr($currentStep['nextstepid'], 'ST-')) {
            $nextStepId = $steps[$currentStep['nextstepid']]['nextstepid'];
            $daoStep->deleteStep($tudu->tuduId, $currentStep['nextstepid']);
            $tuduStepNum = $tuduStepNum - 1;
            $updateOrder = false;
        }

        // 增加工作流程记录
        $stepId = Dao_Td_Tudu_Step::getStepId();

        $ret = $daoStep->createStep(array(
            'orgid'      => $orgId,
            'tuduid'     => $tudu->tuduId,
            'uniqueid'   => $uniqueId,
            'stepid'     => $stepId,
            'prevstepid' => $currentStep['prevstepid'],
            'nextstepid' => $nextStepId,
            'type'       => Dao_Td_Tudu_Step::TYPE_EXECUTE,
            'iscurrent'  => true,
            'isshow'     => false,
            'ordernum'   => $orderNum + 1,
            'createtime' => time()
        ));
        if (!$ret) {
            return false;
        }
        // 更新认领步骤的nextstepid
        $daoStep->updateStep($tudu->tuduId, $currentStep['stepid'], array('nextstepid' => $stepId));
        // 更新流步骤排序
        foreach ($steps as $key => $step) {
            if ($step['ordernum'] > $orderNum && $updateOrder) {
                $daoStep->updateStep($tudu->tuduId, $step['stepid'], array('ordernum' => $step['ordernum'] + 1));
            }
        }

        // 步骤添加认领人信息
        $daoStep->addUser(array(
            'tuduid' => $tudu->tuduId,
            'stepid' => $stepId,
            'uniqueid' => $uniqueId,
            'userinfo' => $claimAccepter['accepterinfo'],
            'processindex' => 1,
            'status' => 0
        ));

        // 更新图度to字段
        $ret = $daoTudu->updateTudu($tudu->tuduId, array(
            'to' => $claimAccepter['accepterinfo'],
            'stepid' => $stepId,
            'stepnum' => $tuduStepNum + 1,
            'acceptmode' => 0
        ));
        if (!$ret) {
            return false;
        }

        $params = array(
            'accepttime' => time(),
            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
        );

        // 更新认领人 td_tudu_user
        $ret = $daoTudu->updateTuduUser($tudu->tuduId, $uniqueId, $params);
        if (!$ret) {
            return false;
        }

        // 更新图度的接受时间
        $daoTudu->updateLastAcceptTime($tudu->tuduId);

        // 更新需要移除的执行人及去除“我执行”标签
        if (!empty($removeAccepter)) {
            foreach ($removeAccepter as $rmAccept) {
                if (!$daoTudu->updateTuduUser($tudu->tuduId, $rmAccept['uniqueid'], array('role' => null))) {
                    continue ;
                }
                if (!$daoTudu->deleteLabel($tudu->tuduId, $rmAccept['uniqueid'], '^a')) {
                    continue ;
                }
            }
        }

        return true;
    }

    /**
     * 认领图度
     *
     * @param string $tuduId
     */
    public function claimTudu($tuduId, $orgId, $uniqueId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = $this->getDao('Dao_Td_Tudu_Step');

        // 获取图度信息
        $tudu = $this->getTuduById($tuduId, $uniqueId);
        if ($tudu->flowId) {
            return $this->flowClaimTudu($tudu, $orgId, $uniqueId);
        }

        // 获取步骤节点信息
        $step = $this->getStep($tuduId, $tudu->stepId);

        $claimAccepter = array(); // 记录认领图度的用户信息
        $removeAccepter = array(); // 记录非认领图度的用户信息
        $accepters = $daoTudu->getAccepters($tuduId);

        foreach ($accepters as $accepter) {
            if ($uniqueId == $accepter['uniqueid']) {
                $claimAccepter = $accepter;
            } else {
                $removeAccepter[] = $accepter;
            }
        }

        // 增加工作流程记录
        $stepId = Dao_Td_Tudu_Step::getStepId();
        $daoStep->addStep($tuduId, $stepId);

        $ret = $daoStep->createStep(array(
            'orgid'      => $orgId,
            'tuduid'     => $tuduId,
            'uniqueid'   => $uniqueId,
            'stepid'     => $stepId,
            'prevstepid' => $tudu->stepId,
            'nextstepid' => '^end',
            'type'       => Dao_Td_Tudu_Step::TYPE_EXECUTE,
            'iscurrent'  => true,
            'isshow'     => false,
            'ordernum'   => $step->orderNum + 1,
            'createtime' => time()
        ));
        if (!$ret) {
             return false;
        }

        $daoStep->addUser(array(
            'tuduid' => $tuduId,
            'stepid' => $stepId,
            'uniqueid' => $uniqueId,
            'userinfo' => $claimAccepter['accepterinfo'],
            'processindex' => 1,
            'status' => 0
        ));

        // 更新图度to字段
        $ret = $daoTudu->updateTudu($tuduId, array(
            'to' => $claimAccepter['accepterinfo'],
            'stepid' => $stepId,
            'stepnum' => $step->orderNum + 1,
            'acceptmode' => 0
        ));
        if (!$ret) {
             return false;
        }

        $params = array(
            'accepttime' => time(),
            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
        );
        // 更新认领人 td_tudu_user
        $ret = $daoTudu->updateTuduUser($tuduId, $uniqueId, $params);

        if (!$ret) {
             return false;
        }

        // 更新图度的接受时间
        $daoTudu->updateLastAcceptTime($tuduId);

        // 更新需要移除的执行人及去除“我执行”标签
        if (!empty($removeAccepter)) {
            foreach ($removeAccepter as $rmAccept) {
                if (!$daoTudu->updateTuduUser($tuduId, $rmAccept['uniqueid'], array('role' => null))) {
                    continue ;
                }
                if (!$daoTudu->deleteLabel($tuduId, $rmAccept['uniqueid'], '^a')) {
                    continue ;
                }
            }
        }

        return true;
    }

    /**
     * 接受任务
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return boolean
     */
    public function acceptTudu($tuduId, $uniqueId, $percent)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $params = array(
            'accepttime' => time(),
            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
        );

        // 更新用户图度信息
        $ret = $daoTudu->updateTuduUser($tuduId, $uniqueId, $params);
        if (!$ret) {
            return false;
        }
        // 更新最后接受时间
        $daoTudu->updateLastAcceptTime($tuduId);

        return $ret;
    }

    /**
     * 拒绝任务
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return boolean
     */
    public function rejectTudu($tuduId, $uniqueId, $isFlow = false)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tuduStatus = $daoTudu->rejectTudu($tuduId, $uniqueId, $isFlow);

        return $tuduStatus;
    }

    /**
     * 删除图度
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return boolean
     */
    public function deleteTudu($tuduId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->deleteTudu($tuduId);

        return $ret;
    }

    /**
     * 删除回复信息
     *
     * @param string $tuduId
     * @param string $postId
     * @return boolean
     */
    public function deletePost($tuduId, $postId)
    {
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        $ret = $daoPost->deletePost($tuduId, $postId);

        return $ret;
    }

    /**
     * 终止（取消）图度
     *
     * @param string  $tuduId
     * @param boolean $isDone
     * @param int     $score
     * @return boolean
     */
    public function cancelTudu($tuduId, $isDone, $score, $parentId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        // 更新图度状态为：已终止
        // 图度为完结的
        $ret = $this->doneTudu($tuduId, $isDone, $score, true, ($parentId != null));
        if (!$ret) {
            return false;
        }

        // 计算父级图度的进度
        if ($parentId) {
            $this->calParentsProgress($parentId);
        }

        return true;
    }

    /**
     * 完成图度
     *
     * @param string  $tuduId
     * @param boolean $isDone
     * @param int      $score
     * @param boolean $isCancel 是否终止(取消)图度
     * @param boolean $isChild 是否图度组里的图度
     * @return boolean
     */
    public function doneTudu($tuduId, $isDone, $score, $isCancel = false, $isChild = false, $type = 'task')
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $params = array(
            'isdone' => $isDone,
            'score' => $score
        );

        // 如果时终止(取消)图度
        if ($isCancel) {
            $params['accepttime'] = null;                         //清除图度接受时间
            $params['status'] = Dao_Td_Tudu_Tudu::STATUS_CANCEL;  //图度状态为：已终止
        }

        // 更新图度
        $ret = $daoTudu->updateTudu($tuduId, $params);
        if (!$ret) {
            return false;
        }

        // 获取图度关联用户
        $users = $daoTudu->getUsers($tuduId);
        // 标签操作
        foreach ($users as $user) {
           if ($isDone) {
                $this->deleteLabel($tuduId, $user['uniqueid'], '^i');  //移除图度箱标签
                $this->addLabel($tuduId, $user['uniqueid'], '^o');     //添加已完成标签
                $this->deleteLabel($tuduId, $user['uniqueid'], '^a');  //移除我执行标签
                $this->deleteLabel($tuduId, $user['uniqueid'], '^e');  //移除我审批标签
                $this->deleteLabel($tuduId, $user['uniqueid'], '^c');
            } else {
                if (!($isChild && $user['role'] == Dao_Td_Tudu_Tudu::ROLE_SENDER)) {
                    $this->addLabel($tuduId, $user['uniqueid'], '^i');  //添加到图度箱
                }
                if ($user['role'] == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER && $type == 'task') {
                    $this->addLabel($tuduId, $user['uniqueid'], '^a');  //添加到我执行
                }
                $this->deleteLabel($tuduId, $user['uniqueid'], '^o');   //移除已完成标签
            }
        }

        return true;
    }

    /**
     * 关闭图度（同done，但不添加已完成标签）
     *
     * @param string  $tuduId
     * @param boolean $isClose
     * @return boolean
     */
    public function closeTudu($tuduId, $isClose)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $params = array('isdone' => $isClose);

        // 重开图度（讨论）
        if (!$isClose) {
            $params['lastposttime'] = time(); // 更新最后回复时间
        }

        // 更新图度
        $ret = $daoTudu->updateTudu($tuduId, $params);
        if (!$ret) {
            return false;
        }

        // 获取图度关联用户
        $users = $daoTudu->getUsers($tuduId);

        // 移除图度箱标签，添加已完成
        $func = $isClose ? 'deleteLabel' : 'addLabel';
        foreach ($users as $user) {
            $this->{$func}($tuduId, $user['uniqueid'], '^i');
        }

        return true;
    }

    /**
     * 图度版块间移动
     *
     * @param string $tuduId
     * @param string $boardId
     * @param string $classId
     * @return boolean
     */
    public function moveTudu($tuduId, $boardId, $classId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->moveTudu($tuduId, $boardId, $classId);

        return $ret;
    }

    /**
     * 创建回复
     *
     * @param array $params
     * @return boolean
     */
    public function createPost(array $params)
    {
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        $postId = $daoPost->createPost($params);

        return $postId;
    }

    /**
     * 发送回复
     *
     * @param string $tuduId
     * @param string $postId
     * @return boolean
     */
    public function sendPost($tuduId, $postId)
    {
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        return $daoPost->sendPost($tuduId, $postId);
    }

    /**
     *
     * @param $tuduId
     * @param $uniqueId
     * @param $percent
     */
    public function updateProgress($tuduId, $uniqueId, $percent = null)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->updateProgress($tuduId, $uniqueId, $percent);
    }

    /**
     * 更新工作流进度
     * @param $tuduId
     * @param $uniqueId
     * @param $percent
     * @return int
     */
    public function updateFlowProgress($tuduId, $uniqueId, $stepId, $percent = null, &$flowPercent = null)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->updateFlowProgress($tuduId, $uniqueId, $stepId, $percent, $flowPercent);
    }

    /**
     * 计算总耗时
     *
     * @param $tuduId
     */
    public function calcElapsedTime($tuduId)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->calcElapsedTime($tuduId);
    }

    /**
     * 计算父级图度的进度
     *
     * @param $tuduId
     * @return boolean
     */
    public function calParentsProgress($tuduId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->calParentsProgress($tuduId);

        return $ret;
    }

    /**
     * 统计标签图度数
     *
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function calculateLabel($uniqueId, $labelId)
    {
        /* @var $daoLabel Dao_Td_Tudu_Label */
        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
        $ret = $daoLabel->calculateLabel($uniqueId, $labelId);

        return $ret;
    }

    /**
     * 增加图度标签
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function addLabel($tuduId, $uniqueId, $labelId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->addLabel($tuduId, $uniqueId, $labelId);

        return $ret;
    }

    /**
     * 删除图度标签
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function deleteLabel($tuduId, $uniqueId, $labelId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->deleteLabel($tuduId, $uniqueId, $labelId);

        return $ret;
    }

    /**
     * 增加收图人
     *
     * 增加关联用户并投递到 ^all 标签，图度投递到用户的图度箱时，仅增加 ^all 标签
     * 执行发送时才根据策略附加其它的标签或丢弃
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param array $params
     * @return boolean
     */
    public function addRecipient($tuduId, $uniqueId, $params = null)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        return $daoTudu->addUser($tuduId, $uniqueId, $params);
    }

    /**
     * 设置图度关联用户已读/未读状态
     *
     * @param string  $tuduId
     * @param string  $uniqueId
     * @param boolean $isRead
     * @return boolean
     */
    public function markRead($tuduId, $uniqueId, $isRead = true)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->markRead($tuduId, $uniqueId, $isRead);

        return $ret;
    }

    /**
     * 设置标签下所有图度为已读
     * 执行后必须重新统计标签的未读数
     *
     * @param string  $tuduId
     * @param string  $uniqueId
     * @param boolean $isRead
     * @return boolean
     */
    public function markLabelRead($tuduId, $uniqueId, $isRead = true)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $ret = $daoTudu->markLabelRead($tuduId, $uniqueId, $isRead);

        return $ret;
    }

    /**
     * 标记所有为未读
     *
     * @param string  $tuduId
     */
    public function markAllUnRead($tuduId)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        return $daoTudu->markAllUnRead($tuduId);
    }

    /**
     * 标记为转发
     *
     * @param $tuduId
     * @param $uniqueId
     */
    public function markForward($tuduId, $uniqueId)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->markForward($tuduId, $uniqueId);
    }

    /**
     *
     * @param $className
     * @return Oray_Dao_Abstract
     */
    private function getDao($className)
    {
        return Tudu_Dao_Manager::getDao($className, Tudu_Dao_Manager::DB_TS);
    }
}