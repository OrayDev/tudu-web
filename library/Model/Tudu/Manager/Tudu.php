<?php
/**
 * Model Tudu Manager Tudu
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Tudu.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * @see Tudu_User
 */
require_once 'Tudu/User.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 */
class Model_Tudu_Manager_Tudu extends Model_Abstract
{
    const CODE_INVALID_TUDUID       = 101;
    const CODE_INVALID_UNIQUEID     = 102;
    const CODE_INVALID_USERID       = 103;
    const CODE_INVALID_FROM_BOARDID = 104;
    const CODE_INVALID_BOARDID      = 105;
    const CODE_SAVE_FAILED          = 106;
    const CODE_INVALID_ORGID        = 107;
    const CODE_FROM_BOARD_NOTEXISTS = 108;
    const CODE_BOARD_NOTEXISTS      = 109;

    const CODE_DENY_ROLE        = 110;
    const CODE_SAME_BOARDID     = 111;
    const CODE_UNKNOW_ACTION    = 112;
    const CODE_INVALID_LABELID  = 113;
    const CODE_INVALID_USERNAME = 114;
    const CODE_TUDU_NOTEXISTS   = 115;

    const CODE_TUDU_CLOSED    = 116;
    const CODE_TUDU_DONE      = 117;
    const CODE_INVALID_POSTID = 118;
    const CODE_POST_NOTEXISTS = 119;
    const CODE_POST_FIRST     = 120;
    const CODE_STEP_NOTCLAIM  = 121;

    const CODE_STEP_CLAIM_FINISH      = 122;
    const CODE_DELETE_TUDUGROUP_CHILD = 123;

    const CODE_INVALID_VOTEID      = 133;
    const CODE_MISSING_VOTE_OPTION = 134;
    const CODE_INVALID_VOTE        = 135;
    const CODE_MORE_VOTE_OPTION    = 136;
    const CODE_MISSING_USERINFO    = 137;
    const CODE_MISSING_POST_PARAMS = 138;

    /**
     *
     * @var array
     */
    protected $_labels;

    /**
     *
     * @var Tudu_User
     */
    protected $_user = null;

    /**
     *
     * @var array
     */
    protected $_sysLabel = array(
        'inbox'     => '^i',
        'drafts'    => '^r',
        'todo'      => '^a',
        'starred'   => '^t',
        'sent'      => '^f',
        'forwarded' => '^w',
        'ignore'    => '^g',
        'notice'    => '^n',
        'discuss'   => '^d',
        'meeting'   => '^m',
        'review'    => '^e',
        'reviewed'  => '^v',
        'done'      => '^o',
        'all'       => '^all'
    );

    /**
     *
     */
    public function __construct()
    {
        /* @var $user Tudu_User */
        $this->_user = Tudu_User::getInstance();

        // 缺少身份认证的用户
        if (!$this->_user->isLogined()) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Invalid user to execute current operation', Model_Tudu_Exception::INVALID_USER);
        }

