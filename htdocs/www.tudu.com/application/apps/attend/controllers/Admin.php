<?php
/**
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Admin.php 2766 2013-03-05 10:16:20Z chenyongfa $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Admin extends Apps_Attend_Abstract
{
    /**
     *
     * @var string
     */
    protected $_admin = null;

    /**
     *
     * @var string
     */
    protected $_appId = 'attend';

    protected $_defaultCategories = array(
        '^checkin'   => array('name' => '补签'),
        '^sick'      => array('name' => '病假'),
        '^affairs'   => array('name' => '事假'),
        '^overtime'  => array('name' => '加班'),
        '^holidays'  => array('name' => '补休'),
        '^year'      => array('name' => '年假'),
        '^wedding'   => array('name' => '婚假'),
        '^maternity' => array('name' => '产假'),
        '^business'  => array('name' => '出差'),
        '^other'     => array('name' => '其他')
    );

    /**
     * 显示设置页面
     */
    public function indexAction()
    {
        $daoAppUser = Tudu_Dao_Manager::getDao('Dao_App_App_User', Tudu_Dao_Manager::DB_APP);
        $daoApp     = Tudu_Dao_Manager::getDao('Dao_App_App_App', Tudu_Dao_Manager::DB_APP);

        $app = $daoApp->getApp(array(
            'orgid' => $this->_user->orgId,
            'appid' => $this->_appId
        ));

        // 没安装或过期
        if (null === $app || ($app->expireDate && $app->expireDate < time())) {
            return Oray_Function::alert('您还没有安装该应用或已过期');
        }

        $users = $daoAppUser->getAppUsers(array(
            'orgid' => $this->_user->orgId,
            'appid' => $this->_appId
        ))->toArray();

        $roles = array();

        foreach($users as $user) {
            $roles[$user['role']][] = $user['itemid'];
        }

        $app      = $app->toArray();
        $settings = $app['settings'];
        if (empty($settings) || !isset($settings['checkoutremind'])) {
            $app['settings']['checkoutremind'] = 1;
        }

        $this->view->roles = $roles;
        $this->view->app   = $app;
    }

    /**
     *
     */
    public function postRun()
    {}

    /**
     * 初始化应用环境
     */
    public function initAction()
    {
        $daoApp = Tudu_Dao_Manager::getDao('Dao_App_App_App', Tudu_Dao_Manager::DB_APP);

        $app = $daoApp->getApp(array(
            'orgid' => $this->_user->orgId,
            'appid' => $this->_appId
        ));

        if ($app->status != 0) {
            return $this->json(true, '应用初始化成功');
        }

        $ts = time();

        // 创建考勤版块
        $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

        $attendBoard = $daoBoard->existsBoard($this->_user->orgId, '^app-attend');
        if (!$attendBoard) {
            $daoBoard->createBoard(array(
                'orgid'     => $this->_user->orgId,
                'boardid'   => '^app-attend',
                'boardname' => 'attend',
                'type'      => 'system',
                'status'    => 1
            ));
            // 创建考勤版块的考勤分类
            /* @var $daoClass Dao_Td_Tudu_Class */
            $daoClass = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Class', Tudu_Dao_Manager::DB_TS);
            $classes  = $daoClass->getClassesByBoardId($this->_user->orgId, '^app-attend')->toArray('classid');
            if (empty($classes) || empty($classes['^attend'])) {
                $daoClass->createClass(array(
                    'orgid'     => $this->_user->orgId,
                    'boardid'   => '^app-attend',
                    'classid'   => '^attend',
                    'classname' => '考勤'
                ));
            }
        }

        // 考勤分类
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        foreach ($this->_defaultCategories as $id => $item) {
            // 默认考勤分类的审批流程
            $stepId = Dao_App_Attend_Category::getStepId();
            $steps  = array(array(
                'stepid'   => $stepId,
                'type'     => 0,
                'next'     => '^end',
                'sections' => '^upper'
            ));
            $daoCategory->createCategory(array(
                'categoryid'   => $id,
                'orgid'        => $this->_user->orgId,
                'categoryname' => $item['name'],
                'flowsteps'    => json_encode($steps),
                'issystem'     => 1,
                'status'       => 1,
                'isshow'       => 1,
                'createtime'   => $ts
            ));
        }

        // 创建默认排班规则
        // 默认
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        $daoSchedule->createSchedule(array(
            'orgid'      => $this->_user->orgId,
            'scheduleid' => '^default',
            'name'       => '默认班',
            'uniqueid'   => '^system',
            'issystem'   => 1,
            'createtime' => $ts
        ));
        for ($i = 0; $i < 7; $i ++) {
            $isExists = $daoSchedule->existsRule($this->_user->orgId, '^default', $i);
            if ($isExists) {
                continue ;
            }

            $status = $i != 0 && $i != 6 ? 1 : 0;

            $daoSchedule->createScheduleRule(array(
                'orgid'         => $this->_user->orgId,
                'scheduleid'    => '^default',
                'ruleid'        => Dao_App_Attend_Schedule::getRuleId(),
                'createtime'    => $ts,
                'week'          => $i,
                'status'        => $status,
                'checkintime'   => 32400,
                'checkouttime'  => 64800,
                'latestandard'  => 0,
                'leavestandard' => 0
            ));
        }

        // 免签
        $daoSchedule->createSchedule(array(
            'orgid'      => $this->_user->orgId,
            'scheduleid' => '^exemption',
            'name'       => '免签班',
            'uniqueid'   => '^system',
            'issystem'   => 1,
            'createtime' => $ts
        ));
        $daoSchedule->createScheduleRule(array(
            'orgid'         => $this->_user->orgId,
            'scheduleid'    => '^exemption',
            'ruleid'        => '^exemptionrule',
            'createtime'    => $ts,
            'latestandard'  => 0,
            'leavestandard' => 0
        ));

        $daoApp->updateApp($this->_user->orgId, $this->_appId, array(
            'status' => 2
        ));

        return $this->json(true, '应用初始化成功');
    }

    /**
     * 保存权限设置
     */
    public function saveAction()
    {
        /* @var $daoAppUser Dao_App_App_User */
        $daoAppUser = Tudu_Dao_Manager::getDao('Dao_App_App_User', Tudu_Dao_Manager::DB_APP);
        $daoApp     = Tudu_Dao_Manager::getDao('Dao_App_App_App', Tudu_Dao_Manager::DB_APP);

        $app = $daoApp->getApp(array('orgid' => $this->_user->orgId, 'appid' => $this->_appId));

        if (!$app || $app->orgId != $this->_user->orgId) {
            return $this->json(false, '未安装该应用程序');
        }

        $status         = (int) $this->getRequest()->getPost('status');
        $checkoutRemind = $this->_request->getPost('checkoutremind');
        $settings       = array('checkoutremind' => $checkoutRemind);
        $params = array(
            'status'   => $status,
            'settings' => json_encode($settings)
        );

        if ($status == 1 && $app->status != $status) {
            $params['activetime'] = strtotime('tomorrow');
        }

        $ret = $daoApp->updateApp($this->_user->orgId, $this->_appId, $params);

        if (!$ret) {
            return $this->json(false, '更新应用状态失败');
        }

        $daoAppUser->deleteUsers($this->_user->orgId, $this->_appId);

        $roles = array(
            Dao_App_App_User::ROLE_ADMIN,
            Dao_App_App_User::ROLE_DEF,
            Dao_App_App_User::ROLE_SUM,
            Dao_App_App_User::ROLE_SC
        );

        foreach ($roles as $role) {
            $val = $this->getRequest()->getPost($role);
            $val = explode("\n", $val);

            foreach ($val as $item) {
                $daoAppUser->addUserRole(array(
                    'orgid'  => $this->_user->orgId,
                    'appid'  => $this->_appId,
                    'itemid' => $item,
                    'role'   => $role
                ));
            }
        }

        return $this->json(true, '保存应用设置成功');
    }
}