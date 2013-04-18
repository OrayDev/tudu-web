<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Step.php 2341 2012-11-06 05:48:08Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Step extends Oray_Dao_Record
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
    public $tuduId;

    /**
     *
     * @var string
     */
    public $stepId;

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     *
     * @var string
     */
    public $prevStepId;

    /**
     *
     * @var string
     */
    public $nextStepId;

    /**
     *
     * @var int
     */
    public $type;
    
    /**
     *
     * @var int
     */
    public $stepStatus;

    /**
     *
     * @var string
     */
    public $isDone;

    /**
     *
     * @var boolean
     */
    public $isShow;

    /**
     *
     * @var int
     */
    public $percent;

    /**
     *
     * @var string
     */
    public $to;

    /**
     *
     * @var int
     */
    public $orderNum;

    /**
     *
     * @var int
     */
    public $createTime;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->tuduId     = $record['tuduid'];
        $this->stepId     = $record['stepid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->prevStepId = $record['prevstepid'];
        $this->nextStepId = $record['nextstepid'];
        $this->type       = $this->_toInt($record['type']);
        $this->stepStatus = isset($record['stepstatus']) ? $this->_toInt($record['stepstatus']) : null;
        $this->isDone     = $this->_toBoolean($record['isdone']);
        $this->orderNum   = $this->_toInt($record['ordernum']);
        $this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}