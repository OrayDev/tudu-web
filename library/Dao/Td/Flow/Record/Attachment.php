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
* @version    $Id: Attachment.php 1846 2012-05-09 01:54:29Z cutecube $
*/

/**
 * @category   Dao
* @package    Dao_Td
* @subpackage Record
* @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
*/
class Dao_Td_Flow_Record_Attachment extends Oray_Dao_Record
{
    /**
     * 文件ID
     *
     * @var string
     */
    public $fileId;

    /**
     * 文件名
     *
     * @var string
     */
    public $fileName;

    /**
     * 文件大小
     *
     * @var int
     */
    public $size;

    /**
     * 文件MIME类型
     *
     * @var string
     */
    public $type;

    /**
     * 保存路径
     *
     * @var string
     */
    public $path;

    /**
     * 工作流ID
     *
     * @var string
     */
    public $flowId;

    /**
     * 上传人ID
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 扩展名
     *
     * @var string
     */
    public $fileExt;

    /**
     * 创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     *
     * @var boolean
     */
    public $isAttach;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->fileId     = $record['fileid'];
        $this->fileName   = $record['filename'];
        $this->fileExt    = isset($record['fileext']) ? array_pop(explode('.', $record['filename'])) : null;
        $this->size       = $this->_toInt($record['size']);
        $this->type       = isset($record['type']) ? $record['type'] : null;
        $this->path       = isset($record['path']) ? $record['path'] : null;
        $this->flowId     = $record['flowid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->isAttach   = isset($record['isattach']) ? $this->_toBoolean($record['isattach']) : null;
        $this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct();
    }
}