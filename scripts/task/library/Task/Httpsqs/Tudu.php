<?php
/**
 * Task_Httpsqs
 *
 * LICENSE
 *
 *
 * @category   Task_Httpsqs_Tudu
 * @package    Task_Httpsqs_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Tudu.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * 后台处理脚本
 * * * 创建图度
 * * * 更新图度
 * * * 回复图度
 * * * 确认图度
 *
 * @category   Task_Httpsqs_Tudu
 * @package    Task_Httpsqs_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Httpsqs_Tudu extends Task_Abstract
{

    /**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     * @var Oray_Memcache
     */
    protected $_memcache = null;

    /**
     *
     * @var array
     */
    protected $_tsDbs = array();

    /**
     *
     * @var string
     */
    protected $_unId = '^system';

    /**
     *
     * @var 类型列表
     */
    protected $_typeNames = array(
        'tudu'    => '图度',
        'task'    => '图度',
        'discuss' => '讨论',
        'notice'  => '公告',
        'meeting' => '会议'
    );

    /**
     *
     */
    public function startUp()
    {
        foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_tsDbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
                continue ;
            }
            Tudu_Dao_Manager::setDb($key, Zend_Db::factory($item['adapter'], $item['params']));
        }

        $this->_memcache = new Oray_Memcache(array(
            'compression'   => $this->_options['memcache']['compression'],
            'compatibility' => $this->_options['memcache']['compatibility']
        ));
        $this->_memcache->addServer(
            $this->_options['memcache']['host'],
            $this->_options['memcache']['port']
        );

        $this->_httpsqs = new Oray_Httpsqs(
            $this->_options['httpsqs']['host'],
            $this->_options['httpsqs']['port'],
            $this->_options['httpsqs']['charset'],
            $this->_options['httpsqs']['names']['tudu']
        );
    }

    /**
     *
     */
    public function shutDown()
    {
        $this->_httpsqs->closeConnection();
    }

    /**
     * 执行
     */
    public function run()
    {
        do {
            $data = $this->_httpsqs->get($this->_options['httpsqs']['names']['tudu']);

            if (!$data || $data == 'HTTPSQS_GET_END') {
                break ;
            }

            list($module, $action, $sub, $query) = explode(' ', $data);

            parse_str($query, $query);

            if ($module !== 'tudu') {
                $this->getLogger()->warn("Invalid param \"module\" values {$module}");
            }

            if (empty($query['tsid'])) {
                $this->getLogger()->warn("Missing param \"tsid\"");
                continue ;
            }

            if (!isset($this->_tsDbs['ts' . $query['tsid']])) {
                return ;
            }

            $tsId = $query['tsid'];
            Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $this->_tsDbs['ts' . $tsId]);

            switch ($action) {
                case 'create':
                    $this->createTudu($query);
                    break;
                case 'update':
                    $this->updateTudu($query);
                    break ;
                case 'review':
                    $this->reviewTudu($query);
                    break;
                case 'reply':
                    $this->reply($query);
                    break;
                case 'confirm':
                    $this->doneTudu($query);
                    break;
                case 'filter':
                    $this->filterTudu($query);
                    break;
                case 'rule':
                    $this->updateRules($query);
                    break;
                case 'cycle':
                    $this->cycle($query);
                    break;
                default:
                    $this->getLogger()->info("Invalid action values {$action}");
                    break;
            }

        } while (true);
    }

    /**
     * 创建图度
     *
     * 检查、保存外部联系人
     * 构造图度talk通知内容和参数
     * 分发其他相关操作队列
     * 1、IM通知
     * 2、图度收发规则
     * 3、外发邮件
     * 4、自动确认
     */
    public function createTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['server'])
            || empty($params['tsid'])
            || empty($params['from'])
            || empty($params['uniqueid']))
        {
            return ;
        }

        $tuduId   = $params['tuduid'];
        $tsId     = $params['tsid'];
        $server   = $params['server'];
        $from     = $params['from'];
        $uniqueId = $params['uniqueid'];

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_unId);

        // 不存在
        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id:{$tuduId} is not exists");
            return ;
        }

        // 查找通知接收人
        $users = $manager->getTuduUsers($tuduId, array('isfroeign' => 0));
        $notifyTo = array();
        foreach ($users as $user) {
            $notifyTo[] = $user['email'];
        }

        // 添加常用版块
        $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
        $favor = $daoBoard->getFavor($tudu->orgId, $tudu->boardId, $uniqueId);

        $weight = 0;
        if (null === $favor) {
            $daoBoard->addFavor(array(
                'orgid'    => $tudu->orgId,
                'boardid'  => $tudu->boardId,
                'uniqueid' => $uniqueId,
                'weight'   => 1
            ));
        // 已存在的增加权重
        } else {
            if ($favor['weight'] < Dao_Td_Board_Board::FAVOR_WEIGHT_LIMIT) {

                $daoBoard->updateFavor($tudu->orgId, $tudu->boardId, $uniqueId, array(
                    'weight' => $favor['weight'] + 1
                ));
            }
        }

        // 最多五个
        $boards = $daoBoard->getBoards(array(
            'orgid'    => $tudu->orgId,
            'uniqueid' => $uniqueId
        ), array('weight' => 1))->toArray();

        $autoFavor = array();
        foreach ($boards as $item) {
            if ($item['weight'] >= Dao_Td_Board_Board::FAVOR_WEIGHT_LIMIT) {
                continue ;
            }

            $autoFavor[$item['boardid']] = $tudu->boardId == $item['boardid'] ? $item['weight'] + 1 : $item['weight'];
        }

        $count = count($autoFavor);
        if ($count > 5 && arsort($autoFavor)) {
            $counter = 0;
            $spliced = array_splice($autoFavor, 5);
            $minWeight = end($autoFavor);
            $offset    = $minWeight - 5;

            if ($offset >= 0) {
                foreach ($autoFavor as $bid => $weight) {
                    $daoBoard->updateFavor($tudu->orgId, $bid, $uniqueId, array('weight' => $weight - $offset));
                }

                foreach ($spliced as $bid => $weight) {
                    $weight -= $offset;
                    if ($weight <= 0) {
                        $daoBoard->deleteFavor($tudu->orgId, $bid, $uniqueId);
                        continue ;
                    }

                    if ($weight == 5 || $tudu->boardId == $bid) {
                        $weight --;
                    }
                    $daoBoard->updateFavor($tudu->orgId, $bid, $uniqueId, array('weight' => $weight));
                }
            }
        }

        // 常用工作流
        if ($tudu->flowId) {
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);
            $flowFavor = $daoFlow->getFavor($tudu->flowId, $uniqueId);

            if (null === $flowFavor) {
                $daoFlow->addFavor($tudu->flowId, $uniqueId);
            } else {
                $daoFlow->updateFavor($tudu->flowId, $uniqueId, array(
                    'weight' => $flowFavor['weight'] + 1,
                    'updatetime' => time()
                ));
            }
        }

        // 工作流回复
        if (!empty($params['flowid']) && !empty($params['nstepid'])) {
            $flowId = $params['flowid'];
            $stepId = $params['nstepid'];

            $types = array(
                0 => '执行',
                1 => '审批',
                2 => '认领'
            );

            $typeText = array(
                0 => '执行本任务',
                1 => '对本任务进行审批',
                2 => '对本任务进行认领'
            );

            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);
            $daoTuduFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $flow = $daoFlow->getFlowById($flowId);
            if (null != $flow) {
            	$flow  = $flow->toArray();
                //$users = $daoStep->getUsers($tuduId, $stepId);
                $tuduFlow = $daoTuduFlow->getFlow(array('tuduid' => $tuduId));
                //$step     = $flow['steps'][$stepId];
                
                foreach ($flow['steps'] as $item) {
                	if ($item['stepid'] == $stepId) {
                		$step = $item;
                	}
                }

                $names = array();
                if (isset($tuduFlow->steps[$stepId])) {
                    $st    = $tuduFlow->steps[$stepId];
                    $users = $st['section'][$st['currentSection']];

                    foreach ($users as $user) {
                        $names[] = $user['truename'];
                    }
                }

                $names = implode(',', $names);

                if ($step) {
                    // 处理描述换行
                    $description = nl2br($step['description']);
                    $content = <<<POST
<div class="tudu_sysinfo_wrap tudu_sys_pass"><div class="tudu_sysinfo_border"></div><div class="tudu_sysinfo_corner"></div>
<div class="tudu_sysinfo_body">
<div class="tudu_sysinfo_wrap">
    <div class="tudu_sysinfo_icon"></div>
    <div class="tudu_sysinfo_content">
        <p><strong>{$types[$step['type']]}：</strong>由&nbsp;{$names}&nbsp;{$typeText[$step['type']]}</p>
        <p><strong>{$step['subject']}</strong></p>
        <p><strong>描述：</strong>{$description}</p>
    </div>
</div>
<div class="tudu_sysinfo_clear"></div>
</div>
<div class="tudu_sysinfo_corner"></div><div class="tudu_sysinfo_border"></div></div>
POST;

                    // 构造参数
                    $post = array(
                        'orgid'      => $tudu->orgId,
                        'boardid'    => $tudu->boardId,
                        'tuduid'     => $tudu->tuduId,
                        'uniqueid'   => '^system',
                        'email'      => 'robot@oray.com',
                        'poster'     => '图度系统',
                        'posterinfo' => '',
                        'content'    => $content
                    );
                    $storage = Tudu_Tudu_Storage::getInstance();

                    $postId = $storage->createPost($post);
                    $manager->sendPost($tuduId, $postId);
                }
            }
        }

        $notifyTo = array_unique(array_diff($notifyTo, array($from)));

        // 发送提醒
        $this->sendTuduNotify('create', $tudu, $from, $server, $notifyTo);

        // 处理ios推送
        $this->_httpsqs->put(implode(' ', array(
            'tudu',
            'create',
            '',
            http_build_query(array(
                'orgid'  => $tudu->orgId,
                'tsid'   => $tsId,
                'tuduid' => $tuduId,
                'subject'=> $tudu->subject,
                'alertto'=> $notifyTo,
                'type'   => $tudu->type
            ))
        )), 'notify');

        // 执行图度规则
        $this->_httpsqs->put(implode(' ', array(
            'tudu',
            'filter',
            '',
            http_build_query(array(
                'tsid'   => $tsId,
                'tuduid' => $tuduId
            ))
        )), $this->_options['httpsqs']['names']['tudu']);

        // 执行外发
        $this->_httpsqs->put(implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $tsId,
                'tuduid'   => $tuduId,
                'uniqueid' => $uniqueId,
                'to'       => '',
                'act'      => null
            ))
        )), $this->_options['httpsqs']['names']['send']);

        // 发送时就已完成
        if ($tudu->isDone && $tudu->parentId) {
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'confirm',
                '',
                http_build_query(array(
                    'tsid'   => $tsId,
                    'tuduid' => $tudu->parentId
                ))
            )), $this->_options['httpsqs']['names']['tudu']);
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 更新图度
     *
     *
     * @param $params
     */
    public function updateTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['server'])
            || empty($params['tsid'])
            || empty($params['from'])
            || empty($params['uniqueid']))
        {
            return ;
        }

        $tuduId   = $params['tuduid'];
        $tsId     = $params['tsid'];
        $server   = $params['server'];
        $from     = $params['from'];
        $uniqueId = $params['uniqueid'];
        $isChangedCc = $params['ischangedCc'];

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_unId);

        // 不存在
        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id:{$tuduId} is not exists");
            return ;
        }

        $isReview = false;
        if ($tudu->type == 'notice' && strpos($tudu->stepId, '^') !== 0) {
            $isReview = true;
        }

        // 查找通知接收人
        $filter = array('isforeign' => 0);
        // 更新讨论时，默认提醒全部
        if (!$tudu->type == 'discuss' && !$tudu->notifyAll && !$isChangedCc && !$isReview) {
            $filter['role'] = 'to';
        }

        $users = $manager->getTuduUsers($tuduId, $filter);
        $notifyTo = array();
        foreach ($users as $user) {
            $notifyTo[] = $user['email'];
        }

        $notifyTo = array_unique(array_diff($notifyTo, array($from)));

        // 发送提醒
        $this->sendTuduNotify('update', $tudu, $from, $server, $notifyTo);

        // 处理ios推送
        $this->_httpsqs->put(implode(' ', array(
            'tudu',
            'update',
            '',
            http_build_query(array(
                'orgid'  => $tudu->orgId,
                'tsid'   => $tsId,
                'tuduid' => $tuduId,
                'subject'=> $tudu->subject,
                'alertto'=> $notifyTo,
                'type'   => $tudu->type
            ))
        )), 'notify');

        // 执行图度规则
        $this->_httpsqs->put(implode(' ', array(
            'tudu',
            'filter',
            '',
            http_build_query(array(
                'tsid'   => $tsId,
                'tuduid' => $tuduId
            ))
        )), $this->_options['httpsqs']['names']['tudu']);

        // 执行外发
        $this->_httpsqs->put(implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $tsId,
                'tuduid'   => $tuduId,
                'uniqueid' => $uniqueId,
                'to'       => '',
                'act'      => null
            ))
        )), $this->_options['httpsqs']['names']['send']);

        // 发送时就已完成
        if ($tudu->isDone && $tudu->parentId) {
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'confirm',
                '',
                http_build_query(array(
                    'tsid'   => $tsId,
                    'tuduid' => $tudu->parentId
                ))
            )), $this->_options['httpsqs']['names']['tudu']);
        }

        // 100的周期任务
        if ($tudu->percent >= 100 && $tudu->cycleId) {
            $daoCycle = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Cycle', Tudu_Dao_Manager::DB_TS);

            $cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId));

            if (null === $cycle) {
                $this->getLogger()->warn("Tudu Cycle id: {$tudu->cycleId} is not exists");
                return ;
            }

            // 当前周期循环次数，发送下一周期
            if ($cycle && $cycle->count == $tudu->cycleNum) {
                $this->_httpsqs->put(implode(' ', array(
                    'tudu',
                    'cycle',
                    '',
                    http_build_query(array(
                        'tsid'    => $tsId,
                        'tuduid'  => $tudu->tuduId,
                        'cycleid' => $tudu->cycleId
                    ))
                )), $this->_options['httpsqs']['names']['tudu']);
            }
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 审批相关操作
     *
     * @param $params
     */
    public function reviewTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['tsid'])
            || empty($params['uniqueid'])
            || empty($params['from'])
            || empty($params['type'])
            || empty($params['stepid'])
            || !isset($params['agree']))
        {
            return ;
        }


        $tuduId      = $params['tuduid'];
        $from        = $params['from'];
        $uniqueId    = $params['uniqueid'];
        $tsId        = $params['tsid'];
        $server      = $params['server'];
        $type        = $params['type'];
        $isChangedCc = $params['ischangedCc'];
        $stepId      = $params['stepid'];
        $agree       = $params['agree'];

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_options['httpsqs']['names']['im']);
        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id: {$tuduId} is not eixsts");
            return ;
        }

        //$daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        // 同意审批，通知下一步审批人或执行人
        $to = array();
        if ($agree) {
            $flow     = $daoFlow->getFlow(array('tuduid' => $tuduId));
            $currStep = isset($flow->steps[$flow->currentStepId]) ? $flow->steps[$flow->currentStepId] : null;
            //$stepUsers = $daoStep->getUsers($tuduId, $tudu->stepId);

            // 审批人
            if ($currStep['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {

                foreach ($currStep['section'] as $sec) {
                    foreach ($sec as $user) {
                        if ($user['status'] == 1) {
                            $info = explode(' ', $user['userinfo']);
                            $to[] = $info[0];
                        }
                    }
                }
            // 执行人
            } elseif($currStep['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                if ($tudu->type == 'notice') {
                    $accepters = $manager->getTuduUsers($tuduId);
                    foreach ($accepters as $item) {
                        $to[] = $item['email'];
                    }
                } else {
                    foreach ($currStep['section'][0] as $user) {
                        //$info = explode(' ', $user['userinfo']);
                        $to[] = $user['username'];
                    }
                }
            }

            $to = array_unique($to);

            // 工作流回复
            if (!empty($params['flowid']) && !empty($params['nstepid']) && !empty($params['stepstatus'])) {
                $flowId = $params['flowid'];
                $stepId = $params['nstepid'];

                $types = array(
                    0 => '执行',
                    1 => '审批',
                    2 => '认领'
                );

                $typeText = array(
                    0 => '执行本任务',
                    1 => '对本任务进行审批',
                    2 => '对本任务进行认领'
                );

                $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);
                //$daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

                $flow = $daoFlow->getFlowById($flowId);
                if (null != $flow) {
                    $flow  = $flow->toArray();
                    $users = array();
                    $step  = null;

                    foreach ($flow['steps'] as $item) {
                        if ($item['stepid'] == $stepId) {
                            $step = $item;
                        }
                    }

                    $names = array();
                    foreach ($currStep['section'] as $sec) {
                        foreach ($sec as $user) {
                            if ($user['status'] == 1) {
                                $names[] = $user['truename'];
                            }
                        }
                    }

                    $names = implode(',', $names);

                    if ($step) {
                        // 处理描述换行
                        $description = nl2br($step['description']);
                        $content = <<<POST
<div class="tudu_sysinfo_wrap tudu_sys_pass"><div class="tudu_sysinfo_border"></div><div class="tudu_sysinfo_corner"></div>
<div class="tudu_sysinfo_body">
<div class="tudu_sysinfo_wrap">
    <div class="tudu_sysinfo_icon"></div>
    <div class="tudu_sysinfo_content">
        <p><strong>{$types[$step['type']]}：</strong>由&nbsp;{$names}&nbsp;{$typeText[$step['type']]}</p>
        <p><strong>{$step['subject']}</strong></p>
        <p><strong>描述：</strong>{$description}</p>
    </div>
</div>
<div class="tudu_sysinfo_clear"></div>
</div>
<div class="tudu_sysinfo_corner"></div><div class="tudu_sysinfo_border"></div></div>
POST;

                         // 构造参数
                        $post = array(
                            'orgid'      => $tudu->orgId,
                            'boardid'    => $tudu->boardId,
                            'tuduid'     => $tudu->tuduId,
                            'uniqueid'   => '^system',
                            'email'      => 'robot@oray.com',
                            'poster'     => '图度系统',
                            'posterinfo' => '',
                            'content'    => $content
                        );
                        $storage = Tudu_Tudu_Storage::getInstance();

                        $postId = $storage->createPost($post);
                        $manager->sendPost($tuduId, $postId);
                    }
                }
            }

            $this->sendTuduNotify('create', $tudu, $from, $server, $to);
        }

        $notifyTo = array();
        $notifyTo[] = $tudu->sender;

        $filter = array('isforeign' => 0);
        if (!$tudu->notifyAll && !$isChangedCc) {
            $filter['role'] = 'to';
        }

        $users = $manager->getTuduUsers($tuduId, $filter);

        foreach ($users as $user) {
            $notifyTo[] = $user['email'];
        }

        // 获取前一步骤用户
        $prevStepId = $currStep['prev'];
        if ($prevStepId && strpos($prevStepId, '^') !== 0) {
            $prevStep = isset($flow->steps[$prevStepId]) ? $flow->steps[$prevStepId] : null;
            //$prevStepUsers = $daoStep->getUsers($tuduId, $prevStepId);
            foreach ($prevStep['section'] as $sec) {
                foreach ($sec as $user) {
                    $notifyTo[] = $user['username'];
                }
            }
        }

        $notifyTo = array_unique(array_diff($notifyTo, array($from)));
        $notifyTo = array_diff($notifyTo, $to);

        $this->sendReviewPostNotify($tudu, $agree, $from, $server, $notifyTo);
        // 处理ios推送
        $this->_httpsqs->put(implode(' ', array(
            'post',
            'create',
            '',
            http_build_query(array(
                'orgid'  => $tudu->orgId,
                'tsid'   => $tsId,
                'tuduid' => $tuduId,
                'subject'=> $tudu->subject,
                'alertto'=> $notifyTo,
                'type'   => $tudu->type
            ))
        )), 'notify');

        // 执行图度规则
        $this->_httpsqs->put(implode(' ', array(
            'tudu',
            'filter',
            '',
            http_build_query(array(
                'tsid'   => $tsId,
                'tuduid' => $tuduId
            ))
        )), $this->_options['httpsqs']['names']['tudu']);

        // 执行外发
        $this->_httpsqs->put(implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $tsId,
                'tuduid'   => $tuduId,
                'uniqueid' => $uniqueId,
                'to'       => '',
                'act'      => null
            ))
         )), $this->_options['httpsqs']['names']['send']);

        // 已完成
        if ($tudu->isDone && $tudu->parentId) {
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'confirm',
                '',
                http_build_query(array(
                    'tsid'   => $tsId,
                    'tuduid' => $tudu->parentId
                ))
            )), $this->_options['httpsqs']['names']['tudu']);
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 回复相关操作
     *
     * @param $params
     */
    public function reply($params)
    {
        if (empty($params['tuduid'])
            || empty($params['postid'])
            || empty($params['sender'])
            || empty($params['uniqueid'])
            || empty($params['from'])
            || empty($params['tsid'])
            || empty($params['server']))
        {
            return ;
        }

        $tuduId   = $params['tuduid'];
        $postId   = $params['postid'];
        $sender   = $params['sender'];
        $uniqueId = $params['uniqueid'];
        $from     = $params['from'];
        $tsId     = $params['tsid'];
        $server   = $params['server'];

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_unId);

        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id: {$tuduId} is not eixsts");
            return ;
        }

        $post = $manager->getPostById($tuduId, $postId);

        if (null == $post) {
            $this->getLogger()->warn("Post id: {$postId} is not eixsts");
            return ;
        }

        // 提醒相关人员 - 发送人、接收人、加星标
        $users     = $manager->getTuduUsers($tudu->tuduId);
        $notifyTo  = array();
        $isForeign = false;
        $daoUser   = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

        foreach ($users as $item) {
            $labels = explode(',', $item['labels']);
            if ($tudu->type == 'notice' || ($tudu->type == 'discuss' && $tudu->notifyAll) || ($tudu->notifyAll || in_array('^t', $labels)) && !in_array('^n', $labels) && !$item['isforeign']) {
                $user = $daoUser->getUser(array('uniqueid' => $item['uniqueid']));
                if ($user) {
                    $notifyTo[] = $user->userName;
                }
            }

            if ($item['isforeign']) {
                $isForeign = true;
            }
        }

        // 工作流回复
        if (!empty($params['flowid']) && !empty($params['nstepid'])) {
            $flowId = $params['flowid'];
            $stepId = $params['nstepid'];

            $types = array(
                0 => '执行',
                1 => '审批',
                2 => '认领'
            );

            $typeText = array(
                0 => '执行本任务',
                1 => '对本任务进行审批',
                2 => '对本任务进行认领'
            );

            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);
            //$daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);
            $daoTuduFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $flow = $daoFlow->getFlowById($flowId);
            if (null != $flow) {
                $flow  = $flow->toArray();

                $tuduFlow = $daoTuduFlow->getFlow(array('tuduid' => $tuduId));
                $step = isset($tuduFlow->steps[$tuduFlow->currentStepId]) ? $tuduFlow->steps[$tuduFlow->currentStepId] : null;

                $names = array();
                foreach ($step['section'] as $sec) {
                    foreach ($sec as $user) {
                        $names[] = $user['truename'];
                    }
                }

                $names = implode(',', $names);

                if ($step) {
                    $content = <<<POST
<p><strong>{$types[$step['type']]}：</strong>由&nbsp;{$names}&nbsp;{$typeText[$step['type']]}</p>
<p><strong>{$step['subject']}</strong></p>
<p><strong>描述：</strong>{$step['description']}</p>
POST;

                     // 构造参数
                    $fpost = array(
                        'orgid'      => $tudu->orgId,
                        'boardid'    => $tudu->boardId,
                        'tuduid'     => $tudu->tuduId,
                        'uniqueid'   => '^system',
                        'email'      => 'robot@oray.com',
                        'poster'     => '图度系统',
                        'posterinfo' => '',
                        'content'    => $content
                    );
                    $storage = Tudu_Tudu_Storage::getInstance();

                    $postId = $storage->createPost($fpost);
                    $manager->sendPost($tuduId, $postId);
                }
            }
        }

        $notifyTo = array_unique(array_merge($notifyTo, array($tudu->sender), $tudu->accepter));
        $notifyTo = array_diff($notifyTo, array($from));

        $this->sendPostNotify($tudu, $post, $from, $server, $notifyTo);
        // 处理ios推送
        $this->_httpsqs->put(implode(' ', array(
            'post',
            $tudu->type,
            '',
            http_build_query(array(
                'orgid'  => $tudu->orgId,
                'tsid'   => $tsId,
                'tuduid' => $tuduId,
                'type'   => $tudu->type,
                'subject'=> $tudu->subject,
                'alertto'=> $notifyTo,
                'type'   => $tudu->type
            ))
        )), 'notify');

        // 外发回复
        if ($isForeign) {
            $content = $this->getPostDescription($post);
            $data = implode(' ', array(
                'send',
                'reply',
                '',
                http_build_query(array(
                    'tsid'     => $tsId,
                    'tuduid'   => $tuduId,
                    'uniqueid' => $uniqueId,
                    'from'     => $sender,
                    'content'  => mb_substr(strip_tags($content), 0, 20, 'utf-8')
                ))
            ));

            $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['send']);
        }

        // 自动确认
        if (!empty($params['confirm'])) {
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'confirm',
                '',
                http_build_query(array(
                    'tsid'   => $tsId,
                    'tuduid' => $tudu->parentId
                ))
            )), $this->_options['httpsqs']['names']['tudu']);
        }

        // 周期任务
        if (!empty($params['cycle'])) {
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'cycle',
                '',
                http_build_query(array(
                    'tuduid'  =>  $tudu->tuduId,
                    'tsid'    => $tsId,
                    'cycleid' => $tudu->cycleId
                ))
            )), $this->_options['httpsqs']['names']['tudu']);
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 图度确认完成相关操作
     *
     * 遍历确认父级图度（如果有并且自动确认）
     * @param $params
     */
    public function doneTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['tsid']))
        {
            return ;
        }

        $tuduId = $params['tuduid'];
        $tsId   = $params['tsid'];
        $path   = array();
        if (isset($params['path'])) {
            $path = explode(',', $params['path']);
        }

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_unId);

        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id: {$tuduId} is not eixsts");
            return ;
        }

        if ($tudu->needConfirm == 0 && $tudu->percent >= 100) {
            $ret = $manager->doneTudu($tuduId, true, 0);
            if (!$ret) {
                $this->getLogger()->warn("Done Tudu failed id:{$tuduId}");
            }

            if ($tudu->parentId) {
                $path[] = $tuduId;

                if(in_array($tudu->parentId, $path)) {
                    break;
                }

                $this->_httpsqs->put(implode(' ', array(
                    'tudu',
                    'confirm',
                    '',
                    http_build_query(array(
                        'tsid'   => $tsId,
                        'tuduid' => $tudu->parentId,
                        'path'   => implode(',', $path)
                    ))
                )), $this->_options['httpsqs']['names']['tudu']);
            }
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 生成周期图度
     *
     * @param $params
     */
    public function cycle($params)
    {
        if (empty($params['tuduid'])
            || empty($params['cycleid'])
            || empty($params['tsid']))
        {
            return ;
        }

        $tuduId  = $params['tuduid'];
        $cycleId = $params['cycleid'];
        $tsId    = $params['tsid'];

        $daoUser  = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        $daoCycle = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Cycle', Tudu_Dao_Manager::DB_TS);
        $manager  = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $this->_unId);
        $fromTuduId = $tudu->tuduId;
        $acceptMode = $tudu->acceptMode;
        $isAuth     = $tudu->isAuth;

        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id: {$tuduId} is not exists");
            return ;
        }

        $cycle = $daoCycle->getCycle(array('cycleid' => $cycleId));

        if (null === $cycle) {
            $this->getLogger()->warn("Tudu Cycle id: {$cycleId} is not exists");
            return ;
        }

        // 已经失效的周期设置
        if ($cycle->isValid == 0) {
            return ;
        }

        if (Dao_Td_Tudu_Cycle::END_TYPE_COUNT == $cycle->endType
            && $cycle->count >= $cycle->endCount) {
            $daoCycle->deleteCycle($cycle->cycleId);
            return ;
        }

        if (Dao_Td_Tudu_Cycle::END_TYPE_DATE == $cycle->endType
            && time() >= $cycle->endDate) {
            $daoCycle->deleteCycle($cycle->cycleId);
            return ;
        }

        $time = $daoCycle->getCycleTime($cycle, $tudu->startTime, $tudu->endTime);

        $recipients = array();
        $to         = array();
        $fromUnId   = null;

        $u = $daoUser->getUserByAddress($tudu->sender);
        if ($u) {
            $recipients[$u->uniqueId] = array(
                'uniqueid' => $u->uniqueId,
                'role'     => 'from'
            );
            $fromUnId = $u->uniqueId;
        }

        if (!$acceptMode) {
            $accepters = $manager->getTuduAccepters($tudu->tuduId);
            foreach ($accepters as $a) {
                $recipients[$a['uniqueid']] = array(
                    'accepterinfo' => $a['accepterinfo'],
                    'uniqueid'     => $a['uniqueid'],
                    'role'         => Dao_Td_Tudu_Tudu::ROLE_ACCEPTER,
                    'tudustatus'   => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
                    'isforeign'    => (int) $a['isforeign'],
                    'percent'      => 0,
                    'authcode'     => ((int) $a['isforeign'] && $tudu->isAuth) ? Oray_Function::randKeys(4) : null
                );

                if ($tudu->isAuth) {
                    $recipients[$a['uniqueid']]['authcode'] = $a['authcode'];
                }

                $to[] = $a['accepterinfo'];
            }
        }

        // 公共周期任务图度数据
        $params = $this->getCycleTuduParams($tudu, $cycle, $to, $fromUnId, $time);
        // 抄送
        if (!empty($tudu->cc)) {
            $cc     = array();
            $sendCc = array();

            foreach ($tudu->cc as $userName => $item) {
                $cc[] = $userName . ' ' . $item[0];
            }

            $params['cc'] = implode("\n", $cc);
            $sendCc       = $this->formatRecipients($params['cc']);
            $addressBook  = Tudu_AddressBook::getInstance();

            foreach ($sendCc as $key => $item) {
                if (isset($item['groupid'])) {
                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($tudu->orgId, $fromUnId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($tudu->orgId, $item['groupid']);
                    }
                    $recipients = array_merge($users, $recipients);
                } else {
                    $user = $addressBook->searchUser($tudu->orgId, $item['email']);
                    if (null === $user) {
                        $user = $addressBook->searchContact($fromUnId, $item['email'], $item['truename']);
                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
                        }
                    }
                    if (!isset($recipients[$user['uniqueid']])) {
                        $recipients[$user['uniqueid']] = $user;
                    }
                }
            }
        }

        // 密送
        if (!empty($tudu->bcc)) {
            $bcc     = array();
            $sendBcc = array();

            foreach ($tudu->bcc as $userName => $item) {
                $bcc[] = $userName . ' ' . $item[0];
            }

            $params['bcc'] = implode("\n", $bcc);
            $sendBcc       = $this->formatRecipients($params['bcc']);
            $addressBook   = Tudu_AddressBook::getInstance();

            foreach ($sendBcc as $key => $item) {
                if (isset($item['groupid'])) {
                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($tudu->orgId, $fromUnId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($tudu->orgId, $item['groupid']);
                    }
                    $recipients = array_merge($users, $recipients);
                } else {
                    $user = $addressBook->searchUser($tudu->orgId, $item['email']);
                    if (null === $user) {
                        $user = $addressBook->searchContact($fromUnId, $item['email'], $item['truename']);
                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
                        }
                    }
                    if (!isset($recipients[$user['uniqueid']])) {
                        $recipients[$user['uniqueid']] = $user;
                    }
                }
            }
        }

        // 会议数据
        if ($tudu->type == 'meeting') {
            $daoMeeting = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Meeting', Tudu_Dao_Manager::DB_TS);
            $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu->tuduId));

            if ($meeting) {
                $params['meeting'] = array(
                    'notifytype' => $meeting->notifyType,
                    'location'   => $meeting->location,
                    'isallday'   => $meeting->isAllday
                );

                $params['meeting']['notifytime'] = Dao_Td_Tudu_Meeting::calNotifyTime($params['starttime'], $meeting->notifyType);
            }
        }

        // 保留周期任务的附件
        if ($cycle->isKeepAttach) {
            $daoAttach = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
            $attaches = $daoAttach->getFiles(array('tuduid' => $tudu->tuduId, 'postid' => $tudu->postId))->toArray();

            $attachNum = 0;
            foreach ($attaches as $attach) {
                if ($attach['isattach']) {
                    $params['attachment'][] = $attach['fileid'];
                } else {
                    $params['file'][] = $attach['fielid'];
                }
            }
        }

        $stepId  = $params['stepid'];
        $tudu    = new Tudu_Tudu_Storage_Tudu($params);
        $storage = Tudu_Tudu_Storage::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));
        $deliver = Tudu_Tudu_Deliver::getInstance();

        $tuduId  = $storage->createTudu($tudu);
        if (!$tuduId) {
            $this->getLogger()->warn("Create Cycle Tudu failed id:{$tuduId}");
            return ;
        }

        if ($params['type'] == 'task' && $tuduId) {
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);
            
            $flow  = $daoFlow->getFlow(array('tuduid' => $fromTuduId));
            $steps = $flow->steps;
            $step  = reset($steps);
            
            $modelFlow = new Model_Tudu_Extension_Flow(array('orgid' => $tudu->orgId, 'tuduid' => $tuduId));

            /*$step = $daoStep->getStep(array('tuduid' => $fromTuduId, 'prevstepid' => '^head'));
            $orderNum   = 1;*/
            $prevStepId = '^head';
            
            $addressBook = Tudu_AddressBook::getInstance();
            // 认领
            if ($step && $step['type'] == Dao_Td_Tudu_Step::TYPE_CLAIM) {
            	
            	$modelFlow->addStep(array(
                    'stepid' => $step['stepid'],
                    'prev'   => $step['prev'],
                    'next'   => '^end',
                    'type'   => $step['type']
                ));
            	$acceptMode = true;
            	
            	$to = array();
            	foreach ($step['section'] as $idx => $sec) {
            		$section = array();
            		foreach ($sec as $user) {
            			$section[] = array(
            			    'uniqueid' => $user['uniqueid'],
            				'username' => $user['username'],
            				'truename' => $user['truename']
            		    );
            			
            			if ($idx == 0) {
            				$to[] = $user['username'] . ' '. $user['truename']; 
            				
            				$recipient = array(
                                 'uniqueid'   => $user['uniqueid'],
            				     'userinfo'   => $user['username'] . ' '. $user['truename'],
            					 'role'       => Dao_Td_Tudu_Tudu::ROLE_ACCEPTER,
            					 'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
            					 'percent'    => 0
            				);
            				
            				$u = $addressBook->searchUser($fromUnId, $user['username']);
            				if (!$u) {
            					$recipient['isforeign'] = 1;

            					if ($isAuth) {
            					    $recipient['auth'] = Oray_Function::randKeys(4);
            					}
            				}
            				
            				$recipients[$recipient['uniqueid']] = $recipient;
            			}
            		}

            		$modelFlow->addStepSection($step['stepid'], $sec);
            	}

            	$modelFlow->stepNum = 1;
            	$modelFlow->flowTo($step['stepid']);

            	$daoFlow->createFlow($modelFlow->toArray());
            	
            	// 更新to字段
            	$manager->updateTudu($tuduId, array(
            		'to'         => implode("\n", $to),
            		'acceptmode' => 1,
            		'accepttime' => null
            	));

            } else {
                // 审批
                $nextId = $step['next'];
            	$modelFlow->addStep(array(
            		'stepid' => $step['stepid'],
            		'prev'   => $step['prev'],
            		'next'   => $step['next'],
            		'type'   => $step['type']
            	));

            	foreach ($step['section'] as $idx => $sec) {
            		$section = array();
            		foreach ($sec as $user) {
            			$section[] = array(
            				'uniqueid' => $user['uniqueid'],
            				'username' => $user['username'],
            				'truename' => $user['truename']
            			);
            			 
            			if ($idx == 0) {
            				$to[] = $user['username'] . ' '. $user['truename'];
            	
            				$recipient = array(
            					'uniqueid'   => $user['uniqueid'],
            					'userinfo'   => $user['username'] . ' '. $user['truename'],
            					'role'       => isset($recipients[$user['uniqueid']]) ? $recipients[$user['uniqueid']]['role'] : null,
            					'isreview'   => true,
            					'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_UNSTART
            				);
            				
            				$recipients[$recipient['uniqueid']] = $recipient;
            			}

            		}
            		
            		$modelFlow->addStepSection($step['stepid'], $sec);
            	}
            	
            	if (isset($flow->steps[$nextId])) {
            		$next = $flow->steps[$nextId];
            		
            		$modelFlow->addStep(array(
            			'stepid' => $next['stepid'],
            			'prev'   => $next['prev'],
            			'next'   => '^end',
            			'type'   => $next['type']
            		));
            		
            		foreach ($next['section'] as $idx => $sec) {
            			$section = array();
            			foreach ($sec as $user) {
            				$section[] = array(
            					'uniqueid' => $user['uniqueid'],
            					'username' => $user['username'],
            					'truename' => $user['truename']
            				);
            			}
            		
            			$modelFlow->addStepSection($next['stepid'], $sec);
            		}
            	}
            	
            	$modelFlow->stepNum = count($modelFlow->steps);
            	$modelFlow->flowTo($step['stepid']);
            	$daoFlow->createFlow($modelFlow->toArray());
            }
        }

        $sendParams = array();
        if ($tudu->type == 'meeting') {
            $sendParams['meeting'] = true;
        }

        if (empty($reviewer)) {
            $ret = $deliver->sendTudu($tudu, $recipients, $sendParams);
            if (!$ret) {
                $this->getLogger()->warn("Send Tudu failed id:{$tuduId}");
                return ;
            }

            if (!$acceptMode) {
                foreach ($recipients as $unId => $recipient) {
                    if (isset($recipient['role']) && $recipient['role'] == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER) {
                        $manager->acceptTudu($tuduId, $unId, null);
                    }
                }
            }
        } else {
            $rev = array_shift($reviewer);

            $ret = $deliver->sendTudu($tudu, array(
                $rev['uniqueid'] => array('tuduid' => $tuduId, 'uniqueid' => $rev['uniqueid']),
                $fromUnId => array('tuduid' => $tuduId, 'uniqueid' => $fromUnId)
            ), null);

            if (!$ret) {
                $this->getLogger()->warn("Send Tudu failed id:{$tuduId}");
                return ;
            }

            $manager->addLabel($tuduId, $rev['uniqueid'], '^e');
        }

        // 发起人的
        if (null !== $fromUnId) {
            $manager->addLabel($tuduId, $fromUnId, '^f');
            $manager->addLabel($tuduId, $fromUnId, '^i');
        }

        $daoCycle->increment($cycle->cycleId);

        // 收发规则过滤
        $data = implode(' ', array(
            'tudu',
            'filter',
            '',
            http_build_query(array(
                'tsid'   => $tsId,
                'tuduid' => $tuduId
            ))
        ));
        $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['tudu']);

        // 外发请求
        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $tsId,
                'tuduid'   => $tuduId,
                'uniqueid' => $fromUnId,
                'to'       => ''
            ))
        ));
        $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['send']);

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 执行图度规则过滤
     *
     * 遍历所有接收人所有可用规则，并执行过滤
     * @param $params
     */
    public function filterTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['tsid']))
        {
            return ;
        }

        $tuduId  = $params['tuduid'];
        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        /** @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = Tudu_Dao_Manager::getDao('Dao_Td_Rule_Rule', Tudu_Dao_Manager::DB_TS);

        $users = $manager->getTuduUsers($tuduId);

        if (!$users) {
            $this->getLogger()->warn("Tudu id:{$tuduId} Users are not exists");
            return ;
        }

        $tudu = $manager->getTuduById($tuduId, $users[0]['uniqueid']);
        if (null === $tudu) {
            $this->getLogger()->warn("Tudu id: {$tuduId} is not exists");
            return ;
        }

        $tudu   = $tudu->toArray();
        $expire = 3600*24;//定义Memcache过期时间： 一天

        // 获取接收用户规则过滤
        foreach ($users as $user) {
            $unId = $user['uniqueid'];

            $rules = $this->_memcache->loadCache(array($daoRule, 'getRulesByUniqueId'), array($unId, array('isvalid' => true)), $expire);

            if ($rules->count() <= 0) {
                continue ;
            }

            foreach ($rules as $rule) {
                $filters     = $rule->getFilters();
                $filterCount = $filters->count();
                $matchCount  = 0;

                if ($filterCount <= 0) {
                    continue ;
                }

                foreach ($filters as $filter) {
                    $contain = false;
                    switch ($filter->what) {
                        // 发起人
                        case 'from':
                            if (is_array($filter->value)) {
                                foreach ($filter->value as $item) {
                                    $item = str_replace(array('oray.com', 'tudu.com'), array('oray', ''), $item);

                                    if ($item == $tudu['from'][3]) {
                                        $contain = true;
                                        break;
                                    }
                                }
                            }
                            break;
                        // 接收人，抄送人
                        case 'to':
                        case 'cc':
                            if (is_array($filter->value)) {
                                $count = 0;
                                $match = 0;
                                foreach ($filter->value as $item) {
                                    $count++;
                                    $item = str_replace(array('oray.com', 'tudu.com'), array('oray', ''), $item);
                                    if (isset($tudu[$filter->what][$item])) {
                                        $match ++;
                                    }
                                 }

                                 if ($count == $match) {
                                     $contain = true;
                                 }
                             }
                             break;
                        case 'subject':
                             $contain = false !== strpos($tudu['subject'], $filter->value);
                             break;
                    }

                    if (($filter->type == 'contain' && $contain) || ($filter->type == 'exclusive' && !$contain)) {
                        $matchCount++;
                    }
                }

                // 匹配过滤条件，执行规则操作
                if ($matchCount == $filterCount) {
                    // 标签
                    if ($rule->operation == 'label') {
                        $manager->addLabel($tuduId, $unId, $rule->value);
                    // 忽略
                    } elseif ($rule->operation == 'ignore') {
                        $manager->deleteLabel($tuduId, $unId, '^i');
                        $manager->addLabel($tuduId, $unId, '^g');
                    // 星标
                    } elseif ($rule->operation == 'starred') {
                        $manager->addLabel($tuduId, $unId, '^t');
                    }

                    // 是否需要邮件提醒
                    if (!empty($rule->mailRemind)) {
                        $mailRemind = $rule->mailRemind;
                        // 邮件提醒可用且图度在指定的板块的
                        if ($mailRemind['isvalid'] // 是否开启邮件提醒功能
                            && !empty($mailRemind['boards']) // 有设置板块
                            && !empty($mailRemind['mailbox']) // 有设置接收的邮箱
                            && is_array($mailRemind['boards']) // 指定板块必须是数组
                            && in_array($tudu['boardid'], $mailRemind['boards'])) // 指定板块下的图度才会有邮件提醒
                        {
                            $emails = array();
                            foreach ($mailRemind['mailbox'] as $email) {
                                // 必须是邮箱
                                if (Oray_Function::isEmail($email)) {
                                    $emails[] = $email;
                                }
                            }

                            if (!empty($emails)) {
                                $remind = array(
                                    'tuduid'     => $tudu['tuduid'],
                                    'tsid'       => $params['tsid'],
                                    'emails'     => $emails,
                                    'subject'    => $tudu['subject'],
                                    'sender'     => $tudu['from'][0],
                                    'lastupdate' => date('Y-m-d H:i:s', $tudu['lastposttime']),
                                    'content'    => mb_substr(strip_tags($tudu['content']), 0, 20, 'utf-8'),
                                    'type'       => $this->_typeNames[$tudu['type']],
                                    'url'        => 'http://' . $tudu['orgid'] . '.tudu.com/tudu/view?tid=' . $tudu['tuduid']
                                );

                                $this->getLogger()->warn("Send Email notify to:" . implode(',', $emails));
                                // 发送邮件提醒请求
                                $data = implode(' ', array(
                                    'send',
                                    'email',
                                    '',
                                    http_build_query($remind)
                                ));
                                $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['send']);
                            }
                        }
                    }
                }
            }
        }

        $this->getLogger()->debug("Tudu id:{$tuduId} done");
    }

    /**
     * 更新图度规则
     * @param $params
     */
    public function updateRules($params)
    {
        if (empty($params['ruleid'])
            || empty($params['tsid']))
        {
            return ;
        }

        $ruleId = $params['ruleid'];

        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        /** @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = Tudu_Dao_Manager::getDao('Dao_Td_Rule_Rule', Tudu_Dao_Manager::DB_TS);
        $rule = $daoRule->getRuleById($ruleId);

        if ($rule == null) {
            $this->getLogger()->warn("Tudu Rule id: {$ruleId} is not exists");
            return ;
        }
        $uniqueId = $rule->uniqueId;

        $filters = $rule->getFilters()->toArray();

        if (count($filters) <= 0) {
            $this->getLogger()->warn("Tudu Rule->getFilters() null ruleid: {$ruleId} is not exists");
            return ;
        }

        $subject = null;
        $subjectType = ' LIKE ';
        foreach ($filters as $key => $filter) {
            if ($filter['what'] == 'subject') {
                $subject = $filter['value'];
                if ($filter['type'] != 'contain') {
                    $subjectType = ' NOT LIKE ';
                }
                unset($filters[$key]);
                break;
            }
        }

        $tsdb = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS);

        // 过滤现有tudu
        if ($rule->isValid) {
            $sql = 'SELECT t.tudu_id AS tuduid, `from`, `to`, `cc`, `subject` FROM td_tudu t '
                 . 'LEFT JOIN td_tudu_user tu ON t.tudu_id = tu.tudu_id '
                 . 'WHERE tu.unique_id = ' . $tsdb->quote($uniqueId) . ' AND tu.labels IS NOT NULL ';

            if ($subject) {
                $sql .= ' AND t.subject ' . $subjectType . $tsdb->quote('%' . $subject . '%');
            }

            $query = $tsdb->query($sql);

            while ($row = $query->fetch()) {
                $match = false;
                if (count($filters)) {
                    $filterCount = count($filters);
                    $matchCount  = 0;

                    // 检查发送人，接收人，抄送人
                    foreach ($filters as $filter) {
                        $contain = false;
                        if (in_array($filter['what'], array('from', 'to', 'cc'))) {
                            if (is_array($filter['value'])) {
                                $vc = count($filter['value']);
                                $mc = 0;

                                $arr = explode("\n", $row[$filter['what']]);
                                $users = array();

                                foreach ($arr as $item) {
                                    $item = explode(' ', $item);
                                    $users[$item[0]] = $item[1];
                                }

                                foreach ($filter['value'] as $value) {
                                    $value = str_replace(array('oray.com', 'tudu.com'), array('oray', ''), $value);
                                    if (isset($users[$value])) {
                                        if ($filter['what'] == 'from' && $filter['type'] == 'contain') {
                                            $matchCount ++;
                                            continue 2;
                                        }
                                        $mc ++;
                                    }
                                }

                                if ($vc == $mc) {
                                    $contain = true;
                                }
                            }
                        } elseif ($filter['what'] == 'subject') {
                            $contain = false !== strpos($tudu['subject'], $filter->value);
                        }

                        if (($contain && $filter['type'] == 'contain') ||(!$contain && $filter['type'] == 'exclusive')) {
                            $matchCount ++;
                        }
                    }

                    if ($filterCount == $matchCount && $matchCount > 0) {
                        $match = true;
                    }
                } else {
                  $match = true;
                }

                // 匹配过滤条件，执行规则操作
                if ($match) {
                    // 标签
                    if ($rule->operation == 'label') {
                        $manager->addLabel($row['tuduid'], $uniqueId, $rule->value);
                    // 忽略
                    } elseif ($rule->operation == 'ignore') {
                        $manager->deleteLabel($row['tuduid'], $uniqueId, '^i');
                        $manager->addLabel($row['tuduid'], $uniqueId, '^g');
                    // 星标
                    } elseif ($rule->operation == 'starred') {
                        $manager->addLabel($row['tuduid'], $uniqueId, '^t');
                    }
                }
            }
        }

        $this->getLogger()->debug("Rule id:{$ruleId} done");
    }

    /**
     * 发送图度提醒
     *
     * @param $tudu
     * @param $server
     * @param $notifyTo
     */
    public function sendTuduNotify($action, $tudu, $from, $server, $notifyTo)
    {
        if (!$tudu) {
            return ;
        }

        $disception  = htmlspecialchars(str_replace('%', '%%', mb_substr(preg_replace('/<[^>]+>/', '', $tudu->content), 0, 100, 'UTF-8')));
        $subject     = htmlspecialchars(htmlspecialchars($tudu->subject)); // 转义两次，talk客户端会有一次转换
        $sendtime    = date('Y-m-d H:i:s', $tudu->createTime);
        $content     = <<<HTML

<strong>您刚收到一个新的{$this->_typeNames[$tudu->type]}</strong><br />
<a href="http://{$server}/frame#m=view&tid={$tudu->tuduId}&page=1" target="_blank" _tid="{$tudu->tuduId}">{$subject}</a><br />
发起人：{$tudu->from[0]}<br />
发送日期：{$sendtime}<br />
$disception
HTML;

        $count = @count($notifyTo);

        if ($count >= 20) {
            $to = array(); $counter = 0;
            do {
                $arr[] = array_shift($notifyTo);

                $counter ++ ;

                if ($counter >= 20 || count($notifyTo) == 0) {
                    $to[] = $arr;
                    $arr  = array();
                    $counter = 0;
                }
            } while (count($notifyTo) > 0);
        } else {
            $to = array($notifyTo);
        }

        foreach ($to as $item) {
            // 发送talk提醒
            $this->_httpsqs->put(implode(' ', array(
                'tudu',
                'create',
                '',
                http_build_query(array(
                    'tuduid'   => $tudu->tuduId,
                    'from'     => $from,
                    'to'       => implode(',', $item),
                    'content'  => $content
                ))
            )), $this->_options['httpsqs']['names']['im']);
        }
    }

    /**
     * 发送回复提醒
     */
    public function sendPostNotify($tudu, $post, $from, $server, $notifyTo)
    {
        $description = $content = htmlspecialchars($this->getPostDescription($post));
        $content     = <<<HTML
<strong>您刚收到一个新的回复</strong><br />
<a href="http://{$server}/frame#m=view&tid=%s&page=1" target="_blank" _tid="{$tudu->tuduId}">%s</a><br />
发起人：{$post->poster}<br />
更新日期：%s<br />
$description
HTML;

        $count = @count($notifyTo);

        if ($count >= 20) {
            $to = array(); $counter = 0;
            do {
                $arr[] = array_shift($notifyTo);

                $counter ++ ;

                if ($counter >= 20 || count($notifyTo) == 0) {
                    $to[] = $arr;
                    $arr  = array();
                    $counter = 0;
                }
            } while (count($notifyTo) > 0);
        } else {
            $to = array($notifyTo);
        }

        foreach ($to as $item) {
            $data = implode(' ', array(
                'tudu',
                'reply',
                '',
                http_build_query(array(
                    'tuduid' =>  $tudu->tuduId,
                    'from' => $from,
                    'to' => implode(',', $item),
                    'content' => sprintf($content, $tudu->tuduId, htmlspecialchars(htmlspecialchars($tudu->subject)), date('Y-m-d H:i:s', time()))
                ))
            ));

            $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['im']);
        }
    }

    /**
     * 发送审批回复提醒
     */
    public function sendReviewPostNotify($tudu, $agree, $from, $server, $notifyTo)
    {
        if (!$tudu) {
            return ;
        }

        $content  = $agree ? '已经同意本次申请。' : '不同意本次申请；<br />请申请者修改后再重新提交审批。';
        $subject  = htmlspecialchars(htmlspecialchars($tudu->subject));
        $sendtime = date('Y-m-d H:i:s', $tudu->createTime);
        $tpl      = <<<HTML
<strong>您刚收到一个新的回复</strong><br />
<a href="http://{$server}/frame#m=view&tid={$tudu->tuduId}&page=1" target="_blank" _tid="{$tudu->tuduId}">{$subject}</a><br />
发起人：{$tudu->from[0]}<br />
发送日期：{$sendtime}<br />
$content
HTML;

        // 发送talk提醒
        $count = @count($notifyTo);

        if ($count >= 20) {
            $to = array(); $counter = 0;
            do {
                $arr[] = array_shift($notifyTo);

                $counter ++ ;

                if ($counter >= 20 || count($notifyTo) == 0) {
                    $to[] = $arr;
                    $arr  = array();
                    $counter = 0;
                }
            } while (count($notifyTo) > 0);
        } else {
            $to = array($notifyTo);
        }

        foreach ($to as $item) {
            // 发送talk提醒
            $data = implode(' ', array(
                'tudu',
                'reply',
                '',
                http_build_query(array(
                    'tuduid'   => $tudu->tuduId,
                    'from'     => $from,
                    'to'       => implode(',', $item),
                    'content'  => $tpl
                ))
            ));

            $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['im']);
        }
    }

    /**
     * 回复描述
     *
     * @param array $post
     * @return string
     */
    public function getPostDescription($post)
    {
        return mb_substr(str_replace('%', '%%', preg_replace('/<[^>]+>/', '', $post->content)), 0, 100, 'UTF-8');
    }

    /**
     * 周期任务/会议
     * 图度数据
     *
     * @param $tudu
     * @param $fromUnId
     */
    public function getCycleTuduParams($tudu, $cycle, $to, $fromUnId, $time)
    {
        // 基本参数
        return array(
            'orgid'        => $tudu->orgId,
            'boardid'      => $tudu->boardId,
            'classid'      => $tudu->classId,
            'tuduid'       => Dao_Td_Tudu_Tudu::getTuduId(),
            'special'      => Dao_Td_Tudu_Tudu::SPECIAL_CYCLE,
            'cycleid'      => $cycle->cycleId,
            'uniqueid'     => $fromUnId,
            'email'        => $tudu->sender,
            'type'         => $tudu->type,
            'subject'      => $tudu->subject,
            'from'         => $tudu->from[3] . ' ' . $tudu->from[0],
            'to'           => implode("\n", $to),
            'priority'     => $tudu->priority,
            'privacy'      => $tudu->privacy,
            'status'       => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
            'content'      => $tudu->content,
            'poster'       => $tudu->from[0],
            'posterinfo'   => $tudu->posterInfo,
            'lastposter'   => $tudu->from[0],
            'lastposttime' => time(),
            'starttime'    => $time[0],
            'endtime'      => $time[1],
            'createtime'   => time(),
            'password'     => $tudu->password,
            'isauth'       => $tudu->isAuth,
            'cyclenum'     => $tudu->cycleNum + 1,
            'stepid'       => Dao_Td_Tudu_Step::getStepId(),
            'stepnum'      => 1,
            'issend'       => 1,
            'acceptmode'   => $tudu->acceptMode,
            'needconfirm'  => $tudu->needConfirm,
            'attachment'   => array(),
            'file'         => array()
        );
    }

    /**
     * 格式化接收人格式
     *
     * @param string $recipients
     */
    public function formatRecipients($recipients)
    {
        if (is_array($recipients)) {
            return $recipients;
        }

        $arr = explode("\n", $recipients);
        $ret = array();
        foreach ($arr as $item) {
            if (!trim($item)) {
                continue ;
            }

            list($key, $name) = explode(' ', $item, 2);
            if (false !== strpos($key, '@')) {
                $ret[$key] = array('email' => $key, 'truename' => $name);
            } else {
                $ret[$key] = array('groupid' => $key, 'truename' => $name);
            }
        }

        return $ret;
    }
}