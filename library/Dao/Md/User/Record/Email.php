<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Email extends Oray_Dao_Record
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
    public $userId;
    
    /**
     * 
     * @var string
     */
    public $address;
    
    /**
     * 
     * @var string
     */
    public $uId;
    
    /**
     * 
     * @var string
     */
    public $domainName;
    
    /**
     * 
     * @var string
     */
    public $password;
    
    /**
     * 
     * @var string
     */
    public $protocol;
    
    /**
     * 
     * @var string
     */
    public $host;
    
    /**
     * 
     * @var int
     */
    public $port;
    
    /**
     * 
     * @var boolean
     */
    public $isSsl;
    
    /**
     * 
     * @var int
     */
    public $type;
    
    /**
     * 
     * @var int
     */
    public $orderNum;
    
    /**
     * 
     * @var string
     */
    public $lastCheckInfo;
    
    /**
     * 
     * @var int
     */
    public $unreadNum;
    
    /**
     * 
     * @var string
     */
    public $lastMailId;
    
    /**
     * 
     * @var string
     */
    public $lastMailSubject;
    
    /**
     * 
     * @var string
     */
    public $lastMailFrom;
    
    /**
     * 
     * @var int
     */
    public $lastCheckTime;
    
    /**
     * Constructor
     * 
     * @param array $records
     */
    public function __construct(array $record)
    {
        $this->orgId   = $record['orgid'];
        $this->userId  = $record['userid'];
        $this->address = $record['address'];
        $this->password = $record['password'];
        $this->protocol = $record['protocol'];
        $this->host     = $record['host'];
        $this->port     = $this->_toInt($record['port']);
        $this->isSsl    = $this->_toBoolean($record['isssl']);
        $this->type     = $this->_toInt($record['type']);
        $this->orderNum = $this->_toInt($record['ordernum']);
        
        $this->lastCheckInfo = empty($record['lastcheckinfo']) ? null : explode("\n", $record['lastcheckinfo'], 3);
        $this->lastCheckTime = isset($record['lastchecktime']) ? $this->_toTimestamp($record['lastchecktime']) : null;
        
        $arr = explode('@', $this->address);
        $this->domainName = $arr[1];
        $this->uId        = $arr[0];
        
        if (is_array($this->lastCheckInfo)) {
            $this->unreadNum = $this->_toInt($this->lastCheckInfo[0]);
            
            if (!empty($this->lastChcekInfo[1])) {
                $this->lastMailId = $this->lastChcekInfo[1];
            }
            
            if (!empty($this->lastChcekInfo[2])) {
                $this->lastMailSubject = $this->lastChcekInfo[2];
            }
            
            if (!empty($this->lastChcekInfo[3])) {
                $this->lastMailFrom = $this->lastChcekInfo[3];
            }
        }
        
        parent::__construct();
    }
}