        $this->_user->userInfo = $this->_user->userName . ' ' . $this->_user->trueName;
    }

    /**
     * 移动图度
     */
    public function move(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        if (empty($params['fbid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "fbid"', self::CODE_INVALID_FROM_BOARDID);
        }

        if (empty($params['bid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "bid"', self::CODE_INVALID_BOARDID);
        }

        $uniqueId    = $this->_user->uniqueId;
        $tuduIds     = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $fromBoardId = $params['fbid'];
        $boardId     = $params['bid'];
        $orgId       = $this->_user->orgId;
        $userId      = $this->_user->userId;
        $classId     = isset($params['classid']) ? $params['classid'] : null;
        /* @var $manager Tudu_Tudu_Manager */
        $manager     = $this->getManager();

        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

        // 源板块是否存在
        $fromBoard = $daoBoard->getBoard(array('orgid' => $orgId, 'boardid' => $fromBoardId));
        if (!$fromBoard) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('From board is not exists', self::CODE_FROM_BOARD_NOTEXISTS);
        }

        // 模板板块
        $board = $daoBoard->getBoard(array('orgid' => $orgId, 'boardid' => $boardId));
        if (!$board) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Board is not exists', self::CODE_BOARD_NOTEXISTS);
        }

        $fromBoard   = $fromBoard->toArray();
        $isModerator = array_key_exists($userId, $fromBoard['moderators']);
        $isSuperModerator = false;

        if ($fromBoard['parentid']) {
            $fromZone = $daoBoard->getBoard(array('orgid' => $orgId, 'boardid' => $fromBoard['parentid']));
            $isSuperModerator = array_key_exists($userId, $fromZone->moderators);
        }

        // 操作人必须同时为源版块和目标版块的版主
        if (!$isModerator && !$isSuperModerator) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('It is not enough role', self::CODE_DENY_ROLE);
        }

        // 来源版块和目标版块不能是同一版块
        if ($boardId == $fromBoardId) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('It is same of Board', self::CODE_SAME_BOARDID);
        }

        $success = 0;
        foreach ($tuduIds as $tuduId) {
            $tudu = $manager->getTuduById($tuduId, $uniqueId);

            if (null === $tudu || $tudu->boardId != $fromBoardId) {
                continue ;
            }

            if ($manager->moveTudu($tuduId, $boardId, $classId)) {
                $success++;
            }
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Move tudu failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 标签
     */
    public function label(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        if (empty($params['fun'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Unknow operate function', self::CODE_UNKNOW_ACTION);
        }

        $uniqueId  = $this->_user->uniqueId;
        $action    = $params['fun'];
        $tuduIds   = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $manager   = $this->getManager();

        switch ($action) {
            // 增加标签
            case 'add':
                if (empty($params['labelid'])) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Missing or invalid value of parameter "labelid"', self::CODE_INVALID_LABELID);
                }
                $labelIds  = !is_array($params['labelid']) ? (array) $params['labelid'] : $params['labelid'];

                foreach ($tuduIds as $tuduId) {
                    foreach ($labelIds as $labelId) {
                        $manager->addLabel($tuduId, $uniqueId, $labelId);
                    }
                }
                break;

            // 删除标签
            case 'del':
                if (empty($params['labelid'])) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Missing or invalid value of parameter "labelid"', self::CODE_INVALID_LABELID);
                }
                $labelIds  = !is_array($params['labelid']) ? (array) $params['labelid'] : $params['labelid'];

                foreach ($tuduIds as $tuduId) {
                    foreach ($labelIds as $labelId) {
                        $manager->deleteLabel($tuduId, $uniqueId, $labelId);
                    }
                }
                break;

            // 移除所有
            case 'remove':
                foreach ($tuduIds as $tuduId) {
                    $tudu = $manager->getTuduById($tuduId, $uniqueId);

                     foreach ($tudu->labels as $labelId) {
                         if (strpos($labelId, '^') === false) {
                             $manager->deleteLabel($tuduId, $uniqueId, $labelId);
                         }
                     }
                }
                break;

            default:
                break;
        }
    }

    /**
     * 标记为
     * 已读、未读
     */
    public function mark(array $params)
    {
        if (empty($params['fun'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Unknow operate function', self::CODE_UNKNOW_ACTION);
        }

        $uniqueId  = $this->_user->uniqueId;
        $action    = $params['fun'];
        $manager   = $this->getManager();

        switch ($action) {
            case 'read':
            case 'unread':
                if (empty($params['tuduid'])) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
                }
                $tuduIds   = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];

                foreach ($tuduIds as $tuduId) {
                    $manager->markRead($tuduId, $uniqueId, ('read' == $action));
                }

                break;
            case 'allread':
                if (empty($params['labelid'])) {
                    require_once 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Missing or invalid value of parameter "labelid"', self::CODE_INVALID_LABELID);
                }
                $labelIds  = !is_array($params['labelid']) ? (array) $params['labelid'] : $params['labelid'];

                // 设置标签下所有图度为已读
                foreach ($labelIds as $labelId) {
                    $manager->markLabelRead($labelId, $uniqueId);
                }

                $labels = $this->getLabels($uniqueId);

                // 统计标签图度数
                foreach ($labels as $label) {
                    $manager->calculateLabel($uniqueId, $label['labelid']);
                }

                break;
            default:
                break;
        }
    }

    /**
     * 星标关注
     */
    public function star(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        if (empty($params['fun'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Unknow operate function', self::CODE_UNKNOW_ACTION);
        }

        $uniqueId  = $this->_user->uniqueId;
        $action    = $params['fun'];
        $tuduIds   = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $manager   = $this->getManager();
        $starred   = $this->_sysLabel['starred'];
        $success   = 0;

        switch ($action) {
            // 添加星标
            case 'star':
                /* @var $daoTudu Dao_Td_Tudu_Tudu */
                $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

                foreach ($tuduIds as $tuduId) {
                    $tudu = $manager->getTuduById($tuduId, $uniqueId);
                    if ($tudu) {
                        if ($tudu->uniqueId != $uniqueId) {
                            $daoTudu->addUser($tuduId, $uniqueId);
                        }
                        $manager->addLabel($tuduId, $uniqueId, $starred);
                        $success ++;
                    }
                }
                break ;

            // 取消星标
            case 'unstar':
                foreach ($tuduIds as $tuduId) {
                    $manager->deleteLabel($tuduId, $uniqueId, $starred);
                    $success ++;
                }
                break ;

            default:
                break ;
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Move tudu failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 忽略图度
     */
    public function ignore(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $uniqueId  = $this->_user->uniqueId;
        $tuduIds   = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $manager   = $this->getManager();
        $action    = isset($params['fun']) ? $params['fun'] : null;
        $success   = 0;

        switch ($action) {
            // 移除忽略标签
            case 'remove':
                foreach ($tuduIds as $tuduId) {
                    $manager->deleteLabel($tuduId, $uniqueId, $this->_sysLabel['ignore']);  //删除忽略标签
                    $success ++;
                }

                break;
            // 添加忽略标签
            default:
                foreach ($tuduIds as $tuduId) {
                    $manager->deleteLabel($tuduId, $uniqueId, $this->_sysLabel['inbox']);  //移除图度箱标签
                    $manager->deleteLabel($tuduId, $uniqueId, $this->_sysLabel['todo']);   //移除我执行标签
                    $manager->addLabel($tuduId, $uniqueId, $this->_sysLabel['ignore']);    //添加忽略标签
                    $success ++;
                }

                break;
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Add ignore label or delete ignore label failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 添加到图度箱
     */
    public function inbox(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $uniqueId  = $this->_user->uniqueId;
        $userName  = $this->_user->userName;
        $tuduIds   = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $manager   = $this->getManager();
        $success   = 0;

        foreach ($tuduIds as $tuduId) {
            // 获得图度信息
            $tudu = $manager->getTuduById($tuduId, $uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue ;
            }

            $manager->deleteLabel($tuduId, $uniqueId, $this->_sysLabel['ignore']); //删除忽略标签
            $manager->addLabel($tuduId, $uniqueId, $this->_sysLabel['all']);       //添加所有图度标签
            $manager->addLabel($tuduId, $uniqueId, $this->_sysLabel['inbox']);     //添加到图度箱

            // 如果是图度任务，且操作人系图度执行人，添加到“我执行”
            if ($tudu->type == 'task' && in_array($userName, $tudu->accepter, true)) {
                $manager->addLabel($tuduId, $uniqueId, $this->_sysLabel['todo']); //添加到我执行
            }

            $success ++;
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Append to inbox failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 接受任务
     */
    public function accept(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $tuduIds  = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $manager  = $this->getManager();
        $success  = 0;  //用于计数操作成功个数

        foreach ($tuduIds as $tuduId) {
            // 获得图度数据
            $tudu = $manager->getTuduById($tuduId, $uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue;
            }
            // 图度不能是已确定状态
            if ($tudu->isDone) {
                continue;
            }
            // 图度不能是“已完成”，“已拒绝”, “已取消”状态
            if ($tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DOING) {
                continue;
            }
            // 操作人必须为图度执行人
            if (!in_array($userName, $tudu->accepter)) {
                continue;
            }

            $ret = $manager->acceptTudu($tuduId, $uniqueId, (int) $tudu->selfPercent);

            if ($ret) {
                // 更新任务进度
                $manager->updateProgress($tuduId, $uniqueId, $tudu->selfPercent);

                // 计算图度已耗时
                $manager->calcElapsedTime($tuduId);

                $success ++; //记录次数

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_ACCEPT,
                    array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId, $this->_user->userInfo),
                    array('accepttime' => time(), 'status' => Dao_Td_Tudu_Tudu::STATUS_DOING)
                );
            }
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Accept tudu failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 拒绝任务
     */
    public function reject(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $tuduIds  = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $manager  = $this->getManager();
        $success  = 0;  //用于计数操作成功个数

        foreach ($tuduIds as $tuduId) {
            // 获得图度数据
            $tudu = $manager->getTuduById($tuduId, $uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue ;
            }
            // 图度不能是已确定状态
            if ($tudu->isDone) {
                continue ;
            }
            // 图度不能是“已完成”，“已拒绝”, “已取消”状态
            if ($tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DOING) {
                continue ;
            }
            // 操作人必须为图度执行人
            if (!in_array($userName, $tudu->accepter)) {
                continue ;
            }

            $tuduStatus = $manager->rejectTudu($tuduId, $uniqueId);
            if (false !== $tuduStatus) {
                $success ++; //记录次数

                // 拒绝后任务状态为完成的，生成周期任务
                if ($tudu->cycleId && $tuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $manager->updateTudu($tuduId, array('cycleid' => null));

                    if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
                        $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

                        if ($config['httpsqs']) {
                            $options = $config['httpsqs'];
                            $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                            $data = implode(' ', array(
                                'tudu',
                                'cycle',
                                '',
                                http_build_query(array(
                                    'tuduid'  => $tudu->tuduId,
                                    'tsid'    => $this->_user->tsId,
                                    'cycleid' => $tudu->cycleId
                                ))
                            ));
                            $httpsqs->put($data, 'tudu');
                        }
                    }
                }

                if ($tudu->parentId) {
                    $manager->calParentsProgress($tudu->parentId);
                }

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_DECLINE,
                    array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId, $this->_user->userInfo),
                    array('selfstatus' => Dao_Td_Tudu_Tudu::STATUS_REJECT, 'status' => $tuduStatus)
                );
            }
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Reject tudu failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 终止（取消）任务
     *
     * 必传参数：
     * tuduid|uniqueid|username|orgid|userinfo|tsid|server
     * server当图度是会议的时候必传
     */
    public function cancel(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $tuduId    = $params['tuduid'];
        $manager   = $this->getManager();

        $tudu = $manager->getTuduById($tuduId, $uniqueId);
        // 图度必须存在
        if (null == $tudu) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu is not exists', self::CODE_TUDU_NOTEXISTS);
        }
        // 图度不能是已确定状态
        if ($tudu->isDone) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu is done', self::CODE_TUDU_DONE);
        }
        // 操作人必须为图度发起人
        if ($tudu->sender != $userName) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('It is not enough role', self::CODE_DENY_ROLE);
        }

        // 执行终止（取消）操作
        $ret = $manager->cancelTudu($tuduId, true, '', $tudu->parentId);
        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Close tudu failed', self::CODE_SAVE_FAILED);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $tuduId,
            Dao_Td_Log_Log::ACTION_TUDU_CANCEL,
            array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId, 'userinfo' => $this->_user->userInfo),
            array('accepttime' => null, 'status' => Dao_Td_Tudu_Tudu::STATUS_CANCEL, 'isdone' => true, 'score' => '')
        );

        // 发送通知,插入消息队列
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    'send',
                    'tudu',
                    '',
                    http_build_query(array(
                        'tsid'     => $this->_user->tsId,
                        'tuduid'   => $tuduId,
                        'uniqueid' => $uniqueId,
                        'to'       => '',
                        'act'      => 'cancel'
                    ))
                ));

                $httpsqs->put($data, 'send');

                if ($tudu->type == 'meeting') {
                    $tpl = <<<HTML
<strong>会议已取消</strong><br />
<a href="http://{$this->_user->domainName}/frame#m=view&tid=%s&page=1" target="_blank">%s</a><br />
发起人：{$tudu->from[0]}<br />
%s
HTML;
                    $data = implode(' ', array(
                        'tudu',
                        'cancel',
                        '',
                        http_build_query(array(
                            'tuduid'  => $tuduId,
                            'from'    => $tudu->from[3],
                            'to'      => implode(',', $tudu->accepter),
                            'content' => sprintf($tpl, $tuduId, $tudu->subject, date('Y-m-d H:i:s', time()), mb_substr(strip_tags($tudu->content), 0, 20, 'utf-8'))
                        ))
                    ));

                    $httpsqs->put($data, 'im');
                }
            }
        }
    }

    /**
     * 关闭/重开图度（讨论）
     *
     * 必传参数：
     * tuduid|uniqueid|username|orgid|userinfo
     * isclose不传值为false
     */
    public function close(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $tuduId    = $params['tuduid'];
        $isClose   = !empty($params['isclose']) ? true : false;
        $manager   = $this->getManager();

        $tudu = $manager->getTuduById($tuduId, $uniqueId);
        // 图度必须存在
        if ($tudu === null) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu is not exists', self::CODE_TUDU_NOTEXISTS);
        }
        // 图度不能是已确定状态
        if ($tudu->isDone && $isClose) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu is closed', self::CODE_TUDU_CLOSED);
        }
        // 操作人必须为图度发起人
        if ($tudu->sender != $userName) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('It is not enough role', self::CODE_DENY_ROLE);
        }

        // 执行关闭/重开图度操作
        $ret = $manager->closeTudu($tuduId, $isClose);
        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Close tudu failed', self::CODE_SAVE_FAILED);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $tuduId,
            ($isClose ? Dao_Td_Log_Log::ACTION_CLOSE : Dao_Td_Log_Log::ACTION_OPEN),
            array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId, 'userinfo' => $this->_user->userInfo),
            array('isdone' => $isClose)
        );
    }

    /**
     * 认领
     *
     * 必要参数
     * tuduid|uniqueid|orgid|username|server|postparams|truename
     */
    public function claim(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $orgId    = $this->_user->orgId;
        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $tuduId   = $params['tuduid'];
        //$manager  = $this->getManager();

        /* @var $daoFlow Dao_Td_Tudu_Flow*/
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        /* @var $daoTudu Dao_Td_Tudu_Flow*/
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 获取图度信息
        $tudu = $daoTudu->getTuduById($uniqueId, $tuduId);
        // 判读图度是否存在
        if (null == $tudu) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu is not exists', self::CODE_TUDU_NOTEXISTS);
        }

        // 获取步骤
        $flowRecord = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));
        $step       = isset($flowRecord->steps[$flowRecord->currentStepId]) ? $flowRecord->steps[$flowRecord->currentStepId] : null;

        // 判断当前是否为认领操作
        if (!$step || $step['type'] != Dao_Td_Tudu_Step::TYPE_CLAIM) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu current step is not claim', self::CODE_STEP_NOTCLAIM);
        }

        // 判读图度是否已有user认领
        if ($tudu->acceptTime) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This tudu current step is claimed', self::CODE_STEP_CLAIM_FINISH);
        }

        $accepters = $daoTudu->getAccepters($tuduId);
        foreach ($accepters as $accepter) {
            if ($uniqueId == $accepter['uniqueid']) {
                $claimAccepter = $accepter;
            } else {
                $removeAccepter[] = $accepter;
            }
        }

        require_once 'Model/Tudu/Extension/Flow.php';
        $flow = new Model_Tudu_Extension_Flow();
        $flow->setAttributes($flowRecord->toArray());

        // 插入新的步骤
        $stepId = Dao_Td_Tudu_Step::getStepId();

        $flow->complete($flow->currentStepId, $this->_user->uniqueId);

        if ($tudu->flowId && strstr($step['next'], 'ST-')) {
            $flow->deleteStep($step['next']);
        }

        $flow->addStep(array(
            'stepid' => $stepId,
            'prev'   => $step['stepid'],
            'next'   => $step['next'],
            'type'   => 0
        ));
        $flow->updateStep($step['stepid'], array('next' => $stepId));
        $flow->addStepSection($stepId, array(array(
            'uniqueid' => $this->_user->uniqueId,
            'truename' => $this->_user->trueName,
            'username' => $this->_user->userName,
            'email'    => $this->_user->userName,
            'status'   => 0
        )));
        $flow->flowTo($stepId);

        if (!$daoFlow->updateFlow($flow->tuduId, $flow->toArray())) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu flow save failed', self::CODE_SAVE_FAILED);
        }

        $ret = $daoTudu->updateTudu($tuduId, array(
            'to'         => $claimAccepter['accepterinfo'],
            'stepid'     => $stepId,
            'stepnum'    => count($flow->steps),
            'acceptmode' => 0
        ));

        $params = array(
            'accepttime' => time(),
            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
        );
        // 更新认领人 td_tudu_user
        $ret = $daoTudu->updateTuduUser($tuduId, $uniqueId, $params);

        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Claim tudu failed', self::CODE_SAVE_FAILED);
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

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        $content = sprintf('%s 认领了该图度。', $this->_user->trueName);
        $header  = array(
            'action'       => 'claim',
            'tudu-claimer' => $this->_user->trueName
        );
        $postParams = array(
            'orgid'      => $this->_user->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'uniqueid'   => $this->_user->uniqueId,
            'poster'     => $this->_user->trueName,
            'posterinfo' => $this->_user->position,
            'email'      => $this->_user->userName,
            'postid'     => Dao_Td_Tudu_Post::getPostId($tuduId),
            'header'     => $header,
            'content'    => $content,
            'lastmodify' => implode(chr(9), array($this->_user->uniqueId, time(), $this->_user->trueName))
        );

        $postId = $daoPost->createPost($postParams);
        //发送回复
        $daoPost->sendPost($tuduId, $postId);
        //标记未读
        $daoTudu->markAllUnRead($tuduId);

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $tuduId,
            Dao_Td_Log_Log::ACTION_TUDU_CLAIM,
            array('orgid' => $orgId, 'uniqueid' => $uniqueId, 'userinfo' => $this->_user->userInfo),
            array('claimuser' => $this->_user->trueName, 'claimtime' => time(), 'status' => Dao_Td_Tudu_Tudu::STATUS_DOING)
        );

        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $charset = isset($config['charset']) ? $config['charset'] : 'utf-8';
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $charset, $options['name']);

                $notifyTo = array($tudu->sender);
                $notifyTo = array_merge($notifyTo, array_keys($tudu->to));
                if ($tudu->notifyAll) {
                    $notifyTo = array_merge($notifyTo, array_keys($tudu->cc));
                }

                $tpl = <<<HTML
