<?php
/**
 * Model Tudu Manager Tudu
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Tudu.php 2073 2012-08-23 09:38:32Z chenyongfa $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * @see Tudu_User
 */
require_once 'Tudu/User.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 */
class Model_Tudu_Manage extends Model_Abstract
{

    const CODE_PERMISSION_DENY   = 110;

    const CODE_DELETE_TUDU_GROUP = 123;

    const CODE_DELETE_TUDU_FAILED = 124;

    /**
     *
     * @var mixed
     */
    protected $_boards = null;

    /**
     *
     * @param Dao_Td_Tudu_Record_Tudu $tudu
     */
    public function delete(&$tudu)
    {
        $user   = Tudu_User::getInstance();

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 删除草稿
        if ($tudu->isDraft) {
            // 没有权限
            if ($tudu->sender != $user->userName) {
                // 没有权限
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Permission denied to delete this tudu', self::CODE_PERMISSION_DENY);
            }

            // 当前图度是图度组
            if ($tudu->isTuduGroup) {
                /* @var $daoTuduGroup Dao_Td_Tudu_Group */
                $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

                $children = $daoTuduGroup->getTudus(array('parentid' => $tudu->tuduId))->toArray();

                foreach ($children as $child) {
                    // 执行删除子图度操作
                    $daoTuduGroup->deleteTudu($child['tuduid']);
                }
            }

            // 执行删除操作
            if ($daoTudu->deleteTudu($tudu->tuduId)) {
                return ;
            }

        // 删除
        } else {

            $boards = $this->_getBoards($tudu->orgId);

            if (!isset($boards[$tudu->boardId])) {
                // board not exists
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('This tudu is tudu group, the group have some tudu', self::CODE_DELETE_TUDUGROUP_CHILD);
            }

            $isModerator      = array_key_exists($user->userId, $boards[$tudu->boardId]['moderators']);
            $isSuperModerator = false;
            if (isset($boards[$boards[$tudu->boardId]['parentid']])) {
                $isSuperModerator = array_key_exists($user->userId, $boards[$boards[$tudu->boardId]['parentid']]['moderators']);
            }

            if ($tudu->sender != $user->userName && !$isModerator && !$isSuperModerator) {
                // 没有权限
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Permission denied to delete this tudu', self::CODE_PERMISSION_DENY);
            }


            /* @var $daoTuduGroup Dao_Td_Tudu_Group */
            $daoTuduGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

            // 当删除的事图度组的时候，判断图度组下是否有子图度
            if ($tudu->isTuduGroup && $daoTuduGroup->getChildrenCount($tudu->tuduId, $user->uniqueId) > 0) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('This tudu is tudu group, the group have some tudu', self::CODE_DELETE_TUDUGROUP_CHILD);
            }

            // 删除操作
            $ret = $daoTudu->deleteTudu($tudu->tuduId);
            if ($ret) {
                if ($tudu->parentId) {
                    // 计算父级图度的进度
                    $daoTudu->calParentsProgress($tudu->parentId);
                    // 更新节点信息
                    if ($daoTuduGroup->getChildrenCount($tudu->parentId) <= 0) {
                        $daoTuduGroup->updateNode($tudu->parentId, array(
                            'type' => Dao_Td_Tudu_Group::TYPE_LEAF
                        ));
                    }
                }

                /* @var $daoLabel Dao_Td_Log_Log */
                $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

                $daoLog->createLog(array(
                    'orgid'      => $tudu->orgId,
                    'uniqueid'   => $user->uniqueId,
                    'operator'   => $user->userInfo,
                    'logtime'    => time(),
                    'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                    'targetid'   => $tudu->tuduId,
                    'action'     => Dao_Td_Log_Log::ACTION_DELETE,
                    'detail'     => serialize(array('orgid' => $user->orgId, 'uniqueid' => $user->uniqueId, 'userinfo' => $user->userInfo)),
                    'privacy'    => 0
                ));

                return ;
            }
        }

        require_once 'Model/Tudu/Exception.php';
        throw new Model_Tudu_Exception('Delete tudu failed', self::CODE_DELETE_TUDU_FAILED);
    }

    /**
     * 图度标签操作
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function label($tuduId, array $labels)
    {
        if (empty($labels)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing or invalid value of parameter "labelid"', self::CODE_INVALID_LABELID);
        }

        $user = Tudu_User::getInstance();
        $uniqueId = $user->uniqueId;

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $ret = false;
        foreach ($labels as $labelId => $action) {
            switch ($action) {
                case 'add':
                    $ret = $daoTudu->addLabel($tuduId, $uniqueId, $labelId);
                    break;

                case 'delete':
                    $ret = $daoTudu->deleteLabel($tuduId, $uniqueId, $labelId);
                    break;
            }
        }

        if (!$ret) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Label operation failed', self::LABEL_OPERATION_FAILED);
        }
    }

    /**
     *
     */
    protected function _getBoards($orgId)
    {
        if (null == $this->_boards) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard      = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
            $this->_boards = $daoBoard->getBoards(array(
                'orgid'    => $orgId
            ), null, 'ordernum DESC')->toArray('boardid');
        }
        return $this->_boards;
    }
}