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
 * @version    $Id: Folder.php 1251 2011-11-07 03:24:44Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_Record_Folder extends Oray_Dao_Record
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
     * @var string
     */
    public $folderId;
    
    /**
     * 
     * @var string
     */
    public $parentFolderId;
    
    /**
     * 
     * @var string
     */
    public $folderName;
    
    /**
     * 
     * @var boolean
     */
    public $isSystem;
    
    /**
     * 
     * @var boolean
     */
    public $isShare;
    
    /**
     * 
     * @var int
     */
    public $folderSize;
    
    /**
     * 
     * @var int
     */
    public $maxQuota;
    
    /**
     * 
     * @var int
     */
    public $createTime;
    
    /**
     * 
     * Constructor
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId          = $record['orgid'];
        $this->uniqueId       = $record['uniqueid'];
        $this->folderId       = $record['folderid'];
        $this->parentFolderId = $record['parentfolderid'];
        $this->folderName     = $record['foldername'];
        $this->isSystem       = $this->_toBoolean($record['issystem']);
        $this->isShare        = $this->_toBoolean($record['isshare']);
        $this->folderSize     = $this->_toInt($record['foldersize']);
        $this->maxQuota       = $this->_toInt($record['maxquota']);
        $this->createTime     = $this->_toTimestamp($record['createtime']);
        
        parent::__construct();
    }
}