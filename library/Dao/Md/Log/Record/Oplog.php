<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md_Log_Record_AdminLog
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Oplog.php 1031 2011-07-28 10:17:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md_Log_Record_AdminLog
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Log_Record_Oplog extends Oray_Dao_Record
{

	/**
	 * 组织ID
	 *
	 * @var string
	 */
	public $orgId;

	/**
	 * 用户ID
	 *
	 * @var string
	 */
	public $userId;

	/**
	 *
	 * @var Ip
	 */
	public $ip;

	/**
	 *
	 * @var string
	 */
	public $local;

	/**
	 * 操作模块对象
	 *
	 * @var string
	 */
	public $module;

	/**
	 * 操作类型
	 *
	 * @var string
	 */
	public $action;

	/**
	 *
	 * @var string
	 */
	public $subAction;

	/**
	 *
	 *
	 * @var string
	 */
	public $target;

	/**
	 * 创建时间
	 *
	 * @var int
	 */
	public $createTime;

	/**
	 * 描述
	 *
	 * @var string
	 */
	public $detail;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
    	$this->orgId      = $record['orgid'];
    	$this->userId     = $record['userid'];
    	$this->ip         = $record['ip'];
    	$this->local      = $record['local'];
    	$this->module     = $record['module'];
    	$this->action     = $record['action'];
    	$this->subAction  = $record['subaction'];
    	$this->target     = $record['target'];
    	$this->detail     = $record['detail'];
    	$this->createTime = $this->_toTimestamp($record['createtime']);

        parent::__construct($record);
    }
}