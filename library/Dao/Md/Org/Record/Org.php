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
 * @version    $Id: Org.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Record_Org extends Oray_Dao_Record
{
    /**
     * 组织ID
     *
     * @var string
     */
    private $_orgId;

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     *
     * @var int
     */
    public $cosId;

    /**
     * 组织简称
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
     * 绑定主机
     *
     * @var string
     */
    public $hosts;

    /**
     * 创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     * 过期时间
     *
     * @var int
     */
    public $expireDate;

    /**
     * 状态
     *
     * @var int
     */
    public $status;
    
    /**
     * 
     * @var boolean
     */
    public $isActive;

    /**
     * TS ID
     *
     * @var int
     */
    public $tsid;

    /**
     * 组织介绍
     *
     * @var string
     */
    public $intro;

    /**
     * 密码强度
     *
     * @var int
     */
    public $passwordLevel;

    /**
     * 重试锁定次数
     *
     * @var int
     */
    public $lockTime;

    /**
     * 企业时区设置
     *
     * @var string
     */
    public $timezone;

    /**
     * 日期格式
     *
     * @var string
     */
    public $dateFormat;

    /**
     * 皮肤
     *
     * @var string
     */
    public $skin;

    /**
     * 登陆页面模板编号
     *
     * @var string
     */
    public $loginSkin;

    /**
     *
     * @var string
     */
    public $defaultPassword;

    /**
     *
     * @var boolean
     */
    public $isHttps;

    /**
     *
     * @var array
     */
    public $timeLimit;

    /**
     *
     * @var int
     */
    public $isIpRule;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->orgName    = $record['orgname'];
        $this->entireName = isset($record['entirename']) ? $record['entirename'] : null;
        $this->createTime = $this->_toTimestamp($record['createtime']);
        $this->expireDate = $this->_toTimestamp($record['expiredate']);
        $this->tsid       = $record['tsid'];
        $this->status     = $this->_toInt($record['status']);
        $this->intro      = $record['intro'];
        $this->timezone   = $record['timezone'];
        $this->dateFormat = $record['dateformat'];
        $this->lockTime   = $this->_toInt($record['locktime']);
        $this->skin       = $record['skin'];
        $this->isActive   = isset($record['isactive']) ? $this->_toBoolean($record['isactive']) : null;
        $this->loginSkin  = isset($record['loginskin']) ? Dao_Md_Org_Org::formatLoginSkin($record['loginskin']) : null;

        $this->isHttps    = $this->_toBoolean($record['ishttps']);
        $this->timeLimit  = $this->_formatTimeLimit($record['timelimit']);

        $this->passwordLevel   = $this->_toInt($record['passwordlevel']);
        $this->defaultPassword = $record['defaultpassword'];

        $this->_orgId = $this->orgId;
        $this->isIpRule   = $this->_toInt($record['isiprule']);

        parent::__construct();
    }

    /**
     * 格式化时段限制数据
     *
     * @param $limit
     */
    private function _formatTimeLimit($limit)
    {
        if (empty($limit)) {
            return null;
        }

        $array = explode("\n", $limit);
        if (count($array) < 7) {
            return null;
        }

        foreach ($array as &$str) {
            $str = (int) base_convert($str, 16, 10);
        }

        return $array;
    }
}