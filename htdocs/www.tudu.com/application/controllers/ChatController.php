<?php
/**
 * Chat Controller
 *
 * @version $Id: ChatController.php 2733 2013-01-31 01:41:03Z cutecube $
 */

class ChatController extends TuduX_Controller_Base
{

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'chat'));

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
     * 聊天记录页面
     */
    public function logAction()
    {
        $email = $this->_request->getQuery('email');
        $group = $this->_request->getQuery('group');

        $this->view->email = $email;
        $this->view->group = $group;
    }

    /**
     * 输出记录
     */
    public function logListAction()
    {
        $email    = $this->_request->getQuery('email');
        $group    = $this->_request->getQuery('group');
        $page     = max((int) $this->_request->getQuery('page'), 1);
        $logId    = $this->_request->getQuery('chatlogid');
        $isPage   = (boolean) $this->_request->getQuery('ispage');
        $pageSize = 100;
        $lastPage = false;

        /* @var $daoChat Dao_Im_Chat_Log */
        $daoChat = $this->getImDao('Dao_Im_Chat_Log');

        $condition = array();
        if ($logId) {
            $log = $daoChat->getLog(array('chatlogid' => $logId));
            $condition['starttime'] = strtotime(date('Y-m-d', $log->createTime));
            $condition['starttime'] = $condition['starttime'] + 86399;
        }

        if (false != strpos($email, '@conference')) {
            $group = $email;
            $email = null;
        }

        if ($email) {
            $condition['ownerid'] = $this->_user->userName;
            $condition['otherid'] = $email;
            $logs = $daoChat->getLogPage($condition, 'ordernum DESC', $page, $pageSize);
        }

        if ($group) {
            $condition['address'] = $this->_user->userName;
            $condition['groupid'] = $group;
            $logs = $daoChat->getDiscussLogPage($condition, 'ordernum DESC', $page, $pageSize);
        }

        $logs = $logs->toArray();

        if (empty($logs) && $isPage) {
            $this->_helper->viewRenderer->setNeverRender();
            return ;
        }

        if (count($logs) != $pageSize) {
            $lastPage = true;
        }

        $ret  = array();
        foreach ($logs as $log) {
            $ret[strtotime(date('Y-m-d', $log['createtime']))][$log['ordernum']] = $log;
        }

        foreach ($ret as &$day) {
            ksort($day);
        }

        ksort($ret);

        $this->view->lastpage = $lastPage;
        $this->view->page     = $page;
        $this->view->email    = $email;
        $this->view->group    = $group;
        $this->view->logs     = $ret;
    }

    /**
     * 详细聊天纪录页（指定记录当前的全部）
     */
    public function detailAction()
    {
        $email    = $this->_request->getQuery('email');
        $group    = $this->_request->getQuery('group');
        $page     = max((int) $this->_request->getQuery('page'), 1);
        $logId    = $this->_request->getQuery('chatlogid');
        $pageSize = 100;

        /* @var $daoChat Dao_Im_Chat_Log */
        $daoChat = $this->getImDao('Dao_Im_Chat_Log');

        $condition = array();
        if ($logId) {
            $log = $daoChat->getLog(array('chatlogid' => $logId));
            $condition['starttime'] = strtotime(date('Y-m-d', $log->createTime));
            $condition['endtime'] = $condition['starttime'] + 86399;

            $this->view->date  = $log->createTime;
        }

        if ($email) {
            $condition['ownerid'] = $this->_user->userName;
            $condition['otherid'] = $email;
        } elseif ($group) {
            $condition['address'] = $this->_user->userName;
            $condition['groupid'] = $group;
        }

        $logs = $daoChat->getLogs($condition, null, 'ordernum ASC')->toArray();

        $this->view->email = $email;
        $this->view->group = $group;
        $this->view->logs  = $logs;
        $this->view->back  = $this->_request->getQuery('back');
    }

    /**
     * 记录搜索
     *
     */
    public function searchAction()
    {
        $key     = trim($this->_request->getQuery('keyword'));
        $target  = trim($this->_request->getQuery('target'));
        $range   = $this->_request->getQuery('range');
        $page    = max(1, (int) $this->_request->getQuery('page'));
        $fromUrl = $this->_request->getQuery('fromurl');

        $pageSize = 20;

        $timeRange = null;
        if (!empty($range)) {
            switch ($range) {
                case 'week':
                    $timeRange = array(strtotime('-7 days'), time());
                    break;
                case 'month':
                    $timeRange = array(strtotime('-30 days'), time());
                    break;
                case '3month':
                    $timeRange = array(strtotime('-90 days'), time());
                    break;
            }
        }

        $params = array(
           'keyword' => $key,
           'target'  => $target,
           'range'   => $range,
           'fromUrl' => $fromUrl
        );

        $config = $this->options['sphinx'];

        $sphinx = new Oray_Search_Sphinx_Client($config);

        $option = new Oray_Search_Sphinx_Option(array(
            'sortby' => 'create_time',
            'mode'   => Oray_Search_Sphinx_Option::MATCH_EXTENDED2,
            'offset' => ($page - 1) * $pageSize
        ));

        $arr = preg_split('/[\s\+\/]+/', $key);

        foreach ($arr as $i => $word) {
            if (!$word) {
                unset($arr[$i]);
            }
            $arr[$i] = $sphinx->escapeString($word);
        }
        $keyword = implode('|' , $arr);

        $keyword = "@content \"{$keyword}\"/1";
        if ($target) {
            if ($target == '^user') {
                $email   = str_replace('@', '\@', $this->_user->userName);
                $keyword = "@users {$email} {$keyword}";
            } elseif ($target == '^groups') {
                $groupId = trim($this->_request->getQuery('groupid'));

                if ($groupId) {
                    // 获取加入时间
                    $inTime = 0;

                    $daoGroup = $this->getImDao('Dao_Im_Group_Group');
                    $member   = $daoGroup->getGroupMember($groupId, $this->_user->userName);

                    if ($member) {
                        $inTime = strtotime($member['createtime']);
                        if (null == $timeRange) {
                            $timeRange = array(0 => 0, 1 => time());
                        }

                        if (empty($timeRange[0]) || $timeRange[0] < $inTime) {
                            $timeRange[0] = $inTime;
                        }

                        $gid = str_replace('-', '', str_replace('@', '\@', $groupId));
                        $keyword = "@groupid {$gid} {$keyword}" ;
                    } else {
                        $keyword = null;
                    }

                    $params['groupid'] = $groupId;
                }
            } else {
                $otherId = $this->_request->getQuery('otherid');
                $otherId = str_replace('@', '\@', $otherId);
                $email   = str_replace('@', '\@', $this->_user->userName);
                $keyword = "@users {$email}+{$otherId}|{$otherId}+{$email} {$keyword}";

                $params['otherid'] = $otherId;
            }
        }

        $logs  = array();
        $total = 0;

        $keys = array();
        $w    = strtolower($w);
        if ($keyword != null) {
            if (!empty($timeRange)) {
                $option->addRangeFilter('create_time', $timeRange[0], $timeRange[1]);
            }

            $result = $sphinx->query($keyword, $option, $config['indexes']['chat'] . ' ' . $config['indexes']['chatinc']);

            $matches = $result->getMatches();
            $total   = $result->getTotal();
            $words   = $result->getWords();

            foreach ($words as $w => $info) {
                if ($w && $info['docs'] > 0 && false !== strpos(strtolower($key), $w)) {
                    $keys[] = $w;
                }
            }

            if (count($matches)) {
                $logNums = array();
                foreach ($matches as $match) {
                    $logNums[] = $match['id'];
                }

                /* @var $daoChatLog Dao_Im_Chat_Log */
                $daoChatLog = $this->getImDao('Dao_Im_Chat_Log');

                $condition = array('ordernum' => $logNums);
                if ($target == '^groups') {
                    $condition['groupid'] = $groupId;
                } else {
                    $condition['ownerid'] = $this->_user->userName;
                }

                $logs = $daoChatLog->getLogs($condition)->toArray();
            }
        }

        $this->view->back = $this->_request->getQuery('back');
        $this->view->logs = $logs;
        $this->view->pageinfo = array(
            'currpage'    => $page,
            'pagecount'   => intval((($total - 1) / $pageSize) + 1),
            'recordcount' => $total
        );
        $this->view->keys    = implode("','", $keys);
        $this->view->query   = $params;
        $this->view->keyword = $keys;
        $this->view->type    = ($target == '^groups' ? 'group' : 'user');
    }

    public function groupAction()
    {
        /* @var $daoImGroup Dao_Im_Contact_Group */
        $daoImGroup = $this->getImDao('Dao_Im_Contact_Group');

        $groups = $daoImGroup->getGroups(array(
            'orgid' => $this->_user->orgId,
            'userid' => $this->_user->userId
        ));

        /* @var $daoImContact Dao_Im_Contact_Contact */
        $daoImContact = $this->getImDao('Dao_Im_Contact_Contact');

        $contacts = $daoImContact->getContacts(array(
            'orgid' => $this->_user->orgId,
            'userid' => $this->_user->userId
        ));

        $data = array(
            'groups' => $groups->toArray(),
            'contacts' => $contacts->toArray()
        );

        return $this->json(true, null, $data);
    }

    /**
     * 获取讨论组
     *
     * return json
     */
    public function discussAction()
    {
        /* @var $daoDiscuss Dao_Im_Group_Group */
        $daoDiscuss = $this->getImDao('Dao_Im_Group_Group');

        $discusses = $daoDiscuss->getGroup(array(
            'address' => $this->_user->userName
        ));

        $data = array(
            'discuss' => $discusses->toArray()
        );

        return $this->json(true, null, $data);
    }

    /**
     * 删除聊天记录
     */
    public function deleteAction()
    {
        $function  = $this->_request->getPost('fun');
        if (!in_array($function, array('one', 'more'))) {
            return $this->json(false, $this->lang['delete_action_error']);
        }

        /* @var $daoChatLog Dao_Im_Chat_Log */
        $daoChatLog = $this->getImDao('Dao_Im_Chat_Log');
        switch ($function) {
            case 'one':
                $chatLogId = $this->_request->getPost('logid');
                $otherId   = $this->_request->getPost('otherid');
                if (!$chatLogId) {
                    return $this->json(false, $this->lang['invalid_chatlogid']);
                }

                $ret = $daoChatLog->deleteLog($chatLogId, $this->_user->userName, $otherId);
                break;
            case 'more':
                $dateTime = $this->_request->getPost('datetime');
                $otherId  = $this->_request->getPost('otherid');
                $condition = array(
                    'otherid' => $otherId,
                    'ownerid' => $this->_user->userName
                );
                switch ($dateTime) {
                    case '0':// 今天之前
                        $condition['createtime'] = date('Y-m-d 00:00:00', time());
                        break;
                    case '1':// 一周之前
                        $condition['createtime'] = date('Y-m-d 00:00:00', strtotime('-1 week'));
                        break;
                    case '2':// 一个月之前
                        $condition['createtime'] = date('Y-m-d 00:00:00', strtotime('-1 month'));
                        break;
                    case '3':// 三个月之前
                        $condition['createtime'] = date('Y-m-d 00:00:00', strtotime('-3 month'));
                        break;
                }

                $maxCount = 100;//每次读取记录的最大数目
                $success  = 0;//成功删除记录的次数
                $time     = 0;//循环读取的次数

                // 分批删除
                do {
                    $logs = $daoChatLog->getLogs($condition, null, null, $maxCount)->toArray();
                    $time ++;
                    if (!count($logs)) {
                        break;
                    }

                    foreach ($logs as $log) {
                        $ret = $daoChatLog->deleteLog($log['chatlogid'], $this->_user->userName, $otherId);
                        if ($ret) {
                            $success ++;
                        }
                    }
                } while (true);

                $ret = $success > 0 ? true : false;

                // 当一次读记录没有任何数据时
                if ($time == 1 && !$ret) {
                    $ret = true;
                }
                break;
        }

        if (!$ret) {
            return $this->json(false, $this->lang['delete_log_failed']);
        }

        return $this->json(true, $this->lang['delete_log_success']);
    }
}