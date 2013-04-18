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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Mailbox extends Oray_Dao_Record
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
	public $userId;
	
	/**
	 * 
	 * @var string
	 */
	public $address;
	
	/**
	 * 
	 * @var string
	 */
	public $password;
	
	/**
	 * 
	 * @var int
	 */
	public $type;
	
	/**
	 * 邮箱@前部分
	 * 
	 * @var string
	 */
	public $uid;
	
	/**
	 * 邮箱域名
	 * 
	 * @var string
	 */
	public $domainName;
	
	/**
	 * 
	 * @var string
	 */
	public $imapHost;
	
	/**
	 * 
	 * @var int
	 */
	public $port;
	
	/**
	 * 
	 * @var boolean
	 */
	public $isSsl;
	
	/**
	 * Constructor
	 * 
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->orgId    = $record['orgid'];
		$this->userId   = $record['userid'];
		$this->address  = $record['address'];
		$this->password = $record['password'];
		$this->imapHost = $record['imaphost'];
		$this->port     = $this->_toInt($record['port']);
		$this->isSsl    = $this->_toBoolean($record['isssl']);
		$this->type     = $this->_toInt($record['type']);
		
		$arr = explode('@', $this->address);
		$this->domainName = $arr[1];
		$this->uid        = $arr[0];
		
		parent::__construct();
	}
}