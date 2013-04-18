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
 * @version    $Id: Mac.php 33 2010-07-19 07:55:47Z gxx $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Mac extends Oray_Dao_Record
{
	/**
	 * 
	 * @var int
	 */
	public $macId;
	
	/**
	 * 
	 * @var string
	 */
	public $macAddr;
	
	/**
	 * @Construct
	 * 
	 * @param $record
	 */
	public function __construct(array $record)
	{
		$this->macId   = $this->_toInt($record['macid']);
		$this->macAddr = $record['macaddr'];
		
		parent::__construct();
	}
}