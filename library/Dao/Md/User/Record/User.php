<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: User.php 1524 2012-01-30 10:00:49Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_User extends Oray_Dao_Record
{

	/**
	 *
	 * @var string
	 */
	public $userId;

	/**
	 *
	 * @var string
	 */
	public $orgId;

	/**
	 *
	 * @var string
	 */
	public $uniqueId;

	/**
	 *
	 * @var string
	 */
	public $deptId;

	/**
	 *
	 * @var string
	 */
	public $castId;

	/**
     *
     * @var string
     */
    public $userName;

	/**
	 *
	 * @var string
	 */
	public $address;

	/**
	 *
	 * @var int
	 */
	public $status;

	/**
	 *
	 * @var boolean
	 */
	public $isShow;

	/**
	 *
	 * @var int
	 */
	public $orderNum;

	/**
	 *
	 * @var string
	 */
	public $adminType;

	/**
	 *
	 * @var int
	 */
	public $adminLevel;

	/**
	 *
	 * @var int
	 */
	public $createTime;

	/**
	 *
	 * @var int
	 */
	public $expireDate;

	/**
	 *
	 * @var int
	 */
	public $maxNdQuota;

	/**
	 *
	 * @var int
	 */
	public $unlockTime;

	/**
	 * 是否被初始化密码
	 *
	 * @var boolean
	 */
	public $initPassword;

	/**
	 *
	 * @var array
	 */
	public $groups = array();

	/**
	 *
	 * @var array
	 */
	public $roles = array();

	/**
	 * Constructor
	 *
	 * @param array $record
	 */
	public function __construct(array $record)
	{
		$this->userId       = $record['userid'];
		$this->orgId        = $record['orgid'];
		$this->address      = $record['userid'] . '@' . $record['orgid'];
		$this->userName     = $record['userid'] . '@' . $record['orgid'];
		$this->deptId       = $record['deptid'];
		$this->castId       = $record['castid'];
		$this->uniqueId     = $record['uniqueid'];
		$this->status       = $this->_toInt($record['status']);
		$this->isShow       = $this->_toBoolean($record['isshow']);
		$this->orderNum     = $this->_toInt($record['ordernum']);
		$this->adminType    = !empty($record['admintype']) ? $record['admintype'] : null;
		$this->adminLevel   = !empty($record['adminlevel']) ? $this->_toInt($record['adminlevel']) : null;
		$this->createTime   = $this->_toTimestamp($record['createtime']);
		$this->expireDate   = $this->_toTimestamp($record['expiredate']);
		$this->unlockTime   = $this->_toTimestamp($record['unlocktime']);
		$this->maxNdQuota   = isset($record['maxndquota']) ? $this->_toInt($record['maxndquota']) : null;
		$this->initPassword = $this->_toBoolean($record['initpassword']);
		$this->groups       = is_array($record['groups'])
    				        ? $record['groups']
    				        : explode(',', $record['groups']);
		$this->roles       = is_array($record['roles'])
    				        ? $record['roles']
    				        : explode(',', $record['roles']);

		parent::__construct();
	}
}