<strong>您刚收到一个新的回复</strong><br />
<a href="http://{$this->_user->domainName}/frame#m=view&tid=%s&page=1" target="_blank" _tid="{$tuduId}">%s</a><br />
发起人：{$this->_user->trueName}<br />
更新日期：%s<br />
{$content}
HTML;

                $data = implode(' ', array(
                    'tudu',
                    'reply',
                    '',
                    http_build_query(array(
                        'tuduid'  => $tudu->tuduId,
                        'from'    => $userName,
                        'to'      => implode(',', $notifyTo),
                        'content' => sprintf($tpl, $tudu->tuduId, $tudu->subject, date('Y-m-d H:i:s', time()))
                    ))
                ));

                $httpsqs->put($data);
            }
        }
    }

    /**
     * 确认图度
     *
     * 必传参数：
     * tuduid|uniqueid|username|orgid|userinfo|tsid
     * isdone不传值为false
     * score不传值为0
     */
    public function done(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        $uniqueId = $this->_user->uniqueId;
        $userName = $this->_user->userName;
        $tuduIds  = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
        $isDone   = !empty($params['isdone']) ? true : false;
        $score    = !empty($params['score']) ? (int) $params['score'] : 0;
        $manager  = $this->getManager();
        $success  = 0;  //用于计数操作成功个数

        foreach ($tuduIds as $tuduId) {
            $tudu = $manager->getTuduById($tuduId, $uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue ;
            }
            // 图度不能是已确定状态
            if ($tudu->isDone && $isDone) {
                continue ;
            }
            // 操作人必须为图度发起人
            if ($tudu->sender != $userName) {
                continue ;
            }
            // 图度不能是“未开始”，“进行中”等状态
            if (($tudu->type != 'task' || $tudu->status < 2) && $isDone) {
                continue ;
            }

            if (!$isDone) {
                $score = 0;
            }

            // 执行确认/取消确认图度操作
            $ret = $manager->doneTudu($tuduId, $isDone, $score, false, ($tudu->parentId != null), $tudu->type);
            if ($ret) {
                $success ++;

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    ($isDone ? Dao_Td_Log_Log::ACTION_TUDU_DONE : Dao_Td_Log_Log::ACTION_TUDU_UNDONE),
                    array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId, 'userinfo' => $this->_user->userInfo),
                    array('isdone' => $isDone, 'score' => $score)
                );

                // 发送通知,插入消息队列
                if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
                    $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

                    if ($config['httpsqs']) {
                        $options = $config['httpsqs'];
                        $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                        $data = implode(' ', array(
                            'send',
                            'tudu',
                            '',
                            http_build_query(array(
                                'tsid'     => $this->_user->tsId,
                                'tuduid'   => $tuduId,
                                'uniqueid' => $uniqueId,
                                'to'       => '',
                                'act'      => 'confirm'
                            ))
                        ));

                        $httpsqs->put($data, 'send');
                    }
                }
            }
        }

        if ($success <= 0) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Append to inbox failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 投票
     */
    public function vote(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        if (empty($params['voteid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "voteid"', self::CODE_INVALID_VOTEID);
        }

        if (empty($params['option'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing vote option', self::CODE_MISSING_VOTE_OPTION);
        }

        $uniqueId = $this->_user->uniqueId;
        $tuduId   = $params['tuduid'];
        $option   = !is_array($params['option']) ? (array) $params['option'] : $params['option'];
        $voteId   = $params['voteid'];
        $voter    = $this->_user->userInfo;
        $manager  = $this->getManager();

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);
        $vote    = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));

        if (!$vote || (!empty($vote->expireTime) && $vote->expireTime + 86400 < time())) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Invalid vote', self::CODE_INVALID_VOTE);
        }

        if ($vote->maxChoices != 0 && count($option) > $vote->maxChoices) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('So more choices in this vote', self::CODE_MORE_VOTE_OPTION);
        }

        $tudu = $manager->getTuduById($tuduId, $uniqueId);
        if($tudu->isDone) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu is closed', self::CODE_TUDU_DONE);
        }

        $ret = $daoVote->vote($tuduId, $voteId, $option, $uniqueId, $voter);
        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Vote failed', self::CODE_SAVE_FAILED);
        }
    }

    /**
     * 删除图度
     */
