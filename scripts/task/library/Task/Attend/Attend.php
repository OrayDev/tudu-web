<?php
/**
 * Job
 *
 * LICENSE
 *
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Attend.php 2771 2013-03-11 03:50:01Z cutecube $
 */

/**
 * 计划：每天执行一次
 *
 * 考勤数据处理
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Attend_Attend extends Task_Abstract
{
    /**
     *
     * @var array<Zend_Db_Adapter>()
     */
    public $_dbs = array();

    /**
     *
     * @var array
     */
    public $_defaultSchedule;

    /**
     *
     */
    public function startUp()
    {
        foreach ($this->_options['multidb'] as $key => $item) {
            Tudu_Dao_Manager::setDb($key, Zend_Db::factory($item['adapter'], $item['params']));
        }
    }

    /**
     * 执行讨论数据处理
     */
    public function run()
    {
        $date = isset($this->_params['date']) ? strtotime($this->_params['date']) : null;
        if (!$date) {
            $date = strtotime('yesterday');
        }

        // 获取开启考勤应用的组织
        $sql = "SELECT org_id, active_time FROM app_org WHERE status = 1 AND active_time <= " . $date;

        $dbApp = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_APP);
        $dbMd  = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD);

        $query = $dbApp->query($sql);

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        // 遍历用户，创建当天的考勤记录
        while ($row = $query->fetch()) {
            $orgId = $row['org_id'];

            // 过滤停用账号
            $users = $dbMd->fetchAll('SELECT org_id, user_id, unique_id FROM md_user WHERE org_id = ' . $dbMd->quote($orgId) . ' AND status <> 0');

            foreach ($users as $user) {
                // 获取排班
                $schedule = $this->getPlan($orgId, $user['unique_id'], $date);

                // 没有排班
                if (empty($schedule)) {
                    continue;
                }

                // 有排班计划，若上班下班都是免签，则跳过
                if (!empty($schedule) && $schedule['checkintime'] === null && $schedule['checkouttime'] === null) {
                    continue ;
                }

                // 不用上班、免签班，跳过
                if (!empty($schedule) && ($schedule['scheduleid'] == '^off' || $schedule['scheduleid'] == '^exemption')) {
                    continue ;
                }

                // 是否存在当天的签到记录
                $attend = $daoDate->getAttendDate(array(
                    'orgid'    => $orgId,
                    'uniqueid' => $user['unique_id'],
                    'date'     => $date
                ));

                // 存在则跳过
                if (null != $attend) {
                    // 下班要签退的情况（非免签）
                    if ($schedule['checkouttime'] !== null) {
                        // 是否有进行下班签退
                        $checkout = $daoCheckin->getCheckin(array(
                            'orgid'      => $orgId,
                            'uniqueid'   => $user['unique_id'],
                            'date'       => $date,
                            'type'       => Dao_App_Attend_Checkin::TYPE_CHECKOUT
                        ));

                        // 没有签退
                        if (null === $checkout) {
                            // 更新考勤统计
                            $this->updateAttendCount($orgId, $user['unique_id'], $date);
                        }
                    }

                    continue ;
                }

                // 需要签到的日子
                if (!empty($schedule)) {
                    // 获取签到、签退状态
                    $checkinStatus = $this->getCheckinStatus($schedule, $user['unique_id'], $date);

                    $params = array(
                        'orgid'      => $orgId,
                        'uniqueid'   => $user['unique_id'],
                        'date'       => $date,
                        'iswork'     => $checkinStatus ? 1 : 0,
                        'checkinstatus' => $checkinStatus,
                        'updatetime' => time()
                    );

                    // 当天是否有考勤申请
                    $isApply = $daoDate->isApply($user['unique_id'], $date);

                    if ($isApply) {
                        $params['iswork'] = 0;
                        $params['checkinstatus'] = 0;
                    }

                    // 创建记录
                    $daoDate->create($params);

                    // 更新考勤统计
                    $this->updateAttendCount($orgId, $user['unique_id'], $date);

                    $this->getLogger()->debug('Create attend record : ' . $user['unique_id'] . '-' . date('Y-m-d', $date));
                }
            }

            // 更新IP异常
            $this->updateAbnormalIp($orgId, $date);
            $this->getLogger()->debug('Update Org Ip Abnormal Finish : date:[' . $date . '],orgid:[' . $orgId . ']');
        }
    }

    /**
     *
     * @param string $orgId
     * @param string $uniqueId
     * @param int    $date
     * @return NULL|array
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
                    $schedule = $this->getDefaultSchedule($orgId, date('w', $date));
                    if (!empty($schedule) && !$schedule['status']) {
                        $schedule = null;
                    }
                } else {
                    $schedule = $daoSchedule->getSchedule(array('orgid' => $orgId, 'scheduleid' => $scheduleId), array('isvalid' => true));
                    if (null === $schedule) {
                        $schedule = $this->getDefaultSchedule($orgId, date('w', $date));
                        if (!empty($schedule) && !$schedule['status']) {
                            $schedule = null;
                        }
                    } else {
                        $schedule = $schedule->toArray();
                    }
                }
            } else {
                // 非工作日的调整成工作日的，默认班填充
                if (!empty($adjust) && $adjust->type == 1) {
                    $schedule = $this->getDefaultSchedule($orgId, date('w', $date));
                }
            }
        } else {
            $schedule = $this->getDefaultSchedule($orgId, date('w', $date));
            if (empty($adjust) && !empty($schedule) && !$schedule['status']) {
                $schedule = null;
            }
        }

        return $schedule;
    }

    /**
     *
     * @param string $orgId
     * @param int    $week
     */
    public function getDefaultSchedule($orgId, $week)
    {
        if (!isset($this->_defaultSchedule[$orgId])) {
            /* @var $daoSchedule Dao_App_Attend_Schedule */
            $daoSchedule = Tudu_Dao_Manager::getDao('Dao_App_Attend_Schedule', Tudu_Dao_Manager::DB_APP);

            $condition = array(
                'orgid'      => $orgId,
                'scheduleid' => '^default',
                'week'       => $week
            );

            $schedule = $daoSchedule->getSchedule($condition);

            $this->_defaultSchedule[$week] = (null !== $schedule) ? $schedule->toArray() : null;
        }

        return $this->_defaultSchedule[$week];
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
     * 获取签到、签退状态
     */
    public function getCheckinStatus($plan, $uniqueId, $date)
    {
        $checkinStatus = 0;

        /* @var $daoCheckin Dao_App_Attend_Checkin */
        $daoCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        // 读取当天所有的签到记录
        $condition = array(
            'uniqueid' => $uniqueId,
            'date'     => $date
        );
        $records = $daoCheckin->getCheckins($condition, null, 'type ASC');
        if (null === $records) {
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
        foreach ($records as $checkin) {
            $type[] = $checkin->type;
        }
        $type = array_unique($type);

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
     * 更新考勤统计
     */
    public function updateAttendCount($orgId, $uniqueId, $date)
    {
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth   = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);

        $exists = $daoMonth->existsRecord($uniqueId, date('Ym', $date));
        if (!$exists) {
            $daoMonth->create(array(
                'orgid'      => $orgId,
                'uniqueid'   => $uniqueId,
                'date'       => date('Ym', $date),
                'updatetime' => time()
            ));
        }

        $sum = $daoDate->dateSum(array(
            'uniqueid'  => $uniqueId,
            'startdate' => mktime(0, 0, 0, date('m', $date), 1, date('Y', $date)),
            'enddate'   => mktime(0, 0, 0, date('m', $date) + 1, 1, date('Y', $date))
        ));

        if (!empty($sum)) {
            $monthParams = array(
                'late'       => (int) $sum['late'],
                'leave'      => (int) $sum['leave'],
                'unwork'     => (int) $sum['unwork'],
                'updatetime' => time()
            );
            $daoMonth->update($uniqueId, date('Ym', $date), $monthParams);
        }
    }

    /**
     * 更新异常IP
     */
    public function updateAbnormalIp($orgId, $date)
    {
        $records = $this->getCountIpCheckins($orgId, $date);
        $unIds   = array();
        if (!empty($records)) {
            while ($row = $records->fetch()) {
                $sumIp    = $row['sumip'];

                if ($sumIp <= 1) {
                    $arr   = explode(',', $row['uniqueid']);
                    $unIds = array_merge($unIds, $arr);
                }
            }
        }

        if (!empty($unIds)) {
            $unIds = array_unique($unIds);
            /* @var $daoDate Dao_App_Attend_Date */
            $daoDate  = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
            /* @var $daoMonth Dao_App_Attend_Month */
            $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);

            foreach ($unIds as $unId) {
                $ret = $daoDate->update($unId, $date, array('isabnormalip' => 1));
                if (!$ret) {
                    $this->getLogger()->warn('Update Item Ip Abnormal Failed : date:[' . $date . '],orgid:[' . $orgId . '],uniqueid:[' . $unId . ']');
                    continue;
                }

                $month = date('Ym', $date);
                $ret   = $daoMonth->update($unId, $month, array('isabnormalip' => 1, 'updatetime' => time()));
                if (!$ret) {
                    $this->getLogger()->warn('Update Item Ip Abnormal Failed : date:[' . $month . '],orgid:[' . $orgId . '],uniqueid:[' . $unId . ']');
                    continue;
                }
                $this->getLogger()->debug('Update Item Ip Abnormal : orgid:[' . $orgId . '],uniqueid:[' . $unId . ']');
            }
        }
    }

    /**
     * 获取签到IP统计
     *
     * @param string $orgId
     * @param int    $date
     * @return array
     */
    public function getCountIpCheckins($orgId, $date)
    {
        $dbApp   = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_APP);
        $table   = 'attend_checkin';
        $columns = 'org_id AS orgid, date, type, ip, GROUP_CONCAT(DISTINCT(unique_id)) AS uniqueid, COUNT(ip) AS sumip';
        $groupBy = 'GROUP BY ip,type';
        $where   = 'org_id = :orgid AND date = :date';
        $bind    = array(
            'orgid' => $orgId,
            'date'  => $date
        );

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$groupBy}";

        try {
            $records = $dbApp->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            return array();
        }

        return $records;
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
}