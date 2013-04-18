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
 * @version    $Id: Role.php 763 2011-05-09 01:41:32Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Role extends Oray_Dao_Record
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
	public $roleId;
	
	/**
	 * 
	 * @var string
	 */
	public $roleName;
	
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
	 * Construct
	 * 
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->orgId      = $record['orgid'];
		$this->roleId     = $record['roleid'];
		$this->roleName   = $record['rolename'];
		$this->isSystem   = $record['issystem'];
		$this->isLocked   = $record['islocked'];
		$this->adminLevel = $record['adminlevel'];
		
		parent::__construct();
	}
}