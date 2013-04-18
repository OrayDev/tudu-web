<?php
/**
 * 排班方案规则记录
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Rule.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Rule extends Oray_Dao_Record
{
    /**
     * 规则ID
     *
     * @var string
     */
    public $ruleId;

    /**
     * 星期
     *
     * @var int
     */
    public $week;

    /**
     * 上班签到时间
     *
     * @var array
     */
    public $checkinTime;

    /**
     * 下班签退时间
     *
     * @var array
     */
    public $checkoutTime;

    /**
     * 状态
     *
     * @var boolean
     */
    public $status;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->ruleId       = $record['ruleid'];
        $this->week         = $this->_toInt($record['week']);
        $this->status       = $this->_toBoolean($record['status']);
        $this->checkinTime  = explode(':', Dao_App_Attend_Schedule::formatTime($record['checkintime']));
        $this->checkoutTime = explode(':', Dao_App_Attend_Schedule::formatTime($record['checkouttime']));

        parent::__construct();
    }
}
