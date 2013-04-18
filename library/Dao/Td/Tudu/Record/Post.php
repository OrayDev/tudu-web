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
 * @version    $Id: Post.php 1584 2012-02-17 09:29:27Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Post extends Oray_Dao_Record
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
	public $boardId;

	/**
	 *
	 * @var string
	 */
	public $uniqueId;

	/**
	 *
	 * @var string
	 */
	public $email;

	/**
	 *
	 * @var string
	 */
	public $userId;

	/**
	 *
	 * @var string
	 */
	public $tuduId;

	/**
	 *
	 * @var string
	 */
	public $postId;

	/**
	 *
	 * @var string
	 */
	public $poster;

	/**
	 *
	 * @var string
	 */
	public $posterInfo;

	/**
	 *
	 * @var string
	 */
	public $header;

	/**
	 *
	 * @var string
	 */
	public $content;

	/**
	 *
	 * @var int
	 */
	public $percent;

	/**
	 *
	 * @var array
	 */
	public $lastModify;

	/**
	 *
	 * @var int
	 */
	public $attachNum;

	/**
	 *
	 * @var int
	 */
	public $elapsedtime;

	/**
	 *
	 * @var boolean
	 */
	public $isFirst;

	/**
     *
     * @var boolean
     */
    public $isForeign;

	/**
	 *
	 * @var boolean
	 */
	public $isLog;

	/**
	 *
	 * @var boolean
	 */
	public $isSend;

	/**
	 *
	 * @var int
	 */
	public $createTime;


    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId        = $record['orgid'];
		$this->boardId      = $record['boardid'];
		$this->tuduId       = $record['tuduid'];
		$this->uniqueId     = $record['uniqueid'];
		$this->email        = $record['email'];
		$userInfo           = explode('@', $record['email']);
		$this->userId       = array_shift($userInfo);
		$this->postId       = $record['postid'];
		$this->poster       = $record['poster'];
		$this->posterInfo   = $record['posterinfo'];
		$this->header       = isset($record['header']) ? Dao_Td_Tudu_Post::praseHeader($record['header']) : null;
		$this->content      = $record['content'];
		$this->percent      = $this->_toInt($record['percent']);
		$this->lastModify   = $this->_toArray($record['lastmodify'], chr(9));
		$this->attachNum    = $this->_toInt($record['attachnum']);
		$this->elapsedtime  = $this->_toInt($record['elapsedtime']);
		$this->createTime   = $this->_toTimestamp($record['createtime']);
		$this->isFirst      = $this->_toBoolean($record['isfirst']);
		$this->isForeign    = $this->_toBoolean($record['isforeign']);
		$this->isLog        = $this->_toBoolean($record['islog']);
		$this->isSend       = $this->_toBoolean($record['issend']);
		$this->isForeign    = $this->_toBoolean($record['isforeign']);

        parent::__construct();
    }
}