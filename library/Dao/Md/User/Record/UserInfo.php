<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: UserInfo.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * @category   Dao
 * @package    Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_UserInfo extends Oray_Dao_Record
{

	/**
	 *
	 * @var string
	 */
	public $trueName;

	/**
	 *
	 * @var string
	 */
	public $nick;

	/**
	 *
	 * @var string
	 */
	public $idNumber;

	/**
	 *
	 * @var string
	 */
	public $position;

	/**
	 *
	 * @var int
	 */
	public $gender;

	/**
	 *
	 * @var int
	 */
	public $birthday;

	/**
	 *
	 * @var string
	 */
	public $mobile;

	/**
	 *
	 * @var string
	 */
	public $tel;

	/**
	 *
	 * @var string
	 */
	public $mailbox;

	/**
	 *
	 * @var string
	 */
	public $officeLocation;

	/**
	 *
	 * @var string
	 */
	public $sign;

	/**
	 *
	 * @var int
	 */
	public $updateTime;

	/**
	 * Constructor
	 *
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->trueName       = $record['truename'];
		$this->nick           = $record['nick'];
		$this->idNumber       = $record['idnumber'];
		$this->position       = $record['position'];
		$this->gender         = $record['gender'];
		$this->birthday       = $this->_toTimestamp($record['birthday']);
		$this->mailbox        = isset($record['mailbox']) ? $record['mailbox'] : null;
		$this->mobile         = $record['mobile'];
		$this->tel            = isset($record['tel']) ? $record['tel'] : null;
		$this->officeLocation = $record['officelocation'];
		$this->sign           = $record['sign'];
		$this->updateTime     = isset($record['updatetime']) ? $this->_toTimestamp($record['updatetime']) : null;

		parent::__construct();
	}
}