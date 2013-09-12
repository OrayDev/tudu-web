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
 * @version    $Id: Remind.php 2890 2013-06-20 07:13:48Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Handler_Abstract
 */
require_once 'Model/Tudu/Extension/Handler/Abstract.php';

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Handler_Remind extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        $remind = $tudu->getExtension('Model_Tudu_Extension_Remind');
        $params = $remind->getAttributes();

        /* @var $daoRemind Dao_Td_Tudu_Remind */
        $daoRemind  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Remind', Tudu_Dao_Manager::DB_TS);
        $tuduRemind = $daoRemind->getRemind(array('tuduid' => $tudu->tuduId));

        if (null !== $tuduRemind) {
            $remind->setAttribute('action', 'update');
        } else {
            $remind->setAttribute('action', 'create');
        }

        if ($params['isvalid']) {
            if ($tudu->cycle) {
                $tudu->special = 9;
            } else {
                $tudu->special = Dao_Td_Tudu_Tudu::SPECIAL_REMIND;
            }
        } else {
            $tudu->special = $tudu->cycle ? 1 : 0;
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function action(Model_Tudu_Tudu &$tudu)
    {
        $remind = $tudu->getExtension('Model_Tudu_Extension_Remind');
        $params = $remind->getAttributes();

        /* @var $daoRemind Dao_Td_Tudu_Remind */
        $daoRemind  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Remind', Tudu_Dao_Manager::DB_TS);

        if ($params['action'] == 'update') {
            if (empty($params)) {
                $params['isvalid'] = 0;
            }

            $daoRemind->updateRemind($tudu->tuduId, $params);
        } else {
            if ($params['isvalid']) {
                $params['tuduid'] = $tudu->tuduId;
                $daoRemind->createRemind($params);
            }
        }
    }
}