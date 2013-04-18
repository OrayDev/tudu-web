<?php
/**
 * 审批流程设置
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Review.php 2764 2013-03-01 10:13:53Z chenyongfa $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Review extends Apps_Attend_Abstract
{
    /**
     * 新建
     * @var string
     */
    const ACTION_CREATE = 'create';

    /**
     * 更新
     * @var string
     */
    const ACTION_UPDATE = 'update';

    /**
     * (non-PHPdoc)
     * @see TuduX_App_Abstract::init()
     */
    public function init()
    {
        /* Initialize action controller here */
        parent::init();
        $this->checkApp();
    }

    /**
     * 审批流程首页
     */
    public function indexAction()
    {
        /* @var $daoFlow Dao_App_Attend_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

        $flows = $daoFlow->getFlows(
            array('orgid' => $this->_user->orgId),
            array('isvalid' => true),
            'issystem DESC, createtime DESC'
        );

        $this->view->flows = $flows->toArray();
    }

    /**
     * 编辑审批流程
     */
    public function modifyAction()
    {
        $flowId = $this->_request->getQuery('flowid');

        if ($flowId) {
            /* @var $daoFlow Dao_App_Attend_Flow */
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

            $condition = array(
                'flowid' => $flowId,
                'orgid'  => $this->_user->orgId
            );
            $flow = $daoFlow->getFlow($condition);

            if (null === $flow) {
                Oray_Function::alert($this->lang['flow_not_exists'], '/app/attend/review/index');
            }

            $this->view->flow   = $flow->toArray();
            $this->view->action = self::ACTION_UPDATE;
        } else {
            $this->view->action = self::ACTION_CREATE;
        }
    }

    /**
     * 预览审批流程
     */
    public function previewAction()
    {
        $flowId = str_replace('_', '^', $this->_request->getQuery('flowid'));

        /* @var $daoFlow Dao_App_Attend_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'flowid' => $flowId,
            'orgid'  => $this->_user->orgId
        );
        $flow = $daoFlow->getFlow($condition);
        $flow = $flow->toArray();

        foreach ($flow['steps'] as &$step) {
            if (isset($step['branches'])) {
                foreach ($step['branches'] as &$branch) {
                    if ($branch['type'] == 2) {
                        $branch['users'] = Dao_Td_Tudu_Tudu::formatAddress($branch['users']);
                    }
                }
            } else {
                if ($step['type'] == 2) {
                    $step['users'] = Dao_Td_Tudu_Tudu::formatAddress($step['users']);
                }
            }
        }

        $this->view->flow = $flow;
    }

    /**
     * 保存审批流程
     */
    public function saveAction()
    {
        $action = $this->_request->getPost('action');
        $post   = $this->_request->getPost();
        $params = array();
        $returnData = array();

        $members = (array) $this->_request->getPost('member');
        $count = count($members);
        if ($count <= 0) {
            return $this->_this->json(false, $this->lang['params_invalid_flow_steps']);
        }

        /* @var $daoFlow Dao_App_Attend_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

        // 更新审批流程
        if ($action == self::ACTION_UPDATE) {
            if (!$post['flowid']) {
                return $this->_this->json(false, $this->lang['parameter_error_fid']);
            }

            if (!empty($post['flowname'])) {
                $params['flowname']   = $post['flowname'];
            }

            $condition = array(
                'flowid' => $post['flowid'],
                'orgid'  => $this->_user->orgId
            );
            $flow = $daoFlow->getFlow($condition);

            if (null === $flow) {
                return $this->_this->json(false, $this->lang['flow_not_exists']);
            }

            $rs = $this->prepareSteps($post);
            if (!$rs['success']) {
                return $this->_this->json(false, $rs['message'], $rs['target']);
            }

            $params['steps'] = Dao_App_Attend_Flow::formatXml($post['flowid'], $this->formatSteps($post));
            $returnData['flowid'] = $post['flowid'];

            $ret = $daoFlow->updateFlow($post['flowid'], $this->_user->orgId, $params);
        // 创建审批流程
        } else {
            if (!$post['flowname']) {
                return $this->_this->json(false, $this->lang['params_invalid_flow_name']);
            }

            $rs = $this->prepareSteps($post);//var_dump($rs);exit;
            if (!$rs['success']) {
                return $this->_this->json(false, $rs['message'], $rs['target']);
            }

            $params['flowname']   = $post['flowname'];
            $params['orgid']      = $this->_user->orgId;
            $params['flowid']     = Dao_App_Attend_Flow::getFlowId();
            $params['steps']      = Dao_App_Attend_Flow::formatXml($params['flowid'], $this->formatSteps($post));
            $params['createtime'] = time();

            $returnData['flowid'] = $params['flowid'];

            $ret = $daoFlow->createFlow($params);
        }

        if (!$ret) {
            return $this->_this->json(false, $this->lang['save_failed']);
        }

        return $this->_this->json(true, $this->lang['save_success'], $returnData);
    }

    /**
     * 修改应用范围页面
     */
    public function applyAction()
    {
        $flowId = $this->_request->getQuery('flowid');

        /* @var $daoFlow Dao_App_Attend_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'flowid' => $flowId,
            'orgid' => $this->_user->orgId
        );
        $flow = $daoFlow->getFlow($condition);

        if (null === $flow) {
            Oray_Function::alert($this->lang['flow_not_exists'], '/app/attend/review/index');
        }

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $categories = $daoCategory->getCategories(
            array('orgid' => $this->_user->orgId),
            null,
            'status DESC, issystem DESC, createtime DESC'
        );

        $this->view->categories = $categories->toArray();
        $this->view->flow       = $flow->toArray();
    }

    /**
     * 保存应用范围
     */
    public function saveapplyAction()
    {
        $flowId      = $this->_request->getPost('flowid');
        $categoryIds = (array) $this->_request->getPost('categoryid');

        if (!$flowId) {
            return $this->_this->json(false, $this->lang['parameter_error_fid']);
        }

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        // 清除关联
        $daoCategory->removeAllFlow($this->_user->orgId, $flowId);

        if (!empty($categoryIds)) {
            $success = 0;
            foreach ($categoryIds as $categoryId) {
                // 添加关联
                $ret = $daoCategory->updateCategory($categoryId, $this->_user->orgId, array('flowid' => $flowId));
                if (!$ret) {
                    continue ;
                }

                $success ++;
            }

            if ($success <= 0) {
                return $this->_this->json(false, $this->lang['save_failed']);
            }
        }

        return $this->_this->json(true, $this->lang['save_success']);
    }

    /**
     * 删除审批流程
     */
    public function deleteAction()
    {
        $flowId = $this->_request->getQuery('flowid');
        if (!$flowId) {
            return $this->_this->json(false, $this->lang['parameter_error_fid']);
        }

        /* @var $daoFlow Dao_App_Attend_Flow */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_App_Attend_Flow', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'flowid' => $flowId,
            'orgid' => $this->_user->orgId
        );
        $flow = $daoFlow->getFlow($condition);

        if (null === $flow) {
            return $this->_this->json(false, $this->lang['flow_not_exists']);
        }

        if ($flow->isSystem) {
            return $this->_this->json(false, $this->lang['not_delete_system_flow']);
        }

        $ret = $daoFlow->deleteFlow($flowId, $this->_user->orgId);
        if (!$ret) {
            return $this->_this->json(false, $this->lang['delete_failed']);
        }

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);
        // 清除关联
        $daoCategory->removeAllFlow($this->_user->orgId, $flowId);

        return $this->_this->json(true, $this->lang['delete_success']);
    }

    /**
     * 判断流程数据是否完整
     *
     * @param array $post
     * @return array
     */
    public function prepareSteps($post)
    {
        $return = array();
        $target = array();
        $success = true;
        $members = (array) $post['member'];

        foreach ($members as $member) {
            if (isset($post['branch-' . $member])) {
                $branches = (array) $post['branch-' . $member];
                $lastBranch = array_pop($branches);
                // 过来最后一个分支
                $branches = array_diff($branches, (array) $lastBranch);
                foreach ($branches as $branch) {
                    // 开始时间段
                    if (empty($post['start-' . $member . '-' . $branch]) && strlen($post['start-' . $member . '-' . $branch]) <= 0) {
                        $success = false;
                        $target[] = 'start-' . $member . '-' . $branch;
                    }

                    // 结束时间段
                    if (empty($post['end-' . $member . '-' . $branch]) && strlen($post['end-' . $member . '-' . $branch]) <= 0) {
                        $success = false;
                        $target[] = 'end-' . $member . '-' . $branch;
                    }

                    // 指定审批人 审批人不能为空
                    $type = (int) $post['type-' . $member . '-' . $branch];
                    if ($type == 2) {
                        if (empty($post['users-' . $member . '-' . $branch])) {
                            $success = false;
                            $target[] = 'users-' . $member . '-' . $branch;
                        }
                    }
                }

                // 最后一个没有设置开始
                if (empty($post['start-' . $member . '-' . $lastBranch]) && strlen($post['start-' . $member . '-' . $lastBranch]) <= 0) {
                    $success = false;
                    $target[] = 'start-' . $member . '-' . $lastBranch;
                }

                // 指定审批人 审批人不能为空
                $type = (int) $post['type-' . $member . '-' . $lastBranch];
                if ($type == 2) {
                    if (empty($post['users-' . $member . '-' . $lastBranch])) {
                        $success = false;
                        $target[] = 'users-' . $member . '-' . $lastBranch;
                    }
                }
            } else {
                // 指定审批人 审批人不能为空
                $type = (int) $post['type-' . $member];
                if ($type == 2) {
                    if (empty($post['users-' . $member])) {
                        $success = false;
                        $target[] = 'users-' . $member;
                    }
                }
            }
        }

        if (!$success) {
            return $this->returnMessage(false, $target, $this->lang['red_form_is_not_null']);
        }

        // 判断是否为数字且为正数
        foreach ($members as $member) {
            if (isset($post['branch-' . $member])) {
                $branches = (array) $post['branch-' . $member];
                foreach ($branches as $branch) {
                    $start = $post['start-' . $member . '-' . $branch];
                    $end   = $post['end-' . $member . '-' . $branch];

                    if ((!is_numeric($start) || !(strpos($start, '.') === false) || !(strpos($start, '-') === false)) && (strlen($start) > 0)) {
                        $success = false;
                        $target[] = 'start-' . $member . '-' . $branch;
                    }

                    if ((!is_numeric($end) || !(strpos($end, '.') === false) || !(strpos($end, '-') === false)) && (strlen($end) > 0)) {
                        $success = false;
                        $target[] = 'end-' . $member . '-' . $branch;
                    }
                }
            }
        }

        if (!$success) {
            return $this->returnMessage(false, $target, $this->lang['time_must_be_int']);
        }

        // 判断时间段是否有冲突
        foreach ($members as $member) {
            if (isset($post['branch-' . $member])) {
                $branches = (array) $post['branch-' . $member];
                $start = null;
                $end   = null;
                foreach ($branches as $branch) {

                    if (($post['start-' . $member . '-' . $branch] >= $post['end-' . $member . '-' . $branch]) && (strlen($post['end-' . $member . '-' . $branch]) > 0)) {
                        $success = false;
                        $target[] = 'end-' . $member . '-' . $branch;
                    }

                    if ($end !== null && $end >= $post['start-' . $member . '-' . $branch]) {
                        $success = false;
                        $target[] = 'start-' . $member . '-' . $branch;
                    }

                    $end = $post['end-' . $member . '-' . $branch];
                }
            }
        }

        if (!$success) {
            return $this->returnMessage(false, $target, $this->lang['review_time_error']);
        }

        return $this->returnMessage();
    }

    /**
     *
     * @param boolean $success
     * @param array   $target
     * @param string  $message
     * @return array
     */
    public function returnMessage($success = true, $target = array(), $message = null)
    {
        if (!is_array($target)) {
            $target = (array) $target;
        }

        $rs = array(
            'success' => $success,
            'target'  => $target,
            'message' => $message
        );

        return $rs;
    }

    /**
     * 处理审批步骤
     *
     * @param array $post
     * @return array
     */
    public function formatSteps($post)
    {
        $steps = array();
        $members = (array) $post['member'];

        foreach ($members as $member) {
            // 步骤ID
            $steps[$post['order-' . $member]]['id'] = !empty($post['id-' . $member]) ? $post['id-' . $member] : Dao_App_Attend_Flow::getStepId();

            // 存在分支
            if (isset($post['branch-' . $member])) {
                $stepBranches = array();
                $branches = (array) $post['branch-' . $member];

                foreach ($branches as $branch) {
                    $type = (int) $post['type-' . $member . '-' . $branch];
                    if ($type == 0) {
                        $users = '^upper';
                    } elseif ($type == 1) {
                        $users = '^uppers';
                    } elseif ($type == 2) {
                        $users = $post['users-' . $member . '-' . $branch];
                    }

                    $stepBranches[] = array(
                        'type'  => $type,
                        'users' => $users,
                        'start' => $post['start-' . $member . '-' . $branch],
                        'end'   => $post['end-' . $member . '-' . $branch]
                    );
                }

                $steps[$post['order-' . $member]]['branches'] = $stepBranches;
            // 没有分支
            } else {
                $type = (int) $post['type-' . $member];
                if ($type == 0) {
                    $users = '^upper';
                } elseif ($type == 1) {
                    $users = '^uppers';
                } elseif ($type == 2) {
                    $users = $post['users-' . $member];
                }

                $steps[$post['order-' . $member]]['type'] = $type;
                $steps[$post['order-' . $member]]['users'] = $users;
            }
        }

        // 排序
        ksort($steps);

        // 处理下一步关系
        foreach ($steps as $key => $step) {
            if (isset($steps[$key + 1]['id'])) {
                $next = $steps[$key + 1]['id'];
            } else {
                $next = '^end';
            }

            $steps[$key]['next'] = $next;
        }

        return $steps;
    }
}