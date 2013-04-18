<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Exception.php 1894 2012-05-31 08:02:57Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Handler_Abstract
 */
require_once 'Model/Tudu/Extension/Handler/Abstract.php';

/**
 * @see Dao_Td_Tudu_Group
 */
require_once 'Dao/Td/Tudu/Group.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Handler_Group extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @var string
     */
    protected $_model = null;

    /**
     *
     * @var Model_Tudu_Send_Interface
     */
    protected $_sendModel = null;

    /**
     *
     * @var string
     */
    protected $_action = 'send';

    /**
     *
     * @var array
     */
    protected $_extendProperties = array(
        'orgid', 'boardid', 'classid', 'content', 'privacy', 'needconfirm', 'notifyall', 'priority', 'endtime', 'starttime', 'from', 'uniqueid', 'poster', 'email'
    );

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        $group  = $tudu->getExtension('Model_Tudu_Extension_Group');

        if (null == $group) {
            return ;
        }

        if ($tudu->operation == 'save') {
            $this->_action = 'save';
        }

        if ($tudu->fromTudu && $tudu->fromTudu->nodeType) {
            $tudu->nodeType = $tudu->fromTudu->nodeType;
            $tudu->rootId   = $tudu->fromTudu->rootId;
        } else {
            $tudu->nodeType = Dao_Td_Tudu_Group::TYPE_ROOT;
            $tudu->rootId   = $tudu->tuduId;
        }

        $children = $group->getChildren();
        foreach ($children as &$child) {
            $params = array(&$child);

            // 继承父任务内容
            if (!$child->tuduId) {
                foreach ($this->_extendProperties as $key) {
                    if ($key == 'content') {
                        if (!trim(strip_tags($child->content, 'img'))) {
                            $child->content = $tudu->content;
                        }
                    } else {
                        if (!$child->{$key}) {
                            $child->{$key} = $tudu->{$key};
                        }
                    }
                }

                $child->nodeType = Dao_Td_Tudu_Group::TYPE_LEAF;
            }

            $child->parentId = $tudu->tuduId;
            $child->rootId   = $tudu->rootId;

            if ($tudu->operation == 'save' || $tudu->operation == 'send' || $tudu->operation == 'divide') {
                $child->content = $child->content ? $child->content : $tudu->content;
                $child->boardId = $child->boardId ? $child->boardId : $tudu->boardId;
            }

            $this->getModel()
                 ->reset()
                 ->prepareTudu('compose', $child)
                 ->applyHooksFunc('compose', 'filter', $params);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function action(Model_Tudu_Tudu &$tudu)
    {
        $group = $tudu->getExtension('Model_Tudu_Extension_Group');

        /* @var $daoGroup Dao_Td_Tudu_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

        if (null == $group) {
            return ;
        }

        if (!$tudu->fromTudu || !$tudu->fromTudu->nodeType) {
            $daoGroup->createNode(array(
                'tuduid'   => $tudu->tuduId,
                'type'     => $tudu->nodeType,
                'uniqueid' => $tudu->uniqueId,
                'rootid'   => $tudu->rootId
            ));
        }

        $children = $group->getChildren();
        $model    = $this->getModel();
        foreach ($children as &$child) {

            $params = array(&$child);

            $model->compose($child);

            if (!$child->fromTudu || !$child->fromTudu->nodeType) {
                $daoGroup->createNode(array(
                    'tuduid'   => $child->tuduId,
                    'type'     => $child->nodeType,
                    'parentid' => $tudu->tuduId,
                    'rootid'   => $tudu->rootId,
                    'uniqueid' => $tudu->uniqueId
                ));
            }

            $model->applyHooksFunc('compose', 'action', $params);

            if ($tudu->operation != 'save') {
                $this->getSendModel()->send($child);
            }
        }
    }

    /**
     *
     */
    public function getModel()
    {
        if (null === $this->_model) {
            $className = 'Model_Tudu_Compose_' . ucfirst($this->_action);
            Zend_Loader::loadClass($className);

            $this->_model = new $className();
        }

        return $this->_model;
    }

    /**
     *
     */
    public function getSendModel()
    {
        if (null === $this->_sendModel) {
            $className = 'Model_Tudu_Send_Common';
            Zend_Loader::loadClass($className);

            $this->_sendModel = new $className();
        }

        return $this->_sendModel;
    }
}