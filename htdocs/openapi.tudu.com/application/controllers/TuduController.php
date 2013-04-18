<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: TuduController.php 2758 2013-02-27 06:15:56Z cutecube $
 */

/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class TuduController extends TuduX_Controller_OpenApi
{

    /**
     *
     * @var array
     */
    protected $_boards = null;

    /**
     *
     * @var array
     */
    public $_lang = array(
        'all' => '所有图度',
        'inbox' => '图度箱',
        'todo'  => '我执行',
        'review' => '我审批',
        'reviewed' => '已审批',
        'drafts'   => '草稿箱',
        'starred' => '星标关注',
        'notice' => '公告',
        'discuss' => '讨论',
        'meeting' => '会议',
        'sent'    => '我发起',
        'forwarded' => '我转发',
        'done'      => '已完成',
        'ignore'    => '忽略',
        'wait'      => '待办',
        'associate' => '相关'
    );

    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 获取图度列表
     */
    public function listAction()
    {
        $query  = $this->_request->getQuery();
        $offset = (int) $this->_request->getQuery('offset', 0);
        $limit  = (int) $this->_request->getQuery('limit', 20);

        $condition = array('uniqueid' => $this->_user->uniqueId);

        // 标签
        if (!empty($query['labelid'])) {
            if ($query['labelid'] == '^c' || $query['labelid'] == '^td') {
                $condition['labelid'] = '^i';
            } else {
                $condition['labelid'] = $query['labelid'];
            }
        }

        // 关键字
        if (!empty($query['keyword'])) {
            $condition['keyword'] = $query['keyword'];
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $label   = null;

        if (isset($query['labelid'])) {
            /* @var $daoLabel Dao_Td_Tudu_Label */
            $daoLabel = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Label', Tudu_Dao_Manager::DB_TS);

            $label = $daoLabel->getLabel(array('uniqueid' => $this->_user->uniqueId), array('labelid' => $query['labelid']));

            if ($label) {
                $labelName = $label->isSystem ? $this->_lang[$label->labelAlias] : $label->labelAlias;

                $label = array(
                    'labelid'   => $query['labelid'],
                    'labelname' => $labelName,
                    'unreadnum' => $label->unreadNum,
                    'totalnum'  => $label->totalNum,
                    'synctime'  => $label->syncTime
                );
            }
        }

        if (isset($query['labelid']) && $query['labelid'] == '^e') {
            $tudus = array();

            $review = $daoTudu->getUserTudus($condition, array('role' => false, 'isdone' => false));
            $rcount = count($review);
            foreach ($review as $idx => $item) {
                if ($idx >= $offset) {
                    $tudus[] = $item;
                }
            }

            $count = count($tudus);

            // 需要读取待审批
            if ($limit > $count || $offset > count($review)) {
                $of = max(0, $offset - $rcount - $count);
                $lm = $limit  - $count;

                $reviewed = $daoTudu->getUserTudus(array('labelid' => '^v', 'uniqueid' => $this->_user->uniqueId), array('role' => false, 'isdone' => false), $of, $lm);

                foreach ($reviewed as $k => $item) {
                    $reviewed[$k]['reviewed'] = true;
                }

                $tudus = array_merge($tudus, $reviewed);
            }

        } else {
            if ($condition['labelid'] == '^i') {
                $offset = $limit = null;
            }

            $tudus = $daoTudu->getUserTudus($condition, array('role' => false, 'isdone' => false), $offset, $limit);
        }

        $ret = array();
        $unreadNum = 0;
        foreach ($tudus as $tudu) {
            $arr = explode(' ' , $tudu['from'], 2);
            //list($from, $fromName) = explode(' ' , $tudu['from'], 2);
            $from = $arr[0];
            $fromName = isset($arr[1]) ? $arr[1] : $arr[0];

            $isSender   = $from == $this->_user->userName;
            $isAccepter = false;

            $to  = array();
            if (!empty($tudu['to'])) {
                $arr = explode("\n", $tudu['to']);

                $labels     = explode(',', $tudu['labels']);
                foreach ($arr as $item) {
                    $arr = explode(' ', $item, 2);
                    $to[] = array(
                        'username' => $arr[0],
                        'truename' => isset($arr[1]) ? $arr[1] : $arr[0]
                    );

                    if ($arr[0] == $this->_user->userName || $arr[0] == $this->_user->address) {
                        $isAccepter = true;
                    }
                }
            }

            //
            $mark   = null;
            $labels = explode(',', $tudu['labels']);
            if (!$tudu['isdraft']) {
                if ($tudu['type'] == 'task' && $tudu['status'] >= 2 && $isSender && !$tudu['isdone']) {
                    $mark = 'confirm';
                }

                if (!$mark && in_array('^e', $labels) && !isset($tudu['reviewed'])) {
                    $mark = 'review';
                }

                if (!$mark && $isAccepter && empty($tudu['accepttime']) && $tudu['selfstatus'] < 2 && in_array($tudu['type'], array('task', 'meeting'))) {
                    $mark = 'accept';
                }
            }

            $item = array(
                'orgid'        => $tudu['orgid'],
                'tuduid'       => $tudu['tuduid'],
                'boardid'      => $tudu['boardid'],
                'type'         => $tudu['type'],
                'subject'      => $tudu['subject'],
                'isread'       => (int) $tudu['isread'],
                'isdone'       => (int) $tudu['isdone'],
                'iscycle'      => !empty($tudu['cycleid']),
                'ispriority'   => (int) $tudu['priority'],
                'istudugroup'  => (empty($tudu['nodetype']) || $tudu['nodetype'] == 'leaf') ? false : true,
                'ispassword'   => !empty($tudu['password']),
                'percent'      => (int) $tudu['percent'],
                'status'       => (int) $tudu['status'],
                'from'         => array('username' => $from, 'truename' => $fromName),
                'to'           => $to,
                'lastposttime' => (int) $tudu['lastposttime'],
                'isdraft'      => (int) $tudu['isdraft'],
                'starttime'    => (int) $tudu['starttime'],
                'endtime'      => (int) $tudu['endtime'],
                'mark'         => $mark,
                'replynum'     => (int) $tudu['replynum'],
                'attachnum'    => (int) $tudu['attachnum'],
                'labels'       => explode(',', $tudu['labels']),
                'selfstatus'   => (int) $tudu['selfstatus']
            );

            // 已超期
            if ($tudu['type'] == 'task' && !empty($tudu['endtime']) && $tudu['status'] < 2) {
                $item['isoutdate'] = (int) $tudu['endtime'] < time() ? 1 : 0;
            }

            if (!empty($query['labelid']) && $query['labelid'] == '^td') {
                if ($tudu['type'] == 'discuss' || $tudu['type'] == 'meeting') {
                    continue ;
                }

                if ($tudu['type'] == 'notice' && !in_array('^e', $item['labels'])) {
                    continue ;
                }

                if ((($isAccepter && !$tudu['accepttime'])
                    || ($isSender && !$tudu['lastaccepttime'])
                    || in_array('^e', $item['labels']))
                    && !$item['istudugroup']
                    && $tudu['status'] < Dao_Td_Tudu_Tudu::STATUS_DONE)
                {
                    if (!$tudu['isread']) {
                        $unreadNum ++;
                    }

                    $ret[] = $item;
                }
                continue ;
            }

            if (!empty($query['labelid']) && $query['labelid'] == '^c') {
                if (!$isAccepter && !$isSender && $tudu['type'] == 'task' && !in_array('^e', $item['labels'])) {

                    if (!$tudu['isread']) {
                        $unreadNum ++;
                    }

                    $ret[] = $item;
                }
                continue ;
            }

            $ret[] = $item;
        }

        if (($query['labelid'] == '^td' || $query['labelid'] == '^c') && null != $label) {
            $label['unreadnum'] = $unreadNum;
            $label['totalnum']  = count($ret);
        }

        if (null !== $label) {
            $this->view->label = $label;
        }

        $this->view->code  = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->tudus = $ret;
    }

    /**
     * 查看图度详细信息
     */
    public function infoAction()
    {
        $tuduId       = $this->_request->getQuery('tuduid');           // 指定图度ID
        $isHtml       = (boolean) $this->_request->getQuery('ishtml');    // 是否直接输出html的图度内容
        $lastPostTime = (int) $this->_request->getQuery('lastposttime');
        $markRead     = (boolean) $this->_request->getQuery('markread');
        $password     = trim($this->_request->getQuery('password'));

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudu    = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        if ($tudu->password && $tudu->password !== $password) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
            $this->view->message = 'You could not access this tudu';
            return ;
        }

        $tudu = $tudu->toArray();

        if (!$isHtml) {
            // 匹配内容中的图片
            $imgs = array();
            preg_match_all('/<img[^>]+src="([^"]+)"[^>]+\/>/i', $tudu['content'], $imgs);

            $tudu['images']  = $imgs[1];
            $tudu['content'] = str_replace(array("\n", "\r", "\t", ' '), array('', '', '', ''), $tudu['content']);
            $tudu['content'] = str_replace(array('&nbsp;', '<br>', '<br />', '</p>', '</tr>', '</td>'), array(' ', "\n", "\n", "\n", "\n", "\t"), $tudu['content']);
            $tudu['content'] = strip_tags($tudu['content']);
        }

        if ($markRead) {
            $daoTudu->markRead($tuduId, $this->_user->uniqueId);
        }

        $return = array(
            'orgid'     => $tudu['orgid'],
            'boardid'   => $tudu['boardid'],
            'tuduid'    => $tudu['tuduid'],
            'type'      => $tudu['type'],
            'flowid'    => $tudu['flowid'],
            'classid'   => $tudu['classid'],
            'classname' => $tudu['classname'],
            'subject'   => $tudu['subject'],
            'from'      => array('username' => $tudu['from'][3], 'truename' => $tudu['from'][0]),
            'to'        => array(),
            'cc'        => array(),
            'bcc'       => array(),
            'percent'   => $tudu['flowid'] ? -1 : $tudu['percent'],
            'starttime' => (int) $tudu['starttime'],
            'endtime'   => (int) $tudu['endtime'],
            'location'  => isset($tudu['location']) ? $tudu['location'] : null,
            'createtime'   => (int) $tudu['createtime'],
            'lastposttime' => (int) $tudu['lastposttime'],
            'istudugroup'  => (int) $tudu['istudugroup'],
            'content'  => $tudu['content'],
            'replynum' => $tudu['replynum'],
            'images'   => $tudu['images'],
            'labels'   => $tudu['labels']
        );

        if ($tudu['type'] == 'meeting') {
            $daoMeeting = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Meeting', Tudu_Dao_Manager::DB_TS);

            $meeting = $daoMeeting->getMeeting(array('tuduid' => $tudu['tuduid']));

            if (null !== $meeting) {
                $return['location'] = $meeting->location;
            }
        }

        $arr = array('to', 'cc', 'bcc');
        foreach ($arr as $k) {
            if (!empty($tudu[$k])) {
                $items = array();
                foreach ($tudu[$k] as $item) {
                    $items[] = array(
                        'username' => $item[3],
                        'truename' => $item[0]
                    );
                }

                $return[$k] = $items;
            }
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;

        $isReceiver = ($this->_user->uniqueId == $tudu['uniqueid']) && count($tudu['labels']);
        $isAccepter = in_array($this->_user->address, $tudu['accepter'], true)
                    || in_array($this->_user->userName, $tudu['accepter'], true);
        $isSender   = in_array($tudu['sender'], array($this->_user->address, $this->_user->userName), true);

        $boards = $this->getBoards(false);
        $board = $boards[$tudu['boardid']];

        $isModerator      = array_key_exists($this->_user->userId, $board['moderators']);
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        $permission = array(
            'reply'      => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_POST) || $isReceiver,
            'accept'     => $isAccepter && !$tudu['selfaccepttime'] && !$tudu['isdone'] && empty($tudu['flowid']),
            'reject'     => $isAccepter && (!$isSender || $tudu['flowid']) && $tudu['selftudustatus'] < Dao_Td_Tudu_Tudu::STATUS_DONE && $tudu['selfpercent'] < 100,
            'modify'     => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU) && ($isSender || $isModerator || $isSuperModerator) && !$tudu['isdone'],
            'agree'      => in_array('^e', $tudu['labels']),
            'disagree'   => in_array('^e', $tudu['labels']),
            'forward'    => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU) && $isAccepter,
            'cancel'     => $isSender && !$tudu['isdone'],
            'confirm'    => $isSender && !$tudu['isdone'],
            'disconfirm' => $tudu['type'] != 'discuss' && $isSender && $tudu['isdone'],
            'ignore'     => !empty($tudu['labels']) && !in_array('^g', $tudu['labels']) && !$tudu['isdone'],
            'mark'       => in_array('^all', $tudu['labels']),
            'inbox'      => in_array('^g', $tudu['labels']),
            'delete'     => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU) && ($isSender || $isModerator || $isSuperModerator),
            'claim'      => false,
            'close'      => $tudu['type'] == 'discuss' && $isSender
        );

        if ($tudu['orgid'] != $this->_user->orgId || (empty($tudu['labels']) && (!$isModerator && !$isSuperModerator))) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        if (empty($tudu['header']) || !isset($tudu['header']['client-type']) || $tudu['header']['client-type'] != 'iOS') {
            $permission['modify'] = false;
        }

        if ($tudu['acceptmode']) {
            $permission['claim']  = true;
            $permission['accept'] = $permission['reject'] = $permission['forward'] = false;
        }

        $permission['progress'] = $tudu['type'] == 'task' && $permission['reply'] && $isAccepter && $tudu['selfaccepttime'];

        if ($tudu['istudugroup']) {
            /* @var $daoTuduGroup Dao_Td_Tudu_Group */
            $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

            if ($daoTuduGroup->getChildrenCount($tudu['tuduid'], $tudu->_user->uniqueId)) {
                $permission['forward'] = $permission['progress'] = false;
            }
        }

        // 图度任务
        if ($tudu['type'] == 'task') {
            if ($isAccepter || $isSender) {
                $permission['ignore'] = false;
            }

            // 已确认完成时，禁止回复的权限
            if ($tudu['isdone']) {
                $permission['reply']  = false;
            }

            // 已完成（完成、取消、拒绝）时，禁止操作的权限
            if ($isAccepter && $tudu['selftudustatus'] >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $permission['cancel']   = false;
                $permission['accept']   = false;
                $permission['reject']   = false;
            }

            if ($tudu['status'] > Dao_Td_Tudu_Tudu::STATUS_DONE) {
                $permission['accept']   = false;
                $permission['reject']   = false;
                $permission['forward']  = false;
            }

            if ($isSender) {
                if ($tudu['status'] >= Dao_Td_Tudu_Tudu::STATUS_DONE) {
                    $permission['accept']   = false;
                    $permission['reject']   = false;
                    $permission['cancel']   = false;
                    $permission['forward']  = false;
                } else {
                    $permission['confirm']  = false;
                }
            }

            if ($isAccepter && $tudu['selfpercent'] == 100 && $tudu['isdone'] === false) {
                $permission['forward'] = true;
            }

            //循环周期任务 已接受的不允许转发
            if ($tudu['special'] == 1 && null !== $tudu['accepttime']) {
                $permission['forward']  = false;
            }
            // 屏蔽新手任务的的权限
            // 工作流没有分工及转发权限
            if ($tuduId == 'newbie-' . $this->_user->uniqueId || $tudu['flowid']) {
                $permission['forward'] = false;
            }

        // 会议
        } elseif ($tudu['type'] == 'meeting') {
            if ($isAccepter || $isSender) {
                $permission['ignore'] = false;
            }

            $permission['forward'] = false;
            $permission['confirm'] = false;

            if ($tudu['isdone']) {
                $permission['reply']  = false;
            }

            if ($tudu['selftudustatus'] > Dao_Td_Tudu_Tudu::STATUS_DONE || $tudu['isdone']) {
                $permission['reject'] = false;
                $permission['accept'] = false;
            }
        } else {
            $permission['accept'] = false;
            $permission['reject'] = false;
            $permission['cancel'] = false;
            $permission['forward'] = false;
            $permission['confirm'] = false;
            $permission['reply']  &= !$tudu['isdone'];
        }

        if ($tudu['type'] == 'notice') {
            $permission['reply']= false;
        }

        if ($tudu['type'] == 'discuss') {
            $permission['close'] = $tudu['type'] == 'discuss' && $isSender && !$tudu['isdone'];
            $permission['reopen'] = $tudu['type'] == 'discuss' && $isSender && $tudu['isdone'];
        }

        // 已分工不能更新任务进度
        if ($tudu['istudugroup'] && $permission['forward']) {
            if (Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS)->getChildrenCount($tuduId, $this->_user->uniqueId) > 0) {
                $permission['forward']  = false;
            }
        }

        // 读取跟踪列表
        $percentMap   = array();
        if ($tudu['type'] == 'task' || $tudu['type'] == 'meeting') {
            $accepters = $daoTudu->getAccepters($tuduId);

            $acc = array();
            foreach ($accepters as $item) {
                list($username, $truename) = explode(' ', $item['accepterinfo']);
                $acc[] = array(
                    'unqueid'  => $item['uniqueid'],
                    'username' => $username,
                    'truename' => $truename,
                    'percent'  => (int) $item['percent'],
                    'status'   => (int) $item['tudustatus'],
                    'isread'   => (boolean) $item['isread'],
                    'accepttime' => $item['tudustatus'] >= 2 ? -1 : (int) $item['accepttime']
                );

                $percentMap[$username] = (int) $item['percent'];
            }

            $return['accepters'] = $acc;
        }

        // 工作流权限判断
        if (($tudu['type'] == 'task' || $tudu['type'] == 'notice') && $tudu['stepnum'] > 0) {

            /* @var $daoFlow Dao_Td_Tudu_Flow */
            $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Flow', Tudu_Dao_Manager::DB_TS);

            $flow = $daoFlow->getFlow(array('tuduid' => $tuduId));

            if (null !== $flow) {
                $steps = $flow->steps;

                $isDisagree = false;
                foreach ($steps as $step) {
                    foreach ($step['section'] as $sec) {
                        foreach ($sec as $u) {
                            if ($step['type'] == 1 && $u['status'] == 3) {
                                $isDisagree = true;
                            }
                        }
                    }
                }
            }

            $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

            $users     = $daoStep->getTuduStepUsers($tuduId);

            // 当前是否最后一步骤，且是否执行
            $isLastExecute = false;
            if ($tudu['type'] == 'task') {
                $tempUsers = $users;

                do {
                    $arr = array_pop($tempUsers);

                    if (!$arr) {
                        break;
                    }

                    if ($arr['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && $arr['stepid'] != $tudu['stepid']) {
                        if ($tudu['stepid'] == '^end') {
                            $isLastExecute = true;
                        }
                        break;
                    }

                    if ($arr['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE && ($arr['stepid'] == $tudu['stepid'] || $tudu['stepid'] == '^end')) {
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
            $currentStepId= null;
            $tempStepId   = null;
            $tempType     = null;
            $isSynchro    = false;
            $isLastExecute= false;
            $isDisagreed  = false;
            $index        = null;
            $isExecute    = false;

            foreach ($users as $u) {

                // 是否有不同意的人
                if ($u['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $u['status'] > 2) {
                    $isDisagreed = true;
                }

                // 是否同时审批
                if ($u['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE && $u['stepid'] == $tudu['stepid']) {
                    if ($index == $u['processindex']) {
                        $sameReview = true;
                    }
                    $index = $u['processindex'];

                    $permission['accept'] = $permission['reject'] = false;
                }

                // 非审批步骤
                if ($u['stepid'] == $tudu['stepid'] && $u['type'] != Dao_Td_Tudu_Step::TYPE_EXAMINE) {
                    $permission['agree'] = $permission['disagree'] = false;
                }

                // 审批
                if ($u['stepid'] == $tudu['stepid'] && $u['uniqueid'] == $this->_user->uniqueId
                    && $u['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE)
                {
                    $permission['agree'] = $permission['disagree'] = $u['status'] == 1;
                }

                // 格式化步骤列表
                list($userName, $trueName) = explode(' ', $u['userinfo']);

                $user = array(
                    'username' => $userName,
                    'truename' => $trueName,
                    'status'   => (int) $u['status'],
                    'percent'  => -1
                );

                if ($u['stepid'] == $tudu['stepid'] && $u['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                    $isExecute = true;
                    $user['percent'] = isset($percentMap[$userName]) ? $percentMap[$userName] : 0;
                } else {
                    $isExecute = false;
                }

                if ($currentStepId != $u['stepid']) {
                    $currentIndex  = null;
                    $currentStepId = $u['stepid'];

                    if ($currentIndex != $u['processindex']) {
                        $currentIndex = $u['processindex'];
                        $steps[] = array(
                            'type'  => $u['type'],
                            'users' => array($user)
                        );
                    } else {
                        end($steps);
                        $key = key($steps);
                        $steps[$key]['users'][] = $user;
                    }
                } else {

                    if ($currentIndex != $u['processindex']) {
                        $currentIndex = $u['processindex'];
                        $steps[] = array(
                            'type'  => $u['type'],
                            'users' => array($user)
                        );
                    } else {
                        end($steps);
                        $key = key($steps);
                        $steps[$key]['users'][] = $user;
                    }
                }

                if ($u['stepid'] == $tudu['stepid'] && $u['type'] == Dao_Td_Tudu_Step::TYPE_EXECUTE) {
                    $user['percent'] = isset($percentMap[$userName]) ? $percentMap[$userName] : 0;
                }

                if ($tudu['flowid'] && $u['stepid'] == $tudu['stepid']) {
                    if (null === $currentIndex && $u['status'] < 2) {
                        $currentIndex = $u['processindex'];
                    }

                    if ($currentIndex == $u['processindex']) {
                        $currentUser[] = $u['userinfo'];
                    }

                    $steptype = isset($user['type']) && $user['type'] == Dao_Td_Tudu_Step::TYPE_EXAMINE;
                }

                $return['steps'] = $steps;
            }

            if (!$isExecute && $tudu['type'] == 'task') {
                $to = array();
                $isExceed  = false;
                $procIndex = null;
                $fExecStepId = null;
                foreach ($users as $u) {
                    if (!$isExceed && $u['stepid'] == $tudu['stepid']) {
                        $isExceed = true;
                    }

                    if ($isExceed && $u['type'] == 0) {
                        if (null !== $procIndex && $procIndex != $u['processindex']) {
                            break ;
                        }

                        if ($fExecStepId === null) {
                            $fExecStepId = $u['stepid'];
                        } elseif ($fExecStepId != $u['stepid']) {
                            continue ;
                        }

                        list($userName, $trueName) = explode(' ', $u['userinfo'], 2);

                        $to[] = array('username' => $userName, 'truename' => $trueName);

                        $procIndex = (int) $u['processindex'];
                    }
                }

                if (!empty($to)) {
                    $return['to'] = $to;
                }
            }

            if ($tudu['type'] == 'notice' && !empty($return['steps'])) {
                if (!$isSender && !in_array('^v', $return['labels']) && !in_array('^e', $return['labels'])) {
                    unset($return['steps']);
                } else {

                    $receiveStep = array(
                        'type'  => 0,
                        'users' => array()
                    );

                    foreach ($return['cc'] as $rec) {
                        $receiveStep['users'][] = array(
                            'username' => $rec['username'],
                            'truename' => $rec['truename'],
                            'percent'  => -1
                        );
                    }

                    $return['steps'][] = $receiveStep;
                }
            }

            // 有不同意的，不允许更新进度
            if ($isDisagreed && !$tudu['flowid']) {
                $permission['accept'] = $permission['reject'] = $permission['progress'] = false;
            }

            if ($tudu['type'] == 'task' && $isSynchro && !$isLastExecute && !$tudu['flowid']) {
                $permission['forward'] = false;
            }
        }

        foreach ($permission as $k => $v) {
            $permission[$k] = (int) $v;
        }

        if ($lastPostTime && $tudu->lastPostTime == $lastPostTime) {
            $this->view->message = 'No modified';    // 未更新
            $this->view->tudu    = null;
        }

        if ($permission['agree'] || $permission['disagree'] || $permission['percent'] || $permission['accept']) {
            $permission['ignore'] = false;
        }

        $return['permission'] = $permission;

        $this->view->tudu = $return;
        //$this->view->permission = $permission;
    }

    /**
     *
     */
    public function detailAction()
    {
        return $this->infoAction();
    }

    /**
     * 获取图度执行人列表
     */
    public function accepterAction()
    {
        $tuduId  = $this->_request->getQuery('tuduid');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudu    = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        $accepters = $daoTudu->getAccepters($tuduId);

        $ret = array();
        foreach ($accepters as $item) {
            list($username, $truename) = explode(' ', $item['accepterinfo']);
            $ret[] = array(
                'unqueid'  => $item['uniqueid'],
                'username' => $username,
                'truename' => $truename,
                'percent'  => (int) $item['percent'],
                'status'   => (int) $item['tudustatus'],
                'isread'   => (boolean) $item['isread'],
                'accepttime' => (int) $item['accepttime']
            );
        }

        $this->view->code      = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->accepters = $ret;
    }

    /**
     * 获取图度步骤列表
     */
    public function stepAction()
    {
        $tuduId = $this->_request->getQuery('tuduid');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudu    = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);

        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        /* @var $daoStep Dao_Td_Tudu_Step */
        $daoStep = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Step', Tudu_Dao_Manager::DB_TS);

        $steps = $daoStep->getSteps(array('tuduid' => $tuduId));

        $users = array();
        /*foreach ($steps as $step) {

        }*/

        $this->view->assign(array(
            'code'  => TuduX_OpenApi_ResponseCode::SUCCESS,
            'steps' => $steps->toArray()
        ));
    }

    /**
     * 图度分工列表接口
     */
    public function childrenAction()
    {
        $tuduId = $this->_request->getQuery('tuduid');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        $condition = array(
            'parentid' => $tuduId,
            'uniqueid' => $this->_user->uniqueId
        );

        $tudus = $daoTudu->getGroupTudus($condition)->toArray();

        $ret = array();
        foreach ($tudus as $tudu) {
            $to = array();
            $isSender   = $tudu['sender'] == $this->_user->userName;
            $isAccepter = false;

            foreach ($tudu['to'] as $item) {
                if ($item[0] == $this->_user->userName) {
                    $isAccepter = true;
                }

                $to[] = array('username' => $item[3], 'truename' => $item[0]);
            }

            $mark = null;

            if ($tudu['type'] == 'task' && $tudu['status'] >= 2 && $isSender && !$tudu['isdone']) {
                $mark = 'confirm';
            }

            if (!$mark && in_array('^e', $tudu['labels'])) {
                $mark = 'review';
            }

            if (!$mark && $isAccepter && empty($tudu['accepttime']) && in_array($tudu['type'], array('task', 'meeting'))) {
                $mark = 'accept';
            }

            $item = array(
                'orgid'        => $tudu['orgid'],
                'tuduid'       => $tudu['tuduid'],
                'boardid'      => $tudu['boardid'],
                'type'         => $tudu['type'],
                'subject'      => $tudu['subject'],
                'isread'       => (boolean) $tudu['isread'],
                'isdone'       => (boolean) $tudu['isdone'],
                'iscycle'      => !empty($tudu['cycleid']),
                'ispriority'   => (boolean) $tudu['priority'],
                'status'       => (int) $tudu['status'],
                'percent'      => (int) $tudu['percent'],
                'from'         => array('username' => $tudu['from'][3], 'truename' => $tudu['from'][0]),
                'to'           => $to,
                'lastposttime' => (int) $tudu['lastposttime'],
                'isdraft'      => (boolean) $tudu['isdraft'],
                'starttime'    => (int) $tudu['starttime'],
                'endtime'      => (int) $tudu['endtime'],
                'mark'         => $mark,
                'attachnum'    => (int) $tudu['attachnum']
            );

            // 已超期
            if ($tudu['type'] == 'task' && !empty($tudu['endtime']) && $tudu['status'] < 2) {
                $item['isoutdate'] = (int) $tudu['endtime'] < time() ? 1 : 0;
            }

            $ret[] = $item;
        }

        $this->view->code  = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->tudus = $ret;
    }

    /**
     * 校验图度访问密码
     */
    public function verifyPasswordAction()
    {
        $tuduId = $this->_request->getPost('tuduid');
        $pwd    = $this->_request->getPost('password');

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $tudu = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        if($tudu->password != $pwd) {
            $this->view->code    = '';
            $this->view->message = 'Password error';
            return ;
        }

        $this->view->code  = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 获取用户有权限的版块数据
     *
     * @param boolean $format
     * @param boolean $isModerator
     * @return array
     */
    public function getBoards($format = true, $isModerator = false)
    {
        $records = $this->_loadBoards();

        if (!$format) {
            return $records;
        }

        $boards = array();
        foreach($records as $key => $val) {
            if ($val['issystem']) continue;
            if ($val['parentid']) {

                if (!array_key_exists($val['parentid'], $records)) {
                    continue;
                }

                // 版主下的板块
                if ($isModerator) {
                    if (!array_key_exists($this->_user->userId, $val['moderators'])// 是否版主
                    && !($val['ownerid'] == $this->_user->userId)// 是否创建者
                    && !array_key_exists($this->_user->userId, $records[$val['parentid']]['moderators'])// 是否上级分区版主
                    ) {
                        continue;
                    }
                } else {
                    if (!in_array('^all', $val['groups'])
                    // 参与人
                    && !(in_array($this->_user->userName, $val['groups'], true) || in_array($this->_user->address, $val['groups'], true))

                    // 参与人（群组）
                    && !sizeof(array_uintersect($this->_user->groups, $val['groups'], "strcasecmp"))

                    // 是否版主
                    && !array_key_exists($this->_user->userId, $val['moderators'])

                    // 是否创建者
                    && !($val['ownerid'] == $this->_user->userId)

                    // 是否上级分区版主
                    && !array_key_exists($this->_user->userId, $records[$val['parentid']]['moderators'])
                    ) {
                        continue;
                    }
                }

                $records[$val['parentid']]['children'][] = &$records[$key];

            } else {
                $boards[$val['boardid']] = &$records[$val['boardid']];
            }
        }
        unset($records);
        // 移除非版主的分区
        if ($isModerator) {
            foreach ($boards as $key => $board) {
                if (!isset($board['children'])) {
                    unset($boards[$key]);
                }
            }
        }

        return $boards;
    }

    /**
     *
     */
    private function _loadBoards()
    {
        if (null === $this->_boards) {
            /* @var $boardDao Dao_Td_Board_Board */
            $boardDao = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
            $this->_boards = $boardDao->getBoards(array(
                'orgid'    => $this->_user->orgId,
                'uniqueid' => $this->_user->uniqueId
            ), null, 'ordernum DESC')->toArray('boardid');
        }
        return $this->_boards;
    }
}