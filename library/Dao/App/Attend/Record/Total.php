<?php
/**
 * Attend_Total
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Total.php 1957 2012-07-02 06:54:25Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Total extends Oray_Dao_Record
{
    /**
     * 考勤分类ID
     * @var string
     */
    public $categoryId;

    /**
     * 考勤分类名称
     * @var string
     */
    public $categoryName;

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
     * 统计结果
     * @var float
     */
    public $total;

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
        $this->categoryId   = $record['categoryid'];
        $this->categoryName = $record['categoryname'];
        $this->orgId        = $record['orgid'];
        $this->uniqueId     = $record['uniqueid'];
        $this->trueName     = isset($record['truename']) ? $record['truename'] : null;
        $this->deptName     = isset($record['deptname']) ? $record['deptname'] : null;
        $this->date         = $this->_toInt($record['date']);
        $this->total        = $this->_toFloat($record['total']);
        $this->updateTime   = $this->_toTimestamp($record['updatetime']);

        parent::__construct();
    }
}