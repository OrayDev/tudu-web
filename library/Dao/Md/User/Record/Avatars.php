<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Avatar
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Avatars.php 37 2010-07-20 10:27:52Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Avatar
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Avatars extends Oray_Dao_Record
{
	
	/**
	 * 
	 * @var string
	 */
	public $avatarsType;
	
	/**
	 * 
	 * @var binray
	 */
	public $avatars;
	
	/**
	 * 
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->avatarsType = $record['avatarstype'];
		$this->avatars     = $record['avatars'];
		
		parent::__construct();
	}
}