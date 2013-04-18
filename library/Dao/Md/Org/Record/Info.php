<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Info.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Record_Info extends Oray_Dao_Record
{
    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 组织全称
     *
     * @var string
     */
    public $entireName;

    /**
     * 所属行业
     *
     * @var int|string
     */
    public $industry;

    /**
     * 企业联系人
     *
     * @var unknown_type
     */
    public $contact;

    /**
     * 联系电话
     *
     * @var string
     */
    public $tel;

    /**
     * 传真
     *
     * @var string
     */
    public $fax;

    /**
     * 省份
     *
     * @var string
     */
    public $province;

    /**
     * 城市
     *
     * @var city
     */
    public $city;

    /**
     * 地址
     *
     * @var string
     */
    public $address;

    /**
     * 邮编
     *
     * @var int
     */
    public $postcode;

    /**
     * 企业简介
     *
     * @var string
     */
    public $description;

    /**
     * 实名状态
     *
     * @var int
     */
    public $realNameStatus;

	/**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId       = $record['orgid'];
        $this->entireName  = $record['entirename'];
        $this->industry    = $record['industry'];
        $this->contact     = $record['contact'];
        $this->tel         = $record['tel'];
        $this->fax         = isset($record['fax']) ? $record['fax'] : null;
        $this->province    = $record['province'];
        $this->city        = $record['city'];
        $this->address     = $record['address'];
        $this->postcode    = $record['postcode'];
        $this->description = $record['description'];
        $this->realNameStatus = $this->_toInt($record['realnamestatus']);

        parent::__construct();
    }
}