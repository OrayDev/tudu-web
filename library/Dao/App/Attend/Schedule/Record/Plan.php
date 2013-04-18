<?php
/**
 * 排班计划数据操作
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Plan.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Record_Plan extends Oray_Dao_Record
{

    /**
     *
     * @var string
     */
    public $orgId;

    /**
     *
     * @var int
     */
    public $date;

    /**
     *
     * @var int
     */
    public $memo;

    /**
     *
     * @var int
     */
    public $updateTime;

    /**
     *
     * @var int
     */
    public $uniqueId;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = isset($record['uniqueid']) ? $record['uniqueid'] : null;
        $this->date       = $this->_toInt($record['date']);
        $this->memo       = $record['memo'];
        $this->updateTime = $this->_toTimestamp($record['updatetime']);

        parent::__construct();
    }
}