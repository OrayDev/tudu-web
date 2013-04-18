<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Access
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Access.php 2121 2012-09-19 05:15:22Z web_op $
 */

/**
 * @category   Tudu
 * @package    Tudu_Access
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Access
{
    const PERM_MOBILE_OFFICING  = 101; //允许移动办公
    const PERM_CUSTOM_SKIN      = 102; //允许自定义皮肤

    const PERM_CREATE_BOARD     = 201; //允许新增版块
    const PERM_UPDATE_BOARD     = 202; //允许编辑板块
    const PERM_DELETE_BOARD     = 203; //允许删除板块
    const PERM_CLOSE_BOARD      = 204; //允许关闭板块
    const PERM_CLEAN_BOARD      = 205; //允许清空板块

    const PERM_CREATE_TUDU      = 301; //允许发新图度
    const PERM_CREATE_POST      = 302; //允许发表回复
    const PERM_UPDATE_TUDU      = 303; //允许编辑图度
    const PERM_UPDATE_POST      = 304; //允许编辑回复
    const PERM_DELETE_TUDU      = 305; //允许删除图度
    const PERM_DELETE_POST      = 306; //允许删除回复
    const PERM_FORWARD_TUDU     = 307; //允许转发图度
    const PERM_MERGE_TUDU_GROUP = 308; //允许添加到图度组

    const PERM_CREATE_DISCUSS   = 501; //允许发起讨论
    const PERM_CREATE_NOTICE    = 502; //允许发起公告
    const PERM_CREATE_MEETING   = 504; //允许发起会议
    const PERM_CREATE_VOTE      = 503; //允许发起投票

    const PERM_VIEW_ATTACH      = 401; //允许下载/查看附件
    const PERM_UPLOAD_ATTACH    = 402; //允许发布附件
    const PERM_MAX_ATTACH_SIZE  = 403; //最大附件尺寸
    const PERM_DAILY_ATTACH_NUM = 404; //每天最大附件数量
 
    const PERM_CREATE_FLOW      = 511; //允许创建工作流
    const PERM_UPDATE_FLOW      = 512; //允许修改工作流
    const PERM_DELETE_FLOW      = 513; //允许删除工作流

	/**
	 * 
	 * @var string
	 */
	const ACCESS_TYPE_BOOLEAN = 'B';
	const ACCESS_TYPE_INTEGER = 'I';

	/**
	 * 访问列表
	 * 
	 * @var array
	 */
	private $_accessList;

	/**
	 * Construct
	 * 
	 * @param array $access
	 */
	public function __construct(array $access)
	{
	    $access = $this->parseAccess($access);
	    $this->setAccess($access);
	}

	/**
	 * 
	 * @param array $access
	 */
	public function setAccess(array $access)
	{
		$this->_accessList = $access;
		return $this;
	}

	/**
	 * 
	 * @return array
	 */
	public function getAccess()
	{
		return $this->_accessList;
	}

	/**
	 * 
	 * @param array $access
	 */
	public function parseAccess(array $access)
	{
		$ret = array();
		foreach ($access as $item) {
			if (array_key_exists($item['accessid'], $ret)) {
			    
				// 直接设置到用户的权限优先，值大的设置优先
				if (!$ret[$item['accessid']]['roleid'] 
				    || $ret[$item['accessid']]['value'] > $item['value']) 
				{
					continue;
				}
			}
			$ret[$item['accessid']] = $item;
		}
		return $ret;
	}

	/**
	 * 获取某个访问设置的值
	 * 
	 * @param ing $assertId
	 * @return mixed
	 */
	public function getAccessValue($accessId)
	{
		if (!$this->exists($accessId)) {
			return null;
		}
		
		return $this->_accessList[$accessId]['value'];
	}

	/**
	 * 是否存在指定权限设置
	 * 
	 * @param string $accessId
	 * @return boolean
	 */
	public function exists($accessId)
	{
		return array_key_exists($accessId, $this->_accessList);
	}

    /**
     * 访问权限设置是否与给定值相等
     * 
     * @param int     $accessId
     * @param mixed   $value
     * @param boolean $strict   是否严格匹配（类型）
     * @return boolean
     */
    public function assertEquals($accessId, $value, $strict = false)
    {
        if (!$this->exists($accessId)) {
        	return false;
        }

        return $strict 
               ? ($this->_accessList[$accessId]['value'] === $value) 
               : ($this->_accessList[$accessId]['value'] == $value); 
    }

    /**
     * 访问权限设置值是否大于给定的值
     * 
     * @param int   $assertId
     * @param mixed $value
     * @return boolean
     */
    public function assertGreater($accessId, $value)
    {
        if (!$this->exists($accessId)) {
        	return false;
        }

        return $this->_accessList[$accessId]['value'] > $value;
    }

    /**
     * 
     * @param int   $accessId
     * @param mixed $privilege
     * @return boolean
     */
    public function isAllowed($accessId, $privilege = null)
    {
    	if (!$this->exists($accessId)) {
    		return false;
    	}

    	$access = $this->_accessList[$accessId];
    	
    	if ($access['valuetype'] == self::ACCESS_TYPE_BOOLEAN) {
    		return $access['value'];
    	}

    	if (null === $privilege) {
    		return false;
    	}

    	return $access['value'] >= $privilege;
    }
}