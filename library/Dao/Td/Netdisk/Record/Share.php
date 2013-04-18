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
 * @version    $Id: Share.php 1998 2012-07-17 02:41:07Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_Record_Share extends Oray_Dao_Record
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
    public $objectId;

    /**
     * 
     * @var string
     */
    public $ownerId;

    /**
     * 
     * @var string
     */
    public $targetId;

    /**
     * 
     * @var string
     */
    public $objectType;

    /**
     * 
     * @var string
     */
    public $ownerInfo;

    /**
     * 
     * @var string
     */
    public $folderName;

    /**
     * 
     * @var bool
     */
    public $isSystem;

    /**
     * 
     * @var int
     */
    public $size;

    /**
     * 
     * @var string
     */
    public $fileName;

    /**
     * 
     * @var int
     */
    public $createTime;

    /**
     * 
     * @var string
     */
    public $ownerEmail;

    /**
     * 
     * @var string
     */
    public $ownerTrueName;

    /**
     * 
     * @var string
     */
    public $fromUniqueId;

    /**
     * 
     * @var string
     */
    public $fromFileId;
    
    /**
     *
     * @var string
     */
    public $attachFileId;

    /**
     *
     * @var string
     */
    public $isFromAttach;

    /**
     * 
     * Constructor
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->objectId   = $record['objectid'];
        $this->ownerId    = $record['ownerid'];
        $this->targetId   = $record['targetid'];
        $this->objectType = $record['objecttype'];
        $this->ownerInfo  = $record['ownerinfo'];
        $this->orgId      = isset($record['orgid']) ? $record['orgid'] : null;
        $this->folderName = isset($record['foldername']) ? $record['foldername'] : null;
        $this->isSystem   = isset($record['issystem']) ? $this->_toBoolean($record['issystem']) : null;
        $this->fileName   = isset($record['filename']) ? $record['filename'] : null;
        $this->size       = isset($record['size']) ? $record['size'] : null;
        $this->createTime = isset($record['createtime']) ? $this->_toTimestamp($record['createtime']) : null;

        if (isset($record['ownerinfo']) || !empty($record['ownerinfo'])) {
			$array = explode("\n", $record['ownerinfo']);
			$this->ownerEmail    = $array[0];
	        $this->ownerTrueName = $array[1];
		}

		$this->fromUniqueId = isset($record['fromuniqueid']) ? $record['fromuniqueid'] : null;
        $this->fromFileId   = isset($record['fromfileid']) ? $record['fromfileid'] : null;
        $this->attachFileId = isset($record['attachfileid']) ? $record['attachfileid'] : null;
        $this->isFromAttach = isset($record['isfromattach']) ? $this->_toBoolean($record['isfromattach']) : null;

        parent::__construct();
    }
}