<?php
/**
 * Attend_Month
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Month.php 2735 2013-01-31 10:11:41Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Month extends Oray_Dao_Record
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
     * 用户所属部门
     * @var string
     */
    public $deptName;

    /**
     * 统计日期
     * @var int
     */
    public $date;

    /**
     * 迟到次数
     * @var int
     */
    public $late;

    /**
     * 早退次数
     * @var int
     */
    public $leave;

    /**
     * 旷工次数
     * @var int
     */
    public $unwork;

    /**
     * 是否IP异常
     * @var boolean
     */
    public $isAbnormalIp;

    /**
     * 更新时间
     * @var int
     */
    public $updateTime;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId        = $record['orgid'];
        $this->uniqueId     = $record['uniqueid'];
        $this->trueName     = isset($record['truename']) ? $record['truename'] : null;
        $this->deptName     = isset($record['deptname']) ? $record['deptname'] : null;
        $this->date         = $this->_toInt($record['date']);
        $this->late         = $this->_toInt($record['late']);
        $this->leave        = $this->_toInt($record['leave']);
        $this->unwork       = $this->_toInt($record['unwork']);
        $this->isAbnormalIp = isset($record['isabnormalip']) ? $this->_toBoolean($record['isabnormalip']) : null;
        $this->updateTime   = $this->_toTimestamp($record['updatetime']);

        parent::__construct();
    }
}
