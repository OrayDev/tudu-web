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
class Model_Tudu_Compose_Divide extends Model_Tudu_Compose_Abstract
{
    /**
     * 当前操作人是否允许在转发时修改图度内容
     *
     * @var boolean
     */
    protected $_isModified = true;

    /**
     * 过滤转发条件
     * 1.当前用户具有图度转发权限
     * 2.图度必须存在且已被发送
     * 3.当前操作用户必须为图度执行人
     * 4.当前图度不能是图度组
     *
     * @see Model_Tudu_Compose_Abstract::filter()
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 权限
        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to divide tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        // 无效的图度
        if (!$tudu->tuduId) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        $this->_fromTudu = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->tuduId);

        // 图度不存在或已被删除
        if (null === $this->_fromTudu) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        // 草稿
        if ($this->_fromTudu->isDraft) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Could not divide a draft tudu', Model_Tudu_Exception::TUDU_IS_DRAFT);
        }

        // 不是执行人
        if (!in_array($this->_user->userName, $this->_fromTudu->accepter)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to divide tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        // 工作流任务不能分工
        if ($this->_fromTudu->flowId) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Could not divide Tudu have flow', Model_Tudu_Exception::TUDU_IS_TUDUFLOW);
        }

        // 是否允许修改
        $boards = $this->_getBoards();
        $board  = $boards[$this->_fromTudu->boardId];
        $isSender         = $this->_fromTudu->sender == $this->_user->userName;
        $isModerator      = array_key_exists($this->_user->userId, $board['moderators']);
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)
            || (!$isSender && !$isModerator && !$isSuperModerator))
        {
            $this->_isModified = false;
        }

        $tudu->fromTudu = $this->_fromTudu;
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::compose()
     */
    public function compose(Model_Tudu_Tudu &$tudu)
    {
        $this->_tuduLog('send', $tudu);
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::send()
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {
        // 移除原有执行人
        $mto = $this->_fromTudu->to;
    }
}