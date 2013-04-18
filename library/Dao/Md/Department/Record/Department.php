<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage Md
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Department.php 806 2011-05-19 01:07:31Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage Md
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Department_Record_Department extends Oray_Dao_Record
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
	public $deptId;
	
	/**
	 * 
	 * @var string
	 */
	public $deptName;
	
	/**
	 * 
	 * @var string
	 */
	public $parentId;
	
	/**
	 * 
	 * @var int
	 */
	public $orderNum;
	
	/**
	 * 
	 * @var boolean
	 */
	public $firstNode;
	
	/**
	 * 
	 * @var boolean
	 */
	public $lastNode;
	
	/**
	 * 
	 * @var int
	 */
	public $depth;
	
	/**
	 * 
	 * @var string
	 */
	public $prefix;
	
	/**
	 * 
	 * @var string
	 */
	public $moderators;
	
	/**
	 * 
	 * @var array
	 */
	public $path;
	
	/**
	 * Construct
	 * 
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->orgId    = $record['orgid'];
		$this->deptId   = $record['deptid'];
		$this->deptName = $record['deptname'];
		$this->parentId = !empty($record['parentid']) ? $record['parentid'] : null;
		$this->orderNum = $record['ordernum'];
		$this->moderators = Dao_Md_Department_Department::formatModerator($record['moderators']);
		
		$this->firstNode = isset($record['firstnode']) && $record['firstnode'] ? true : false;
		$this->lastNode  = isset($record['lastnode']) && $record['lastnode'] ? true : false;
		$this->depth     = isset($record['depth']) ? $this->_toInt($record['depth']) : null;
		$this->prefix   = !empty($record['prefix']) ? $record['prefix'] : null;
		$this->path      = isset($record['path']) ? $record['path'] : array();
		
		parent::__construct();
	}
}