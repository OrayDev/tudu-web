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
 * @version    $Id: Users.php 2388 2012-11-16 09:19:41Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Users extends Oray_Dao_Record
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
    public $address;

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
     * 部门名称
     *
     * @var string
     */
    public $deptName;

    /**
     * 职位
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
     * 手机
     *
     * @var string
     */
    public $mobile;

    /**
     * 固定电话
     *
     * @var string
     */
    public $tel;

    /**
     * pinyin
     *
     * @var string
     */
    public $pinyin;

    /**
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var boolean
     */
    public $isAvatars;

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
    public $updateTime;

    /**
     *
     * @var int
     */
    public $lastUpdateTime;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->userId     = $record['userid'];
        $this->address    = $record['userid'] . '@' . $record['orgid'];
        $this->userName   = $record['userid'] . '@' . $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->deptId     = $record['deptid'];
        $this->deptName   = isset($record['deptname']) ? $record['deptname']: null;
        $this->position   = isset($record['position']) ? $record['position']: null;
        $this->gender     = isset($record['gender']) ? $this->_toInt($record['gender']) : null;
        $this->status     = isset($record['status']) ? $this->_toInt($record['status']): null;
        $this->mobile     = isset($record['mobile']) ? $record['mobile']: null;
        $this->tel        = isset($record['tel']) ? $record['tel']: null;
        $this->pinyin     = isset($record['pinyin']) ? $record['pinyin']: null;
        $this->trueName   = trim($record['truename']);
        $this->isAvatars  = isset($record['isavatars']) ? (boolean) !$record['isavatars'] : null;
        $this->createTime = isset($record['createtime']) ? $this->_toTimestamp($record['createtime']) : null;
        $this->updateTime = isset($record['updatetime']) ? $this->_toTimestamp($record['updatetime']) : null;
        $this->lastUpdateTime = isset($record['lastupdatetime']) ? $this->_toTimestamp($record['lastupdatetime']) : null;

        parent::__construct();
    }
}