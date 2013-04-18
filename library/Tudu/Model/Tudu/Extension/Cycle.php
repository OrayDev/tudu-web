<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cycle.php 1828 2012-04-28 09:48:32Z cutecube $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 会议扩展数据维护实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Cycle extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     * 保存会议数据
     *
     * @param $data
     */
    public function onSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract $data)
    {
        /* @var $daoCycle Dao_Td_Tudu_Cycle */
        $daoCycle = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Cycle', Tudu_Dao_Manager::DB_TS);

        $cycle = $data->getAttributes();

        if (null !== $daoCycle->getCycle(array('cycleid' => $tudu->cycleId))) {
            $daoCycle->updateCycle($tudu->cycleId, $cycle);
        } else {
            $daoCycle->createCycle($cycle);
        }
    }
}