//     public function delete(array $params)
//     {
//         if (empty($params['tuduid'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
//         }

//         if (empty($params['orgid'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
//         }

//         if (empty($params['uniqueid'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "uniqueid"', self::CODE_INVALID_UNIQUEID);
//         }

//         if (empty($params['userid'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "userid"', self::CODE_INVALID_USERID);
//         }

//         if (empty($params['username'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "username"', self::CODE_INVALID_USERNAME);
//         }

//         $userName = $params['username'];
//         $orgId    = $params['orgid'];
//         //$tuduIds  = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
//         $uniqueId = $params['uniqueid'];
//         $userId   = $params['userid'];
//         $manager  = $this->getManager();
//         $trueTuduIds = array();  //用于记录删除成功的图度ID

//         /* @var $daoBoard Dao_Td_Board_Board */
//         $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
//         $boards   = $daoBoard->getBoards(array(
//             'orgid'    => $orgId,
//             'uniqueid' => $uniqueId
//         ), null, 'ordernum DESC')->toArray('boardid');

//         // 获得图度数据
//         $tudus  = $manager->getTudusByIds($tuduIds);
//         foreach ($tudus as $tudu) {
//             if (!$boards[$tudu->boardId]) {
//                 continue;
//             }

//             // 版主与超级版主的权限检测
//             $isModerator = array_key_exists($userId, $boards[$tudu->boardId]['moderators']);
//             $isSuperModerator = false;
//             if (!empty($boards[$tudu->boardId]['parentid'])) {
//                 $parentId = $boards[$tudu->boardId]['parentid'];
//                 $isSuperModerator = array_key_exists($userId, $boards[$parentId]['moderators']);
//             }

