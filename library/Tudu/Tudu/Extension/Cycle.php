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
 * @version    $Id: Cycle.php 1387 2011-12-14 02:07:40Z web_op $
 */

/**
 * 投票扩展
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension_Cycle extends Tudu_Tudu_Extension_Abstract
{

    /**
     * 格式化周期任务参数
     *
     * @param array  $params
     * @param string $suffix
     * @return array
     */
    public function formatParams($params, $suffix = '')
    {
        $cycle = array();

        if (!empty($params['cycleid' . $suffix])) {
            $cycle['cycleid'] = $params['cycleid' . $suffix];
        } else {
            $cycle['cycleid'] = Dao_Td_Tudu_Cycle::getCycleId();
        }

        $cycle['mode']        = $params['mode' . $suffix];
        $cycle['endtype']     = $params['endtype' . $suffix];
        $cycle['displaydate'] = $params['displaydate' . $suffix];

        // 重复范围
        if ($cycle['endtype'] == Dao_Td_Tudu_Cycle::END_TYPE_COUNT) {
            $cycle['endcount'] = (int) $params['endcount' . $suffix];
        } elseif ($cycle['endtype'] == Dao_Td_Tudu_Cycle::END_TYPE_DATE) {
            $cycle['enddate']  = @strtotime($params['enddate' . $suffix]);
        } else {
            $cycle['endtype'] = Dao_Td_Tudu_Cycle::END_TYPE_NONE;
        }

        if ($cycle['displaydate'] == 1 && empty($params['starttime' . $suffix])) {
            $params['starttime'] = time();
        }

        $cycle['type']  = (int) $params['type' . '-' . $cycle['mode'] . $suffix];
        $prefix         = $cycle['mode' . $suffix] . '-' . $cycle['type' . $suffix] . '-';
        $cycle['day']   = isset($params[$prefix . 'day' . $suffix]) ? (int) $params[$prefix . 'day' . $suffix] : 0;
        $cycle['week']  = isset($params[$prefix . 'week' . $suffix]) ? (int) $params[$prefix . 'week' . $suffix] : 0;
        $cycle['month'] = isset($params[$prefix . 'month' . $suffix]) ? (int) $params[$prefix . 'month' . $suffix] : 0;

        $cycle['iskeepattach']  = !empty($params['iskeepattach' . $suffix]) ? 1 : 0;

        if (isset($params[$prefix . 'weeks'])) {
            $cycle['weeks'] = implode(',', $params[$prefix . 'weeks' . $suffix]);
        }

        if (isset($params[$prefix . 'at'])) {
            $cycle['at'] = (int) $params[$prefix . 'at' . $suffix];
        }

        if (isset($params[$prefix . 'what'])) {
            $cycle['what'] = $params[$prefix . 'what' . $suffix];
        }

        if (!empty($params['starttime' . $suffix]) && !empty($params['endtime' . $suffix])) {
            $cycle['period'] = Oray_Function::dateDiff('d', strtotime($params['starttime' . $suffix]), strtotime($params['endtime' . $suffix]));
        }

        return $cycle;
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function onPrepare(Tudu_Tudu_Storage_Tudu &$tudu, array $params)
    {}

    /**
     * 创建图度
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function preCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->cycle) {
            /* @var $daoCycle Dao_Td_Tudu_Cycle */
            $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');

            $cycle = $tudu->cycle;

            $daoCycle->createCycle($cycle);
        }
    }

    /**
     * 更新图度时执行
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function preUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /* @var $daoCycle Dao_Td_Tudu_Cycle */
        $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');

        $params = $tudu->cycle;

        if (!empty($params)) {
            if (null != $daoCycle->getCycle(array('cycleid' => $tudu->cycleId))) {
                $daoCycle->updateCycle($tudu->cycleId, $params);
            } else {
                $daoCycle->createCycle($params);
            }
        } else {
            $tudu->special = 0;
            $tudu->cycleId = null;
        }
    }
}