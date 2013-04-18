<?php
/**
 * Attend_Record_Date
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Date.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Date extends Oray_Dao_Record
{
    /**
     * 企业组织ID
     * @var string
     */
    public $orgId;

    /**
     * 用户唯一ID
     * @var string
     */
    public $uniqueId;

    /**
     * 用户真实姓名
     * @var string
     */
    public $trueName;

    /**
     * 用户所在部门
     * @var string
     */
    public $deptName;

    /**
     * 日期
     * @var int
     */
    public $date;

    /**
     * 是否迟到
     * @var boolean
     */
    public $isLate;

    /**
     * 是否早退
     * @var boolean
     */
    public $isLeave;

    /**
     * 是否旷工
     * @var boolean
     */
    public $isWork;

    /**
     * 是否IP异常
     * @var boolean
     */
    public $isAbnormalIp;

    /**
     * 签到、签退状态
     * @var int
     */
    public $checkinStatus;

    /**
     * 工作时长
     * @var int
     */
    public $workTime;

    /**
     * 更新时间
     * @var int
     */
    public $updateTime;

    /**
     * 备注
     *
     * @var string
     */
    public $memo;

    /**
     * 考勤分类ID
     *
     * @var array
     */
    public $categories;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->trueName   = isset($record['truename']) ? $record['truename'] : null;
        $this->deptName   = isset($record['deptname']) ? $record['deptname'] : null;
        $this->date       = $this->_toTimestamp($record['date']);
        $this->isLate     = $this->_toBoolean($record['islate']);
        $this->isLeave    = $this->_toBoolean($record['isleave']);
        $this->isWork     = $this->_toBoolean($record['iswork']);
        $this->isAbnormalIp  = isset($record['isabnormalip']) ? $this->_toBoolean($record['isabnormalip']) : null;
        $this->checkinStatus = Dao_App_Attend_Date::formatCheckinStatus($this->_toInt($record['checkinstatus']));
        $this->workTime   = $this->_toInt($record['worktime']);
        $this->memo       = isset($record['memo']) ? Dao_App_Attend_Date::formatMemo($record['memo']) : null;
        $this->updateTime = $this->_toTimestamp($record['updatetime']);
        $this->categories = !empty($record['categories']) ? explode(',', $record['categories']) : array();

        parent::__construct();
    }
}