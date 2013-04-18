<?php
/**
 * 排班计划数据操作
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Adjust.php 1880 2012-05-23 10:44:12Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Schedule_Record_Adjust extends Oray_Dao_Record
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
    public $adjustId;

    /**
     *
     * @var string
     */
    public $subject;

    /**
     *
     * @var int
     */
    public $startTime;

    /**
     *
     * @var int
     */
    public $endTime;

    /**
     *
     * @var int
     */
    public $type;

    /**
     *
     * @var int
     */
    public $createTime;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->adjustId   = $record['adjustid'];
        $this->subject    = $record['subject'];
        $this->startTime  = $this->_toTimestamp($record['starttime']);
        $this->endTime    = $this->_toTimestamp($record['endtime']);
        $this->type       = $this->_toInt($record['type']);
        $this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}