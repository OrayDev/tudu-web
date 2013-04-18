<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Login.php 1031 2011-07-28 10:17:56Z cutecube $
 */
class Dao_Td_Log_Record_Login extends Oray_Dao_Record
{
    /**
     * 日志Id
     * @var string
     */
    public $LoginLogId;
    
    /**
     * 企业Id
     * @var string
     */
    public $orgId;
    
    /**
     * 用户唯一Id
     * @var string
     */
    public $uniqueId;
    
    /**
     * 用户Email地址
     * @var string
     */
    public $address;
    
    /**
     * 用户真实姓名
     * @var string
     */
    public $trueName;
    
    /**
     * IP地址
     * @var string
     */
    public $ip;
    
    /**
     * IP所在地
     * @var string
     */
    public $local;
    
    /**
     * 运营商
     * @var string
     */
    public $isp;
    
    /**
     * 登录途径
     * @var string
     */
    public $from;
    
    /**
     * 客户端信息
     * @var string
     */
    public $clientInfo;
    
    /**
     * 日志创建时间
     * @var int
     */
    public $createTime;
    
	/**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->LoginLogId = $record['loginlogid'];
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->address    = $record['address'];
        $this->trueName   = $record['truename'];
        $this->ip         = $record['ip'];
        $this->local      = !empty($record['local']) ? $record['local'] : null;
        $this->isp        = !empty($record['isp']) ? $record['isp'] : null;
        $this->from       = !empty($record['from']) ? $record['from'] : null;
        $this->clientInfo = $record['clientinfo'];
        $this->createTime = $this->_toTimestamp($record['createtime']);
        
        parent::__construct();
    }
}