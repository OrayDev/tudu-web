<?php
/**
 * 排班方案记录
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Schedule.php 2766 2013-03-05 10:16:20Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Schedule extends Oray_Dao_Record
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
     * 颜色
     *
     * @var string
     */
    public $bgcolor;

    /**
     * 是否系统排班方案
     *
     * @var boolean
     */
    public $isSystem;

    /**
     * 规则ID
     *
     * @var string
     */
    public $ruleId;

    /**
     * 周几
     *
     * @var int
     */
    public $week;

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
     * 工作时长
     *
     * @var int
     */
    public $workTime;

    /**
     * 记录创建时间
     *
     * @var int
     */
    public $createTime;

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
        $this->isSystem     = $this->_toBoolean($record['issystem']);
        $this->bgcolor      = isset($record['bgcolor']) ? $record['bgcolor'] : null;
        $this->ruleId       = isset($record['ruleid']) ? $record['ruleid'] : null;
        $this->week         = isset($record['week']) ? $this->_toInt($record['week']) : null;
        $this->checkinTime  = Dao_App_Attend_Schedule::formatTime($record['checkintime']);
        $this->checkoutTime = Dao_App_Attend_Schedule::formatTime($record['checkouttime']);
        $this->lateStandard = $this->_toInt($record['latestandard']);
        $this->lateCheckin  = $this->_toInt($record['latecheckin']);
        $this->leaveCheckout= $this->_toInt($record['leavecheckout']);
        $this->status       = isset($record['status']) ? $this->_toBoolean($record['status']) : null;
        $this->createTime   = $this->_toTimestamp($record['createtime']);

        if (null !== $record['checkouttime'] && null !== $record['checkintime']) {
            $workTime = (int) $record['checkouttime'] - (int) $record['checkintime'];
        } else {
            $workTime = 0;
        }
        $this->workTime = Dao_App_Attend_Schedule::formatTime($workTime, true);

        parent::__construct();
    }
}