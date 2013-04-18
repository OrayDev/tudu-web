<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class BoardController extends TuduX_Controller_OpenApi
{

    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 获取版块列表
     */
    public function listAction()
    {
        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

        $boards = $daoBoard->getBoards(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId))->toArray('boardid');

        $return = array();
        foreach ($boards as $bid => $board) {
            if ($board['issystem'] || $board['status'] != 0) {
                continue ;
            }

            if ($board['parentid']) {
                if (!array_key_exists($bid, $boards)) {
                    continue ;
                }

                if (!in_array('^all', $board['groups'])
                // 参与人
                && !(in_array($this->_user->userName, $board['groups'], true) || in_array($this->_user->address, $board['groups'], true))

                // 参与人（群组）
                && !sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"))

                // 是否版主
                && !array_key_exists($this->_user->userId, $board['moderators'])

                // 是否创建者
                && !($board['ownerid'] == $this->_user->userId)

                // 是否上级分区版主
                && !array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators'])
                ) {
                    continue;
                }

                $boards[$board['parentid']]['children'][] = &$boards[$bid];

            } else {

                $return[] = &$boards[$bid];
            }
        }

        $ret = array();
        foreach ($return as $item) {
            if (empty($item['children'])) {
                continue ;
            }

            $ret[] = array(
                'orgid'   => $item['orgid'],
                'boardid' => $item['boardid'],
                'type'    => $item['type'],
                'boardname' => $item['boardname'],
                'privacy' => (int) $item['privacy'],
                'ordernum' => (int) $item['ordernum'],
                'issystem' => (int) $item['issystem'],
                'needconfirm' => (int) $item['needconfirm'],
                'isflowonly' => (int) $item['flowonly'],
                'isclassify' => (int) $item['isclassify'],
                'status'     => (int) $item['status'],
                'children'   => $this->_formatBoardChildren($item['children']),
                'updatetime' => null
            );
        }

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->boards = $ret;
    }

    /**
     *
     */
    public function classesAction()
    {
        $boardId = $this->_request->getQuery('boardid');

        /* @var $daoClass Dao_Td_Tudu_Class */
        $daoClass = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Class', Tudu_Dao_Manager::DB_TS);

        $conditions = array(
            'orgid' => $this->_user->orgId,
        );

        if (!empty($boardId)) {
            $conditions['boardid'] = $boardId;
        }

        $classes = $daoClass->getClasses($conditions)->toArray();

        $this->view->code    = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->classes = $classes;
    }

    /**
     *
     * @param array $boards
     */
    private function _formatBoardChildren(array $boards)
    {
        $ret = array();
        foreach ($boards as $item) {
            $ret[] = array(
                'orgid'   => $item['orgid'],
                'boardid' => $item['boardid'],
                'type'    => $item['type'],
                'boardname' => $item['boardname'],
                'privacy' => (int) $item['privacy'],
                'ordernum' => (int) $item['ordernum'],
                'issystem' => (int) $item['issystem'],
                'needconfirm' => (int) $item['needconfirm'],
                'isflowonly' => (int) $item['flowonly'],
                'isclassify' => (int) $item['isclassify'],
                'updatetime' => null
            );
        }

        return $ret;
    }
}