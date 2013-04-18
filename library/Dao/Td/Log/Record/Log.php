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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Log_Record_Log extends Oray_Dao_Record
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
    public $targetType;
    
    /**
     * 
     * @var string
     */
    public $targetId;
    
    /**
     * 
     * @var string
     */
    public $uniqueId;
    
    /**
     * 
     * @var string
     */
    public $operator;
    
    /**
     * 
     * @var string
     */
    public $email;
    
    /**
     * 
     * @var string
     */
    public $trueName;
    
    /**
     * 
     * @var boolean
     */
    public $privacy;
    
    /**
     * 
     * @var string
     */
    public $action;
    
    /**
     * 
     * @var mixed
     */
    public $detail;
    
    /**
     * 
     * @var int
     */
    public $logTime;
    
    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->targetType = $record['targettype'];
        $this->targetId   = $record['targetid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->operator   = $record['operator'];
        $this->privacy    = $this->_toBoolean($record['privacy']);
        $this->action     = $record['action'];
        $this->detail     = !empty($record['detail']) ? @unserialize($record['detail']) : null;
        $this->logTime    = $this->_toTimestamp($record['logtime']);
        
        if (!empty($this->operator)) {
            $arr = explode(' ', $this->operator, 2);
            $this->email    = $arr[0];
            $this->trueName = $arr[1];
        }
        
        parent::__construct();
    }
}