<?php
/**
 * Tudu Manager Controller
 *
 * @version $Id: TuduMgrController.php 2809 2013-04-07 09:57:05Z cutecube $
 */
class TuduMgrController extends TuduX_Controller_Base
{
    /**
     *
     * @var Tudu_Tudu_Manager
     */
    public $manager;

    public function init()
    {
        $this->_helper->viewRenderer->setNeverRender();

        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));

        if (!$this->_user->isLogined()) {
            return $this->json(false, $this->lang['login_timeout']);
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            return $this->json(false, $this->lang['forbid_access']);
        }

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD => $this->multidb->getDefaultDb(),
            Tudu_Dao_Manager::DB_TS => $this->getTsDb()
        ));

        $this->manager = Tudu_Tudu_Manager::getInstance();
    }

    /**
     * 添加接收人
     */
    public function foreignAddAction()
    {
        $tuduId = $this->_request->getPost('tid');
        $to = $this->_request->getPost('to');
        $cc = $this->_request->getPost('cc');

        // 参数：图度ID必须存在
         if (!$tuduId) {
             return $this->json(false, $this->lang['invalid_tuduid']);
         };

        $fromTudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);

        // 图度必须存在
        if (null === $fromTudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
        // 必须是发起人才具备此操作
        if ($fromTudu->sender != $this->_user->userName) {
            return $this->json(false, $this->lang['perm_deny_add_foreign']);
        }

        // 检查外发用户email格式，外发用户是否存在，不存在则创建
        $address = array(
            'to' => $this->_formatRecipients($to),
            'cc' => $this->_formatRecipients($cc)
        );

        $tuduAddr = array(
            'to' => array(),
            'cc' => array()
        );
        $sendTo = array();

        foreach ($fromTudu->to as $k => $item) {
            $tuduAddr['to'][] = implode(' ', array(
                !is_numeric($k) ? $k : '',
                $item[0]
            ));
        }

        foreach ($fromTudu->cc as $k => $item) {
            $tuduAddr['cc'][] = implode(' ', array(
                !is_int($k) ? $k : '',
                $item[0]
            ));
        }

        /* @var $addressBook Tudu_AddressBook */
        $addressBook = Tudu_AddressBook::getInstance();

        $receivers = $this->manager->getTuduUsers($tuduId, array('isforeign' => 1));

        foreach ($address as $k => $arr) {
            foreach ($arr as $email => $name) {
                if (!$email && !$name) {
                    continue ;
                }

                $email = preg_replace('/^#+/', '', $email);

                // 本系统用户不参与外发
                if (false !== strpos($email, '@') && null !== $addressBook->searchUser($this->_user->orgId, $email)) {
                    return $this->json(false, $this->lang['deny_add_user_to_foreign']);
                }

                // 新添加的外发用户是否已存在
                foreach ($receivers as $receiver) {
                    if (($email && $email == $receiver['email'])) {
                        $display = $email ? $email : $name;
                        return $this->json(false, sprintf($this->lang['foreign_user_already_exists'], $display));
                    }
                }
                // 不存在联系人时创建
                if (!$addressBook->searchContact($this->_user->uniqueId, $email, $name)) {
                    $this->manager->createContact($this->_user->uniqueId, $email, $name);
                }

                $sendTo[] = $email;
                $tuduAddr[$k][] = implode(' ', array(
                    $email, $name
                ));
            }
        }

        /* @var $deliver Tudu_Tudu_Deliver */
        $deliver = Tudu_Tudu_Deliver::getInstance();
        /* @var $storage Tudu_Tudu_Storage */
        $storage = Tudu_Tudu_Storage::getInstance();

        $tudu = $storage->prepareTudu(array('to' => $to, 'cc' => $cc), $fromTudu);

        $recipients = $deliver->prepareRecipients($this->_user->uniqueId, $this->_user->userId, $tudu);

        // 添加用户
        if (!$deliver->sendTudu($tudu, $recipients)) {
            return $this->json(false, $this->lang['add_foreign_failure']);
        }

        // 更新图度用户
        $this->manager->updateTudu($tuduId, array(
            'to' => implode("\n", $tuduAddr['to']),
            'cc' => implode("\n", $tuduAddr['cc'])
        ));

        $config = $this->bootstrap->getOption('httpsqs');
        // 发送邮件
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        // 发送外部邮件（如果有），处理联系人
        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'   => $this->_user->tsId,
                'tuduid' => $tuduId,
                'uniqueid' => $this->_user->uniqueId,
                'to'       => implode(',', $sendTo)
            ))
        ));

        $httpsqs->put($data, 'send');

        return $this->json(true, $this->lang['add_foreign_success']);
    }

    /**
     * 删除接收人
     */
    public function foreignDeleteAction()
    {
        $tuduId   = $this->_request->getPost('tid');
        $uniqueId = $this->_request->getPost('uniqueid');

        // 参数：图度ID必须存在
         if (!$tuduId || !$uniqueId) {
             return $this->json(false, $this->lang['invalid_tuduid']);
         };

        $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);

        // 图度必须存在
        if (null === $tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
        // 必须是发起人才具备此操作
        if ($tudu->sender != $this->_user->userName) {
            return $this->json(false, $this->lang['perm_deny_add_foreign']);
        }

        $userInfo = $this->manager->getUser($tuduId, $uniqueId);

        if (null === $userInfo) {
            return $this->json(false, $this->lang['foreign_not_exists']);
        }

        if (!$userInfo['isforeign']) {
            return $this->json(false, $this->lang['deny_remove_user_in_foreign']);
        }

        $role = $userInfo['role'];
        $tuduAddr = array(
            'to' => array(),
            'cc' => array()
        );

        foreach ($tudu->to as $k => $item) {
            if (($userInfo['accepterinfo'][3] == $k && !is_int($k))
                || ($userInfo['accepterinfo'][0] == $k && !$k))
            {
                continue ;
            }

            $tuduAddr['to'][] = implode(' ', array(
                !is_int($k) ? $k : '',
                $item[0]
            ));
        }

        foreach ($tudu->cc as $k => $item) {
            if (($userInfo['accepterinfo'][3] == $k && !is_int($k))
                || ($userInfo['accepterinfo'][0] == $k && !$k))
            {
                continue ;
            }

            $tuduAddr['cc'][] = implode(' ', array(
                !is_int($k) ? $k : '',
                $item[0]
            ));
        }

        // 删除标签
        foreach ($tudu->labels as $labelId) {
            $this->manager->deleteLabel($tuduId, $uniqueId, $labelId);
        }

        // 添加用户
        if (!$this->manager->deleteUser($tuduId, $uniqueId)) {
            return $this->json(false, $this->lang['remove_foreign_failure']);
        }

        // 更新图度用户
        $this->manager->updateTudu($tuduId, array(
            'to' => implode("\n", $tuduAddr['to']),
            'cc' => implode("\n", $tuduAddr['cc'])
        ));

        // 是接收人，需要重新统计完成率
        if ($role == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER) {
            // 更新转发编辑后的任务进度
            $this->manager->updateProgress($tuduId, $uniqueId, null);

            if ($tudu->parentId) {
                $this->manager->calParentsProgress($tudu->parentId);
            }
        }

        return $this->json(true, $this->lang['remove_foreign_success']);
    }

    /**
     * 删除图度
     */
     public function deleteAction()
     {
         // 删除图度权限
         if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU)) {
             return $this->json(false, $this->lang['perm_deny_delete_tudu']);
         }

         $tuduIds = (array) $this->_request->getParam('tid');

         // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        // 获得图度数据
        $tudus  = $this->manager->getTudusByIds($tuduIds);
        $boards = $this->getBoards(false);
         $trueTuduIds = array();  //用于记录删除成功的图度ID

         foreach ($tudus as $tudu) {
             if (!$boards[$tudu->boardId]) {
                 continue;
             }
             // 版主与超级版主的权限检测
             $isModerator = array_key_exists($this->_user->userId, $boards[$tudu->boardId]['moderators']);
             $isSuperModerator = false;
             if (!empty($boards[$tudu->boardId]['parentid'])) {
                 $parentId = $boards[$tudu->boardId]['parentid'];
                 $isSuperModerator = array_key_exists($this->_user->userId, $boards[$parentId]['moderators']);
             }

             if ($tudu->sender == $this->_user->userName || $isModerator || $isSuperModerator) {
                 // 当删除的事图度组的时候，判断图度组下是否有子图度
                 if ($tudu->isTuduGroup && $this->manager->getChildrenCount($tudu->tuduId) > 0) {
                     return $this->json(false, sprintf($this->lang['delete_not_null_tudugroup'], $tudu->subject));
                 }

                 // 删除操作
                 $ret = $this->manager->deleteTudu($tudu->tuduId);
                 if ($ret) {
                     // 记录删除成功的图度ID
                     $trueTuduIds[] = $tudu->tuduId;

                     if ($tudu->parentId) {
                         // 计算父级图度的进度
                         $this->manager->calParentsProgress($tudu->parentId);
                        // 更新节点信息
                         if ($this->manager->getChildrenCount($tudu->parentId) <= 0) {
                             $this->manager->updateNode($tudu->parentId, array(
                                'type' => Dao_Td_Tudu_Group::TYPE_LEAF
                             ));
                         }
                     }

                     // 添加操作日志
                    $this->_writeLog(
                        Dao_Td_Log_Log::TYPE_TUDU,
                        $tudu->tuduId,
                        Dao_Td_Log_Log::ACTION_DELETE
                    );
                 }
             }
         }

         if (!count($trueTuduIds)) {
             return $this->json(false, $this->lang['tudu_delete_failure']);
         }

         return $this->json(true, $this->lang['tudu_delete_success'], $trueTuduIds);
     }

     /**
      * 删除回复
      */
     public function deletePostAction()
     {
         $tuduId = $this->_request->getParam('tid');
         $postId = $this->_request->getParam('pid');

         // 参数：图度ID必须存在
         if (!$tuduId) {
             return $this->json(false, $this->lang['invalid_tuduid']);
         };
         // 参数：回复ID必须存在
         if (!$postId) {
             return $this->json(false, $this->lang['invalid_postid']);
         };
         // 删除回复权限
         if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_POST)) {
             return $this->json(false, $this->lang['perm_deny_delete_post']);
         }

         $post = $this->manager->getPostById($tuduId, $postId);
         // 回复必须存在
         if (!$post) {
             return $this->json(true, $this->lang['post_delete_success']);
         }
         // 提交删除回复不能是图度内容
         if ($post->isFirst) {
             return $this->json(false, $this->lang['forbid_delete_first_post']);
         }
         // 非回复者，检查版主权限
         if ($post->uniqueId != $this->_user->uniqueId) {
             $boards = $this->getBoards(false);

             if (!isset($boards[$post->boardId])
                 || !array_key_exists($this->_user->userId, $boards[$post->boardId]['moderators'])) {
                 return $this->json(false, $this->lang['perm_deny_delete_post']);
            }
         }

         $ret = $this->manager->deletePost($tuduId, $postId);
         if (!$ret) {
             return $this->json(true, $this->lang['post_delete_failure']);
         }

         // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_POST,
            $postId,
            Dao_Td_Log_Log::ACTION_DELETE
        );

        return $this->json(true, $this->lang['post_delete_success']);
     }

     /**
      * 丢弃草稿
      */
     public function discardAction()
     {
         $tuduIds = (array) $this->_request->getParam('tid');

         // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        // 获得图度数据
        $tudus = $this->manager->getTudusByIds($tuduIds)->toArray();
        $trueTuduIds = array();  //用于记录删除成功的图度ID

        foreach ($tudus as $tudu) {
            // 当前图度必须是草稿，且操作人必须图度的发起人
            if ($tudu['isdraft'] && strcasecmp($tudu['sender'], $this->_user->userName) == 0) {
                // 当前图度是图度组
                if ($tudu['istudugroup']) {
                    $children = $this->manager->getTudus(array('parentid' => $tudu['tuduid']))->toArray();

                    foreach ($children as $child) {
                        // 执行删除子图度操作
                         $this->manager->deleteTudu($child['tuduid']);
                     }
                }
                // 执行删除操作
                 $this->manager->deleteTudu($tudu['tuduid']);
                 $trueTuduIds[] = $tudu['tuduid'];
            }
        }

        if (!count($trueTuduIds)) {
             return $this->json(false, $this->lang['tudu_delete_failure']);
         }

         return $this->json(true, null, $trueTuduIds);
     }

     /**
      * 移动图度
      */
     public function moveAction()
     {
         $tuduIds     = explode(',', $this->_request->getPost('tid'));
         $fromBoardId = $this->_request->getPost('fbid');
         $boardId     = $this->_request->getPost('bid');
         $classId     = $this->_request->getPost('cid', null);

         // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['not_selected_board']);
        }

         $boards = $this->getBoards(false);

         // 来源版块必须存在
         if (!isset($boards[$fromBoardId])) {
             return $this->json(false, $this->lang['from_board_not_exists']);
         }

         $fromBoard = $boards[$fromBoardId];

         $isModerator = array_key_exists($this->_user->userId, $fromBoard['moderators']);
         $isSuperModerator = false;

         if ($fromBoard['parentid']) {
             $fromZone = $boards[$fromBoard['parentid']];

             $isSuperModerator = array_key_exists($this->_user->userId, $fromZone['moderators']);
         }

         // 操作人必须同时为源版块和目标版块的版主
         if (!$isModerator && !$isSuperModerator) {
             return $this->json(false, $this->lang['deny_to_move_tudu']);
         }

         // 来源版块和目标版块不能是同一版块
         if ($boardId == $fromBoardId) {
             return $this->json(false, $this->lang['move_to_current_board']);
         }

         // 目前版块必须存在
         if (!isset($boards[$boardId])) {
             return $this->json(false, $this->lang['target_board_not_exists']);
         }

         $success = 0;
         foreach ($tuduIds as $tuduId) {
             $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);

             if (null === $tudu || $tudu->boardId != $fromBoardId) {
                 continue ;
             }

             if ($this->manager->moveTudu($tuduId, $boardId, $classId)) {
                 $success++;
             }
         }

         if ($success <= 0) {
             return $this->json(false, $this->lang['tudu_move_failure']);
         }

         return $this->json(true, $this->lang['tudu_move_success']);
     }

     /**
      * 标签操作
      */
     public function labelAction()
     {
         $tuduIds = (array) $this->_request->getParam('tid');
         // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

         $alias    = (array) $this->_request->getParam('label');
         $fun      = $this->_request->getParam('fun');
         $uniqueId = $this->_user->uniqueId;
         $labels   = $this->getLabels();
         $labelIds = array();

         foreach ($alias as $a) {
             if (isset($labels[$a]) && !$labels[$a]['issystem']) {
                 $labelIds[] = $labels[$a]['labelid'];
             }
         }

         switch ($fun) {
             // 增加标签
             case 'add':
                 foreach ($tuduIds as $tuduId) {
                     foreach ($labelIds as $labelId) {
                         $this->manager->addLabel($tuduId, $uniqueId, $labelId);
                     }
                 }
                 break;

             // 删除标签
             case 'del':
                 foreach ($tuduIds as $tuduId) {
                     foreach ($labelIds as $labelId) {
                         $this->manager->deleteLabel($tuduId, $uniqueId, $labelId);
                     }
                 }
                 break;

             // 移除所有标签
             case 'remove':
                 foreach ($tuduIds as $tuduId) {
                     $tudu = $this->manager->getTuduById($tuduId, $uniqueId);

                     foreach ($tudu->labels as $labelId) {
                         if (strpos($labelId, '^') === false) {
                             $this->manager->deleteLabel($tuduId, $uniqueId, $labelId);
                         }
                     }
                 }
                 break;

             default:
                 break;
         }

         /*if ($fun != 'remove') {
             $this->_labels = null;
            $labels = $this->getLabels(null);
         }*/
         return $this->json(true, $this->lang[$fun . '_label_success']);
     }

     /**
      * 标记为
      */
     public function markAction()
     {
         $tuduIds  = (array) $this->_request->getParam('tid');
         $alias    = (array) $this->_request->getParam('label');
         $isRead   = $this->_request->getParam('read');
         $fun      = $this->_request->getParam('fun');
        $uniqueId = $this->_user->uniqueId;
         $data     = null;

         switch ($fun) {
             case 'read':
             case 'unread':
                 // 参数：图度ID必须存在
                 if (!count($tuduIds)) {
                    return $this->json(false, $this->lang['invalid_tuduid']);
                }

                foreach ($tuduIds as $tuduId) {
                     $this->manager->markRead($tuduId, $uniqueId, ('read' == $fun));
                 }

                 break;

             case 'allread':
                 // 参数：标签ID必须存在
                 if (!count($alias)) {
                     return $this->json(false, $this->lang['invalid_labelid']);
                 }

                 $labelIds = array();
                 $labels   = $this->getLabels();
                 foreach ($alias as $a) {
                     if (isset($labels[$a]) && (!$labels[$a]['issystem'] || $fun == 'allread')) {
                         $labelIds[] = $labels[$a]['labelid'];
                     }
                 }

                 // 设置标签下所有图度为已读
                 foreach ($labelIds as $labelId) {
                     $this->manager->markLabelRead($labelId, $uniqueId, $isRead);
                 }

                 // 统计标签图度数
                 foreach ($labels as $label) {
                     $this->manager->calculateLabel($uniqueId, $label['labelid']);
                 }
                break;

            default:
                 break;
         }

         $data = null;
         if ($fun != 'allread') {
             $this->_labels = null;
             $labels = $this->getLabels(null);
             $data   = array();
            foreach ($labels as $index => $label) {
                // 过滤“所有图度”与“已审批”标签
                if ($labels[$index]['labelalias'] == 'all' || $labels[$index]['labelalias'] == 'reviewed') {
                    continue ;
                }

                $labels[$index]['labelname'] = $labels[$index]['issystem']
                                             ? $this->lang['label_' . $labels[$index]['labelalias']]
                                             : $labels[$index]['labelalias'];

                $data[] = $labels[$index];
            }
         }

         return $this->json(true, $this->lang['mark_success'], $data);
     }

     /**
      * 星标操作
      */
     public function starAction()
     {
         $tuduIds   = (array) $this->_request->getParam('tid');
         $fun      = $this->_request->getParam('fun');;
         $uniqueId = $this->_user->uniqueId;

         // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $sysLabel = $this->bootstrap->getOption('tudu');
        $sysLabel = $sysLabel['label'];
        $success  = 0; //用于计数操作成功个数

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        switch ($fun) {
            // 添加星标
            case 'star':
                foreach ($tuduIds as $tuduId) {
                    $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId, array());
                    if ($tudu) {
                        if ($tudu->uniqueId != $this->_user->uniqueId) {
                            $daoTudu->addUser($tuduId, $this->_user->uniqueId);
                        }
                        $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['starred']);
                        $success ++;
                    }
                }
                break ;

            // 取消星标
            case 'unstar':
                foreach ($tuduIds as $tuduId) {
                    $this->manager->deleteLabel($tuduId, $uniqueId, $sysLabel['starred']);
                    $success ++;
                }
                break ;

            default:
                break ;
        }

        if ($success <= 0) {
            return $this->json(false);
        }

        $labels = $this->getLabels(null);
        $data   = array();
        foreach ($labels as $index => $label) {
            // 过滤“所有图度”与“已审批”标签
            if ($labels[$index]['labelalias'] == 'all' || $labels[$index]['labelalias'] == 'reviewed') {
                continue ;
            }

            $labels[$index]['labelname'] = $labels[$index]['issystem']
                                         ? $this->lang['label_' . $labels[$index]['labelalias']]
                                         : $labels[$index]['labelalias'];

            $data[] = $labels[$index];
        }

        return $this->json(true, null, $data);
     }

     /**
     * 忽略
     */
    public function ignoreAction()
    {
        $tuduIds  = (array) $this->_request->getParam('tid');
        $type     = $this->_request->getParam('type');
        $uniqueId = $this->_user->uniqueId;

        // 参数：图度ID必须存在
         if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $sysLabel = $this->bootstrap->getOption('tudu');
        $sysLabel = $sysLabel['label'];
        $success  = 0; //用于计数操作成功个数

        // 移除忽略标签
        if ($type == 'remove') {
            foreach ($tuduIds as $tuduId) {
                $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
                if (null === $tudu) {
                    continue;
                }

                // 忽略的任务，可返回到图度箱
                if ($tudu->type == 'task') {
                    $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['inbox']);  //添加图度箱标签
                }

                $this->manager->deleteLabel($tuduId, $uniqueId, $sysLabel['ignore']);  //删除忽略标签
                $success ++;
            }

        // 添加忽略标签
        } else {
            $tids = array_values($tuduIds);
            if (strlen($tids[0]) > 0) {
                foreach ($tuduIds as $tuduId) {
                    $this->manager->deleteLabel($tuduId, $uniqueId, $sysLabel['inbox']);  //移除图度箱标签
                    $this->manager->deleteLabel($tuduId, $uniqueId, $sysLabel['todo']);   //移除我执行标签
                    $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['ignore']);    //添加忽略标签
                    $success ++;
                }
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['ignore_failure']);
        }

        return $this->json(true, $this->lang['ignore_success']);
    }

    /**
     * 添加到图度箱
     */
    public function inboxAction()
    {
        $tuduIds  = (array) $this->_request->getParam('tid');
        $uniqueId = $this->_user->uniqueId;

        // 参数：图度ID必须存在
        if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $sysLabel = $this->bootstrap->getOption('tudu');
        $sysLabel = $sysLabel['label'];
        $sucess   = 0;  //用于计数操作成功个数

        foreach ($tuduIds as $tuduId) {
            // 获得图度信息
            $tudu = $this->manager->getTuduById($tuduId, $uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue ;
            }

            $this->manager->deleteLabel($tuduId, $uniqueId, $sysLabel['ignore']); //删除忽略标签
            $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['all']);       //添加所有图度标签
            $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['inbox']);     //添加到图度箱

            // 如果是图度任务，且操作人系图度执行，添加到“我执行”
            if ($tudu->type == 'task' && in_array($this->_user->userName, $tudu->accepter, true)) {
                $this->manager->addLabel($tuduId, $uniqueId, $sysLabel['todo']); //添加到我执行
            }

            $sucess ++;
        }

        if ($sucess <= 0) {
            return $this->json(false, $this->lang['']);
        }

        return $this->json(true, $this->lang['inbox_success']);
    }

    /**
     * 接受任务
     */
    public function acceptAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tid'));

        // 参数：图度ID必须存在
        if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $success = 0;  //用于计数操作成功个数
        foreach ($tuduIds as $tuduId) {
            // 获得图度数据
            $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
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
            $isAccepter = in_array($this->_user->userName, $tudu->accepter);
            // 会议执行人有群组
            if ($tudu->type == 'meeting') {
                foreach ($tudu->accepter as $item) {
                    if ($isAccepter) break;
                    if (strpos($item, '^') == 0) {
                        $isAccepter = in_array($item, $this->_user->groups, true);
                    }
                }
            }
            if (!$isAccepter) {
                continue ;
            }

            $ret = $this->manager->acceptTudu($tuduId, $this->_user->uniqueId, (int) $tudu->selfPercent);

            // 更新任务进度
            $this->manager->updateProgress($tuduId, $this->_user->uniqueId, $tudu->selfPercent);

            // 计算图度已耗时
            $this->manager->calcElapsedTime($tuduId);

            // 移除待办
            if (in_array('^td', $tudu->labels)) {
                $this->manager->deleteLabel($tudu->tuduId, $this->_user->uniqueId, '^td');
                $this->manager->deleteLabel($tudu->tuduId, $tudu->uniqueId, '^td');
            }

            if ($ret) {
                $success ++; //记录次数

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_ACCEPT,
                    array('accepttime' => time(), 'status' => Dao_Td_Tudu_Tudu::STATUS_DOING)
                );
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['accept_failure']);
        }

        return $this->json(true, $this->lang['accept_success']);
    }

    /**
     * 拒绝图度
     */
    public function rejectAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tid'));

        // 参数：图度ID必须存在
        if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        /* @var $daoFlow Dao_Td_Tudu_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

        /* @var $daoTudu Dao_Td_Tudu_Group */
        $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

        $resourceManager = new Tudu_Model_ResourceManager_Registry();
        $resourceManager->setResource(Tudu_Model::RESOURCE_CONFIG, $this->bootstrap->getOptions());

        Tudu_Model::setResourceManager($resourceManager);

        $success = 0;  //用于计数操作成功个数
        foreach ($tuduIds as $tuduId) {
            // 获得图度数据
            $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
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
            $isAccepter = in_array($this->_user->userName, $tudu->accepter);
            // 会议执行人有群组
            if ($tudu->type == 'meeting') {
                foreach ($tudu->accepter as $item) {
                    if ($isAccepter) break;
                    if (strpos($item, '^') == 0) {
                        $isAccepter = in_array($item, $this->_user->groups, true);
                    }
                }
            }
            if (!$isAccepter) {
                continue ;
            }

            $isFlow     = !empty($tudu->flowId) ? true : false;
            $tuduStatus = $daoTudu->rejectTudu($tuduId, $this->_user->uniqueId, $isFlow);
            if (false !== $tuduStatus) {
                $success ++; //记录次数

                $updateTudu = array();
                /*if ($tudu->type == 'task' && empty($tudu->flowId)) { //任务拒绝步骤的指向
                    $updateTudu = array('stepid' => '^head');
                }*/

                // 拒绝后任务状态为完成的，生成周期任务
                if ($tudu->cycleId && $tuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $config = $this->bootstrap->getOption('httpsqs');

                    // 插入消息队列
                    $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

                    $updateTudu = array('cycleid' => null);

                    $data = implode(' ', array(
                        'tudu',
                        'cycle',
                        '',
                        http_build_query(array(
                            'tuduid' =>  $tudu->tuduId,
                            'tsid' => $this->_user->tsId,
                            'cycleid' => $tudu->cycleId
                        ))
                    ));
                    $httpsqs->put($data, 'tudu');
                }

                if (!empty($updateTudu)) {
                    $daoTudu->updateTudu($tuduId, $updateTudu);
                }

                $tudu = $tudu->toArray();
                // 工作流 拒绝 步骤就拒绝
                if ($tudu['flowid']) {
                    $tudu['cc'] = null;

                    $flowRecord  = $daoFlow->getFlow(array('tuduid' => $tudu['tuduid']));
                    $flow        = new Model_Tudu_Extension_Flow($flowRecord->toArray());

                    $composeTudu = new Model_Tudu_Tudu(array(
                        'tuduid' => $tudu['tuduid'],
                        'status' => 1,
                        'type'   => $tudu['type'],
                        'flowid' => $tudu['flowid']
                    ));

                    if (isset($flow->steps[$flow->currentStepId])) {
                        $step    = $flow->steps[$flow->currentStepId];
                        $prevId  = $step['prev'];
                        $isFrowardStep = false;

                        // 工作流中转发的步骤
                        // 如果所有人拒绝，则删除
                        if (0 !== strpos($tudu['stepid'], 'F-')) {
                            $flow->deleteStep($flow->currentStepId);

                            $isFrowardStep = true;
                        } else {
                            // 更新当前步骤状态
                            $flow->reject($flow->currentStepId, $this->_user->userName);
                        }

                        if (isset($flow->steps[$prevId])) {
                            $prev         = $flow->steps[$prevId];
                            $updateStatus = $isFrowardStep;
                            $nextIndex    = null;

                            // 上一步骤系审批
                            if ($prev['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                                $reviewer = array();

                                $flow->resetStep($prevId);
                                foreach ($prev['section'] as $idx => $sec) {
                                    foreach ($sec as $i => $u) {
                                        if ($idx == 0) {
                                            $reviewer[$u['username']] = array('username' => $u['username'], 'truename' => $u['truename'], 'uniqueid' => $u['uniqueid']);
                                        }
                                    }
                                }

                                $composeTudu->reviewer = $reviewer;
                            // 上一步骤系执行
                            } else {
                                $to = array();

                                $flow->resetStep($prevId);
                                foreach ($prev['section'] as $idx => $sec) {
                                    foreach ($sec as $i => $u) {
                                        if ($idx == 0) {
                                            $to[$u['username']] = array('username' => $u['username'], 'truename' => $u['truename'], 'uniqueid' => $u['uniqueid']);
                                        }
                                    }
                                }

                                $composeTudu->reviewer = null;
                                $composeTudu->to       = $to;
                            }
                            $stepId = $prev['stepid'];

                        } else {
                            $stepId = $prevId;
                        }
                    } else {
                        $stepId = '^break';
                    }

                    $flow->flowTo($stepId);
                    $composeTudu->stepId = $stepId;

                    if ($stepId == '^break' || $stepId == '^head') {
                        $composeTudu->to = array(
                            $tudu['from'][3] => array('username' => $tudu['from'][3], 'truename' => $tudu['from'][0])
                        );
                    }

                    // 准备发送
                    //$modelCompose = new Model_Tudu_Compose_Save($tudu);
                    $modelSend    = new Model_Tudu_Send_Common();
                    //$recipients = $deliver->prepareRecipients($this->_user->uniqueId, $this->_user->userId, $tudu);

                    // 移除原执行人
                    if ($composeTudu->to && !$composeTudu->reviewer) {
                        $accepters = $daoTudu->getAccepters($tudu['tuduid']);
                        $to        = $composeTudu->to;
                        foreach ($accepters as $item) {
                            list($email, ) = explode(' ', $item['accepterinfo'], 2);

                            // 移除执行人角色，我执行标签
                            if (!empty($to) && !array_key_exists($email, $to)
                                    && $daoTuduGroup->getChildrenCount($tudu['tuduid'], $item['uniqueid']) <= 0)
                            {
                                $daoTudu->removeAccepter($tudu['tuduid'], $item['uniqueid']);
                                $this->manager->deleteLabel($tudu['tuduid'], $item['uniqueid'], '^a');
                            }
                        }
                    }

                    // 执行人自动接受图度
                    $currentStep = $flow->getStep($flow->currentStepId);
                    if ($stepId != '^break' && $stepId != '^head' && isset($steps[$stepId]) && $currentStep['type'] != Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                        if ($currentStep['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                            foreach ($currentStep['section'][0] as $item) {
                                $daoTudu->acceptTudu($tudu['tuduid'], $item['uniqueid'], null);
                            }
                            $updateParams['acceptmode'] = 0;
                        } else if ($currentStep['type'] == Dao_Td_Tudu_Step::TYPE_CLAIM) {
                            $composeTudu->acceptMode = 1;
                            $composeTudu->acceptTime = null;
                        }
                    }
                    //$this->manager->updateTudu($tudu->tuduId, $updateParams);

                    $daoFlow->updateFlow($flow->tuduId, $flow->toArray());
                    //$modelCompose->compose($composeTudu);
                    $params = $composeTudu->getStorageParams();
                    $daoTudu->updateTudu($tudu['tuduid'], $params);
                    $modelSend->send($composeTudu, true);

                    $daoTudu->markAllUnRead($tudu['tuduid']);
                    $daoTudu->updateFlowProgress($tudu['tuduid'], null, $flow->currentStepId);
                
                // 不是自动工作流，回到发起
                } else {
                	$flowRecord  = $daoFlow->getFlow(array('tuduid' => $tudu['tuduid']));
                	$flow        = new Model_Tudu_Extension_Flow($flowRecord->toArray());
                	
                	$flow->flowTo('^head');
                	
                	$daoFlow->updateFlow($tudu['tuduid'], $flow->toArray());
                }

                if ($tudu['parentid']) {
                    $daoTudu->calParentsProgress($tudu['parentid']);
                }

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    Dao_Td_Log_Log::ACTION_TUDU_DECLINE,
                    array('selfstatus' => Dao_Td_Tudu_Tudu::STATUS_REJECT, 'status' => $tuduStatus)
                );
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['reject_failure']);
        }

        return $this->json(true, $this->lang['reject_success']);
    }

    /**
     * 终止（取消）任务
     */
    public function cancelAction()
    {
        $tuduId = $this->_request->getParam('tid');

        // 参数：图度ID必须存在
        if (!$tuduId) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
        // 图度必须存在
        if (null == $tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
        // 图度不能是已确定状态
        if ($tudu->isDone) {
            return $this->json(false, $this->lang['tudu_is_done']);
        }
        // 操作人必须为图度发起人
        if ($tudu->sender != $this->_user->userName) {
            return $this->json(false, $this->lang['perm_deny_cancel_tudu']);
        }
        // 执行终止（取消）操作
        $ret = $this->manager->cancelTudu($tuduId, true, '', $tudu->parentId);
        if (!$ret) {
            return $this->json(false, $this->lang['']);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $tuduId,
            Dao_Td_Log_Log::ACTION_TUDU_CANCEL,
            array('accepttime' => null, 'status' => Dao_Td_Tudu_Tudu::STATUS_CANCEL, 'isdone' => true, 'score' => '')
        );

        $config = $this->bootstrap->getOption('httpsqs');

        // 插入消息队列
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
        // 发送外部邮件（如果有），处理联系人
        $data = implode(' ', array(
            'send',
            'tudu',
            '',
            http_build_query(array(
                'tsid'   => $this->_user->tsId,
                'tuduid' => $tuduId,
                'uniqueid' => $this->_user->uniqueId,
                'to'       => '',
                'act'      => 'cancel'
            ))
        ));

        // 发送取消通知
        if ($tudu->type == 'meeting') {

            $tpl = <<<HTML
<strong>会议已取消</strong><br />
<a href="http://{$this->_request->getServer('HTTP_HOST')}/frame#m=view&tid=%s&page=1" target="_blank">%s</a><br />
发起人：{$tudu->from[0]}<br />
%s
HTML;
            $data = implode(' ', array(
                'tudu',
                'cancel',
                '',
                http_build_query(array(
                    'tuduid' =>  $tuduId,
                    'from' => $tudu->from[3],
                    'to' => implode(',', $tudu->accepter),
                    'content' => sprintf($tpl, $tudu->tuduId, $tudu->subject, date('Y-m-d H:i:s', time()), mb_substr(strip_tags($tudu->content), 0, 20, 'utf-8'))
                    ))
                ));

            $httpsqs->put($data, 'im');
        }

        $httpsqs->put($data, 'send');

        return $this->json(true, $this->lang['cancel_success']);
    }

    /**
     * 认领图度
     */
    public function claimAction()
    {
        $tuduId = trim($this->_request->getParam('tid'));

        $resourceManager = new Tudu_Model_ResourceManager_Registry();
        $resourceManager->setResource(Tudu_Model::RESOURCE_CONFIG, $this->bootstrap->getOptions());

        Tudu_Model::setResourceManager($resourceManager);

        $model = new Model_Tudu_Manager_Tudu();

        try {
            $model->claim(array('tuduid' => $tuduId));
        } catch (Model_Tudu_Exception $e) {
            $err = $this->lang['tudu_claim_failed'];
            switch ($e->getCode()) {
                case Model_Tudu_Manager_Tudu::CODE_INVALID_TUDUID:
                    $err = $this->lang['tudu_not_exists'];
                    break ;
                case Model_Tudu_Manager_Tudu::CODE_STEP_NOTCLAIM:
                    $err = $this->lang['step_not_claim'];
                    break ;
                case Model_Tudu_Manager_Tudu::CODE_STEP_CLAIM_FINISH:
                    $err = $this->lang['tudu_has_already_claim'];
                    break ;
            }

            return $this->json(false, $err);
        }

        return $this->json(true, $this->lang['tudu_claim_success']);
    }

    /**
     * 关闭/重开图度（讨论）
     */
    public function closeAction()
    {
        $tuduId  = $this->_request->getParam('tid');
        $isClose = (boolean) $this->_request->getParam('isclose');
        // 参数：图度ID必须存在
        if (!$tuduId) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
        // 图度必须存在
        if (null == $tudu) {
            return $this->json(false, $this->lang['tudu_not_exists']);
        }
        // 图度不能是已确定状态
        if ($tudu->isDone && $isClose) {
            return $this->json(false, $this->lang['discuss_is_closed']);
        }
        // 操作人必须为图度发起人
        if ($tudu->sender != $this->_user->userName) {
            return $this->json(false, $this->lang['perm_deny_close_tudu']);
        }

        // 执行关闭/重开图度操作
        $ret = $this->manager->closeTudu($tuduId, $isClose);
        if (!$ret) {
            return $this->json(false, $this->lang['']);
        }

        // 添加操作日志
        $this->_writeLog(
            Dao_Td_Log_Log::TYPE_TUDU,
            $tuduId,
            ($isClose ? Dao_Td_Log_Log::ACTION_CLOSE : Dao_Td_Log_Log::ACTION_OPEN),
            array('isdone' => $isClose)
        );

        return $this->json(true, $this->lang['state_success']);
    }

    /**
     * 完成 （确认）图度
     */
    public function doneAction()
    {
        $tuduIds = explode(',', $this->_request->getParam('tid'));
        $isDone = (boolean) $this->_request->getParam('isdone');
        $score  = (int) $this->_request->getParam('score');

        // 参数：图度ID必须存在
        if (!count($tuduIds)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        $success = 0;  //用于计数操作成功个数
        foreach ($tuduIds as $tuduId) {
            // 获得图度数据
            $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
            // 图度必须存在
            if (null == $tudu) {
                continue ;
            }
            // 图度不能是已确定状态
            if ($tudu->isDone && $isDone) {
                continue ;
            }
            // 操作人必须为图度发起人
            if ($tudu->sender != $this->_user->userName) {
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
            $ret = $this->manager->doneTudu($tuduId, $isDone, $score, false, ($tudu->parentId != null), $tudu->type);
            if ($ret) {
                $success ++;

                $config = $this->bootstrap->getOption('httpsqs');

                if ($isDone) {
                    // 插入消息队列
                    $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
                    // 发送外部邮件（如果有），处理联系人
                    $data = implode(' ', array(
                        'send',
                        'tudu',
                        '',
                        http_build_query(array(
                            'tsid'   => $this->_user->tsId,
                            'tuduid' => $tuduId,
                            'uniqueid' => $this->_user->uniqueId,
                            'to'       => '',
                            'act'      => 'confirm'
                        ))
                    ));

                    $httpsqs->put($data, 'send');
                }

                // 添加操作日志
                $this->_writeLog(
                    Dao_Td_Log_Log::TYPE_TUDU,
                    $tuduId,
                    ($isDone ? Dao_Td_Log_Log::ACTION_TUDU_DONE : Dao_Td_Log_Log::ACTION_TUDU_UNDONE),
                    array('isdone' => $isDone, 'score' => $score)
                );
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['state_failure']);
        }

        return $this->json(true, $this->lang['state_success']);
    }

    /**
      * 投票
      */
    public function voteAction()
    {
        $tuduId = $this->_request->getPost('tuduid');
        $voteId = $this->_request->getPost('voteid');
        $option = (array) $this->_request->getPost('option-' . $voteId);

        if (!$tuduId) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        if (!$voteId) {
            return $this->json(false, 'invalid_voteid');
        }

        if (empty($option)) {
            return $this->json(false, $this->lang['no_selected_option']);
        }

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));

        if (!$vote || (!empty($vote->expireTime) && $vote->expireTime + 86400 < time())) {
            return $this->json(false, $this->lang['vote_is_invalid']);
        }

        if ($vote->maxChoices != 0 && count($option) > $vote->maxChoices) {
            return $this->json(false, sprintf($this->lang['more_vote_option'], $vote->maxChoices));
        }

        $tudu = $this->getDao('Dao_Td_Tudu_Tudu')->getTuduById($this->_user->uniqueId, $tuduId);
        if($tudu->isDone) {
            return $this->json(false, $this->lang['vote_is_close']);
        }

        $voter = $this->_user->userName . ' ' . $this->_user->trueName;
        $ret = $daoVote->vote($tuduId, $voteId, $option, $this->_user->uniqueId, $voter);

        if (!$ret) {
            return $this->json(false, $this->lang['vote_failure']);
        }

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));
        $vote->getOptions();
        $vote->countVoter();

        return $this->json(true, $this->lang['vote_success'], $vote->toArray());
   }

   /**
    * 合并到图度组
    */
   public function mergeGroupAction()
   {
       $tuduId   = $this->_request->getPost('tid');
       $targetId = $this->_request->getPost('targetid');
       // 目标图度组ID 图度ID
       if (!$targetId || !$tuduId) {
           return $this->json(false, $this->lang['invalid_tuduid']);
       }

       $targetTudu = $this->manager->getTuduById($targetId, $this->_user->uniqueId);
       // 目标图度组必须存在
       if (null == $targetTudu) {
           return $this->json(false, $this->lang['target_tudugroup_not_exists']);
       }

       $tudu = $this->manager->getTuduById($tuduId, $this->_user->uniqueId);
       // 图度必须存在
       if (null == $tudu) {
           return $this->json(false, $this->lang['tudu_not_exists']);
       }

       // 当前图度发起人是否为目标的图度组的执行人
       $isAccepter  = in_array($tudu->sender, $targetTudu->accepter, true);
       /* @var $addressBook Tudu_AddressBook */
       $addressBook = Tudu_AddressBook::getInstance();
       $user        = $addressBook->searchUser($this->_user->orgId, $tudu->sender);

       if (!$isAccepter) {
           $ret = $this->manager->addRecipient($targetId, $user['uniqueid'], array(
               'role'         => 'to',
               'accepterinfo' => $user['email'] . ' ' . $user['truename'],
               'tudustatus'   => 1
           ));

           if (!$ret) {
               return $this->json(true, $this->lang['append_tudu_group_failed']);
           }

           $this->manager->acceptTudu($targetId, $user['uniqueid'], null);
           $to = array_merge($targetTudu->accepter, (array) $tudu->sender);
           $this->manager->updateTudu($targetId, array('to' => implode("\n", $to)));

           $stepUser = array(array(
               'uniqueid'     => $user['uniqueid'],
               'accepterinfo' => $user['email'] . ' ' . $user['truename'],
               'processindex' => 0,
               'stepstatus'   => 0,
               'percent'      => 0
           ));
           $this->manager->addStepUsers($targetId, $targetTudu->stepId, $stepUser);

           $this->manager->addLabel($targetId, $user['uniqueid'], '^all');
           $this->manager->addLabel($targetId, $user['uniqueid'], '^i');
           $this->manager->addLabel($targetId, $user['uniqueid'], '^a');
       }

       /* @var $daoGroup Dao_Td_Tudu_Group */
       $daoGroup = $this->getDao('Dao_Td_Tudu_Group');

       $ret = $daoGroup->createNode(array(
           'tuduid'   => $tuduId,
           'parentid' => $targetId,
           'uniqueid' => $user['uniqueid'],
           'rootid'   => !empty($targetTudu->rootId) ? $targetTudu->rootId : null,
           'type'     => Dao_Td_Tudu_Group::TYPE_LEAF
       ));

       if (!$ret) {
           return $this->json(true, $this->lang['append_tudu_group_failed']);
       }

       // 计算图度组进度
       $this->manager->calParentsProgress($targetId);
       // 标记所有为未读
       $this->manager->markAllUnRead($targetId);

       return $this->json(true, $this->lang['append_tudu_group_success']);
   }

    /**
     * 格式化收件人
     * array(
     * 'identify' => xxx
     * 'name' => xxx
     * 'extend' => xxx
     * )
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

            $ret[$info[0]] = $info[1];
        }

        return $ret;
    }
}