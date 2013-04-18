<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Contact_Record_Group extends Oray_Dao_Record
{
    /**
     * 
     * @var string
     */
    public $groupId;
    
    /**
     * 
     * @var string
     */
    public $uniqueId;
    
    /**
     * 
     * @var boolean
     */
    public $isSystem;
    
    /**
     * 
     * @var string
     */
    public $groupName;
    
    /**
     * 
     * @var string
     */
    public $bgColor;
    
    /**
     * 
     * @var int
     */
    public $orderNum;
    
    /**
     * Constructor
     * 
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->groupId   = $record['groupid'];
        $this->uniqueId  = $record['uniqueid'];
        $this->isSystem  = $this->_toBoolean($record['issystem']);
        $this->groupName = $record['groupname'];
        $this->bgColor   = $record['bgcolor'];
        $this->orderNum  = $this->_toInt($record['ordernum']);
        
        parent::__construct();
    }
}