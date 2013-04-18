<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: ComposeController.php 2063 2012-08-17 08:22:45Z chenyongfa $
 */

/**
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Foreign_ComposeController extends TuduX_Controller_Foreign
{
    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));
        if (!$this->_tudu) {
            $this->json(false, 'error');
        }

        if (null === $this->_tudu || null === $this->_user) {
            $this->json(false, $this->lang['tudu_not_exists']);
        }

        if (!$this->_isValid()) {
            $this->json(false, $this->lang['foreign_access_invalided']);
        }
    }

    /**
     * 转发图度
     *
     */
    public function sendAction()
    {
        $post = $this->_request->getPost();

        $post = array_merge(array('to' => '', 'cc' => ''), $post);

        $action = $post['action'];
        $type   = $post['type'];

        // 判断操作，默认为发送
        if (!in_array($action, array('send', 'save'))) {
            $action = 'send';
        }

        // 判断类型，默认为任务
        if (!in_array($type, array('task', 'discuss', 'notice'))) {
            $type = 'task';
        }

        // 当前用户唯一ID
        $uniqueId    = $this->_user['uniqueid'];

        // 是否现在发送
        $isSend      = true;

        // 是否已经发送过，可判读来源的图度是否发送过，已发送过的不允许保存为草稿
        $isSent      = false;

        // 是否转发
        $isForward   = !empty($post['forward']);

        // 是否来源于草稿
        $isFromDraft = false;

        // 是否发起人
        $isSender    = false;

        // 是否执行人
        $isAccpter   = false;

        // 是否通知所有关联人员
        $notifyAll = !empty($post['notifyall']);

        // 需要发送提醒的人
        $notifyTo = array();

        // 抄送人加入自己
        $post['cc'] .= "\n" . $this->_user['email'] . ' ' . $this->_user['truename'];

        // 需要发送的地址，可能为空
        $address = array(
            'to' => $this->_formatRecipients($post['to']),
            'cc' => $this->_formatRecipients($post['cc'], true)
            );

        // 需要发送的执行人，方便后面调用
        $accepters = $address['to'];

        // 需要投递的联系人数据，保存用户唯一ID
        // uniqueid => array(isaccepter => {boolean}, accepterinfo => {string})
        $recipients = array();

        // 需要移除接受人的用户唯一ID
        $removeAccepters = array();

        if (null === $this->_tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }

        $fromTudu = $this->_tudu;

        // 日志记录内容
        $tuduLog = array('action' => 'create', 'detail' => array());
        $postLog = array('action' => 'create', 'detail' => array());

        ////////////////////////////
        // 操作及参数判断

        // 发送操作
        if ('send' == $action) {

            $isAccpter = array_key_exists($this->_user['email'], $accepters);

            // 如果是转发
            if ($isForward) {

                // 转发时，必须有图度存在
                if (!$fromTudu) {
                    $this->json(false, $this->lang['tudu_not_exists']);
                }

                // 图度组不能转发
                if ($fromTudu->isTuduGroup) {
                    $this->json(false, $this->lang['deny_forward_tudugroup']);
                }

                // 非图度执行人不能转发图度
                if (!in_array($this->_user['email'], $fromTudu->accepter)) {
                    $this->json(false, $this->lang['forbid_non_accepter_forward']);
                }

                // 执行人不能转发给自己
                if ($isAccpter) {
                    $this->json(false, $this->lang['forbid_forward_myself']);
                }

                foreach ($address['to'] as $a => $n) {
                    if (in_array($a, $fromTudu->accepter, true)) {
                        $this->json(false, sprintf($this->lang['user_is_accepter'], $n));
                    }
                }

                $tuduLog['action'] = Dao_Td_Log_Log::ACTION_TUDU_FORWARD;

            }

        // 保存图度
        } else if ('save' == $action) {

            $this->json(false);

        }

        // 发送时参数判断，1.检查必须的参数，2.检查联系人是否存在。保存草稿时不需要这些判断
        if ($isSend) {

            if ('task' == $type) {

                if (empty($address['to']) && (!$fromTudu || !$fromTudu->isTuduGroup)) {
                    $this->json(false, $this->lang['missing_to']);
                }

            } else {

                if (empty($address['cc'])) {
                    $this->json(false, $this->lang['missing_cc']);
                }
            }

            if (!$isForward && empty($post['subject'])) {
                $this->json(false, $this->lang['missing_subject']);
            }
            if (empty($post['content'])) {
                $this->json(false, $this->lang['missing_content']);
            }

            /* @var $daouser Dao_Td_Contact_Contact */
            $daoContact = $this->getDao('Dao_Td_Contact_Contact');
            /* @var $daoUser Dao_Md_User_User */
            $daoUser = Oray_Dao::factory('Dao_Md_User_User');

            $forwardInfo = array();

            //被转发用户继承转发用户进度
            if ($isForward) {
                $forwardInfo = array(
                    'forwardinfo' => $this->_user['truename'] . "\n" . time(),
                    'percent'     => isset($post['percent']) ? (int) $post['percent'] : $fromTudu->selfPercent
                );
            }

            $users = $this->_deliver->getTuduUsers($this->_tudu->tuduId);

            $isAuth = $fromTudu->isAuth;

            // 外部联系人转发，仅从当前图度相关用户中检查
            foreach ($address['to'] as $a => $name) {
                foreach ($users as $u) {
                    if ($u['email'] == $a && $u['truename'] == $name) {
                        $unId = $u['uniqueid'];
                        $recipients[$unId] = array_merge(array(
                            'uniqueid'     => $unId,
                            'role'         => Dao_Td_Tudu_Tudu::ROLE_ACCEPTER,
                            'accepterinfo' => $a . ' ' . $name,
                            'percent'      => 0,
                            'tudustatus'   => 0,
                            'isforeign'    => $u['isforeign'],
                            'authcode'     => ($u['isforeign'] && $isAuth) ? Oray_Function::randKeys(4) : null
                        ), $forwardInfo);

                        continue 2;
                    }
                }

                $unId = Dao_Td_Contact_Contact::getContactId();
                $info = Oray_Function::isEmail($a) ? $a . ' ' . $name : $name;

                $recipients[$unId] = array_merge(array(
                    'uniqueid'     => $unId,
                    'role'         => Dao_Td_Tudu_Tudu::ROLE_ACCEPTER,
                    'accepterinfo' => $info,
                    'percent'      => 0,
                    'tudustatus'   => 0,
                    'isforeign'    => 1,
                    'authcode'     => $isAuth ? Oray_Function::randKeys(4) : null
                ), $forwardInfo);
            }

            // 去除原有执行人
            if ($fromTudu) {
                $fromAccepter = $this->_deliver->getTuduAccepters($fromTudu->tuduId);
                $removeInfos  = array();
                $to = array();

                foreach ($fromAccepter as $acpter) {

                    if ($isForward) {
                        if ($acpter['uniqueid'] == $uniqueId) {
                            $removeAccepters[] = $this->_user['uniqueid'];
                            $removeInfos[$this->_user['uniqueid']] = $acpter['accepterinfo'];
                            continue ;
                        }
                    } elseif (!isset($recipients[$acpter['uniqueid']])
                              || !is_array($recipients[$acpter['uniqueid']])) {

                        $removeAccepters[] = $acpter['uniqueid'];
                        $removeInfos[$acpter['uniqueid']] = $acpter['accepterinfo'];
                        continue ;
                    }

                    if (isset($recipients[$acpter['uniqueid']]['tudustatus'])) {
                        $recipients[$acpter['uniqueid']]['percent'] = (int) $acpter['percent'];

                        if (!$isForward && $acpter['tudustatus'] != 3) {
                            $recipients[$acpter['uniqueid']]['tudustatus'] = $acpter['tudustatus'];
                        }
                    }

                    $to[] = $acpter['accepterinfo'];
                    $acceptInfo = explode(' ', $acpter['accepterinfo']);
                    $notifyTo[] = $acceptInfo[0];
                }

                $post['to'] = array_unique(array_merge($to, explode("\n", $post['to'])));
                $post['to'] = implode("\n", $post['to']);

                if ($fromTudu->isTuduGroup && !empty($removeAccepters)) {
                    /** @var $daoGroup Dao_Td_Tudu_Group */
                    $daoGroup = $this->getDao('Dao_Td_Tudu_Group');
                    foreach ($removeAccepters as $unId) {
                        if ($daoGroup->getChildrenCount($fromTudu->tuduId, $unId) > 0) {
                            $this->json(false, sprintf($this->lang['user_has_divide'], $removeInfos[$unId]));
                        }
                    }
                }
            }

            // 处理抄送人
            $arrCC = array();
            // 外部联系人转发，仅从当前图度相关用户中检查
            foreach ($address['cc'] as $a => $name) {
                foreach ($users as $u) {
                    if ($u['email'] == $a && $u['truename'] == $name) {
                        $unId = $u['uniqueid'];
                        $recipients[$unId] = array(
                            'uniqueid'     => $unId,
                            'role'         => Dao_Td_Tudu_Tudu::ROLE_CC,
                            'accepterinfo' => $a . ' ' . $name,
                            'isforeign'    => $u['isforeign'],
                            'authcode'     => ($u['isforeign'] && $isAuth) ? Oray_Function::randKeys(4) : null
                        );

                        continue 2;
                    }
                }

                $unId = Dao_Td_Contact_Contact::getContactId();

                $recipients[$unId] = array(
                    'uniqueid'     => $unId,
                    'role'         => Dao_Td_Tudu_Tudu::ROLE_CC,
                    'accepterinfo' => $a . ' ' . $name,
                    'isforeign'    => 1,
                    'authcode'     => $isAuth ? Oray_Function::randKeys(4) : null
                );
            }

            // 编辑/转发，合并原有转发人信息
            if (null !== $fromTudu) {
                $fromCC = array();
                foreach ($fromTudu->cc as $addr => $cc) {
                    if (!array_key_exists($addr, $address['cc'])) {
                        $fromCC[] = $addr . ' ' . $cc[0];
                    }
                }
                $post['cc'] = implode("\n", $fromCC) . "\n" . $post['cc'];
            }

            // 通知所有人
            if (in_array($type, array('notice', 'discuss')) || $notifyAll) {
                $notifyTo = array_merge($notifyTo, $arrCC);
            }

            if ($fromTudu) {
                $users = $this->_deliver->getTuduUsers($fromTudu->tuduId);

                foreach ($users as $item) {
                    $labels = explode(',', $item['labels']);
                    if (in_array('^t', $labels) && !in_array('^n', $labels)) {
                        $user = $daoUser->getUser(array('uniqueid' => $item['uniqueid']));
                        $notifyTo[] = $user->address;
                    }
                }
            }

            // 通知跳过当前操作用户(如果有)
            $notifyTo = array_unique(array_diff($notifyTo, array($this->_user['email'])));

            if ($type == 'notice' && !isset($post['remind'])) {
                $notifyTo = null;
            }

            //$recipients = array_unique($recipients);

            //var_dump($address);
            //var_dump($recipients);
        }

        ////////////////////////////////
        // 参数构造逻辑

        // 基本参数
        $params = array(
            'orgid' => $this->_tudu->orgId,
            'boardid' => $fromTudu ? $fromTudu->boardId : $post['bid'],
            'email' => $this->_user['email'],
            'type' => $type,
            'subject' => isset($post['subject']) ? $post['subject'] : $fromTudu->subject,
            'to' => $post['to'],
            'cc' => $post['cc'],
            'priority' => empty($post['priority']) ? 0 : (int) $post['priority'],
            'privacy' => empty($post['privacy']) ? 0 : (int) $post['privacy'],
            'status' => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
            'lastposttime' => $this->_timestamp,
            'content' => $post['content'],
            'attachment' => !empty($post['attach']) ? (array) $post['attach'] : array(),
            'file'       => !empty($post['file']) ? (array) $post['file'] : array()
            );

        if (isset($post['starttime'])) {
            $params['starttime'] = !empty($post['starttime']) ? strtotime($post['starttime']) : null;
        }

        if (isset($post['endtime'])) {
            $params['endtime'] = !empty($post['endtime']) ? strtotime($post['endtime']) : null;
        }

        if (isset($post['totaltime']) && is_numeric($post['totaltime'])) {
            $params['totaltime'] = round((float) $post['totaltime'], 2) * 3600;
        }

        if (isset($post['percent'])) {
            $params['percent'] = min(100, (int) $post['percent']);
        }

        if (isset($post['classid'])) {
            $params['classid'] = $post['classid'];
        }

        if (!empty($post['notifyall'])) {
            $params['notifyall'] = $post['notifyall'];
        }
        // 公告置顶
        if ($type == 'notice' && !empty($params['endtime']) && $params['endtime'] >= strtotime('today')) {
            $params['istop'] = 1;
        } else {
            $params['istop'] = 0;
        }

        // 仅当草稿发送时更新创建时间
        if (!$fromTudu || ($isFromDraft && $isSend)) {
            $params['createtime'] = $this->_timestamp;
        }

        // 更新图度操作时，一些参数设置
        if (!isset($params['percent'])) {
            $params['percent'] = $fromTudu->percent;
        }

        if (isset($params['percent'])) {
            if (100 === $params['percent']) {
                $params['status'] = Dao_Td_Tudu_Tudu::STATUS_DONE;
                $params['cycle']  = null;
            } elseif ($params['percent'] > 0) {
                $params['status'] = Dao_Td_Tudu_Tudu::STATUS_DOING;
            }
        }

        // 处理日志记录内容
        $tuduLog['detail'] = $params;
        $postLog['detail'] = array(
            'content' => $params['content']
        );
        unset(
            $tuduLog['detail']['cycle'],
            $tuduLog['detail']['vote'],
            $tuduLog['detail']['email'],
            $tuduLog['detail']['content'],
            $tuduLog['detail']['attachment'],
            $tuduLog['detail']['file'],
            $tuduLog['detail']['poster'],
            $tuduLog['detail']['posterinfo']
        );
        $logPrivacy = !$isSend;

        ///////////////////////////////////
        // 保存图度数据

        $tuduId = $fromTudu->tuduId;
        $postId = $fromTudu->postId;

        // 内容的参数
        $postParams = array(
            'content'    => $params['content'],
            'lastmodify' => implode(chr(9), array($uniqueId, $this->_timestamp, $this->_user['truename'])),
            'createtime' => $this->_timestamp,
            'attachment' => $params['attachment'],
            'isforeign'  => 1,
            'file'       => !empty($post['file']) ? (array) $post['file'] : array()
        );

        // 从未发送时（草稿），相关的数据初始化（时效性的数据清除）
        if (!$isSent) {

            // 创建时间可相当于最先发送的时间
            $params['createtime'] = $this->_timestamp;

            // 未发送过，不存在最后编辑
            unset($postParams['lastmodify']);
        }

        // 不变更发起人
        unset($params['from']);

        if ($isForward) {
            // 转发，更新最后转发人信息，不更新图度元数据，新建回复内容
            unset($postParams['lastmodify']);
            $params['subject']    = $fromTudu->subject;
            $params['content']    = $fromTudu->content;
            $params['status']     = Dao_Td_Tudu_Tudu::STATUS_UNSTART;
            $params['accepttime'] = null;

            $params['lastforward'] = implode("\n", array(
               $this->_user['truename'],
               time()
            ));

            // 先发送新的回复
            $postParams = array_merge($postParams, array(
                'orgid'      => $this->_tudu->orgId,
                'boardid'    => $fromTudu->boardId,
                'tuduid'     => $tuduId,
                'uniqueid'   => $this->_user['uniqueid'],
                'poster'     => $this->_user['truename'],
                'email'      => $this->_user['email']
            ));

            $postId = $this->_deliver->createPost($postParams);
            if (!$postId) {
                $this->json(false, $this->lang['save_failure']);
            }

            $this->getDao('Dao_Td_Tudu_Post')->sendPost($tuduId, $postId);

            $postLog['detail'] = $postParams;

            // 工作流程
            $steps = $this->_manager->getSteps($tuduId)->toArray('stepid');
            if (!empty($steps) && $type = 'task') {
                $currentStep = $this->_tudu->stepId && false === strpos($this->_tudu->stepId, '^') ? $steps[$this->_tudu->stepId] : array_pop($steps);
                // 当前为审批步骤
                $stepNum  = count($steps);
                $newSteps = array();

                $currentTo  = array_keys($this->_formatStepRecipients($params['to']));
                $fromTo     = array_keys($this->_tudu->to);
                $fromCount  = count($fromTo);
                $isChangeTo = count($currentTo) != $fromCount || count(array_uintersect($fromTo, $currentTo, "strcasecmp")) != $fromCount;

                if ($isChangeTo) {
                    $prevId   = $currentStep['stepid'];
                    $orderNum = $currentStep['ordernum'];
                    $stepId   = Dao_Td_Tudu_Step::getStepId();

                    $newSteps[$stepId] = array(
                        'orgid'  => $this->_tudu->orgId,
                        'tuduid' => $tuduId,
                        'stepid' => $stepId,
                        'uniqueid' => $uniqueId,
                        'prevstepid' => $prevId,
                        'nextstepid' => '^end',
                        'type' => $this->_tudu->acceptMode ? Dao_Td_Tudu_Step::TYPE_CLAIM : Dao_Td_Tudu_Step::TYPE_EXECUTE,
                        'ordernum' => ++$orderNum,
                        'createtime' => time(),
                        'users'    => $this->_formatStepRecipients($params['to'])
                    );

                    $params['stepid'] = $stepId;
                }

                // 移除后随未开始执行的步骤
                foreach ($steps as $step) {
                    if ($step['ordernum'] > $currentStep['ordernum']) {
                        $this->_manager->deleteStep($tuduId, $step['stepid']);
                        $stepNum--;
                    }
                }

                foreach ($newSteps as $step) {
                    if ($this->_manager->createStep($step)) {var_dump($step['users']);
                        $recipients = $this->_prepareStepRecipients($this->_tudu->orgId, $uniqueId, $step['users']);

                        $processIndex = $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE ? 0 : null;

                        $this->_manager->addStepUsers($tuduId, $step['stepid'], $recipients, $processIndex);

                        $stepNum++;
                    }
                }

                $params['stepnum'] = $stepNum;
            }

            // 更新图度
            if (!$this->_deliver->updateTudu($tuduId, $params)) {
                $this->json(false, $this->lang['save_failure']);
            }

        }

        // 过滤日志变更内容参数
        if ($fromTudu) {
            $arrFromTudu = $fromTudu->toArray();
            foreach ($tuduLog['detail'] as $k => $val) {
                // 记录增加抄送人
                if ($k == 'cc') {
                    $arr = explode("\n", $val);
                    foreach ($arr as $idx => $v) {
                        $ccArr = explode(' ', $v);
                        if (array_key_exists($ccArr[0], $fromTudu->cc)) {
                            unset($arr[$idx]);
                        }
                    }

                    if (!$arr) {
                        unset($tuduLog['detail']['cc']);
                    } else {
                        $tuduLog['detail']['cc'] = implode("\n", $arr);
                    }
                    continue ;
                }
                // 过滤未更新字段
                if (array_key_exists($k, $arrFromTudu) && $val == $arrFromTudu[$k]) {
                    unset($tuduLog['detail'][$k]);
                }
            }
            // 内容没有变更
            if (!$isForward) {
                if ($postLog['detail']['content'] == $fromTudu->content) {
                    unset($postLog['detail']);
                } else {
                    if (isset($postParams['lastmodify'])) {
                        $postLog['detail']['lastmodify'] = $postParams['lastmodify'];
                    }
                    $postLog['detail']['createtime'] = $postParams['createtime'];
                }
            }
            if (empty($tuduLog['detail']['cc'])) {
                unset($tuduLog['detail']['cc']);
            }

            unset($tuduLog['detail']['from']);
        }

        // 写入操作日志
        $this->_writeLog(Dao_Td_Log_Log::TYPE_TUDU, $tuduId, $tuduLog['action'], $tuduLog['detail'], $logPrivacy);
        if (!empty($postLog['detail'])) {
            $this->_writeLog(Dao_Td_Log_Log::TYPE_POST, $postId, $postLog['action'], $postLog['detail'], $logPrivacy);
        }

        $sendParams   = array();

        if ($type != 'task') {
            $sendParams['notice']  = $type == 'notice';
            $sendParams['discuss'] = $type == 'discuss';
        }

        // 删除需要移除的接受人
        if ($removeAccepters) {
            if (!$this->_deliver->removeTuduAccepter($tuduId, $removeAccepters)) {
                $this->json(false, $this->lang['send_failure']);
            }
        }

        // 发送图度
        if (!$this->_deliver->sendTudu($tuduId, $recipients, $sendParams)) {
            $this->json(false, $this->lang['send_failure']);
        }

        // 已发送的任务更新时，设置所有人为未读状态
        if ($isSent) {
            $this->_manager->markAllUnread($tuduId);
        }

        // 转发任务时，设置当前关联用户为转发状态
        if ($isForward) {
            $this->_manager->markForward($tuduId, $uniqueId);

            // 更新转发编辑后的任务进度
            $this->_deliver->updateProgress($tuduId, $uniqueId, null);

            // 更新转发后的任务接受状态
            $this->_deliver->updateLastAcceptTime($tuduId, $uniqueId, null);

            // 移除“我执行”标签
            $this->_manager->deleteLabel($tuduId, $uniqueId, '^a');
        }

        // 重新计算父级图度进度
        if ($fromTudu && $fromTudu->parentId) {
            $this->_deliver->calParentsProgress($fromTudu->parentId);
        }

        if ('task' == $type) {

            // 发起人为当前执行人
            if ($isAccpter) {

                // 自动接受任务
                $this->_deliver->acceptTudu($tuduId, $uniqueId, null);

                // 添加我执行
                $this->_deliver->addLabel($tuduId, $uniqueId, '^a');

                // 接受添加日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_ACCEPT,
                    array('status' => Dao_Td_Tudu_Tudu::STATUS_DOING, 'accepttime' => time())
                );

            // 非当前执行人
            } else {

                // 设为已读
                $this->_deliver->markRead($tuduId, $uniqueId);
            }

        }

        $config = $this->_bootstrap->getOption('httpsqs');

        // 插入消息队列
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        // 收发规则过滤
        $data = implode(' ', array(
            'tudu',
            'filter',
            '',
            http_build_query(array(
                'tsid'   => $this->_tsId,
                'tuduid' => $tuduId
            ))
        ));

        $httpsqs->put($data, 'tudu');

        // 发送外部邮件（如果有），处理联系人
        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'   => $this->_tsId,
                'tuduid' => $tuduId,
                'uniqueid' => $this->_user['uniqueid'],
                'to'       => ''
            ))
        ));

        $httpsqs->put($data, 'send');

        // IM提醒
        if (!empty($notifyTo)) {

            $content = str_replace('%', '%%', mb_substr(preg_replace('/<[^>]+>/', '', $params['content']), 0, 100, 'UTF-8'));

            $names = array(
                'task'    => '图度',
                'discuss' => '讨论',
                'notice'  => '公告'
            );
            $tpl = <<<HTML
<strong>您刚收到一个新的{$names[$type]}</strong><br />
<a href="http://{$this->_request->getServer('HTTP_HOST')}/frame#m=view&tid=%s&page=1" target="_blank">%s</a><br />
发起人：{$this->_user['truename']}<br />
更新日期：%s<br />
$content
HTML;
            $data = implode(' ', array(
                'tudu',
                'create',
                '',
                http_build_query(array(
                    'tuduid' =>  $this->_tudu->tuduId,
                    'from' => $this->_user['email'],
                    'to' => implode(',', $notifyTo),
                    'content' => sprintf($tpl, $this->_tudu->tuduId, $params['subject'], date('Y-m-d H:i:s', time()))
                    ))
                ));

            $httpsqs->put($data);
        }

        $this->json(true, $this->lang['send_success'], $tuduId);

    }

    /**
     * 发表回复
     *
     */
    public function replyAction()
    {
        $action = $this->_request->getPost('action');
        $isLog  = (boolean) $this->_request->getPost('updateprogress');
        $post   = $this->_request->getPost();

        $uniqueId = $this->_user['uniqueid'];
        $fromPost = null;

        if (null === $this->_tudu) {
            $this->json(false, $this->lang['tudu_not_exists']);
        }

        $tudu = $this->_tudu;

        if ('modify' == $action) {

            if (!empty($post['fpid'])) {
                $fromPost = $this->_deliver->getPostById($tudu->tuduId, $post['fpid']);
            }
            if (null === $fromPost) {
                return $this->json(false, $this->lang['post_not_exists']);
            }
        } else {
            $post['remind'] = true;
        }

        // 已确认的任务，禁止回复操作
        if ($tudu->isDone) {
            return $this->json(false, $this->lang['tudu_is_done']);
        }

        if (empty($post['content'])) {
            return $this->json(false, $this->lang['missing_content']);
        }

        $params = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'uniqueid'   => $uniqueId,
            'email'      => $this->_user['email'],
            'poster'     => $this->_user['truename'],
            'posterinfo' => '',
            'content'    => $post['content'],
            'islog'      => $isLog,
            'isforeign'  => 1,
            'attachment' => !empty($post['attach']) ? array_unique((array) $post['attach']) : array(),
            'file'       => !empty($post['file']) ? array_unique((array) $post['file']) : array()
        );

        if ($isLog) {
            $params['elapsedtime'] = round((float) $post['elapsedtime'], 2) * 3600;
            $params['percent']     = min(100, (int) $post['percent']);
        }

        if (!$fromPost) {
            $postId = $this->_deliver->createPost($params);

            if (!$postId) {
                return $this->json(false, $this->lang['post_send_failure']);
            }

            // 设置所有人为未读状态
            $this->_deliver->markAllUnread($tudu->tuduId);

            // 添加操作日志
            $this->_writeLog(
                Dao_Td_Log_Log::TYPE_POST,
                $postId,
                Dao_Td_Log_Log::ACTION_CREATE,
                $params
            );

        } else {
            $postId = $fromPost->postId;

            // 增加最后编辑信息
            $params['lastmodify'] = implode(chr(9), array($uniqueId, $this->_timestamp, $this->_user->trueName));

            $this->_deliver->updatePost($tudu->tuduId, $postId, $params);

            // 记录更新内容
            $arrFromPost = $fromPost->toArray();
            $updates = array();
            foreach ($params as $key => $val) {
                if (in_array($key, array('file', 'attachment'))) {
                    continue ;
                }

                if ($val != $arrFromPost[$key]) {
                    $updates[$key] = $val;
                }
            }

            // 添加操作日志
            $this->_writeLog(
                Dao_Td_Log_Log::TYPE_POST,
                $postId,
                Dao_Td_Log_Log::ACTION_CREATE,
                $updates
            );
        }

        $this->getDao('Dao_Td_Tudu_Post')->sendPost($tudu->tuduId, $postId);

        // 更新图度进度
        $tuduPercent = $tudu->percent;
        if ($isLog && !$tudu->isTuduGroup) {
            $tuduPercent = $this->_deliver->updateProgress($tudu->tuduId, $tudu->uniqueId, (int) $params['percent']);

            // 计算父级图度进度
            if ($tudu->parentId) {
                $this->_deliver->calParentsProgress($tudu->parentId);
            }

            // 添加操作日志
            $this->_writeLog(
                Dao_Td_Log_Log::TYPE_TUDU,
                $tudu->tuduId,
                Dao_Td_Log_Log::ACTION_TUDU_PROGRESS,
                array('percent' => $params['percent'], 'elapsedtime' => $tudu->elapsedTime + (int) $post['elapsedtime'])
            );
        }

        $config = $this->_bootstrap->getOption('httpsqs');

        // 插入消息队列
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        // IM提醒
        if (!empty($post['remind'])) {
            // 提醒相关人员 - 发送人、接收人、加星标
            $users = $this->_deliver->getTuduUsers($tudu->tuduId);
            $notifyTo = array();
            foreach ($users as $item) {
                if ($item['isforeign']) {
                    continue ;
                }

                $labels = explode(',', $item['labels']);
                if (($tudu->notifyAll || in_array('^t', $labels)) && !in_array('^n', $labels) && !$item['isforeign']) {
                    $user = $this->getMdDao('Dao_Md_User_User')->getUser(array('uniqueid' => $item['uniqueid']));
                    if ($user) {
                        $notifyTo[] = $user->address;
                    }
                }
            }

            // 提醒跳过发送人
            $notifyTo = array_unique(array_merge($notifyTo, array($tudu->sender), $tudu->accepter));
            $notifyTo = array_diff($notifyTo, array($this->_user['email']));

            $content = mb_substr(str_replace('%', '%%', preg_replace('/<[^>]+>/', '', $params['content'])), 0, 100, 'UTF-8');

            $tpl = <<<HTML
<strong>您刚收到一个新的回复</strong><br />
<a href="http://{$this->_request->getServer('HTTP_HOST')}/frame#m=view&tid=%s&page=1" target="_blank">%s</a><br />
发起人：{$this->_user['truename']}<br />
更新日期：%s<br />
$content
HTML;
            $data = implode(' ', array(
                'tudu',
                'reply',
                '',
                http_build_query(array(
                    'tuduid' =>  $tudu->tuduId,
                    'from' => $this->_user['email'],
                    'to' => implode(',', $notifyTo),
                    'content' => sprintf($tpl, $tudu->tuduId, $tudu->subject, date('Y-m-d H:i:s', time()))
                    ))
                ));

            $httpsqs->put($data);

            // 发送外部提醒(如果有)
            $data = implode(' ', array(
                'send',
                'reply',
                '',
                http_build_query(array(
                    'tsid'   => $this->_tsId,
                    'tuduid' =>  $tudu->tuduId,
                    'uniqueid' => $this->_user['uniqueid'],
                    'from' => $this->_user['truename'],
                    'content' => mb_substr(strip_tags($content), 0, 20, 'utf-8')
                    ))
                ));

            $httpsqs->put($data, 'send');
        }

        // 周围期任务
        if ($isLog && $tudu->cycleId && $tuduPercent >= 100) {
            $this->_deliver->updateTudu($tudu->tuduId, array('cycleid' => null));

            $data = implode(' ', array(
                'tudu',
                'cycle',
                '',
                http_build_query(array(
                    'tuduid' =>  $this->_tudu->tuduId,
                    'tsid' => $this->_tsId,
                    'cycleid' => $this->_tudu->cycleId
                    ))
                ));
            $httpsqs->put($data, 'tudu');
        }

        $this->json(true, $this->lang['post_send_success']);
    }

    /**
     * 格式化收件人
     * array(email => name ... )
     *
     * @param string $str
     */
    private function _formatRecipients($str, $containGroup = false)
    {
        $ret = array();
        $arr = explode("\n", $str);
        foreach ($arr as $item) {
            $info = explode(' ' , $item, 3);
            if (empty($info[1])) {
                continue ;
            }

            if ($info[0]) {
                $ret[$info[0]] = $info[1];
            } else {
                $ret[] = $info[1];
            }
        }

        return $ret;
    }
    
    /**
     * 格式化接收人格式
     *
     * @param string $recipients
     */
    private function _formatStepRecipients($recipients)
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

    /**
     *
     * @param $orgId
     * @param $uniqueId
     * @param $users
     */
    private function _prepareStepRecipients($orgId, $uniqueId, array $users)
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
                $user['processindex'] = (int)$item['processindex'];
            }

            if (isset($item['stepstatus'])) {
                $user['stepstatus'] = (int)$item['stepstatus'];
            }

            $recipients[$user['uniqueid']] = $user;
        }

        return $recipients;
    }

    public function sendmailAction()
    {

        $config = $this->_bootstrap->getOption('httpsqs');

        // 插入消息队列
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        // 发送外部邮件（如果有），处理联系人
        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'     => $this->_tsId,
                'tuduid'   => $this->_tudu->tuduId,
                'uniqueid' => $this->_user['uniqueid'],
                'to'       => ''
            ))
        ));

        $httpsqs->put($data, 'send');

        echo 'x';
        exit;
    }
}