<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage Md
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Department.php 1552 2012-02-03 08:25:35Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage Md
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Department_Department extends Oray_Dao_Abstract
{

	/**
	 * 获取部门信息
	 *
	 * @param array $condition
	 * @param array $filter
	 * @return Dao_Md_Department_Record_Department
	 */
	public function getDepartment(array $condition, $filter = null)
	{
		$table   = 'md_department';
		$columns = 'org_id AS orgid, dept_id AS deptid, dept_name AS deptname, parent_dept_id AS parentid, order_num AS ordernum, moderators';

		$where   = array();

		if (isset($condition['orgid'])) {
			$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
		}

		if (isset($condition['deptid'])) {
			$where[] = 'dept_id = ' . $this->_db->quote($condition['deptid']);
		}

		if (!$where) {
			return null;
		}

		$where = implode(' AND ', $where);

		$sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

		$record = $this->_db->fetchRow($sql);

		if (!$record) {
			return null;
		}

		return Oray_Dao::record('Dao_Md_Department_Record_Department', $record);
	}

	/**
	 * 获取部门列表
	 *
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getDepartments(array $condition, $filter = null, $sort = null, $maxCount = null)
	{
		$table   = 'md_department';
		$columns = 'org_id AS orgid, dept_id AS deptid, dept_name AS deptname, parent_dept_id AS parentid, order_num AS ordernum, moderators';
		$where   = array();
		$order   = array();
		$limit   = '';

		if (!empty($condition['orgid'])) {
			$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
		}

		if (!empty($condition['parentid'])) {
			$where[] = 'parent_dept_id = ' . $this->_db->quote($condition['parentid']);
		}

		if (!$where) {
			return new Oray_Dao_Recordset();
		}

		// WHERE
		if ($where) {
			$where = 'WHERE ' . implode(' AND ', $where);
		}

		// ORDER
		$order = 'ORDER BY order_num DESC';

		$sql = "SELECT {$columns} FROM {$table} {$where} {$order}";

		$_records = $this->_db->fetchAll($sql);

        if (empty($_records)) {
            return new Oray_Dao_Recordset();
        }

        $records = array();

        $records = self::sortRecords($_records);
        $records = self::formatRecords($records);

        return new Oray_Dao_Recordset($records, 'Dao_Md_Department_Record_Department');
	}

	/**
	 * 创建部门
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function createDepartment(array $params)
	{
		if (empty($params['orgid'])
			|| empty($params['deptid'])
			|| empty($params['deptname'])) {
			return false;
		}

		$table  = 'md_department';
		$bind   = array(
            'org_id'    => $params['orgid'],
            'dept_id'   => $params['deptid'],
            'dept_name' => $params['deptname']
		);
		$orderNum = null;

		if (!empty($params['parentid'])) {
			$bind['parent_dept_id'] = $params['parentid'];
		}

		if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$bind['order_num'] = $params['ordernum'];
		}

		try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		//$this->sortDepartment($params['orgid'], $params['deptid'], $orderNum);

		return $params['deptid'];
	}

	/**
	 * 更新部门信息
	 *
	 * @param string $orgId
	 * @param string $deptId
	 * @param array  $params
	 * @return boolean
	 */
	public function updateDepartment($orgId, $deptId, array $params)
	{
		if (!$orgId || !$deptId) {
			return false;
		}

		$table = 'md_department';
		$bind  = array();
		$where = 'org_id = ' . $this->_db->quote($orgId) . ' AND dept_id = ' . $this->_db->quote($deptId);

		if (!empty($params['deptname'])) {
			$bind['dept_name'] = $params['deptname'];
		}

		if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$bind['order_num'] = $params['ordernum'];
		}

		if (isset($params['parentid'])) {
			$bind['parent_dept_id'] = $params['parentid'] ? $params['parentid'] : null;
		}

        if (array_key_exists('moderators', $params)) {
            $bind['moderators'] = $params['moderators'];
        }

		try {
			$this->_db->update($table, $bind, $where);
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		/*if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$this->sortDepartment($orgId, $deptId, $params['ordernum']);
		}*/

		return true;
	}

	/**
	 * 删除部门
	 *
	 * @param string $orgId
	 * @param string $deptId
	 */
	public function deleteDepartment($orgId, $deptId)
	{
		if (!$orgId || !$deptId) {
			return false;
		}

		$sqls[] = 'UPDATE md_user SET dept_id = null WHERE org_id = ' . $this->_db->quote($orgId) . ' AND dept_id = ' . $this->_db->quote($deptId);
		$sqls[] = 'DELETE FROM md_department WHERE org_id = ' . $this->_db->quote($orgId) . ' AND dept_id = ' . $this->_db->quote($deptId);

		//$this->sortDepartment($orgId, $deptId);
		try {
		    foreach ($sqls as $sql) {
                $this->_db->query($sql);
		    }
		} catch (Zend_Db_Exception $e) {
			$this->_catchException($e, __METHOD__);
			return false;
		}

		return true;
	}

	/**
	 * 移除部门用户
	 *
	 * @param string $orgId
	 * @param string $deptId
	 * @param string $userId
	 * @return boolean
	 */
	public function removeUser($orgId, $deptId, $userId = null)
	{
		if (!$orgId || !$deptId) {
			return false;
		}

		$table = 'md_user';
		$where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'dept_id = ' . $this->_db->quote($deptId);

		if ($userId) {
			$userId = (array) $userId;
            $userId = array_map(array($this->_db, 'quote'), $userId);
            $where[] = 'user_id IN (' . implode(',', $userId) .')';
		}

		$where = implode(' AND ', $where);

		try {
			$this->_db->update($table, array('dept_id' => null), $where);
		} catch (Zend_Db_Exceptioin $e) {
			return false;
		}

		return true;
	}

    /**
     * 添加部门用户
     *
     * @param string $orgId
     * @param string $deptId
     * @param string $userId
     * @return boolean
     */
    public function addUser($orgId, $deptId, $userId)
    {
        if (!$orgId || !$deptId || !$userId) {
            return false;
        }

        $table = 'md_user';
        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);

        $userId = (array) $userId;
        $userId = array_map(array($this->_db, 'quote'), $userId);
        $where[] = 'user_id IN (' . implode(',', $userId) .')';

        $where = implode(' AND ', $where);

        try {
            $this->_db->update($table, array('dept_id' => $deptId), $where);
        } catch (Zend_Db_Exceptioin $e) {
            return false;
        }

        return true;
    }

	/**
	 * 获取用户数量
	 *
	 * @param string $orgId
	 * @param string $deptId
	 * @return int
	 */
	public function getUserCount($orgId, $deptId)
	{
		if (!$orgId || !$deptId) {
			return false;
		}

		$sql = 'SELECT COUNT(0) FROM md_user WHERE dept_id = ' . $this->_db->quote($deptId) . ' '
		     . 'AND org_id = ' . $this->_db->quote($orgId);

		$count = (int) $this->_db->fetchOne($sql);

		return $count;
	}

	/**
	 * 获取组织部门个数
	 *
	 * @param string $orgId
	 * @return string|number
	 */
	public function getDepartmentCount($orgId)
	{
	    if (!$orgId) {
			return false;
		}
		
		$sql = 'SELECT COUNT(0) FROM md_department WHERE org_id = ' . $this->_db->quote($orgId);

		$count = (int) $this->_db->fetchOne($sql);

		return $count;
	}

	/**
	 * 获取子级部门ID
	 *
	 * @param string $orgId
	 * @param string $deptId
	 * @return int
	 */
	public function getChildDeptid($orgId, $deptId)
	{
		if (!$orgId || !$deptId) {
            return false;
        }
        $departments = $this->getDepartments(array('orgid' => $orgId))->toArray();

	    $deptIds = array();
		$depth = false;
		foreach ($departments as $dept) {
			if (false !== $depth && $dept['depth'] <= $depth) {
				break;
			}

			if ($dept['deptid'] == $deptId) {
				$depth = $dept['depth'];
			}

			if (false !== $depth) {
				$deptIds[] = $dept['deptid'];
			}
		}

        return $deptIds;
	}

	/**
	 * 获取子级部门数量
	 *
	 * @param string $orgId
	 * @param string $deptId
	 * @return int
	 */
	public function getChildCount($orgId, $deptId)
	{
		if (!$orgId || !$deptId) {
            return false;
        }

        $sql = 'SELECT COUNT(0) FROM md_department WHERE parent_dept_id = ' . $this->_db->quote($deptId) . ' '
             . 'AND org_id = ' . $this->_db->quote($orgId);

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
	}

	/**
	 * 取出的部门记录重新排序
	 *
	 * @param array $records
	 */
	public static function sortRecords(array $records)
	{
		$ret = array();
		foreach ($records as $index => $record) {
            if (!$record['parentid']) {
                unset($records[$index]);

                $record['depth']     = 0;
                $record['firstnode'] = false;
                $record['lastnode']  = false;
                $record['path']      = array();
                $ret = array_merge($ret, self::_parseDepartments($record, $records));
            }
        }

        $ret[0]['firstnode'] = true;

        end($ret);
        do {
            $record = current($ret);

            if (empty($record['parentid'])) {
                $ret[key($ret)]['lastnode'] = true;
                break;
            }
        } while (prev($ret));
        reset($ret);

        return $ret;
	}

    /**
     * 格式化数据
     *
     * @param $records
     */
    public static function formatRecords(array $records)
    {
        $first = array();
        $line = array();

        foreach ($records as &$row){
            if (!isset($first[$row['parentid']])) {
                $row['firstnode'] = $first[$row['parentid']] = 1;
            } else {
                $row['firstnode'] = 0;
            }

            // 如果是最后节点就不显示垂直线
            $line[$row['depth']] = !$row['lastnode'];

            // 前缀表示字符串
            $row['prefix'] = '';

            // 递归深度
            for ($i = 0; $i <= $row['depth']; $i++) {

                // 当前深度
                if ($i == $row['depth']) {

                    // 是否最后节点
                    if ($row['lastnode']) {
                        $row['prefix'] = $row['prefix'].'└';
                    } else {
                        $row['prefix'] = $row['prefix'].'├';
                    }

                } else {

                    // 显示垂直线
                    if (isset($line[$i]) && $line[$i]) {
                        $row['prefix'] = $row['prefix'].'│';
                    } else {
                        $row['prefix'] = $row['prefix'].'&nbsp;';
                    }
                }
            }
        }

        return $records;
    }

    /**
     * 更新部门排序
     *
     * @param string $orgId
     * @param string $parentId
     * @param string $deptId
     * @return boolean
     */
    /*public function sortDepartment($orgId, $deptId, $orderNum = null)
    {
    	if (!$orgId && !$deptId) {
    		return false;
    	}

    	$department = $this->getDepartment(array('orgid' => $orgId, 'deptid' => $deptId));

    	if (null == $department) {
    		return false;
    	}

    	if (!$department->orderNum && $department->orderNum == $orderNum) {
    		return true;
    	}

    	$sql = 'SELECT org_id AS orgid, dept_id AS deptid FROM md_department WHERE org_id = ' . $this->_db->quote($orgId) . ' '
    	     . 'AND parent_dept_id ' . ($department->parentId == null ? ' IS NULL ' : (' = ' . $this->_db->quote($department->parentId))) . ' '
    	     . 'AND dept_id <> ' . $this->_db->quote($deptId) . ' '
    	     . 'ORDER BY order_num DESC';

    	$departments = $this->_db->fetchAll($sql);

    	if (!$orderNum || $orderNum <= 0) {
    		$orderNum = 1;
    	} else {
    		if ($department->orderNum > $orderNum) {
    			$orderNum++;
    		}
    	}

    	$depts = array();
    	$num   = count($departments) + 1;
        for ($index = 0, $count = count($departments); $index < $count; $index++) {
        	if ($departments[$index]['deptid'] == $deptId) {
        		continue;
        	}

        	if ($num == $orderNum) {
        		$num --;
        	}

        	$depts[$num] = $departments[$index]['deptid'];

        	$num --;
        }

        $depts[$orderNum] = $deptId;

        $sql = 'UPDATE md_department SET order_num = :ordernum WHERE dept_id = :deptid AND org_id = ' . $this->_db->quote($orgId);

    	try {
    		foreach ($depts as $num => $deptId) {
    			$this->_db->query($sql, array(
                    'ordernum'  => $num,
                    'deptid'    => $deptId
    			));
    		}
    	} catch (Zend_Db_Exception $e) {
    		return false;
    	}

    	return true;
    }*/

    /**
     * 另一种方式的排序
     *
     * @param string $orgId
     * @param string $deptId
     * @param string $sort
     * @return boolean
     */
    public function sortDepartment($orgId, $deptId, $sort)
    {
        $dept = $this->getDepartment(array(
            'deptid'=> $deptId,
            'orgid' => $orgId
        ));

        if (null === $dept) {
            return false;
        }

        $sql = 'SELECT order_num, dept_id FROM md_department WHERE org_id = ' . $this->_db->quote($orgId);

        if ($dept->parentId) {
            $sql .= ' AND parent_dept_id = ' . $this->_db->quote($dept->parentId);
        }

        if ($sort == 'down') {
            $sql .= ' AND order_num < ' . $dept->orderNum . ' ORDER BY order_num DESC';
        } else {
            $sql .= ' AND order_num > ' . $dept->orderNum . ' ORDER BY order_num ASC';
        }

        $sql .= ' LIMIT 1';
        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return false;
            }

            $this->updateDepartment($orgId, $deptId, array('ordernum' => (int) $record['order_num']));

            $this->updateDepartment($orgId, $record['dept_id'], array('ordernum' => (int) $dept->orderNum));

            return true;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }
    }

    /**
     * 获取最大排序索引
     *
     * @param $orgId
     * @param $parentId
     * @return int
     */
    public function getMaxOrderNum($orgId, $parentId = null)
    {
        $sql = 'SELECT MAX(order_num) FROM md_department WHERE org_id = ' . $this->_db->quote($orgId);

        if ($parentId) {
            $sql .= ' AND parent_dept_id = ' . $this->_db->quote($parentId);
        } else {
            $sql .= ' AND parent_dept_id IS NULL';
        }

        try {
            $count = (int) $this->_db->fetchOne($sql);
        } catch (Zend_Db_Execption $e) {
            $this->_catchExpection($e, __METHOD__);
            return false;
        }

        return $count;
    }

    /**
     * 添加部门负责人
     *
     * @param string $orgId
     * @param string $deptId
     * @param string $userId
     * @return boolean
     */
    public function addModerator($orgId, $deptId, $userId)
    {
        if (!$orgId || !$deptId || !$userId) {
            return false;
        }

        $table = 'md_department_moderator';
        $bind  = array(
            'org_id'  => $orgId,
            'dept_id' => $deptId,
            'user_id' => $userId
        );

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 移除部门负责人
     *
     * @param string $orgId
     * @param string $deptId
     * @param string $userId
     * @return boolean
     */
    public function removeModerator($orgId, $deptId, $userId)
    {
        if (!$orgId || !$deptId || !$userId) {
            return false;
        }

        $sql = 'DELETE FROM md_department_moderator WHERE org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'dept_id = ' . $this->_db->quote($deptId) . ' AND '
             . 'user_id = ' . $this->_db->quote($userId);

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param $orgId
     * @param $deptId
     * @return array
     */
    public function getDepartmentModerators($orgId, $deptId)
    {
        if (!$orgId || !$deptId) {
            return array();
        }

        $sql = "SELECT org_id AS orgid, dept_id AS deptid, user_id AS userid FROM md_deptartment_leader WHERE "
             . 'org_id = ' . $this->_db->quote($orgId) . ' AND '
             . 'dept_id = ' . $this->_db->quote($deptId);

        try {
            $records = $this->_db->fetchAll($sql);

            return $records;
        } catch (Zend_Db_Exeption $e) {
            $this->_catchExecption($e, __METHOD__);
            return false;
        }
    }

    /**
     * 部门是否存在
     *
     * @param string $orgId
     * @param string $deptId
     * @param string $deptName
     * @return boolean
     */
    public function existsDepartment($orgId, $deptId = null, $deptName = null)
    {
    	if (!$orgId || (!$deptId && !$deptName)) {
    		return false;
    	}

    	$sql = 'SELECT COUNT(0) FROM md_department WHERE org_id = ' . $this->_db->quote($orgId);

    	if ($deptId) {
    		$sql .=  ' AND dept_id = ' . $this->_db->quote($deptId);
    	}

        if ($deptName) {
            $sql .=  ' AND dept_name = ' . $this->_db->quote($deptName);
        }

    	$count = (int) $this->_db->fetchOne($sql);

    	return $count > 0;
    }

    /**
     * 整理读取出来的部门数据
     *
     * @param array $record
     * @param array $records
     * @return array
     */
    private static function _parseDepartments($record, array &$records)
    {
        $array = array($record);
        $children = array();
        foreach ($records as $index => $department) {
            if ($department['parentid'] == $record['deptid']) {

                $department['depth']     = $record['depth'] + 1;
                $department['firstnode'] = false;
                $department['lastnode']  = false;
                $department['path']      = array_merge($record['path'], array($record['deptid']));

                $children[] = $department;
                unset($records[$index]);
            }
        }

        if ($children) {
            $children[0]['firstnode'] = true;
            $children[count($children) - 1]['lastnode'] = true;

            foreach ($children as &$item) {
                $item = self::_parseDepartments($item, $records);
                $array = array_merge($array, $item);
            }
        }

        return $array;
    }

    /**
     * 获取部门ID
     *
     * @param string $orgId
     * @return string
     */
    public static function getDeptId($orgId = null)
    {
    	return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }

	/**
	 * 格式部门负责人信息
	 *
	 * 返回格式
	 * array(0 => id1, 1 => id2, 3 => id3)
	 *
	 * @param $str
	 * @return array
	 */
    public static function formatModerator($str)
    {
    	$ret = array();
    	if (empty($str)) return $ret;
    	$a = explode(",", $str);
        for($i=0; $i<count($a); $i++) {
            if ($a[$i]) {
                $ret[] = $a[$i];
            }
	    }
    	return $ret;
    }

	/**
     * 获取用户ID
     *
     * @param string $orgId
     * @param string $groupId
     * @return array
     */
    public function getUserIds($orgId, $deptId)
    {
    	$sql = 'SELECT user_id FROM md_user WHERE org_id = ' . $this->_db->quote($orgId) . ' '
    	     . 'AND dept_id = ' . $this->_db->quote($deptId);

    	$records = $this->_db->fetchAll($sql);

    	$ret = array();
    	foreach ($records as $record) {
    		$ret[] = $record['user_id'];
    	}

    	return $ret;
    }
}