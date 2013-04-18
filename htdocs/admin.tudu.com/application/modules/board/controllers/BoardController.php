<?php
/**
 * 分区管理控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: BoardController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Board_BoardController extends TuduX_Controller_Admin
{
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'board'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('create', 'delete', 'update', 'merge', 'create.board', 'update.groups', 'updateuser', 'sort.board'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 输出分区信息
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));

        $this->view->boards = $boards->toArray();
        $this->view->orgid  = $this->_orgId;
        $this->view->registModifier('is_group', array($this, 'isGroup'));
    }

    /**
     * 创建分区
     */
    public function createAction()
    {
        /* @var @daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardName = trim($this->_request->getPost('boardname'));
        $type      = $this->_request->getPost('type');

        if (!$boardName) {
            return $this->json(false, $this->lang['invalid_params_boardname']);
        }

        $boardId  = Dao_Td_Board_Board::getBoardId($this->_orgId);

        $condition = array(
            'orgid' => $this->_orgId,
            'type'  => $type
        );
        $orderNum = $daoBoard->getBoardMaxOrderNum($condition);

        $params = array(
            'orgid'     => $this->_orgId,
            'boardid'   => $boardId,
            'boardname' => $boardName,
            'type'      => $type,
            'ordernum'  => $orderNum + 1,
            'privacy'   => 1
        );

        $ret = $daoBoard->createBoard($params);

        if (!$ret) {
            return $this->json(false, $this->lang['zone_create_failure']);
        }

        // 插入消息队列
        $options = $this->_bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);
        $data    = implode(' ', array(
            'board',
            'create',
            null,
            http_build_query(array())
        ));

        $httpsqs->put($data, 'notify');

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        $this->json(true, $this->lang['zone_create_success'], array('boardid' => $boardId, 'boards' => $boards));
    }

    /**
     * 新建板块、分区
     */
    public function createBoardAction()
    {
        $zone      = $this->_request->getPost('boardid');
        $boardName = trim($this->_request->getPost('boardname'));
        $zoneName  = trim($this->_request->getPost('zonename'));

        if (!$zone) {
            return $this->json(false, '请选择所属分区');
        }

        if ($zone == 'add-zone' && !$zoneName) {
            return $this->json(false, $this->lang['invalid_params_boardname']);
        }

        if (!$boardName) {
            return $this->json(false, '请输入板块名称');
        }

        /* @var @daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        if (!empty($zoneName)) {
            $zoneId  = Dao_Td_Board_Board::getBoardId($this->_orgId);
            $condition = array(
                'orgid' => $this->_orgId,
                'type'  => 'zone'
            );
            $orderNum = $daoBoard->getBoardMaxOrderNum($condition);
            $params = array(
                'orgid'     => $this->_orgId,
                'boardid'   => $zoneId,
                'boardname' => $zoneName,
                'type'      => 'zone',
                'ordernum'  => $orderNum + 1
            );

            $ret = $daoBoard->createBoard($params);
            if (!$ret) {
                return $this->json(false, $this->lang['zone_create_failure']);
            }
            $zone = $zoneId;
        }

        if (!empty($boardName)) {
            $boardId  = Dao_Td_Board_Board::getBoardId($this->_orgId);
            $condition = array(
                'orgid' => $this->_orgId,
                'type'  => 'board',
                'parentid' => $zone
            );
            $orderNum = $daoBoard->getBoardMaxOrderNum($condition);
            $params = array(
                'orgid'     => $this->_orgId,
                'boardid'   => $boardId,
                'type'      => 'board',
                'boardname' => $boardName,
                'parentid'  => $zone,
                'ownerid'   => $this->_user->userId,
                'ordernum'  => $orderNum + 1
            );

            $ret = $daoBoard->createBoard($params);
            if (!$ret) {
                return $this->json(false, '创建板块失败');
            }
        }

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        return $this->json(true, '创建板块成功', array('boardid' => $boardId, 'boards' => $boards));
    }

    /**
     * 更新分区
     */
    public function updateAction()
    {
        /* @var @$daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardId   = $this->_request->getPost('boardid');
        $boardName = trim($this->_request->getPost('boardname'));
        $type      = $this->_request->getPost('type');

        if (!$boardId) {
            return $this->json(false, $this->lang['invalid_params_boardid']);
        }

        $board = $daoBoard->getBoard(array(
            'orgid'   => $this->_orgId,
            'boardid' => $boardId
        ));

        if (null === $board) {
            if ($type == 'zone') {
                return $this->json(false, $this->lang['zone_not_exists']);
            } else {
                return $this->json(false, '板块不存在或已被删除');
            }
        }

        if (!$boardName) {
            if ($type == 'zone') {
                return $this->json(false, $this->lang['invalid_params_boardname']);
            } else {
                return $this->json(false, '请输入板块名称');
            }
        }

        $params = array(
            'boardname' => $boardName,
            'type'      => $type
        );

        $ret = $daoBoard->updateBoard($this->_orgId, $boardId, $params);

        if (!$ret) {
            if ($type == 'zone') {
                return $this->json(false, $this->lang['zone_update_failure']);
            } else {
                return $this->json(false, '更新板块失败');
            }
        }

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        // 插入消息队列
        $options = $this->_bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);
        $data    = implode(' ', array(
            'board',
            'update',
            null,
            http_build_query(array('bid' => $boardId))
        ));

        $httpsqs->put($data, 'notify');

        $this->json(true, $type == 'zone' ? $this->lang['zone_update_success'] : '更新板块成功', array('boardid' => $boardId, 'boards' => $boards));

    }

    /**
     * 删除分区
     */
    public function deleteAction()
    {
        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardId = $this->_request->getPost('boardid');
        $type    = $this->_request->getPost('type');

        if (!$boardId) {
            return $this->json(false, $this->lang['invalid_params_boardid']);
        }

        if (strpos($boardId, '^') === 0) {
            return $this->json(false, $this->lang['disable_delete_systemzone']);
        }

        $board = $daoBoard->getBoard(array(
            'orgid'   => $this->_orgId,
            'boardid' => $boardId
        ));

        switch ($type) {
            case 'zone':
                if (null === $board) {
                    return $this->json(false, $this->lang['zone_not_exists']);
                }

                if ($daoBoard->getChildCount($this->_orgId, $boardId) > 0) {
                    return $this->json(false, $this->lang['zone_has_children']);
                }

                break ;
            case 'board':
                if (null === $board) {
                    return $this->json(false, '板块不存在或已被删除');
                }

                /* @var $daoTudu Dao_Td_Tudu_Tudu */
                $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu', $this->_multidb->getDb('ts1'));

                if ($daoTudu->countTudu(array('boardid' => $boardId, 'isdraft' => 0))) {
                    return $this->json(false, '不能删除非空版块，请先清空版块再进行删除');
                }

                break ;
        }
        $ret = $daoBoard->deleteBoard($this->_orgId, $boardId);

        if (!$ret) {
            return $this->_son(false, $type == 'zone' ? $this->lang['zone_delete_failure'] : '删除板块失败');
        }
        $daoBoard->tidySort($this->_orgId, $type, $board->parentId);

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

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        return $this->json(true, $type == 'zone' ? $this->lang['zone_delete_success'] : '删除板块成功', $boards);
    }

    /**
     * 更新分区、板块负责人
     */
    public function updateuserAction()
    {
        /* @var $daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');

        $boardId    = $this->_request->getPost('boardid');
        $moderators = (array) $this->_request->getPost('userid');

        if (!$boardId) {
            return $this->json(false, $this->lang['invalid_params_boardid']);
        }

        $board = $daoBoard->getBoard(array(
            'orgid'   => $this->_orgId,
            'boardid' => $boardId
        ));

        if (null === $board) {
            return $this->json(false, $this->lang['zone_not_exists']);
        }

        if (!$moderators) {
            return $this->json(false, $this->lang['zone_user_select_null']);
        }
        $member = '';
        foreach ($moderators as $userId) {
            $user = $daoUser->getUser(array(
                'orgid'  => $this->_orgId,
                'userid' => $userId
            ));
            $userinfo = $daoUser->getUserInfo(array(
                'orgid'  => $this->_orgId,
                'userid' => $userId
            ));
            if (!$user || !$userinfo) {
                return $this->json(false, $this->lang['user_not_exists']);
            }
            $member .= $user->userId . ' ' . $userinfo->trueName . "\n";
        }

        $params = array(
            'moderators' => $member
        );

        $ret = $daoBoard->updateBoard($this->_orgId, $boardId, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['zone_user_update_failure']);
        }

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        return $this->json(true, $this->lang['zone_user_update_success'], $boards);
    }

    /**
     * 更新板块参与人
     */
    public function updateGroupsAction()
    {
        /* @var $daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardId  = $this->_request->getPost('boardid');
        $userIds  = (array) $this->_request->getPost('userid');
        $groupIds = (array) $this->_request->getPost('groupid');

        if (!$boardId) {
            return $this->json(false, $this->lang['invalid_params_boardid']);
        }

        $board = $daoBoard->getBoard(array(
            'orgid'   => $this->_orgId,
            'boardid' => $boardId
        ));

        if (null === $board) {
            return $this->json(false, '板块不存在或已被删除');
        }

        if (empty($userIds) && empty($groupIds)) {
            $groupIds[] = '^all';
            //return $this->json(false, '你尚未选择板块参与人');
        }

        $groups = array();
        if (!empty($userIds)) {
            foreach ($userIds as $userId) {
                $groups[] = $userId . '@' . $this->_orgId;
            }
        }

        if (!empty($groupIds)) {
            foreach ($groupIds as $groupId) {
                $groups[] = $groupId;
            }
        }

        $params = array(
            'groups' => implode("\n", $groups)
        );

        $ret = $daoBoard->updateBoard($this->_orgId, $boardId, $params);

        if (!$ret) {
            return $this->json(false, '更新板块参与人失败');
        }

        return $this->json(true, '更新板块参与人成功');
    }

    /**
     * 合并分区操作
     */
    public function mergeAction()
    {
        /* @var @daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardId = $this->_request->getPost('boardid');
        $target  = $this->_request->getPost('targetid');

        if (!$boardId) {
            return $this->json(false, $this->lang['board_lost_params_boardid']);
        }

        if (strpos($boardId, '^') === 0) {
            return $this->json(false, $this->lang['disable_meger_systemzone']);
        }

        if (!$target) {
            return $this->json(false, $this->lang['board_lost_params_target']);
        }

        if ($boardId == $target) {
            return $this->json(false, $this->lang['board_is_same']);
        }

        if (!$daoBoard->existsBoard($this->_orgId, $boardId)) {
            return $this->json(false, $this->lang['zone_not_exists']);
        }

        if (!$daoBoard->existsBoard($this->_orgId, $target)) {
            return $this->json(false, $this->lang['target_zone_not_exists']);
        }

        $ret = $daoBoard->mergeBoard($this->_orgId, $boardId, $target);

        if (!$ret) {
            return $this->json(false, $this->lang['board_merge_failure']);
        }
        $daoBoard->tidySort($this->_orgId, 'zone');

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        return $this->json(true, $this->lang['board_merge_success'], $boards);
    }

    /**
     * 排序
     */
    public function sortBoardAction()
    {
        /* @var @daoOrg Dao_Td_Board_Board */
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts1'));

        $boardId  = $this->_request->getPost('boardid');
        $parentId = $this->_request->getPost('parentid');
        $sort     = $this->_request->getPost('sort');
        $type     = $this->_request->getPost('type');

        $ret = $daoBoard->sortBoard($boardId, $this->_orgId, $type, $sort, $parentId);

        $boards = $daoBoard->getBoards(array(
            'orgid' => $this->_orgId
        ), null, array('ordernum' => 'DESC'));
        $boards = $this->formatBoards($boards->toArray());

        return $this->json($ret, null, $boards);
    }

    /**
     * 判断是否为群组
     *
     * @param string $str
     */
    public function isGroup($str)
    {
        $pos = strpos($str, '@');
        if ($pos === false) {
            return true;
        }

        return false;
    }

    /**
     * 格式化分区板块数据
     */
    public function formatBoards(array $boards)
    {
        if (empty($boards)) {
            return array();
        }

        $ret = array();
        foreach ($boards as $board) {
            if ($board['boardid'] == '^system' || $board['boardid'] == '^app-attend') {
                continue ;
            }
            $moderators = array();
            $moderatorsName = array();
            foreach ($board['moderators'] as $key => $item) {
                $moderators[] = $key;
                $moderatorsName[] = $item;
            }
            $groups = array();
            if ($board['type'] == 'board') {
                foreach ($board['groups'] as $item) {
                    $isGroup  = $this->isGroup($item);
                    $str      = $isGroup ? 'group_' : '';
                    $str     .= str_replace('@'.$this->_orgId, '', $item);
                    $groups[] =  $str;
                }
            }
            $ret[] = array(
                'boardid' => $board['boardid'],
                'boardname' => $board['boardname'],
                'parentid' => $board['parentid'],
                'ordernum' => $board['ordernum'],
                'type' => $board['type'],
                'moderators' => implode(',', $moderators),
                'moderatorsname' => count($moderatorsName) <= 0 ? '-' : implode(',', $moderatorsName),
                'groups' => count($groups) <= 0 ? '' : implode(',', $groups)
            );
        }

        return $ret;
    }
}