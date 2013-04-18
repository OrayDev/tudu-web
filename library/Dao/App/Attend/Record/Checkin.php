<?php
/**
 * Attend_Checkin
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Checkin.php 1800 2012-04-18 09:59:49Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Checkin extends Oray_Dao_Record
{
    /**
     * 签到ID
     * @var string
     */
    public $checkinId;

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
     * 签到日期
     * @var int
     */
    public $date;

    /**
     * 签到类型
     * @var int
     */
    public $type;

    /**
     * 考勤状态
     * @var int
     */
    public $status;

    /**
     * ip地址
     * @var string
     */
    public $ip;

    /**
     * 地理位置
     * @var string
     */
    public $address;

    /**
     * 签到时间
     * @var int
     */
    public $createTime;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->checkinId  = $record['checkinid'];
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->date       = $this->_toTimestamp($record['date']);
        $this->type       = $this->_toInt($record['type']);
        $this->status     = $this->_toInt($record['status']);
        $this->ip         = long2ip($record['ip']);
        $this->address    = $record['address'];
        $this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}