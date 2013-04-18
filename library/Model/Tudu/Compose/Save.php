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
class Model_Tudu_Compose_Save extends Model_Tudu_Compose_Abstract
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

        // 必须有创建图度的权限
        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('User permission deined for current operation', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        if ($tudu->tuduId) {
            $this->_fromTudu = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->tuduId);

            // 不存在
            if (null === $this->_fromTudu || $this->_fromTudu->uniqueId != $this->_user->uniqueId) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
            }

            // 不是草稿的
            if (!$this->_fromTudu->isDraft) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Could not save tudu had been sent', Model_Tudu_Exception::TUDU_NOTEXISTS);
            }

            $tudu->fromTudu = $this->_fromTudu;
            $tudu->postId   = $this->_fromTudu->postId;
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
        // 保存图度
        if (null !== $this->_fromTudu) {
            $this->_updateTudu($tudu);
        } else {
            $this->_createTudu($tudu);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::send()
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        // 添加到发起人草稿箱
        $labels = $daoTudu->addUser($tudu->tuduId, $this->_user->uniqueId, array());
        if (false !== $labels) {
            if (is_string($labels)) {
                $labels = explode(',', $labels);
            } else {
                $labels = array();
            }

            // 添加到草稿箱
            if (!in_array('^r', $labels)) {
                $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^r');
            }
        }
    }
}