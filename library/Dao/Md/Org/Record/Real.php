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
 * @version    $Id: Real.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Record_Real extends Oray_Dao_Record
{
    /**
     * 实名认证记录ID
     *
     * @var string
     */
    public $realNameId;

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 企业组织简称
     *
     * @var string
     */
    public $orgName;
    
    /**
     * 企业组织全称
     *
     * @var string
     */
    public $entireName;

    /**
     * 企业组织绑定域名
     *
     * @var string
     */
    public $domainName;

    /**
     * 实名认证文件路径
     *
     * @var string
     */
    public $fileUrl;

    /**
     * 企业实名认证状态
     *
     * @var int
     */
    public $status;

    /**
     * 备注
     *
     * @var string
     */
    public $memo;

    /**
     * 创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     * 更新时间
     *
     * @var int
     */
    public $updateTime;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->realNameId = $record['realnameid'];
        $this->orgId      = $record['orgid'];
        $this->orgName    = isset($record['orgname']) ? $record['orgname'] : null;
        $this->entireName = isset($record['entirename']) ? $record['entirename'] : null;
        $this->domainName = isset($record['domainname']) ? $record['domainname'] : null;
        $this->fileUrl    = isset($record['fileurl']) ? $record['fileurl'] : null;
        $this->status     = $this->_toInt($record['status']);
        $this->memo       = isset($record['memo']) ? $record['memo'] : null;
        $this->createTime = $this->_toTimestamp($record['createtime']);
        $this->updateTime = $this->_toTimestamp($record['updatetime']);

        parent::__construct();
    }
}