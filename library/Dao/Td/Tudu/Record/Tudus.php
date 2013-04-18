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
 * @version    $Id: Tudus.php 549 2010-12-31 09:49:57Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Tudus extends Oray_Dao_Record
{
	/**
	 * 组织ID
	 * 
	 * @var string
	 */
	public $orgId;
	
	/**
	 * 版块ID
	 * 
	 * @var string
	 */
	public $boardId;
	
	/**
	 * 图度ID
	 * 
	 * @var string
	 */
	public $tuduId;
	
	/**
	 * 发起人
	 * 
	 * @var string
	 */
	public $from;
	
	/**
	 * 执行人
	 * 
	 * @var string
	 */
	public $to;
	
	/**
	 * 标题
	 * 
	 * @var string
	 */
	public $subject;
	
	/**
	 * 发起人
	 * 
	 * @var string
	 */
	public $sender;
	
	/**
	 * 是否草稿
	 * 
	 * @var boolean
	 */
	public $isDraft;
	
	/**
	 * 节点类型
	 * 
	 * @var string
	 */
	public $nodeType;
	
	/**
	 * 父级ID
	 * 
	 * @var string
	 */
	public $parentId;
	
	/**
	 * 是否图度组
	 * 
	 * @var boolean
	 */
	public $isTuduGroup;
	
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
		$this->from         = Dao_Td_Tudu_Tudu::formatAddress($record['from'], true);
		$this->to           = Dao_Td_Tudu_Tudu::formatAddress($record['to'], false);
		$this->isDraft      = $this->_toBoolean($record['isdraft']);
		$this->subject      = $record['subject'];
		$this->nodeType     = $record['nodetype'];
		$this->parentId     = $record['parentid'];
		$this->isTuduGroup  = in_array($this->nodeType, array(Dao_Td_Tudu_Group::TYPE_NODE, Dao_Td_Tudu_Group::TYPE_ROOT));
    	
    	if (isset($this->from[3])) {
    	    $this->sender = $this->from[3];
    	}
    	
        parent::__construct();
    }
}