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
 * @version    $Id: Meeting.php 1292 2011-11-15 10:10:57Z cutecube $
 */

/**
 * 投票扩展
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension_Meeting extends Tudu_Tudu_Extension_Abstract
{

    /**
     * 格式化投票参数
     *
     * @param array  $params
     * @param string $suffix
     * @return array
     */
    public function formatParams($params, $suffix = '')
    {

        $meeting = array(
            'notifytype' => !empty($params['remindbefore' . $suffix]) && isset($params['notifytype' . $suffix])
                            ? (int) $params['notifytype' . $suffix]
                            : null,
            'isallday'   => !empty($params['allday' . $suffix]) ? 1 : 0
        );

        if (isset($params['location'])) {
            $meeting['location'] = $params['location'. $suffix];
        }

        if ($meeting['notifytype']) {
            $meeting['notifytime'] = Dao_Td_Tudu_Meeting::calNotifyTime(strtotime($params['starttime'. $suffix]), $meeting['notifytype'. $suffix]);
        } else {
            $meeting['notifytime'] = null;
        }

        return $meeting;
    }


    /**
     * 创建图度
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function postCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /* @var $daoMeeting Dao_Td_Tudu_Meeting */
        $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');

        $meeting = $tudu->meeting ? $tudu->meeting : array();

        $meeting['tuduid'] = $tudu->tuduId;
        $meeting['orgid']  = $tudu->orgId;

        $daoMeeting->createMeeting($meeting);

    }

    /**
     * 更新图度时执行
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function postUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /* @var $daoMeeting Dao_Td_Tudu_Meeting */
        $daoMeeting = $this->getDao('Dao_Td_Tudu_Meeting');

        $meeting = $tudu->meeting ? $tudu->meeting : array();

        $daoMeeting->updateMeeting($tudu->tuduId, $meeting);
    }
}