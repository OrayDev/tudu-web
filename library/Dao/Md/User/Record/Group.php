<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Group.php 828 2011-05-24 10:14:50Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Group extends Oray_Dao_Record
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
	public $groupId;
	
	/**
	 * 
	 * @var string
	 */
	public $groupName;
	
	/**
	 * 
	 * @var boolean
	 */
	public $isSystem;
	
	/**
	 * 
	 * @var boolean
	 */
	public $isLocked;
	
	/**
	 * 
	 * @var int
	 */
	public $adminLevel;
	
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
		$this->orgId      = $record['orgid'];
		$this->groupId    = $record['groupid'];
		$this->groupName  = $record['groupname'];
		$this->isSystem   = $record['issystem'];
		$this->isLocked   = $record['islocked'];
		$this->adminLevel = $record['adminlevel'];
		$this->orderNum   = $this->_toInt($record['ordernum']);
		
		parent::__construct();
	}
}