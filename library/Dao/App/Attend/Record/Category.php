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
 * @version    $Id: Category.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_Category extends Oray_Dao_Record
{
    /**
     * 考勤分类ID
     * @var string
     */
    public $categoryId;

    /**
     * 企业组织ID
     * @var string
     */
    public $orgId;

    /**
     * 考勤分类名称
     * @var string
     */
    public $categoryName;

    /**
     * 审批流程
     * @var array
     */
    public $flowSteps;

    /**
     * 考勤分类状态
     * @var int
     */
    public $status;

    /**
     * 是否系统考勤分类
     * @var boolean
     */
    public $isSystem;

    /**
     * 创建时间
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
        $this->categoryId   = $record['categoryid'];
        $this->orgId        = $record['orgid'];
        $this->categoryName = $record['categoryname'];
        $this->flowSteps    = Dao_App_Attend_Category::formatSteps($record['flowsteps']);
        $this->status       = $this->_toInt($record['status']);
        $this->isSystem     = $this->_toBoolean($record['issystem']);
        $this->createTime   = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}