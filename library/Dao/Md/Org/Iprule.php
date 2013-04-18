<?php
/**
 * Tudu  -  IP地址过滤
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Iprule.php 1031 2011-07-28 10:17:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Iprule extends Oray_Dao_Abstract
{

    const TYPE_ENABLE  = 0;
    const TYPE_DISABLE = 1;

    /**
     * 获取组织设置IP限制列表
     *
     * @param array $condition
     * @param mixed $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getIprules(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_org_iprule';
        $columns = 'org_id AS orgid, type, rule, is_valid AS isvalid, exception';
        $where   = array();
        $order   = '';
        $limit   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type']) && is_int($condition['type'])) {
            $where[] = 'type = ' . $condition['type'];
        }

        if (is_array($filter) && array_key_exists('isvalid', $filter)) {
            if ($filter['isvalid'] !== null) {
                $where[] = 'is_valid = ' . $filter['isvalid'] ? 1 : 0;
            }
        } else {
            $where[] = 'is_valid = 1';
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        $where = implode(' AND ', $where);

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }

        return new Oray_Dao_Recordset($records, 'Dao_Md_Org_Record_Iprule');
    }

    /**
     * 读取IP过滤记录
     * @param $orgId
     */
    public function getIprule(array $condition)
    {
        $table   = 'md_org_iprule';
        $columns = 'org_id AS orgid, type, rule, is_valid AS isvalid, exception';
        $where   = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['type']) && is_int($condition['type'])) {
            $where[] = 'type = ' . $condition['type'];
        }
        
        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where}";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
        	return null;
        }

        return Oray_Dao::record('Dao_Md_Org_Record_Iprule', $record);
    }

    /**
	 * 判断记录是否存在
	 *
	 * @param $orgId
	 * @return boolean
	 */
    public function existsIprule($orgId)
    {
        $sql = 'SELECT COUNT(0) FROM md_org_iprule WHERE org_id = ' . $this->_db->quote($orgId);

		$count = (int) $this->_db->fetchOne($sql);

		return $count > 0;
    }


    /**
	 * 创建IP地址过滤
	 *
	 * @param array $params
	 * @return boolean
	 */
    public function createIprule(array $params)
    {
        if (empty($params['orgid'])) {
			return false;
		}

		$table = 'md_org_iprule';

		$bind   = array(
            'org_id'    => $params['orgid']
		);

        if (!empty($params['rule'])) {
			$bind['rule'] = $params['rule'];
		}

        if (!empty($params['exception'])) {
			$bind['exception'] = $params['exception'];
		}

        try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		return true;
    }

    /**
	 * 更新IP地址过滤
	 *
	 * @param array $params
	 * @return boolean
	 */
    public function updateIprule($orgId, array $params)
    {
        if (!$orgId) {
            return false;
        }

        $bind  = array();
		$table = 'md_org_iprule';
		$where = 'org_id = ' . $this->_db->quote($orgId);

        if (array_key_exists('rule', $params)) {
			$bind['rule'] = $params['rule'];
		}

        if (array_key_exists('exception', $params)) {
			$bind['exception'] = $params['exception'];
		}

		try {
			$this->_db->update($table, $bind, $where);
		} catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
			return false;
		}

		return true;
    }
}