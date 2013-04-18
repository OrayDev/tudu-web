<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Group.php 1298 2011-11-16 10:32:56Z cutecube $
 */

/**
 * 图度组扩展
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension_Group extends Tudu_Tudu_Extension_Abstract
{
    /**
     *
     * @param array $params
     * @return array
     */
    public function onPrepare(Tudu_Tudu_Storage_Tudu &$tudu, array $params)
    {}

    /**
     * 保存图度树节点
     *
     * @param $tuduId
     * @param $params
     */
    public function saveNode($tuduId, array $params)
    {
        /* @var $daoTuduGroup Dao_Td_Tudu_Group */
        $daoTuduGroup = $this->getDao('Dao_Td_Tudu_Group');

        $node = $daoTuduGroup->getNode($tuduId);

        if (null === $node) {
            $params['tuduid'] = $tuduId;
            $ret = $daoTuduGroup->createNode($params);
        } else {
            $ret = $daoTuduGroup->updateNode($tuduId, array(
                'type'     => $params['type'],
                'parentid' => isset($params['parentid']) ? $params['parentid'] : null,
                'rootid'   => isset($params['rootid']) ? $params['rootid'] : null
            ));
        }

        return $ret;
    }

    /**
     * 创建图度
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function postCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->nodeType) {
            /* @var $daoTuduGroup Dao_Td_Tudu_Group */
            $daoTuduGroup = $this->getDao('Dao_Td_Tudu_Group');

            $ret = $daoTuduGroup->createNode(array(
                'tuduid'   => $tudu->tuduId,
                'uniqueid' => $tudu->uniqueId,
                'type'     => $tudu->nodeType,
                'parentid' => $tudu->parentId,
                'rootid'   => $tudu->rootId
            ));
        }
    }

    /**
     * 更新图度时执行
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function postUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->nodeType) {
            $this->saveNode($tudu->tuduId, array(
                'uniqueid' => $tudu->uniqueId,
                'type'     => $tudu->nodeType,
                'parentid' => $tudu->parentId,
                'rootid'   => $tudu->rootId
            ));
        }
    }

    /**
     * 分工图度时触发
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function onDivide(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->nodeType) {
            $this->saveNode($tudu->tuduId, array(
                'uniqueid' => $tudu->uniqueId,
                'type'     => $tudu->nodeType,
                'parentid' => $tudu->parentId,
                'rootid'   => $tudu->rootId
            ));
        }
    }
}