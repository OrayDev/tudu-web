<?php
/**
 * Board Controller
 *
 * @author Hiro
 * @version $Id: BoardController.php 2790 2013-03-22 02:31:43Z chenyongfa $
 */

class BoardController extends TuduX_Controller_Base
{
    public function init()
    {
        /* Initialize action controller here */
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'board', 'tudu'));

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }

        $this->view->LANG = $this->lang;
    }

    public function indexAction()
    {
        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        $boardId = $this->_request->getParam('bid');
        $classId = $this->_request->getParam('cid');

        //$orgAttrs = $this->_user->getOrgAttrs();
        //$this->view->org = $orgAttrs;

        $this->view->boardnav = $this->getBoardNav($boardId);
        $this->view->iscreate = $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_BOARD, true);

        if ($boardId) {

            $boards = $this->getBoards(false);

            if (!isset($boards[$boardId]) || !in_array($boards[$boardId]['status'], array(0, 2))) {
                return Oray_Function::alert($this->lang['board_not_exists'], '/board/');
            }

            $board = $boards[$boardId];

            // 板块属性
            $attribute = array();
            if ($board['privacy']) {
                $attribute[] = $this->lang['board_privacy'];
            }
            if ($board['protect']) {
                $attribute[] = $this->lang['disable_edit'];
            }
            if ($board['needconfirm']) {
                $attribute[] = $this->lang['tudu_need_confirm'];
            }
            if ($board['flowonly']) {
                $attribute[] = $this->lang['flow_only'];
            }
            $board['attribute'] = $attribute;
        }

        if (isset($board) && $board['type'] == 'board') {

            // 继承上级分区权限
            if (!empty($board['parentid'])
                && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])) {
                $isSuperModerator = true;
            } else {
                $isSuperModerator = false;
            }

            $isOwner     = ($this->_user->userId == $board['ownerid']);
            $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
            $inGroup     = in_array($this->_user->userName, $board['groups'], true) || sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));

            $access = array(
                'modify' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_BOARD) && ($isModerator || $isSuperModerator || $isOwner),
                'deletetudu' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU) && ($isModerator || $isSuperModerator),
                'movetudu' => $isModerator || $isSuperModerator,
                'delete' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_BOARD) && $isSuperModerator,
                'close'  => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CLOSE_BOARD) && $isSuperModerator,
                'clear'  => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU) && ($isModerator || $isSuperModerator),
                'access' => $isModerator || $isSuperModerator || $inGroup || $isOwner
            );
            //var_dump($this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CLOSE_BOARD));
            if (!$access['access']) {
                return Oray_Function::alert($this->lang['perm_deny_visit'], '/board/');
            }

            /* @var $daoClass Dao_Td_Tudu_Class */
            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            $classes  = $daoClass->getClassesByBoardId($this->_user->orgId, $boardId, 'ordernum ASC');

            $page = max(1, (int) $this->_request->getQuery('page'));
            $pageSize = $this->_user->option['pagesize'];

            /* @var $tuduDao Dao_Td_Tudu_Tudu */
            $tuduDao = $this->getDao('Dao_Td_Tudu_Tudu');

            $condition = array(
                'uniqueid' => $this->_user->uniqueId,
                'orgid' => $this->_user->orgId,
                'boardid' => $boardId
            );
            $query = array('bid' => $boardId);

            if (!empty($classId)) {
                $condition['classid'] = $classId;
                $query['cid'] = $classId;
            }

            if ($inGroup && !($isModerator || $isSuperModerator)) {
                $condition['privacy'] = 1;
            }

            // 临时人员
            if ($this->_user->status == 2) {
                $condition['self'] = true;
            }

            $sort = array(
               'istop' => 'DESC',
               'lastposttime' => 'DESC'
            );
            $tudus = $tuduDao->getBoardTuduPage($condition, $sort, $page, $pageSize);

            // 处理快捷板块
            $attentions = array('children' => array());
            foreach ($boards as $item) {
                if ($item['isattention'] && $item['status'] != 2) {
                    $attentions['children'][] = $item;
                }
            }

            $boards = $this->getBoards(true);

            if (!empty($attentions['children'])) {
                $attentions['type'] = 'zone';
                $attentions['boardname'] = $this->lang['my_attention_board'];
                $boards = array_merge(array($attentions), $boards);
            }

            $this->view->pageinfo  = array(
                'query'       => $query,
                'currpage'    => $tudus->currentPage(),
                'pagecount'   => $tudus->pageCount(),
                'recordcount' => $tudus->recordCount(),
                'url'         => '/board'
            );

            $this->view->attentions = $attentions;
            $this->view->board  = $board;
            $this->view->access = $access;
            $this->view->tudus  = $tudus->toArray();
            $this->view->boards = $boards;
            $this->view->classes = $classes->toArray();

            $this->view->ismoderators = $isModerator || $isSuperModerator;

            $this->render('tudu');

            return;
        }

        $boards   = $this->getBoards();
        $stats    = $daoBoard->getBoardStats($this->_user->orgId);
        $cast     = $this->getCast();
        $myBoards = array();
        $showMore = false;

        // 我的板块
        if (!$boardId) {
            $attentionsBoards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);
        }

        foreach($boards as $key => &$val) {
            if ($val['type'] == 'zone' && !isset($val['children'])) {
                unset($boards[$key]);
                continue;
            }
            // 处理板块参与人
            foreach ($val['children'] as &$child) {
                $person = array();
                foreach ($child['groups'] as $item) {
                    if (strpos($item, '@') === false) {
                        if (isset($cast['groups'][$item])) {
                            $person[] = array($item, $cast['groups'][$item]['groupname']);
                        }
                    } else {
                        if (isset($cast['users'][$item])) {
                            $person[] =  array($item, $cast['users'][$item]['truename']);
                        }
                    }
                }
                $child['groupsperson'] = $person;

                // 我的板块
                if (!empty($attentionsBoards)) {
                    foreach($attentionsBoards as $board) {
                        if ($board['boardid'] == $child['boardid']) {
                            $myBoards[$board['ordernum']] = $child;
                        }
                    }
                }

                // 是否需要显示更多版块
                if ($child['status'] == 2 && !$showMore) {
                    $showMore = true;
                }
            }
        }

        if ($boardId) {
            if (!isset($boards[$boardId])) {
                return Oray_Function::alert($this->lang['perm_board_visit'], '/board/');
            }
            $boards = array($boardId => $boards[$boardId]);
        }

        if (!empty($myBoards)) {
            ksort($myBoards);
        }

        $this->view->boards   = $boards;
        $this->view->stats    = $stats;
        $this->view->myboards = $myBoards;
        $this->view->showmore = $showMore;
    }

    /**
     * 搜索表单
     */
    public function searchFormAction()
    {
        $boards = $this->getBoards();

        $this->view->boards = $boards;
    }

    /**
     * 搜索
     */
    public function searchAction()
    {
        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $tuduDao = $this->getDao('Dao_Td_Tudu_Tudu');

        $query = $this->_request->getQuery();

        $params = array();
        $condition = array(
            'uniqueid' => $this->_user->uniqueId,
            'orgid' => $this->_user->orgId,
            'priority' => 0 // 禁止搜索隐私类图度
            );

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

        if (!empty($query['type'])) {
            $condition['type'] = $query['type'];
            $params['type'] = $query['type'];
        }

        if (isset($query['status']) && $query['status'] != '') {
            $condition['status'] = (int) $query['status'];
            $params['status'] = $query['status'];
        }

        $boards = $this->getBoards();
        $boardIds = array();

        // 获取用户有权限浏览的版块ID
        foreach($boards as $zone) {
            if (!isset($zone['children'])) {
                continue;
            }
            foreach($zone['children'] as $board) {
                $boardIds[] = $board['boardid'];
            }
        }

        if (!empty($query['bid'])) {
            $bid = (array) $query['bid'];
            $bid = array_intersect($bid, $boardIds);

            $condition['boardid'] = $bid;
            $params['bid'] = $bid;
        } else {
            $condition['boardid'] = $boardIds;
        }

        // 天
        if (!empty($query['time'])) {
        	$params['time'] = $query['time'];
            $time = time() - 86400 * (int) $query['time'];
            if ($query['timetype']) {
                $condition['createtime'] = array('end' => $time);
            } else {
                $condition['createtime'] = array('start' => $time);
            }
        }

        $sortType = $this->_request->getQuery('sorttype');
        $sortAsc  = $this->_request->getQuery('sortasc');

        $params['sorttype'] = $sortType;
        $params['sortasc']  = $sortAsc;

        $sort = $sortType . ' ' . ($sortAsc ? 'ASC' : 'DESC');

        $page = max(1, (int) $this->_request->getQuery('page'));
        $pageSize = $this->_user->option['pagesize'];

        $tudus = $tuduDao->getBoardTuduPage($condition, $sort, $page, $pageSize);

        $this->view->pageinfo  = array(
            'query'       => $params,
            'currpage'    => $tudus->currentPage(),
            'pagecount'   => $tudus->pageCount(),
            'recordcount' => $tudus->recordCount(),
            'url'         => '/board/search'
        );

        $this->view->tudus  = $tudus->toArray();
        $this->view->params = $params;
        $this->view->query  = http_build_query($params);
    }


    /**
     * 创建分区
     */
    public function createAction()
    {
        $post = $this->_request->getPost();

        $boardName  = $post['name'];
        $parentId   = $post['parentid'];
        $moderators = $post['moderators'];
        $groups     = $post['groups'];

        $classes    = array();

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_BOARD)) {
            return $this->json(false, $this->lang['perm_deny_create']);
        }

        if (!$boardName) {
            return $this->json(false, $this->lang['params_invalid_name']);
        }

        if (!$parentId) {
            return $this->json(false, $this->lang['params_invalid_parentid']);
        }

        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        if (!$daoBoard->existsBoard($this->_user->orgId, $parentId)) {
            return $this->json(false, $this->lang['parent_zone_not_exists']);
        }

        $boardId = Dao_Td_Board_Board::getBoardId($this->_user->orgId);

        $condition = array(
            'orgid' => $this->_user->orgId,
            'type'  => 'board',
            'parentid' => $parentId
        );
        $orderNum = $daoBoard->getBoardMaxOrderNum($condition);

        $params = array(
            'orgid'     => $this->_user->orgId,
            'boardid'   => $boardId,
            'type'      => 'board',
            'boardname' => $boardName,
            'parentid'  => $parentId,
            'ownerid'   => $this->_user->userId,
            'memo'      => $post['memo'],
            'ordernum' => $orderNum + 1
        );

        // 管理人员
        //$daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        if (!$moderators) {
            $params['moderators'] = $this->_user->userId . ' ' . $this->_user->trueName;
        } else {
            $params['moderators'] = $moderators;
        }

        if (!$groups) {
            $params['groups'] = '^all';
        } else {
            $params['groups'] = $groups;
        }

        if (isset($post['privacy'])) {
            $params['privacy'] = (int) $post['privacy'];
        }

        if (isset($post['protect'])) {
            $params['protect'] = (int) $post['protect'];
        }

        if (isset($post['isclassify'])) {
            $params['isclassify'] = (int) $post['isclassify'];
        }

        if (isset($post['needconfirm'])) {
            $params['needconfirm'] = (int) $post['needconfirm'];
        }

        if (isset($post['flowonly'])) {
            $params['flowonly'] = (int) $post['flowonly'];
        }

        $ret = $daoBoard->createBoard($params);

        if (!$ret) {
            return $this->json(false, $this->lang['board_create_failure']);
        }

        // 主题分类
        if (!empty($post['newclass']) && is_array($post['newclass'])) {
            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            $orderNum = 1;
            foreach ($post['newclass'] as $class) {
                if (empty($post['classname-' . $class])) continue ;

                $daoClass->createClass(array(
                    'orgid'     => $this->_user->orgId,
                    'boardid'   => $boardId,
                    'classid'   => Dao_Td_Tudu_Class::getClassId(),
                    'classname' => $post['classname-' . $class],
                    'ordernum'  => (int) $post['ordernum-' . $class]
                ));
            }
        }

        // 创建模板
        if(!empty($post['templates'])) {

            $daoTemplate = $this->getDao('Dao_Td_Tudu_Template');

            if (!empty($post['template']) && is_array($post['template'])) {
                foreach($post['template'] as $number) {
                    if (empty($post['tplname-' . $number])) continue ;

                    $tplParams = array(
                        'orgid'      => $this->_user->orgId,
                        'boardid'    => $boardId,
                        'templateid' => Dao_Td_Tudu_Template::getTemplateId(),
                        'creator'    => $this->_user->uniqueId,
                        'name'       => $post['tplname-' . $number],
                        'content'    => $post['tplcontent-' . $number],
                        'ordernum'  => (int) $post['tplordernum-' . $number]
                    );
                    if(!$daoTemplate->createTemplate($tplParams)) {
                        continue ;
                    }
                }
            }
        }

        $this->_writeLog(Dao_Td_Log_Log::TYPE_BOARD, $boardId, Dao_Td_Log_Log::ACTION_CREATE, $params);

        $config  = $this->bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
        $httpsqs->put(implode(' ', array(
            'board',
            'create',
            '',
            http_build_query(array())
        )), 'notify');

        return $this->json(true, $this->lang['board_create_success'], $boardId);
    }

    /**
     *
     * 编辑
     */
    public function modifyAction()
    {
        $boardId = $this->_request->getQuery('bid');
        $board   = array();
        $action  = 'create';

        $daoBoard = $this->getDao('Dao_Td_Board_Board');
        $daoTemplate = $this->getDao('Dao_Td_Tudu_Template');
        if ($boardId) {

            $board = $daoBoard->getBoard(array('orgid' => $this->_user->orgId, 'boardid' => $boardId));

            $templates = $daoTemplate->getTemplatesByBoardId($this->_user->orgId, $boardId, null, 'ordernum ASC');

            if (null === $board) {
                return Oray_Function::alert($this->lang['board_not_exists']);
            }

            $board = $board->toArray();

            $action = 'update';

            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            $classes  = $daoClass->getClassesByBoardId($this->_user->orgId, $board['boardid'], 'ordernum ASC');

            if($templates) {
                $this->view->templates = $templates->toArray();
            }

            $this->view->classes = $classes->toArray();
        } else {
            if (!$this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_BOARD, true)) {
                Oray_Function::alert($this->lang['perm_deny_create'], '/board/');
            }
        }

        $zones = $daoBoard->getBoards(array(
            'orgid' => $this->_user->orgId,
            'type'  => 'zone'
        ));

        // 权限
        $access = array(
            'discuss' => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_DISCUSS, true),
            'notice'  => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_NOTICE, true),
            'task'    => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_TUDU, true),
            'meeting' => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_MEETING, true),
            'board'   => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_BOARD, true),
            'upload'  => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_UPLOAD_ATTACH, true),
            'meeting' => $this->_user->getAccess()->assertEquals(Tudu_Access::PERM_CREATE_MEETING, true)
        );

        //$uploadOpt = $this->bootstrap->getOption('upload');

        $this->view->board  = $board;
        $this->view->zones  = $zones->toArray();
        $this->view->action = $action;
        $this->view->newwin = (boolean) $this->_request->getQuery('newwin');
        $this->view->access = $access;
    }


    /**
     * 更新分区
     */
    public function updateAction()
    {
        $post = $this->_request->getPost();

        $boardId    = $post['bid'];
        $parentId   = $post['parentid'];
        $moderators = $post['moderators'];
        $groups     = $post['groups'];

        if (!$boardId) {
            return $this->json(false, $this->lang['params_invalid_boardid']);
        }

        $daoBoard = $this->getDao('Dao_Td_Board_Board');
        $boards   = $this->getBoards(false);
        if (!$board = $daoBoard->getBoard(array('orgid' => $this->_user->orgId, 'boardid' => $boardId))) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        // 继承上级分区权限
        if (array_key_exists($this->_user->userId, $boards[$board->parentId]['moderators'])) {
            $isSuperModerator = true;
        } else {
            $isSuperModerator = false;
        }

        $isOwner     = ($this->_user->userId == $board->ownerId);

        $isModerator = array_key_exists($this->_user->userId, $board->moderators);
        $inGroup     = in_array($this->_user->userName, $board->groups, true) || sizeof(array_uintersect($this->_user->groups, $board->groups, "strcasecmp"));

        if ((!$isOwner && !$isModerator && !$isSuperModerator)
            || !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_BOARD))
        {
            return $this->json(false, $this->lang['perm_deny_update']);
        }

        if (empty($post['name'])) {
            return $this->json(false, $this->lang['params_invalid_name']);
        }

        if (!$parentId) {
            return $this->json(false, $this->lang['params_invalid_parentid']);
        }

        if (!$daoBoard->existsBoard($this->_user->orgId, $parentId)) {
            return $this->json(false, $this->lang['parent_zone_not_exists']);
        }

        $params = array(
            'boardname' => $post['name'],
            'parentid'  => $parentId,
            'memo'      => $post['memo'],
            'moderators'=> $moderators,
            'groups'    => $groups,
            'privacy'   => isset($post['privacy']) ? (int) $post['privacy'] : 0,
            'protect'   => isset($post['protect']) ? (int) $post['protect'] : 0,
            'isclassify'=> isset($post['isclassify']) ? (int) $post['isclassify'] : 0,
            'needconfirm'=> isset($post['needconfirm']) ? (int) $post['needconfirm'] : 0,
            'flowonly'  => isset($post['flowonly']) ? (int) $post['flowonly'] : 0
        );

        // 管理人员
        //$daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        if (!$params['moderators']) {
            $params['moderators'] = $this->_user->userId . ' ' . $this->_user->trueName;
        }

        $logDetail = $params;

        $ret = $daoBoard->updateBoard($this->_user->orgId, $boardId, $params);

        // 更新模板
        $daoTemplate = $this->getDao('Dao_Td_Tudu_Template');

        $templates = $daoTemplate->getTemplatesByBoardId($this->_user->orgId, $boardId, null, 'ordernum ASC');

        // 获取原模板ID
        $tplIds = array();
        foreach($templates as $template) {
            $tplIds[] = $template->templateId;
        }

        //if(!empty($post['templates'])) {
        // 更新
        $tplId = array();
        if (!empty($post['number']) && is_array($post['number'])) {
            foreach($post['number'] as $key) {

                $templateId = $post['tplid-' . $key];
                if(!$templateId) {
                    continue ;
                }
                $tplId[] = $templateId;

                $tplParams = array(
                    'name'       => $post['tplname-' . $key],
                    'content'    => $post['tplcontent-' . $key],
                    'ordernum'    => (int) $post['tplordernum-' . $key]
                );

                if(!$daoTemplate->updateTemplate($templateId, $boardId, $tplParams)) {
                    continue ;
                }
            }
        }
        // 删除
        $result = array_diff($tplIds, $tplId);
        foreach($result as $rs) {
            $daoTemplate->deleteTemplate($rs, $boardId);
        }

        // 创建
        if (!empty($post['template']) && is_array($post['template'])) {
            foreach($post['template'] as $number) {
                if (empty($post['tplname-' . $number])) continue ;

                $tplParams = array(
                    'orgid'      => $this->_user->orgId,
                    'boardid'    => $boardId,
                    'templateid' => Dao_Td_Tudu_Template::getTemplateId(),
                    'creator'    => $this->_user->uniqueId,
                    'name'       => $post['tplname-' . $number],
                    'content'    => $post['tplcontent-' . $number],
                    'ordernum'    => (int) $post['tplordernum-' . $number]
                );

                if(!$daoTemplate->createTemplate($tplParams)) {
                    continue ;
                }
            }
        }
        //}

        // 更新原有分类
        $daoClass = $this->getDao('Dao_Td_Tudu_Class');

        $classes = $daoClass->getClassesByBoardId($this->_user->orgId, $boardId)->toArray();
        $classCount = count($classes);
        foreach ($classes as $class) {
            $classId = $class['classid'];

            if (!isset($post['classname-' . $classId])) {
                $daoClass->deleteClass($this->_user->orgId, $classId);
                $classCount -- ;
            } else {
                $daoClass->updateClass($this->_user->orgId, $classId, array(
                    'classname' => $post['classname-' . $classId],
                    'ordernum'  => (int) $post['ordernum-' . $classId]
                ));
            }
        }

        // 主题分类
        if (!empty($post['newclass']) && is_array($post['newclass'])) {
            $daoClass = $this->getDao('Dao_Td_Tudu_Class');
            foreach ($post['newclass'] as $class) {
                if (empty($post['classname-' . $class])) continue ;

                $daoClass->createClass(array(
                    'orgid'     => $this->_user->orgId,
                    'boardid'   => $boardId,
                    'classid'   => Dao_Td_Tudu_Class::getClassId(),
                    'classname' => $post['classname-' . $class],
                    'ordernum'  => isset($post['ordernum-' . $class]) && $post['ordernum-' . $class] != ''
                                   ? (int) $post['ordernum-' . $class]
                                   : ++$classCount
                ));
            }
        }

        if (!$ret) {
            return $this->json(false, $this->lang['board_update_failure']);
        }

        $arrBoard = $board->toArray();
        foreach ($arrBoard['moderators'] as $uid => $name) {
            $arrBoard['moderators'][$uid] = $uid . ' ' . $name;
        }
        $arrBoard['moderators'] = implode("\n", $arrBoard['moderators']);
        $arrBoard['groups'] = implode("\n", $arrBoard['groups']);
        foreach ($logDetail as $key => $val) {
            if ($val == $arrBoard[$key]) {
                unset($logDetail[$key]);
            }
        }
        $this->_writeLog(Dao_Td_Log_Log::TYPE_BOARD, $boardId, Dao_Td_Log_Log::ACTION_UPDATE, $logDetail);

        $config  = $this->bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
        $httpsqs->put(implode(' ', array(
            'board',
            'update',
            '',
            http_build_query(array('bid' => $boardId))
        )), 'notify');

        return $this->json(true, $this->lang['board_update_success'], $boardId);
    }

    /**
     * 新建主题分类
     */
    public function classesAction()
    {
        $boardId = $this->_request->getParam('bid');
        $className = $this->_request->getParam('classname');

        if (!$boardId) {
            return $this->json(false, $this->lang['invalid_boardid']);
        }

        if (!$className) {
            return $this->json(false, $this->lang['invalid_class_name']);
        }

        $boards = $this->getBoards(false);
        if (!isset($boards[$boardId])) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $board = $boards[$boardId];
        // 继承上级分区权限
        if (array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])) {
            $isSuperModerator = true;
        } else {
            $isSuperModerator = false;
        }

        $isOwner     = ($this->_user->userId == $board['ownerid']);

        $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
        $inGroup     = in_array($this->_user->userName, $board['groups'], true) || sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));

        if ((!$isOwner && !$isModerator && !$isSuperModerator)
            || !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_BOARD))
        {
            return $this->json(false, $this->lang['perm_deny_update']);
        }

        /* @var $daoClass Dao_Td_Tudu_Class */
        $daoClass = $this->getDao('Dao_Td_Tudu_Class');

        $classId = $daoClass->createClass(array(
            'orgid'     => $this->_user->orgId,
            'boardid'   => $boardId,
            'classid'   => Dao_Td_Tudu_Class::getClassId(),
            'classname' => $className
        ));

        if (!$classId) {
            return $this->json(false, $this->lang['create_board_class_failure']);
        }

        return $this->json(true, $this->lang['create_board_class_success'], array('cid' => $classId, 'cn' => $className));
    }

    /**
     * 获取版块列表
     */
    public function boardListAction()
    {
        $boards = $this->getBoards();
        foreach($boards as $key => $val) {
            if ($val['type'] == 'zone' && !isset($val['children'])) {
                unset($boards[$key]);
            }
        }
        return $this->json(true, null, $boards);
    }

    /**
     * 管理版块
     */
    public function manageAction()
    {
        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board');
        $boards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);

        $access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN)
        );

        $this->view->access = $access;
        $this->view->boards = $boards;
    }

    /**
     * 分区板块排序
     */
    public function sortAction()
    {
        $boardId    = $this->_request->getPost('bid');
        $objBoardId = $this->_request->getPost('objid');
        $sort       = $this->_request->getPost('sort');

        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        // 整理数据
        $sort     = array();
        $zoneNum  = -1;
        $boardNum = -1;

        $boards = $this->getBoards();

        // 删除当前用户排序的版块
        $daoBoard->removeBoardSort($this->_user->uniqueId);
        foreach($boards as $key => $val) {
            if ($val['type'] == 'zone' && !isset($val['children'])) {
                continue;
            }

            $sort[$val['boardid']] = $zoneNum;
            foreach ($val['children'] as $child) {
                $sort[$child['boardid']] = $boardNum;
                $boardNum--;
            }
            $zoneNum--;
        }

        if (empty($sort[$objBoardId]) || empty($sort[$boardId])) {
            return $this->json(false);
        }

        // 交换排序号
        $objNum  = $sort[$objBoardId];
        $currNum = $sort[$boardId];

        $sort[$boardId]    = $objNum;
        $sort[$objBoardId] = $currNum;

        // 更新排序
        $ret = $daoBoard->updateBoardSort($this->_user->uniqueId, json_encode($sort));
        if ($ret) {
            $this->_boards = null;
        }

        return $this->json($ret);
    }

    /**
     * 关注分区
     */
    public function attentionAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $post = $this->_request->getPost();

        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        if (empty($post)) {
            $ret = $daoBoard->removeAllAttention($this->_user->orgId, $this->_user->uniqueId);
            if (!$ret) {
                return $this->json(false, $this->lang['add_board_attention_failure']);
            }

            return $this->json(true, $this->lang['add_board_attention_success'], null);
        }

        $member = $this->_request->getPost('member');
        if (!is_array($member)) {
            $member = (array) $member;
        }

        if (!empty($member) && isset($member)) {
            $post   = $this->_request->getPost();
            $sucess = 0;
            // 移除用户所有快捷版块
            $daoBoard->removeAllAttention($this->_user->orgId, $this->_user->uniqueId);
            foreach ($member as $idx) {
                $boardId  = str_replace('_', '^', $post['boardid-' . $idx]);
                $orderNum = $post['ordernum-' . $idx];

                if (!$boardId) {
                    continue;
                }

                $boards = $this->getBoards(false);
                if (!isset($boards[$boardId])) {
                    continue;
                }

                $board = $boards[$boardId];

                $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
                $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
                $inGroups    = in_array($this->_user->userName, $board['groups'], true) || sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));

                // 没有权限
                if (!$isSuperModerator && !$isModerator && !$inGroups) {
                    continue;
                }

                // 添加快捷版块
                $ret = $daoBoard->addAttention($this->_user->orgId, $boardId, $this->_user->uniqueId, $orderNum);
                if ($ret) {
                    $sucess ++;
                }
            }

            if ($sucess <= 0) {
                return $this->json(false, $this->lang['add_board_attention_failure']);
            }

            $attentionBoards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);
            return $this->json(true, $this->lang['add_board_attention_success'], $attentionBoards);

        } else {
            $type    = $this->_request->getPost('type');
            $boardId = $this->_request->getPost('bid');

            if (!$boardId) {
                return $this->json(false, $this->lang['board_not_exists']);
            }

            $type = $type == 'add' ? 'add' : 'remove';

            $boards = $this->getBoards(false);
            if (!isset($boards[$boardId])) {
                return $this->json(false, $this->lang['board_not_exists']);
            }

            $board = $boards[$boardId];

            $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
            $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
            $inGroups    = in_array($this->_user->userName, $board['groups'], true) || sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));

            // 没有权限
            if ($type == 'add' && !$isSuperModerator && !$isModerator && !$inGroups) {
                return $this->json(false, $this->lang['perm_deny_visit']);
            }

            if ($type == 'add') {
                $orderNum = $daoBoard->getAttentionBoardsMaxOrderNum($this->_user->orgId, $this->_user->uniqueId);
                $ret = $daoBoard->addAttention($this->_user->orgId, $boardId, $this->_user->uniqueId, $orderNum + 1);
            } else {
                $ret = $daoBoard->removeAttention($this->_user->orgId, $boardId, $this->_user->uniqueId);
            }

            if (!$ret) {
                return $this->json(false, $this->lang[$type . '_board_attention_failure']);
            }

            return $this->json(true, $this->lang[$type . '_board_attention_success']);
        }
    }

    /**
     * 排序关注分区下的板块
     */
    public function sortAttentionAction()
    {
        $boardId = $this->_request->getPost('bid');
        $type    = $this->_request->getPost('type');

        /* @var $daoBoard Dao_Td_Board_Board*/
        $daoBoard = $this->getDao('Dao_Td_Board_Board');
        $ret      = $daoBoard->sortAttention($this->_user->orgId, $this->_user->uniqueId, $boardId, $type);

        return $this->json($ret);
    }

    /**
     * 关闭板块
     */
    public function closeAction()
    {
        $boardId = $this->_request->getPost('bid');
        $isClose = (boolean) $this->_request->getPost('isclose');

        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        if (empty($boardId)) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $boards = $this->getBoards(false);

        if (!isset($boards[$boardId])) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $board = $boards[$boardId];

        // 继承上级分区权限
        if (!empty($board['parentid'])
            && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])) {
            $isSuperModerator = true;
        } else {
            $isSuperModerator = false;
        }

        $isOwner     = ($this->_user->userId == $board['ownerid']);
        $isModerator = array_key_exists($this->_user->userId, $board['moderators']);

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CLOSE_BOARD)
             && ($isModerator || $isSuperModerator))
        {
            return $this->json(false, $this->lang['perm_deny_close']);
        }

        $status = $isClose ? Dao_Td_Board_Board::STATUS_CLOSED : Dao_Td_Board_Board::STATUS_NORMAL;

        $ret = $daoBoard->updateBoard($this->_user->orgId, $boardId, array('status' => $status));

        $act = $isClose ? 'close' : 'open';
        if (!$ret) {
            return $this->json(false, $this->lang["board_{$act}_failure"]);
        }

        $this->_writeLog(Dao_Td_Log_Log::TYPE_BOARD, $boardId, Dao_Td_Log_Log::ACTION_CLOSE, array('status' => $status));

        return $this->json(true, $this->lang["board_{$act}_success"]);
    }

    /**
     * 清空板块
     */
    public function clearAction()
    {
        // 暂时屏蔽
        $this->json(false, 'unsupport');

        $boardId = $this->_request->getPost('bid');
        $isClose = (boolean) $this->_request->getPost('close');

        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        if (empty($boardId)) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $boards = $this->getBoards(false);

        if (!isset($boards[$boardId])) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $board = $boards[$boardId];

        // 继承上级分区权限
        if (!empty($board['parentid'])
            && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])) {
            $isSuperModerator = true;
        } else {
            $isSuperModerator = false;
        }

        $isOwner     = ($this->_user->userId == $board['ownerid']);
        $isModerator = array_key_exists($this->_user->userId, $board['moderators']);

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_TUDU)
           || (!$isModerator && !$isSuperModerator))
        {
            return $this->json(false, $this->lang['perm_deny_clear']);
        }

        $ret = $daoBoard->clearBoard($this->_user->orgId, $boardId);

        if (!$ret) {
            return $this->json(false, $this->lang['board_clear_failure']);
        }

        return $this->json(true, $this->lang['board_clear_success']);
    }

    /**
     * 删除板块
     */
    public function deleteAction()
    {
        $boardId = $this->_request->getPost('bid');

        $daoBoard = $this->getDao('Dao_Td_Board_Board');
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu  = $this->getDao('Dao_Td_Tudu_Tudu');

        if (empty($boardId)) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        $boards = $this->getBoards(false);

        if (!isset($boards[$boardId])) {
            return $this->json(false, $this->lang['board_not_exists']);
        }

        if (strpos($boardId, '^') === 0) {
            return $this->json(false, $this->lang['delete_sys_board']);
        }

        if ($daoTudu->countTudu(array('boardid' => $boardId, 'isdraft' => 0))) {
            return $this->json(false, $this->lang['delete_not_null_board']);
        }

        $board = $boards[$boardId];

        // 继承上级分区权限
        if (!empty($board['parentid'])
            && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])) {
            $isSuperModerator = true;
        } else {
            $isSuperModerator = false;
        }

        $isOwner     = ($this->_user->userId == $board['ownerid']);
        $isModerator = array_key_exists($this->_user->userId, $board['moderators']);

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_BOARD) || !$isSuperModerator) {
            return $this->json(false, $this->lang['perm_deny_delete']);
        }

        $ret = $daoBoard->deleteBoard($this->_user->orgId, $boardId);

        if (!$ret) {
            return $this->json(false, $this->lang['board_delete_failure']);
        }
        $daoBoard->tidySort($this->_user->orgId, $boards[$boardId]['type'], $boards[$boardId]['parentid']);

        // 插入消息队列
        $options = $this->_bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);
        $data    = implode(' ', array(
            'board',
            'delete',
            null,
            http_build_query()
        ));

        $httpsqs->put($data, 'notify');

        $this->_writeLog(Dao_Td_Log_Log::TYPE_BOARD, $boardId, Dao_Td_Log_Log::ACTION_DELETE);

        return $this->json(true, $this->lang['board_delete_success']);
    }

    /**
     * /board/add-favor
     *
     * 手工添加常用版块
     */
    public function addFavorAction()
    {
        $boardId = $this->_request->getPost('bid');

        /*@var $daoBoard Dao_Td_Board_Favor*/
        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        $maxWeight = $daoBoard->getMaxFavorWeight($this->_user->uniqueId);

        $weight = $maxWeight >= Dao_Td_Board_Board::FAVOR_WEIGHT_LIMIT ? $maxWeight : Dao_Td_Board_Board::FAVOR_WEIGHT_LIMIT;
        $favor  = $daoBoard->getFavor($this->_user->orgId, $boardId, $this->_user->uniqueId);

        if (!$favor) {
            $ret = $daoBoard->addFavor(array(
                'orgid'    => $this->_user->orgId,
                'boardid'  => $boardId,
                'uniqueid' => $this->_user->uniqueId,
                'weight'   => $weight + 1
            ));
        } elseif ($favor['weight'] < Dao_Td_Board_Board::FAVOR_WEIGHT_LIMIT) {
            $ret = $daoBoard->updateFavor($this->_user->orgId, $boardId, $this->_user->uniqueId, array(
                'weight'   => $weight + 1
            ));
        }

        return $this->json($ret, null);
    }

    /**
     * /board/add-favor
     *
     * 手工添加常用版块
     */
    public function removeFavorAction()
    {
        $boardId = $this->_request->getPost('bid');

        /*@var $daoBoard Dao_Td_Board_Favor*/
        $daoBoard = $this->getDao('Dao_Td_Board_Board');

        $ret = $daoBoard->deleteFavor($this->_user->orgId, $boardId, $this->_user->uniqueId);

        return $this->json($ret, null);
    }

    /**
     * 输出组织架构数据
     */
    public function getCast()
    {
        /* @var $castDao Dao_Md_User_Cast */
        $castDao = $this->getMdDao('Dao_Md_User_Cast');
        $users   = $castDao->getCastUsers(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId), null, 'ordernum DESC');
        $users   = $users->toArray('username');

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = $this->getMdDao('Dao_Md_User_Group');
        $groups   = $daoGroup->getGroups(array('orgid' => $this->_user->orgId));
        $groups   = $groups->toArray('groupid');

        return array(
            'users'  => $users,
            'groups' => $groups
        );
    }

    /**
     * 获取邮箱地址
     */
    private function _getAddress($str)
    {
        $ret = array();
        $pattern = "/(([\w-]*)@([^ ]*)) [^\n]*/";
        preg_match_all($pattern, $str, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $ret[] = $matches[1][$i];
        }
        return $ret;
    }
}

