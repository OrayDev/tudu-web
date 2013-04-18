<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Model_Tudu_Compose_Abstract
 */
require_once 'Model/Tudu/Compose/Abstract.php';

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Compose_Send extends Model_Tudu_Compose_Abstract
{

    /**
     * 图度参数过滤
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $boards = $this->_getBoards();

        if (!$tudu->boardId || !isset($boards[$tudu->boardId])) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Board not exists', Model_Tudu_Exception::BOARD_NOTEXISTS);
        }
        $board = $boards[$tudu->boardId];

        $isModerator      = array_key_exists($this->_user->userId, $board['moderators']);
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
        $inGroups         = in_array($this->_user->userName, $board['groups'], true) || (boolean) sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));
        $isOwner          = $board['ownerid'] == $this->_user->userId;

        if ($tudu->tuduId) {
            $this->_fromTudu = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->tuduId);

            // 不存在
            if (null === $this->_fromTudu) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
            }

            // 权限判断
            $allow = true;
            if (!$this->_fromTudu->isDraft) {
                if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)) {
                    $allow = false;
                }
            } elseif (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
                $allow = false;
            }

            // 版块权限
            $isSender = $this->_fromTudu->sender == $this->_user->userName;

            if (!$isSender && !$isModerator && !$isSuperModerator) {
                $allow = false;
            }

            if (!$allow) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('User permission deined for current operation', Model_Tudu_Exception::PERMISSION_DENIED);
            }

            // 记录当前图度
            $tudu->fromTudu = $this->_fromTudu;
            $tudu->postId   = $this->_fromTudu->postId;
            $tudu->flowId   = $this->_fromTudu->flowId;
            $tudu->isDraft  = $this->_fromTudu->isDraft;

            /* @var $daoTuduGroup Dao_Td_Tudu_Group */
            $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);
            if (!$this->_fromTudu->isDraft) {
                $accepters = $daoTudu->getAccepters($tudu->tuduId);
                $to        = $tudu->to;

                foreach ($accepters as $accepter) {
                    list($username, $truename) = explode(' ', $accepter['accepterinfo'], 2);
                    $childrenCount = $daoTuduGroup->getChildrenCount($tudu->tuduId, $accepter['uniqueid']);

                    if (isset($to[0][$username]) || $childrenCount > 0) {
                        $to[0][$username] = array(
                            'username' => $username,
                            'truename' => $truename,
                            'email'    => $username,
                            'uniqueid' => $accepter['uniqueid']
                        );

                        if (!isset($to[0][$username]['percent']) || $childrenCount > 0) {
                            $to[0][$username]['percent'] = (int) $accepter['percent'];
                            $to[0][$username]['status']  = $accepter['percent'] <= 0 ? 0 : ($accepter['percent'] >= 100 ? 2 : 1);
                        }

                    }
                }

                $tudu->to = $to;
            }

        // 新建
        } else {
            if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('User permission deined for current operation', Model_Tudu_Exception::PERMISSION_DENIED);
            }

            //
            if ($board['status'] != 1 && ((!$inGroups && !$isModerator && !$isSuperModerator && !$isOwner) || !empty($board['isclose']))) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Board not exists', Model_Tudu_Exception::BOARD_NOTEXISTS);
            }
        }

    }

    /**
     * 发送图度
     *
     * @param Model_Tudu_Tudu $tudu
     * @throws Model_Tudu_Exception
     */
    public function compose(Model_Tudu_Tudu &$tudu)
    {
        if (!$tudu->tuduId) {
            $tudu->setAttribute('tuduid', Dao_Td_Tudu_Tudu::getTuduId());
        }

        // 保存图度
        if (null !== $tudu->fromTudu) {
            $this->_updateTudu($tudu);
        } else {
            $this->_createTudu($tudu);
        }

        $logAction = null !== $tudu->fromTudu && !$tudu->fromTudu->isDraft ? 'update' : 'send';

        $this->_tuduLog($logAction, $tudu);
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::send()
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $user    = Tudu_User::getInstance();

        // 添加到自己
        $daoTudu->addUser($tudu->tuduId, $this->_user->uniqueId, array(
            'role' => 'from'
        ));
        $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^all');

        if (!$tudu->parentId) {
            $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^i');
        }

        // 已发送
        if (!$this->_fromTudu || $this->_fromTudu->sender == $this->_user->userName) {
            $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^f');
        }

        // 移除草稿标签
        if ($this->_fromTudu && $this->_fromTudu->isDraft) {
            $daoTudu->deleteLabel($tudu->tuduId, $this->_user->unqiueId, '^r');
        }

        if ($tudu->type == 'notice') {
            $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^n');
        }

        if ($tudu->type == 'discuss') {
            $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^d');
        }

        if ($tudu->type == 'meeting') {
            $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^m');
        }

        // 发送图度
        $to = $tudu->to;
        if ($tudu->type == 'task' && !$tudu->reviewer && !$tudu->isDraft) {
            // 移除原有执行人
            $accepters = $daoTudu->getAccepters($tudu->tuduId);

            $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

            foreach ($accepters as $item) {
                list($username, ) = explode(' ', $item['accepterinfo'], 2);
                // 修改用户关联记录为非执行人，移除“我执行”标签
                if (!empty($to) && !array_key_exists($username, $to) && $daoTuduGroup->getChildrenCount($tudu->tuduId, $item['uniqueid']) <= 0) {
                    $daoTudu->removeAccepter($tudu->tuduId, $item['uniqueid']);
                    $daoTudu->deleteLabel($tudu->tuduId, $item['uniqueid'], '^a');
                }
            }
        }
    }
}