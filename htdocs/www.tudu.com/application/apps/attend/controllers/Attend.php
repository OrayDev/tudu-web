<?php
/**
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Attend.php 2766 2013-03-05 10:16:20Z chenyongfa $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Attend extends Apps_Attend_Abstract
{
    /**
     * 当前日期（时间戳）
     *
     * @var int
     */
    protected $_currentDate;

    /**
     * 当期月份(例如：201204)
     *
     * @var int
     */
    protected $_currentMonth;

    /**
     *
     * @var array
     */
    protected $_checkins;

    /**
     * (non-PHPdoc)
     * @see TuduX_App_Abstract::init()
     */
    public function init()
    {
        /* Initialize action controller here */
        parent::init();
        $this->checkApp();

        $this->_currentDate  = strtotime(date('Y-m-d'));
        $this->_currentMonth = date('Ym');
    }

    /**
     * 考勤登记首页
     */
    public function indexAction()
    {
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);
        $condition  = array('uniqueid' => $this->_user->uniqueId, 'date' => $this->_currentDate);
        $records    = $daoCheckin->getCheckins($condition, null, 'type ASC, createtime ASC');

        $checkinTime = null;

        // 考勤签到信息
        $checkins = $records->toArray();
        foreach ($checkins as $checkin) {
            if ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                $checkinTime = $checkin['createtime'];
                $this->view->checkin = $checkin;
            } elseif ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                $this->view->checkout = $checkin;
            }
        }

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate  = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        $workTime = 0;
        // 工作时长
        $dateRecord = $daoDate->getAttendDate($condition);
        if ($dateRecord !== null && $dateRecord->workTime > 0) {
            $workTime = $this->formatTime($dateRecord->workTime);
        } else {
            if (null !== $checkinTime) {
                $workTime = time() - $checkinTime;
            }
            if ($workTime != 0) {
                $workTime = $this->formatTime($workTime);
            }
        }
        $this->view->worktime = $workTime;

        // 月考勤分类统计
        $count = array('late' => 0, 'leave' => 0, 'unwork' => 0);
        $query = array('uniqueid' => $this->_user->uniqueId, 'date' => $this->_currentMonth);

        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        $monthRecord = $daoMonth->getAttendMonth($query);

        /* @var $daoTotal Dao_App_Attend_Total */
        $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);
        $totals = $daoTotal->getAttendTotals($query);

        if (null !== $monthRecord) {
            $count['late']   = $monthRecord->late;
            $count['leave']  = $monthRecord->leave;
            $count['unwork'] = $monthRecord->unwork;
        }

        if (null !== $totals) {
            foreach ($totals as $total) {
                if ((int) $total->total > 0) {
                    $count[$total->categoryId] = array('name' => $total->categoryName, 'total' => $total->total);
                }
            }
        }

        // 读取当前天排班信息
        $plan = $this->getPlan();

        $this->view->plan  = $plan;
        $this->view->count = $count;
        $this->view->date  = array('year' => date('Y'), 'month' => date('m'));
        $this->view->unid  = $this->_user->uniqueId;
    }

    /**
     * 各种考勤申请明细
     */
    public function applyinfoAction()
    {
        $categoryId = $this->_request->getQuery('cid');
        $uniqueId   = $this->_request->getQuery('unid');
        $year       = $this->_request->getQuery('year');
        $month      = $this->_request->getQuery('month');
        $general    = array('late', 'leave', 'unwork');
        $condition  = array();
        $startDate  = mktime(0, 0, 0, $month, 1, $year);
        $endDate    = mktime(0, 0, 0, $month + 1, 1, $year);

        $condition['uniqueid'] = $uniqueId;
        $condition['orgid']    = $this->_user->orgId;

        if (!in_array($categoryId, $general)) {
            /* @var $daoApply Dao_App_Attend_Apply */
            $daoApply   = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);
            /* @var $daoCategory Dao_App_Attend_Category */
            $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

            $category = $daoCategory->getCategory(array(
                'categoryid' => $categoryId,
                'orgid'      => $this->_user->orgId
            ));

            $condition['categoryid'] = $categoryId;
            $condition['starttime']  = $startDate;
            $condition['endtime']    = $endDate;

            $applys = $daoApply->getMonthApplies($condition);
            $title  = $category->categoryName . '申请明细';

            $this->view->applys = $applys;
        } else {
            /* @var $daoCheckin Dao_App_Attend_Checkin */
            $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

            $condition['date'] = array('start' => $startDate, 'end' => $endDate);

            if ($categoryId == 'late') {
                $condition['islate'] = 1;
                $title = '考勤迟到记录';
            } elseif ($categoryId == 'leave') {
                $condition['isleave'] = 1;
                $title = '考勤早退记录';
            } else {
                $condition['iswork'] = 1;
                $title = '考勤旷工记录';
            }

            $records  = $daoCheckin->getViolationRecords($condition);
            $checkins = array();
            foreach ($records as $date => $item) {
                $checkin = array();
                foreach ($item as $val) {
                    if ($val['type'] == 0) {
                        $checkin['checkintime']    = $val['createtime'];
                        $checkin['checkinip']      = $val['ip'];
                        $checkin['checkinaddress'] = !empty($val['address']) ? $val['address'] : $this->lang['unknow'];
                    } elseif ($val['type'] == 1) {
                        $checkin['checkouttime']    = $val['createtime'];
                        $checkin['checkoutip']      = $val['ip'];
                        $checkin['checkoutaddress'] = !empty($val['address']) ? $val['address'] : $this->lang['unknow'];
                    }
                }
                $checkins[$date] = $checkin;
            }

            $this->view->checkins = $checkins;
        }

        $this->view->title      = $title;
        $this->view->categoryid = $categoryId;
        $this->view->general    = $general;
    }

    /**
     * 读取考勤明细
     *
     * json 返回整个表格(html标记语言)
     */
    public function infoAction()
    {
        $date = $this->_request->getQuery('date');
        $unId = $this->_request->getQuery('unid');
        $arr = array();

        if (empty($unId)) {
            $unId = $this->_user->uniqueId;
        }

        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate    = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);

        $isToday    = $date == strtotime(date('Y-m-d')) ? true : false;
        $condition  = array('uniqueid' => $unId, 'date' => $date);
        $dateRecord = $daoDate->getAttendDate($condition);
        $records    = $daoCheckin->getCheckins($condition, null, 'type ASC, createtime ASC');
        $checkins   = $records->toArray();
        $date       = date('Y-m-d', $date);
        $arr['date']= $date;
        $isCheckout = false;
        $isCheckin  = false;

        foreach ($checkins as $checkin) {
            $time = date('H:i', $checkin['createtime']);
            $address = !empty($checkin['address']) ? $checkin['address'] : $this->lang['unknow'];

            if ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                $arr['checkin'] = array('time' => $time, 'ip' => $checkin['ip'], 'address' => $address);
                $isCheckin      = true;
            } elseif ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                $arr['checkout'] = array('time' => $time, 'ip' => $checkin['ip'], 'address' => $address);
                $isCheckout      = true;
            }
        }

        if (null !== $dateRecord) {
            // 格式化工作时长
            $workTime = 0;
            if ($dateRecord->workTime != 0) {
                $workTime = $this->formatTime($dateRecord->workTime);
            }
            $workTime = is_string($workTime) ? $workTime : '0:0';
            $workTimeString = str_replace(':', $this->lang['hour'], $workTime) . $this->lang['min'];
            $arr['worktime'] = $workTimeString;

            // 考勤状况
            if (!$isToday || ($isCheckout && $isCheckin)) {
                $status = array();
                if ($dateRecord->isLate) {
                    $status[] = $this->lang['category_late'];
                }
                if ($dateRecord->isLeave) {
                    $status[] = $this->lang['category_leave'];
                }
                if ($dateRecord->isWork) {
                    $status[] = $this->lang['category_unwork'];
                }
                if (!empty($status)) {
                    $statusHtml = implode(',', $status);
                    $arr['status'] = $statusHtml;
                }
            }
        }

        $remarks = array();
        // 备注 签到、签退状态
        if (null !== $dateRecord && null !== $dateRecord->checkinStatus) {
            $csHtml = array();
            foreach ($dateRecord->checkinStatus as $item) {
                if ($item == 0) {
                    $csHtml[] = $this->lang['un_checkin'];
                } else {
                    $csHtml[] = $this->lang['un_checkout'];
                }
            }
            $remarks[] = '<p>' . implode($this->lang['comma'], $csHtml) . '</p>';
        }
        // 读取考勤申请信息
        if (null !== $dateRecord && null !== $dateRecord->memo) {
            $remarkHtml = array();
            foreach ($dateRecord->memo as $memo) {
                $statusHtml = $this->lang['apply_status_2'];
                if ($memo['ischeckin']) {
                    if ($memo['type'] == 0) {
                        $checkinType = "签到";
                    } elseif ($memo['type'] == 1) {
                        $checkinType = "签退";
                    }
                    $remarkHtml[] = "<p>{$statusHtml}{$this->lang['cln']}{$memo['categoryname']}({$checkinType})</p><p>{$this->lang['time']}{$this->lang['cln']}{$memo['checkintime']}</p>";
                } else {
                    $remarkHtml[] = "<p>{$statusHtml}{$this->lang['cln']}{$memo['categoryname']}</p><p>{$this->lang['time']}{$this->lang['cln']}{$memo['start']} {$this->lang['zhi']} {$memo['end']} {$this->lang['total']} {$memo['period']}{$this->lang['hour']}</p>";
                }
            }
            $remarks[] = implode('', $remarkHtml);
        }

        if (!empty($remarks)) {
            $arr['memo'] = implode('', $remarks);
        }

        return $this->_this->json(true, null, $arr);
    }

    /**
     * 签到
     */
    public function checkinAction()
    {
        // 签到类型
        $type = (int) $this->_request->getPost('type');
        if (!isset($type)) {
            return $this->_this->json(false, $this->lang['error_type_of_checkin']);
        }

        // 签到时间
        $checkinTime = time();

        /* @var $daoIp Dao_Md_Ip_Info */
        $daoIp    = Tudu_Dao_Manager::getDao('Dao_Md_Ip_Info', Tudu_Dao_Manager::DB_MD);
        // 签到来源IP、ip位置
        $clientIp = $this->_request->getClientIp();
        $ipInfo   = $daoIp->getInfoByIp($clientIp);

        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        // 默认当次签到类型为正常
        $status = Dao_App_Attend_Checkin::STATUS_NORMAL;

        // 读取当前天排班信息
        $plan = $this->getPlan();
        if (!empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
            // 上班签到
            if ($type == Dao_App_Attend_Checkin::TYPE_CHECKIN && $plan['checkintime']) {
                $setCheckinTime = $this->_currentDate + $this->formatTimeToSec($plan['checkintime']);
                if (!empty($plan['latestandard'])) {
                    $setCheckinTime += $plan['latestandard'] * 60;
                }
                if ($checkinTime > $setCheckinTime) {
                    $status = Dao_App_Attend_Checkin::STATUS_LATE;
                    if (!empty($plan['latecheckin'])) {
                        $outworkTime    = $plan['latecheckin'] * 60;
                        $setCheckinTime = $this->_currentDate + $this->formatTimeToSec($plan['checkintime']);
                        if ($checkinTime - $setCheckinTime > $outworkTime) {
                            $status = Dao_App_Attend_Checkin::STATUS_WORK;
                        }
                    }
                }
            // 下班签退
            } elseif ($type == Dao_App_Attend_Checkin::TYPE_CHECKOUT && $plan['checkouttime']) {
                $setCheckoutTime = $this->_currentDate + $this->formatTimeToSec($plan['checkouttime']);
                $calType = 0;
                $cinTime = 0;
                if ($plan['checkintime']) {
                    // 读取签到记录
                    $checkin = $daoCheckin->getCheckin(array(
                        'orgid'      => $this->_user->orgId,
                        'uniqueid'   => $this->_user->uniqueId,
                        'date'       => $this->_currentDate,
                        'type'       => 0
                    ));

                    $calType = null === $checkin ? 0 : 1;

                    if (null !== $checkin) {
                        $cinTime     = $this->formatTimeToSec(date('H:i', $checkin->createTime));
                        $planCinTime = $this->formatTimeToSec($plan['checkintime']);
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
                            if (!empty($plan['leavecheckout'])) {
                                $outworkTime     = $plan['leavecheckout'] * 60;
                                $setCheckoutTime = $this->_currentDate + $this->formatTimeToSec($plan['checkouttime']);
                                if ($setCheckoutTime - $checkinTime > $outworkTime) {
                                    $status = Dao_App_Attend_Checkin::STATUS_WORK;
                                }
                            }
                        }

                        break;
                    case 1:
                        $planWorkTime = $this->formatTimeToSec($plan['checkouttime']) - $this->formatTimeToSec($plan['checkintime']);
                        $userWorkTime = $this->formatTimeToSec(date('H:i', $checkinTime)) - $cinTime;

                        if ($userWorkTime < $planWorkTime) {
                            $status = Dao_App_Attend_Checkin::STATUS_LEAVE;
                            if (!empty($plan['leavecheckout']) && $this->calculateTime($userWorkTime, $planWorkTime) > $plan['leavecheckout'] * 60) {
                                $status = Dao_App_Attend_Checkin::STATUS_WORK;
                            }

                        }

                        break;
                }
            }
        }

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);

        // 考勤当天是否有考勤申请
        $isApply = $daoDate->isApply($this->_user->uniqueId, $this->_currentDate);
        if ($isApply) { // 有考勤申请考勤状况为正常
            $status = Dao_App_Attend_Checkin::STATUS_NORMAL;
        }

        $checkin = $daoCheckin->getCheckin(array(
            'orgid'      => $this->_user->orgId,
            'uniqueid'   => $this->_user->uniqueId,
            'date'       => $this->_currentDate,
            'type'       => $type
        ));
        if (null === $checkin) {
            // 创建签到记录
            $checkinId = $daoCheckin->createCheckin(array(
                'checkinid'  => Dao_App_Attend_Checkin::getCheckinId(),
                'orgid'      => $this->_user->orgId,
                'uniqueid'   => $this->_user->uniqueId,
                'date'       => $this->_currentDate,
                'status'     => $status,
                'type'       => $type,
                'ip'         => sprintf('%u', ip2long($clientIp)),
                'address'    => null !== $ipInfo ? $ipInfo->city : null,
                'createtime' => $checkinTime
            ));
        } else {
            if ($type == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                if ($checkin->createTime > $checkinTime) {
                    $checkinId = $daoCheckin->updateCheckin($checkin->checkinId, array(
                        'status'     => $status,
                        'ip'         => sprintf('%u', ip2long($clientIp)),
                        'address'    => null !== $ipInfo ? $ipInfo->city : null,
                        'createtime' => $checkinTime
                    ));
                } else {
                    $checkinId = $checkin->checkinId;
                }
            } elseif ($type == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                if ($checkin->createTime < $checkinTime) {
                    $checkinId = $daoCheckin->updateCheckin($checkin->checkinId, array(
                        'status'     => $status,
                        'ip'         => sprintf('%u', ip2long($clientIp)),
                        'address'    => null !== $ipInfo ? $ipInfo->city : null,
                        'createtime' => $checkinTime
                    ));
                } else {
                    $checkinId = $checkin->checkinId;
                }
            }
        }

        // 创建当天考勤统计，默认旷工
        if ($checkinId) {
            // 获取签到、签退状态
            $checkinStatus = 0;

            if (!$isApply && !empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
                $checkinStatus = $this->getCheckinStatus($plan);
            }

            $attendDate = $daoDate->getAttendDate(array('uniqueid' => $this->_user->uniqueId, 'date' => $this->_currentDate));
            if (!$attendDate) {
                $iswork = 1;
                if ($isApply
                    || ($plan !== null && !$plan['checkintime'] && !$plan['checkouttime'])
                    || empty($plan)
                    || (!empty($plan) && ($plan['scheduleid'] == '^off' || $plan['scheduleid'] == '^exemption'))
                    || ($type == 0 && $plan !== null && !$plan['checkouttime'] && $status != Dao_App_Attend_Checkin::STATUS_WORK))
                {
                    $iswork = 0;
                }

                $daoDate->create(array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $this->_user->uniqueId,
                    'date'       => $this->_currentDate,
                    'iswork'     => $iswork,
                    'checkinstatus' => $checkinStatus,
                    'updatetime' => time()
                ));
            } else {
                if ($attendDate->checkinStatus != $checkinStatus) {
                    $update = array('checkinstatus' => $checkinStatus);
                    if ($checkinStatus != 0) {
                        $update['iswork'] = 1;
                    }

                    $daoDate->update($this->_user->uniqueId, $this->_currentDate, $update);
                }
            }

            // 判断月统计表是否已有当月的统计记录
            /* @var $daoMonth Dao_App_Attend_Month */
            $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
            $exists = $daoMonth->existsRecord($this->_user->uniqueId, $this->_currentMonth);
            if (!$exists) {
                $daoMonth->create(array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $this->_user->uniqueId,
                    'date'       => $this->_currentMonth,
                    'updatetime' => time()
                ));
            }
        } else {
            return $this->_this->json(false, $this->lang['checkin_failed']);
        }

        // 下班签退，进行当天考勤统计（工作时长等）、当月考勤统计
        if ($type == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
            $this->attendCount($plan, $isApply);
        }

        $message = $type == Dao_App_Attend_Checkin::TYPE_CHECKOUT ? '签退成功' : '签到成功';

        return $this->_this->json(true, $message, array('status' => $status, 'ip' => $clientIp, 'address' => (!empty($ipInfo->city)) ? $ipInfo->city : $this->lang['unknow'], 'time' => date('H:i', $checkinTime)));
    }

    /**
     * 获取签到、签退状态
     */
    public function getCheckinStatus($plan)
    {
        $checkinStatus = 0;

        // 读取当天所有的签到记录
        $checkins = $this->getCheckins();
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
     * 进行考勤统计
     */
    public function attendCount($plan, $isApply = false)
    {
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate    = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);
        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth   = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);

        $workTime    = 0;       // 工作时长
        $isLate      = false;   // 是否迟到
        $isLeave     = false;   // 是否早退
        $isWork      = false;   // 是否旷工
        $dateParams  = array(); // 用于存储签到日的统计结果
        $monthParams = array(); // 用于存储签到月份的统计结
        $checkinTime = null;
        $checkoutTime= null;

        // 读取当天所有的签到记录
        $checkins = $this->getCheckins();
        foreach ($checkins as $checkin) {
            if (!$isApply && (!empty($plan) && ($plan['scheduleid'] != '^off' || $plan['scheduleid'] != '^exemption'))) {
                if ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_LATE) {
                    $isLate = true;
                } elseif ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_LEAVE) {
                    $isLeave = true;
                } elseif ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_WORK) {
                    $isWork = true;
                }
            }

            if ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                $checkinTime = $checkin['createtime'];
            } elseif ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                $checkoutTime = $checkin['createtime'];
            }
        }

        if (null !== $checkinTime && null !== $checkoutTime) {
            $workTime = $checkoutTime - $checkinTime;
        }

        if ($plan !== null && !$plan['checkintime'] && !$plan['checkouttime']) {
            $isWork = false;
        }
        if (($plan !== null && $plan['checkintime'] && !$checkinTime)
            || ($plan !== null && $plan['checkouttime'] && !$checkoutTime))
        {
            $isWork = true;
        }

        $dateParams['iswork']  = $isWork ? true : false;
        $dateParams['islate']  = $isLate? true : false;
        $dateParams['isleave'] = $isLeave ? true : false;

        // 工作时长
        if ($workTime != 0) {
            $dateParams['worktime'] = $workTime;
        }

        if (!empty($dateParams)) {
            $dateParams['updatetime'] = time();
            $daoDate->update($this->_user->uniqueId, $this->_currentDate, $dateParams);
        }

        // 判断月统计表是否已有当月的统计记录
        $sum = $daoDate->dateSum(array(
            'uniqueid'  => $this->_user->uniqueId,
            'startdate' => mktime(0, 0, 0, date('m'), 1, date('Y')),
            'enddate'   => mktime(0, 0, 0, date('m') + 1, 1, date('Y'))
        ));

        if (!empty($sum)) {
            $monthParams['updatetime'] = time();
            $monthParams['late']   = (int) $sum['late'];
            $monthParams['leave']  = (int) $sum['leave'];
            $monthParams['unwork'] = (int) $sum['unwork'];
            $daoMonth->update($this->_user->uniqueId, $this->_currentMonth, $monthParams);
        }
    }

    /**
     * 签到记录
     */
    public function getCheckins($sort = null)
    {
        $ret = array();
        if (null === $this->_checkins) {
            /* @var $daoCheckin Dao_App_Attend_Checkin */
            $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

            $condition = array(
                'uniqueid' => $this->_user->uniqueId,
                'date'     => $this->_currentDate
            );

            $records = $daoCheckin->getCheckins($condition, null, 'createtime ASC, type ASC');

            $this->_checkins = $records;
        }

        if (!empty($sort)) {
            $ret = $this->_checkins->toArray($sort);
        } else {
            $ret = $this->_checkins->toArray();
        }

        return $ret;
    }

    /**
     *
     */
    public function widgetAction()
    {
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);
        $condition = array('uniqueid' => $this->_user->uniqueId, 'date' => $this->_currentDate);
        $records = $daoCheckin->getCheckins($condition, null, 'type ASC, createtime ASC');

        $checkinTime = null;

        // 考勤签到信息
        $checkins = $records->toArray();
        foreach ($checkins as $checkin) {
            if ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                $checkinTime = $checkin['createtime'];
                $this->view->checkin = $checkin;
            } elseif ($checkin['type'] == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                $this->view->checkout = $checkin;
            }
        }

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate  = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        $workTime = 0;
        // 工作时长
        $dateRecord = $daoDate->getAttendDate($condition);
        if ($dateRecord !== null && $dateRecord->workTime > 0) {
            $workTime = $this->formatTime($dateRecord->workTime);
        } else {
            if (null !== $checkinTime) {
                $workTime = time() - $checkinTime;
            }
            if ($workTime != 0) {
                $workTime = $this->formatTime($workTime);
            }
        }
        $this->view->worktime = $workTime;

        // 月考勤分类统计
        $count = array('late' => 0, 'leave' => 0, 'unwork' => 0);
        $query = array('uniqueid' => $this->_user->uniqueId, 'date' => date('Ym'));

        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        $monthRecord = $daoMonth->getAttendMonth($query);

        if (null !== $monthRecord) {
            $count['late']   = $monthRecord->late;
            $count['leave']  = $monthRecord->leave;
            $count['unwork'] = $monthRecord->unwork;
        }

        $this->view->count = $count;
    }

    /**
     * 获取服务器时间，json返回
     */
    public function gettimeAction()
    {
        return $this->_this->json(true, null, array('currtime' => time()));
    }

    /**
     * 获取签到提醒，json返回
     */
    public function getcheckintipsAction()
    {
        $tips = array(
            'checkin'  => array('isshow' => false, 'starttime' => '^off', 'outtime' => '^off'),
            'checkout' => array('isshow' => false, 'starttime' => '^off', 'outtime' => '^off')
        );

        // 是否需要签退提醒
        $checkoutRemind = empty($this->_settings) ? true : false;
        if (!empty($this->_settings) && $this->_settings['checkoutremind']) {
            $checkoutRemind = true;
        }

        $plan = $this->getPlan();
        if (!empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
            $checkins = $this->getCheckins('type');
            $date     = date('Y-m-d', time());

            if ($plan['checkintime'] && !isset($checkins[0])) {
                $checkinTime = strtotime($date . ' ' . $plan['checkintime']);
                $min = Oray_Function::dateDiff('min', time(), $checkinTime);

                $tips['checkin']['starttime'] = $checkinTime - 3600;
                $tips['checkin']['outtime']   = $checkinTime + 3600;

                // 下班前后1小时才有提醒
                if ($min <= 60 && $min >= -60) {
                    $tips['checkin']['isshow']    = true;
                    $tips['checkin']['starttime'] = '^off';
                }
            }

            if ($checkoutRemind && $plan['checkouttime'] && !isset($checkins[1])) {
                $checkoutTime = strtotime($date . ' ' . $plan['checkouttime']);
                // 灵活提示
                if (isset($checkins[0]) && $plan['checkintime']) {
                    $checkin = $checkins[0];
                    if ($checkin['status'] == Dao_App_Attend_Checkin::STATUS_NORMAL) {
                        $planCheckinTime = strtotime($date . ' ' . $plan['checkintime']);
                        if ($checkin['createtime'] > $planCheckinTime) {
                            $planCheckoutTime = strtotime($date . ' ' . $plan['checkouttime']);
                            $planWorkTime = $planCheckoutTime - $planCheckinTime;
                            $checkoutTime = $checkin['createtime'] + $planWorkTime;
                        }
                    }
                }
                $min = Oray_Function::dateDiff('min', time(), $checkoutTime);

                $tips['checkout']['starttime'] = $checkoutTime;
                $tips['checkout']['outtime']   = $checkoutTime + 1800;

                // 下班前后半小时才有提醒
                if ($min <= 0 && $min >= -30) {
                    $tips['checkout']['isshow']    = true;
                    $tips['checkout']['starttime'] = '^off';
                }
            }
        }

        return $this->_this->json(true, null, array('tips' => $tips, 'currtime' => time()));
    }

    /**
     * 获取用户当天排班计划
     * 没有设置则返回默认班信息
     */
    public function getPlan($uniqueId = null, $date = null)
    {
        if (empty($uniqueId)) {
            $uniqueId = $this->_user->uniqueId;
        }
        if (empty($date)) {
            $date = $this->_currentDate;
        }

        /* @var $daoAdjust Dao_App_Attend_Schedule_Adjust */
        $daoAdjust = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Adjust', Tudu_Dao_Manager::DB_APP);
        // 若调整为非工作日，返回null
        $adjust = $daoAdjust->getUserAdjust(array('uniqueid' => $uniqueId, 'datetime' => $date));
        if (null !== $adjust && $adjust->type == 0) {
            return null;
        }

        /* @var $daoSchedule Dao_App_Attend_Schedule */
        $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);
        /* @var $daoPlan Dao_App_Attend_Schedule_Plan */
        $daoPlan = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule_Plan', Tudu_Dao_Manager::DB_APP);

        // 读取当前月排班计划
        $condition = array('date' => $this->_currentMonth, 'uniqueid' => $uniqueId);
        $plan      = $daoPlan->getMonthPlan($condition);
        $day       = date('j', $this->_currentDate);

        if ($plan !== null) {
            $plan = $plan->toArray();
            $plan = $plan['plan'];
            if (!empty($plan) && isset($plan[$day])) {
                $scheduleId = $plan[$day];
            }
        } else {
            $weekPlan = $daoPlan->getWeekPlan(array('uniqueid' => $uniqueId));
            if ($weekPlan !== null) {
                $weekPlan = $weekPlan->toArray();
                $scheduleId = $this->getPlanByWeekPlan($weekPlan, $this->_currentDate);
            }
        }

        $schedule = null;
        if (!empty($scheduleId)) {
            if ($scheduleId != '^off') {
                if ($scheduleId == '^default') {
                    $schedule = $daoSchedule->getSchedule(array(
                        'orgid'      => $this->_user->orgId,
                        'scheduleid' => '^default',
                        'week'       => date('w', $this->_currentDate),
                        'status'     => 1
                    ));
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                } else {
                    $schedule = $daoSchedule->getSchedule(array('orgid' => $this->_user->orgId, 'scheduleid' => $scheduleId), array('isvalid' => true));
                    if (null === $schedule) {
                        $schedule = $daoSchedule->getSchedule(array(
                            'orgid'      => $this->_user->orgId,
                            'scheduleid' => '^default',
                            'week'       => date('w', $this->_currentDate),
                            'status'     => 1
                        ));
                    }
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                }
            } else {
                // 非工作日的调整成工作日的，默认班填充
                if (!empty($adjust) && $adjust['type'] == 1) {
                    $schedule = $daoSchedule->getSchedule(array(
                        'orgid'      => $this->_user->orgId,
                        'scheduleid' => '^default',
                        'week'       => date('w', $this->_currentDate)
                    ));
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                }
            }
        } else {
            $schedule = $daoSchedule->getSchedule(array(
                'orgid'      => $this->_user->orgId,
                'scheduleid' => '^default',
                'week'       => date('w', $this->_currentDate)
            ));
            $schedule = (null !== $schedule) ? $schedule->toArray() : null;
            if (empty($adjust) && !empty($schedule) && !$schedule['status']) {
                $schedule = null;
            }
        }

        return $schedule;
    }

    /**
     * 通过周排班获取当日排班
     *
     * @param array $plans
     * @param int   $year
     * @param int   $month
     */
    public function getPlanByWeekPlan($plan, $date)
    {
        if (empty($plan)) {
            return null;
        }

        $currPlan  = null;
        $w         = date('w', $plan['effectdate']) == 0 ? 7 : date('w', $plan['effectdate']);
        $sd        = date('j', $plan['effectdate']) - ($w - 1);
        $start     = strtotime(date('Y', $plan['effectdate']) . '-' . date('m', $plan['effectdate']) . '-' . $sd);
        $i         = 1;

        if ($date >= $plan['effectdate']) {
            $wd       = date('w', $date);
            $diff     = $this->dateWeekDiff($date, $start);
            $value    = $wd + (($diff % $plan['cyclenum']) * 7);
            $currPlan = $plan['plan'][$value];
        }

        return $currPlan;
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
     * 格式化工作时长
     *
     * @param int $time
     * @return int|string
     */
    public function formatTime($time)
    {
        if (null == $time || $time == 0) {
            return 0;
        }

        //除去整天之后剩余的时间
        $time = $time%(3600*24);
        // 小时
        $hour = floor($time/3600);
        //除去整小时之后剩余的时间
        $time = $time%3600;
        // 分钟
        $minute = floor($time/60);
        // 秒
        //$second = $time%60;

        //返回字符串
        return $hour . ':' . $minute;
    }

    /**
     * 格式化时间（返回秒）
     *
     * @param string $str
     * @return int
     */
    public function formatTimeToSec($str)
    {
        if (!$str) {
            return 0;
        }

        $arr = explode(':', $str);
        $sec = (int) $arr[0] * 3600;
        $sec = $sec + (int) $arr[1] * 60;

        return $sec;
    }
}