//             if ($tudu->sender == $userName || $isModerator || $isSuperModerator) {
//                 // 当删除的事图度组的时候，判断图度组下是否有子图度
//                 if ($tudu->isTuduGroup && $manager->getChildrenCount($tudu->tuduId) > 0) {
//                     require_once 'Model/Tudu/Exception.php';
//                     $params['error-subject'] = $tudu->subject;
//                     throw new Model_Tudu_Exception('This tudu is tudu group, the group have some tudu', self::CODE_DELETE_TUDUGROUP_CHILD);
//                 }

//                 // 删除操作
//                 $ret = $manager->deleteTudu($tudu->tuduId);
//                 if ($ret) {
//                     // 记录删除成功的图度ID
//                     $trueTuduIds[] = $tudu->tuduId;

//                     if ($tudu->parentId) {
//                         // 计算父级图度的进度
//                         $manager->calParentsProgress($tudu->parentId);
//                         // 更新节点信息
//                         if ($manager->getChildrenCount($tudu->parentId) <= 0) {
//                             $manager->updateNode($tudu->parentId, array(
//                                'type' => Dao_Td_Tudu_Group::TYPE_LEAF
//                             ));
//                         }
//                     }

//                     // 添加操作日志
//                     if (!empty($params['userinfo'])) {
//                         $this->_writeLog(
//                             Dao_Td_Log_Log::TYPE_TUDU,
//                             $tudu->tuduId,
//                             Dao_Td_Log_Log::ACTION_DELETE,
//                             array('orgid' => $orgId, 'uniqueid' => $uniqueId, 'userinfo' => $params['userinfo'])
//                         );
//                     }
//                 }
//             }
//         }

