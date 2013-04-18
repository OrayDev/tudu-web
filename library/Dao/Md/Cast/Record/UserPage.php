<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: UserPage.php 228 2010-08-20 11:34:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Cast_Record_UserPage extends Oray_Dao_Record
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
    public $domainName;
    
    /**
     * 
     * @var string
     */
    public $address;
    
    /**
     * 
     * @var string
     */
    public $uniqueId;
    
    /**
     * 部门ID
     * 
     * @var string
     */
    public $deptId;
    
    /**
     * 
     * @var string
     */
    public $tel;
    
    /**
     * 
     * @var string
     */
    public $mobile;
    
    /**
     * 
     * @var string
     */
    public $deptName;
    
    /**
     * 
     * @var string
     */
    public $position;
    
    /**
     * 
     * @var string
     */
    public $trueName;
    
    /**
     * Constructor
     * 
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->userId     = $record['userid'];
        $this->domainName = $record['domainname'];
        $this->address    = $record['userid'] . '@' . $record['domainname'];
        $this->uniqueId   = $record['uniqueid'];
        $this->deptId     = $record['deptid'];
        $this->trueName   = $record['truename'];
        $this->mobile     = $record['mobile'];
        $this->tel        = $record['tel'];
        $this->position   = $record['position'];
        $this->deptName   = $record['deptname'];
        
        parent::__construct();
    }
}