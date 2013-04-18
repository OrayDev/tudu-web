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
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Cycle_Cycle extends Model_Abstract
{
    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareTuduCycle(Model_Tudu_Tudu &$tudu)
    {
        if (null != ($cycle = $tudu->getExtension('Model_Tudu_Extension_Cycle'))) {
            $tudu->cycleId = $cycle->cycleId;
            $tudu->special = 1;
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function saveTuduCycle(Model_Tudu_Tudu &$tudu)
    {
        $cycle = $tudu->getExtension('Model_Tudu_Extension_Cycle');

        /* @var $daoCycle Dao_Td_Tudu_Cycle */
        $daoCycle = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Cycle', Tudu_Dao_Manager::DB_TS);

        $params = $cycle->getAttributes();

        if (null !== $daoCycle->getCycle(array('cycleid' => $cycle->cycleId))) {
            $daoCycle->updateCycle($cycle->cycleId, $params);
        } else {
            $daoCycle->createCycle($params);
        }
    }
}