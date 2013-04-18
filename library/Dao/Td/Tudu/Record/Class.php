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
 * @version    $Id: Class.php 397 2010-10-20 10:54:08Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Class extends Oray_Dao_Record
{
	/**
	 * 
	 * @var string
	 */
	public $orgId;
	
	/**
	 * 板块ID
	 * 
	 * @var string
	 */
	public $boardId;
	
	/**
	 * 分类
	 * 
	 * @var string
	 */
	public $classId;
	
	/**
	 * 
	 * @var string
	 */
	public $className;
	
	
	/**
	 * 
	 * @var int
	 */
	public $orderNum;
	
	
    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
	    $this->orgId     = $record['orgid'];
		$this->boardId   = $record['boardid'];
		$this->classId   = $record['classid'];
		$this->className = $record['classname'];
		$this->orderNum  = $this->_toInt($record['ordernum']);

        parent::__construct();
    }
}