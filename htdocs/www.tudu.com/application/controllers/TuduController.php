<?php
/**
 * Tudu Controller
 *
 * @author Hiro
 * @version $Id: TuduController.php 2809 2013-04-07 09:57:05Z cutecube $
 */

class TuduController extends TuduX_Controller_Base
{
    /**
     *
     */
    private $_sortTypes = array('lastposttime', 'subject', 'endtime', 'from', 'to', 'starttime', 'percent');

    /**
     *
     * @var array
     */
    private $_depts;

    /**
     *
     * @var array
     */
    private $_labelVisible = array(
       '^all' => 0,
       '^i'   => 1,
       '^a'   => 2,
       '^r'   => 1,
       '^e'   => 2,
       '^v'   => 2,
       '^m'   => 2,
       '^f'   => 2,
       '^t'   => 2,
       '^n'   => 0,
       '^d'   => 0,
       '^o'   => 0,
       '^g'   => 0
    );

    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));
        $this->view->LANG = $this->lang;

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }

        $this->view->uploadsizelimit = $this->options['upload']['sizelimit'] / 1024;
    }

    /**
     * 默认（图度列表）
     */
    public function indexAction()
    {
        $page       = max(1, (int) $this->_request->getQuery('page'));
        $search     = $this->_request->getQuery('search');
        $chart      = $this->_request->getQuery('chart');
        $query      = $this->_request->getQuery();
        $pageSize   = $this->_user->option['pagesize'];
        $params     = array();
        $coreseek   = false;

        $config = $this->bootstrap->getOption('tudu');
        $labels = $this->getLabels();

        //gantt
        if($chart == 'gantt') {
            $type      = $this->_request->getQuery('type');
            $startDate = $this->_request->getQuery('sd');

            if (!$startDate) {
                $startDate = $this->_timestamp;
            }

            // 本周
            if ($type == 'week') {
                $startDate = $startDate - (int) date('w', $startDate) * 86400;
                $startDate = strtotime(date('Y-m-d', $startDate));

                $endDate = $startDate + 6 * 86400;

                $weekNum = (int) date('W', $startDate) - (int) date('W', strtotime(date('Y-m-1', $startDate))) + 1;

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
        }

        $isSearch = in_array($search, array('query', 'adv'));

        $reLoad   = false;
        $isUnread = false;

        // 检查系统标签是否存在
        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
        foreach($config['label'] as $alias => $id) {
            if (!isset($labels[$alias])) {
                $daoLabel->createLabel(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'labelalias' => $alias,
                    'labelid' => $id,
                    'isshow'  => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                    'issystem' => true,
                    'ordernum' => $this->_labelDefaultSetting[$alias]['ordernum']));
                $daoLabel->calculateLabel($this->_user->uniqueId, $id);
                $reLoad = true;
            }
        }

        if ($reLoad) {
            $this->_labels = null;
            $labels = $this->getLabels();
        }

        $params['search'] = $search;
        if ('cat' == $search || $isSearch) {
            $cat           = $this->_request->getQuery('cat');
            $params['cat'] = $cat;
        } else if (array_key_exists($search, $config['label'])) {
            $cat = $search;
        }

        if ($isSearch) {
            $coreseek = $this->_request->getQuery('coreseek');
        }

        if (isset($cat) && isset($labels[$cat])) {
            $label = $labels[$cat];
        }

        if (!isset($label)) {
            return ;
        }

        $isInbox = $label['labelalias'] == 'inbox';
        $isTodo  = $label['labelalias'] == 'todo';
        $isReview  = $label['labelalias'] == 'review';

        if ($label['issystem']) {
            $label['displayname'] = $this->lang['label_' . $label['labelalias']];
        } else {
            $label['displayname'] = $label['labelalias'];
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $condition = array();

        $condition['label']    = $label['labelid'];
        $condition['uniqueid'] = $this->_user->uniqueId;

        if($chart == 'gantt') {
            $condition['startdate'] = $startDate;
            $condition['enddate'] = $endDate;
        }

        // 是否需要重新统计图度数量
        $isReCount = false;
        if (isset($query['unread']) && $query['unread'] != '') {
            $condition['isread'] = !(boolean) $query['unread'];
            $isUnread = !$condition['isread'];

            $params['unread'] = $query['unread'];
        }

        if (!empty($query['undone'])) {
            $condition['type']   = 'task';
            $condition['status'] = 2;
            $condition['isdone'] = 0;

            $isReCount = true;

            $params['undone'] = $query['undone'];

            $this->view->undone = true;
        }

        if (!empty($query['cid'])) {
            $condition['classid'] = $query['cid'];
            $isReCount = true;
        }

        // 普通搜索、高级搜索
        if ($isSearch && !$coreseek) {
            if (!empty($query['from'])) {
                $condition['from'] = $query['from'];
                $params['from'] = $condition['from'];
            }

            if (!empty($query['to'])) {
                $condition['to'] = $query['to'];
                $params['to'] = $condition['to'];
            }

            if (!empty($query['keyword'])) {
                $condition['keyword'] = $query['keyword'];
                $params['keyword'] = $query['keyword'];
            }

            if (!empty($query['bid'])) {
                $condition['boardid'] = $query['bid'];
                $params['bid'] = $query['bid'];
            }

            if (!empty($query['classid'])) {
                $condition['classid'] = $query['classid'];
                $params['classid'] = $query['classid'];
            }

            if (isset($query['status']) && $query['status'] != '') {
                $condition['status'] = (int) $query['status'];

                $params['status'] = $condition['status'];
            }

            if (!empty($query['createtime'])
                && (!is_array($query['createtime']) || !empty($query['createtime']['start']) || !empty($query['createtime']['end']))) {
                $condition['createtime'] = $query['createtime'];

                $params['createtime'] = $condition['createtime'];
            }

            if (!empty($query['endtime'])
               && (!is_array($query['endtime']) || !empty($query['endtime']['start']) || !empty($query['endtime']['end']))) {
                $condition['endtime'] = $query['endtime'];

                $params['endtime'] = $condition['endtime'];
            }

            $isReCount = true;
        // 全文搜索
        } elseif ($isSearch && $coreseek) {
            $sortType = (int) $this->_request->getQuery('sorttype');
            $sortAsc  = (int) $this->_request->getQuery('sortasc');
            $sortMode = $sortAsc ? Oray_Search_Sphinx_Option::SORT_ATTR_ASC : Oray_Search_Sphinx_Option::SORT_ATTR_DESC;
            $sortBy   = $this->_sortTypes[$sortType];
            $config   = $this->options['sphinx'];

            switch ($sortBy) {
                case 'lastposttime':
                    $sortBy = 'last_post_time';
                    break;
                case 'endtime':
                    $sortBy = 'end_time';
                    break;
                case 'starttime':
                    $sortBy = 'start_time';
                    break;
            }

            $sphinx = new Oray_Search_Sphinx_Client($config);
            $option = new Oray_Search_Sphinx_Option(array(
                'sort'   => $sortMode,
                'sortby' => $sortBy,
                'groupsort' => '',
                'mode'   => Oray_Search_Sphinx_Option::MATCH_EXTENDED2,
                'offset' => ($page - 1) * $pageSize,
                'limit'  => $pageSize
            ));

            $params['sorttype']   = $sortType;
            $params['sortasc']    = $sortAsc;
            $params['keyword']    = $query['keyword'];
            $params['coreseek']   = 1;
            $arr = preg_split('/[\s\+\/]+/', $query['keyword']);

            foreach ($arr as $i => $word) {
                if (!$word) {
                    unset($arr[$i]);
                    continue;
                }
                $arr[$i] = $sphinx->escapeString($word);
            }

            $keyword = $words = implode('|' , $arr);
            $keyword = "@(subject,body,users) {$keyword}";
            $keyword = "@org_id {$this->_user->orgId} @uniqueid {$this->_user->uniqueId} {$keyword}";

            $result = $sphinx->query($keyword, $option, $config['indexes']['tudu'] . ' ' . $config['indexes']['tuduinc']);

            $matches = $result->getMatches();
            $total   = $result->getTotal();
            //$words   = $result->getWords();
            $tudus   = array();

            if (count($matches)) {
                $tuduIndexNum = array();
                foreach ($matches as $match) {
                    $tuduIndexNum[] = $match['id'];
                }

                $condition['tuduindexnum'] = $tuduIndexNum;
                $tudus = $daoTudu->getTuduPage($condition, '', null, null)->toArray();
            }

            $this->view->columns  = array('sender', 'subject' ,'accepter_endtime', 'reply', 'lastpost', 'star');
            $this->view->labels   = $labels;
            $this->view->tudus    = $tudus;
            $this->view->label    = $label;
            $this->view->sort     = array($sortType, $sortAsc ^ 1);
            $this->view->query    = http_build_query($params);
            $params['words']      = $words;
            $this->view->params   = $params;
            $this->view->boards   = $this->getBoards();
            $this->view->coreseek = $coreseek;
            $this->view->pageinfo = array(
                'query'       => $params,
                'currpage'    => $page,
                'pagecount'   => ($total - 1) / $pageSize + 1,
                'recordcount' => $total,
                'url'         => '/tudu/'
            );
            $this->view->registFunction('format_label', array($this, 'formatLabels'));
            return $this->render('search');
        }

        // 过滤 x 天前的图度
        $filterExpired = isset($this->_user->option['expiredfilter'])
                         && $this->_user->option['expiredfilter'] > 0
                         && in_array($label['labelalias'], array('inbox'));

        if ($filterExpired) {
            $expireDate = strtotime('today') - 86400 * $this->_user->option['expiredfilter'];

            // 公告，忽略
            /*if (in_array($label['labelalias'], array('ignore', 'notice'))) {
                $condition['createtime'] = $expireDate;
            } else {
                $condition['expiredate'] = $expireDate;
            }*/

            if ($isInbox && !$isSearch) {
                $condition['starttime']['start'] = $expireDate;
                //$condition['expiredate'] = $expireDate;
            }

            $isReCount = true;
        }

        if (($params['search'] == 'sent' || $params['search'] == 'cat') && !empty($query['unfinish'])) {
            $condition['status'] = array(0, 1);
            $condition['type']   = 'task';
            $params['unfinish']  = $condition['status'];
            $params['type']      = $condition['type'];
            $isReCount = false;

            $this->view->unfinish = true;
        }

        /*
        if (!isset($_SESSION['sort']) || !is_array($_SESSION['sort'])) {
            $_SESSION['sort'] = array(0, 0);
        }

        $sortType = $this->_request->getQuery('sorttype', $_SESSION['sort'][0]);
        $sortAsc  = $this->_request->getQuery('sortasc', $_SESSION['sort'][1]);
        $isSort   = isset($_GET['sorttype']) || isset($_GET['sortasc']);

        if ($sortType != $_SESSION['sort'][0] && isset($this->_sortTypes[$sortType])) {
            $_SESSION['sort'][0] = $sortType;
        }
        if ($sortAsc != $_SESSION['sort'][1]) {
            $_SESSION['sort'][1] = $sortAsc;
        }

        $sort = $this->_sortTypes[$_SESSION['sort'][0]] . ' ' . ($_SESSION['sort'][1] ? 'ASC' : 'DESC');
        */

        $sortType = (int) $this->_request->getQuery('sorttype');
        $sortAsc  = (int) $this->_request->getQuery('sortasc');
        $isSort   = isset($_GET['sorttype']) || isset($_GET['sortasc']);

        if ($sortType > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sorttype'] = $sortType;
        }

        if ($sortAsc > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sortasc'] = $sortAsc;
        }

        $sort = array();

        if (!$isInbox && !$isSearch && !$isTodo && !$isReview) {
            $sort[] = 'istop DESC';
        }

        $sort[] = $this->_sortTypes[$sortType] . ' ' . ($sortAsc ? 'ASC' : 'DESC');

        if ($isReCount) {
            $recordCount = $daoTudu->countTudu($condition);

            if ($filterExpired) {
                $label['totalnum']  = $recordCount;
                $label['unreadnum'] = $daoTudu->countTudu(array_merge(array('isread' => 0), $condition));
            }
        } else {
            $recordCount = $isUnread ? $label['unreadnum'] : $label['totalnum'];
        }

        // 我发起  未完成图度数
        if ($label['labelalias'] == 'sent' || !$label['issystem']) {
            $unFinishCount = $daoTudu->getTuduCount(array(
                'orgid'    => $this->_user->orgId,
                'labelid'  => $label['labelid'],
                'uniqueid' => $this->_user->uniqueId,
                'type'     => 'task',
                'status'   => array(0, 1)
            ));
            $label['unfinish'] = $unFinishCount;
            if (!empty($query['unfinish'])) {
                $recordCount = $unFinishCount;
            }
        }

        if ($isInbox || $isTodo || $isReview) {
            $page = $pageSize = $pageCount = null;
        } else {
            $pageCount = ($recordCount - 1) / $pageSize + 1;
        }

        if (null !== $pageCount && $page > $pageCount) {
            $page = $pageCount;
        }

        $tudus = $daoTudu->getTuduPage($condition, $sort, $page, $pageSize)->toArray();

        if($chart == 'gantt') {
            foreach ($tudus as $key => $tudu) {
                if ($tudu['isexpired'] && time() < $startDate) {
                    unset($tudus[$key]);
                }
            }
        }

        if ($label['labelalias'] == 'ignore') {
            $label['unreadnum'] = 0;
        }

        // 列表项
        $columns = array();
        switch ($label['labelalias']) {
            case 'notice':
            case 'discuss':
                $columns = array('sender', 'subject', 'replynum_viewnum', 'lastpost' , 'star');
                break;
            case 'meeting':
                $columns = array('sender', 'subject', 'starttime', 'replynum_viewnum', 'lastpost' , 'star');
                break;
            case 'drafts':
                $columns = array('subject', 'accepter_endtime');
                break;
            case 'sent':
                $columns = array('subject', 'accepter_endtime', 'reply', 'lastpost', 'star');
                break;
            case 'todo':
                $columns = array('sender', 'subject', 'endtime', 'reply', 'lastpost', 'star');
                break;
            default:
                $columns = array('sender', 'subject' ,'accepter_endtime', 'reply', 'lastpost', 'star');
                break;
        }

        $unUpdateOther = false;
        $unUpdateDiscuss = false;
        $unUpdateTodo = array();
        // 图度箱分组显示处理
        if (($isInbox || $isTodo || $isReview) && $tudus && !$isSearch) {
            if (!$isSort) {
                $today = strtotime('today');
                if ($isInbox) {
                    $group = array('meeting' => array(), 'notice' => array(), 'await' => array(), 'doing' => array(), 'discuss' => array(), 'waitconfirm' => array(), 'other' => array());
                } elseif ($isTodo) {
                    $group = array('unaccept' => array(), 'noendtime' => array(), 'today' => array(), '7day' => array(), 'later' => array());
                } else {
                    $group = array('await_review' => array(), 'done_review' => array());
                }
                $threeMonthAgoDate = mktime(0, 0, 0, date("m")-3, date("d"), date("Y"));
                foreach ($tudus as $tudu) {
                    if ($isInbox) {
                        // 公告
                        if ($tudu['type'] == 'notice' && !in_array('^e', $tudu['labels']) && $tudu['steptype'] != 1) {
                            $group['notice'][] = $tudu;
                            continue ;
                        }

                        // 会议
                        if ($tudu['type'] == 'meeting') {
                            $group['meeting'][] = $tudu;
                            continue ;
                        }

                        // 讨论
                        if ($tudu['type'] == 'discuss') {
                            if ($tudu['lastposttime'] <= $threeMonthAgoDate && !$isUnread) {
                                $tudu['far'] = true;
                                $unUpdateDiscuss = true;
                            }
                            $group['discuss'][] = $tudu;

                            continue ;
                        }

                        // 待办 & 进行中
                        $isAccepter = in_array($this->_user->address, $tudu['accepter'], true)
                                    || in_array($this->_user->userName, $tudu['accepter'], true);
                        $isSender   = $tudu['sender'] == $this->_user->address
                                    || $tudu['sender'] == $this->_user->userName;

                        if ($isSender || $isAccepter) {
                            if ($tudu['status'] >= Dao_Td_Tudu_Tudu::STATUS_DONE && !$tudu['isdone']) {
                                $group['waitconfirm'][] = $tudu;
                            } elseif ((($isAccepter && !$tudu['selfaccepttime'])
                                      || ($isSender && !$tudu['accepttime'])
                                      || in_array('^e', $tudu['labels']))
                                      && $tudu['type'] !== 'discuss'
                                      && !$tudu['istudugroup'])
                            {
                                $group['await'][] = $tudu;
                            } else {
                                $group['doing'][] = $tudu;
                            }

                            continue;
                        } elseif (in_array('^e', $tudu['labels'])) {
                            $group['await'][] = $tudu;
                            continue ;
                        }

                        // 其他 (被转发)
                        if ($tudu['lastposttime'] <= $threeMonthAgoDate && !$isUnread) {
                            $tudu['far'] = true;
                            $unUpdateOther = true;
                        }
                        $group['other'][] = $tudu;
                    }

                    if ($isTodo) {
                        $threeDayAgoDate = mktime(0, 0, 0, date("m"), date("d")-3, date("Y"));
                        if (!$tudu['selfaccepttime'] && $tudu['selftudustatus'] < 3) {
                            $group['unaccept'][] = $tudu;
                            continue ;
                        }

                        $k = null;
                        if (empty($tudu['endtime'])) {
                            $k = 'noendtime';
                        } elseif ($tudu['endtime'] - $today < 86400) {
                            $k = 'today';
                        } elseif ($tudu['endtime'] - $today <= 7 * 86400) {
                            $k = '7day';
                        } else {
                            $k = 'later';
                        }

                        if ($tudu['lastposttime'] <= $threeDayAgoDate && !$isUnread && $tudu['status'] >= 2) {
                            $tudu['far'] = true;
                            $unUpdateTodo[$k] = true;
                        }

                        if ($tudu['selftudustatus'] >= 2) {
                            if ($tudu['selftudustatus'] == 3) {
                                $k = 'today';
                            }
                            $group[$k]['done'][] = $tudu;
                        } else {
                            $group[$k]['undone'][] = $tudu;
                        }
                    }

                    if ($isReview) {
                        if (null !== $tudu['selfstepstatus'] && $tudu['selfstepstatus'] < 2) {
                            $group['await_review'][] = $tudu;
                        } else {
                            $group['done_review'][] = $tudu;
                        }
                    }
                }

                if ($chart == 'gantt') {
                    unset($group['notice'], $group['discuss']);
                }

                if ($isTodo) {
                    foreach ($group as $k => $g) {
                        if (array_key_exists('done', $group[$k]) || array_key_exists('undone', $group[$k])) {
                            if (!isset($group[$k]['done'])) {
                                $group[$k]['done'] = array();
                            }

                            if (!isset($group[$k]['undone'])) {
                                $group[$k]['undone'] = array();
                            }

                            $group[$k] = array_merge($group[$k]['undone'], $group[$k]['done']);
                            unset($group[$k]['undone'], $group[$k]['done']);
                        }
                    }
                }
            } else {
                $group = array();
                $sortCol = $this->_sortTypes[$sortType];
                $currYear = date('Y');
                $currWeek = date('W');
                $today    = strtotime('today');
                $tomorrow = $today + 86400;
                foreach ($tudus as $tudu) {
                    $groupkey = $tudu[$sortCol];
                    if ($sortCol == 'lastposttime' || $sortCol == 'endtime') {
                        $year = date('Y', $tudu[$sortCol]);
                        $week = date('W', $tudu[$sortCol]);
                        if ($tudu[$sortCol] >= $tomorrow) {
                            $groupkey = date('Y-m-d', $tudu[$sortCol]);
                        } elseif ($tudu[$sortCol] >= $today) {
                            $groupkey = 'today';
                        } elseif ($year == $currYear && $week == $currWeek) {
                            $groupkey = date('Y-m-d', $tudu[$sortCol]);
                        } elseif ($year == $currYear && $currWeek - $week == 1) {
                            $groupkey = 'last_week';
                        } else {
                            $groupkey = 'more_early';
                        }
                    }

                    if ($sortCol == 'from') {
                        $groupkey = isset($tudu[$sortCol][0]) ? $tudu[$sortCol][0] : 'contact_null';
                    }

                    if ($sortCol == 'to') {
                        if (!$tudu['accepter']) {
                            $groupkey = 'contact_null';
                        } else {
                            $to = reset($tudu['to']);
                            $groupkey = count($tudu['accepter']) > 1 ? 'multi' : $to[0];
                        }
                    }

                    if ($sortCol == 'percent') {
                        if ($tudu['type'] == 'task' && $groupkey === null) {
                            $groupkey = 0;
                        }
                        if (($tudu['type'] == 'notice' || $tudu['type'] == 'meeting' || $tudu['type'] == 'discuss') && $groupkey == 0) {
                            $groupkey = null;
                        }
                        if ($groupkey === null) {
                            $groupkey = '-';
                        }
                    }

                    $group[$groupkey][] = $tudu;
                }
                if ($sortAsc && $sortCol == 'percent') {
                    $null = isset($group['-']) ? $group['-'] : array();
                    unset($group['-']);
                    $group['-'] = $null;
                }
                // 已拒绝、已终止的任务放相应分组的最后
                if ($sortCol == 'percent') {
                    foreach ($group as $key => $value) {
                        $sortGroups = array();
                        $pushGroups = array();
                        foreach ($value as $tudu) {
                            if ($tudu['type'] == 'task' && ($tudu['selftudustatus'] == 3 || $tudu['selftudustatus'] == 4 || $tudu['status'] == 3 || $tudu['status'] == 4)) {
                                $pushGroups[] = $tudu;
                            } else {
                                $sortGroups[] = $tudu;
                            }
                        }
                        $group[$key] = array_merge($sortGroups, $pushGroups);
                    }
                }
            }

            $tudus = $group;
        }

        if (!$isInbox && !$isTodo && !$isReview) {
            $this->view->pageinfo = array(
                'query'       => $params,
                'currpage'    => $page,
                'pagecount'   => $pageCount,
                'recordcount' => $recordCount,
                'url'         => '/tudu/'
            );
        }

        $this->view->columns= $columns;
        $this->view->unread = $isUnread;
        $this->view->labels = $labels;
        $this->view->tudus  = $tudus;
        $this->view->label  = $label;
        $this->view->sort   = array($sortType, $sortAsc ^ 1);
        $this->view->issort = $isSort;
        $this->view->query  = http_build_query($params);
        $this->view->unupdateother = $unUpdateOther;
        $this->view->unupdatediscuss = $unUpdateDiscuss;
        $this->view->unupdatetodo = $unUpdateTodo;

        $this->view->registFunction('format_label', array($this, 'formatLabels'));

        // 显示搜索结果列表
        if ($isSearch) {
            //$daoCast = Oray_Dao::factory('Dao_Md_Cast_Cast', $this->multidb->getDb());

            //$this->view->users  = $daoCast->getCastUsers($this->_user->orgId, $this->_user->castId)->toArray();
            $boards = $this->getBoards();
            $this->view->params   = $params;
            $this->view->boards   = $boards;
            $this->view->coreseek = $coreseek;
            return $this->render('search');
        }

        //var_dump($params['chart']);exit;
        if($chart == 'gantt') {
            $this->view->registFunction('cal_gantt', array($this, 'ganttDraw'));

            $this->view->params    = array(
                'next' => $endDate + 86400,
                'prev' => $startDate - 86400
            );
            $this->view->type      = $type;
            $this->view->startdate = $startDate;
            $this->view->enddate   = $endDate;
            $this->view->headers   = $headers;

            return $this->render('ganttchart');
        }

    }

    /**
     * 多标签列表聚合页面
     *
     */
    public function convergeAction()
    {
        $search = $this->_request->getQuery('search');
        $query  = $this->_request->getQuery('unread');
        $params = array();

        $reLoad   = false;
        $isUnread = false;

        // 检查系统标签是否存在
        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
        $config   = $this->bootstrap->getOption('tudu');
        $labels   = $this->getLabels();

        foreach($config['label'] as $alias => $id) {
            if (!isset($labels[$alias])) {
                $daoLabel->createLabel(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'labelalias' => $alias,
                    'labelid' => $id,
                    'isshow'  => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                    'issystem' => true,
                    'ordernum' => $this->_labelDefaultSetting[$alias]['ordernum']));
                $daoLabel->calculateLabel($this->_user->uniqueId, $id);
                $reLoad = true;
            }
        }

        if ($reLoad) {
            $this->_labels = null;
            $labels = $this->getLabels();
        }

        // 审批
        $supports = array(
            'review' => array(
                'review' => null,
                'reviewed' => array(
                    'pagesize' => 20,
                    'page'     => 1
                )
            )
        );

        if (!array_key_exists($search, $supports)) {
            return Oray_Function::alert('不被支持的标签');
        }

        $params['search'] = $search;

        $sortType = (int) $this->_request->getQuery('sorttype', 0);
        $sortAsc  = (int) $this->_request->getQuery('sortasc', 0);
        $isSort   = isset($_GET['sorttype']) || isset($_GET['sortasc']);

        if ($sortType > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sorttype'] = $sortType;
        }

        if ($sortAsc > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sorttype'] = $sortType;
        }

        $sort = array();

        $sort[] = $this->_sortTypes[$sortType] . ' ' . ($sortAsc ? 'ASC' : 'DESC');

        $groups = $supports[$search];
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $list = array();
        $condition = array('uniqueid' => $this->_user->uniqueId);

        if (isset($query['unread'])) {
            $condition['isread'] = !(boolean) $query['unread'];
            $params['unread'] = $query['unread'];
        }

        $totalNum = 0;
        $unreadNum = 0;
        foreach ($groups as $alias => $item) {
            $label = $labels[$alias];

            $condition['label'] = $label['labelid'];

            if ($item != null) {
                $item = array_merge($condition, $item);
            } else {
                $item = $condition;
            }

            $pageSize = isset($item['pagesize']) ? $item['pagesize'] : null;
            $page     = isset($item['page']) ? $item['page'] : null;

            $tudus = $daoTudu->getTuduPage($item, $sort, $page, $pageSize);
            $tudus = $tudus->toArray();
            if ($isSort) {
                $group   = array();
                $sortCol = $this->_sortTypes[$sortType];

                foreach ($tudus as $tudu) {
                    $groupkey = $tudu[$sortCol];
                    if ($sortCol == 'percent') {
                        if ($tudu['type'] == 'notice' && $groupkey == 0) {
                            $groupkey = null;
                        }
                        if ($tudu['type'] == 'task' && $groupkey === null) {
                            $groupkey = 0;
                        }
                    }
                    $group[$groupkey][] = $tudu;
                }

                $tudus = array();
                foreach($group as $key => $item) {
                    $tudus = array_merge($tudus, $item);
                }
            }

            $list[$alias] = $tudus;

            $totalNum  += $label['totalnum'];
            $unreadNum += $label['unreadnum'];
        }

        $columns = array('sender', 'subject', 'endtime', 'reply', 'lastpost', 'star');

        $this->view->label   = array(
            'labelalias'  => 'review',
            'displayname' => $this->lang['label_review'],
            'totalnum'    => $totalNum,
            'unreadnum'   => $unreadNum
        );
        $this->view->columns = $columns;
        $this->view->tudus   = $list;
        $this->view->labels  = $labels;
        $this->view->params  = $params;
        $this->view->query   = http_build_query($params);
        $this->view->sort    = array($sortType, $sortAsc ^ 1);
        $this->view->registFunction('format_label', array($this, 'formatLabels'));
    }

    /**
     * 输出图度列表
     */
    public function listAction()
    {
        $offset   = $this->_request->getQuery('offset');
        $start    = $this->_request->getQuery('start');
        $end      = $this->_request->getQuery('end');
        $label    = $this->_request->getQuery('label');
        $sortType = (int) $this->_request->getQuery('sorttype', 0);
        $sortAsc  = (int) $this->_request->getQuery('sortasc', 0);
        $isSort   = isset($_GET['sorttype']) || isset($_GET['sortasc']);
        $pageSize = (int) $this->_request->getQuery('pagesize');
        $page     = (int) $this->_request->getQuery('page');
        $count    = 10;

        // 检查系统标签是否存在
        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
        $config   = $this->bootstrap->getOption('tudu');
        $labels   = $this->getLabels();
        $reLoad   = false;
        foreach($config['label'] as $alias => $id) {
            if (!isset($labels[$alias])) {
                $daoLabel->createLabel(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'labelalias' => $alias,
                    'labelid' => $id,
                    'isshow'  => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                    'issystem' => true,
                    'ordernum' => $this->_labelDefaultSetting[$alias]['ordernum']));
                $daoLabel->calculateLabel($this->_user->uniqueId, $id);
                $reLoad = true;
            }
        }

        if ($reLoad) {
            $this->_labels = null;
            $labels = $this->getLabels();
        }

        $headers = array(
            'reviewed' => array('sender', 'subject', 'endtime', 'reply', 'lastpost', 'star')
        );

        if ($sortType > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sorttype'] = $sortType;
        }

        if ($sortAsc > 0 && isset($this->_sortTypes[$sortType])) {
            $params['sorttype'] = $sortType;
        }

        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $sort = array();

        $sort[] = $this->_sortTypes[$sortType] . ' ' . ($sortAsc ? 'ASC' : 'DESC');

        $condition = array('uniqueid' => $this->_user->uniqueId);

        if ($label) {
            $condition['label'] = $labels[$label]['labelid'];
        }

        if ($start) {
            $condition['createtime']['start'] = $start;
        }
        if ($end) {
            $condition['createtime']['end'] = $start;
        }

        $tudus = $daoTudu->getTuduPage($condition, $sort, $page, $pageSize)->toArray();

        if ($isSort) {
            $group   = array();
            $sortCol = $this->_sortTypes[$sortType];

            foreach ($tudus as $tudu) {
                $groupkey = $tudu[$sortCol];
                if ($sortCol == 'percent') {
                    if ($tudu['type'] == 'notice' && $groupkey == 0) {
                        $groupkey = null;
                    }
                    if ($tudu['type'] == 'task' && $groupkey === null) {
                        $groupkey = 0;
                    }
                }
                $group[$groupkey][] = $tudu;
            }

            $tudus = array();
            foreach($group as $key => $item) {
                $tudus = array_merge($tudus, $item);
            }
        }

        if (isset($headers[$label])) {
            $this->view->columns = $headers[$label];
        }

        $this->view->tudus = $tudus;
        $this->view->back  = $this->_request->getQuery('back');
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
            $endTime = strtotime('today') + 86400;
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

        if ($completetime && $endTime) {
            $completetime = strtotime(date('Y-m-d 00:00:00', $completetime)) + 86400;

            if ($status == 2 && $completetime < $endTime) {
                $return['exleftlimit'] = false;
                $return['exrightlimit'] = true;
                $return['exwidth'] = round(($endTime - $completetime)/($max - $min) * 100, 2) . '%';
                $return['exleft'] = round((($completetime - $min)/($max - $min) * 100), 2) . '%';

                /*if (!$params['endtime']) {
                    $return['exleftlimit'] = false;
                    $return['exrightlimit'] = true;
                    $return['rightlimit'] = true;
                    $return['width'] = max(0, round(($endTime - $completetime)/($max - $min) * 100, 2)) . '%';
                }*/
            }

            /*if ($max < time()+86400) {
                if ($status == 2 && !$params['endtime']) {
                    $return['exleftlimit'] = false;
                    $return['exrightlimit'] = false;
                    //$return['exwidth'] = round(($completetime - $startTime)/($max - $min) * 100, 2) . '%';
                    //$return['exleft'] = round((($startTime - $min)/($max - $min) * 100), 2) . '%';
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
     * 查看私密图度，密码提交验证
     */
    public function authAction()
    {
        $tuduId    = $this->_request->getPost('tuduid');
        $pwd       = $this->_request->getPost('password');

        if (!$tuduId) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        if(!$pwd) {
            return $this->json(false, $this->lang['missing_private_pwd']);
        }

        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId, array());

        if($tudu->password != $pwd) {
            return $this->json(false, $this->lang['password_error']);
        }

        $this->session->privacy[$tuduId] = $pwd;

        return $this->json(true);

    }

    /**
     * 查看图度内容
     */
    public function viewAction()
    {
        $tuduId    = $this->_request->getQuery('tid');
        $page      = $this->_request->getQuery('page');
        $isForward = (boolean) $this->_request->getQuery('forward');
        $isReview  = (boolean) $this->_request->getQuery('review');
        $isDivide  = (boolean) $this->_request->getQuery('isdivide', $this->_request->getQuery('divide'));
        $isInvite  = (boolean) $this->_request->getQuery('invite'); //邀请
        $isApply   = (boolean) $this->_request->getQuery('apply');
        $isInvert  = (boolean) $this->_request->getQuery(
                       'invert',
                       (isset($this->_user->option['postsort']) && $this->_user->option['postsort'] == 1)
                     );
        $newwin    = (boolean) $this->_request->getQuery('newwin');
        $floor     = $this->_request->getQuery('floor');
        $pageSize  = !empty($this->_user->option['replysize']) ? $this->_user->option['replysize'] : 20;
        $query     = array();
        $votes     = null;
        $params    = $this->_request->getPost();
        $isSynchro = false; //是否逐级执行

        if (!$tuduId) {
            return $this->_redirect($this->_refererUrl);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId, array());

        if (!$tudu) {
            return $this->_redirect($this->_refererUrl);
            //Oray_Function::alert($LANG['tudu_not_exists'], $_SERVER['HTTP_REFERER']);
        }

        if ($newwin) {
            $daoOrg = $this->getMdDao('Dao_Md_Org_Org');
            $org = $daoOrg->getOrg(array('orgid' => $this->_user->orgId));
            $this->view->org   = $org->toArray();
        }

        $upload = $this->options['upload'];
        $upload['cgi']['upload'] .= '?' . session_name() . '=' . Zend_Session::getId()
                                  . '&email=' . $this->_user->address;
        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));
        //$this->view->registModifier('tudu_format_post_header', array($this, 'formatPostHeader'));
        $this->view->upload = $upload;
        $this->view->sessionid = $this->_sessionId;

        $boards = $this->getBoards(false);
        $board = $boards[$tudu->boardId];

        $isSender     = in_array($tudu->sender, array($this->_user->address, $this->_user->userName), true);
        // 当前版块版主
        $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
        // 上级分区负责人
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        // 草稿，显示编辑界面
        if ($tudu->isDraft || $isForward || $isDivide || $isReview || $isInvite || $isApply) {
            if ($tudu->isDraft && $tudu->appId == 'attend') {
                $this->_redirect('/app/attend/apply/modify?tid=' . $tudu->tuduId);
            }

            // 判断是否有转发图度的权限
            if ($isForward && !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
                Oray_Function::alert($this->lang['perm_deny_forward_tudu'], '/tudu?view=' . $tuduId . '&page=1');
            }

            // 图度组不能参与审批
            if ($isReview && $tudu->isTuduGroup) {
                Oray_Function::alert($this->lang['tudu_group_review'], '/tudu?view=' . $tuduId . '&page=1');
            }

            $access = array(
                'upload' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH)
            );
            $attachments = array();

            if ($tudu->attachNum > 0 && !$isForward) {
                /**
                 *
                 * @var Dao_Td_Attachment_File
                 */
                $daoFile = $this->getDao('Dao_Td_Attachment_File');

                $attachments = $daoFile->getFiles(array(
                    'tuduid' => $tudu->tuduId,
                    'postid' => $tudu->postId
                ));

                $attachments = $attachments->toArray();
            }

            if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
                /**
                 * @var Dao_Td_Tudu_Vote
                 */
                $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

                $votes  = $daoVote->getVotesByTuduId($tudu->tuduId);

                $votes = $votes->toArray();
                $votes = $daoVote->formatVotes($votes);
            }

            if ($tudu->cycleId) {
                $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');

                $cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId));

                $this->view->cycle = $cycle->toArray();
            }

            if ($tudu->flowId) {
                /* @var $daoFlow Dao_Td_Flow_Flow */
                $daoFlowTpl = $this->getDao('Dao_Td_Flow_Flow');

                $flows = $daoFlowTpl->getFlows(array('orgid' => $this->_user->orgId, 'boardid' => $tudu->boardId), array('isvalid' => 1));

                $this->view->flows = $flows->toArray();

                $flow = $daoFlowTpl->getFlowById($tudu->flowId, array('isvalid' => 1))->toArray();
                $steps = $this->formatStepsToHtml($flow['steps']);
                $this->view->flowhtml = $steps;
            }

            // 会议信息
            if ($tudu->type == 'meeting') {
                $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');
                $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu->tuduId));

                if (null !== $meeting) {
                    $this->view->meeting = $meeting->toArray();
                }
            }

            $type = $tudu->type;
            $tudu = $tudu->toArray();
            $tudu['attachments'] = $attachments;

            if ($isForward) {
                $tudu['to'] = array();
                $tudu['cc'] = array();
                $this->view->action = 'forward';
            }

            if ($isReview) {
                $tudu['cc'] = array();
                $this->view->action = 'review';
            }

            if (!($this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU) && ($isSender || $isModerators || $isSuperModerator))) {
                $tudu['bcc'] = array();
            }

            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            $classes  = $daoClass->getClassesByBoardId($this->_user->orgId, $tudu['boardid'], array('ordernum' => 'ASC'));

            // 板块输出
            $daoBoard = $this->getDao('Dao_Td_Board_Board');
            $boards = $this->getBoards();

            foreach ($boards as $key => $board) {
                if (!empty($board['children'])) {
                    foreach ($board['children'] as $k => $child) {
                        if ($tudu['boardid'] == $child['boardid']) {
                            $tudu['boardname'] = $child['boardname'];
                            break;
                        }
                    }
                }
            }

            // 图度组草稿、分工
            if (($tudu['isdraft'] && $tudu['istudugroup'] && !$isForward && !$isReview && !$isInvite) || $isDivide) {

                /* @var $daoFlow Dao_Td_Tudu_Flow */
                $daoFlow = $this->getDao('Dao_Td_Tudu_Flow');

                $children = $daoTudu->getGroupTudus(
                    array('parentid' => $tudu['tuduid'], 'senderid' => $this->_user->uniqueId, 'uniqueid' => $this->_user->uniqueId),
                    array('isdraft' => null)
                );
                $children = $children->toArray();

                foreach ($children as $key => $child) {
                    $to     = array();
                    $toText = array();
                    if (empty($child['flowid']) && $child['stepid']) {

                        $flow  = $daoFlow->getFlow(array('tuduid' => $child['tuduid']));
                        $steps = $flow->steps;

                        $isExceed = false;
                        foreach ($steps as $sid => $step) {
                            if (!$isExceed && ($sid == $flow->currentStepId || $flow->currentStepId == '^head')) {
                                $isExceed = true;
                            }

                            if ($isExceed) {
                                foreach ($step['section'] as $sec) {
                                    foreach ($sec as $idx => $u) {
                                        if ($idx > 0) {
                                            $to[] = '+';
                                        }
                                        $to[] = $u['username'] . ' ' . $u['truename'];
                                    }

                                    if (!empty($to)) {
                                        $to[] = '>';
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($to)) {
                        foreach ($to as $item) {
                            if ($item == '+' || $item == '>') {
                                $toText[] = $item;
                                continue;
                            }
                            $info = explode(' ', $item);
                            if (isset($info[1])) {
                                $toText[] = $info[1];
                            }
                        }
                    }
                    $children[$key]['totext'] = $toText;
                    $children[$key]['to'] = $to;
                }

                $this->view->children = $children;
                $this->view->divide   = $isDivide;
                $this->view->type     = 'group';
            }

            $tudu['users'] = $daoTudu->getUsers($tudu['tuduid']);
            $accepters = array();

            foreach ($tudu['users'] as $user) {
                if ($user['role'] == 'to' && $user['accepterinfo']) {
                    $info = explode(' ', $user['accepterinfo']);
                    if (count($info) >= 2) {
                        $accepters[] = array(
                            'email'    => str_replace(array('oray.com', 'tudu.com'), array('oray', ''), $info[0]),
                            'truename' => $info[1],
                            'percent'  => !empty($user['percent']) ? $user['percent'] : 0
                        );
                    }
                }
            }

            // 逐级执行，获取执行人 及 审批人填充
            if (($isApply || ($tudu['stepid'] && $tudu['stepid'] != '^trunk' && $tudu['stepid'] != '^end'))) {
                /* @var $daoFlow Dao_Td_Tudu_Flow */
                $daoFlow = $this->getDao('Dao_Td_Tudu_Flow');

                $flow  = $daoFlow->getFlow(array('tuduid' => $tudu['tuduid']));
                $steps = $flow->steps;

                $reviewer = array();
                $to       = array();
                $isExceed = false;
                $processindex = null;
                $toIdx    = null;
                foreach ($steps as $sid => $step) {
                    if (!$isExceed && ($sid == $flow->currentStepId || $flow->currentStepId == '^head')) {
                        $isExceed = true;
                    }

                    foreach ($step['section'] as $idx => $sec) {
                        if (!empty($to) && ($step['type'] == 0 || $step['type'] == 2)) {
                            $to[]['userinfo'] = '>';
                        }

                        if (!empty($reviewer) && $step['type'] == 1) {
                            $reviewer[]['userinfo'] = '>';
                        }

                        foreach ($sec as $i => $u) {

                            if ($step['type'] == 1 && (!isset($u['status']) || $u['status'] != 2)) {
                                if ($i > 0) {
                                    $reviewer[]['userinfo'] = '+';
                                }
                                $reviewer[] = array('email' => $u['username'], 'truename' => $u['truename'], 'userinfo' => $u['username'] . ' ' . $u['truename']);
                            }

                            if ($isExceed && $step['type'] == 0 && (!isset($u['status']) || $u['status'] != 4)) {
                                if ($i > 0) {
                                    $u[] = '+';
                                }
                                $to[] = array('email' => $u['username'], 'truename' => $u['truename'], 'userinfo' => $u['username'] . ' ' . $u['truename']);
                            }
                        }
                    }
                }

                // 移除第一个系“+”的问题
                if (!empty($reviewer) && count($reviewer) > 1) {
                    $l = count($reviewer) - 1;
                    if ($reviewer[0]['userinfo'] == '+') {
                        unset($reviewer[0]);
                    }
                    //移除最后一个系“+”的问题
                    if ($reviewer[$l]['userinfo'] == '+') {
                        unset($reviewer[$l]);
                    }
                }

                if (!$isReview) {
                    $this->view->reviewer = $reviewer;
                }
                if (!empty($to)) {
                    $tudu['to'] = $to;
                }
            }

            if ($isReview || $isForward || $isApply) {
                if ($this->_request->getPost('content')) {
                    $tudu['content'] = $this->_request->getPost('content');
                } else {
                    $tudu['content'] = '';
                }
            }

            $arrBoard = $this->getBoards(false);
            $board    = isset($arrBoard[$tudu['boardid']]) ? $arrBoard[$tudu['boardid']] : null;
            if ($isForward) {
                $tudu['to'] = array();
                $isModerators = $isSuperModerator = false;
                if ($board) {
                    $board = $arrBoard[$tudu['boardid']];

                    // 当前版块版主
                    $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
                    // 上级分区负责人
                    $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $arrBoard[$board['parentid']]['moderators']));
                }

                $access['modify'] = $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)
                                  && ($this->_user->userName == $tudu['sender'] || $isModerators || $isSuperModerator);
            }

            $this->view->registModifier('format_board_list', array($this, 'formatBoardList'));

            $this->view->classes   = $classes->toArray();
            $this->view->boards    = $boards;
            $this->view->board     = $board;
            $this->view->tudu      = $tudu;
            $this->view->accepters = $accepters;
            $this->view->access    = $access;
            $this->view->isforward = $isForward;
            $this->view->isdivide  = $isDivide;
            $this->view->isInvite  = $isInvite;
            $this->view->isreview  = $isReview;
            $this->view->isapply   = $isApply;
            $this->view->votes     = $votes;
            $this->view->back      = $this->_request->getQuery('back');
            $this->view->newwin    = $newwin;
            $this->view->issynchro = $isSynchro;

            $this->render('modify_' . $type);
            return ;
        }

        if ($tudu->appId == 'attend') {
            $this->_redirect('/app/attend/apply/view?tid=' . $tudu->tuduId);
        }

        /* @var $tudu Dao_Td_Tudu_Record_Tudu */

        // 注:Receiver跟Accepter不同，前者指相关的接收人（参与人员），后者指图度的执行人
        $isReceiver   = ($this->_user->uniqueId == $tudu->uniqueId) && count($tudu->labels);
        $isAccepter   = in_array($this->_user->address, $tudu->accepter, true)
                      || in_array($this->_user->userName, $tudu->accepter, true);
        $isSender     = in_array($tudu->sender, array($this->_user->address, $this->_user->userName), true);//$this->_user->address == $tudu->sender;

        // 是否当前版块参与人，(所属群组中包含参与群组 || 当前用户[email]在参与人列表中)
        $inGroups     = in_array($this->_user->userName, $board['groups'], true) || (boolean) sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));
        // 上级分区负责人
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        // 会议执行人有群组
        if ($tudu->type == 'meeting') {
            foreach ($tudu->accepter as $item) {
                if ($isAccepter) break;
                if (strpos($item, '^') == 0) {
                    $isAccepter = in_array($item, $this->_user->groups, true);
                }
            }
        }

        // 图度的相关权限
        $access = array(
            // 查看 - 参与人员或版主或超级版主或版块参与者（隐私类图度除外）
            'view'     => $isReceiver || $isModerators || $isSuperModerator || (!$tudu->privacy && !$board['privacy'] && $inGroups),
            // 回复 - 回复权限或参与人员
            'reply'    => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_POST) || $isReceiver,
            // 编辑 - 编辑权限&发起人或版主
            'modify'   => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU) && ($isSender || $isModerators || $isSuperModerator),
            // 删除 - 删除权限&发起人或版主
            'delete'   => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU) && ($isSender || $isModerators || $isSuperModerator),
            // 上传 - 上传权限
            'upload'   => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH),
            // 添加到组
            'merge' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_MERGE_TUDU_GROUP) && $tudu->type == 'task' && !$tudu->isDone && !$tudu->isTuduGroup && ($isSender || $isAccepter),
            // 接受 - 执行人&未接收
            'accept'   => $isAccepter && !$tudu->selfAcceptTime && !$tudu->isDone,
            // 拒绝 - 执行人&不是发起人
            'reject'   => $isAccepter && (!$isSender || $tudu->flowId) && $tudu->selfTuduStatus != Dao_Td_Tudu_Tudu::STATUS_REJECT,
            // 取消 - 发起人&未确认
            'cancel'   => $isSender && !$tudu->isDone,
            // 转发 - 转发权限&执行人
            'forward'  => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU) && $isAccepter,
            // 更新进度 - 执行人&已接受
            'progress' => $isAccepter && $tudu->selfAcceptTime,
            // 确认完成 - 发起人&未确认
            'confirm'  => $isSender && !$tudu->isDone && $tudu->status >= 2,
            // 取消确认 - 发起人&已确认
            'undone'   => $isSender && $tudu->isDone,
            // (取消)忽略 - 相关人员
            'ignore'   => $isReceiver,
            // 邀请
            'invite'   => $isAccepter && $tudu->type == 'meeting' && !$tudu->isDone,
            // 分工 执行人&已接受
            'divide'   => $isAccepter && $tudu->selfAcceptTime && !$tudu->cycleId && $tudu->type == 'task',
            // 是否接受者
            'accepter' => $isAccepter,
            // 是否发送者
            'sender'   => $isSender,
            // 是否申请审批
            'review'   => $isAccepter && $tudu->type == 'task' && !$tudu->isDone && !$tudu->isTuduGroup && $tudu->selfAcceptTime,
            // 同意申请
            'agree'    => false,
            // 不同意申请
            'disagree' => false,
            // 认领
            'claim' => $tudu->type == 'task' && $isAccepter && $tudu->acceptMode && !$tudu->acceptTime,
            // 重置投票
            'resetvote' =>  $tudu->type == 'discuss' && $isSender && !$tudu->isDone
        );

        // 临时人员
        if (!$this->_user->status == 2 && !$isSender && !$isReceiver && !$isModerators && !$isSuperModerator) {
            $access['view'] = false;
        }

        if ($tudu->isTuduGroup && $isSender) {
            $access['divide'] = false;
        }

        if ($access['merge'] && $tudu->rootId) {
            $access['merge'] = false;
        }

        if (!$access['view'] && $tudu->rootId) {
            //$this->_redirect($_SERVER['HTTP_REFERER']);
            $daoGroup   = $this->getDao('Dao_Td_Tudu_Group');
            $groupTudus = $daoGroup->getParentTudus(array('rootid' => $tudu->rootId));

            // 没有权限
            if(true === $this->_checkParentAccess($tuduId, $groupTudus)) {
                $access['view'] = true;
            }
        }

        if (!$access['view']) {
            Oray_Function::alert($this->lang['perm_deny_view_tudu'], $_SERVER['HTTP_REFERER'], '/frame/home');
        }

        //若图度含有私密任务密码则跳转输入密码页面  草稿箱的不执行此操作
        if(!$isSender && empty($this->session->privacy[$tuduId]) && $tudu->password) {
            $this->view->tudu = $tudu->toArray();
            return $this->render('privacy');
        }

        // 读取投票
        if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            /* @var $daoVote Dao_Td_Tudu_Vote */
            $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

            $votes = $daoVote->getVotesByTuduId($tudu->tuduId);

            $votes = $votes->toArray();
            $votes = $daoVote->formatVotes($votes);

            foreach ($votes as $voteId => &$vote) {
                // 统计参与人
                $vote['countvoter'] = $daoVote->countVoter($tudu->tuduId, $voteId);
                $isVoted            = $daoVote->hasVote($tudu->tuduId, $voteId, $this->_user->uniqueId);
                $expired            = !empty($vote['expiretime']) && time() > $vote['expiretime'] + 86400;
                $vote['expired']    = $expired;
                $vote['isvoted']    = $isVoted;
                $vote['enabled']    = !$isVoted && !$expired && ($isReceiver || $isAccepter);

                // 创建人可见投票参与人
                if ($vote['anonymous'] && $isSender) {
                    $vote['privacy'] = true;
                }
            }
        }

        // 读取周期任务信息
        if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_CYCLE && $tudu->cycleId) {
            /* @var $daoCycle Dao_Td_Tudu_Cycle */
            $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');
            if ($cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId))) {
                $this->view->cycleremind = $this->_formatCycleInfo($cycle->toArray(), $tudu);
                $this->view->cycle = $cycle->toArray();
            }
        }

        // 会议信息
        if ($tudu->type == 'meeting') {
            $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');
            $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu->tuduId));

            if (null !== $meeting) {
                $this->view->meeting = $meeting->toArray();
            }
        }

        // 读取分工图度
        if ($tudu->nodeType == Dao_Td_Tudu_Group::TYPE_NODE || $tudu->nodeType == Dao_Td_Tudu_Group::TYPE_ROOT) {
            $children = $daoTudu->getGroupTudus(
                array('parentid' => $tudu->tuduId, 'uniqueid' => $this->_user->uniqueId),
                null,
                'lastposttime DESC'
            )->toArray();
        }

        // 任务的相关逻辑处理
        if ($tudu->type == 'task') {

            if ($isAccepter || $isSender) {
                $access['ignore'] = false;
            }

            // 任务类权限的特殊过滤
            // 已确认完成时，禁止操作的权限
            if ($tudu->isDone) {
                $access['reply']   = false;
                $access['modify']  = false;
                $access['forward'] = false;
                $access['divide']  = false;
                $access['accept']  = false;
                $access['reject']  = false;
            }

            // 已完成（完成、取消、拒绝）时，禁止操作的权限
            if ($isAccepter && $tudu->selfTuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $access['cancel']   = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['forward']  = false;
                if (count($tudu->accepter) > 1 && $tudu->selfTuduStatus >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $access['progress'] = false;
                }

                if ($tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DONE || $tudu->isDone) {
                    $access['divide']   = false;
                }
            }

            if ($tudu->status > Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $access['progress'] = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['forward']  = false;
                $access['divide']   = false;
            }

            if ($isSender) {
                if ($tudu->status >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $access['accept']   = false;
                    $access['reject']   = false;
                    $access['cancel']   = false;
                    $access['forward']  = false;
                    $access['progress'] = false;
                } else {
                    $access['confirm']  = false;
                }
            }

            if ($isAccepter && $tudu->selfPercent == 100 && $tudu->isDone === false) {
                $access['forward'] = true;
            }

            /*
            if ($tudu->stepId && strpos($tudu->stepId, '^') !== 0) {

                $daoStep = $this->getDao('Dao_Td_Tudu_Step');

                $step = $daoStep->getCurrentStep($tuduId, $tudu->stepId, $this->_user->uniqueId);

                if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    $this->view->isreview = true;
                }

                if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    if ($step['uniqueid'] == $this->_user->uniqueId
                        && $step['status'] == 1
                        && !$tudu->isDone)
                    {
                        $access['agree']  = true;
                        $access['disagree']  = true;
                        $access['ignore'] = false;
                    }

                    $access['forward']  = false;
                    $access['divide']  = false;
                    $access['accept']   = false;
                    $access['reject']   = false;
                    $access['progress'] = false;
                    $access['review']  = false;
                }
            }*/

            //循环周期任务 已接受的不允许转发
            if ($tudu->special == 1 && null !== $tudu->acceptTime) {
                $access['forward']  = false;
            }

            // 认领进行中
            if ($isAccepter && $tudu->acceptMode && !$tudu->acceptTime) {
                $access['forward']  = false;
                $access['divide']   = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['progress'] = false;
                $access['review']   = false;
                $access['agree']    = false;
                $access['disagree'] = false;
            }

            // 已认领
            //if ($isAccepter && $tudu->acceptMode && $tudu->acceptTime) {
                //$access['progress'] = true;
            //}

            // 屏蔽新手任务的的权限
            // 工作流没有分工及转发权限
            if ($tuduId == 'newbie-' . $this->_user->uniqueId) {
                $access['forward'] = false;
                $access['divide']  = false;
            }

            if ($tudu->flowId && 0 !== strpos('^', $tudu->flowId)) {
                $access['divide'] = false;
            }

            $remind = '';

            if ($tudu->flowId) {
                /* @var $daoFlow Dao_Td_Flow_Flow */
                $daoFlow = $this->getDao('Dao_Td_Flow_Flow');
                $flow = $daoFlow->getFlowById($tudu->flowId);

                $remind .= sprintf($this->lang['remind_flow_tips_info'], $flow->subject, ($tudu->from[1] == $this->_user->userId)? $this->lang['me'] : $tudu->from[0]);
                $this->view->remind = $remind;
            }
        // 会议
        } elseif ($tudu->type == 'meeting') {

            if ($isAccepter || $isSender) {
                $access['ignore'] = false;
            }

            $access['forward'] = false;
            $access['progress'] = false;
            $access['confirm'] = false;

            if ($tudu->isDone) {
                $access['reply']  = false;
                $access['modify'] = false;
                $access['accept']  = false;
                $access['reject']  = false;
            }

            if ($tudu->selfTuduStatus > Dao_Td_Tudu_Tudu::STATUS_DONE || $tudu->isDone) {
                $access['reject'] = false;
                $access['accept'] = false;
            }

            // 显示发起人提醒信息
            if ($isAccepter && $meeting) {

                $startTime = $meeting->isAllday ? date('Y-m-d', $tudu->startTime) : date('Y-m-d H:i', $tudu->startTime);

                $remind = sprintf($this->lang['remind_meeting_left'], ($tudu->from[1] == $this->_user->userId)? $this->lang['me'] : $tudu->from[0], $startTime);

                // 未接受
                if (!$tudu->selfAcceptTime) {
                    $remind .= $this->lang['remind_meeting_accept'];
                }

                $this->view->remind = $remind;
            }

        } else {
            $access['accept'] = false;
            $access['reject'] = false;
            $access['cancel'] = false;
            $access['forward'] = false;
            $access['progress'] = false;
            $access['confirm'] = false;
            $access['undone'] = false;
            $access['review'] = false;
            $access['modify'] &= !$tudu->isDone;
            $access['reply']  &= !$tudu->isDone;
        }

        // 如果是公告发起人可进行回复
        if ($tudu->type == 'notice') {
            $access['reply']= false;
            $access['upload'] = false;
            if ($tudu->stepId && strpos($tudu->stepId, '^') !== 0) {
                /* @var $daoStep Dao_Td_Tudu_Step */
                $daoStep = $this->getDao('Dao_Td_Tudu_Step');

                $step = $daoStep->getCurrentStep($tuduId, $tudu->stepId, $this->_user->uniqueId);

                if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    $this->view->isreview = true;
                }

                if (null !== $step && $step['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    if ($step['uniqueid'] == $this->_user->uniqueId
                        && $step['status'] == 1
                        && !$tudu->isDone)
                    {
                        $access['agree']  = true;
                        $access['disagree']  = true;
                    }
                }
            }
        }

        // 已分工不能更新任务进度
        if ($tudu->isTuduGroup && ($access['progress'] || $access['forward'])) {
            if ($this->getDao('Dao_Td_Tudu_Group')->getChildrenCount($tudu->tuduId, $this->_user->uniqueId) > 0) {
                $access['progress'] = false;
                $access['forward']  = false;
            }
        }

        // 已关联用户，设置已读状态
        if ($tudu->uniqueId == $this->_user->uniqueId) {
            if (!$tudu->isRead) {
                $daoTudu->markRead($tuduId, $this->_user->uniqueId);
            }

        // 增加到关联用户，解决版块中的已读未读状态问题（!!会导致重新发送的不会投递到用户图度箱）
        } else {
            $daoTudu->addUser($tuduId, $this->_user->uniqueId, array('isread' => true));
        }

        // 增加浏览次数
        $daoTudu->hit($tuduId);

        // 公告从图度箱中移除
        if ($tudu->type == 'notice' && in_array('^i', $tudu->labels) && !in_array('^e', $tudu->labels) && $tudu->stepType != 1
            && (!$tudu->isTop && !$tudu->endTime || $tudu->endTime < strtotime('today'))
            && empty($access['agree']))
        {
            $daoTudu->deleteLabel($tuduId, $this->_user->uniqueId, '^i');
        }

        $uniqueId    = $this->_request->getQuery('unid');
        $back        = $this->_request->getQuery('back');
        $recordCount = $tudu->replyNum + 1;
        $labels      = $this->getLabels();

        $query = array(
            'tid'  => $tudu->tuduId,
            'back' => $back,
            'invert' => $isInvert ? 1 : 0
        );

        $condition = array(
            'tuduid' => $tudu->tuduId
        );

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        if ($uniqueId) {
            $condition['uniqueid'] = $uniqueId;
            $query['unid'] = $uniqueId;

            $recordCount = $daoPost->getPostCount($tudu->tuduId, $uniqueId);
        }

        $pageCount = $pageSize > 0 ? intval(($recordCount - 1) / $pageSize) + 1 : 1;

        $isLast = false;
        if ($page == 'last') {
            $page = $pageCount;
            $isLast = true;
        } else {
            $page = min($pageCount, max(1, (int) $page));
        }

        if (isset($floor)) {
            if ($floor == 0) {
                $page = 1;
            } else {
                $page = intval(($floor) / $pageSize) + 1;
            }
            $this->view->floor     = $floor;
            $this->view->jumpfloor = true;
        }
        $postSort = $isInvert ? 'createtime DESC': 'createtime ASC';

        // 获取回复内容
        $posts = $daoPost->getPostPage($condition, $postSort, $page, $pageSize)->toArray();

        // 回复者的在线状态
        $status = array();

        // 回复的相关权限
        $postAccess = array(
            'modify' => !$board['protect'] && $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_POST),
            'delete' => !$board['protect'] && $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_POST)
        );

        foreach ($posts as $key => $post) {
            // 公告过滤不可见的回复
            if ($tudu->type == 'notice' && !$access['modify'] && !$post['isfirst'] && !in_array('^v', $tudu->labels) && !in_array('^e', $tudu->labels)) {
                unset($posts[$key]);
                continue;
            }

            // 读取回复的附件信息
            if ($post['attachnum'] > 0) {
                $files = $this->getDao('Dao_Td_Attachment_File')->getFiles(array(
                    'tuduid' => $tudu->tuduId,
                    'postid' => $post['postid']
                ));

                $posts[$key]['attachment'] = $files->toArray();
            }

            // 权限
            if (!$post['isfirst'] && !$tudu->isDone) {
                $posts[$key]['access'] = array(
                    'modify' => $postAccess['modify'] && ($isModerators || $post['uniqueid'] == $this->_user->uniqueId),
                    'delete' => $postAccess['delete'] && ($isModerators || $post['uniqueid'] == $this->_user->uniqueId)
                );
            }

            if ($post['header']) {
                $posts[$key]['header'] = $this->formatPostHeader($post['header'], $post['poster']);
            }

            // 不显示自己的在线状态
            //if ($post['email'] == $this->_user->address) {
            //    $post['email'] = null;
            //}

            /*if ($post['email']) {
                if (!array_key_exists($post['email'], $status)) {
                    $status[$post['email']] = false;
                }
                $posts[$key]['imstatus'] = &$status[$post['email']];
            }*/
        }

        $unSendPost = $daoPost->getPost(array('tuduid' => $tuduId, 'uniqueid' => $this->_user->uniqueId, 'issend' => 0));
        if ($unSendPost) {
            $unSendPost = $unSendPost->toArray();
            $unSendPost['content'] = $this->formatContent($unSendPost['content']);
            // 读取回复的附件信息
            if ($unSendPost['attachnum'] > 0) {
                $files = $this->getDao('Dao_Td_Attachment_File')->getFiles(array(
                    'tuduid' => $tuduId,
                    'postid' => $unSendPost['postid']
                ));

                $unSendPost['attachments'] = $files->toArray();
            }
        } else {
            $unSendPost = array();
        }

        $unSendPost = array_merge($unSendPost, $params);

        if (isset($params['percent'])) {
            $unSendPost['percent'] = (int) $params['percent'];
        }

        if (isset($params['attach']) && is_array($params['attach'])) {
            $attachments = $daoFile->getFiles(array(
                'fileid'   => $params['attach'],
                'uniqueid' => $this->_user->uniqueId
            ), array('isattachment' => null));

            $unSendPost['attachments'] = array_unique($attachments->toArray());
            $unSendPost['attachnum']   = count($params['attachments']);
        }

        if (isset($params['savetime'])) {
            $unSendPost['savetime'] = (int) $params['savetime'];
        }
        if (isset($params['elapsedtime'])) {
            $unSendPost['elapsedtime'] = (int)$params['elapsedtime'] * 3600;
        }

        /**
         * 读取父级图度相关内容
         */
        if ($tudu->parentId) {
            $parent = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->parentId);

            if (null !== $parent) {
                $parentBoard = $boards[$parent->boardId];

                $isParentModerators = array_key_exists($this->_user->userId, $parentBoard['moderators']);
                $inParentGroups     = (boolean) sizeof(array_uintersect($this->_user->groups, $parentBoard['groups'], "strcasecmp")) || in_array($this->_user->userName, $parentBoard['groups'], true);
                $isParentSuperModerator = (!empty($parentBoard['parentid']) && array_key_exists($this->_user->userId, $boards[$parentBoard['parentid']]['moderators']));
                $isParentReceiver = ($this->_user->uniqueId == $parent->uniqueId) && count($parent->labels);

                if ($isParentReceiver || $isParentModerators || $isParentSuperModerator
                    || (!$parent->privacy && !$parentBoard['privacy'] && $inParentGroups))
                {
                    $this->view->parent = array('subject' => $parent->subject);
                }
            }
        }

        if ($tudu->prevTuduId) {
            $prevTudu = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->prevTuduId);

            //$access['accept'] &= $prevTudu->isDone;

            if (null != $prevTudu) {
                $this->view->prevtudu = array('tuduid' => $prevTudu->tuduId, 'subject' => $prevTudu->subject);
            }
        }

        if (!empty($this->session->tuduContact[$tudu->tuduId])) {
            $contactIds = $this->session->tuduContacts[$tudu->tuduId];
            $contacts   = $daoTudu->getUsers($tudu->tuduId, array('uniqueid' => $contactIds, 'isforeign' => 1));

            unset($this->session->tuduContact[$tudu->tuduId]);
            $this->view->contacts = $contacts;
        }

        $isDisagreed = false;
        $isViewFlow  = ($isSender || $access['modify']) ? true : false;

        if (($tudu->type == 'task' || $tudu->type == 'notice') && $tudu->stepNum > 0) {
            $daoFlow = $this->getDao('Dao_Td_Tudu_Flow');

            $flow  = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));
            $steps = $flow->steps;

            if (0 !== strpos($flow->currentStepId, '^')) {
                $access['confirm'] = false;
            }

            if ($flow->currentStepId == '^break') {
                $access['forward']  = false;
                $access['divide']   = false;
                $access['accept']   = false;
                $access['reject']   = false;
                $access['progress'] = false;
                $access['review']   = false;
                $access['agree']    = false;
                $access['disagree'] = false;
                $access['confirm']  = false;
            }

            //var_dump($steps);exit;
            $stepView = array();
            foreach ($steps as &$item) {

                // 处理当前步骤
                if ($item['stepid'] == $flow->currentStepId && count($item['section'])) {

                    // 审批
                    if ($item['type'] == 1) {
                        $this->view->isreview   = true;
                        $this->view->samereview = count($item['section'][$item['currentSection']]) > 1;

                        $access['forward'] = $access['divide'] = $access['accept'] = $access['reject'] = $access['progress'] = false;
                    }

                    $currentSection = $item['currentSection'];

                    if (!isset($item['section'][$currentSection])) {
                        break ;
                    }

                    $section = &$item['section'][$currentSection];

                    $to = array();
                    foreach ($section as &$user) {

                        // 处理执行步骤显示
                        if ($item['type'] == 0) {
                            $accepters = $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);

                            if (!empty($accepters)) {
                                foreach ($accepters as $accepter) {
                                    if ($accepter['uniqueid'] == $user['uniqueid']) {
                                        $user['percent'] = $accepter['percent'];
                                    }
                                }
                            } else {
                                $user['percent'] = $tudu->percent;
                            }
                        }

                        // 审批权限
                        if ($item['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['status'] == 1 && $user['uniqueid'] == $this->_user->uniqueId) {
                            $access['agree'] = $access['disagree'] = true;
                        }

                        $to[$user['username']] = array($user['truename'], null, null, $user['username']);
                    }
                    $tudu->to = $to;

                    if ($item['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                        $access['accept'] = $access['reject'] = $access['forward'] = $access['divide'] = false;
                        $this->view->isreview = true;
                    }
                }

                // 被中断
                // 即步骤中有审批不同意
                if (!empty($item['isbreak'])) {
                    if ($tudu->flowId) {
                        $access['accept'] = false;
                        if (strpos($tudu->stepId, '^') === 0) {
                            $access['reject'] = false;
                        }

                    } else {
                        $access['progress'] = $access['accept'] = $access['reject'] = false;
                    }
                }
            }

            $this->view->steps = $steps;


            /*$daoStep = $this->getDao('Dao_Td_Tudu_Step');

            $users = $daoStep->getTuduStepUsers($tudu->tuduId);
            $accepters = $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);

            // 当前是否最后一步骤，且是否执行
            $isLastExecute = false;
            if ($tudu->type == 'task') {
                $tempUsers = $users;

                do {
                    $arr = array_pop($tempUsers);

                    if (!$arr) {
                        break;
                    }

                    if ($arr['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && $arr['stepid'] != $tudu->stepId) {
                        if ($tudu->stepId == '^end') {
                            $isLastExecute = true;
                        }
                        break;
                    }
                    if ($arr['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && ($arr['stepid'] == $tudu->stepId || $tudu->stepId == '^end')) {
                        $isLastExecute = true;
                        break;
                    }
                } while (true);
            }

            $steps        = array();
            $isExceed     = false;
            $processIndex = null;
            $sameReview   = false;
            $currentUser  = array();
            $currentIndex = null;
            $tempStepId   = null;
            $tempType     = null;
            foreach ($users as &$user) {
                if ($tudu->type == 'task' && !$isLastExecute && $tempStepId != $user['stepid'] && $tempType == $user['type'] && $user['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                    $isSynchro = true;
                }
                $tempStepId = $user['stepid'];
                $tempType   = $user['type'];

                $info = explode(' ', $user['userinfo']);
                $user['email']    = $info[0];
                $user['truename'] = $info[1];

                if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['uniqueid'] == $this->_user->uniqueId) {
                    $isViewFlow = true;
                }

                if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && $user['stepid'] == $tudu->stepId && !empty($accepters)) {
                    foreach ($accepters as $accepter) {
                        if ($accepter['uniqueid'] == $user['uniqueid']) {
                            $user['percent'] = $accepter['percent'];
                        }
                    }
                }

                $processIndex = $user['processindex'];

                if (!$isExceed && $user['stepid'] == $tudu->stepId) {
                    $isExceed = true;
                }

                if ($isExceed && ($user['stepid'] != $tudu->stepId || ($user['type'] == 1 && $user['status'] < 1))) {
                    $user['future'] = true;
                }

                $steps[$user['ordernum']]['users'][]    = $user;
                $steps[$user['ordernum']]['stepid']     = $user['stepid'];
                $steps[$user['ordernum']]['type']       = $user['type'];
                $steps[$user['ordernum']]['stepstatus'] = $user['stepstatus'];
                $steps[$user['ordernum']]['future']     = !empty($user['future']);

                if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['status'] > 2) {
                    if (!$tudu->flowId) {
                        $access['progress'] = false;
                    }
                    $access['divide']   = false;
                    $isDisagreed = true;
                }

                if ($tudu->flowId && $user['stepid'] == $tudu->stepId) {
                    if (null === $currentIndex && $user['status'] < 2) {
                        $currentIndex = $user['processindex'];
                    }
                    if ($currentIndex == $user['processindex']) {
                        $currentUser[] = $user['userinfo'];
                    }

                    if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                        $steptype = 1;
                    } else {
                        $steptype = 0;
                    }
                    $this->view->steptype = $steptype;
                }
            }
            // 判断是否同时审批
            $index = null;
            foreach ($users as $item) {

                if ($item['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $item['stepid'] == $tudu->stepId) {
                    if ($index == $item['processindex']) {
                        $sameReview = true;
                    }
                    $index = $item['processindex'];
                }
            }

            if ($sameReview) {
                foreach ($users as $item) {
                    if ($tudu->flowId && $item['stepid'] == $tudu->stepId) {
                        $currentUser[] = $item['userinfo'];
                    }
                }
            }

            ksort($steps);

            if (!empty($currentUser)) {
                $tudu->to = Dao_Td_Tudu_Tudu::formatAddress(implode("\n", array_unique($currentUser)));
            }

            if ($isDisagreed && count($steps)) {
                if ($tudu->flowId) {
                    $access['accept'] = false;
                    if (strpos($tudu->stepId, '^') === 0) {
                        $access['reject'] = false;
                    }
                } else {
                    $lastStep = end($steps);

                    if ($lastStep['type'] == 0) {
                        $arrTo = array();
                        foreach ($lastStep['users'] as $u) {
                            $arrTo[$u['email']] = array($u['truename'], null, null, $u['email']);
                        }

                        $tudu->to = $arrTo;

                        if (!isset($arrTo[$this->_user->userName])) {
                            $access['accept'] = false;
                            $access['reject'] = false;
                        }
                    }

                    reset($steps);
                }
            }

            if ($sameReview) {
                $this->view->samereview = $sameReview;
            }

            if (count($steps) > 1 || $isViewFlow) {
                $this->view->steps = $steps;
            }*/
        }

        if ($tudu->type == 'task' && $isSynchro && !$isLastExecute && !$tudu->flowId) {
            $access['forward'] = false;
            $access['divide']  = false;
        }

        $cookies = $this->_request->getCookie();

        $this->view->tudu      = $tudu->toArray();
        $this->view->posts     = $posts;
        $this->view->pageinfo  = array(
            'currpage'    => $page,
            'pagecount'   => $pageCount,
            'pagesize'    => $pageSize,
            'recordcount' => $recordCount,
            'query'       => $query,
            'url'         => '/tudu/view'
        );
        $this->view->last   = $isLast;
        $this->view->cookies= serialize($cookies);
        $this->view->votes  = $votes;
        $this->view->query  = $query;
        $this->view->labels = $labels;
        $this->view->board  = $board;
        $this->view->boardnav  = $this->getBoardNav($tudu->boardId);
        $this->view->access    = $access;
        //$this->view->imstatus  = $imStatus;
        $this->view->isinvert  = $isInvert;
        $this->view->unreply  = $unSendPost;
        $this->view->moreaccepter = (boolean) (count($tudu->accepter) > 1);
        $this->view->newwin  = $newwin;
        $this->view->registFunction('format_label', array($this, 'formatLabels'));

        $this->render('view_' . $tudu->type);
    }

    /**
     * 输出外部链接列表
     */
    public function foreignAction()
    {
        $tuduId = trim($this->_request->getQuery('tid'));

        if (empty($tuduId)) {
            return $this->json(false, $this->lang['invalid_tuduid']);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null === $tudu) {
            return ;
        }

        // 权限
        $users = $daoTudu->getUsers($tuduId);

        $foreigner = array();

        foreach ($users as $user) {
            $user['info'] = explode(' ', $user['accepterinfo']);
            $user['role'] = !empty($user['role']) ? $user['role'] : Dao_Td_Tudu_Tudu::ROLE_CC;
            if ($user['isforeign']
                && in_array($user['role'], array(Dao_Td_Tudu_Tudu::ROLE_ACCEPTER, Dao_Td_Tudu_Tudu::ROLE_CC)))
            {
                $foreigner[$user['role']][] = $user;
            }
        }

        $this->view->tudu      = $tudu->toArray();
        $this->view->foreigner = $foreigner;
    }

    /**
     * 图度预览
     *
     */
    public function previewAction()
    {
        $params = $this->_getModifyParams(true);

        $tuduParams = $params['tudu'];

        $tudu     = array();
        $children = array();
        // 已经存在的图度
        if (!empty($tuduParams['tuduid'])) {
            /** @var $daoTudu Dao_Td_Tudu_Tudu */
            $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

            $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduParams['tuduid']);

            if (null === $tudu) {
                Oray_Function::alert($this->_lang['tudu_not_exists']);
            }

            $boards = $this->getBoards(false);
            $board  = $boards[$tudu->boardId];

            $isSender     = in_array($tudu->sender, array($this->_user->address, $this->_user->userName));
            $isModerators = false;
            $isSuperModerator = false;
            if (isset($boards[$tudu->boardId])) {
                $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
                $isSuperModerator = (!empty($board['parentid'])
                                     && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
            }

            if (!$isSender && !$isModerators && !$isSuperModerator) {
                Oray_Function::alert($this->lang['perm_deny_view_tudu'], $_SERVER['HTTP_REFERER']);
            }

            if ($tudu->isTuduGroup) {
                $children = $this->getDao('Dao_Td_Tudu_Tudu')->getGroupTudus(
                    array('parentid' => $tudu->tuduId, 'uniqueid' => $this->_user->uniqueId),
                    null,
                    'lastposttime DESC'
                )->toArray();

                $children = array_merge($children, $params['children']);
            }

            $tudu = array_merge($tudu->toArray(), $tuduParams);

        } else {
            $tudu = $tuduParams;

            $tudu['from'] = array(
                $this->_user->trueName,
                $this->_user->userName
            );
            $tudu['createtime'] = time();
            $tudu['status']     = 0;

            $children = $params['children'];
        }

        $vote = $params['vote'];

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));

        $this->view->vote     = $vote;
        $this->view->tudu     = $tudu;
        $this->view->children = $children;
    }

    /**
     * 预览甘特图
     */
    public function previewGanttAction()
    {
        $tuduId = $this->_request->getQuery('tid');
        $tudu = array();
        if ($tuduId) {
            /** @var $daoTudu Dao_Td_Tudu_Tudu */
            $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

            $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

            if (null === $tudu) {
                Oray_Function::alert($this->_lang['tudu_not_exists']);
                return ;
            }

            $tudu = $tudu->toArray();

            $childrens = $daoTudu->getGroupTudus(array(
                'parentid' => $tuduId,
                'uniqueid' => $this->_user->uniqueId
            ))->toArray();

            $data =  array('tudu' => $tudu, 'childrens' => $childrens);
            $time = $this->getGanttTime($data);

        } else {

            $params = $this->_getModifyParams(true);
            $tudu = $params['tudu'];
            $childrens = $params['children'];

            $tudu['starttime'] = isset($tudu['starttime']) ? strtotime($tudu['starttime']) : null;
            $tudu['endtime'] = isset($tudu['endtime']) ? strtotime($tudu['endtime']) : null;
            if ($tudu['endtime'] !== null && $tudu['endtime'] < strtotime(date('Y-m-d 00:00:00', time()))) {
                $tudu['isexpired'] = 1;
            }

            $common = array (
                'labels' => array('^all', '^r'),
                'isdraft' => 1
            );

            $tudu = array_merge($tudu, $common, array('istudugroup' => 1));

            foreach ($childrens as $key => $child) {
                $child['starttime'] = isset($child['starttime']) ? strtotime($child['starttime']) : null;
                $child['endtime'] = isset($child['endtime']) ? strtotime($child['endtime']) : null;
                if ($child['endtime'] !== null && $child['endtime'] < strtotime(date('Y-m-d 00:00:00', time()))) {
                    $child['isexpired'] = 1;
                }

                $childrens[$key] = array_merge($child, $common);
            }

            $data =  array('tudu' => $tudu, 'childrens' => $childrens);
            $time = $this->getGanttTime($data);
        }

        $minTime = $time['min'];
        $maxTime = $time['max'];
        $start   = $time['start'];
        $end     = $time['end'];

        $headers = array();
        for ($md = $minTime; $md <= $maxTime; $md += 86400) {
            $headers[] = $md;
        }

        $this->view->registFunction('cal_gantt', array($this, 'ganttDraw'));
        $this->view->tudu        = $tudu;
        $this->view->childrens   = $childrens;
        $this->view->starttime   = $minTime;
        $this->view->endtime     = $maxTime;
        $this->view->headers     = $headers;
        $this->view->start       = $start;
        $this->view->end         = $end;

        $this->render('preview-gantt');
    }

    /**
     * 编辑表单输出分工图度数据
     *
     */
    public function tuduAction()
    {
        $tuduId = $this->_request->getQuery('tid');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu   = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu      = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
        $accepters = $daoTudu->getAccepters($tuduId);

        foreach($accepters as &$accepter) {
            $info = explode(' ', $accepter['accepterinfo'], 2);
            if (count($info) == 2) {
                $accepter['email']    = $info[0];
                $accepter['truename'] = $info[1];
            }

            $accepter['percent']  = (int) $accepter['percent'];
        }

        if (null === $tudu || ($this->_user->userName != $tudu->sender
            && !in_array($this->_user->userName, $tudu->accepter)))
        {
            return $this->json(false, null);
        }

        $tudu = $tudu->toArray();
        if ($tudu['attachnum']) {
            $daoFile = $this->getDao('Dao_Td_Attachment_File');
            $tudu['attachments'] = $daoFile->getFiles(array('postid' => $tudu['postid']))->toArray();
        }

        $flowhtml = null;

        // 分工工作流程
        if ($tudu['flowid'] && $tudu['stepnum'] > 0) {
            /* @var $daoFlow Dao_Td_Flow_Flow */
            $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

            if ($tudu['isdraft']) {
                $flow = $daoFlow->getFlowById($tudu['flowid'], array('isvalid' => 1))->toArray();
                if (!empty($flow)) {
                    $flowhtml = $this->formatStepsToHtml($flow['steps']);
                }
            } else {
                /* @var $daoStep Dao_Td_Tudu_Step */
                $daoStep = $this->getDao('Dao_Td_Tudu_Step');

                $users    = $daoStep->getTuduStepUsers($tudu['tuduid']);
                $steps    = array();
                $isExceed = false;
                $isDisagreed = false;

                foreach ($users as &$user) {
                    $info = explode(' ', $user['userinfo']);
                    $user['email']    = $info[0];
                    $user['truename'] = $info[1];

                    if (!$isExceed && $user['stepid'] == $tudu['stepid']) {
                        $isExceed = true;
                    } else {
                        $isExceed = false;
                    }

                    if ($isExceed && ($user['type'] == 1 && $user['status'] < 1)) {
                        $user['future'] = true;
                    }

                    if (!$isExceed && $user['stepid'] != $tudu['stepid'] && $user['type'] == 1) {
                        $user['future'] = true;
                    }

                    if (!isset($user['future'])) {
                        $user['future'] = false;
                    }

                    $steps[$user['ordernum']]['users'][]    = $user;
                    $steps[$user['ordernum']]['stepid']     = $user['stepid'];
                    $steps[$user['ordernum']]['type']       = $user['type'];
                    $steps[$user['ordernum']]['stepstatus'] = $user['stepstatus'];

                    if ($user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $user['status'] > 2) {
                        $isDisagreed = true;
                    }
                    $isExceed = false;
                }
                ksort($steps);

                if (!$isDisagreed && count($steps)) {
                    $lastStep = end($steps);
                    reset($steps);
                }

                if (count($steps) > 0) {
                    $html   = array();
                    $pidx   = null;
                    $sidx   = null;
                    $html[] = '<div class="flowhtml">';
                    $html[] = '<span>'.$this->lang['tudu_flow'].'：</span>';
                    foreach ($steps as $step) {
                        if ($step['type'] == 1) {
                            foreach ($step['users'] as $user) {
                                if (!empty($pidx) && !empty($sidx)) {
                                    if ($pidx != $user['processindex'] || $sidx != $user['stepid']) {
                                        $html[] = '<span class="icon icon_flow_arrow"></span>';
                                    } else {
                                        $html[] = '<span class="icon icon_flow_plus"></span>';
                                    }
                                }
                                $pidx = $user['processindex'];
                                $sidx = $user['stepid'];

                                if (!$user['future'] || $user['status'] >= 2) {
                                    $html[] = '<span title="<'.$user['email'].'>'.$user['truename'].'">'.$user['truename'];

                                    if ($user['status'] == 2) {
                                        $html[] = '('.$this->lang['agree'].')';
                                    } elseif ($user['status'] == 3) {
                                        $html[] = '('.$this->lang['disagree'].')';
                                    } else {
                                        $html[] = '('.$this->lang['wait_review'].')';
                                    }
                                    $html[] = '</span>';
                                } else {
                                    $html[] = '<span title="<'.$user['email'].'>'.$user['truename'].'">'.$user['truename'].'('.$this->lang['future_review'].')</span>';
                                }
                            }
                        } else {
                            if (count($step['users']) > 1) {
                                $html[] = '<span class="icon icon_flow_arrow"></span>';
                                $title = array();
                                foreach ($step['users'] as $user) {
                                    $title[] = '<'.$user['email'].'>'.$user['truename'];
                                }
                                $html[] = '<span title="'.implode(',', $title).'">'.$this->lang['multi_accepter'];
                                $html[] = '</span>';
                            } else {
                                $html[] = '<span class="icon icon_flow_arrow"></span>';
                                $html[] = '<span title="<'.$step['users'][0]['email'].'>'.$step['users'][0]['truename'].'">'.$step['users'][0]['truename'];
                                $html[] = '</span>';
                            }
                        }
                    }

                    $html[]   = '</div>';
                    $flowhtml = implode('', $html);
                }
            }
        }

        $tudu['flowhtml'] = $flowhtml;

        $cc = array();
        $ccText = array();
        $bcc = array();
        $bccText = array();
        foreach ($tudu['cc'] as $a => $item) {
            $cc[] = $a . ' ' . $item[0];
            $ccText[] = $item[0];
        }
        if ($tudu['bcc'] !== null) {
            foreach ($tudu['bcc'] as $a => $item) {
                $bcc[] = $a . ' ' . $item[0];
                $bccText[] = $item[0];
            }
        }

        $tudu['starttime'] = isset($tudu['starttime']) ? date('Y-m-d', $tudu['starttime']) : '';
        $tudu['endtime'] = isset($tudu['endtime']) ? date('Y-m-d', $tudu['endtime']) : '';
        $tudu['totaltime'] = isset($tudu['totaltime']) ? $tudu['totaltime']/3600 : '';
        $tudu['percent'] = isset($tudu['percent']) ? $tudu['percent'] : 0;
        $tudu['content'] = $this->formatContent($tudu['content']);
        $tudu['cc'] = implode("\n", $cc);
        $tudu['cc-text'] = implode(',', $ccText);
        $tudu['bcc'] = implode("\n", $bcc);
        $tudu['bcc-text'] = implode(',', $bccText);
        $tudu['bid'] = $tudu['boardid'];
        $tudu['accepters'] = $accepters;

        // 默认允许修改进度
        $isModifyPercent = empty($tudu['flowid']) ? true : false;

        // 审批人填充
        if ($tudu && $tudu['type'] == 'task' && $tudu['stepid'] && empty($tudu['flowid'])) {
            /* @var $daoStep Dao_Td_Tudu_Step */
            $daoStep = $this->getDao('Dao_Td_Tudu_Step');

            $stepUsers = $daoStep->getTuduStepUsers($tudu['tuduid']);

            $reviewer = array();
            $to       = array();
            $toText   = array();
            $isExceed = false;
            $processindex = null;
            $toIdx    = null;
            foreach ($stepUsers as $u) {
                if (!$isExceed && ($u['stepid'] == $tudu['stepid'] || $tudu['stepid'] == '^head')) {
                    $isExceed = true;
                }

                if ($u['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $u['status'] != 2) {
                    if ($processindex == $u['processindex'] && ($u['stepid'] == $tudu['stepid'] || $tudu['stepid'] == '^head')) {
                        $reviewer[] = '+';
                    }
                    $reviewer[] = $u['userinfo'];
                }
                $processindex = $u['processindex'];

                if ($isExceed && $u['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && (int) $u['stepstatus'] != 4) {
                    if (empty($toIdx)) {
                        $to[] = $u['userinfo'];
                        $toIdx = $u['ordernum'];
                        continue;
                    }
                    if (!empty($toIdx) && $toIdx == $u['ordernum']) {
                        $to[] = '+';
                    } else {
                        $to[] = '>';
                        // 逐级不允许修改进度
                        $isModifyPercent = false;
                    }
                    $to[] = $u['userinfo'];
                    $toIdx = $u['ordernum'];
                }
            }

            // 移除第一个系“+”的问题
            if (!empty($reviewer) && count($reviewer) > 1) {
                $l = count($reviewer) - 1;
                if ($reviewer[0] == '+') {
                    unset($reviewer[0]);
                }
                //移除最后一个系“+”的问题
                if ($reviewer[$l] == '+') {
                    unset($reviewer[$l]);
                }
            }

            if (!empty($to)) {
                foreach ($to as $item) {
                    if ($item == '+' || $item == '>') {
                        $toText[] = $item;
                        continue;
                    }
                    $info = explode(' ', $item);
                    if (isset($info[1])) {
                        $toText[] = $info[1];
                    }
                }
                $tudu['to-text'] = implode(',', $toText);
                $tudu['to'] = implode("\n", $to);
            }
            $tudu['reviewer'] = implode("\n", $reviewer);
        }
        $tudu['ismodifypercent'] = $isModifyPercent;

        return $this->json(true, null, $tudu);
    }

    /**
     * 获取接收人列表
     *
     */
    public function accepterAction()
    {
        $tuduId = $this->_request->getQuery('tid');

        $accepters = $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);

        foreach($accepters as &$accepter) {
            $accepter['accepttime']  = is_numeric($accepter['accepttime']) ? (int) $accepter['accepttime'] : null;
            $accepter['elapsedtime'] = round((float) $accepter['elapsedtime'], 1);

            $info = explode(' ', $accepter['accepterinfo'], 2);
            if (count($info) == 2) {
                $accepter['email']    = $info[0];
                $accepter['truename'] = $info[1];
            }

            $accepter['percent']  = (int) $accepter['percent'];

            if ($accepter['forwardinfo']) {
                $forwardInfo = explode("\n", $accepter['forwardinfo']);
                $accepter['forwardfrom'] = $forwardInfo[0];
                $accepter['forwardtime'] = isset($forwardInfo[1]) ? (int) $forwardInfo[1] : null;
            }

            $accepter['statustext'] = !$accepter['accepttime'] && $accepter['tudustatus'] != Dao_Td_Tudu_Tudu::STATUS_REJECT
                                    ? $this->lang['status_needaccept']
                                    : $this->lang['tudu_status_' . $accepter['tudustatus']];
        }

        $this->json(true, null, $accepters);
    }

    /**
     *
     */
    public function childrenAction()
    {
        /** @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $tuduId   = $this->_request->getQuery('tid');
        $viewType = $this->_request->getQuery('view');

        $startDate = $this->_request->getQuery('sd');
        $endDate   = $this->_request->getQuery('ed');

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null !== $tudu) {

            $isReceiver = $tudu->uniqueId == $this->_user->uniqueId && count($tudu->labels);

            $sortType = (int) $this->_request->getQuery('sorttype', 0);
            $sortAsc  = (int) $this->_request->getQuery('sortasc', 0);

            $boards = $this->getBoards(false);

            /*
            if ($sortType != $_SESSION['sort'][0] && isset($this->_sortTypes[$sortType])) {
                $_SESSION['sort'][0] = $sortType;
            }
            if ($sortAsc != $_SESSION['sort'][1]) {
                $_SESSION['sort'][1] = $sortAsc;
            }
            */

            $sort = $this->_sortTypes[$sortType] . ' ' . ($sortAsc == 1 ? 'ASC' : 'DESC');

            $condition = array(
                'parentid' => $tuduId,
                'uniqueid' => $this->_user->uniqueId
            );

            if ($viewType == 'gantt') {
                $condition['starttime'] = $startDate;
                $condition['endtime']   = $endDate;
            }

            $tudus = $daoTudu->getGroupTudus($condition, null, $sort)->toArray();

            if ($viewType == 'gantt') {
                foreach ($tudus as $key => $tudu) {
                    if ($tudu['isexpired'] && time() < $startDate) {
                        unset($tudus[$key]);
                    }
                }
            }

            $boardAccess = array();
            /*if (!$isReceiver) {
                foreach ($tudus as &$item) {
                    $board = $boards[$item['boardid']];
                    if (!isset($boardAccess[$item['boardid']])) {
                        $boardAccess[$item['boardid']] = array(
                            'ismoderator' => array_key_exists($this->_user->userId, $board['moderators'])
                                          || (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])),
                            'ingroup' => (array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"))
                        );
                    }

                    $isReceiver = ($this->_user->uniqueId == $item['uniqueid']) && count($item['labels']);
                    if (!$isReceiver && !$boardAccess[$item['boardid']]['ismoderator']
                        && ($item['privacy'] || $board['privacy'] || !$boardAccess[$item['boardid']['ingroup']]))
                    {
                        $item['deny'] = true;
                    }
                }
            }*/

            $this->view->sort    = array($sortType, $sortAsc ^ 1);
            $this->view->currUrl = urlencode($this->_request->getQuery('currUrl'));
            $this->view->tudus   = $tudus;
            $this->view->tuduid  = $tuduId;

            if ($viewType == 'gantt') {
                $this->view->registFunction('cal_gantt', array($this, 'ganttDraw'));

                $headers = array();
                for ($md = $startDate; $md <= $endDate; $md += 86400) {
                    $headers[] = $md;
                }

                $this->view->headers   = $headers;
                $this->view->startdate = $startDate;
                $this->view->enddate   = $endDate;
                $this->view->type      = $this->_request->getQuery('type');

                if ($this->_request->getQuery('tpl') == 'previewchildgantt') {
                    return $this->render('child_gantt');
                }
                return $this->render('children_gantt');
            }
        }
    }

    /**
     * 编辑表单
     */
    public function modifyAction()
    {
        $tuduId = $this->_request->getQuery('tid');
        $type   = $this->_request->getQuery('type', 'task');
        $to     = $this->_request->getQuery('to', $this->_request->getQuery('email')); // email IM跳转
        $boardId= trim($this->_request->getQuery('bid'));
        $flowId = $this->_request->getQuery('flowid');
        $autosave = $this->_request->getPost('autosave');
        $newwin = (boolean) $this->_request->getQuery('newwin');
        $attachments = null;
        $isSynchro = false; //是否逐级执行

        $tudu  = array();
        $votes = array();

        $access = $this->_user->getAccess();

        // 权限
        $perm = array(
            'task'    => $access->assertEquals(Tudu_Access::PERM_CREATE_TUDU, true),
            'discuss' => $access->assertEquals(Tudu_Access::PERM_CREATE_DISCUSS, true),
            'notice'  => $access->assertEquals(Tudu_Access::PERM_CREATE_NOTICE, true),
            'meeting' => $access->assertEquals(Tudu_Access::PERM_CREATE_MEETING, true),
            'board'   => $access->assertEquals(Tudu_Access::PERM_CREATE_BOARD, true)
        );

        $perm['group'] = $perm['task'];

        if (!in_array($type, array('task', 'notice', 'discuss', 'group', 'meeting'))) {
            foreach ($perm as $key => $value) {
                if ($value) {
                    $type = $key;
                    break;
                }
            }
        }

        if ($type == 'board') {
            return $this->_redirect('/board/modify');
        }

        if ($tuduId) {

            if (!$access->assertEquals(Tudu_Access::PERM_UPDATE_TUDU, true)) {
                Oray_Function::alert($this->lang['perm_deny_update_tudu'], '/tudu/?search=inbox');
            }

            $tudu = $this->getDao('Dao_Td_Tudu_Tudu')->getTuduById($this->_user->uniqueId, $tuduId);

            if (null === $tudu) {
                Oray_Function::alert($this->_lang['tudu_not_exists']);
            }

            $perm = array(
                'discuss' => false,
                'group'   => false,
                'notice'  => false,
                'task'    => false,
                'board'   => false
            );

            $boards  = $this->getBoards(false);

            $board        = $boards[$tudu->boardId];
            $isSender     = in_array($tudu->sender, array($this->_user->address, $this->_user->userName));
            $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
            // 上级分区负责人
            $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
            $isOwner          = $board['ownerid'] == $this->_user->userId;

            if (!$isSender && !$isModerators && !$isSuperModerator) {
                Oray_Function::alert($this->lang['perm_deny_update_tudu'], '/tudu/?search=inbox');
            }

            $daoClasses = $this->getDao('Dao_Td_Tudu_Class');
            $classes = $daoClasses->getClassesByBoardId($this->_user->orgId, $tudu->boardId, array('ordernum' => 'ASC'))->toArray();

            if ($isModerators || $isSuperModerator || $isOwner) {
                $classes[] = array(
                    'classname' => $this->lang['create_board_class'],
                    'classid' => '^add-class'
                );
            }

            $this->view->classes = $classes;

            if ($tudu->attachNum > 0) {
                /**
                 *
                 * @var Dao_Td_Attachment_File
                 */
                $daoFile = $this->getDao('Dao_Td_Attachment_File');

                $attachments = $daoFile->getFiles(array(
                    'tuduid' => $tudu->tuduId,
                    'postid' => $tudu->postId
                ));

                $attachments = $attachments->toArray();
            }

            if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
                /* @var $daoVote Dao_Td_Tudu_Vote */
                $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

                $votes  = $daoVote->getVotesByTuduId($tudu->tuduId);

                $votes = $votes->toArray();
                $votes = $daoVote->formatVotes($votes);

            }

            if ($tudu->cycleId) {
                $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');

                $cycle = $daoCycle->getCycle(array('cycleid' => $tudu->cycleId));

                $this->view->cycle = $cycle->toArray();
            }

            /* @var $daoFlow Dao_Td_Tudu_Flow */
            $daoFlow = $this->getDao('Dao_Td_Tudu_Flow');

            if ($tudu->flowId) {
                /* @var $daoFlowTpl Dao_Td_Flow_Flow */
                $daoFlowTpl = $this->getDao('Dao_Td_Flow_Flow');

                $flow = $daoFlowTpl->getFlow(array('flowid' => $tudu->flowId))->toArray();

                $flows[] = $flow;
                $this->view->flows = $flows;

                $isDisagreed = false;
                if ($tudu->type == 'task' && $tudu->stepNum > 0) {

                    $flow = $daoFlow->getFlow(array('tuduid' => $tudu->tuduId));

                    $steps = array();
                    $isExceed = false;
                    $counter  = 0;
                    foreach ($flow->steps as $sid => $st) {
                        if (!$isExceed && $sid == $flow->currentStepId) {
                            $isExceed = true;
                        }

                        foreach ($st['section'] as $idx => $sec) {
                            foreach ($sec as $i => $u) {
                                $user = array(
                                    'email'      => $u['username'],
                                    'truename'   => $u['truename'],
                                    'stepid'     => $sid,
                                    'type'       => $st['type'],
                                    'stepstatus' => isset($u['status']) ? (int) $u['status'] : 0,
                                    'processindex' => $counter
                                );

                                if ($isExceed && ($sid != $flow->currentStepId || ($st['type'] == 1 && (!isset($user['status']) || $user['status'] < 1)))) {
                                    $user['future'] = true;
                                }

                                $steps[$counter]['users'][] = $user;
                                $steps[$counter]['type'] = $st['type'];
                            }

                            $counter ++;
                        }
                    }

                    ksort($steps);

                    if (!$isDisagreed && count($steps)) {
                        $lastStep = end($steps);
                        reset($steps);
                    }

                    if (count($steps) > 1) {
                        $this->view->steps = $steps;
                    }
                }
            }

            if ($tudu->type == 'meeting') {
                $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');

                $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu->tuduId));

                if (null !== $meeting) {
                    $this->view->meeting = $meeting->toArray();
                }
            }

            if ($tudu->isTuduGroup && (!$tudu->to || in_array($this->_user->userName, $tudu->accepter))) {
                //$type = 'group';
                $type = 'task';
                $children = $this->getDao('Dao_Td_Tudu_Tudu')->getGroupTudus(
                    array('parentid' => $tudu->tuduId, 'senderid' => $this->_user->uniqueId, 'uniqueid' => $this->_user->uniqueId),
                    null,
                    'lastposttime DESC'
                )->toArray();

                /* @var $daoFlow Dao_Td_Tudu_Flow */
                $daoFlow = $this->getDao('Dao_Td_Tudu_Flow');

                foreach ($children as $key => $child) {
                    $to     = array();
                    $toText = array();

                    if (empty($child['flowid']) && $child['stepid']) {

                        $flow = $daoFlow->getFlow(array('tuduid' => $child['tuduid']));

                        if (!$flow) {
                            continue ;
                        }

                        $isExceed = false;
                        $toIdx    = null;
                        foreach ($flow->steps as $sid => $step) {
                            if (!$isExceed && ($sid == $flow->currentStepId || $flow->currentStepId = '^head')) {
                                $isExceed = true;
                            }

                            if ($isExceed && $step['type'] == 0) {
                                foreach ($step['section'] as $sec) {
                                    if (!empty($to)) {
                                        $to[] = '>';
                                    }

                                    foreach ($sec as $idx => $u) {
                                        if (!isset($u['status']) || (int) $u['status'] != 4) {
                                            if ($idx > 0) {
                                                $to[] = '+';
                                            }

                                            $to[] = $u['username'] . ' ' . $u['truename'];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!empty($to)) {
                        foreach ($to as $item) {
                            if ($item == '+' || $item == '>') {
                                $toText[] = $item;
                                continue;
                            }
                            $info = explode(' ', $item);
                            if (isset($info[1])) {
                                $toText[] = $info[1];
                            }
                        }
                    }
                    $children[$key]['totext'] = $toText;
                    $children[$key]['to'] = $to;

                    if ($child['status'] == 3) {

                        $claimAccepters = array();
                        foreach ($flow->steps as $step) {
                            if ($step['type'] == 2) {
                                foreach ($step['section'] as $sec) {
                                    foreach ($sec as $u) {
                                        $claimAccepters[] = array(
                                            'email'    => $u['username'],
                                            'truename' => $u['truename'],
                                            'userinfo' => $u['username'] . ' ' . $u['truename']
                                        );
                                    }
                                }
                            }
                        }

                        if (!empty($claimAccepters)) {
                            $this->view->claimAccepters = $claimAccepters;
                        }

                    }
                }

                $this->view->children = $children;
            }

            if ($tudu->status == 3 && !$tudu->isTuduGroup && $tudu->stepId) {
                $flow = $daoFlow->getFlow(array('tuduid' => $tuduId));

                if ($flow && isset($flow->steps[$tudu->stepId])) {
                    //$claimAccepters = $daoFlow->getUsers($tuduId, $claim->stepId);
                    $step = $flow->steps[$tudu->stepId];

                    if ($step['type'] == 2) {
                        $claimAccepters = $step['section'][0];

                        foreach($claimAccepters as &$claimAccepter) {
                            $info = explode(' ', $claimAccepter['userinfo'], 2);
                            if (count($info) == 2) {
                                $claimAccepter['email']    = $info[0];
                                $claimAccepter['truename'] = $info[1];
                            }
                        }
                        $this->view->claimAccepters = $claimAccepters;
                    }
                }
            }

            $tudu = $tudu->toArray();
            $tudu['attachments'] = $attachments;

            $type = $tudu['type'];

            if ($type == 'task' && !empty($tudu['stepid']) && $tudu['stepid'] == '^end') {

                $to = array();
                foreach ($tudu['to'] as $item) {
                    if (!empty($to)) {
                        $to[]['userinfo'] = '+';
                    }

                    $to[] = array(
                        'email' => $item[3],
                        'truename' => $item[0],
                        'userinfo' => $item[3] . ' ' . $item[0]
                    );
                }

                $tudu['to'] = $to;

            // 逐级执行，执行人填充  审批人填充
            } elseif ($tudu && empty($tudu['flowid']) && ($type == 'task' || $type == 'notice') && !empty($tudu['stepid'])) {

                /* @var $daoFlow Dao_Td_Tudu_Flow */
                $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

                $flow = $daoFlow->getFlow(array('tuduid' => $tudu['tuduid']));

                $steps = $flow->steps;
                
                $currStepId = null;
                if (false === strpos($flow->currentStepId, '^')) {
                	$currStepId = $flow->currentStepId;
                } else {
                	foreach ($flow->steps as $st) {
                        foreach ($st['section'] as $sec) {
                        	foreach ($sec as $u) {
                        		if (!isset($u['status']) || $u['status'] <= 1 || $u['status'] == 3) {
                        			$currStepId = $st['stepid'];
                        			break 3;
                        		}
                        	}
                        }
                	}

                	$currStepId = null === $currStepId ? '^head': $currStepId;
                }
                
                //$currStepId = false === strpos($flow->currentStepId, '^') ? $flow->currentStepId : 
                
                $step  = isset($flow->steps[$currStepId]) ? $flow->steps[$currStepId] : null;

                foreach ($steps as $st) {
                    if ($st['type'] == 1 && $st['section']) {
                        foreach ($st['section'] as $sec) {
                            foreach ($sec as $u) {
                                if ($u['status'] > 2) {
                                    $step = $st;
                                    break 3;
                                }
                            }
                        }
                    }
                }

                if (!$step) {
                    $reviewer = array();
                    $to       = array();
                    $step     = isset($steps[$flow->currentStepId]) ? $steps[$flow->currentStepId] : null;

                    if (null === $step && $flow->currentStepId == '^head') {
                        $step = reset($steps);
                    }
                }

                if (null !== $step) {
                    if ($step['type'] == 1) {
                        foreach ($step['section'] as $idx => $section) {
                            if (isset($step['currentSection']) && $idx < $step['currentSection']) {
                                continue ;
                            }

                            if (!empty($reviewer)) {
                                $reviewer[] = array('userinfo' => '>');
                            }

                            foreach ($section as $i => $user) {
                                if (isset($user['status']) && $user['status'] == 2) {
                                    continue ;
                                }

                                if ($idx > 0 && $i > 0) {
                                    $reviewer[] = array('userinfo' => '+');
                                }

                                $reviewer[] = array('email' => $user['username'], 'truename' => $user['truename'], 'userinfo' => $user['username'] . ' ' . $user['truename']);
                            }

                        }

                        $executeStep = isset($steps[$step['next']]) ? $steps[$step['next']] : null;
                    } else {
                        $executeStep = isset($steps[$currStepId]) ? $steps[$currStepId] : null;
                    }

                    if ($executeStep && $executeStep['type'] != 1) {
                        foreach ($executeStep['section'] as $idx => $section) {
                            if (isset($executeStep['currentSection']) && $idx < $executeStep['currentSection']) {
                                continue ;
                            }

                            if (!empty($to)) {
                                $to[] = array('userinfo' => '>');
                            }

                            foreach ($section as $i => $user) {
                                if ($i > 0) {
                                    $to[] = array('userinfo' => '+');
                                }

                                $to[] = array('email' => $user['username'], 'truename' => $user['truename'], 'userinfo' => $user['username'] . ' ' . $user['truename']);
                            }
                        }
                    }
                }

                if (!empty($reviewer)) {
                    $this->view->reviewer = $reviewer;
                }

                if (!empty($to)) {
                    $tudu['to'] = $to;
                }

            }

        } else {
            if (!$perm[$type]) {
                Oray_Function::alert($this->lang['perm_deny_create_' . $type], '/tudu/?search=inbox');
            }

            if ($this->_request->getPost()) {
                $params = $this->_getModifyParams();
                $tudu = $params['tudu'];
                if ($type == 'meeting') {
                    unset($tudu['starttime']);
                    unset($tudu['endtime']);
                }
                if (!empty($tudu['type']) && $tudu['type'] == 'meeting') {
                    unset($tudu['starttime']);
                    unset($tudu['endtime']);
                }
                unset($tudu['type']);
            }

            $boards  = $this->getBoards(false);

            if (!empty($boardId) && isset($boards[$boardId])) {
                $board        = $boards[$boardId];

                $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
                $isOwner      = $board['ownerid'] == $this->_user->userId;
                // 上级分区负责人
                $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

                if ($boardId && (!isset($tudu['tuduid']) || empty($tudu['boardid']))) {
                    $tudu['boardid'] = $boardId;
                }

                $daoClasses = $this->getDao('Dao_Td_Tudu_Class');
                $classes = $daoClasses->getClassesByBoardId($this->_user->orgId, $tudu['boardid'], array('ordernum' => 'ASC'))->toArray();

                // XXX
                if ($isModerators || $isSuperModerator || $isOwner) {
                    $classes[] = array(
                        'classname' => $this->lang['create_board_class'],
                        'classid' => '^add-class'
                    );
                }

                $this->view->classes = $classes;
            }

            if ($flowId) {
                $this->view->flowid = $flowId;
            }

            if (isset($tudu['to']) && is_array($tudu['to'])) {
                foreach ($tudu['to'] as $idx => $item) {
                    $tudu['to'][$idx]['userinfo'] = $item[3] . ' ' . $item[0];
                }
            }
        }

        if ($to && !$tudu) {
            if (Oray_Function::isEmail($to)) {
                $to = explode('@', $to);
                $userId = $to[0];echo $userId;
                $daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

                $to = $daoUser->getUsers(array('orgid' => $this->_user->orgId, 'userid' => $userId), null, null, 1)->toArray();

                if (!empty($to[0])) {
                    $email = $to[0]['userid'] . '@' . $to[0]['domainname'];
                    $tudu['to'] = array($email => array(
                        0 => $to[0]['truename'],
                        1 => $to[0]['userid'],
                        2 => $to[0]['domainname']
                    ));
                    $tudu['accepter'] = array($email);
                }
            } else {
                // IM email特殊处理
                $to = explode("\n", $to);
                foreach ($to as $item) {
                    $tudu['to'][]['userinfo'] = $item;
                }
                /*if (strpos($to, ' ') === false) {
                    $to = ' ' . $to;
                }
                $tudu['to'] = Dao_Td_Tudu_Tudu::formatAddress($to);*/
            }
        }

        $perm['upload'] = $access->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH);

        $uploadOpt = $this->bootstrap->getOption('upload');

        $boards  = $this->getBoards();
        $cookies = $this->_request->getCookie();

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));

        $board = null;
        if (!$tudu) {
            foreach ($boards as $key => $zone) {
                if (!empty($zone['children'])) {
                    foreach ($zone['children'] as $k => $child) {
                        if ($child['status'] == 2) {
                            unset($boards[$key]['children'][$k]);
                            $boards[$key]['children'] = array_values ($boards[$key]['children']);
                        }
                    }
                }

                if ($zone['type'] == 'zone' && empty($zone['children'])) {
                    unset($boards[$key]);
                }
            }
            //var_dump($boards['^zone']);exit();
        } else {
            foreach ($boards as $key => $zone) {
                if (!empty($zone['children']) && !empty($tudu['boardid'])) {
                    foreach ($zone['children'] as $k => $child) {
                        if ($tudu['boardid'] == $child['boardid']) {
                            $tudu['boardname'] = $child['boardname'];
                            $board = $child;
                            // 执行人拒绝任务，在不可修改板块也可修改图度
                            if ($board['protect']
                              && (isset($tudu['status']) && $tudu['status'] == Dao_Td_Tudu_Tudu::STATUS_REJECT
                                 && in_array($tudu['sender'], array($this->_user->address, $this->_user->userName)))
                              || empty($tudu['tuduid']) || !empty($tudu['isdraft']))
                            {
                                $board['protect'] = 0;
                            }
                            break;
                        }
                    }
                }
            }
        }

        // 处理快捷板块
        //$attentions = array('children' => array());
        /* @var $daoBoard Dao_Td_Board_Board */
        /*$daoBoard = $this->getDao('Dao_Td_Board_Board');
        $attenBoards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);
        if ($attenBoards !== null) {
            $attentions['children'] = $attenBoards;
        }

        if (!empty($attentions['children'])) {
            $attentions['type'] = 'zone';
            $attentions['boardname'] = $this->lang['my_attention_board'];
            $boards = array_merge(array($attentions), $boards);
        }*/

        $upload = $this->options['upload'];
        $upload['cgi']['upload'] .= '?' . session_name() . '=' . $this->_sessionId
                                  . '&email=' . $this->_user->address;

        if (!empty($tudu['tuduid'])) {
            $tudu['users'] = $this->getDao('Dao_Td_Tudu_Tudu')->getUsers($tudu['tuduid']);
        }

        if ($autosave) {
            $tudu['autosave'] = $autosave;
        }

        $accepters = $this->getDao('Dao_Td_Tudu_Tudu')->getAccepters($tuduId);

        foreach($accepters as &$accepter) {

            $info = explode(' ', $accepter['accepterinfo'], 2);
            if (count($info) == 2) {
                $accepter['email']    = str_replace(array('oray.com', 'tudu.com'), array('oray', ''), $info[0]);
                $accepter['truename'] = $info[1];
            }

            $accepter['percent']  = (int) $accepter['percent'];
        }

        if ($tudu && $type == 'task') {
            $canNotDelAccepter = array();
            foreach($accepters as &$accepter) {
                $childrenCount = $this->getDao('Dao_Td_Tudu_Group')->getChildrenCount(
                    $tudu['tuduid'], $accepter['uniqueid']
                );

                if ($childrenCount > 0) {
                    $canNotDelAccepter[] = array(
                        'uniqueid' => $accepter['uniqueid'],
                        'truename' => $accepter['truename'],
                        'username' => $accepter['email'],
                        'accepterinfo' => $accepter['accepterinfo']
                    );
                }
            }
            $this->view->cannotdelaccepter = $canNotDelAccepter;
        }

        if ($newwin) {
            $daoOrg = $this->getMdDao('Dao_Md_Org_Org');
            $org = $daoOrg->getOrg(array('orgid' => $this->_user->orgId));
            $this->view->org   = $org->toArray();
        }

        $this->view->registModifier('format_board_list', array($this, 'formatBoardList'));

        $this->view->board  = $board;
        $this->view->back   = $this->_request->getQuery('back');
        $this->view->reopen = $this->_request->getQuery('reopen');
        $this->view->upload = $upload;
        $this->view->cookies= serialize($cookies);
        $this->view->boards = $boards;
        $this->view->tudu   = $tudu;
        $this->view->accepters   = $accepters;
        $this->view->access = $perm;
        $this->view->votes  = $votes;
        $this->view->ndfile = !empty($tudu['nd-attach']) ? $tudu['nd-attach'] : null;
        $this->view->newwin = $newwin;
        $this->view->sessionid = $this->_sessionId;
        $this->view->issynchro = $isSynchro;

        $this->render('modify_' . $type);
    }

    /**
     * 显示打印页面
     */
    public function printAction()
    {
        $tuduId = $this->_request->getQuery('tid');
        $vote   = null;

        if (!$tuduId) {
            return $this->_redirect($this->_refererUrl);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId, array());

        if (null === $tudu) {
            Oray_Function::alert($this->lang['tudu_not_exists']);
        }

        $boards = $this->getBoards(false);
        $board = $boards[$tudu->boardId];

        // 注:Receiver跟Accepter不同，前者指相关的接收人（参与人员），后者指图度的执行人
        $isReceiver   = ($this->_user->uniqueId == $tudu->uniqueId) && count($tudu->labels);
        $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
        $inGroups     = in_array($this->_user->userName, $board['groups'], true) || (boolean) sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        if (!$isReceiver && !$isModerators && !$isSuperModerator && ($tudu->privacy || $board['privacy'] || $inGroups)) {
            //$this->_redirect($_SERVER['HTTP_REFERER']);
            Oray_Function::alert($this->lang['perm_deny_view_tudu'], $_SERVER['HTTP_REFERER']);
        }

        // 读取投票
        if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            /**
             * @var Dao_Td_Tudu_Vote
             */
            $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

//            $vote = $daoVote->getVoteByTuduId($tudu->tuduId);
//
//            $vote->getOptions();
//
//            $isVoted = $vote->isVoted($this->_user->uniqueId);
//
//            $vote = $vote->toArray();
//            $vote['expired'] = $vote['expiretime'] && time() > $vote['expiretime'];
//            $vote['isvoted'] = $isVoted;
//            $vote['enabled'] = !$vote['isvoted'] && !$vote['expired'];

              $vote = $daoVote->getVoteByTuduId($tudu->tuduId);

              $vote->getOptions();
              $vote->countVoter();

              $isVoted = $vote->isVoted($this->_user->uniqueId);

              $vote = $vote->toArray();
              $vote['expired'] = $vote['expiretime'] && time() > $vote['expiretime'];
              $vote['isvoted'] = $isVoted;

        }

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));

        /* @var $daoTudu Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');
        // 获取回复内容
        $posts = $daoPost->getPosts(array('tuduid' => $tuduId), null, 'createtime ASC')->toArray();

        $this->view->boardnav = $this->getBoardNav($tudu->boardId);

        $this->view->tudu  = $tudu->toArray();
        $this->view->posts = $posts;
        $this->view->vote  = $vote;
    }

    /**
     * 编辑回复
     */
    public function postAction()
    {
        $access = array(
            'upload' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPLOAD_ATTACH),
            'modify' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_POST)
        );

        $tuduId = $this->_request->getQuery('tid');
        $postId = $this->_request->getQuery('pid');
        $post   = array();

        $params  = $this->_request->getPost();

        /*if (!empty($content)) {
            $post['content'] = $content;
        }*/

        if (!$tuduId) {
            return $this->_redirect($_SERVER['HTTP_REFERER']);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');

        /* @var $daoFile Dao_Td_Attachment_File */
        $daoFile = $this->getDao('Dao_Td_Attachment_File');

        if (null === $tudu || !$tudu->uniqueId) {
            return $this->_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($postId) {
            // 编辑权限检查
            if (!$access['modify']) {
                Oray_Function::alert($this->lang['perm_deny_update_post']);
            }

            $post = $daoPost->getPost(array('tuduid' => $tuduId, 'postid' => $postId));
            if (!$post) {
                $this->_redirect('/tudu/?search=inbox');
            }

            // 不是回复者时，读取版主的权限
            if ($post->uniqueId !== $this->_user->uniqueId) {
                $boards = $this->getBoards(false);
                $board = $boards[$post->boardId];
                $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
                if (!$isModerators) {
                    Oray_Function::alert($this->lang['perm_deny_update_post']);
                }
            }

            if ($post->attachNum > 0) {

                $attachments = $daoFile->getFiles(array(
                    'tuduid' => $post->tuduId,
                    'postid' => $post->postId
                ));

                $post->attachments = $attachments->toArray();
            }

            $access['progress'] = $post->isLog
                                && in_array($this->_user->userName, $tudu->accepter)
                                && $tudu->selfTuduStatus < Dao_Td_Tudu_Tudu::STATUS_DONE;

            $post = $post->toArray();
        } else {

            if (count($tudu->accepter) > 1) {
                $access['progress'] = in_array($this->_user->userName, $tudu->accepter)
                                    && $tudu->selfAcceptTime
                                    && $tudu->selfTuduStatus < Dao_Td_Tudu_Tudu::STATUS_DONE;
            } else {
                $access['progress'] = in_array($this->_user->userName, $tudu->accepter)
                                    && $tudu->selfAcceptTime
                                    && $tudu->status < Dao_Td_Tudu_Tudu::STATUS_DONE;
            }

            $fromPostId = isset($params['fpid']) ? $params['fpid'] : null;
            $fromPost   = null;

            if ($fromPostId) {
                $fromPost = $daoPost->getPost(array('tuduid' => $tuduId, 'uniqueid' => $this->_user->uniqueId, 'postid' => $fromPostId));
                if ($fromPost) {
                    $post = $fromPost->toArray();
                }
            }

            $post = array_merge($post, $params);

            if (isset($post['percent'])) {
                $post['percent'] = (int) $post['percent'];
            }

            if (isset($params['attach']) && is_array($params['attach'])) {
                $attachments = $daoFile->getFiles(array(
                    'fileid'   => $params['attach'],
                    'uniqueid' => $this->_user->uniqueId
                ), array('isattachment' => null));

                $post['attachments'] = array_unique($attachments->toArray());
                $post['attachnum']   = count($post['attachments']);
            }

            if (isset($params['savetime'])) {
                $post['savetime'] = (int) $params['savetime'];
            }
            if (isset($params['elapsedtime'])) {
                $post['elapsedtime'] = (int)$params['elapsedtime'] * 3600;
            }
        }

        $boards = $this->getBoards(false);
        if (isset($boards[$tudu->boardId])) {
            $this->view->board = $boards[$tudu->boardId];
        }

        $cookies = $this->_request->getCookie();

        $this->view->registModifier('tudu_format_content', array($this, 'formatContent'));

        $upload = $this->options['upload'];
        $upload['cgi']['upload'] .= '?' . session_name() . '=' . $this->_sessionId
                                  . '&email=' . $this->_user->address;

        $this->view->upload  = $upload;
        $this->view->cookies = serialize($cookies);
        $this->view->post = $post;
        $this->view->tudu = $tudu->toArray();
        $this->view->access = $access;
        $this->view->back = $this->_request->getQuery('back');
        $this->view->newwin  = (boolean) $this->_request->getQuery('newwin');
        $this->render('modify_post');
    }

    /**
     * 显示附件列表
     */
    public function attachAction()
    {
        $tuduId = $this->_request->getQuery('tid');

        $daoAttach = $this->getDao('Dao_Td_Attachment_File');

        $attachs = $daoAttach->getTuduFiles(array(
            'tuduid' => $tuduId
        ))->toArray();

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));
        $this->view->registModifier('tudu_get_attachment_url', array($this, 'getAttachmentUrl'));
        $this->view->attachs = $attachs;
    }


    /**
     * 显示日志列表
     */
    public function logAction()
    {
        $tuduId = $this->_request->getQuery('tid');

        $daoLog = $this->getDao('Dao_Td_Log_Log');
        $logs   = $daoLog->getLogs(array(
            'orgid' => $this->_user->orgId,
            'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
            'targetid' => $tuduId,
            'privacy' => 0
        ))->toArray();

        $this->view->registFunction('format_log_detail', array($this, 'formatLogDetail'));

        $this->view->logs = $logs;
    }

    /**
     * 用户名片
     */
    public function userCardAction()
    {
        $userId = $this->_request->getQuery('userid');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        $user = $daoUser->getUserCard(array(
            'orgid'  => $this->_user->orgId,
            'userid' => $userId
        ));

        $this->json(true, null, $user);
    }

    /**
     * 输出分类数据
     */
    public function classesAction()
    {
        $bid = $this->_request->getQuery('bid');

        $boards  = $this->getBoards(false);
        $board   = $boards[$bid];

        $isOwner = $board['ownerid'] == $this->_user->userId;
        $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
        $isSuperModerator = array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']);
        $isNewClass = $isOwner || $isModerators || $isSuperModerator;

        $daoClass = $this->getDao('Dao_Td_Tudu_Class');

        $classes = $daoClass->getClassesByBoardId($this->_user->orgId, $bid, array('ordernum' => 'ASC'));
        $classes = $classes->toArray();

        if ($isNewClass && $classes) {
            $classes[] = array(
                'classname' => $this->lang['create_board_class'],
                'classid' => '^add-class'
            );
        }

        $this->json(true, null, $classes);
    }

    /**
     * 工作流数据
     */
    public function flowAction()
    {
        $flowId = $this->_request->getQuery('flowid');
        $attachments = array();

        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');
        /* @var $daoAttachment Dao_Td_Flow_Attachment */
        $daoAttachment = $this->getDao('Dao_Td_Flow_Attachment');

        $flow = $daoFlow->getFlowById($flowId, array('isvalid' => 1))->toArray();
        $cc = array();
        foreach ($flow['cc'] as $key => $val) {
            $cc[] = array('email' => $val[3], 'name' => $val[0]);
        }
        $flow['cc'] = $cc;
        $flow['content'] = $this->formatContent($flow['content']);

        $steps = $this->formatStepsToHtml($flow['steps']);

        // 读取附件
        if ($daoAttachment->existsAttach($flowId, 1)) {
            $attachments = $daoAttachment->getAttachments(array('flowid' => $flowId), array('isattach' => 1));
            $attachments = $attachments->toArray();
        }

        return $this->json(true, null, array('flow' => $flow, 'steps' => $steps, 'attach' => $attachments));
    }

    /**
     * 是否执行清除CAST数据
     */
    public function clearCastAction()
    {
        $loadTime = $this->_request->getQuery('loadtime');
        $updateTime = $this->cache->get('TUDU-CAST-UPDATE-' . $this->_user->orgId);
        $this->json(true, null, array('clear' => $updateTime - $loadTime > 0));
    }

    /**
     * 获取模板目录
     */
    public function tplListAction()
    {
        $boardId = $this->_request->getParam('bid');
        $daoTemplate = $this->getDao('Dao_Td_Tudu_Template');
        $templates = $daoTemplate->getTemplatesByBoardId($this->_user->orgId, $boardId, null, 'ordernum ASC');
        $tpl = array();
        foreach($templates as $template) {
            $tpl[] = array(
                $template->templateId,
                $template->name
            );
        }
        return $this->json(true, null, $tpl);
    }

    /**
     * 获取模板内容
     */
    public function tplContentAction()
    {
        $tplId = $this->_request->getParam('tplid');
        $boardId = $this->_request->getParam('bid');
        $daoTemplate = $this->getDao('Dao_Td_Tudu_Template');
        $condition = array(
            'orgid' => $this->_user->orgId,
            'boardid' => $boardId,
            'templateid' => $tplId
        );
        $template = $daoTemplate->getTemplate($condition);

        return $this->json(true, null, $template->content);
    }

    /**
     * 获取投票选项
     */
    public function voteOptionsAction()
    {
        $tuduId = $this->_request->getParam('tid');
        $voteId = $this->_request->getParam('voteid');

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));
        $vote->getOptions();

        $vote = $vote->toArray();
        return $this->json(true, null, $vote['options']);
    }

    /**
     * 导出投票数据
     */
    public function exportVoteAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $tuduId = $this->_request->getParam('tid');
        $voteId = $this->_request->getParam('voteid');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu  = $this->getDao('Dao_Td_Tudu_Tudu');
        $tudu     = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId, array());
        $isSender = in_array($tudu->sender, array($this->_user->address, $this->_user->userName), true);

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        $vote = $daoVote->getVote(array('tuduid' => $tuduId, 'voteid' => $voteId));
        $vote->getOptions();

        $vote     = $vote->toArray();
        $filename = Oray_Function::utf8ToGbk(sprintf($this->lang['vote_info'], $vote['title']) . '.csv');
        $data     = array();
        // 准备导出数据的列名
        $columns  = array(
            Oray_Function::utf8ToGbk($this->lang['vote_option']),
            Oray_Function::utf8ToGbk($this->lang['vote_result']),
            Oray_Function::utf8ToGbk($this->lang['vote_percent'])
        );

        // 创建人可见投票参与人
        if ($vote['anonymous'] && $isSender) {
            $vote['privacy'] = true;
        }

        // 公开参与人
        if ($vote['privacy']) {
            $columns[] = Oray_Function::utf8ToGbk($this->lang['vote_voter']);
        }

        $data[] = implode(',', $columns);

        foreach ($vote['options'] as $optionId => $option) {
            // 公开参与人
            if ($vote['privacy']) {
                $voters = array();
                if (!empty($option['voters'])) {
                    foreach ($option['voters'] as $voter) {
                        if (trim($voter)) {
                            $voter = explode(' ', $voter);
                            $voters[] = $voter[1];
                        }
                    }
                }
                $voters  = !empty($voters) ? implode('、', $voters) : '-';
            }

            $percent = ($option['votecount'] / $vote['votecount']) * 100;

            $optionItem = array(
                Oray_Function::utf8ToGbk($option['text']),
                $option['votecount'],
                $percent . '%'
            );

            // 公开参与人
            if ($vote['privacy']) {
                $optionItem[] = Oray_Function::utf8ToGbk($voters);
            }

            $data[] = implode(',', $optionItem);
        }

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo implode("\n", $data);
    }

    /**
     * 获取图度组
     */
    public function tuduGroupsAction()
    {
        // 读取分区板块信息
        $tuduBoards = $this->getBoards();
        $boards     = array();
        foreach ($tuduBoards as $key => $zone) {
            if ($zone['type'] == 'zone' && empty($zone['children'])) {
                unset($tuduBoards[$key]);
                continue;
            }

            unset($tuduBoards[$key]['children']);
            $boards[] = $tuduBoards[$key];

            if (!empty($zone['children'])) {
                foreach ($zone['children'] as $k => $child) {
                    if ($child['status'] != 2) {
                        $boards[] = $child;
                    }
                }
            }
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        // 读取图度组
        $tudus   = $daoTudu->getTuduGroups(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId))->toArray();
        $groups  = array();

        // 过滤图度组
        foreach ($tudus as $tudu) {
            $isAccepter = in_array($this->_user->address, $tudu['accepter'], true) || in_array($this->_user->userName, $tudu['accepter'], true);
            $isSender   = in_array($tudu['sender'], array($this->_user->address, $this->_user->userName), true);

            if ($isSender || $isAccepter) {
                $groups[] = $tudu;
            }
        }

        return $this->json(true, null, array('boards' => $boards, 'tudus' => $groups));
    }

    /**
     *
     */
    public function getFloorsAction()
    {
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost  = $this->getDao('Dao_Td_Tudu_Post');
        $tuduId   = $this->_request->getParam('tid');
        $postId   = $this->_request->getParam('pid');

        $post  = $daoPost->getPost(array('tuduid' => $tuduId, 'postid' => $postId));
        if (null !== $post) {
            $content = $this->formatContent($post->content);
        } else {
            $content = null;
        }

        return $this->json(true, null, array('content' => $content));
    }

    /**
     * 处理工作流程
     *
     * @param array $steps
     */
    public function formatStepsToHtml($steps)
    {
        if (count($steps) <= 0) {
            return null;
        }

        $html = array();
        $stepIdx = 0;
        $countSteps = count($steps);
        $html[] = '<div class="flowhtml">';
        $html[] = '<span>'.$this->lang['tudu_flow'].'：</span>';

        $prevUser = array(array('username' => $this->_user->userName));

        foreach ($steps as $stepId => $step) {

            if ($stepIdx > 0) {
                $html[] = '<span class="icon icon_flow_arrow"></span>';
            }

            $stepIdx ++;
            $section = isset($step['section']) ? $step['section'] : $step['sections'];
            if (is_array($section)) {
                foreach ($section as $idx => $section) {
                    if ($idx > 0) {
                        $html[] = '<span class="icon icon_flow_arrow"></span>';
                    }

                    $prevUser = array();
                    foreach ($section as $i => $user) {
                        if ($i > 0) {
                            $html[] = '<span class="icon icon_flow_plus"></span>';
                        }

                        if ($step['type'] == 1) {
                            $html[] = '<span title="'.$user['username'].'">'.$user['truename'].'('.$this->lang['future_review'].')'.'</span>';
                        } else {
                            $html[] = '<span title="'.$user['username'].'">' . $user['truename'] . '</span>';
                        }

                        $prevUser[] = $user;
                    }

                }
            } elseif ($section == '^upper') {
                if (empty($prevUser)) {
                    $html[] = $this->lang['higher_review'];
                    continue ;
                }

                foreach ($prevUser as $item) {
                    $moderator = array();
                    foreach ($prevUser as $user) {
                        $m = $this->_getUpper($user['username']);
                        if (empty($m)) {
                            $moderator = null;
                            break;
                        }
                        $moderator = array_unique(array_merge($moderator, $m));
                    }

                    if (empty($moderator)) {
                        $html[] = '<span class="red" title="'.$this->lang['missing_upper_reviewer'].'">'.$this->lang['higher_review'].'</span>';
                    } else {
                        $reviewer = array();
                        foreach ($moderator as $m) {
                            $reviewer[] = '<span title="'.$m['email'].'">'.$m['truename'].'('.$this->lang['future_review'].')'.'</span>';
                        }
                        $html[] = implode('<span class="icon icon_flow_plus"></span>', $reviewer);
                    }
                    //$html[] = '<span class="icon icon_flow_arrow">d</span>';
                }
            }
        }

        $html[] = '</div>';
        $html   = implode('', $html);
        return $html;
    }

    /**
     *
     * @param array $data
     */
    public function formatName($data)
    {
        $ret = array();
        foreach ($data as $item) {
            list ($userName, $trueName) = explode(' ', $item);
            $ret[] = '<'.$userName.'>'.$trueName;
        }
        return implode(',', $ret);
    }

    /**
     * 格式化恢复
     *
     * @param $header
     */
    public function formatPostHeader($header, $poster = null)
    {
        if (!empty($header['action']) && ($header['action'] == 'claim' || $header['action'] == 'review')) {
            $ret = array(
                'action' => $header['action'],
            );
            if ($header['action'] == 'review') {
                if (isset($header['tudu-act-value'])) {
                    $ret['val'] = $header['tudu-act-value'];
                }

                $ret['text'] = '';
                if (!empty($poster)) {
                    $ret['text'] = $poster . ' ';
                }

                if ($ret['val']) {
                    if (isset($header['tudu-reviewer'])) {
                        $ret['text'] .= sprintf($this->lang['agree_reply'], $header['tudu-reviewer']);
                    } elseif (isset($header['tudu-to'])) {
                        $ret['text'] .= sprintf($this->lang['agree_reply_to_exec'], $header['tudu-to']);
                    } else {
                        $ret['text'] .= $this->lang['agree_reply_no_next'];
                    }
                } else {
                    $ret['text'] .= $this->lang['reject_reply'];
                }
            } else if ($header['action'] == 'claim') {
                $ret['val']  = 1;
                $ret['text'] = sprintf($this->lang['claim_accepter_log'], $header['tudu-claimer']);
            }

            return $ret;
        }

        return null;
    }

    /**
     * 格式化内容
     *
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
     * 处理文件大小
     *
     * @param $size
     */
    public function formatFileSize($size)
    {
        $base = 1024;
        $units = array(pow($base, 3) => 'GB', pow($base, 2) => 'MB', $base => 'KB');

        foreach ($units as $step => $unit) {
            $val = $size / $step;
            if ($val >= 1) {
                return round($val, 2) . $unit;
            }
        }

        return $size . 'B';
    }

    /**
     * 输出日志详细信息
     *
     * @param array $params
     */
    public function formatLogDetail(array $params, &$smarty)
    {
        if (empty($params['action'])) {
            return null;
        }

        $decpts = $this->lang['tudu_log'];
        $detail = $params['detail'];
        $action = $params['action'];

        if ($action == 'create' && $params['detail']['group']) {
            $action = 'create_group';
        }

        $ret = array($decpts['action_' . $action]);

        if (is_array($detail)) {
            foreach ($detail as $key => $val) {
                if ($key == 'bcc' || $key == 'reviewer') {continue ;}
                if (in_array($key, array('to', 'cc')) && $val) {
                    if (is_string($val)) {
                        $arr = explode("\n", $val);
                    }
                    $names = array();
                    foreach ($arr as $item) {
                        $item = explode(' ', $item);
                        if (empty($item[1])) continue ;
                        $names[] = $item[1];
                    }
                    if ($names) {
                        $ret[] = $decpts[$key] . $this->lang['cln'] . implode(',', $names);
                    }
                    continue ;
                }
                if (!array_key_exists($key, $decpts)) {
                    continue ;
                }

                switch ($key) {
                    case 'status':
                    case 'selfstatus':
                        $val = $this->lang['tudu_status_' . $val];
                        break;
                    case 'endtime':
                    case 'starttime':
                        if (!$val) {
                            continue ;
                        }
                        $val = date('Y-m-d', $val);
                        break;
                    case 'totaltime':
                        $val = round($val/3600, 1);
                        break;
                    case 'agree':
                        $val = !empty($val) ? $this->lang['agree'] : $this->lang['disagree'];
                        break;
                }

                $str = $decpts[$key] . $this->lang['cln'] . $val;
                if (array_key_exists($key . '_suffix', $decpts)) {
                    $str .= $decpts[$key . '_suffix'];
                }
                $ret[] = $str;
            }
        }

        $ret = implode(';&#13; ', $ret);
        if (!empty($params['assign'])) {
            $smarty->assign($params['assign'], $ret);
        } else {
            return $ret;
        }
    }

    /**
     *
     * @param array $boards
     */
    public function formatBoardList($boards)
    {
        foreach ($boards as &$zone) {
            if (!empty($zone['children'])) {
                foreach($zone['children'] as &$item) {
                    unset($item['memo']);
                }
            }
        }

        return json_encode($boards);
    }

    /**
     * 获取编辑页面数据
     *
     * @param boolean $isExternal 是否获取扩展数据（周期任务，投票，图度组子任务等）
     */
    private function _getModifyParams($isExternal = false)
    {
        $params = $this->_request->getParams();

        $tuduFields = array(
            'type', 'bid', 'subject', 'subject', 'to', 'cc', 'priority',
            'privacy', 'status', 'content', 'attach', 'starttime', 'endtime', 'totaltime', 'percent',
            'classid', 'notifyall', 'istop', 'classname', 'nd-attach', 'isauth',
            'chidx', 'prev'
        );

        $tudu     = array();
        $vote     = array();
        $cycle    = array();
        $children = array();

        // 获取图度数据
        foreach ($tuduFields as $key) {
            if (array_key_exists($key, $params)) {
                if (empty($params[$key])) {
                    continue ;
                }
                switch ($key) {
                    case 'bid':
                        $tudu['boardid'] = $params[$key];
                        break;
                    case 'ftid':
                        $tudu['tuduid'] = $params[$key];
                        break;
                    case 'prev':
                        $tudu['prevtuduid'] = $params[$key];
                        break;
                    case 'to':
                        if ($params['type'] == 'tudu') {
                            $to = explode("\n", $params[$key]);
                            foreach ($to as $item) {
                                $tudu[$key][]['userinfo'] = $item;
                            }
                        } else {
                            $tudu[$key] = Dao_Td_Tudu_Tudu::formatAddress($params[$key]);
                        }
                        break;
                    case 'cc':
                        $tudu[$key] = Dao_Td_Tudu_Tudu::formatAddress($params[$key]);
                        break;
                    case 'percent':
                        if ($params[$key] != 0) {
                            $tudu[$key] = (int) $params[$key];
                        }
                        break;
                    case 'totaltime':
                        $tudu[$key] = (int) $params[$key] * 3600;
                        break;
                    default:
                        $tudu[$key] = $params[$key];
                }
            }
        }

        if (!empty($tudu['to'])) {
            $tudu['accepter'] = array_keys($tudu['to']);
        }

        if (!empty($tudu['attach']) && !empty($tudu['nd-attach'])) {
            $tudu['attach'] = array_diff($tudu['attach'], array_intersect($tudu['attach'], $tudu['nd-attach']));
            $tudu['attachnum'] = !empty($tudu['attach']) ? count($tudu['attach']) : 0;
        }

        // 获取附件
        if (!empty($tudu['attach'])) {
            $daoFile = $this->getDao('Dao_Td_Attachment_File');
            $tudu['attachments'] = $daoFile->getFiles(
                array('fileid' => $tudu['attach']),
                array('isattachment' => null)
            )->toArray();
        }

        if (!empty($tudu['nd-attach'])) {
            $daoNetdisk = $this->getDao('Dao_Td_Netdisk_File');

            $files = $daoNetdisk->getFiles(array('fileid' => $tudu['nd-attach'], 'uniqueid' => $this->_user->uniqueId));

            $tudu['attachnum'] += count($files);
            $tudu['nd-attach'] = $files->toArray();
        }

        // 扩展数据，于预览页面显示内容
        if ($isExternal && !empty($tudu['type'])) {
            switch ($tudu['type']) {
                // 获取重复周期 或 图度组新建子任务
                case 'task':
                    $chIndex = isset($params['chidx']) ? $params['chidx'] : null;
                    $cycle   = isset($params['cycle']) && $params['cycle'] ? $params['cycle'] : null;

                    if (null !== $chIndex) {
                        foreach ($chIndex as $idx) {
                            // 获取图度数据
                            $child = array();
                            foreach ($tuduFields as $key) {
                                if (!empty($params[$key . '-' . $idx])) {
                                    if ($key == 'bid') {
                                        $child['boardid'] = $params[$key . '-' . $idx];
                                    } elseif (in_array($key, array('to', 'cc'))) {
                                        $child[$key] = Dao_Td_Tudu_Tudu::formatAddress($params[$key . '-' . $idx]);
                                    } elseif ($key == 'percent') {
                                        $child[$key] = (int) $params[$key . '-' . $idx];
                                    } else {
                                        $child[$key] = $params[$key . '-' . $idx];
                                    }
                                } elseif (isset($tudu[$key])) {
                                    $child[$key] = $tudu[$key];
                                }
                            }

                            if (!empty($child['to'])) {
                                $child['accepter'] = array_keys($child['to']);
                            }

                            if (!empty($child['attach'])) {
                                $child['attachnum'] = count($child['attach']);
                            }

                            $children[] = $child;
                        }
                    } elseif (null !== $cycle) {
                        $cycleFields = array(
                            'at', 'what', 'day', 'week', 'month', 'weeks'
                        );

                        $cycle = array(
                            'endtype' => isset($params['endtype']) ? $params['endtype'] : null,
                            'enddate' => isset($params['enddate']) ? $params['enddate'] : null,
                            'endcount' => isset($params['endcount']) ? $params['endcount'] : null,
                            'cycleid'  => isset($params['cycleid']) ? $params['cycleid'] : null,
                            'mode'     => isset($params['mode']) ? $params['mode'] : null
                        );

                        $cycle['type'] = isset($params['type-'.$cycle['mode']]) ? $params['type-'.$cycle['mode']] : null;

                        $prefix = $cycle['mode'] . '-' . $cycle['type'] . '-';

                        foreach ($cycleFields as $key) {
                            if (isset($params[$prefix . $key])) {
                                $cycle[$key] = $params[$prefix . $key];
                            }
                        }
                    }

                    break;

                // 尝试获取投票数据
                case 'discuss':
                    if (isset($params['vote']) && $params['vote']
                        &&!empty($params['newoption']) && is_array($params['newoption'])) {
                        $vote['maxchoices'] = isset($params['maxchoices']) ? (int) $params['maxchoices'] : 1;
                        foreach ($params['newoption'] as $item) {
                            if (empty($params['text-'. $item])) {
                                continue ;
                            }
                            $vote['options'][] = array(
                                'optionid' => $item,
                                'text'     => $params['text-' . $item],
                                'ordernum' => !empty($params['ordernum-' . $item]) ? $params['ordernum' . $item] : 0
                            );
                        }
                    }
                    break;
            }
        }

        return array(
            'tudu'     => $tudu,
            'cycle'    => $cycle,
            'vote'     => $vote,
            'children' => $children
        );
    }

    /**
     *
     * @param cycle $cycle
     */
    private function _formatCycleInfo($cycle, $tudu)
    {
        if (!$cycle) return null;

        $str  = $this->lang['cycle_remind'];
        $mode = $cycle['mode'];

        //$what = $cycle[$mode] . $this->lang[$mode];
        $what = '';

        switch ($mode) {
            case 'day':
                if ($cycle['what'] == 'workday') {
                    $what .= $this->lang['cycle_every'] . $this->lang['work_day'];
                } elseif ($cycle['type'] == 3) {
                    $what .= $this->lang['cycle_every_complete'] . $cycle['day'] . $this->lang['day'];
                } else {
                    $what .= $this->lang['cycle_every'] . $cycle['day'] . $this->lang['day'];
                }
                break;
            case 'week':
                if ($cycle['type'] == 3) {
                    $what = $this->lang['cycle_every_complete'] . $cycle['week'] . $this->lang['week'];
                } else {
                    $weeks = array();
                    foreach ($cycle['weeks'] as $val) {
                        $weeks[] = $this->lang['week_' . $val];
                    }
                    $what = sprintf($this->lang['cycle_repeat_week'], $cycle['week'], implode($this->lang['comma'], $weeks));
                }
                break;
            case 'month':
                if ($cycle['type'] == 1) {
                    $what = $this->lang['cycle_every'] . $cycle['month'] . $this->lang['cycle_month'] . $this->lang['cycle_number'] . $cycle['day'] . $this->lang['day'];
                } elseif ($cycle['type'] == 2) {
                    $what = $this->lang['cycle_every'] . $cycle['month'] . $this->lang['cycle_month'] . $this->lang['cycle_after'] . $this->lang['cycle_at_' . $cycle['at']];
                    if (isset($this->lang[$cycle['what']])) {
                        $what .= $this->lang[$cycle['what']];
                    } else {
                        $what .= $this->lang['date_' . $cycle['what']];
                    }
                } else {
                    $what = $this->lang['cycle_every_complete'] . $cycle['month'] . $this->lang['month'];
                }
                break;
        }

        $validTime = $tudu->startTime ? date('Y-m-d', $tudu->startTime) : date('Y-m-d', $tudu->createTime);
        if ($cycle['enddate'] && $cycle['endtype'] == 2) {
            $validTime .= ' - ' . date('Y-m-d', $cycle['enddate']);
        }

        $str = sprintf($str, $what, $validTime);

        if ($cycle['endcount'] && $cycle['endtype'] == 1) {
            $str .= sprintf($this->lang['cycle_repeat_times'], $cycle['endcount']);
        }

        return $str;
    }

    private function _formatInfo($params) {
        $params = array();
        $params['subject'] = $this->_request->getParam('subject');
        return $params;
    }

    /**
     * 获取图度发起人、执行人、抄送人地址
     *
     * @param array $data
     * return array
     */
    private function _getTuduAddress($data)
    {
        if (!$data) {
            return null;
        }

        $address = array();

        $address[] = $data['from'][3];
        if (!empty($data['to'])) {
            foreach ($data['to'] as $to) {
                $address[] = $to[3];
            }
        }
        if (!empty($data['cc'])) {
            foreach ($data['cc'] as $cc) {
                $address[] = $cc[3];
            }
        }

        return array_unique($address);
    }

    /**
     * 检查上级图度组权限
     *
     * @param string $tuduId
     * @param array $tudus
     */
    private function _checkParentAccess($tuduId, $tudus)
    {
        if (!$tuduId) {
            return null;
        }

        $address = array();
        foreach ($tudus as $key => $tudu) {
            if ($tuduId == $key) {
                $address = $this->_getTuduAddress($tudu);

                if (!in_array($this->_user->address, $address)) {
                    $parentId = $tudu['parentid'];
                    if (!$parentId) {
                        return false;
                    }
                    return $this->_checkParentAccess($parentId, $tudus);
                } else {
                    return true;
                }
            }
        }

        return true;
    }

    /**
     * 获取指定上级人员
     *
     * @param string $userName
     */
    private function _getUpper($userName)
    {
        list($userId, ) = explode('@', $userName, 2);
        $depts          = $this->_getDepartments($this->_user->orgId);

        $addressBook = Tudu_AddressBook::getInstance();

        $user = $addressBook->searchUser($this->_user->orgId, $userName);

        if (empty($user['deptid'])) {
            $user['deptid'] = '^root';
        }

        if (empty($depts[$user['deptid']])) {
            return null;
        }

        $dept = $depts[$user['deptid']];

        // 是当前部门负责人
        if (in_array($userId, $dept['moderators']) && $user['deptid'] != '^root') {
            $dept = $depts[$dept['parentid']];
        }

        $ret = array();
        foreach ($dept['moderators'] as $m) {
            $u = $addressBook->searchUser($this->_user->orgId, $m . '@' . $this->_user->orgId);
            if (!$u) {
                return null;
            }
            $ret[$u['email']] = $u;
        }

        return $ret;
    }

    /**
     * 获取部门列表
     *
     * @param string $orgId
     */
    protected function _getDepartments($orgId)
    {
        if (null === $this->_depts) {
            /* @var Dao_Md_Department_Department */
            $daoDepts = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $this->_depts = $daoDepts->getDepartments(array(
                'orgid'  => $orgId
            ))->toArray('deptid');
        }

        return $this->_depts;
    }


    /**
     * 获取图度组的开始时间和结束时间
     *
     * @param array $data
     * return array
     */
    private function getGanttTime(array $data) {
        $tudu = $data['tudu'];
        $childrens = $data['childrens'];

        $nowTime = strtotime(date('Y-m-d 00:00:00', time()));
        $minTime = $nowTime;
        $maxTime = $nowTime;
        $expiredNum = 0;
        $startTimeNull = 0;
        $endTimeNull = 0;
        foreach ($childrens as $children) {
            if ($minTime > $children['starttime'] && $children['starttime']) {
                $minTime = $children['starttime'];
            }
            if ($maxTime < $children['endtime'] && $children['endtime']) {
                $maxTime = $children['endtime'];
            }
            if (isset($children['isexpired'])) {
                $expiredNum++;
            }
        }

        if ($minTime > $nowTime && $tudu['starttime']) {
            $minTime = min($tudu['starttime'], $minTime);
        } elseif ($minTime == $nowTime && !$tudu['starttime']) {
            $minTime = null;
        } elseif ($minTime == $nowTime && $tudu['starttime']) {
            $minTime = $tudu['starttime'];
        }

        if ($maxTime > $nowTime && $tudu['endtime']) {
            $maxTime = max($tudu['endtime'], $maxTime);
        } elseif ($maxTime == $nowTime && !$tudu['endtime']) {
            $maxTime = null;
        } elseif ($maxTime == $nowTime && $tudu['endtime']) {
            $maxTime = $tudu['endtime'];
        }

        if (!$minTime && $maxTime) {
            $minTime = $maxTime;
            $startTimeNull = 1;
        }

        if ($minTime && !$maxTime) {
            $maxTime = $minTime;
            $endTimeNull = 1;
        }

        if (isset($tudu['isexpired'])) {
            $expiredNum++;
        }

        if (!$maxTime && !$minTime) {
            $minTime = strtotime(date('Y-m-d 00:00:00', time()));
            $maxTime = strtotime(date('Y-m-d 00:00:00', time()));
            $startTimeNull = 1;
            $endTimeNull = 1;
        }

        if (($expiredNum > 0 && $maxTime && $maxTime < time()) || ($expiredNum > 0 && !$maxTime)) {
            $maxTime = strtotime(date('Y-m-d 00:00:00', time()));
        }

        return array('min' => $minTime, 'max' => $maxTime, 'start' => $startTimeNull, 'end' => $endTimeNull);
    }
}
