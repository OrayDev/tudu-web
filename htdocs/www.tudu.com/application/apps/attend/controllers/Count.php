<?php
/**
 * 考勤统计
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Count.php 2769 2013-03-07 10:09:47Z chenyongfa $
 */

/**
 * @see Apps_Attend_Abstract
 */
require_once dirname(__FILE__) . '/Abstract.php';

class Apps_Attend_Count extends Apps_Attend_Abstract
{
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
     * 考勤汇总（月统计）
     */
    public function indexAction()
    {
        $year  = $this->_request->getQuery('year');
        $month = $this->_request->getQuery('month');
        $page  = max(1, (int) $this->_request->getQuery('page'));
        $keywords = $this->_request->getQuery('keywords');
        $deptId   = $this->_request->getQuery('deptid');
        $pageSize = 25;

        $year  = !empty($year) ? $year : date('Y');
        $month = !empty($month) ? str_pad($month, 2, '0', STR_PAD_LEFT) : date('m');

        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        /* @var $daoTotal Dao_App_Attend_Total */
        $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);

        // 读取分类
        $categories = $daoCategory->getCategories(array('orgid' => $this->_user->orgId), array('status' => 1), 'issystem DESC, createtime DESC');

        $query = array();
        $userCondition = array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
        );
        $roles = $this->getRoles();

        $condition = array('orgid' => $this->_user->orgId, 'date' => $year . $month);
        $params    = array('year' => $year, 'month' => $month);

        if (!empty($roles) && !empty($roles['sum'])) {
            if (!empty($keywords)) {
                $userCondition['keyword'] = $keywords;
                $query['keywords'] = $keywords;
            }

            if (!empty($deptId)) {
                $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

                $deptIds = $daoDept->getChildDeptid($this->_user->orgId, $deptId);
                $userCondition['deptid'] = array_merge((array) $deptId, $deptIds);
                $query['deptid'] = $deptId;
            } else {
                if (empty($roles['admin']) && !empty($roles['sum'])) {
                    $depts = array();
                    if (!empty($roles['moderator'])) {
                        $depts = $this->getModerateDepts(true, true);
                    }/* else {
                        $depts = $this->getRoleDepts(true, true);
                    }*/

                    if (!empty($depts)) {
                        $userCondition['deptid'] = $depts;
                    }
                }
            }

            /* @var $daoCast Dao_Md_User_Cast */
            $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);

            $users = $daoCast->getCastUserPage($userCondition, 'deptid DESC', $page, $pageSize);

            $pageInfo = array(
                'currpage'    => $page,
                'pagecount'   => $users->pageCount(),
                'recordcount' => $users->recordCount(),
                'url'         => '/app/attend/count/index',
                'query'       => array_merge($query, $params)
            );

            $users = $users->toArray();

            $uniqueIds = array();
            foreach ($users as $user) {
                $uniqueIds[] = $user['uniqueid'];
            }
            $condition['uniqueid'] = $uniqueIds;

            // 读取部门
            if (empty($roles['admin']) && !empty($roles['sum']) && $this->_user->deptId) {
                if (!empty($roles['moderator'])) {
                    $depts = $this->getModerateDepts(true);
                } else {
                    //$depts = $this->getRoleDepts(true);
                    $depts = $this->getDepts();
                }
            } else {
                $depts = $this->getDepts();
            }

            $this->view->depts      = $depts;
            $this->view->pageinfo   = $pageInfo;
        } else {
            $users = array();
            $condition['uniqueid'] = $this->_user->uniqueId;

            /* @var $daoUser Dao_Md_User_User*/
            $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
            $user    = $daoUser->getUserCard($userCondition);
            $users[] = $user;
        }

        $datas = array();
        $records = $daoMonth->getAttendMonthList($condition)->toArray('uniqueid');
        $totals  = $daoTotal->getAttendTotals($condition);

        foreach ($users as $user) {
            foreach ($totals as $total) {
                $id = strtr($total->categoryId, array('^' => ''));
                if ($total->uniqueId == $user['uniqueid']) {
                    $datas[$user['uniqueid']][$id] = $total->total;
                }
            }
            foreach ($categories as $category) {
                $id = strtr($category->categoryId, array('^' => ''));
                if (!isset($datas[$user['uniqueid']][$id])) {
                    $datas[$user['uniqueid']][$id] = 0;
                }
            }
            foreach ($records as $uniqueId => $record) {
                foreach ($record as $key => $value) {
                    if ($uniqueId == $user['uniqueid']) {
                        $datas[$user['uniqueid']][$key] = $value;
                    }
                }
            }

            $datas[$user['uniqueid']]['uniqueid'] = $user['uniqueid'];
            $datas[$user['uniqueid']]['truename'] = $user['truename'];
            $datas[$user['uniqueid']]['deptname'] = $user['deptname'];
        }

        $this->view->categories = $categories->toArray();
        $this->view->records    = $datas;
        $this->view->date       = array('year' => $year, 'month' => $month);
        $this->view->query      = array_merge($query, $params, array('page' => $page));
    }

    /**
     * 考勤汇总（月每天统计）
     */
    public function listAction()
    {
        $unId  = $this->_request->getQuery('unid');
        $year  = $this->_request->getQuery('year');
        $month = $this->_request->getQuery('month');
        $back  = $this->_request->getQuery('back');
        $page  = max(1, (int) $this->_request->getQuery('page'));
        $keywords = $this->_request->getQuery('keywords');
        $categoryId = $this->_request->getQuery('categoryid');
        $pageSize = 25;

        $year  = !empty($year) ? $year : date('Y');
        $month = !empty($month) ? str_pad($month, 2, '0', STR_PAD_LEFT) : date('m');
        $unId  = !empty($unId) ? $unId : $this->_user->uniqueId;
        $date  = array(
            'start' => mktime(0, 0, 0, (int) $month, 1, (int) $year),
            'end' => mktime(0, 0, 0, (int) $month + 1, 1, (int) $year)
        );

        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);
        $categories = $daoCategory->getCategories(array('orgid' => $this->_user->orgId), null, 'status DESC, issystem DESC, createtime DESC');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        $userInfo = $daoUser->getUserCard(array('uniqueid' => $unId));

        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Checkin */
        $daCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        $condition = array(
            'uniqueid' => $unId,
            'date' => $date
        );
        $params = array('unid' => $unId, 'year' => $year, 'month' => $month);
        $count  = array('late' => 0, 'leave' => 0, 'unwork' => 0);
        if (!empty($keywords)) {
            $params['keywords'] = $keywords;
        }
        if (!empty($back)) {
            $params['back'] = $back;
        }
        if (!empty($categoryId)) {
            $params['categoryid'] = $categoryId;
            if ($categoryId != '^late' && $categoryId != '^leave' && $categoryId != '^unwork' && $categoryId != '^uncheckin' && $categoryId != '^uncheckout') {
                $condition['categoryid'] = $categoryId;
            }
            if ($categoryId == '^late') {
                $condition['islate'] = true;
            }
            if ($categoryId == '^leave') {
                $condition['isleave'] = true;
            }
            if ($categoryId == '^unwork') {
                $condition['iswork'] = true;
            }
            if ($categoryId == '^uncheckin') {
                $condition['uncheckin'] = true;
            }
            if ($categoryId == '^uncheckout') {
                $condition['uncheckout'] = true;
            }
        }

        if (null !== $userInfo) {
            $recordCount = $daoDate->countDate($condition);
            $pageCount   = ($recordCount - 1) / $pageSize + 1;
            $today       = strtotime(date('Y-m-d'));
            $records     = $daoDate->getAttendDatePage($condition, 'date ASC', $page, $pageSize)->toArray();
            $checkins    = $daCheckin->getCheckins(array('uniqueid' => $unId, 'date' => array('start' => mktime(0, 0, 0, (int) $month, 1, (int) $year), 'end' => mktime(0, 0, 0, (int) $month + 1, 1, (int) $year))))->toArray();

            foreach ($records as &$record) {
                $isCheckin          = false;
                $isCheckout         = false;
                $isToday            = $record['date'] == $today ? true : false;
                $record['truename'] = $userInfo['truename'];
                $record['deptname'] = $userInfo['deptname'];
                foreach ($checkins as &$checkin) {
                    if ($record['date'] == $checkin['date']) {
                        // 上班签到
                        if ($checkin['type'] == 0) {
                            $isCheckin                = true;
                            $record['checkintime']    = $checkin['createtime'];
                            $record['checkinip']      = $checkin['ip'];
                            $record['checkinaddress'] = !empty($checkin['address']) ? $checkin['address'] : $this->lang['unknow'];
                        // 下班签退
                        } elseif ($checkin['type'] == 1) {
                            $isCheckout                = true;
                            $record['checkouttime']    = $checkin['createtime'];
                            $record['checkoutip']      = $checkin['ip'];
                            $record['checkoutaddress'] = !empty($checkin['address']) ? $checkin['address'] : $this->lang['unknow'];
                        }
                        unset($checkin);
                    }
                }
                if ($isToday && (!$isCheckout || !$isCheckin)) {
                    $record['iswork'] = 0;
                }
            }

            $pageInfo = array(
                'currpage'    => $page,
                'pagecount'   => (int) $pageCount,
                'recordcount' => $recordCount,
                'url'         => '/app/attend/count/list',
                'query'       => $params
            );

            $query = array('uniqueid' => $unId, 'date' => $year . $month);

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

            $this->view->records  = $records;
            $this->view->pageinfo = $pageInfo;
        } else {
            $userInfo = array('truename' => $params['keywords']);
        }

        $deptIds = array();
        $roles = $this->getRoles();
        if (empty($roles['admin']) && !empty($roles['sum']) && $this->_user->deptId) {
            if (!empty($roles['moderator'])) {
                $depts = $this->getModerateDepts(true);
            } else {
                //$depts = $this->getRoleDepts(true);
                $depts = $this->getDepts();
            }
        } else {
            $depts = $this->getDepts();
        }

        foreach ($depts as $dept) {
            $deptIds[] = $dept['deptid'];
        }

        $this->view->deptids    = $deptIds;
        $this->view->userinfo   = $userInfo;
        $this->view->categories = $categories->toArray();
        $this->view->date       = array('year' => $year, 'month' => $month);
        $this->view->query      = array_merge($params, array('page' => $page));
        $this->view->count      = $count;
        $this->view->registModifier('format_time', array($this, 'formatTime'));
    }

    /**
     * 导出数据
     */
    public function exportAction()
    {
        $this->_this->setNeverRender();

        $type  = $this->_request->getQuery('type');
        $url   = $this->_request->getQuery('url');
        $year  = $this->_request->getQuery('year');
        $month = $this->_request->getQuery('month');
        //$page  = max(1, (int) $this->_request->getQuery('page'));
        //$pageSize = 25;

        if (!in_array($type, array('month', 'date'))) {
            Oray_Function::alert($this->lang['error_type_of_operate'], $url);
        }

        /* @var $daoMonth Dao_App_Attend_Month */
        $daoMonth = Tudu_Dao_Manager::getDao('Dao_App_Attend_Month', Tudu_Dao_Manager::DB_APP);
        /* @var $daoTotal Dao_App_Attend_Total */
        $daoTotal = Tudu_Dao_Manager::getDao('Dao_App_Attend_Total', Tudu_Dao_Manager::DB_APP);
        /* @var $daoCategory Dao_App_Attend_Category */
        $daoCategory = Tudu_Dao_Manager::getDao('Dao_App_Attend_Category', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Date */
        $daoDate = Tudu_Dao_Manager::getDao('Dao_App_Attend_Date', Tudu_Dao_Manager::DB_APP);
        /* @var $daoDate Dao_App_Attend_Checkin */
        $daCheckin = Tudu_Dao_Manager::getDao('Dao_App_Attend_Checkin', Tudu_Dao_Manager::DB_APP);

        $data = array();
        switch ($type) {
            case 'month':
                $keywords = $this->_request->getQuery('keywords');
                $deptId   = $this->_request->getQuery('deptid');
                $filename = $year . $month . '.csv';

                // 读取分类
                $categories = $daoCategory->getCategories(
                    array('orgid' => $this->_user->orgId),
                    array('status' => 1),
                    'issystem DESC, createtime DESC'
                );

                // 导出数据的列名
                $columns = array(
                    Oray_Function::utf8ToGbk($this->lang['name']),
                    Oray_Function::utf8ToGbk($this->lang['dept']),
                    Oray_Function::utf8ToGbk($this->lang['category_late'] . '(' . $this->lang['times'] . ')'),
                    Oray_Function::utf8ToGbk($this->lang['category_leave'] . '(' . $this->lang['times'] . ')'),
                    Oray_Function::utf8ToGbk($this->lang['category_unwork'] . '(' . $this->lang['times'] . ')'),
                );

                // 导出数据分类的列名
                foreach ($categories as $category) {
                    if ($category->categoryId == '^checkin') {
                        $columns[] = Oray_Function::utf8ToGbk($category->categoryName . '(' . $this->lang['times'] . ')');
                    } else {
                        $columns[] = Oray_Function::utf8ToGbk($category->categoryName . '(' . $this->lang['hour'] . ')');
                    }
                }

                $data[] = implode(',', $columns);

                $userCondition = array(
                    'orgid'  => $this->_user->orgId,
                    'userid' => $this->_user->userId
                );
                $roles = $this->getRoles();

                $condition = array('orgid' => $this->_user->orgId, 'date' => $year . $month);

                if (!empty($roles) && !empty($roles['sum'])) {
                    if (!empty($keywords)) {
                        $userCondition['keyword'] = $keywords;
                    }

                    if (!empty($deptId)) {
                        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

                        $deptIds = $daoDept->getChildDeptid($this->_user->orgId, $deptId);
                        $userCondition['deptid'] = array_merge((array) $deptId, $deptIds);
                    } else {
                        if (empty($roles['admin']) && !empty($roles['sum'])) {
                            $depts  = array();
                            if (!empty($roles['moderator'])) {
                                $depts = $this->getModerateDepts(true, true);
                            }/* else {
                                $depts = $this->getRoleDepts(true, true);
                            }*/

                            if (!empty($depts)) {
                                $userCondition['deptid'] = $depts;
                            }
                        }
                    }

                    /* @var $daoCast Dao_Md_User_Cast */
                    $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
                    $users = $daoCast->getCastUsers($userCondition, null, 'deptid DESC');

                    $users = $users->toArray();

                    $uniqueIds = array();
                    foreach ($users as $user) {
                        $uniqueIds[] = $user['uniqueid'];

                        $condition['uniqueid'] = $uniqueIds;
                    }
                } else {
                    $users = array();
                    $condition['uniqueid'] = $this->_user->uniqueId;

                    /* @var $daoUser Dao_Md_User_User*/
                    $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
                    $user    = $daoUser->getUserCard($userCondition);
                    $users[] = $user;
                }
 
                $datas = array();
                $records = $daoMonth->getAttendMonthList($condition)->toArray('uniqueid');
                $totals  = $daoTotal->getAttendTotals($condition);

                foreach ($users as $user) {
                    foreach ($totals as $total) {
                        $id = strtr($total->categoryId, array('^' => ''));
                        if (!isset($datas[$user['uniqueid']][$id])) {
                            $datas[$user['uniqueid']][$id] = 0;
                        }
                        if ($total->uniqueId == $user['uniqueid']) {
                            $datas[$user['uniqueid']][$id] = $total->total;
                        }
                    }
                    foreach ($categories as $category) {
                        $id = strtr($category->categoryId, array('^' => ''));
                        if (!isset($datas[$user['uniqueid']][$id])) {
                            $datas[$user['uniqueid']][$id] = 0;
                        }
                    }
                    foreach ($records as $uniqueId => $record) {
                        foreach ($record as $key => $value) {
                            if (!isset($datas[$user['uniqueid']][$key])) {
                                $datas[$user['uniqueid']][$key] = 0;
                            }
                            if ($uniqueId == $user['uniqueid']) {
                                $datas[$user['uniqueid']][$key] = $value;
                            }
                        }
                    }

                    $datas[$user['uniqueid']]['uniqueid'] = $user['uniqueid'];
                    $datas[$user['uniqueid']]['truename'] = $user['truename'];
                    $datas[$user['uniqueid']]['deptname'] = $user['deptname'];
                }

                $data[] = $this->arrayToString($datas, $type, $categories);
                break ;
            case 'date':
                $unId       = $this->_request->getQuery('unid');
                $categoryId = $this->_request->getQuery('categoryid');

                // 读取用户信息/* @var $daoUser Dao_Md_User_User */
                $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
                $userInfo = $daoUser->getUserCard(array('uniqueid' => $unId));

                // 导出的文件名称
                $filename = $userInfo['truename'] . '(' . $year . $month . ').csv';

                $date  = array(
                    'start' => mktime(0, 0, 0, (int) $month, 1, (int) $year),
                    'end' => mktime(0, 0, 0, (int) $month + 1, 1, (int) $year)
                );
                $condition = array(
                    'uniqueid' => $unId,
                    'date'     => $date
                );
                if (!empty($categoryId)) {
                    if ($categoryId != '^late' && $categoryId != '^leave' && $categoryId != '^unwork' && $categoryId != '^uncheckin' && $categoryId != '^uncheckout') {
                        $condition['categoryid'] = $categoryId;
                    }
                    if ($categoryId == '^late') {
                        $condition['islate'] = true;
                    }
                    if ($categoryId == '^leave') {
                        $condition['isleave'] = true;
                    }
                    if ($categoryId == '^unwork') {
                        $condition['iswork'] = true;
                    }
                    if ($categoryId == '^uncheckin') {
                        $condition['uncheckin'] = true;
                    }
                    if ($categoryId == '^uncheckout') {
                        $condition['uncheckout'] = true;
                    }
                }
                $records  = $daoDate->getAttendDatePage($condition, 'date ASC')->toArray();
                $checkins = $daCheckin->getCheckins(array('uniqueid' => $unId, 'date' => $date))->toArray();
                $today    = strtotime(date('Y-m-d'));

                foreach ($records as &$record) {
                    $isCheckin          = false;
                    $isCheckout         = false;
                    $isToday            = $record['date'] == $today ? true : false;
                    $record['truename'] = $userInfo['truename'];
                    $record['deptname'] = $userInfo['deptname'];
                    foreach ($checkins as &$checkin) {
                        if ($record['date'] == $checkin['date']) {
                            // 上班签到
                            if ($checkin['type'] == 0) {
                                $isCheckin                = true;
                                $record['checkintime']    = $checkin['createtime'];
                                $record['checkinip']      = $checkin['ip'];
                                $record['checkinaddress'] = !empty($checkin['address']) ? $checkin['address'] : $this->lang['unknow'];
                            // 下班签退
                            } elseif ($checkin['type'] == 1) {
                                $isCheckout                = true;
                                $record['checkouttime']    = $checkin['createtime'];
                                $record['checkoutip']      = $checkin['ip'];
                                $record['checkoutaddress'] = !empty($checkin['address']) ? $checkin['address'] : $this->lang['unknow'];
                            }
                            unset($checkin);
                        }
                    }
                    if ($isToday && (!$isCheckout || !$isCheckin)) {
                        $record['iswork'] = 0;
                    }
                }

                // 导出数据的列名
                $columns = array(
                    Oray_Function::utf8ToGbk($this->lang['name']),
                    Oray_Function::utf8ToGbk($this->lang['dept']),
                    Oray_Function::utf8ToGbk($this->lang['date']),
                    Oray_Function::utf8ToGbk($this->lang['checkin']),
                    Oray_Function::utf8ToGbk($this->lang['checkout']),
                    Oray_Function::utf8ToGbk($this->lang['work_time']),
                    Oray_Function::utf8ToGbk($this->lang['category_late']),
                    Oray_Function::utf8ToGbk($this->lang['category_leave']),
                    Oray_Function::utf8ToGbk($this->lang['category_unwork']),
                );

                $data[] = implode(',', $columns);
                $data[] = $this->arrayToString($records, $type);
                break ;
        }

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo implode("\n", $data);
    }

    /**
     *
     * @param array $records
     * @return string
     */
    public function arrayToString(array $records, $type, $categories = null)
    {
        $data = array();

        foreach ($records as $record) {
            $rs = array();
            $rs[] = Oray_Function::utf8ToGbk($record['truename']);
            $rs[] = Oray_Function::utf8ToGbk($record['deptname']);

            if ($type == 'date') {
                $weekday = date('w', $record['date']);
                $rs[] = Oray_Function::utf8ToGbk(date('Y-m-d', $record['date']) . '(' . $this->lang['week_' . $weekday] . ')');
                $rs[] = isset($record['checkintime']) ? Oray_Function::utf8ToGbk(date('H:i', $record['checkintime']) . '(' . $record['checkinip'] . ' ' . $record['checkinaddress'] . ')') : '-';
                $rs[] = isset($record['checkouttime']) ? Oray_Function::utf8ToGbk(date('H:i', $record['checkouttime']) . '(' . $record['checkoutip'] . ' ' . $record['checkoutaddress'] . ')') : '-';
                $rs[] = Oray_Function::utf8ToGbk($this->formatTime($record['worktime']));
                $rs[] = $record['islate'] ? 1 : 0;
                $rs[] = $record['isleave'] ? 1 : 0;
                $rs[] = $record['iswork'] ? 1 : 0;

            } elseif ($type == 'month') {
                $rs[] = $record['late'] ? $record['late'] : 0;
                $rs[] = $record['leave'] ? $record['leave'] : 0;
                $rs[] = $record['unwork'] ? $record['unwork'] : 0;
                foreach ($categories as $category) {
                    $id = strtr($category->categoryId, array('^' => ''));
                    foreach ($record as $key => $val) {
                        if ($key == $id) {
                            $rs[] = $val;
                        }
                    }
                }
            }

            $data[] = implode(',', $rs);
        }

        return implode("\n", $data);
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
            return 0 . $this->lang['hour'];
        }

        //除去整天之后剩余的时间
        $time = $time%(3600*24);
        // 小时
        $hour = floor($time/3600);
        //除去整小时之后剩余的时间
        $time = $time%3600;
        // 分钟
        $minute = floor($time/60);

        //返回字符串
        return $hour . $this->lang['hour'] . $minute . $this->lang['min'];
    }
}