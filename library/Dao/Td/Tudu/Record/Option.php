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
 * @version    $Id: Option.php 304 2010-09-07 08:17:11Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Option extends Oray_Dao_Record
{
	
	/**
	 * 
	 * @var string
	 */
	public $tuduId;
	
	/**
	 * 
	 * @var string
	 */
	public $optionId;
	
	/**
	 * 
	 * @var string
	 */
	public $text;
	
	/**
	 * 
	 * @var int
	 */
	public $voteCount;
	
	/**
	 * 
	 * @var int
	 */
	public $orderNum;
	
	/**
	 * 
	 * @var array
	 */
	public $voters;
	
	/**
	 * Construct
	 * 
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->tuduId    = $record['tuduid'];
		$this->optionId  = $record['optionid'];
		$this->text      = $record['text'];
		$this->voteCount = $this->_toInt($record['votecount']);
		$this->orderNum  = isset($record['ordernum']) ? $this->_toInt($record['ordernum']) : null;
		$this->voters    = isset($record['voters']) ? $this->_formatVoters($record['voters']) : null;
		
		parent::__construct();
	}
	
	/**
	 * 
	 * @param string $voters
	 */
	private function _formatVoters($voters)
	{
		if (!$voters) return null;
		
		$arr = explode("\n", $voters);
		
		return $arr;
	}
}