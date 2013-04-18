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
 * @version    $Id: File.php 1251 2011-11-07 03:24:44Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Netdisk_Record_File extends Oray_Dao_Record
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
    public $fileId;

    /**
     *
     * @var string
     */
    public $fileName;

    /**
     *
     * @var int
     */
    public $size;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $path;

    /**
     *
     *
     * @var boolean
     */
    public $isFromAttach;

    /**
     *
     * @var string
     */
    public $attachFileId;

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
     * @var boolean
     */
    public $isShare;

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
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->folderId   = $record['folderid'];
        $this->fileId     = $record['fileid'];
        $this->fileName   = $record['filename'];
        $this->size       = $this->_toInt($record['size']);
        $this->type       = $record['type'];
        $this->path       = $record['path'];
        $this->isFromAttach = $this->_toBoolean($record['isfromattach']);
        $this->attachFileId = $record['attachfileid'];
        $this->fromUniqueId = $record['fromuniqueid'];
        $this->fromFileId   = $record['fromfileid'];
        $this->isShare    = $this->_toBoolean($record['isshare']);

        $this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}