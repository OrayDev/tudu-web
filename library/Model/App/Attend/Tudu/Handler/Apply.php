<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Exception.php 1894 2012-05-31 08:02:57Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Handler_Abstract
 */
require_once 'Model/Tudu/Extension/Handler/Abstract.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_App_Attend_Tudu_Handler_Apply extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @var string
     */
    protected $_fromApply = null;

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        $apply = $tudu->getExtension('Model_App_Attend_Tudu_Apply');

        if (!$apply) {
            return ;
        }

        /* @var $daoApply Dao_App_Attend_Apply */
        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

        if ($tudu->tuduId) {
            $this->_fromApply = $daoApply->getApply(array('tuduid' => $tudu->tuduId));

            if ($this->_fromApply) {
                $apply->applyId = $this->_fromApply->applyId;

                if ($tudu->operation == 'review') {
                    $apply->setAttributes($this->_fromApply->toArray());
                }
            }
        }

        // 创建审批流程
        if ($tudu->operation == 'send' || $tudu->operation == 'save') {

            $error  = null;
            $issend = $tudu->operation == 'send';
            do {
                if (!$apply->categoryId) {
                    $error = 'Invalid Category id for attend apply';
                    break;
                }

                if ($issend && (!$apply->startTime && $apply->categoryId != '^checkin')) {
                    $error = 'Invalid Starttime for attend apply';
                    break;
                }

                if ($issend && (!$apply->endTime && $apply->categoryId != '^checkin')) {
                    $error = 'Invalid Endtime for attend apply';
                    break ;
                }

            } while (false);

            // 参数错误
            if (null != $error) {
                require_once 'Model/App/Attend/Exception.php';
                $code = false !== strpos($error, 'Category')
                ? Model_App_Attend_Exception::APPLY_MISSING_CATEGORYID
                : Model_App_Attend_Exception::APPLY_INVALID_TIME;

                throw new Model_App_Attend_Exception($error, $code);
            }

            /* @var $daoCategory Dao_App_Attend_Category */
            $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

            $category = $daoCategory->getCategory(array('categoryid' => $apply->categoryId, 'orgid' => $apply->orgId));

            if (null === $category) {
                require_once 'Model/App/Attend/Exception.php';
                throw new Model_App_Attend_Exception('Category id: [' . $apply->categoryId . '] is not exists', Model_App_Attend_Exception::CATEGORY_NOT_EXISTS);
            }

            $tudu->subject = $category->categoryName;

            // 创建图度工作流
            if ($tudu->operation == 'send' && ($apply->status == 0 || $apply->status == 3) && !$this->_fromApply) {
                require_once 'Model/Tudu/Extension/Flow.php';
                $flow = new Model_Tudu_Extension_Flow(array(
                    'orgid' => $tudu->orgId,
                ));

                $steps = $category->flowSteps;

                $stepIds = array();
                foreach ($steps as $item) {
                    $stepId = isset($item['stepid']) ? $item['stepid'] : Dao_Td_Tudu_Flow::getStepId(null);

                    $stepIds[] = $stepId;

                    $flow->addStep(array(
                        'stepid'      => $stepId,
                        'prev'        => !empty($item['prev']) ? $item['prev'] : '^head',
                        'next'        => !empty($item['next']) ? $item['next'] : '^end',
                        'type'        => 1,
                        'description' => isset($item['description']) ? $item['description'] : null,
                        'subject'     => isset($item['subject']) ? $item['subject'] : null
                    ));

                    if (is_array($item['sections'])) {
                        foreach ($item['sections'] as $section) {
                            $flow->addStepSection($stepId, $section);
                        }
                    } else {
                        $flow->addStepSection($stepId, $item['sections'], $tudu);
                    }
                }

                $flow->flowTo($stepIds[0]);
                $users = $flow->getCurrentUser();

                $flow->isPrepared = true;

                $tudu->reviewer = $users;
                $tudu->setExtension($flow);
            }

            // 新建的
            if (!$this->_fromApply) {
                if ($apply->target) {
                    list($userName, $trueName) = explode(' ', $apply->target);

                    $addressBook = Tudu_AddressBook::getInstance();

                    $user = $addressBook->searchUser($userName);
                    if ($user) {
                        $apply->uniqueId = $user['uniqueid'];
                        $apply->userInfo = $userName . ' ' . $trueName;

                        $tudu->bcc= array(array(
                        'uniqueid' => $user['uniqueid'],
                        'username' => $user['username'],
                        'truename' => $user['truename']
                        ));
                    }
                } else {
                    $apply->uniqueId = $apply->senderId;
                    $apply->userInfo = $tudu->poster . ' ' . $tudu->email;
                }
            }
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function action(Model_Tudu_Tudu &$tudu)
    {
        $opt = $tudu->operation;

        if ($opt == 'save') {
            $this->_save($tudu);
        } elseif ($opt == 'review') {
            $this->_review($tudu);
        } else {
            $this->_send($tudu);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    protected function _review(Model_Tudu_Tudu &$tudu)
    {
        $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');
        /* @var $apply Model_App_Attend_Tudu_Apply */
        $apply = $tudu->getExtension('Model_App_Attend_Tudu_Apply');

        // 结束流程，需要更新相关考勤记录
        if ($flow->currentStepId == '^end') {

            // 补签申请的另外处理
            if ($apply->categoryId == '^checkin') {
                return $this->updateCheckinApply($tudu, $apply);
            }

            $data = $apply->getAttributes();

            /* @var $daoApply Dao_App_Attend_Apply */
            $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

            $daoApply->updateApply($data['applyid'], array('status' => 2));
            $daoApply->updateReviewer($data['applyid'], $tudu->uniqueId, array('status' => 1));

            $startTime = $data['starttime'];
            $endTime   = $data['endtime'];

            // 更新考勤信息备注
            /* @var $daoDate Dao_App_Attend_Date */
            $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
            /* @var $daoMonth Dao_App_Attend_Month */
            $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
            /* @var $daoTotal Dao_App_Attend_Total */
            $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);

            $start  = strtotime(date('Y-m-d', $startTime));
            $end    = strtotime(date('Y-m-d', $endTime));
            $days   = floor(($end - $start) / 86400);
            $period = round($data['period'], 1);
            $memo   = array($data['categoryname'], $data['starttime'], $data['endtime'], $period);

            for($i=0; $i < $days + 1; $i++) {
                $date       = $start + $i * 86400;
                $attendDate = $daoDate->getAttendDate(array('uniqueid' => $data['uniqueid'], 'date' => $date));

                if (!$attendDate) {
                    $plan  = $this->getPlan($data['orgid'], $data['uniqueid'], $date);
                    $checkinStatus = 0;

                    if (!empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
                        $checkinStatus = $this->getCheckinStatus($plan, $data['uniqueid'], $date);
                    }

                    $sum = array($checkinStatus);
                    $sum[] = 4;
                    $checkinStatus = array_sum($sum);

                    $params = array(
                        'orgid'      => $data['orgid'],
                        'uniqueid'   => $data['uniqueid'],
                        'date'       => $date,
                        'checkinstatus' => $checkinStatus,
                        'updatetime' => time()
                    );

                    $rs = Dao_App_Attend_Date::formatCheckinStatus($checkinStatus);
                    if (!empty($rs)) {
                        foreach ($rs as $item) {
                            if (0 == $item || 1 == $item) {
                                $params['iswork'] = 1;
                                break;
                            }
                        }
                    }

                    $daoDate->create($params);
                } else {
                    $isUpdate = true;
                    $sum      = array(4);
                    if (!empty($attendDate->checkinStatus)) {
                        foreach ($attendDate->checkinStatus as $item) {
                            if (0 == $item) {
                                $sum[] = 1;
                            } elseif (1 == $item) {
                                $sum[] = 2;
                            } elseif (2 == $item) {
                                $isUpdate = false;
                                break;
                            }
                        }
                    }

                    if ($isUpdate) {
                        $checkinStatus = array_sum($sum);
                        $daoDate->update($data['uniqueid'], $date, array('checkinstatus' => $checkinStatus));
                    }
                }

                $daoDate->addApply(array(
                'orgid'      => $data['orgid'],
                'uniqueid'   => $data['uniqueid'],
                'date'       => $date,
                'categoryid' => $data['categoryid'],
                'memo'       => implode('|', $memo)
                ));
            }

            $months = array();
            $months[date('Ym', $start)] = array('year' => date('Y', $start), 'month' => date('m', $start));
            $months[date('Ym', $end)]   = array('year' => date('Y', $end), 'month' => date('m', $end));

            foreach ($months as $month => $val) {
                // 统计考勤类型次数等
                if(!$daoTotal->existsRecord($data['categoryid'], $data['uniqueid'], $month)) {
                    $daoTotal->create(array(
                    'orgid'      => $data['orgid'],
                    'uniqueid'   => $data['uniqueid'],
                    'categoryid' => $data['categoryid'],
                    'date'       => $month,
                    'total'      => $period,
                    'updatetime' => time()
                    ));
                } else {
                    $daoTotal->updateTotal($data['categoryid'], $data['uniqueid'], $month, $period);
                }
            }

            /* @var $daoTudu Dao_Td_Tudu_Tudu */
            $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

            // 更新图度
            $ret = $daoTudu->updateTudu($tudu->tuduId, array('isdone' => 1));
            if (!$ret) {
                return false;
            }

            // 获取图度关联用户
            $users = $daoTudu->getUsers($tudu->tuduId);
            // 标签操作
            foreach ($users as $u) {
                $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^i');  //移除图度箱标签
                $daoTudu->addLabel($tudu->tuduId, $u['uniqueid'], '^o');     //添加已完成标签
                $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^a');  //移除我执行标签
                $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^e');  //移除我审批标签
            }

        // 需要添加下一步审批人员的关联
        } else {

            /* @var $daoApply Dao_App_Attend_Apply */
            $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

            $apply    = $daoApply->getApply(array('tuduid' => $tudu->tuduId));

            // 不同意
            if (!$tudu->agree) {
                $daoApply->updateApply($apply->applyId, array('status' => 3));
            } else {
                $daoApply->updateApply($apply->applyId, array('status' => 1));
            }

            if (null !== $apply) {
                $daoApply->updateReviewer($apply->applyId, $tudu->uniqueId, array('status' => $tudu->agree ? 1 : 2));

                if ($tudu->reviewer) {
                    $reviewer = $tudu->reviewer;
                    foreach ($reviewer as $rec) {
                        $daoApply->addReviewer($apply->applyId, $rec['uniqueid'], 0);
                    }
                }
            }
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    protected function _send(Model_Tudu_Tudu &$tudu)
    {
        /* @var $apply Model_App_Attend_Tudu_Apply */
        $apply = $tudu->getExtension('Model_App_Attend_Tudu_Apply');

        /* @var $daoApply Dao_App_Attend_Apply */
        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

        $params = $apply->getAttributes();

        $params['tuduid'] = $tudu->tuduId;

        if (null !== $this->_fromApply) {
            if ($apply->status > 2) {
                $params['status'] = 1;
            }

            $ret = $daoApply->updateApply($apply->applyId, $params);

            $applyId = $apply->applyId;
        } else {
            $applyId = Dao_App_Attend_Apply::getApplyId();
            $params['applyid'] = $applyId;

            $ret = $daoApply->createApply($params);
        }

        $flow  = $tudu->getExtension('Model_Tudu_Extension_Flow');
        $users = $flow->getCurrentUser();

        foreach ($users as $u) {
            $daoApply->addReviewer($apply->applyId, $u['uniqueid'], $u['status']);
        }

        if (!$ret) {
            require_once 'Model/App/Attend/Exception.php';
            throw new Model_App_Attend_Exception('Save apply failure', Model_App_Attend_Exception::APPLY_SAVE_FAILED);
        }

        $tudu->applyId = $applyId;
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    protected function _save(Model_Tudu_Tudu &$tudu)
    {
        /* @var $apply Model_App_Attend_Tudu_Apply */
        $apply = $tudu->getExtension('Model_App_Attend_Tudu_Apply');

        /* @var $daoApply Dao_App_Attend_Apply */
        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);

        $params = $apply->getAttributes();

        $params['tuduid'] = $tudu->tuduId;

        if (null !== $this->_fromApply) {
            if ($apply->status > 2) {
                $params['status'] = 1;
            }

            $ret = $daoApply->updateApply($apply->applyId, $params);

            $applyId = $apply->applyId;
        } else {
            $applyId = Dao_App_Attend_Apply::getApplyId();
            $params['applyid'] = $applyId;

            $ret = $daoApply->createApply($params);
        }

        if (!$ret) {
            require_once 'Model/App/Attend/Exception.php';
            throw new Model_App_Attend_Exception('Save apply failure', Model_App_Attend_Exception::APPLY_SAVE_FAILED);
        }

        $tudu->applyId = $applyId;
    }

    /**
     * 进行考勤统计
     */
    public function attendCount($plan, $uniqueId, $date, $isApply = false)
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
        $checkins = $this->getCheckins($uniqueId, $date);
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

        if ($isWork) {
            $isLate  = false;
            $isLeave = false;
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
            $daoDate->update($uniqueId, $date, $dateParams);
        }

        // 判断月统计表是否已有当月的统计记录
        $sum = $daoDate->dateSum(array(
        'uniqueid'  => $uniqueId,
        'startdate' => mktime(0, 0, 0, date('m'), 1, date('Y')),
        'enddate'   => mktime(0, 0, 0, date('m') + 1, 1, date('Y'))
        ));

        if (!empty($sum)) {
            $monthParams['updatetime'] = time();
            $monthParams['late']   = (int) $sum['late'];
            $monthParams['leave']  = (int) $sum['leave'];
            $monthParams['unwork'] = (int) $sum['unwork'];
            $daoMonth->update($uniqueId, date('Ym', $date), $monthParams);
        }
    }

    /**
     * 获取签到、签退状态
     */
    public function getCheckinStatus($plan, $uniqueId, $date)
    {
        $checkinStatus = 0;

        // 读取当天所有的签到记录
        $checkins = $this->getCheckins($uniqueId, $date);
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
    public function getCheckins($uniqueId, $date)
    {
        $ret = array();
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        $condition = array(
        'uniqueid' => $uniqueId,
        'date'     => $date
        );

        $records = $daoCheckin->getCheckins($condition, null, 'createtime ASC, type ASC');

        $ret = $records->toArray();
        return $ret;
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @param Model_App_Attend_Tudu_Apply $data
     */
    public function updateCheckinApply(Model_Tudu_Tudu $tudu, Model_App_Attend_Tudu_Apply $data)
    {
        $data = $data->getAttributes();

        /* @var $daoApply Dao_App_Attend_Apply */
        $daoApply = Tudu_Dao_Manager::getDao('Dao_App_Attend_Apply', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        /* @var $daoTotal Dao_App_Attend_Total */
        $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        // 更新相关状态
        $daoApply->updateApply($data['applyid'], array('status' => 2));
        $daoApply->updateReviewer($data['applyid'], $tudu->uniqueId, array('status' => 1));

        // 签到
        if ($data['checkintype'] == 0) {
            $type = 0;
            $checkinTime = $data['starttime'];
            // 签退
        } elseif ($data['checkintype'] == 1) {
            $type = 1;
            $checkinTime = $data['endtime'];
        }
        $status = Dao_App_Attend_Checkin::STATUS_NORMAL;

        // 读取补签那天排班计划
        $applyDate  = strtotime(date('Y-m-d', $checkinTime));
        $applyMonth = date('Ym', $applyDate);
        $plan       = $this->getPlan($data['orgid'], $data['uniqueid'], $applyDate);

        if (!empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
            // 上班签到
            if ($type == Dao_App_Attend_Checkin::TYPE_CHECKIN && $plan['checkintime']) {
                $setCheckinTime = $applyDate + $this->formatTimeToSec($plan['checkintime']);
                if (!empty($plan['latestandard'])) {
                    $setCheckinTime += $plan['latestandard'] * 60;
                }
                if ($checkinTime > $setCheckinTime) {
                    $status = Dao_App_Attend_Checkin::STATUS_LATE;
                    if (!empty($plan['latecheckin'])) {
                        $outworkTime    = $plan['latecheckin'] * 60;
                        $setCheckinTime = $applyDate + $this->formatTimeToSec($plan['checkintime']);
                        if ($checkinTime - $setCheckinTime > $outworkTime) {
                            $status = Dao_App_Attend_Checkin::STATUS_WORK;
                        }
                    }
                }
                // 下班签退
            } elseif ($type == Dao_App_Attend_Checkin::TYPE_CHECKOUT && $plan['checkouttime']) {
                $setCheckoutTime = $applyDate + $this->formatTimeToSec($plan['checkouttime']);
                $calType = 0;
                $cinTime = 0;
                if ($plan['checkintime']) {
                    // 读取签到记录
                    $checkin = $daoCheckin->getCheckin(array(
                    'orgid'      => $data['orgid'],
                    'uniqueid'   => $data['uniqueid'],
                    'date'       => $applyDate,
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
                                $setCheckoutTime = $applyDate + $this->formatTimeToSec($plan['checkouttime']);
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

        $checkin = $daoCheckin->getCheckin(array(
        'orgid'      => $data['orgid'],
        'uniqueid'   => $data['uniqueid'],
        'date'       => $applyDate,
        'type'       => $type
        ));
        if (null === $checkin) {
            // 创建签到记录
            $checkinId = $daoCheckin->createCheckin(array(
            'checkinid'  => Dao_App_Attend_Checkin::getCheckinId(),
            'orgid'      => $data['orgid'],
            'uniqueid'   => $data['uniqueid'],
            'date'       => $applyDate,
            'status'     => $status,
            'type'       => $type,
            'createtime' => $checkinTime
            ));
        } else {
            if ($type == Dao_App_Attend_Checkin::TYPE_CHECKIN) {
                if ($checkin->createTime > $checkinTime) {
                    $checkinId = $daoCheckin->updateCheckin($checkin->checkinId, array(
                    'status'     => $status,
                    'createtime' => $checkinTime
                    ));
                } else {
                    $checkinId = $checkin->checkinId;
                }
            } elseif ($type == Dao_App_Attend_Checkin::TYPE_CHECKOUT) {
                if ($checkin->createTime < $checkinTime) {
                    $checkinId = $daoCheckin->updateCheckin($checkin->checkinId, array(
                    'status'     => $status,
                    'createtime' => $checkinTime
                    ));
                } else {
                    $checkinId = $checkin->checkinId;
                }
            }
        }

        if ($checkinId) {
            // 获取签到、签退状态
            $checkinStatus = 0;

            if (!empty($plan) && $plan['scheduleid'] != '^off' && $plan['scheduleid'] != '^exemption') {
                $checkinStatus = $this->getCheckinStatus($plan, $data['uniqueid'], $applyDate);
            }

            $attendDate = $daoDate->getAttendDate(array('uniqueid' => $data['uniqueid'], 'date' => $applyDate));
            if (!$attendDate) {
                $iswork = 1;
                if (($plan !== null && !$plan['checkintime'] && !$plan['checkouttime'])
                || empty($plan)
                || (!empty($plan) && ($plan['scheduleid'] == '^off' || $plan['scheduleid'] == '^exemption'))
                || ($type == 0 && $plan !== null && !$plan['checkouttime'] && $status != Dao_App_Attend_Checkin::STATUS_WORK))
                {
                    $iswork = 0;
                }

                $daoDate->create(array(
                    'orgid'      => $data['orgid'],
                    'uniqueid'   => $data['uniqueid'],
                    'date'       => $applyDate,
                    'iswork'     => $iswork,
                    'checkinstatus' => $checkinStatus,
                    'updatetime' => time()
                ));
            } else {
                $sum = array($checkinStatus);
                if (!empty($attendDate->checkinStatus)) {
                    foreach ($attendDate->checkinStatus as $item) {
                        if ($item == 2) {
                            $sum[] = 4;
                            break;
                        }
                    }
                }

                $checkinStatus = array_sum($sum);
                $update        = array('checkinstatus' => $checkinStatus);
                $rs            = Dao_App_Attend_Date::formatCheckinStatus($checkinStatus);
                if (!empty($rs)) {
                    foreach ($rs as $item) {
                        if (0 == $item || 1 == $item) {
                            $update['iswork'] = 1;
                            break;
                        }
                    }
                }

                $daoDate->update($data['uniqueid'], $applyDate, $update);
            }

            // 判断月统计表是否已有当月的统计记录
            $exists = $daoMonth->existsRecord($data['uniqueid'], $applyMonth);
            if (!$exists) {
                $daoMonth->create(array(
                'orgid'      => $data['orgid'],
                'uniqueid'   => $data['uniqueid'],
                'date'       => $applyMonth,
                'updatetime' => time()
                ));
            }
        }

        $this->attendCount($plan, $data['uniqueid'], $applyDate);

        $memo   = array($data['categoryname'], $type, 0 == $type ? $data['starttime'] : $data['endtime']);

        $daoDate->addApply(array(
        'orgid'      => $data['orgid'],
        'uniqueid'   => $data['uniqueid'],
        'date'       => $applyDate,
        'categoryid' => $data['categoryid'],
        'memo'       => implode('|', $memo)
        ));

        // 统计考勤类型次数等
        if(!$daoTotal->existsRecord($data['categoryid'], $data['uniqueid'], $applyMonth)) {
            $daoTotal->create(array(
            'orgid'      => $data['orgid'],
            'uniqueid'   => $data['uniqueid'],
            'categoryid' => $data['categoryid'],
            'date'       => $applyMonth,
            'total'      => 1,
            'updatetime' => time()
            ));
        } else {
            $daoTotal->updateTotal($data['categoryid'], $data['uniqueid'], $applyMonth);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 更新图度
        $ret = $daoTudu->updateTudu($tudu->tuduId, array('isdone' => 1));
        if (!$ret) {
            return false;
        }

        // 获取图度关联用户
        $users = $daoTudu->getUsers($tudu->tuduId);
        // 标签操作
        foreach ($users as $u) {
            $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^i');  //移除图度箱标签
            $daoTudu->addLabel($tudu->tuduId, $u['uniqueid'], '^o');     //添加已完成标签
            $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^a');  //移除我执行标签
            $daoTudu->deleteLabel($tudu->tuduId, $u['uniqueid'], '^e');  //移除我审批标签
        }
    }

    /**
     * 获取用户排班计划
     * 没有设置则返回默认班信息
     */
    public function getPlan($orgId, $uniqueId, $date)
    {
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
        $condition = array('date' => date('Ym', $date), 'uniqueid' => $uniqueId);
        $plan      = $daoPlan->getMonthPlan($condition);
        $day       = date('j', $date);

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
                $scheduleId = $this->getPlanByWeekPlan($weekPlan, $date);
            }
        }

        $schedule = null;
        if (!empty($scheduleId)) {
            if ($scheduleId != '^off') {
                if ($scheduleId == '^default') {
                    $query = array(
                    'orgid'      => $orgId,
                    'scheduleid' => '^default',
                    'week'       => date('w', $date),
                    'status'     => 1
                    );
                    if (!empty($adjust) && $adjust->type == 1) {
                        $query = array(
                        'orgid'      => $orgId,
                        'scheduleid' => '^default',
                        'week'       => date('w', $date)
                        );
                    }
                    $schedule = $daoSchedule->getSchedule($query);
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                } else {
                    $schedule = $daoSchedule->getSchedule(array('orgid' => $orgId, 'scheduleid' => $scheduleId), array('isvalid' => true));
                    if (null === $schedule) {
                        $query = array(
                        'orgid'      => $orgId,
                        'scheduleid' => '^default',
                        'week'       => date('w', $date),
                        'status'     => 1
                        );
                        if (!empty($adjust) && $adjust->type == 1) {
                            $query = array(
                            'orgid'      => $orgId,
                            'scheduleid' => '^default',
                            'week'       => date('w', $date)
                            );
                        }
                        $schedule = $daoSchedule->getSchedule($query);
                    }
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                }
            } else {
                // 非工作日的调整成工作日的，默认班填充
                if (!empty($adjust) && $adjust->type == 1) {
                    $schedule = $daoSchedule->getSchedule(array(
                    'orgid'      => $orgId,
                    'scheduleid' => '^default',
                    'week'       => date('w', $date)
                    ));
                    $schedule = (null !== $schedule) ? $schedule->toArray() : null;
                }
            }
        } else {
            $schedule = $daoSchedule->getSchedule(array(
            'orgid'      => $orgId,
            'scheduleid' => '^default',
            'week'       => date('w', $date)
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