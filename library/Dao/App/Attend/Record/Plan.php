<?php
/**
 * 排班方案记录
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Plan.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Plan extends Oray_Dao_Record
{
    /**
     * 所属组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 用户唯一ID
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 
     * @var string
     */
    public $planId;

    /**
     * 
     * @var int
     */
    public $type;

    /**
     *
     * @var int
     */
    public $cycleNum;

    /**
     * 
     * @var int
     */
    public $value;

    /**
     * 排班方案ID
     *
     * @var string
     */
    public $scheduleId;

    /**
     * 排班方案名称
     *
     * @var string
     */
    public $name;

    /**
     * 上班时间
     *
     * @var int
     */
    public $checkinTime;

    /**
     * 下班时间
     *
     * @var int
     */
    public $checkoutTime;

    /**
     * 迟到标准
     *
     * @var int
     */
    public $lateStandard;

    /**
     * 迟到 旷工时间
     *
     * @var int
     */
    public $lateCheckin;

    /**
     * 早退标准
     *
     * @var int
     */
    public $leaveStandard;

    /**
     * 早退 旷工时间
     *
     * @var int
     */
    public $leaveCheckout;

    /**
     * 状态
     *
     * @var boolean
     */
    public $status;

    /**
     *
     * @var int
     */
    public $startTime;

    /**
     *
     * @var int
     */
    public $endTime;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId        = $record['orgid'];
        $this->uniqueId     = $record['uniqueid'];
        $this->scheduleId   = $record['scheduleid'];
        $this->name         = $record['name'];
        $this->planId       = $record['planid'];
        $this->type         = $record['type'];
        $this->value        = $record['value'];
        $this->cycleNum     = $record['cyclenum'];
        $this->checkinTime  = Dao_App_Attend_Schedule::formatTime($record['checkintime']);
        $this->checkoutTime = Dao_App_Attend_Schedule::formatTime($record['checkouttime']);
        $this->lateStandard = isset($record['latestandard']) ? $this->_toInt($record['latestandard']) : null;
        $this->lateCheckin  = isset($record['latecheckin']) ? $this->_toInt($record['latecheckin']) : null;
        $this->leaveStandard= isset($record['leavestandard']) ? $this->_toInt($record['leavestandard']) : null;
        $this->leaveCheckout= isset($record['leavecheckout']) ? $this->_toInt($record['leavecheckout']) : null;
        $this->status       = isset($record['status']) ? $this->_toBoolean($record['status']) : null;
        $this->startTime    = isset($record['starttime']) ? $this->_toTimestamp($record['starttime']) : null;
        $this->endTime      = isset($record['endtime']) ? $this->_toTimestamp($record['endtime']) : null;

        parent::__construct();
    }
}