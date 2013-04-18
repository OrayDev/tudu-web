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
 * @version    $Id: UserPage.php 1613 2012-02-21 10:16:47Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_UserPage extends Oray_Dao_Record
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
    public $userName;

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 部门ID
     *
     * @var string
     */
    public $deptId;

    /**
     *
     * @var int
     */
    public $gender;

    /**
     *
     * @var string
     */
    public $deptName;

    /**
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var string
     */
    public $trueName;

    /**
     *
     * @var int
     */
    public $createTime;

    /**
     *
     * @var int
     */
    public $unlockTime;

    /**
     *
     * @var array
     */
    public $groups;

    /**
     *
     * @var array
     */
    public $roles;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->userId     = $record['userid'];
        $this->userName   = $record['userid'] . '@' . $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->deptId     = $record['deptid'];
        $this->gender     = $record['gender'];
        $this->status     = $this->_toInt($record['status']);
        $this->trueName   = trim($record['truename']);
        $this->deptName   = $record['deptname'];
        $this->createTime = $this->_toTimestamp($record['createtime']);
        $this->unlockTime = $this->_toTimestamp($record['unlocktime']);
        $this->groups     = !empty($record['groups']) ? explode(',', $record['groups']) : array();
        $this->roles      = !empty($record['roles']) ? explode(',', $record['roles']) : array();

        parent::__construct();
    }
}