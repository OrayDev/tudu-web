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
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Handler_Cycle extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        $cycle = $tudu->getExtension('Model_Tudu_Extension_Cycle');

        if (null !== $cycle) {
            $tudu->cycleId = $cycle->cycleId;
            $tudu->special = 1;
        }

        /* @var $daoCycle Dao_Td_Tudu_Cycle */
        $daoCycle = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Cycle', Tudu_Dao_Manager::DB_TS);

        $params = $cycle->getAttributes();

        if (null !== $daoCycle->getCycle(array('cycleid' => $cycle->cycleId))) {
            $ret = $daoCycle->updateCycle($cycle->cycleId, $params);
        } else {
            $ret = $daoCycle->createCycle($params);
        }

        if (!$ret) {
            $tudu->cycleId = null;
            $tudu->special = 0;
        }
    }
}