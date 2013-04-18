<?php
/**
 * Attend_Category
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Apply.php 2764 2013-03-01 10:13:53Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Apply extends Oray_Dao_Record
{

    /**
     * 申请ID
     *
     * @var stirng
     */
    public $applyId;

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 申请流程的图度ID
     *
     * @var string
     */
    public $tuduId;

    /**
     * 申请类型ID
     *
     * @var string
     */
    public $categoryId;

    /**
     * 申请人
     *
     * @var string
     */
    public $userInfo;

    /**
     * 申请人姓名
     *
     * @var string
     */
    public $trueName;

    /**
     *
     * @var string
     */
    public $userName;

    /**
     *
     * @var string
     */
    public $deptName;

    /**
     * 申请人唯一ID
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 创建人唯一ID
     *
     * @var string
     */
    public $senderId;

    /**
     * 时间类型
     *
     * @var bool
     */
    public $isAllday;

    /**
     * 补签类型
     * 
     * @var int
     */
    public $checkinType;

    /**
     * 申请起始时间
     *
     * @var int
     */
    public $startTime;

    /**
     * 申请结束时间
     *
     * @var int
     */
    public $endTime;

    /**
     * 状态
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var 总时间
     */
    public $period;

    /**
     * 记录创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     * 类型名称
     *
     * @var string
     */
    public $categoryName;

    /**
     *
     * @var int
     */
    public $reviewStatus;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->applyId    = $record['applyid'];
        $this->orgId      = $record['orgid'];
        $this->tuduId     = $record['tuduid'];
        $this->categoryId = $record['categoryid'];
        $this->userInfo   = $record['userinfo'];
        $this->uniqueId   = $record['uniqueid'];
        $this->senderId   = $record['senderid'];
        $this->status     = $this->_toInt($record['status']);
        $this->startTime  = $this->_toTimeStamp($record['starttime']);
        $this->endTime    = $this->_toTimeStamp($record['endtime']);
        $this->createTime = $this->_toTimeStamp($record['createtime']);
        $this->period     = $this->_toFloat($record['period']);
        $this->isAllday   = isset($record['isallday']) ? $this->_toBoolean($record['isallday']) : null;
        $this->checkinType= isset($record['checkintype']) ? $this->_toInt($record['checkintype']) : null;

        list($trueName, $userName) = explode(' ', $this->userInfo);

        $this->trueName = $trueName;
        $this->userName = $userName;

        $this->deptName     = isset($record['deptname']) ? $record['deptname'] : null;
        $this->categoryName = isset($record['categoryname']) ? $record['categoryname'] : null;
        $this->reviewStatus = isset($record['reviewstatus']) && is_numeric($record['reviewstatus']) ? $this->_toInt($record['reviewstatus']) : null;

        parent::__construct();
    }
}