//         if (!count($trueTuduIds)) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Delete tudu failed', self::CODE_SAVE_FAILED);
//         }

//         $params['del-tuduids'] = $trueTuduIds;
//     }

    /**
     * 删除回复
     */
    public function deletePost(array $params)
    {
        if (empty($params['tuduid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
        }

        if (empty($params['postid'])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "postid"', self::CODE_INVALID_POSTID);
        }

        $orgId    = $this->_user->orgId;
        $tuduId   = $params['tuduid'];
        $postId   = $params['postid'];
        $userId   = $this->_user->userId;
        $uniqueId = $this->_user->uniqueId;
        $manager  = $this->getManager();

        $post = $manager->getPostById($tuduId, $postId);
        // 回复必须存在
        if (!$post) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('This post is not exists', self::CODE_POST_NOTEXISTS);
        }

        // 提交删除回复不能是图度内容
        if ($post->isFirst) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('The post is first in this tudu', self::CODE_POST_FIRST);
        }

        // 非回复者，检查版主权限
        if ($post->uniqueId != $uniqueId) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
            $board = $daoBoard->getBoard(array('orgid' => $orgId, 'boardid' => $post->boardId))->toArray();

            if ($board === null || !array_key_exists($userId, $board['moderators'])) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('It is not enough role', self::CODE_DENY_ROLE);
            }
        }

        $ret = $manager->deletePost($tuduId, $postId);
        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Discard tudu failed', self::CODE_SAVE_FAILED);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_POST,
            $postId,
            Dao_Td_Log_Log::ACTION_DELETE,
            array('orgid' => $orgId, 'uniqueid' => $uniqueId, 'userinfo' => $this->_user->userInfo)
        );
    }

