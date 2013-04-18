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
 * @version    $Id: Cycle.php 1368 2011-12-09 00:44:07Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
 class Dao_Td_Tudu_Record_Flow extends Oray_Dao_Record
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
    * @var array
    */
    public $steps;

    /**
    *
    * @var string
    */
    public $currentStepId;

    /**
    *
    * @var string
    */
    public $flowId;

    /**
    *
    * @param array $record
    */
    public function __construct(array $record)
    {
        $this->orgId  = $record['orgid'];
        $this->tuduId = $record['tuduid'];
        $this->currentStepId = $record['currentstepid'];
        $this->flowId = $record['flowid'];
        $this->steps  = Dao_Td_Tudu_Flow::parseSteps($record['steps']);

        parent::__construct();
    }

    /**
    *
    * @return array
    */
    public function getCurrentStep()
    {
        if (isset($this->steps[$this->currentStepId])) {
            return $this->steps[$this->currentStepId];
        }

        return null;
    }

    /**
    *
    * @return array
    */
    public function getCurrentUsers()
    {
        $step = $this->getCurrentStep();

        if (null == $step) {
            return null;
        }

        $currentSectionIndex = $step['currentsection'];
        $sections = $step['sections'];

        foreach ($sections as $idx => $item) {
            if ($idx == $currentSectionIndex) {
                return $item;
            }
        }

        return null;
    }
 }