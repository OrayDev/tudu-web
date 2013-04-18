<?php
/**
 * 排班计划数据(周排班)
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: PlanWeek.php 2518 2012-12-19 10:03:52Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Record_PlanWeek extends Oray_Dao_Record
{
    /**
     *
     * @var string
     */
    public $orgId;

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     *
     * @var array
     */
    public $plan;

    /**
     *
     * @var string
     */
    public $cycleNum;

    /**
     *
     * @var string
     */
    public $memo;

    /**
     *
     * @var int
     */
    public $effectDate;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->plan       = json_decode($record['plan'], true);
        $this->cycleNum   = $this->_toInt($record['cyclenum']);
        $this->memo       = isset($record['memo']) ? $record['memo'] : null;
        $this->effectDate = $this->_toTimestamp($record['effectdate']);

        parent::__construct();
    }
}