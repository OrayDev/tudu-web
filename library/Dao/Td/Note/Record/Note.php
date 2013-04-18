<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Note
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Note.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Note
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Note_Record_Note extends Oray_Dao_Record
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
    public $noteId;

    /**
     *
     * @var string
     */
    public $tuduId;

    /**
     *
     * @var string
     */
    public $subject;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var int
     */
    public $color;

    /**
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var int
     */
    public $createTime;

    /**
     *
     * @var int
     */
    public $updateTime;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->orgId    = $record['orgid'];
        $this->uniqueId = $record['uniqueid'];
        $this->noteId   = $record['noteid'];
        $this->tuduId   = isset($record['tuduid']) ? $record['tuduid'] : null;
        $this->subject  = isset($record['subject']) ? $record['subject'] : null;
        $this->content  = $record['content'];
        $this->status   = $this->_toInt($record['status']);
        $this->color    = $this->_toInt($record['color']);
        $this->createTime = $this->_toTimestamp($record['createtime']);
        $this->updateTime = $this->_toTimestamp($record['updatetime']);

        parent::__construct();
    }
}