<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cycle.php 1387 2011-12-14 02:07:40Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Cycle extends Oray_Dao_Record
{
    /**
     *
     * @var string
     */
    public $cycleId;

    /**
     * 定期模式
     *
     * @var int
     */
    public $mode;

    /**
     * 类型
     *
     * @var int
     */
    public $type;

    /**
     * 几天
     *
     * @var int
     */
    public $day;

    /**
     * 几个星期
     *
     * @var int
     */
    public $week;

    /**
     * 几个月
     *
     * @var int
     */
    public $month;

    /**
     * 多个星期
     *
     * @var array
     */
    public $weeks;

    /**
     * 第几个什么日子
     *
     * @var int
     */
    public $at;

    /**
     * 第几个什么日子
     *
     * @var string
     */
    public $what;

    /**
     *
     * @var unknown_type
     */
    public $period;

    /**
     * 已执行次数
     *
     * @var int
     */
    public $count;

    /**
     * 结束类型
     *
     * @var int
     */
	public $endType;

	/**
	 * 重复次数
	 *
	 * @var int
	 */
	public $endCount;

	/**
	 * 结束日期
	 *
	 * @var int
	 */
	public $endDate;

	/**
	 * 标题是否显示开始日期
	 *
	 * @var int
	 */
	public $displayDate;

	/**
     * 是否保留周期附件
     *
     * @var boolean
     */
    public $isKeepAttach;

	/**
	 * 是否有效(删除周期任务时会标记为无效)
	 *
	 * @var int
	 */
	public $isValid;

	/**
	 *
	 * @param array $record
	 */
	public function __construct(array $record)
	{
	    $this->cycleId  = $record['cycleid'];
	    $this->mode     = $record['mode'];
	    $this->type     = $this->_toInt($record['type']);
	    $this->day      = $this->_toInt($record['day']);
	    $this->week     = $this->_toInt($record['week']);
	    $this->month    = $this->_toInt($record['month']);
	    $this->weeks    = $this->_toArray($record['weeks']);
	    $this->at       = $this->_toInt($record['at']);
	    $this->what     = $record['what'];
	    $this->period   = $this->_toInt($record['period']);
	    $this->count    = $this->_toInt($record['count']);
	    $this->endType  = $this->_toInt($record['endtype']);
	    $this->endCount = $this->_toInt($record['endcount']);
	    $this->endDate  = $this->_toTimestamp($record['enddate']);
	    $this->displayDate  = isset($record['displaydate']) ? $this->_toInt($record['displaydate']) : null;
	    $this->isValid  = $this->_toInt($record['isvalid']);
	    $this->isKeepAttach = isset($record['iskeepattach']) ? $this->_toBoolean($record['iskeepattach']) : null;

		parent::__construct();
	}
}