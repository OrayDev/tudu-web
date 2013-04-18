<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Invite extends Oray_Dao_Abstract
{
	
	/**
	 * 获取邀请码数量
	 * 
	 * @param string $orgId
	 */
	public function getCount($orgId)
	{
		$sql = "SELECT invite_count FROM md_organization WHERE org_id = " . $this->_db->quote($orgId);
		
		$count = (int) $this->_db->fetchOne($sql);
		
		return $count;
	}
	
	/**
	 * 更新企业邀请码数量
	 * 
	 * @param string $orgId
	 * @param int $count
	 */
	public function updateCount($orgId, $count)
	{
		if (!$orgId || !is_int($count)) {
			return false;
		}
		
		$table = 'md_organization';
		$bind  = array(
            'invite_count' => $count
		);
		$where = 'org_id = ' . $this->_db->quote($orgId);
		
		try {
			$this->_db->update($table, $bind, $where);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}
		
		return true;
	}
	
	/**
	 * 插入邀请码记录
	 */
	public function createCode($params)
	{
		if (empty($params['code'])) {
			return false;
		}
		
		$table = 'md_invite';
		$bind  = array();
		
		$bind['code'] = $params['code'];
		
		if (isset($params['status']) && is_int($params['status'])) {
			$bind['status'] = $params['status'];
		}
		
		if (!empty($params['orgid'])) {
			$bind['org_id'] = $params['orgid'];
		}
		
		if (isset($params['type'])) {
			$bind['type'] = (int) $params['type'];
		}
		
		$bind['create_time'] = time();
		
		try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * @param string $params
	 */
	public function updateCode($code, $params)
	{
		if (empty($code)) {
            return false;
        }
        
        $table = 'md_invite';
        $bind  = array();
        $where = 'code = ' . $this->_db->quote($code);
        
        if (isset($params['status']) && is_int($params['status'])) {
            $bind['status'] = $params['status'];
        }
        
        if (!empty($params['toorgid'])) {
            $bind['to_org_id'] = $params['toorgid'];
        }
        
        if (!empty($params['usetime'])) {
            $bind['use_time'] = $params['usetime'];
        }
        
        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
	}
	
	/**
	 * 获取邀请信息
	 * 
	 * @param int $code
	 */
	public function getInvite($code)
	{
		$columns = 'code, org_id AS orgid, status, to_org_id AS toorgid, create_time AS createtime';
		$table   = 'md_invite';
		
		$sql = "SELECT {$columns} FROM {$table} WHERE code = " . $this->_db->quote($code);
		
		return $this->_db->fetchRow($sql);
	}
	
	/**
	 * 生成邀请码
	 */
	public static function getCode()
	{
		$str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		$i = 0;
		$ret = '';
		do {
			$k = rand(0, 35);
			$ret .= $str[$k];
			
			$i++;
		} while ($i <= 15);
		
		return $ret;
	}
}