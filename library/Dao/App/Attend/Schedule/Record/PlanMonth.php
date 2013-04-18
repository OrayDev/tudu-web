<?php
/**
 * 排班计划数据(月排班)
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: PlanMonth.php 2509 2012-12-17 10:32:15Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Record_PlanMonth extends Oray_Dao_Record
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
     * @var int
     */
    public $date;

    /**
     *
     * @var array
     */
    public $plan;

    /**
     *
     * @var string
     */
    public $memo;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->orgId    = $record['orgid'];
        $this->uniqueId = $record['uniqueid'];
        $this->date     = $this->_toInt($record['date']);
        $this->plan     = json_decode($record['plan'], true);
        $this->memo     = isset($record['memo']) ? $record['memo'] : null;

        parent::__construct();
    }
}