//     /**
//      * 删除草稿
//      */
//     public function discard(array $params)
//     {
//         if (empty($params['tuduid'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "tuduid"', self::CODE_INVALID_TUDUID);
//         }

//         if (empty($params['username'])) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Missing or invalid value of parameter "username"', self::CODE_INVALID_USERNAME);
//         }

//         $userName = $params['username'];
//         $tuduIds  = !is_array($params['tuduid']) ? (array) $params['tuduid'] : $params['tuduid'];
//         $manager  = $this->getManager();
//         $trueTuduIds  = array();  //用于记录删除成功的图度ID

//         // 获得图度数据
//         $tudus = $manager->getTudusByIds($tuduIds)->toArray();

//         foreach ($tudus as $tudu) {
//             // 当前图度必须是草稿，且操作人必须图度的发起人
//             if ($tudu['isdraft'] && strcasecmp($tudu['sender'], $userName) == 0) {
//                 // 当前图度是图度组
//                 if ($tudu['istudugroup']) {
//                     $children = $manager->getTudus(array('parentid' => $tudu['tuduid']))->toArray();

//                     foreach ($children as $child) {
//                          // 执行删除子图度操作
//                          $manager->deleteTudu($child['tuduid']);
//                      }
//                 }

//                 // 执行删除操作
//                 $manager->deleteTudu($tudu['tuduid']);
//                 $trueTuduIds[] = $tudu['tuduid'];
//             }
//         }

