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
 * @version    $Id: Meeting.php 1826 2012-04-27 09:47:39Z cutecube $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 会议扩展数据维护实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Meeting extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     * 保存会议数据
     *
     * @param $data
     */
    public function onSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract $data)
    {
        /* @var $daoMeeting Dao_Td_Tudu_Meeting */
        $daoMeeting = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Meeting', Tudu_Dao_Manager::DB_TS);

        $attrs = $data->getAttributes();

        if ($daoMeeting->existsMeeting($tudu->tuduId)) {
            if (empty($attrs)) {
                return true;
            }

            $ret = $daoMeeting->updateMeeting($tudu->tuduId, $attrs);
        } else {
            $attrs['tuduid'] = $tudu->tuduId;
            $attrs['orgid']  = $tudu->orgId;

            $ret = $daoMeeting->createMeeting($attrs);
        }


    }
}