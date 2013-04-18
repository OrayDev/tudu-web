<?php
/**
 * 考勤分类
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Category.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Category extends Apps_Attend_Abstract
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
     * 考勤分类首页
     */
    public function indexAction()
    {
        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $categories = $daoCategory->getCategories(
            array('orgid' => $this->_user->orgId),
            array('isshow' => 1),
            'status DESC, issystem DESC, createtime DESC'
        );

        $this->view->categories = $categories->toArray();
    }

    /**
     * 编辑考勤分类
     */
    public function modifyAction()
    {
        $categoryId = $this->_request->getQuery('categoryid');

        if ($categoryId) {
            /* @var $daoCategory Dao_App_Attend_Category */
            $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

            $condition = array(
                'categoryid' => $categoryId,
                'orgid'      => $this->_user->orgId
            );
            $category = $daoCategory->getCategory($condition);

            if (null === $category) {
                /**
                 * @see Oray_Function
                 */
                require_once 'Oray/Function.php';

                Oray_Function::alert($this->lang['category_not_exists'], '/app/attend/category/index');
            }

            $category = $category->toArray();
            foreach ($category['flowsteps'] as $key => &$step) {
                if (isset($step['sections'])) {
                    $step['users'] = $this->_formatStepSection($step['sections']);
                }

                if (isset($step['branches'])) {
                    foreach ($step['branches'] as &$branch) {
                        if (isset($branch['sections'])) {
                            $branch['users'] = $this->_formatStepSection($branch['sections']);
                        }
                    }
                }
            }

            $this->view->category = $category;
            $this->view->action   = self::ACTION_UPDATE;
        } else {
            $this->view->action   = self::ACTION_CREATE;
        }
    }

    /**
     * 保存考勤分类
     */
    public function saveAction()
    {
        $action = $this->_request->getPost('action');
        $post   = $this->_request->getPost();
        $params = array();
        $returnData = array();
        $isSystem = false;

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        // 更新
        if ($action == self::ACTION_UPDATE) {
            $condition = array(
                'categoryid' => $post['categoryid'],
                'orgid'      => $this->_user->orgId
            );
            $category = $daoCategory->getCategory($condition);

            if (null === $category) {
                return $this->_this->json(false, $this->lang['category_not_exists']);
            }

            $isSystem = $category->isSystem;

            if (!$post['categoryid']) {
                return $this->_this->json(false, $this->lang['parameter_error_cid']);
            }
        }

        if (!$isSystem && !$post['categoryname']) {
            return $this->_this->json(false, $this->lang['params_invalid_category_name']);
        }

        // 必须有流程步骤
        $members = (array) $this->_request->getPost('member');
        $count = count($members);
        if ($count <= 0) {
            return $this->_this->json(false, $this->lang['params_invalid_flow_steps']);
        }

        if (!empty($post['categoryname'])) {
            $categoryId = $action == self::ACTION_UPDATE ? $post['categoryid'] : null;
            $rs = $daoCategory->existsCategoryName($this->_user->orgId, $post['categoryname'], $categoryId);
            if ($rs) {
                return $this->_this->json(false, '已有此分类名称的考勤分类');
            }
        }

        $rs = $this->prepareSteps($post);
        if (!$rs['success']) {
            return $this->_this->json(false, $rs['message'], $rs['target']);
        }

        $flowId              = empty($post['flowid']) ? Dao_App_Attend_Category::getFlowId() : $post['flowid'];
        $params['flowsteps'] = Dao_App_Attend_Category::formatData($this->formatSteps($post));

        // 更新考勤分类
        if ($action == self::ACTION_UPDATE) {
            if (!empty($post['categoryname'])) {
                $params['categoryname'] = $post['categoryname'];
            }

            $returnData['categoryid'] = $post['categoryid'];

            $ret = $daoCategory->updateCategory($post['categoryid'], $this->_user->orgId, $params);
        // 创建考勤分类
        } else {
            $params['categoryname'] = $post['categoryname'];
            $params['orgid']        = $this->_user->orgId;
            $params['categoryid']   = Dao_App_Attend_Category::getCategoryId();
            $params['createtime']   = time();

            $returnData['categoryid'] = $params['categoryid'];

            $ret = $daoCategory->createCategory($params);
        }

        if (!$ret) {
            return $this->_this->json(false, $this->lang['save_failed']);
        }

        return $this->_this->json(true, $this->lang['save_success'], $returnData);
    }

    /**
     * 删除考勤分类
     */
    public function deleteAction()
    {
        $categoryId = $this->_request->getQuery('categoryid');

        if (!$categoryId) {
            return $this->_this->json(false, $this->lang['parameter_error_cid']);
        }

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'categoryid' => $categoryId,
            'orgid'      => $this->_user->orgId
        );
        $category = $daoCategory->getCategory($condition);

        if (null === $category) {
            return $this->_this->json(false, $this->lang['category_not_exists']);
        }

        if ($category->isSystem) {
            return $this->_this->json(false, $this->lang['not_delete_system_category']);
        }

        // 已有的考勤分类被考勤申请过，暂时保留
        $isUsed = $daoCategory->summaryApply($this->_user->orgId, $categoryId);

        if ($isUsed) {
            $ret = $daoCategory->updateCategory($categoryId, $this->_user->orgId, array('isshow' => 0));
        } else {
            $ret = $daoCategory->deleteCategory($categoryId, $this->_user->orgId);
        }

        if (!$ret) {
            return $this->_this->json(false, $this->lang['delete_failed']);
        }

        return $this->_this->json(true, $this->lang['delete_success']);
    }

    /**
     * 考勤分类停用、启用
     */
    public function statusAction()
    {
        $categoryId = $this->_request->getQuery('categoryid');
        $type       = (int) $this->_request->getQuery('type');

        if (!$categoryId) {
            return $this->_this->json(false, $this->lang['parameter_error_cid']);
        }

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'categoryid' => $categoryId,
            'orgid'      => $this->_user->orgId
        );
        $category = $daoCategory->getCategory($condition);

        if (null === $category) {
            return $this->_this->json(false, $this->lang['category_not_exists']);
        }

        $ret = $daoCategory->updateCategory($categoryId, $this->_user->orgId, array('status' => $type));

        if (!$ret) {
            return $this->_this->json(false, $this->lang['operate_failed']);
        }

        return $this->_this->json(true, $this->lang['operate_success']);
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
            $steps[$post['order-' . $member]]['id'] = !empty($post['id-' . $member]) ? $post['id-' . $member] : Dao_App_Attend_Category::getStepId();

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
                        $users = $this->_parseStepSection($post['users-' . $member . '-' . $branch]);
                    }

                    $stepBranches[] = array(
                        'type'     => $type,
                        'sections' => $users,
                        'start'    => $post['start-' . $member . '-' . $branch],
                        'end'      => $post['end-' . $member . '-' . $branch]
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
                    $users = $this->_parseStepSection($post['users-' . $member]);
                }

                $steps[$post['order-' . $member]]['type'] = $type;
                $steps[$post['order-' . $member]]['sections'] = $users;
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

    /**
     *
     * @param mixed $section
     */
    protected function _formatStepSection($section)
    {
        if (is_string($section)) {
            return $section;
        }

        $arr = array();
        foreach ($section as $sec) {
            if (!empty($arr)) {
                $arr[] = '>';
            }
            $u = array();
            foreach ($sec as $user) {
                if (!empty($u)) {
                    $u[] = '+';
                }

                $u[] = $user['username'] . ' ' . $user['truename'];
            }

            $arr = array_merge($arr, $u);
        }

        return implode("\n", $arr);
    }

    /**
     *
     * @param string $str
     */
    protected function _parseStepSection($str)
    {
        $arr = explode("\n", $str);

        $ret = array();
        $sec = array();
        foreach ($arr as $item) {
            if ($item == '>') {
                $ret[] = $sec;
                $sec = array();
                continue ;
            }

            if ($item == '+') {
                continue ;
            }

            $u = explode(' ', $item);
            $sec[] = array(
                'username' => $u[0],
                'truename' => isset($u[1]) ? $u[1] : $u[0]
            );
        }

        $ret[] = $sec;

        return $ret;
    }
}