//         if (!count($trueTuduIds)) {
//             require_once 'Model/Tudu/Exception.php';
//             throw new Model_Tudu_Exception('Discard tudu failed', self::CODE_SAVE_FAILED);
//         }

//         $params['del-tuduids'] = $trueTuduIds;
//     }

    /**
     * 读取用户标签
     *
     * @param string $uniqueId
     * @return array
     */
    public function getLabels($uniqueId)
    {
        if (null === $this->_labels) {
            /* @var $daoLabel Dao_Td_Tudu_Label */
            $daoLabel = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Label', Tudu_Dao_Manager::DB_TS);
            $this->_labels = $daoLabel->getLabelsByUniqueId($uniqueId, null, 'issystem DESC, ordernum DESC, alias ASC')->toArray();
        }

        return $this->_labels;
    }

    /**
     * @return Tudu_Tudu_Manager
     */
    public function getManager()
    {
        /**
         * @see Tudu_Tudu_Manager
         */
        require_once 'Tudu/Tudu/Manager.php';
        $manager = Tudu_Tudu_Manager::getInstance();

        return $manager;
    }

    /**
     * 写入操作日志
     *
     * @param string  $targetType
     * @param string  $targetId
     * @param string  $action
     * @param array   $detail
     * @param array   $params
     * @param boolean $privacy
     * @param boolean $isSystem
     */
    protected function _writeLog($targetType, $targetId, $action, array $params, $detail = null, $privacy = false, $isSystem = false)
    {
        if (null !== $detail) {
            $detail = serialize($detail);
        }

        /* @var $daoLabel Dao_Td_Log_Log */
        $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

        $daoLog->createLog(array(
            'orgid'      => $params['orgid'],
            'uniqueid'   => $params['uniqueid'],
            'operator'   => $isSystem ? '^system 图度系统' : $params['userinfo'],
            'logtime'    => time(),
            'targettype' => $targetType,
            'targetid'   => $targetId,
            'action'     => $action,
            'detail'     => $detail,
            'privacy'    => $privacy ? 1 : 0
        ));
    }
}