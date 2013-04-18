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
 * @version    $Id: Meeting.php 774 2011-05-10 11:18:13Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Meeting extends Oray_Dao_Record
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
     * 分类
     * 
     * @var string
     */
    public $notifyType;
    
    /**
     * 分类
     * 
     * @var string
     */
    public $notifyTime;
    
    /**
     * 
     * @var string
     */
    public $location;
    
    /**
     * 
     * @var int
     */
    public $isAllday;
    
    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId     = $record['orgid'];
        $this->tuduId    = $record['tuduid'];
        $this->notifyTime= $this->_toInt($record['notifytime']);
        $this->notifyType= $this->_toInt($record['notifytype']);
        $this->location  = $record['location'];
        $this->isAllday  = $this->_toBoolean($record['isallday']);

        parent::__construct();
    }
}