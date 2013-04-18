<?php
/**
 * Calendar Controller
 *
 * @version $Id: CalendarController.php 2320 2012-11-01 02:26:25Z web_op $
 */

class CalendarController extends TuduX_Controller_Base
{

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu', 'calendar'));

        $this->view->LANG = $this->lang;
    }

    /**
     *
     */
    public function preDispatch()
    {
        if (!$this->_user->isLogined()) {
            $action = $this->_request->getActionName();

            if (in_array($action, array('index', 'log', 'logList'))) {
                $this->jump(null, array('error' => 'timeout'));
            } else {
                return $this->json(false, $this->lang['login_timeout']);
            }
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     *
     */
    public function indexAction()
    {
        $unIds     = trim($this->_request->getQuery('unid'));
        $uniqueId  = $this->_user->uniqueId;
        $date      = $this->_request->getQuery('date');
        $isShowCal = false;
        $isquery   = true;
        $queryDate = array();

        if (!empty($date)) {
            $isShowCal = true;
            $dateStart = strtotime($date);
            $queryDate = array('start' => $dateStart, 'end' => $dateStart + 86400);
            $this->view->current = date('d', $dateStart);
        }

        if (empty($unIds) || $unIds == $uniqueId) {
            $isquery = false;
            $unIds = array($uniqueId);
        } else {
            $unIds = explode(',', $unIds);
        }

        $type      = $this->_request->getQuery('type', 'week');
        $startDate = $this->_request->getQuery('sd');

        if (!$startDate) {
            $startDate = $this->_timestamp;
        }

        // 本周
        if ($type == 'week') {
            $startDate = $startDate - (int) date('w', $startDate) * 86400;
            $startDate = strtotime(date('Y-m-d', $startDate));

            $endDate = $startDate + 6 * 86400;

            $startWeekNum   = (int) date('W', strtotime(date('Y-m-1', $startDate)));
            $currentWeekNum = (int) date('W', $startDate);

            if ($currentWeekNum >= $startWeekNum) {
                $weekNum = $currentWeekNum - $startWeekNum + 1;
            } else {
                $weekNum = $currentWeekNum + 1;
            }

            $this->view->weeknum = $weekNum;
        // 本月
        } else {
            $startDate = strtotime(date('Y-m-1', $startDate));
            $endDate   = strtotime(date('Y-m-t', $startDate));
        }

        $headers = array();
        for ($md = $startDate; $md <= $endDate; $md += 86400) {
            $headers[] = $md;
        }

        // 读取图度数据
        /* @var $daoCaneldar Dao_Td_Tudu_Calendar */
        $daoCaneldar = $this->getDao('Dao_Td_Tudu_Calendar');

        $tudus = array();

        foreach ($unIds as $unId) {
            $records = $daoCaneldar->getCalendarTudus(array(
                'uniqueid'  => $this->_user->uniqueId,
                'target'    => $unId,
                'role'      => 'to',
                'date'      => $queryDate,
                'starttime' => $startDate,
                'endtime'   => $endDate
            ), 'status ASC, lastposttime DESC');

            $tudus[$unId] = $records->toArray();
        }

        if (!$isquery) {
            $tudus = array_pop($tudus);
        }

        /***********日历***************/
        $year  = date('Y', $startDate);
        $month = date('m', $startDate);
        $lines = 21;
        $cal   = array();

        // 当前月第一天的时间戳
        $monthFirst = mktime(0, 0, 0, $month, 1, $year);
        // 星期几
        $firstW = date('w', $monthFirst);
        // 月天数
        $t = date('t', $monthFirst);

        // 循环第一行
        for ($i = 1; $i <= $lines - $firstW; $i++) {
            $cal['first'][] = $i;
        }
        $cal['first'] = array_pad($cal['first'], -21, 0);

        // 循环第二行
        for ($j = $cal['first'][20] + 1; $j <= $t; $j++) {
            $cal['last'][] = $j;
        }
        $cal['last'] = array_pad($cal['last'], 21, 0);
        $this->view->year  = $year;
        $this->view->month = $month;
        $this->view->cal   = $cal;
        /***********日历***************/

        $this->view->registFunction('cal_gantt', array($this, 'ganttDraw'));

        $params = array(
            'next' => $endDate + 86400,
            'prev' => $startDate - 86400,
            'unid' => implode(',', $unIds)
        );
        if ($isShowCal) {
            $params['date'] = $date;
        }

        $this->view->params    = $params;
        $this->view->type      = $type;
        $this->view->startdate = $startDate;
        $this->view->enddate   = $endDate;
        $this->view->headers   = $headers;
        $this->view->tudus     = $tudus;
        $this->view->isquery   = $isquery;
        $this->view->isshowcal = $isShowCal;
    }

    /**
     * 查询子图度
     */
    public function childrenAction()
    {
        /** @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $tuduId = $this->_request->getQuery('tid');
        $type   = $this->_request->getQuery('type');
        $startDate = $this->_request->getQuery('sd');
        $endDate = $this->_request->getQuery('ed');

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null !== $tudu) {

            $isReceiver = $tudu->uniqueId == $this->_user->uniqueId && count($tudu->labels);

            $boards = $this->getBoards(false);

            $tudus = $daoTudu->getGroupTudus(array(
                'parentid' => $tuduId,
                'uniqueid' => $this->_user->uniqueId,
                'starttime' => $startDate,
                'endtime'   => $endDate
            ), null, 'lastposttime DESC')->toArray();

            $boardAccess = array();

            foreach ($tudus as $key => $tudu) {
                if ($tudu['isexpired'] && time() < $startDate) {
                    unset($tudus[$key]);
                }
            }

            $this->view->currUrl = urlencode($this->_request->getQuery('currUrl'));
            $this->view->tudus   = $tudus;

        }

        $headers = array();
        for ($md = $startDate; $md <= $endDate; $md += 86400) {
            $headers[] = $md;
        }

        $this->view->registFunction('cal_gantt', array($this, 'ganttDraw'));

        $this->view->headers   = $headers;
        $this->view->type      = $type;
        $this->view->startdate = $startDate;
        $this->view->enddate   = $endDate;
    }

    /**
     *
     * @param $smarty
     * @param $params
     */
    public function ganttDraw($params, &$smarty)
    {
        $min = $params['min'];
        $max = $params['max'];
        $startTime = $params['starttime'];
        $endTime   = $params['endtime'];
        $isexpired = $params['isexpired'];
        $status    = $params['status'];
        $completetime = $params['completetime'];
        $istudugroup  = $params['istudugroup'];
        $isAllDay  = isset($params['allday']) ? $params['allday'] : false;
        $return    = array(
            'width' => '100%',
            'left'  => '0',
            'leftlimit' => true,
            'rightlimit' => true
        );

        if (!$min || !$max) {
            return ;
        }

        $max += 86400;

        if (!$endTime) {
            $endTime = strtotime('today');
        }

        if (!$params['endtime'] && $completetime) {
            $endTime = $completetime;
        }

        if ($startTime < $min) {
            $return['leftlimit'] = false;
        }

        $startTime = max($min, $startTime);
        $endTime   = min($max, $endTime);

        if ($isAllDay) {
            $startTime = strtotime(date('Y-m-d 00:00:00', $startTime));
        }

        if ($isAllDay || date('H:i:s', $endTime) == '00:00:00') {
            $endTime = strtotime(date('Y-m-d 00:00:00', $endTime)) + 86400;
        }

        if ($startTime <= $min) {
            $return['left'] = 0;
        } else {
            $return['left'] = round((($startTime - $min)/($max - $min) * 100), 2) . '%';
        }

        $return['width'] = min(100, max(0, round(($endTime - $startTime)/($max - $min) * 100, 2))) . '%';

        if ($isexpired && $istudugroup) {
            $endTime = strtotime(date('Y-m-d 00:00:00', time())) + 86400;
            $return['width'] = min(100, max(0, round(($endTime - $startTime)/($max - $min) * 100, 2))) . '%';
        }

        if ($isexpired && !$istudugroup) {
            $return['rightlimit'] = false;
            $startTime = $endTime;
            $endTime = strtotime('today') + 86400;
            $return['exwidth'] = round(($endTime - $startTime)/($max - $min) * 100, 2) . '%';
            $return['exleft']  = round((($startTime - $min)/($max - $min) * 100), 2) . '%';
            //$return['left']    = $return['exleft'];
            //$return['width']   = $return['exwidth'];
            $return['rightlimit'] = false;
        }

        if ($completetime) {
            $completetime = strtotime(date('Y-m-d 00:00:00', $completetime)) + 86400;

            if ($status == 2 && $completetime < $endTime) {
                /*$return['exleftlimit'] = true;
                $return['exrightlimit'] = false;
                $return['exwidth'] = min(100, round(($completetime - $startTime)/($max - $min) * 100, 2)) . '%';
                $return['exleft'] = round((($startTime - $min)/($max - $min) * 100), 2) . '%';*/
                $return['exleftlimit'] = false;
                $return['exrightlimit'] = true;
                $return['exwidth'] = round(($endTime - $completetime)/($max - $min) * 100, 2) . '%';
                $return['exleft'] = round((($completetime - $min)/($max - $min) * 100), 2) . '%';

                /*if (!$params['endtime']) {
                    $return['exleftlimit'] = false;
                    $return['exrightlimit'] = true;
                    $return['rightlimit'] = true;
                    $return['width'] = max(0, round(($completetime - $startTime)/($max - $min) * 100, 2)) . '%';
                }*/
            }

            /*if ($max < time()+86400) {
                if ($status == 2 && !$params['endtime']) {
                    $return['exleftlimit'] = false;
                    $return['exrightlimit'] = false;
                    $return['exwidth'] = round(($completetime - $startTime)/($max - $min) * 100, 2) . '%';
                    $return['exleft'] = round((($startTime - $min)/($max - $min) * 100), 2) . '%';
                }
            }*/
        }

        if (!empty($params['assign'])) {
            $smarty->assign($params['assign'], $return);
        } else {
            return $return;
        }
    }

    /**
     * 输出组织架构数据
     *
     */
    public function castAction()
    {
        /* @var $daoDepartment Dao_Md_Department_Department */
        $daoDepartment = $this->getMdDao('Dao_Md_Department_Department');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = $this->getMdDao('Dao_Md_User_Cast');

        $depts = $daoDepartment->getDepartments(array(
            'orgid' => $this->_user->orgId
        ))->toArray();
        $users = $daoCast->getCastUsers(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId), null, 'ordernum DESC');

        $resultDepts = array();
        $depth = null;
        for ($i = 0; $i < count($depts); $i++) {
            if($depth == $depts[$i]['depth']) {
                $depth = null;
            }

            foreach($depts[$i]['moderators'] as $value) {
                if($this->_user->userId == $value){
                    $resultDepts[] = $depts[$i];
                    $depth = $depts[$i]['depth'];
                }
            }

            if(null !== $depth && $depts[$i]['depth'] > $depth ) {
                $resultDepts[] = $depts[$i];
            }
        }

        $data = array(
            'users' => $users->toArray(),
            'depts' => $resultDepts
        );
        $this->json(true, null, $data);
    }

    /**
     * 导出日程表数据
     */
    public function exportAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $unIds = trim($this->_request->getQuery('unid'));
        $unIds = explode(',', $unIds);
        $type      = $this->_request->getQuery('type', 'week');
        $startDate = $this->_request->getQuery('sd');

        // 周
        if ($type == 'week') {
            $startDate = $startDate - (int) date('w', $startDate) * 86400;
            $startDate = strtotime(date('Y-m-d', $startDate));

            $endDate = $startDate + 6 * 86400;
        // 月
        } else {
            $startDate = strtotime(date('Y-m-1', $startDate));
            $endDate   = strtotime(date('Y-m-t', $startDate));
        }

        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = $this->getMdDao('Dao_Md_User_Cast');

        $users = $daoCast->getCastUsers(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId), null, 'ordernum DESC');

        /* @var $daoTudu Dao_Td_Tudu_Calendar */
        $daoCaneldar = $this->getDao('Dao_Td_Tudu_Calendar');
        $tudus       = array();

        // 读取图度数据
        foreach ($unIds as $unId) {
            $records = $daoCaneldar->getCalendarTudus(array(
                'uniqueid'  => $this->_user->uniqueId,
                'target'    => $unId,
                'role'      => 'to',
                'starttime' => $startDate,
                'endtime'   => $endDate
            ), 'status ASC, lastposttime DESC');

            $tudus[$unId] = $records->toArray();
        }

        $filename = 'calendar-' . date('Ymd') . '.csv';
        $data     = array();
        // 准备导出数据的列名
        $columns  = array(
            Oray_Function::utf8ToGbk($this->lang['tudu_user']),
            Oray_Function::utf8ToGbk($this->lang['subject']),
            Oray_Function::utf8ToGbk($this->lang['receiver']),
            Oray_Function::utf8ToGbk($this->lang['percent']),
            Oray_Function::utf8ToGbk($this->lang['starttime']),
            Oray_Function::utf8ToGbk($this->lang['endtime']),
            Oray_Function::utf8ToGbk($this->lang['finish_time'])
        );

        $data[]    = implode(',', $columns);
        $castUsers = $users->toArray('uniqueid');
        $accepterUsers = $users->toArray('username');

        foreach ($tudus as $uid => $tudu) {
            $user     = $castUsers[$uid];
            $trueName = !empty($user['truename']) ? $user['truename'] : $user['userid'];
            foreach ($tudu as $record) {
                $startTime    = !empty($record['starttime']) ? date('Y-m-d H:i', $record['starttime']) : '-';
                $endTime      = !empty($record['endtime']) ? date('Y-m-d H:i', $record['endtime']) : '-';
                $completeTime = !empty($record['completetime']) ? date('Y-m-d H:i', $record['completetime']) : '-';
                $percent      = !empty($record['percent']) ? $record['percent'] . '%' : '0%';
                $accepters    = !empty($record['accepter']) ? $record['accepter'] : array();
                $receiver     = array();

                foreach ($accepters as $accepter) {
                    if (!empty($accepterUsers[$accepter])) {
                        $accepterUser = $accepterUsers[$accepter];
                        $receiver[]   = !empty($accepterUser['truename'])? $accepterUser['truename'] : $accepterUser['userid'];
                    }
                }
                $receiver = !empty($receiver) ? implode('、', $receiver) : '-';

                $data[] = implode(',', array(
                    Oray_Function::utf8ToGbk($trueName),
                    Oray_Function::utf8ToGbk($record['subject']),
                    Oray_Function::utf8ToGbk($receiver),
                    $percent,
                    $startTime,
                    $endTime,
                    $completeTime,
                ));
            }
        }

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo implode("\n", $data);
    }
}