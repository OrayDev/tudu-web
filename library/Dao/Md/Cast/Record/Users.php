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
 * @version    $Id: Users.php 2206 2012-10-11 07:06:15Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Cast_Record_Users extends Oray_Dao_Record
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
    public $domainName;

    /**
     *
     * @var string
     */
    public $address;

    /**
     *
     * @var string
     */
    public $mobile;

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
     * @var string
     */
    public $trueName;

    /**
     *
     * @var string
     */
    public $pinyin;

    /**
     *
     * @var string
     */
    public $tel;

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
        $this->domainName = $record['domainname'];
        $this->address    = $record['userid'] . '@' . $record['domainname'];
        $this->uniqueId   = $record['uniqueid'];
        $this->deptId     = $record['deptid'];
        $this->mobile     = $record['mobile'];
        $this->trueName   = $record['truename'];
        $this->pinyin     = $record['pinyin'];
        $this->tel        = $record['tel'];
        $this->lastUpdateTime = $record['lastupdatetime'];

        parent::__construct();
    }
}