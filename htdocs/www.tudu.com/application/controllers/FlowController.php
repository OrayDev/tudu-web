<?php
/**
 * Flow Controller
 * 工作流管理控制器
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: FlowController.php 2809 2013-04-07 09:57:05Z cutecube $
 */
class FlowController extends TuduX_Controller_Base
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';

    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'flow', 'tudu'));
        $this->view->LANG = $this->lang;

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     * 工作流列表
     */
    public function indexAction()
    {
        $access = array(
            'create' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_FLOW)
        );

        // 读取用户为版主的板块
        $boards = $this->getBoards(true, true);
        // 有权限创建工作流，但仍需判断是否为版主
        if ($access['create'] && empty($boards)) {
            // 若用户均不是某一板块的负责人或分区负责人，则无权限新建工作流
            $access['create'] = false;
        }

        $flows = $this->getFlows(true);
        $flows = $this->sortFlows($flows);
        $favors = array();

        foreach ($flows as $item) {
            foreach ($item['children'] as $flow) {
                if (isset($flow['weight']) && $flow['weight'] >= 5) {
                    $favors[] = $flow;
                }
            }
        }

        $this->view->favors = $favors;
        $this->view->flows  = $flows;
        $this->view->access = $access;
    }

    /**
     * 搜索工作流
     */
    public function searchAction()
    {
        $keyword = $this->_request->getQuery('keyword');

        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $condition = array(
            'orgid' => $this->_user->orgId,
            'keyword' => $keyword
        );
        $flows = $daoFlow->getFlows($condition, null, 'createtime DESC');

        $flows = $this->inRole($flows->toArray());
        $flows = $this->inAvaliable($flows, false);

        $this->view->keyword = $keyword;
        $this->view->flows   = $flows;
    }

    /**
     * 编辑图度工作流
     */
    public function modifyAction()
    {
        $flowId = $this->_request->getQuery('flowid');

        $flow        = array();
        $perm        = array();
        $attachments = null;
        $countAttach = 0;
        $action      = self::ACTION_CREATE;

        if ($flowId) {
            /* @var $daoFlow Dao_Td_Flow_Flow */
            $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

            // 判读是否具有更新流的权限
            if (!$this->_user->getAccess()->assertEquals(Tudu_Access::PERM_UPDATE_FLOW, true)) {
                Oray_Function::alert($this->lang['perm_deny_update_flow'], '/tudu/?search=inbox');
            }

            // 有权限更新工作流，但仍需判断是否为版主
            if ($this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_FLOW)) {
                $boards = $this->getBoards(true, true);
                // 若用户均不是某一板块的负责人或分区负责人，则无权限新建工作流
                if (empty($boards)) {
                    Oray_Function::alert($this->lang['perm_deny_update_flow'], '/tudu/?search=inbox');
                }
            }

            $flow = $daoFlow->getFlowById($flowId);

            if (null === $flow) {
                Oray_Function::alert($this->lang['flow_not_exists']);
            }

            $flow = $flow->toArray();
            $action = self::ACTION_UPDATE;

            $boards = $this->getBoards(false);
            // 权限
            $isOwner = $this->_user->uniqueId == $flow['uniqueid'];
            $isBoardOwner = $this->_user->userId == $boards[$flow['boardid']]['ownerid'];
            $isBoardModerator = array_key_exists($this->_user->userId, $boards[$flow['boardid']]['moderators']);
            $isSuperModerator = false;
            if (!empty($flow['parentid'])) {
                $isSuperModerator = array_key_exists($this->_user->userId, $boards[$flow['parentid']]['moderators']);
            }
            $flow['access'] = array(
                'modify' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_FLOW) && ($isSuperModerator || $isBoardModerator || $isBoardOwner || $isOwner)
            );

            // 用于模板输出
            foreach ($flow['steps'] as &$step) {
                $step['users']    = $this->_recoveryReviewer($step['sections'], $step['type']);
                $step['username'] = $this->_formatUserName($step['users']);
            }

            // 是否有附件
            /* @var $daoAttachment Dao_Td_Flow_Attachment */
            $daoAttachment = $this->getDao('Dao_Td_Flow_Attachment');

            if ($daoAttachment->existsAttach($flowId, 1)) {
                $attachments = $daoAttachment->getAttachments(array('flowid' => $flowId), array('isattach' => 1));

                $attachments = $attachments->toArray();
                $countAttach = count($attachments);
            }
            $flow['countattach'] = $countAttach;
            $flow['attachments'] = $attachments;
        }

        // 判读是否具有创建工作流的权限
        if ($action == self::ACTION_CREATE) {
            if (!$this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_FLOW, true)) {
                Oray_Function::alert($this->lang['perm_deny_create_flow'], '/tudu/?search=inbox');
            }

            // 有权限新建工作流，但仍需判断是否为版主
            if ($this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_FLOW)) {
                $boards = $this->getBoards(true, true);
                // 若用户均不是某一板块的负责人或分区负责人，则无权限新建工作流
                if (empty($boards)) {
                    Oray_Function::alert($this->lang['perm_deny_create_flow'], '/tudu/?search=inbox');
                }
            }

            $perm['create'] = $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_FLOW);
        }

        $boards = $this->getBoards(true, true);
        $board = null;
        if (!$flow) {
            foreach ($boards as $key => $board) {
                if (!empty($board['children'])) {
                    foreach ($board['children'] as $k => $child) {
                        if ($child['status'] == 2) {
                            unset($boards[$key]['children'][$k]);
                            $boards[$key]['children'] = array_values ($boards[$key]['children']);
                        }
                        // 此处不显示常用板块
                        if (isset($boards[$key]['children'][$k])) {
                            $boards[$key]['children'][$k]['weight'] = null;
                        }
                    }
                }

                if ($board['type'] == 'zone' && empty($board['children'])) {
                    unset($boards[$key]);
                }
            }
        } else {
            foreach ($boards as $key => $zone) {
                if (!empty($zone['children']) && !empty($flow['boardid'])) {
                    foreach ($zone['children'] as $k => $child) {
                        if ($flow['boardid'] == $child['boardid']) {
                            $flow['boardname'] = $child['boardname'];
                            $board = $child;

                            $daoClass = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Class', Tudu_Dao_Manager::DB_TS);

                            $classes = $daoClass->getClasses(array('orgid' => $this->_user->orgId, 'boardid' => $board['boardid']));

                            $this->view->classes = $classes->toArray();
                            break;
                        }
                    }
                }
            }
        }

        $access = $this->_user->getAccess();
        $perm['upload'] = $access->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH);

        $cookies = $this->_request->getCookie();
        $upload = $this->options['upload'];
        $upload['cgi']['upload'] .= '?' . session_name() . '=' . $this->_sessionId
                                 . '&email=' . $this->_user->address;

        $this->view->cookies = serialize($cookies);
        $this->view->upload  = $upload;
        $this->view->boards  = $boards;
        $this->view->access  = $perm;
        $this->view->flow    = $flow;
        $this->view->action  = $action;
        $this->view->uploadsizelimit = $this->options['upload']['sizelimit'] / 1024;
        $this->view->back    = $this->_request->getQuery('back');
        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
    }

    /**
     * 保存图度工作流
     */
    public function saveAction()
    {
        $flowId = $this->_request->getPost('flowid');
        $post   = $this->_request->getPost();

        if (empty($post['bid'])) {
            return $this->json(false, $this->lang['params_invalid_boardid']);
        }

        if (empty($post['subject'])) {
            return $this->json(false, $this->lang['params_invalid_flow_subject']);
        }
        $members = (array) $this->_request->getPost('member');
        $count = count($members);
        if ($count <= 0) {
            return $this->json(false, $this->lang['params_invalid_flow_steps']);
        }

        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');
        /* @var $daoAttachment Dao_Td_Flow_Attachment */
        $daoAttachment = $this->getDao('Dao_Td_Flow_Attachment');

        $params = array(
            'boardid'  => $post['bid'],
            'classid'  => !empty($post['classid']) ? $post['classid'] : null,
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'subject'  => trim($post['subject']),
            'cc'       => !empty($post['cc']) ? $post['cc'] : null,
            'description' => !empty($post['description']) ? trim($post['description']) : null,
            'elapsedtime' => !empty($post['elapsedtime']) ? $post['elapsedtime'] : null
        );

        if (!empty($post['avaliable'])) {
            $params['avaliable'] = $post['avaliable'];
        } else {
            $params['avaliable'] = $this->_user->userName;
        }

        if (!empty($post['content'])) {
            $params['content'] = $post['content'];
        }

        // 处理附件
        $attachment = array();
        if (!empty($post['attach']) && is_array($post['attach'])) {
            foreach ($post['attach'] as $item) {
                $attachment[] = array(
                    'fileid' => $item,
                    'isattach' => true,
                );
            }
            unset($post['attachment']);
        }

        // 处理图片文件
        if (!empty($post['file']) && is_array($post['file'])) {
            foreach ($post['file'] as $item) {
                $attachment[] = array(
                    'fileid' => $item,
                    'isattach' => false,
                );
            }
            unset($post['file']);
        }

        // 处理网盘文件
        if (!empty($post['nd-attach']) && is_array($post['nd-attach'])) {
            /* @var $daoNdFile Dao_Td_Netdisk_File */
            $daoNdFile     = $this->getDao('Dao_Td_Netdisk_File');
            /* @var $daoFile Dao_Td_Attachment_File */
            $daoFile = $this->getDao('Dao_Td_Attachment_File');

            foreach ($post['nd-attach'] as $ndFileId) {
                $attach = $daoAttachment->getAttachment(array('fileid' => $ndFileId));
                if (null !== $attach) {
                    $attachment[] = array(
                        'fileid' => $ndFileId,
                        'isattach' => true
                    );
                    continue ;
                }

                $file = $daoNdFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $ndFileId));
                if ($file) {
                    $fid = $daoFile->createFile(array(
                        'uniqueid' => $this->_user->uniqueId,
                        'fileid'   => $ndFileId,
                        'orgid'    => $this->_user->orgId,
                        'filename' => $file->fileName,
                        'path'     => $file->path,
                        'type'     => $file->type,
                        'size'     => $file->size,
                        'createtime' => time()
                    ));

                    if ($fid) {
                        $attachment[] = array(
                            'fileid' => $ndFileId,
                            'isattach' => true
                        );
                    }
                }
            }
        }

        // 创建
        if (!$flowId) {
            $params['flowid']     = Dao_Td_Flow_Flow::getFlowId();
            $params['createtime'] = time();
            $params['steps']      = $this->formatSteps($post);

            $ret    = $daoFlow->createFlow($params);
            $flowId = $ret;

            // 添加附件关联
            if (!empty($attachment)) {
                foreach ($attachment as $attach) {
                    $daoAttachment->addAttachment($flowId, $attach['fileid'], (boolean) $attach['isattach']);
                }
            }

            $avalible     = explode("\n", $params['avaliable']);
            $updateGroups = true;
        // 更新
        } else {
            $updateAttach = false;
            $flow         = $daoFlow->getFlowById($flowId);
            // 修改的工作流不存在或已被删除
            if (null === $flow) {
                Oray_Function::alert($this->lang['flow_not_exists']);
            }
            // 检查是否已有图度使用了本工作流
            $isValid = $daoFlow->isValidFlow($flowId);
            if ($isValid) {
                // 比较步骤流程
                $isNew = $this->compareSteps($this->formatSteps($post), $flow->steps);
                // 流程发生变化创建新的工作流
                if ($isNew) {
                    // 更新is_valid字段
                    $daoFlow->updateFlow($flowId, array('isvalid' => 0));

                    $params['flowid'] = Dao_Td_Flow_Flow::getFlowId();
                    $params['steps']  = $this->formatSteps($post, $flowId);
                    // 创建新的工作流
                    $ret    = $daoFlow->createFlow($params);
                    $flowId = $ret;

                    // 附件处理
                    if ($daoAttachment->existsAttach($flow->flowId)) {
                        $attachments = $daoAttachment->getAttachments(array('flowid' => $flow->flowId));
                        foreach ($attachments as $attach) {
                            $daoAttachment->addAttachment($flowId, $attach->fileId, $attach->isAttach);
                        }
                    }
                // 流程没有变化
                } else {
                    $params['steps'] = $this->formatSteps($post);
                    $ret             = $daoFlow->updateFlow($flowId, $params);
                    $updateAttach    = true;
                }
            // 流程没有使用过，直接保存
            } else {
                $params['steps'] = $this->formatSteps($post);
                $ret             = $daoFlow->updateFlow($flowId, $params);
                $updateAttach    = true;
            }

            if ($updateAttach) {
                // 更新时清除附件关联
                $daoAttachment->deleteAttachment($flowId);
                if (!empty($attachment)) {
                    foreach ($attachment as $attach) {
                        $daoAttachment->addAttachment($flowId, $attach['fileid'], (boolean) $attach['isattach']);
                    }
                }
            }

            // 比较可用人群，若有更新则与板块的参与人比较，缺少则添加到板块参与人
            $avalible     = explode("\n", $params['avaliable']);
            $diff         = array_diff($avalible, $flow->avaliable);
            $updateGroups = !empty($diff) ? true : false;
        }

        if (!$ret) {
            return $this->json(false, ($post['action'] == 'update') ? $this->lang['update_flow_failed'] : $this->lang['create_flow_failed']);
        }

        // 是否需要更新板块参与人
        if ($updateGroups) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = $this->getDao('Dao_Td_Board_Board');

            $boardId = $params['boardid'];
            $boards  = $this->getBoards(false);
            $board   = $boards[$boardId];

            $diff = array_diff($avalible, $board['groups']);
            if (!empty($diff)) {
                $groups = array_merge($avalible, $board['groups']);
                $groups = array_unique($groups);

                $daoBoard->updateBoard($this->_user->orgId, $boardId, array('groups' => implode("\n", $groups)));
            }
        }

        return $this->json(true, ($post['action'] == 'update') ? $this->lang['update_flow_success'] : $this->lang['create_flow_success'], array('flowid' => $flowId));
    }

    /**
     * 删除图度工作流
     */
    public function deleteAction()
    {
        $flowId = $this->_request->getPost('flowid');

        if (!$flowId) {
            return $this->json(false, $this->lang['invalid_flowid']);
        }

        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $flow = $daoFlow->getFlowById($flowId);

        // 工作流正在使用中，标记is_valid
        $isValid = $daoFlow->isValidFlow($flowId);
        if ($isValid) {
            $ret = $daoFlow->updateFlow($flowId, array('isvalid' => 0));
        } else {
            $ret = $daoFlow->deleteFlow($this->_user->orgId, $flowId);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['delete_flow_failed']);
        }

        return $this->json(true, $this->lang['delete_flow_success']);
    }

    /**
     * 输出流程图
     *
     */
    public function chartAction()
    {
        $flowId = $this->_request->getQuery('flowid');

        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $flow = $daoFlow->getFlowById($flowId);

        $this->view->flow = $flow->toArray();
    }

    /**
     * 输出板块下工作流
     */
    public function flowsAction()
    {
        $bid = $this->_request->getQuery('bid');
        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $flows = $daoFlow->getFlows(array('orgid' => $this->_user->orgId, 'boardid' => $bid), null, 'createtime DESC');
        $records = $flows->toArray();

        $flows = $this->inAvaliable($records, false);
        return $this->json(true, null, $flows);
    }

    /**
     *
     * @param array $post
     */
    public function formatSteps($post, $flowId = null)
    {
        $steps = array();
        $members = (array) $post['member'];
        $pervIds = array();
        foreach ($members as $member) {
            $steps[$post['order-' . $member]] = array(
                    'stepid'      => !empty($post['id-' . $member]) ? $post['id-' . $member] : Dao_Td_Flow_Flow::getStepId(),
                    'type'        => (int) $post['type-' . $member],
                    'subject'     => $post['subject-' . $member],
                    'description' => $post['description-' . $member],
                    'sections'    => $this->_formatRecipients($post['users-' . $member])
            );
            $pervIds[$post['order-' . $member]] = $post['prev-' . $member];
        }
        ksort($steps);

        // 处理下一步与上一步关系
        foreach ($steps as $key => $step) {
            if (isset($steps[$key + 1]['stepid'])) {
                $next = $steps[$key + 1]['stepid'];
            } else {
                $next = '^end';
            }

            $steps[$key]['prev'] = (in_array($pervIds[$key], $members)) ? $steps[$pervIds[$key]]['stepid'] : $pervIds[$key];
            $steps[$key]['next'] = $next;
        }

        if ($flowId) {
            // 更新步骤ID
            $newSteps = array();
            foreach ($steps as $key => $step) {
                $newSteps[] = array(
                    'stepid'      => Dao_Td_Flow_Flow::getStepId(),
                    'type'        => $step['type'],
                    'subject'     => $step['subject'],
                    'description' => $step['description'],
                    'sections'    => $step['sections']
                );
                unset($step);
            }
            unset($steps);

            // 处理下一步与上一步关系
            foreach ($newSteps as $key => $step) {
                if (isset($newSteps[$key + 1]['stepid'])) {
                    $next = $newSteps[$key + 1]['stepid'];
                } else {
                    $next = '^end';
                }

                $newSteps[$key]['prev'] = (in_array($pervIds[$key], $members)) ? $newSteps[$pervIds[$key]]['stepid'] : $pervIds[$key];
                $newSteps[$key]['next'] = $next;
            }
            $steps = $newSteps;
        }

        return json_encode($steps);
    }

    /**
     * 比较步骤
     *
     * @param array $new
     * @param array $old
     * @return boolean
     */
    public function compareSteps($new, $old) {
        if (count($new) != count($old)) {
            return true;
        } else {
            foreach ($new as $key => $n) {
                $rs = array_diff($new[$key], $old[$key]);
                if (isset($rs['type'])
                   || isset($rs['subject'])
                   || isset($rs['description'])
                   || isset($rs['next'])
                   || isset($rs['prev']))
                {
                    return true;
                }
            }

        }

        return false;
    }

    /**
     * 更新步骤流程
     *
     * @param string $flowId
     * @param array $steps
     */
    public function updateSteps($flowId, $steps)
    {
        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $params = array(
            'steps' => Dao_Td_Flow_Flow::formatXml($flowId, $steps)
        );

        return $daoFlow->updateFlow($flowId, $params);
    }

    /**
     * 获取用户有权限的图度工作流数据
     *
     * @param boolean $format
     * @return array
     */
    public function getFlows($role = true)
    {
        $records = $this->loadFlows();

        if ($role) {
            $records = $this->inRole($records);
        }

        $flows = $this->inAvaliable($records, true);

        $boards = $this->getBoards(true);
        foreach($flows as $bid => $flow) {
            if (isset($flows[$bid]['boardid'])) {
                continue;
            }

            $flows[$bid]['boardid']   = $boards[$bid]['boardid'];
            $flows[$bid]['boardname'] = $boards[$bid]['boardname'];
        }

        $boards = $this->getBoards(true, true);
        foreach($boards as $bid => $board) {
            if (!isset($flows[$bid])) {
                $flows[$bid]['boardid']   = $boards[$bid]['boardid'];
                $flows[$bid]['boardname'] = $boards[$bid]['boardname'];
                $flows[$bid]['children']  = array();
            }
        }
        return $flows;
    }

    /**
     * 工作流排序
     * 自己创建的排前，他人创建的排后
     */
    public function sortFlows($records)
    {
        $results    = array();
        foreach($records as $key => $record) {
            foreach ($record as $k => $v) {
                if ($k == 'children') {
                    $ownerFlows = array();
                    $otherFlows = array();
                    foreach ($v as $val) {
                        if ($val['uniqueid'] == $this->_user->uniqueId) {
                            $ownerFlows[] = $val;
                        } else {
                            $otherFlows[] = $val;
                        }
                    }
                    $results[$key]['children'] = array_merge($ownerFlows, $otherFlows);
                } else {
                    $results[$key][$k] = $v;
                }
            }
        }

        return $results;
    }

    /**
     *
     * @param array $records
     * @return array
     */
    public function inAvaliable($records, $format = false)
    {
        $flows = array();
        $boards = $this->getBoards(false);
        foreach($records as $key => $record) {
            if ($record['parentid']) {
                $isModerator      = array_key_exists($this->_user->userId, $boards[$record['boardid']]['moderators']);
                $isSuperModerator = array_key_exists($this->_user->userId, $boards[$record['parentid']]['moderators']);

                // 非板块下可用人群或版主或创建者 工作流不可见
                if (!in_array('^all', $boards[$record['parentid']]['groups'])
                    && !(in_array($this->_user->userName, $boards[$record['boardid']]['groups'], true) || in_array($this->_user->address, $boards[$record['boardid']]['groups'], true))
                    && !sizeof(array_uintersect($this->_user->groups, $boards[$record['boardid']]['groups'], "strcasecmp"))
                    && !($boards[$record['parentid']]['ownerid'] == $this->_user->userId)) {
                    continue;
                }
                // 非工作流可用人群或工作流创建者 工作流不可见
                if (!in_array('^all', $record['avaliable'])
                    // 参与人
                    && !(in_array($this->_user->userName, $record['avaliable'], true) || in_array($this->_user->address, $record['avaliable'], true))
                    // 参与人（群组）
                    && !sizeof(array_uintersect($this->_user->groups, $record['avaliable'], "strcasecmp"))
                    // 是否创建者
                    && !($record['uniqueid'] == $this->_user->uniqueId)

                    && !$isModerator

                    && !$isSuperModerator
                ) {
                    continue;
                }

                if ($format) {
                    $flows[$record['parentid']]['children'][] = &$records[$key];
                } else {
                    $flows[] = &$records[$key];
                }
            }
        }

        unset($records);
        return $flows;
    }

    /**
     * 处理当前用户对工作流的权限
     *
     * @param array $records
     * @return array
     */
    public function inRole($flows)
    {
        $boards = $this->getBoards(false);

        $allowUpdate = $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_FLOW);
        $allowDelete = $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_FLOW);

        foreach($flows as $key => $flow) {
            $isOwner = $this->_user->uniqueId == $flow['uniqueid'];
            //$isBoardOwner = $this->_user->userId == $boards[$flow['boardid']]['ownerid'];
            $isBoardModerator = array_key_exists($this->_user->userId, $boards[$flow['boardid']]['moderators']);
            $isSuperModerator = array_key_exists($this->_user->userId, $boards[$flow['parentid']]['moderators']);
            $flows[$key]['access'] = array(
                'modify' => $allowUpdate && ($isSuperModerator || $isBoardModerator || $isOwner),
                'delete' => $allowDelete && ($isSuperModerator || $isBoardModerator || $isOwner)
            );
        }

        return $flows;
    }

    /**
     * 加载图度工作流
     */
    public function loadFlows()
    {
        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $flows = $daoFlow->getFlows(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId), null, 'createtime DESC');
        return $flows->toArray();
    }

    /**
     * 格式化内容
     *
     * @param string $content
     */
    public function formatContent($content)
    {
        if (!$content) {
            return $content;
        }

        $matches = array();
        preg_match_all('/AID:([^"]+)/', $content, $matches);

        if (!empty($matches[1])) {
            $array = array_unique($matches[1]);
            $auth  = md5($this->_sessionId . $this->session->logintime);
            foreach ($array as $item) {
                $content = str_replace("AID:{$item}", $this->getAttachmentUrl($item, 'view'), $content);
            }
        }

        return $content;
    }

    /**
     * 处理附件地址
     *
     * @param string $fid
     * @param string $act
     * @return string
     */
    public function getAttachmentUrl($fid, $act = null)
    {
        $sid  = $this->_sessionId;
        $auth = md5($sid . $fid . $this->session->auth['logintime']);

        $url = $this->options['sites']['file']
             . $this->options['upload']['cgi']['download']
             . "?sid={$sid}&fid={$fid}&auth={$auth}";

        if ($act) {
            $url .= '&action=' . $act;
        }

        return $url;
    }

    /**
     * 格式化审批人或执行人格式
     *
     * @param string $recipients
     */
    private function _formatRecipients($recipients)
    {
        if ($recipients == '^upper') {
            return $recipients;
        }

        $arr  = explode("\n", $recipients);
        $asyn = 1;
        $ret  = array();
        foreach ($arr as $item) {
            $item = trim($item);

            if (!$item || 0 === strpos($item, '>') || 0 === strpos($item, '+')) {
                $asyn = $item == '+';
                continue ;
            }

            list ($userName, $trueName) = explode(' ', $item);
            if (!$asyn) {
                $ret[] = array(array('username' => $userName, 'truename' => $trueName));
            } else {
                end($ret);
                $last = key($ret);

                $ret[$last][] = array('username' => $userName, 'truename' => $trueName);
            }
        }

        $return = array();
        foreach ($ret as $item) {
            $return[] = $item;
        }

        return $return;
    }

    /**
     * 格式化审批人格式
     *
     * @param array $reviewer
     */
    private function _recoveryReviewer($sections, $type)
    {
        if (!is_array($sections)) {
            return $sections;
        }

        $reviewer = array();

        if ($type == 1) {
            $sum      = count($sections);
            $j        = 0;

            foreach ($sections as $item) {
                $j++;
                $count = count($item);
                $i     = 0;

                foreach ($item as $user) {
                    $i++;
                    $reviewer[] = $user['username'] . ' ' . $user['truename'];
                    if ($count != $i) {
                        $reviewer[] = '+';
                    }
                }
                if ($sum != $j) {
                    $reviewer[] = '>';
                }
            }
        } else {
            foreach ($sections as $item) {
                foreach ($item as $user) {
                    $reviewer[] = $user['username'] . ' ' . $user['truename'];
                }
            }
        }

        return implode("\n", $reviewer);
    }

    /**
     *
     * @param string $address
     */
    private function _formatUserName($address)
    {
        $name = array();
        $users = explode("\n", $address);
        foreach ($users as $user) {
            $user = explode(" ", $user);
            if (!empty($user[1])) {
                $name[] = $user[1];
            }

            if (count($name) > 2) {
                $name[] = '...';
                break;
            }
        }

        return implode(',', $name);
    }
}