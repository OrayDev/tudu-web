<?php
/**
 * 排班
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Schedule.php 2767 2013-03-06 09:30:50Z chenyongfa $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Schedule extends Apps_Attend_Abstract
{
    /**
     *
     * @var array
     */
    public $defaultPlan;

    /**
     * 背景颜色
     *
     * @var array
     */
    private $_bgColors = array(
        '#800000', '#DC143C', '#FF0000', '#B22222', '#C71585', '#D87093', '#FF00FF', '#FF1493', '#FFB6C1',
        '#4B0082', '#800080', '#9932CC', '#EE82EE', '#7B68EE', '#9370DB', '#D8BFD8', '#8B4513', '#D2691E',
        '#CD5C5C', '#BC8F8F', '#F08080', '#FFA07A', '#FF4500', '#FF8C00', '#D2B48C', '#FFDAB9', '#FFFF00',
        '#B8860B', '#FFD700', '#BDB76B', '#2F4F4F', '#556B2F', '#228B22', '#808000', '#00FF00', '#66CDAA',
        '#00008B', '#483D8B', '#00BFFF', '#008B8B', '#00FFFF', '#B0E0E6', '#000000', '#778899', '#696969'
    );

    /**
     * (non-PHPdoc)
     * @see TuduX_App_Abstract::init()
     */
    public function init()
    {
        parent::init();
        $this->checkApp();

        $this->view->role = $this->getRoles();
    }

    /**
     * 排班方案列表
     */
    public function indexAction()
    {
        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        $condition = array('orgid' => $this->_user->orgId);
        $schedules = $daoSchedule->getSchedules($condition, array('issystem' => false, 'isvalid' => true), 'createtime DESC');

        $this->view->schedules   = $schedules->toArray();
    }

    /**
     * 默认班信息
     */
    public function defaultRuleAction()
    {
        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        $week = date('w');
        $rule = $daoSchedule->getSchedule(array(
            'orgid'      => $this->_user->orgId,
            'scheduleid' => '^default',
            'week'       => $week
        ));

        if (null !== $rule) {
            $rule = $rule->toArray();
        }

        $this->view->week   = $week;
        $this->view->rule   = $rule;
    }

    /**
     * 编辑排班方案
     */
    public function modifyAction()
    {
        $scheduleId = $this->_request->getQuery('scheduleid');

        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        if ($scheduleId) {
            $condition = array(
                'orgid'      => $this->_user->orgId,
                'scheduleid' => $scheduleId
            );
            $schedule = $daoSchedule->getSchedule($condition);

            // 方案不存在
            if (null === $schedule) {
                Oray_Function::alert($this->lang['schedule_plan_not_exists'], '/app/attend/schedule/index');
            }

            // 修改权限验证
            $role = $this->getRoles();
            if (empty($role['admin']) && $schedule->uniqueId != $this->_user->uniqueId) {
                Oray_Function::alert('您没有修改该排班方案的权限', '/app/attend/schedule/index');
            }

            $schedule = $schedule->toArray();

            if ($scheduleId == '^default') {
                $rules = $daoSchedule->getScheduleRules($condition)->toArray('week');
                $schedule['rules'] = $rules;
            } else {
                $schedule['checkintime'] = null !== $schedule['checkintime'] ? explode(':', $schedule['checkintime']) : null;
                $schedule['checkouttime'] = null !== $schedule['checkouttime'] ? explode(':', $schedule['checkouttime']) :null;
            }

            $this->view->schedule = $schedule;
        } else {
            $schedules = $daoSchedule->getSchedules(
                    array('orgid' => $this->_user->orgId),
                    array('isvalid' => true),
                    'createtime DESC')
            ->toArray('scheduleid');

            $count = count($this->_bgColors);
            if (count($schedules) >= $count) {
                Oray_Function::alert(sprintf('最多能创建 %s 个排班方案', $count), '/app/attend/schedule/index');
            }

            $tempColor   = array();
            foreach ($schedules as $item) {
                $tempColor[] = $item['bgcolor'];
            }

            do {
                $randomColor = $this->_bgColors[array_rand($this->_bgColors)];
                if (!in_array($randomColor, $tempColor)) {
                    break;
                }
            } while (true);

            $this->view->randomcolor = $randomColor;
        }

        $this->view->bgcolors = $this->_bgColors;
    }

    /**
     * 保存排班方案
     */
    public function saveAction()
    {
        $action = $this->_request->getPost('action');
        $post   = $this->_request->getPost();

        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        if ($action == 'update') {
            if (empty($post['scheduleid'])) {
                return $this->_this->json(false, $this->lang['parameter_error_sid']);
            }

            if (!empty($post['bgcolor'])) {
                $existsColor = $daoSchedule->existsBgcolor($this->_user->orgId, $post['bgcolor'], $post['scheduleid']);
                if ($existsColor) {
                    return $this->_this->json(false, '您提交的颜色不能与其他方案颜色重复');
                }
            }

            // 默认排班方案
            if ($post['scheduleid'] == '^default') {                
                // 清除排班方案规则状态
                $daoSchedule->clearAllStatus($this->_user->orgId, $post['scheduleid']);

                $members = (array) $this->_request->getPost('member');
                foreach ($members as $member) {
                    $week = $post['week-' . $member];
                    $ruleParams = array();

                    $ruleParams['status']        = !empty($post['status-' . $member]) ? 1 : 0;
                    $ruleParams['latestandard']  = !empty($post['latestandard']) ? $post['latestandard'] : 0;
                    $ruleParams['latecheckin']   = !empty($post['latecheckin'])? $post['latecheckin'] : null;
                    $ruleParams['leavecheckout'] = !empty($post['leavecheckout']) ? $post['leavecheckout'] : null;

                    // 判断记录是否存在
                    if (!$daoSchedule->existsRule($this->_user->orgId, $post['scheduleid'], $week)) {
                        $params = array(
                            'orgid'        => $this->_user->orgId,
                            'scheduleid'   => $post['scheduleid'],
                            'ruleid'       => Dao_App_Attend_Schedule::getRuleId(),
                            'week'         => $post['week-' . $member],
                            'checkintime'  => $this->formatTime($post['checkintime-hour-' . $member] . ':' . $post['checkintime-min-' . $member]),
                            'checkouttime' => $this->formatTime($post['checkouttime-hour-' . $member] . ':' . $post['checkouttime-min-' . $member]),
                            'createtime'   => time()
                        );

                        $ruleId = $daoSchedule->createScheduleRule(array_merge($params, $ruleParams));
                        if (!$ruleId) {
                            return $this->_this->json(false, $this->lang['save_failed']);
                        }

                    // 更新
                    } else {
                        $ruleId = $post['ruleid-' . $member];
                        $ruleParams['checkintime'] = $this->formatTime($post['checkintime-hour-' . $member] . ':' . $post['checkintime-min-' . $member]);
                        $ruleParams['checkouttime'] = $this->formatTime($post['checkouttime-hour-' . $member] . ':' . $post['checkouttime-min-' . $member]);

                        $ret = $daoSchedule->updateScheduleRule($this->_user->orgId, $post['scheduleid'], $ruleId, $ruleParams);
                        if (!$ret) {
                            return $this->_this->json(false, $this->lang['save_failed']);
                        }
                    }
                }

                if (!empty($post['bgcolor'])) {
                    $ret = $daoSchedule->updateSchedule($this->_user->orgId, $post['scheduleid'], array('bgcolor' => $post['bgcolor']));
                    if (!$ret) {
                        return $this->_this->json(false, $this->lang['save_failed']);
                    }
                }

            // 自定义排班方案
            } else {
                if (empty($post['ruleid'])) {
                    return $this->_this->json(false, $this->lang['parameter_error_ruleid']);
                }

                $condition = array(
                    'orgid'      => $this->_user->orgId,
                    'scheduleid' => $post['scheduleid']
                );
                $schedule = $daoSchedule->getSchedule($condition);

                // 方案不存在
                if (null === $schedule) {
                    return $this->_this->json(false, $this->lang['schedule_plan_not_exists']);
                }

                $params = array();
                if (!empty($post['name'])) {
                    $params['name'] = $post['name'];
                }

                if (!empty($post['bgcolor'])) {
                    $params['bgcolor'] = $post['bgcolor'];
                }

                if (!empty($params)) {
                    $ret = $daoSchedule->updateSchedule($this->_user->orgId, $post['scheduleid'], $params);
                    if (!$ret) {
                        return $this->_this->json(false, $this->lang['save_failed']);
                    }
                }

                $ruleParams = array();

                $ruleParams['latestandard']  = $this->isNotEmpty('late-standard', $post) ? $post['late-standard'] : null;
                if ($ruleParams['latestandard'] === null && $this->isNotEmpty('checkin-hour', $post)) {
                    $ruleParams['latestandard'] = 0;
                }
                $ruleParams['latecheckin']   = $this->isNotEmpty('late-checkin', $post) ? $post['late-checkin'] : null;
                $ruleParams['leavecheckout'] = $this->isNotEmpty('leave-checkout', $post) ? $post['leave-checkout'] : null;
                $ruleParams['checkintime']   = $this->isNotEmpty('checkin-hour', $post) ? $this->formatTime($post['checkin-hour'] . ':' . $post['checkin-min']) : null;
                $ruleParams['checkouttime']  = $this->isNotEmpty('checkout-hour', $post) ? $this->formatTime($post['checkout-hour'] . ':' . $post['checkout-min']) : null;

                $ret = $daoSchedule->updateScheduleRule($this->_user->orgId, $post['scheduleid'], $post['ruleid'], $ruleParams);
                if (!$ret) {
                    return $this->_this->json(false, $this->lang['save_failed']);
                }
            }

        // 创建排班方案
        } else {
            $ruleParams = array();
            $ruleParams['latestandard']  = $this->isNotEmpty('late-standard', $post) ? $post['late-standard'] : null;
            if ($ruleParams['latestandard'] === null && $this->isNotEmpty('checkin-hour', $post)) {
                $ruleParams['latestandard'] = 0;
            }
            $ruleParams['latecheckin']   = $this->isNotEmpty('late-checkin', $post) ? $post['late-checkin'] : null;
            $ruleParams['leavecheckout'] = $this->isNotEmpty('leave-checkout', $post) ? $post['leave-checkout'] : null;
            $ruleParams['checkintime']   = $this->isNotEmpty('checkin-hour', $post) ? $this->formatTime($post['checkin-hour'] . ':' . $post['checkin-min']) : null;
            $ruleParams['checkouttime']  = $this->isNotEmpty('checkout-hour', $post) ? $this->formatTime($post['checkout-hour'] . ':' . $post['checkout-min']) : null;

            if (!empty($post['scheduleid'])) {
                $params = array(
                    'name'       => $post['name'],
                    'createtime' => time()
                );

                if (!empty($post['bgcolor'])) {
                    $existsColor = $daoSchedule->existsBgcolor($this->_user->orgId, $post['bgcolor'], $post['scheduleid']);
                    if ($existsColor) {
                        return $this->_this->json(false, '您提交的颜色不能与其他方案颜色重复');
                    }
                    $params['bgcolor'] = $post['bgcolor'];
                }

                $ret = $daoSchedule->updateSchedule($this->_user->orgId, $post['scheduleid'], $params);
                if (!$ret) {
                    return $this->_this->json(false, $this->lang['save_failed']);
                }

                $ret = $daoSchedule->updateScheduleRule($this->_user->orgId, $post['scheduleid'], $post['ruleid'], $ruleParams);
                if (!$ret) {
                    return $this->_this->json(false, $this->lang['save_failed']);
                }
            } else {
                $count = count($this->_bgColors);
                if ($daoSchedule->countSchedule($this->_user->orgId) >= $count) {
                    return $this->_this->json(false, sprintf('最多能创建 %s 个排班方案', $count));
                }

                if (!empty($post['bgcolor'])) {
                    $existsColor = $daoSchedule->existsBgcolor($this->_user->orgId, $post['bgcolor']);
                    if ($existsColor) {
                        return $this->_this->json(false, '您提交的颜色不能与其他方案颜色重复');
                    }
                }

                $params = array(
                    'scheduleid' => Dao_App_Attend_Schedule::getScheduleId(),
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $this->_user->uniqueId,
                    'name'       => $post['name'],
                    'createtime' => time()
                );
  
                if (!empty($post['bgcolor'])) {
                    $params['bgcolor'] = $post['bgcolor'];
                }

                $scheduleId = $daoSchedule->createSchedule($params);
                if (!$scheduleId) {
                    return $this->_this->json(false, $this->lang['save_failed']);
                }

                $ruleParams['orgid']      = $this->_user->orgId;
                $ruleParams['scheduleid'] = $scheduleId;
                $ruleParams['ruleid']     = Dao_App_Attend_Schedule::getRuleId();
                $ruleParams['createtime'] = time();

                $ruleId = $daoSchedule->createScheduleRule($ruleParams);
                if (!$ruleId) {
                    return $this->_this->json(false, $this->lang['save_failed']);
                }
            }
        }

        return $this->_this->json(true, $this->lang['save_success']);
    }

    /**
     * 更新方案颜色
     */
    public function updatecolorAction()
    {
        $scheduleId = $this->_request->getPost('scheduleid');
        $bgcolor    = $this->_request->getPost('bgcolor');
        $orgId      = $this->_user->orgId;

        if (!in_array($bgcolor, $this->_bgColors)) {
            return $this->_this->json(false, '您提交的颜色代码不符合要求');
        }

        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        $existsColor = $daoSchedule->existsBgcolor($orgId, $bgcolor, $scheduleId);
        if ($existsColor) {
            return $this->_this->json(false, '您提交的颜色不能与其他方案颜色重复');
        }

        $ret = $daoSchedule->updateSchedule($orgId, $scheduleId, array('bgcolor' => $bgcolor));
        if (!$ret) {
            return $this->_this->json(false, '更新排班方案颜色失败');
        }

        return $this->_this->json(true);
    }

    /**
     * 删除排班方案
     */
    public function deleteAction()
    {
        $scheduleId = $this->_request->getQuery('scheduleid');

        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'orgid'      => $this->_user->orgId,
            'scheduleid' => $scheduleId
        );
        $schedule = $daoSchedule->getSchedule($condition);

        // 方案不存在
        if (null === $schedule) {
            return $this->_this->json(false, $this->lang['schedule_plan_not_exists']);
        }

        // 系统排班方案不允许删除
        if ($schedule->isSystem) {
            return $this->_this->json(false, $this->lang['not_delete_system_schedule_plan']);
        }

        $ret = $daoSchedule->deleteSchedule($this->_user->orgId, $scheduleId, true);
        if (!$ret) {
            return $this->_this->json(false, $this->lang['delete_failed']);
        }

        return $this->_this->json(true, $this->lang['delete_success']);
    }

    /**
     * 排班月历输出
     */
    public function calendarAction()
    {
        $year     = $this->_request->getQuery('year');
        $month    = $this->_request->getQuery('month');
        $uniqueId = trim($this->_request->getQuery('unid'));
        $today    = mktime(0, 0, 0, date('m'), date('j'), date('Y'));

        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);
        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);

        // 读取排班方案
        $schedules = $daoSchedule->getSchedules(array('orgid' => $this->_user->orgId), array('isvalid' => true), 'createtime DESC')->toArray('scheduleid');

        // 读取当前月排班计划
        $condition = array('date' => $year . str_pad($month, 2, '0', STR_PAD_LEFT), 'uniqueid' => $uniqueId);
        $currPlan  = $daoPlan->getMonthPlan($condition);
        if ($currPlan !== null) {
            $currPlan = $currPlan->toArray();
            $currPlan = $currPlan['plan'];
        } else {
            $currPlan = $this->getWeekPlan($uniqueId, $year, $month);
        }

        // 处理月历
        $isNeedFirstLine = false; //月历第一行是否含有上月后几天的
        $isNeedLastLine  = false; //月历最后一行是否含有下月前几天的
        $monthFirst      = mktime(0, 0, 0, $month, 1, $year); //当前月第一天的时间戳
        $firstW          = (int) date('w', $monthFirst);//当前月第一天为星期几
        $t               = date('t', $monthFirst);//月天数
        $monthLast       = mktime(0, 0, 0, $month, $t, $year);//当前月最后一天的时间戳
        $lastW           = (int) date('w', $monthLast);//当前月最后一天为星期几
        $midStart        = 1;     //月历中间默认开始天号
        $midEnd          = $t;//月历中间默认结束天号

        if ($firstW != 0) {
            $start = $this->getCalendarCondition('last', $firstW, (int) $month, (int) $year);
            $isNeedFirstLine = true;
        } else {
            $start = mktime(0, 0, 0, $month, 1, $year);
        }

        if ($lastW != 6) {
            $end = $this->getCalendarCondition('next', $lastW, (int) $month, (int) $year);
            $isNeedLastLine = true;
        } else {
            $end = mktime(0, 0, 0, $month, $t, $year);
        }

        // 读取工作日调整
        $adjusts = $daoAdjust->getUserAdjusts(array('uniqueid' => $uniqueId, 'datetime' => array('start' => $start, 'end' => $end)))->toArray();
        // 读取考勤统计
        $dates   = $daoDate->getAttendDates(array('uniqueid' => $uniqueId, 'date' => array('start' => $start, 'end' => ($end + 86400))))->toArray('date');

        // 月历第一行含有上月后几天的
        if ($isNeedFirstLine) {
            $firstLine = array();
            $lastMonth = date('m', $start);
            $lastYear  = date('Y', $start);
            $i         = 1;
            $length    = 7 - (int) $firstW;

            // 读取上月排班计划
            $condition = array('date' => $lastYear . $lastMonth, 'uniqueid' => $uniqueId);
            $lastPlan      = $daoPlan->getMonthPlan($condition);
            if ($lastPlan !== null) {
                $lastPlan = $lastPlan->toArray();
                $lastPlan = $lastPlan['plan'];
            } else {
                $lastPlan = $this->getWeekPlan($uniqueId, $lastYear, $lastMonth);
            }

            for ($day = date('j', $start); $day <= date('t', $start); $day++) {
                $firstLine[] = $this->formatPlan($day, $lastMonth, $lastYear, $lastPlan, $schedules, $adjusts, $dates);
            }

            while ($i <= $length) {
                $firstLine[] = $this->formatPlan($i, $month, $year, $currPlan, $schedules, $adjusts, $dates);
                $i ++;
            }
            $midStart = $i;

            $this->view->firstline = $firstLine;
        }

        // 月历最后一行含有下月前几天的
        if ($isNeedLastLine) {
            $lastLine  = array();
            $nextMonth = date('m', $end);
            $nextYear  = date('Y', $end);
            $i         = (int) $t - $lastW;
            $midEnd    = $i - 1;
            while ($i <= $t) {
                $lastLine[] = $this->formatPlan($i, $month, $year, $currPlan, $schedules, $adjusts, $dates);
                $i ++;
            }

            // 读取下月排班计划
            $condition = array('date' => $nextYear . $nextMonth, 'uniqueid' => $uniqueId);
            $nextPlan  = $daoPlan->getMonthPlan($condition);
            if ($nextPlan !== null) {
                $nextPlan = $nextPlan->toArray();
                $nextPlan = $nextPlan['plan'];
            } else {
                $nextPlan = $this->getWeekPlan($uniqueId, $nextYear, $nextMonth);
            }

            for ($day = 1; $day <= date('j', $end); $day++) {
                $lastLine[] = $this->formatPlan($day, $nextMonth, $nextYear, $nextPlan, $schedules, $adjusts, $dates);
            }
            $this->view->lastline = $lastLine;
        }

        // 月历中间部分
        $midLines = array();
        $midLine  = array();
        for ($d = $midStart; $d <= $midEnd; $d++) {
            $midLine[] = $this->formatPlan($d, $month, $year, $currPlan, $schedules, $adjusts, $dates);
            if (count($midLine) == 7) {
                $midLines[] = $midLine;
                $midLine    = array();
            }
        }

        $this->view->isfirstline = $isNeedFirstLine;
        $this->view->islastline  = $isNeedLastLine;
        $this->view->midlines    = $midLines;
        $this->view->curtime     = time();
        $this->view->today       = $today;
        $this->view->uniqueid    = $uniqueId;
    }

    /**
     * 排班计划列表
     */
    public function userAction()
    {
        $query    = $this->_request->getQuery();
        $page     = max(1, (int) $this->_request->getQuery('page'));
        $pageSize = 20;

        $roles = $this->getRoles();

        $condition     = array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
        );
        $planCondition = array();
        if (!empty($query['deptid'])) {
            $dept = $query['deptid'];

            $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $deptIds = $daoDept->getChildDeptid($this->_user->orgId, $query['deptid']);
            $condition['deptid'] = array_merge((array) $dept, $deptIds);

        } else {

            if (empty($roles['admin']) && !empty($roles['sc']) && $this->_user->deptId) {
                if (!empty($roles['moderator'])) {
                    $depts = $this->getModerateDepts(true, true);
                } else {
                    $depts = $this->getRoleDepts(true, true);
                }

                $condition['deptid'] = $depts;
            }
        }

        if (!empty($query['keyword'])) {
            $condition['keyword'] = $query['keyword'];
        }

        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);

        $users = $daoCast->getCastUserPage($condition, 'deptid DESC', $page, $pageSize);

        $pageInfo = array(
            'query'       => $query,
            'currpage'    => $page,
            'pagecount'   => $users->pageCount(),
            'recordcount' => $users->recordCount(),
            'url'         => '/app/attend/schedule/user'
        );

        $users = $users->toArray('uniqueid');

        $uniqueIds = array();
        foreach ($users as $user) {
            $uniqueIds[] = $user['uniqueid'];

            $planCondition['uniqueid'] = $uniqueIds;
        }

        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);

        if (!isset($query['year'])) {
            $query['year'] = (int) date('Y');
        }

        if (!isset($query['month'])) {
            $query['month'] = (int) date('m');
        }

        $planCondition['date'] = $query['year'] . str_pad($query['month'], 2, '0', STR_PAD_LEFT);

        $plans = $daoPlan->getPlanList($planCondition, null, 'updatetime ASC')->toArray();

        foreach ($plans as $plan) {
            if (isset($users[$plan['uniqueid']])
                && empty($users['memo']))
            {
                $users[$plan['uniqueid']]['memo'] = $plan['memo'];
            }
        }

        if (empty($roles['admin']) && !empty($roles['sc']) && $this->_user->deptId) {
            if (!empty($roles['moderator'])) {
                $depts = $this->getModerateDepts(true);
            } else {
                $depts = $this->getRoleDepts(true);
            }
        } else {
            $depts = $this->getDepts();
        }

        $this->view->depts    = $depts;
        $this->view->users    = $users;
        $this->view->plans    = $plans;
        $this->view->pageinfo = $pageInfo;
        $this->view->query    = $query;
    }

    /**
     * 用户排班计划（月历表）
     */
    public function userplanAction()
    {
        $uniqueId = trim($this->_request->getQuery('unid'));
        $year  = $this->_request->getQuery('year');
        $month = $this->_request->getQuery('month');

        // 读取用户信息
        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        $user = $daoUser->getUserCard(array('orgid' => $this->_user->orgId, 'uniqueid' => $uniqueId));

        if (null === $user) {
            Oray_Function::alert($this->lang['user_not_exists'], '/app/attend/schedule/user');
        }

        $this->view->userinfo = $user;
        $this->view->back     = $this->_request->getQuery('back');
        $this->view->date     = array('year' => !empty($year) ? $year : date('Y'), 'month' => !empty($month) ? $month : date('m'));
    }

    /**
     * 修改免签班
     */
    public function exemptionAction()
    {
        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        $schedule = $daoSchedule->getSchedule(array('orgid' => $this->_user->orgId, 'scheduleid' => '^exemption'));

        $this->view->schedule = $schedule->toArray();
        $this->view->bgcolors = $this->_bgColors;
    }

    /**
     * 保存免签班
     */
    public function saveexemptionAction()
    {
        $users  = $this->_request->getPost('user');

        if (!is_array($users) || empty($users)) {
            return $this->json(false, $this->lang['param_user_null']);
        }

        /* @var $addressBook Tudu_AddressBook */
        $addressBook = Tudu_AddressBook::getInstance();
        $unIds       = array();

        // 整理用户唯一Id
        foreach ($users as $item) {
            // 帐号
            if (false !== strpos($item, '@')) {
                $user = $addressBook->searchUser($this->_user->orgId, $item);

                if ($user === null) {
                    continue;
                }

                $unIds[] = $user['uniqueid'];
            // 群组
            } else {
                $groupUsers = $addressBook->getGroupUsers($this->_user->orgId, $item);

                if (empty($groupUsers)) {
                    continue;
                }

                foreach ($groupUsers as $user) {
                    $unIds[] = $user['uniqueid'];
                }
            }
        }

        // 处理的用户不存在
        if (empty($unIds)) {
            return $this->json(false, '提交处理的用户不存在或已被删除');
        }

        $unIds      = array_unique($unIds);
        $effectDate = strtotime('tomorrow');
        $tomorrow   = date('j', $effectDate);
        $weekStart  = strtotime('last Monday');
        $date       = date('Ym');
        $nextMonth  = strtotime('+1 month');
        $nextY      = date('Y', $nextMonth);
        $nextM      = date('m', $nextMonth);
        $nextDate   = $nextY . $nextM;
        $nextDays   = date('t', $nextMonth);

        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);

        // 读取排班计划
        $condition = array('date' => $date, 'uniqueid' => $unIds);
        $plans     = $daoPlan->getMonthPlans($condition)->toArray('uniqueid');

        // 周排班计划
        $cycleNum = 1;
        $weekPlan = array();
        for ($i = 0; $i < 7; $i++) {
            $weekPlan[$i] = '^exemption';
        }
        $weekParams = array(
            'orgid'      => $this->_user->orgId,
            'plan'       => json_encode($weekPlan),
            'cyclenum'   => $cycleNum,
            'memo'       => null,
            'effectdate' => $effectDate
        );
        $planParams = array(
            'orgid'      => $this->_user->orgId,
            'date'       => $date,
            'memo'       => null,
            'updatetime' => time()
        );

        foreach ($unIds as $uniqueId) {
            $oldPlan  = $daoPlan->getWeekPlan(array('uniqueid' => $uniqueId));
            if ($oldPlan !== null) {
                $oldPlan = $oldPlan->toArray();
                $this->createOldMonthPlan($oldPlan, $uniqueId);
            }

            $weekParams['uniqueid'] = $uniqueId;

            $week = $daoPlan->updatePlanForWeek($weekParams);
            if (!$week) {
                continue;
            }

            // 根据周排班生成当前月的月排班计划
            $plan = isset($plans[$uniqueId]) ? $plans[$uniqueId]['plan'] : array();
            for ($i = $tomorrow; $i <= date('t'); $i++) {
                $dateTime = strtotime(date('Y') . '-' . date('m') . '-' . $i);
                $wd       = date('w', $dateTime);
                $diff     = $this->dateWeekDiff($dateTime, $weekStart);
                $value    = $wd + (($diff % $cycleNum) * 7);

                if (isset($weekPlan[$value])) {
                    $plan[$i] = $weekPlan[$value];
                }
            }

            if (empty($plan)) {
                continue;
            }

            $planParams['uniqueid'] = $uniqueId;
            $planParams['plan']     = json_encode($plan);

            $ret = $daoPlan->updatePlanForMonth($planParams);

            // 创建计划失败
            if (!$ret) {
                continue;
            }

            // 处理下月排班模板
            if (isset($nextDate) && isset($nextMonth) && !empty($weekPlan)) {
                $nextPlan = array();
                $i        = 1;

                while ($i <= $nextDays) {
                    $dateTime = strtotime($nextY . '-' . $nextM . '-' . $i);
                    $wd       = date('w', $dateTime);
                    $diff     = $this->dateWeekDiff($dateTime, $weekStart);
                    $value    = $wd + (($diff % $cycleNum) * 7);

                    if (isset($weekPlan[$value])) {
                        $nextPlan[$i] = $weekPlan[$value];
                    }
                    $i++;
                }

                if (!empty($nextPlan)) {
                    $nextMonthParams = array(
                        'orgid'      => $this->_user->orgId,
                        'uniqueid'   => $uniqueId,
                        'date'       => $nextDate,
                        'plan'       => json_encode($nextPlan),
                        'memo'       => null,
                        'updatetime' => time()
                    );

                    $daoPlan->updatePlanForMonth($nextMonthParams);
                }
            }
        }

        // 返回成功
        return $this->json(true, $this->lang['save_success']);
    }

    /**
     * 加载用户排班计划
     */
    public function loadplansAction()
    {
        $year      = $this->_request->getQuery('year');
        $month     = $this->_request->getQuery('month');
        $emails    = $this->_request->getQuery('email');
        $monthPlans=array();
        $uniqueIds = array();
        $tempEmails= array();

        if (!empty($emails)) {
            /* @var $addressBook Tudu_AddressBook */
            $addressBook = Tudu_AddressBook::getInstance();
            $emails      = explode(',', $emails);
            $emails      = array_unique($emails);
            foreach ($emails as $email) {
                if (strlen($email) <= 0) {
                    continue;
                }

                $user = $addressBook->searchUser($this->_user->orgId, $email);
                if ($user === null) {
                    continue;
                }

                $uniqueIds[$email] = $user['uniqueid'];
                $tempEmails[$user['uniqueid']] = $email;
            }
        }

        if (!empty($uniqueIds)) {
            /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
            $daoPlan   = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);
            $date      = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
            $condition = array('date' => $date, 'uniqueid' => $uniqueIds);
            $plans     = $daoPlan->getMonthPlans($condition)->toArray('uniqueid');
            $weekPlanUnids = array_diff($uniqueIds, array_keys($plans));

            foreach ($plans as $key => $item) {
                $monthPlans[$tempEmails[$key]] = $item['plan'];
            }

            if (!empty($weekPlanUnids)) {
                $weekPlans = $daoPlan->getWeekPlans(array('uniqueid' => $weekPlanUnids))->toArray('uniqueid');
                if (!empty($weekPlans)) {
                    $weekPlans  = $this->formatToMonthPlans($weekPlans, $year, $month);
                    foreach ($weekPlans as $key => $item) {
                        $monthPlans[$tempEmails[$key]] = $item;
                    }
                }
            }
        }

        return $this->json(true, null, $monthPlans);
    }

    /**
     * 排班计划，排班方案与用户关联编辑页面
     */
    public function planAction()
    {
        $uniqueId    = trim($this->_request->getQuery('uniqueid'));
        $emails      = $this->_request->getQuery('email');
        $year        = $this->_request->getQuery('year');
        $month       = $this->_request->getQuery('month');
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        $roles       = $this->getRoles();
        $condition   = array('orgid' => $this->_user->orgId);
        $uniqueIds   = explode(',', $uniqueId);
        $monthPlans  = array();

        if (!empty($emails)) {
            /* @var $addressBook Tudu_AddressBook */
            $addressBook = Tudu_AddressBook::getInstance();
            $emails      = explode(',', $emails);
            foreach ($emails as $email) {
                if (strlen($email) <= 0) {
                    continue;
                }

                $user = $addressBook->searchUser($this->_user->orgId, $email);
                if ($user === null) {
                    continue;
                }

                $uniqueIds[] = $user['uniqueid'];
            }
        }

        $schedules = $daoSchedule->getSchedules(
                $condition,
                array('isvalid' => true),
                'createtime DESC')
        ->toArray('scheduleid');

        $depts = array();
        if (empty($roles['admin']) && !empty($roles['sc']) && $this->_user->deptId) {
            if (!empty($roles['moderator'])) {
                $depts = $this->getModerateDepts(true);
            } else {
                $depts = $this->getRoleDepts(true);
            }
        }
        if (!empty($depts)) {
            $deptIds = array();
            foreach ($depts as $dept) {
                $deptIds[] = $dept['deptid'];
            }

            $this->view->deptids = implode(',', $deptIds);
        }

        if (empty($year)) {
            $year = date('Y');
        }

        if (empty($month)) {
            $month = date('m');
        }

        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan   = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);
        $query     = array('year' => $year, 'month' => $month);
        $date      = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
        $condition = array('date' => $date, 'uniqueid' => $uniqueIds);
        $plans     = $daoPlan->getMonthPlans($condition)->toArray('uniqueid');
        $weekPlanUnids = array_diff($uniqueIds, array_keys($plans));

        foreach ($plans as $key => $item) {
            $monthPlans[$key] = $item['plan'];
        }

        if (!empty($weekPlanUnids)) {
            $weekPlans = $daoPlan->getWeekPlans(array('uniqueid' => $weekPlanUnids))->toArray('uniqueid');
            if (!empty($weekPlans)) {
                $weekPlans  = $this->formatToMonthPlans($weekPlans, $year, $month);
                $monthPlans = array_merge($monthPlans, $weekPlans);
            }
        }

        if (!empty($monthPlans)) {
            $this->view->plans = $monthPlans;
        }

        $this->view->uniqueid    = $uniqueIds;
        $this->view->schedules   = $schedules;
        $this->view->query       = $query;
        $this->view->back        = $this->_request->getQuery('back');
        $this->view->bgcolors    = $this->_bgColors;
    }

    /**
     * 获取群组用户
     */
    public function getgroupusersAction()
    {
        $groupIds = explode(',', $this->_request->getQuery('groupid'));
        $members  = array();

        /* @var $addressBook Tudu_AddressBook */
        $addressBook = Tudu_AddressBook::getInstance();

        foreach ($groupIds as $groupId) {
            $members[] = $addressBook->getGroupUsers($this->_user->orgId, $groupId);
        }

        $ret = array();
        foreach ($members as $member) {
            foreach ($member as $uniqueId => $item) {
                $ret[$uniqueId] = $item;
            }
        }
        unset($members);

        return $this->json(true, null, $ret);
    }

    /**
     * plan.save
     * 保存排班计划
     */
    public function saveplanAction()
    {
        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);

        $members = (array) $this->_request->getPost('user');
        $type    = $this->_request->getPost('type');
        $post    = $this->_request->getPost();

        if (!is_array($members) || empty($members)) {
            return $this->json(false, $this->lang['param_user_null']);
        }

        // 月排班，如修改月份小于当前月，提示不能修改
        if ($type == 1){
            /*if ($post['year'] < date('Y') || ($post['year'] == date('Y') && $post['month'] < date('m'))) {
                return $this->json(false, '抱歉，不能修改以往的排班计划');
            }

            // 月排班，当前月修改刚好是最后一天，提示修改无效
            if ($post['year'] == date('Y') && $post['month'] == date('n') && date('t') == date('j')) {
                return $this->json(false, '抱歉，当前修改的排班计划是无效的');
            }*/

            $date = $post['year'] . str_pad($post['month'], 2, '0', STR_PAD_LEFT);
        } else {
            $date      = date('Ym');
            $nextMonth = strtotime('+1 month');
            $nextY     = date('Y', $nextMonth);
            $nextM     = date('m', $nextMonth);
            $nextDate  = $nextY . $nextM;
            $nextDays  = date('t', $nextMonth);
        }

        /* @var $addressBook Tudu_AddressBook */
        $addressBook = Tudu_AddressBook::getInstance();
        $unIds       = array();
        $user        = array();

        foreach ($members as $key => $member) {
            $userName        = $post['user-' . $member];
            $user[$userName] = $addressBook->searchUser($this->_user->orgId, $userName);

            if ($user[$userName] === null) {
                unset($members[$key]);
                continue;
            }

            $unIds[] = $user[$userName]['uniqueid'];
        }

        // 处理的用户不存在
        if (empty($unIds)) {
            return $this->json(false, '提交处理的用户不存在或已被删除');
        }

        $condition = array('date' => $date, 'uniqueid' => $unIds);
        $plans     = $daoPlan->getMonthPlans($condition)->toArray('uniqueid');
        $valueNum  = $post['valuenum'];
        $cycleNum  = (int) $post['cyclenum'];

        if (($post['year'] == date('Y') && $post['month'] == date('n')) || $type == 0) {
            $effectDate = strtotime('tomorrow');
            $weekStart  = strtotime('last Monday');
            $tomorrow   = date('j', $effectDate);
        }

        foreach ($members as $member) {
            $userName = $post['user-' . $member];
            if (!isset($user[$userName])) {
                continue;
            }

            $uniqueId = $user[$userName]['uniqueid'];

            // 周排班
            if ($type == 0) {
                $oldPlan = $daoPlan->getWeekPlan(array('uniqueid' => $uniqueId));
                if ($oldPlan !== null) {
                    $oldPlan = $oldPlan->toArray();
                    $this->createOldMonthPlan($oldPlan, $uniqueId);
                }

                for ($i = 0; $i <= $valueNum; $i++) {
                    if (isset($post['value-' . $member . '-' . $i])) {
                        $weekPlan[$i] = $post['value-' . $member . '-' . $i];
                    }
                }

                if (empty($weekPlan)) {
                    continue;
                }

                // 更新用户周排班计划
                $weekParams = array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $uniqueId,
                    'plan'       => json_encode($weekPlan),
                    'cyclenum'   => $cycleNum,
                    'memo'       => trim($post['memo']),
                    'effectdate' => $effectDate
                );

                $week = $daoPlan->updatePlanForWeek($weekParams);
                if (!$week) {
                    return $this->json(false, $this->lang['save_plan_failed']);
                }

                // 根据周排班生成当前月的月排班计划
                $plan = isset($plans[$uniqueId]) ? $plans[$uniqueId]['plan'] : array();
                for ($i = $tomorrow; $i <= date('t'); $i++) {
                    $dateTime = strtotime(date('Y') . '-' . date('m') . '-' . $i);
                    $wd       = date('w', $dateTime);
                    $diff     = $this->dateWeekDiff($dateTime, $weekStart);
                    $value    = $wd + (($diff % $cycleNum) * 7);

                    if (isset($weekPlan[$value])) {
                        $plan[$i] = $weekPlan[$value];
                    }
                }

                if (empty($plan)) {
                    continue;
                }

                $params = array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $uniqueId,
                    'date'       => $date,
                    'plan'       => json_encode($plan),
                    'memo'       => trim($post['memo']),
                    'updatetime' => time()
                );

                $ret = $daoPlan->updatePlanForMonth($params);

                // 创建计划失败
                if (!$ret) {
                    return $this->json(false, $this->lang['save_plan_failed']);
                }

                // 处理下月排班模板
                if (isset($nextDate) && isset($nextMonth) && !empty($weekPlan)) {
                    $nextPlan = array();
                    $i        = 1;

                    while ($i <= $nextDays) {
                        $dateTime = strtotime($nextY . '-' . $nextM . '-' . $i);
                        $wd       = date('w', $dateTime);
                        $diff     = $this->dateWeekDiff($dateTime, $weekStart);
                        $value    = $wd + (($diff % $cycleNum) * 7);

                        if (isset($weekPlan[$value])) {
                            $nextPlan[$i] = $weekPlan[$value];
                        }
                        $i++;
                    }

                    if (!empty($nextPlan)) {
                        $nextMonthParams = array(
                            'orgid'      => $this->_user->orgId,
                            'uniqueid'   => $uniqueId,
                            'date'       => $nextDate,
                            'plan'       => json_encode($nextPlan),
                            'memo'       => trim($post['memo']),
                            'updatetime' => time()
                        );

                        $daoPlan->updatePlanForMonth($nextMonthParams);
                    }
                }

            // 月排班
            } else {
                $plan = isset($plans[$uniqueId]) ? $plans[$uniqueId]['plan'] : array();
                if (empty($plan)) {
                    $oldPlan = $daoPlan->getWeekPlan(array('uniqueid' => $uniqueId));
                    if ($oldPlan !== null) {
                        $oldPlan = $oldPlan->toArray();
                        $this->createOldMonthPlan($oldPlan, $uniqueId);
                        $plan = $this->formatMonthPlan($oldPlan, $uniqueId, date('Y'), date('m'));
                    }
                }
                $tempPlan = $plan;
                for ($i = 0; $i <= $valueNum; $i++) {
                    // 当月的小于明天的不作处理
                    /*if (isset($tomorrow) && $i < $tomorrow) {
                        continue;
                    }*/

                    if (isset($post['value-' . $member . '-' . $i])) {
                        $plan[$i] = $post['value-' . $member . '-' . $i];
                    }
                }

                if (empty($plan)) {
                    continue;
                }

                $params = array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $uniqueId,
                    'date'       => $date,
                    'plan'       => json_encode($plan),
                    'memo'       => trim($post['memo']),
                    'updatetime' => time()
                );

                $ret = $daoPlan->updatePlanForMonth($params);

                // 创建计划失败
                if (!$ret) {
                    return $this->json(false, $this->lang['save_plan_failed']);
                }

                if (!empty($tempPlan)) {
                    $diffDates = array();
                    foreach ($tempPlan as $key => $value) {
                        foreach ($plan as $day => $item) {
                            if (isset($tomorrow) && $key >= $tomorrow) {
                                continue;
                            }
                            if ($key == $day && $value != $item) {
                                $diffDates[$key] = $item;
                            }
                        }
                    }

                    // 更新以往考勤数据
                    $this->updateCheckinData($uniqueId, $post['year'], $post['month'], $diffDates);
                }
            }
        }

        // 返回成功
        return $this->json(true, $this->lang['save_plan_success']);
    }

    /**
     * 工作日调整
     */
    public function adjustAction()
    {
        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);

        $adjusts = $daoAdjust->getAdjusts(array('orgid' => $this->_user->orgId), 'createtime DESC');

        $this->view->adjusts = $adjusts->toArray();
    }

    /**
     * 保存工作日调整
     */
    public function modifyadjustAction()
    {
        // 权限验证
        $role = $this->getRoles();
        if (empty($role['admin'])) {
            Oray_Function::alert('您没有创建或修改工作日调整的权限', '/app/attend/schedule/index');
        }

        $adjustId = $this->_request->getQuery('adjustid');
        $deptIds  = array();

        if (!empty($adjustId)) {
            /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
            $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);

            $adjust = $daoAdjust->getAdjust(array('adjustid' => $adjustId));

            //
            if (null === $adjust || $adjust->orgId != $this->_user->orgId) {
                Oray_Function::alert($this->lang['adjust_not_exists']);
            }

            $users = $daoAdjust->getUsers(array('adjustid' => $adjustId));

            $this->view->adjust = $adjust->toArray();
            $this->view->users  = $users;
        }

        $roles = $this->getRoles();
        $depts = array();
        if (empty($roles['admin']) && !empty($roles['sc']) && $this->_user->deptId) {
            if (!empty($roles['moderator'])) {
                $depts = $this->getModerateDepts(true);
            } else {
                $depts = $this->getRoleDepts(true);
            }
        }
        foreach ($depts as $dept) {
            $deptIds[] = $dept['deptid'];
        }

        $this->view->deptids = implode(',', $deptIds);
    }

    /**
     * 保存工作日调整
     */
    public function saveadjustAction()
    {
        // 权限验证
        $role = $this->getRoles();
        if (empty($role['admin'])) {
            Oray_Function::alert('您没有创建或修改工作日调整的权限', '/app/attend/schedule/index');
        }

        $adjustId = $this->_request->getPost('adjustid');
        $post     = $this->_request->getPost();
        $users    = $this->_request->getPost('user');

        if (!is_array($users) || empty($users)) {
            return $this->json(false, $this->lang['param_user_null']);
        }

        $startTime = strtotime($post['starttime']);
        $endTime   = strtotime($post['endtime']);
        $params    = array(
            'subject'   => $post['subject'],
            'starttime' => $startTime,
            'endtime'   => $endTime,
            'type'      => $post['type']
        );
        $adjustDate = array(
            'starttime' => $startTime,
            'endtime'   => $endTime
        );

        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);

        if ($adjustId) {
            $adjust = $daoAdjust->getAdjust(array('adjustid' => $adjustId));

            // 不存在
            if (null === $adjust || $adjust->orgId != $this->_user->orgId) {
                return $this->json(false, $this->lang['adjust_not_exists']);
            }

            $daoAdjust->updateAdjust($adjustId, $params);

            $daoAdjust->removeUser($adjustId);

        } else {
            $params['orgid']      = $this->_user->orgId;
            $params['adjustid']   = Dao_App_Attend_Schedule_Adjust::getAdjustId();
            $params['createtime'] = time();

            $adjustId = $daoAdjust->createAdjust($params);
        }

        // 添加用户
        $addressBook = Tudu_AddressBook::getInstance();
        foreach ($users as $item) {
            // 帐号
            if (false !== strpos($item, '@')) {
                $members = array($addressBook->searchUser($this->_user->orgId, $item));
            // 群组
            } else {
                $members = $addressBook->getGroupUsers($this->_user->orgId, $item);
            }

            foreach ($members as $user) {
                // 添加用户关联
                $daoAdjust->addUser(array(
                    'orgid'      => $this->_user->orgId,
                    'adjustid'   => $adjustId,
                    'uniqueid'   => $user['uniqueid'],
                    'createtime' => time()
                ));

                $this->updateAdjustAttend($user['uniqueid'], $post['type'], $adjustDate);
            }
        }

        return $this->json(true, $this->lang['save_adjust_success']);
    }

    /**
     * 删除
     */
    public function deleteadjustAction()
    {
        $adjustId = $this->_request->getPost('adjustid');

        if (empty($adjustId)) {
            return $this->json(false, $this->lang['param_adjust_null']);
        }

        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);

        $adjust = $daoAdjust->getAdjust(array('adjustid' => $adjustId));

        // 不存在
        if (null === $adjust || $adjust->orgId != $this->_user->orgId) {
            return $this->json(false, $this->lang['adjust_not_exists']);
        }

        $daoAdjust->deleteAdjust($adjustId);

        return $this->json(true, $this->lang['delete_adjust_success']);
    }

    /**
     *
     * @param string $uniqueId
     * @param int $type
     * @param array $dates
     */
    public function updateAdjustAttend($uniqueId, $type, $dates)
    {
        $today = strtotime(date('Y-m-d'));
        if (empty($uniqueId) || $dates['starttime'] > $today) {
            return ;
        }

        $dateArr = array();
        $start   = $dates['starttime'];
        $orgId   = $this->_user->orgId;
        while ($start <= $today) {
            $dateArr[] = $start;
            $start += 86400;
        }

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate  = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);

        $startDate   = $dateArr[0];
        $popDate     = array_pop($dateArr);
        $endDate     = $popDate + 86400;
        $condition   = array(
            'uniqueid' => $uniqueId,
            'orgid'    => $orgId,
            'date'     => array('start' => $startDate, 'end' => $endDate)
        );
        $attendDates = $daoDate->getAttendDates($condition)->toArray('date');

        $arr = array();
        $month = array();
        foreach ($attendDates as $date => $item) {
            $arr[]   = $date;
            $m       = date('Ym', $date);
            if (!array_key_exists($m, $month)) {
                $month[$m] = array('month' => (int) date('m', $date), 'year' => (int) date('Y', $date));
            }

            $params  = array();
            if ($type == 0) {
                if ($item['islate'] || $item['isleave'] || $item['iswork']) {
                    $params['islate'] = 0;
                    $params['isleave'] = 0;
                    $params['iswork'] = 0;
                }
            }

            if (!empty($params)) {
                $daoDate->update($uniqueId, $date, $params);
            }
        }

        if ($type == 1) {
            $diffDate = array_diff($dateArr, $arr);
            foreach ($diffDate as $d) {
                $m = date('Ym', $d);
                if (!array_key_exists($m, $month)) {
                    $month[$m] = array('month' => (int) date('m', $d), 'year' => (int) date('Y', $d));
                }
                $params = array(
                    'orgid'    => $orgId,
                    'uniqueid' => $uniqueId,
                    'date'     => $d,
                    'iswork'   => 1
                );

                $daoDate->create($params);
            }
        }

        // 更新月统计
        foreach ($month as $ym => $item) {
            $monthParams = array();
            $sum = $daoDate->dateSum(array(
                'uniqueid'  => $uniqueId,
                'startdate' => mktime(0, 0, 0, $item['month'], 1, $item['year']),
                'enddate'   => mktime(0, 0, 0, $item['month'] + 1, 1, $item['year'])
            ));

            if (!empty($sum)) {
                $monthParams['updatetime'] = time();
                $monthParams['late']   = (int) $sum['late'];
                $monthParams['leave']  = (int) $sum['leave'];
                $monthParams['unwork'] = (int) $sum['unwork'];
                $daoMonth->update($uniqueId, $ym, $monthParams);
            }
        }
    }

    /**
     *
     * @param string $uniqueId
     * @param int $year
     * @param int $month
     * @param array $dates
     */
    public function updateCheckinData($uniqueId, $year, $month, $dates)
    {
        if (empty($dates)) {
            return ;
        }

        $orgId       = $this->_user->orgId;
        $updateMonth = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate  = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);
        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        foreach ($dates as $day => $scheduleId) {
            $schedule = null;
            $handle   = 'default';
            $date     = strtotime($year . '-' . $month . '-' . $day);
            if ($scheduleId == '^off') {
                $handle = 'off';
            } else {
                // 若调整为非工作日
                $adjust = $daoAdjust->getUserAdjust(array('uniqueid' => $uniqueId, 'datetime' => $date));
                if (null !== $adjust && $adjust->type == 0) {
                    $handle = 'off';
                } else {
                    $schedule = $daoSchedule->getSchedule(array('orgid' => $orgId, 'scheduleid' => $scheduleId), array('isvalid' => true));
                    if (null === $schedule) {
                        $schedule = $daoSchedule->getSchedule(array(
                            'orgid'      => $orgId,
                            'scheduleid' => '^default',
                            'week'       => date('w', $date),
                            'status'     => 1
                        ));
                    }
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                    if ($schedule === null) {
                        $handle = 'off';
                    }
                }
            }

            switch ($handle) {
                case 'off':
                    // 更新考勤状况
                    $params = array(
                        'islate'  => 0,
                        'isleave' => 0,
                        'iswork'  => 0,
                        'checkinstatus' => 0
                    );
                    $exists = $daoDate->existsRecord($uniqueId, $date);
                    if (!$exists) {
                        $params['orgid']    = $orgId;
                        $params['uniqueid'] = $uniqueId;
                        $params['date']     = $date;

                        $daoDate->create($params);
                    } else {
                        $daoDate->update($uniqueId, $date, $params);
                    }
                    break;

                case 'default':
                default:
                    $checkins = $this->getCheckins($uniqueId, $date);
                    $checkinRecord = null;
                    foreach ($checkins as $checkin) {
                        $checkinTime = $checkin['createtime'];
                        if ($checkin['type'] == 0) {
                            $checkinRecord = $checkin;
                            $status = Dao_App_Attend_Checkin::STATUS_NORMAL;
                            if (!empty($schedule['checkintime'])) {
                                $setCheckinTime = $date + $this->formatTime($schedule['checkintime']);
                                if (!empty($schedule['latestandard'])) {
                                    $setCheckinTime += $schedule['latestandard'] * 60;
                                }
                                if ($checkinTime > $setCheckinTime) {
                                    $status = Dao_App_Attend_Checkin::STATUS_LATE;
                                    if (!empty($schedule['latecheckin'])) {
                                        $outworkTime    = $schedule['latecheckin'] * 60;
                                        $setCheckinTime = $date + $this->formatTime($schedule['checkintime']);
                                        if ($checkinTime - $setCheckinTime > $outworkTime) {
                                            $status = Dao_App_Attend_Checkin::STATUS_WORK;
                                        }
                                    }
                                }
                            }
                            if ($checkin['status'] != $status) {
                                $daoCheckin->updateCheckin($checkin['checkinid'], array(
                                    'status' => $status
                                ));
                            }
                        } elseif ($checkin['type'] == 1) {
                            $status = Dao_App_Attend_Checkin::STATUS_NORMAL;
                            if (!empty($schedule['checkouttime'])) {
                                $setCheckoutTime = $date + $this->formatTime($schedule['checkouttime']);
                                $calType = 0;
                                $cinTime = 0;
                                if ($schedule['checkintime']) {
                                    $calType = null === $checkinRecord ? 0 : 1;

                                    if (null !== $checkinRecord) {
                                        $cinTime     = $this->formatTime(date('H:i', $checkin['createtime']));
                                        $planCinTime = $this->formatTime($schedule['checkintime']);
                                        if ($planCinTime > $cinTime) {
                                            $cinTime = $planCinTime;
                                        }
                                    }
                                }

                                if ($cinTime == 0) {
                                    $calType = 0;
                                }

                                switch ($calType) {
                                    case 0:
                                        if ($checkinTime < $setCheckoutTime) {
                                            $status = Dao_App_Attend_Checkin::STATUS_LEAVE;
                                            if (!empty($schedule['leavecheckout'])) {
                                                $outworkTime     = $schedule['leavecheckout'] * 60;
                                                $setCheckoutTime = $date + $this->formatTime($schedule['checkouttime']);
                                                if ($setCheckoutTime - $checkinTime > $outworkTime) {
                                                    $status = Dao_App_Attend_Checkin::STATUS_WORK;
                                                }
                                            }
                                        }

                                        break;
                                    case 1:
                                        $planWorkTime = $this->formatTime($schedule['checkouttime']) - $this->formatTime($schedule['checkintime']);
                                        $userWorkTime = $this->formatTime(date('H:i', $checkinTime)) - $cinTime;

                                        if ($userWorkTime < $planWorkTime) {
                                            $status = Dao_App_Attend_Checkin::STATUS_LEAVE;
                                            if (!empty($schedule['leavecheckout']) && $this->calculateTime($userWorkTime, $planWorkTime) > $schedule['leavecheckout'] * 60) {
                                                $status = Dao_App_Attend_Checkin::STATUS_WORK;
                                            }

                                        }

                                        break;
                                }
                            }
                            if ($checkin['status'] != $status) {
                                $daoCheckin->updateCheckin($checkin['checkinid'], array(
                                    'status' => $status
                                ));
                            }
                        }
                    }
  
                    // 考勤当天是否有考勤申请
                    $isApply = $daoDate->isApply($uniqueId, $date);
                    $isLate  = false;
                    $isLeave = false;
                    $isWork  = false;
                    $dateParams = array();

                    $checkins = $this->getCheckins($uniqueId, $date);
                    foreach ($checkins as $checkin) {
                        if (!$isApply) {
                            if ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_LATE) {
                                $isLate = true;
                            } elseif ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_LEAVE) {
                                $isLeave = true;
                            } elseif ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_WORK) {
                                $isWork = true;
                            }
                        }
                    }

                    if (!empty($schedule) && empty($checkins)) {
                        $isWork = true;
                    }

                    $dateParams['iswork']  = $isWork ? true : false;
                    $dateParams['islate']  = $isLate? true : false;
                    $dateParams['isleave'] = $isLeave ? true : false;
                    $dateParams['checkinstatus'] = !empty($schedule) ? $this->getCheckinStatus($checkins, $schedule) : 0;

                    $exists = $daoDate->existsRecord($uniqueId, $date);
                    if (!$exists) {
                        $dateParams['orgid']    = $orgId;
                        $dateParams['uniqueid'] = $uniqueId;
                        $dateParams['date']     = $date;

                        $daoDate->create($dateParams);
                    } else {
                        $daoDate->update($uniqueId, $date, $dateParams);
                    }
            }
        }

        $sum = $daoDate->dateSum(array(
            'uniqueid'  => $uniqueId,
            'startdate' => mktime(0, 0, 0, $month, 1, $year),
            'enddate'   => mktime(0, 0, 0, $month + 1, 1, $year)
        ));

        if (!empty($sum)) {
            $monthParams['updatetime'] = time();
            $monthParams['late']       = (int) $sum['late'];
            $monthParams['leave']      = (int) $sum['leave'];
            $monthParams['unwork']     = (int) $sum['unwork'];
            $daoMonth->update($uniqueId, $updateMonth, $monthParams);
        }
    }

    /**
     * 获取签到、签退状态
     */
    public function getCheckinStatus($checkins, $plan)
    {
        $checkinStatus = 0;

        if (null === $checkins) {
            if ($plan !== null) {
            if ($plan['checkintime'] && $plan['checkouttime']) {
                    $checkinStatus = 3;
                } elseif ($plan['checkintime'] && !$plan['checkouttime']) {
                    $checkinStatus = 1;
                } elseif (!$plan['checkintime'] && $plan['checkouttime']) {
                    $checkinStatus = 2;
                }
            }
            return $checkinStatus;
        }

        $type     = array();
        $sum      = array();
        foreach ($checkins as $checkin) {
            $type[] = $checkin['type'];
        }

        // 没上班签到
        if ($plan !== null && $plan['checkintime'] && !in_array(0, $type)) {
            $sum[] = 1;
        }
        // 没下班签退
        if ($plan !== null && $plan['checkouttime'] && !in_array(1, $type)) {
            $sum[] = 2;
        }

        if (!empty($sum)) {
            $checkinStatus = array_sum($sum);
        }

        return $checkinStatus;
    }

    /**
     * 签到记录
     */
    public function getCheckins($uniqueId, $date, $sort = null)
    {
        $ret = array();
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'uniqueid' => $uniqueId,
            'date'     => $date
        );

        $records = $daoCheckin->getCheckins($condition, null, 'createtime ASC, type ASC');

        if (!empty($sort)) {
            $ret = $records->toArray($sort);
        } else {
            $ret = $records->toArray();
        }

        return $ret;
    }

    /**
     *
     * @param array $weekPlan
     * @param string $uniqueId
     * @param int $year
     * @param int $month
     */
    public function formatMonthPlan($weekPlan, $uniqueId, $year, $month)
    {
        if (empty($weekPlan)) {
            return array();
        }

        $w         = date('w', $weekPlan['effectdate']) == 0 ? 7 : date('w', $weekPlan['effectdate']);
        $sd        = date('j', $weekPlan['effectdate']) - ($w - 1);
        $weekStart = strtotime(date('Y', $weekPlan['effectdate']) . '-' . date('m', $weekPlan['effectdate']) . '-' . $sd);

        $first = strtotime($year . '-' . $month . '-01');
        $days  = date('t', $first);
        $plan  = array();
        $i     = 1;

        while ($i <= $days) {
            $dateTime = strtotime($year . '-' . $month . '-' . $i);
            $wd       = date('w', $dateTime);
            $diff     = $this->dateWeekDiff($dateTime, $weekStart);
            $value    = $wd + (($diff % $weekPlan['cyclenum']) * 7);
            $plan[$i] = $weekPlan['plan'][$value];
            $i++;
        }

        return $plan;
    }

    /**
     * 通过周排班创建以往月排班数据（过去已往没有的月份）
     *
     * @param array $weekPlan
     */
    public function createOldMonthPlan($weekPlan, $uniqueId)
    {
        if (empty($weekPlan)) {
            return;
        }

        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);
        $oldYM = date('Ym', $weekPlan['effectdate']);

        if (date('Ym')> $oldYM) {
            $months    = array();
            $start     = strtotime('+1 month', $weekPlan['effectdate']);
            $end       = time();
            $w         = date('w', $weekPlan['effectdate']) == 0 ? 7 : date('w', $weekPlan['effectdate']);
            $sd        = date('j', $weekPlan['effectdate']) - ($w - 1);
            $weekStart = strtotime(date('Y', $weekPlan['effectdate']) . '-' . date('m', $weekPlan['effectdate']) . '-' . $sd);

            do {
                $months[] = array('value' => date('Ym', $start), 'year' => date('Y', $start), 'month' => date('m', $start));
                $start    = strtotime('+1 month', $start);
            } while($start < $end);

            foreach ($months as $month) {
                $exists = $daoPlan->existsMonthPlan($uniqueId, $month['value']);
                if (!$exists) {
                    $first = strtotime($month['year'] . '-' . $month['month'] . '-01');
                    $days  = date('t', $first);
                    $plan  = array();
                    $i     = 1;

                    while ($i <= $days) {
                        $dateTime = strtotime($month['year'] . '-' . $month['month'] . '-' . $i);
                        $wd       = date('w', $dateTime);
                        $diff     = $this->dateWeekDiff($dateTime, $weekStart);
                        $value    = $wd + (($diff % $weekPlan['cyclenum']) * 7);
                        $plan[$i] = $weekPlan['plan'][$value];
                        $i++;
                    }

                    if (!empty($plan)) {
                        $params = array(
                            'orgid'      => $this->_user->orgId,
                            'uniqueid'   => $uniqueId,
                            'date'       => $month['value'],
                            'plan'       => json_encode($plan),
                            'memo'       => $weekPlan['memo'],
                            'updatetime' => time()
                        );

                        $daoPlan->updatePlanForMonth($params);
                    }
                }
            }
        }
    }

    /**
     * 获取月历查询条件
     *
     * @param string $type
     * @param int    $w
     * @param int    $month
     * @param int    $year
     */
    public function getCalendarCondition($type, $w, $month, $year)
    {
        if ($type == 'last') {
            $month = $month - 1;
            $firstDay  = mktime(0, 0, 0, $month, 1, $year);
            $monthDays = date('t', $firstDay);
            $day = $monthDays - ($w - 1);
        } else {
            $month = $month + 1;
            $day = 6 - $w;
        }

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * 获取默认班细信息
     */
    public function getDefaultPlan($week)
    {
        if (!isset($this->defaultPlan[$week])) {
            /* @var $daoSchedule Dao_App_Attend_Schedule */
            $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

            $condition = array(
                'orgid'      => $this->_user->orgId,
                'scheduleid' => '^default',
                'week'       => $week
            );

            $schedule = $daoSchedule->getSchedule($condition);

            $this->defaultPlan[$week] = (null !== $schedule) ? $schedule->toArray() : null;
        }

        return $this->defaultPlan[$week];
    }

    /**
     * 获取周排班并格式化
     *
     * @param string $uniqueId
     * @param int $year
     * @param int $month
     */
    public function getWeekPlan($uniqueId, $year, $month)
    {
        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);

        $weekPlan = $daoPlan->getWeekPlan(array('uniqueid' => $uniqueId));
        if ($weekPlan !== null) {
            $weekPlan = $weekPlan->toArray();
            $plan = $this->formatToMonthPlans($weekPlan, $year, $month, true);

            return $plan;
        }

        return array();
    }

    /**
     *
     * @param array $adjusts
     * @param int   $dateTime
     * @return array
     */
    public function getAdjustByDate($adjusts, $dateTime)
    {
        $ret = array();
        if (empty($adjusts)) {
            return $ret;
        }

        foreach ($adjusts as $adjust) {
            if ($adjust['starttime'] <= $dateTime && $adjust['endtime'] >= $dateTime) {
                $ret = $adjust;
                break;
            }
        }

        return $ret;
    }

    /**
     * 格式化处理月历排班有效数据
     *
     * @param int|string $day
     * @param int|string $month
     * @param int|string $year
     * @param array $plan
     * @param array $schedules
     * @param array $adjusts
     * @param array $dates
     * @return array
     */
    public function formatPlan($day, $month, $year, $plan, $schedules, $adjusts, $dates)
    {
        $rs             = array('day' => $day, 'month' => $month, 'year' => $year);
        $dateTime       = strtotime($year . '-' . $month . '-' . $day);
        $today          = strtotime(date('Y-m-d'));
        $rs['week']     = date('w', $dateTime);
        $rs['datetime'] = $dateTime;

        // 处理有工作日调整的情况
        $adjust = $this->getAdjustByDate($adjusts, $dateTime);
        if (!empty($adjust)) {
            if ($adjust['type'] == 0) {
                $rs['scheduleid'] = '^off';
                $rs['name']       = '';
                return $rs;
            }
        }

        // 有设置排班
        if (!empty($plan) && isset($plan[$day])) {
            $scheduleId       = $plan[$day];
            $rs['scheduleid'] = $scheduleId;

            if ($scheduleId != '^off') {
                $schedule = array();
                if (!isset($schedules[$scheduleId])) {
                    $scheduleId = '^default';
                }

                if ($scheduleId == '^default') {
                    $schedule = $this->getDefaultPlan(date('w', $dateTime));
                    if (!empty($schedule) && !$schedule['status']) {
                        $schedule = array();
                    }
                } elseif (isset($schedules[$scheduleId])) {
                    $schedule = $schedules[$scheduleId];
                }

                if (empty($schedule)) {
                    $rs['scheduleid'] = '^off';
                    $rs['name']       = '';
                } else {
                    $rs['checkintime']  = $schedule['checkintime'];
                    $rs['checkouttime'] = $schedule['checkouttime'];
                    $rs['name']         = $schedule['name'];
                    // 考勤状态异常
                    if (isset($dates[$dateTime])) {
                        $attendDate = $dates[$dateTime];
                        if ($dateTime != $today && ($attendDate['islate'] || $attendDate['iswork'] || $attendDate['isleave'])) {
                            $rs['mark'] = true;
                        }
                    }
                }
            } else {
                // 不用上班的，且有调整为工作日的
                if (!empty($adjust) && $adjust['type'] == 1) {
                    $schedule = $this->getDefaultPlan(date('w', $dateTime));
                    if (empty($schedule)) {
                        $rs['scheduleid'] = '^off';
                        $rs['name']       = '';
                    } else {
                        $rs['scheduleid']   = $schedule['scheduleid'];
                        $rs['checkintime']  = $schedule['checkintime'];
                        $rs['checkouttime'] = $schedule['checkouttime'];
                        $rs['name']         = $schedule['name'];
                        // 考勤状态异常
                        if (isset($dates[$dateTime])) {
                            $attendDate = $dates[$dateTime];
                            if ($dateTime != $today && ($attendDate['islate'] || $attendDate['iswork'] || $attendDate['isleave'])) {
                                $rs['mark'] = true;
                            }
                        }
                    }
                } else {
                    $rs['name'] = '';
                }
            }
        // 无设置排班，采用默认班
        } else {
            $schedule = $this->getDefaultPlan(date('w', $dateTime));
            if (empty($adjust) && !empty($schedule) && !$schedule['status']) {
                $schedule = array();
            }
            if (empty($schedule)) {
                $rs['scheduleid'] = '^off';
                $rs['name']       = '';
            } else {
                $rs['scheduleid']   = $schedule['scheduleid'];
                $rs['checkintime']  = $schedule['checkintime'];
                $rs['checkouttime'] = $schedule['checkouttime'];
                $rs['name']         = $schedule['name'];
                // 考勤状态异常
                if (isset($dates[$dateTime])) {
                    $attendDate = $dates[$dateTime];
                    if ($dateTime != $today && ($attendDate['islate'] || $attendDate['iswork'] || $attendDate['isleave'])) {
                        $rs['mark'] = true;
                    }
                }
            }
        }

        return $rs;
    }

    /**
     * 处理成月排班排班表
     *
     * @param array $plans
     * @param int   $year
     * @param int   $month
     */
    public function formatToMonthPlans($plans, $year, $month, $isCalendar = false)
    {
        if (empty($plans)) {
            return array();
        }

        $monthPlan = array();
        $first     = strtotime($year . '-' . $month . '-01');
        $days      = date('t', $first);

        if (!$isCalendar) {
            foreach ($plans as $unId => $plan) {
                $w     = date('w', $plan['effectdate']) == 0 ? 7 : date('w', $plan['effectdate']);
                $sd    = date('j', $plan['effectdate']) - ($w - 1);
                $start = strtotime(date('Y', $plan['effectdate']) . '-' . date('m', $plan['effectdate']) . '-' . $sd);
                $temp  = array();
                $i     = 1;

                while ($i <= $days) {
                    $dateTime = strtotime($year . '-' . $month . '-' . $i);
                    if ($dateTime >= $plan['effectdate']) {
                        $wd       = date('w', $dateTime);
                        $diff     = $this->dateWeekDiff($dateTime, $start);
                        $value    = $wd + (($diff % $plan['cyclenum']) * 7);
                        $temp[$i] = $plan['plan'][$value];
                    }
                    $i++;
                }
                $monthPlan[$unId] = $temp;
            }
        } else {
            $w     = date('w', $plans['effectdate']) == 0 ? 7 : date('w', $plans['effectdate']);
            $sd    = date('j', $plans['effectdate']) - ($w - 1);
            $start = strtotime(date('Y', $plans['effectdate']) . '-' . date('m', $plans['effectdate']) . '-' . $sd);
            $temp  = array();
            $i     = 1;

            while ($i <= $days) {
                $dateTime = strtotime($year . '-' . $month . '-' . $i);
                if ($dateTime >= $plans['effectdate']) {
                    $wd       = date('w', $dateTime);
                    $diff     = $this->dateWeekDiff($dateTime, $start);
                    $value    = $wd + (($diff % $plans['cyclenum']) * 7);
                    $temp[$i] = $plans['plan'][$value];
                }
                $i++;
            }
            $monthPlan = $temp;
        }

        return $monthPlan;
    }

    /**
     * 格式化时间（返回秒）
     *
     * @param string $str
     * @return int
     */
    public function formatTime($str)
    {
        if (!$str) {
            return 0;
        }

        $arr = explode(':', $str);
        $sec = (int) $arr[0] * 3600;
        $sec = $sec + (int) $arr[1] * 60;

        return $sec;
    }

    /**
     * 计算日期的周数
     */
    public function dateWeekDiff($curr, $start)
    {
        $ret = $curr - $start;
        $ret = floor($ret / 604800);

        return $ret;
    }

    /**
     * 计算时间间隔
     *
     * @param int $time1
     * @param int $time2
     */
    public function calculateTime($time1, $time2)
    {
        if ($time1 > $time2) {
            return $time1 - $time2;
        }

        return $time2 - $time1;
    }

    /**
     * 产生随机颜色
     */
    private function randomColor()
    {
        $str = '#';
        for($i = 0 ; $i < 6 ; $i++) {
            $randNum = rand(0 , 15);
            switch ($randNum) {
                case 10: $randNum = 'A'; break;
                case 11: $randNum = 'B'; break;
                case 12: $randNum = 'C'; break;
                case 13: $randNum = 'D'; break;
                case 14: $randNum = 'E'; break;
                case 15: $randNum = 'F'; break;
            }
            $str .= $randNum;
        }

        return $str;
    }

    /**
     * 判断是否为空
     *
     * @param string $key
     * @param string $post
     * @return bool
     */
    private function isNotEmpty($key, $post)
    {
        if (isset($post[$key])) {
            $str = trim($post[$key]);
            if (strlen($str) > 0) {
                return true;
            }
        }

        return false;
    }
}