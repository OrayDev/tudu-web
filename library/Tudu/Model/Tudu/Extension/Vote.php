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
 * @version    $Id: Vote.php 1828 2012-04-28 09:48:32Z cutecube $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 会议扩展数据维护实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Vote extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     * 保存会议数据
     *
     * @param $data
     */
    public function onSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract $data)
    {
        /* @var $daoMeeting Dao_Td_Tudu_Meeting */
        $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);

        $attrs   = $data->getAttributes();
        $options = $data->getOptions();

        $vote = array();

        if (isset($attrs['maxchoices']) && is_int($attrs['maxchoices'])) {
            $vote['maxchoices'] = $attrs['maxchoices'];
        }
        if (isset($attrs['privacy'])) {
            $vote['privacy'] = $attrs['privacy'] ? 1 : 0;
        }
        if (isset($attrs['visible'])) {
            $vote['visible'] = $attrs['visible'] ? 1 : 0;
        }
        if (isset($attrs['expiretime']) && is_int($attrs['expiretime'])) {
            $vote['expiretime'] = $attrs['expiretime'];
        }
        if (isset($attrs['votecount'])) {
            $vote['votecount'] = (int) $attrs['votecount'];
        }
        $vote['tuduid'] = $tudu->tuduId;

        if ($daoVote->existsVote($tudu->tuduId)) {
            $ret = $daoVote->updateVote($tudu->tuduId, $vote);
        } else {
            $ret = $daoVote->createVote($vote);
        }

        foreach ($options as $option) {
            if (!empty($option['isnew'])) {
                $option['tuduid'] = $tudu->tuduId;
                $daoVote->createOption($option);
            } else {
                $daoVote->updateOption($tudu->tuduId, $option['optionid'], $option);
            }
        }
    }
}