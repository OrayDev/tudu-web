<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cast.php 2206 2012-10-11 07:06:15Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Cast_Cast extends Oray_Dao_Abstract
{
	/**
	 *
	 * @var string
	 */
	const ID_DEFAULT = '^default';

	/**
	 *
	 * @param array $condition
	 * @param array $filter
	 * @return Dao_Md_Cast_Record_Cast
	 */
	public function getCast(array $condition, $filter = null)
	{
		$table   = 'md_cast';
		$columns = 'org_id AS orgid, cast_id AS castid, cast_name AS castname, is_default as isdefault';
		$where   = array();

		if (!empty($condition['orgid'])) {
			$where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
		}

		if (!empty($condition['castid'])) {
			$where[] = 'cast_id = ' . $this->_db->quote($condition['castid']);
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

		return Oray_Dao::record('Dao_Md_Cast_Record_Cast', $record);
	}

	/**
	 *
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 * @return Oray_Dao_Recordset
	 */
	public function getCasts(array $condition, $filter = null, $sort = null, $maxCount = null)
	{
		$table   = 'md_cast';
        $columns = 'org_id AS orgid, cast_id AS castid, cast_name AS castname, is_default as isdefault';
        $where   = array();
        $limit   = '';
        $order   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['isdefault'])) {
            $where[] = 'is_default = ' . $condition['isdefault'] ? 1 : 0;
        }

        if (!$where) {
        	return new Oray_Dao_Recordset();
        }

        $where = ' WHERE ' . implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'isdefault':
                    $key = 'is_default';
                    break;
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
        	$order = 'ORDER BY ' . $order;
        }

        if (is_int($maxCount) || $maxCount > 0) {
        	$limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Md_Cast_Record_Cast');
	}

	/**
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function createCast(array $params)
	{
		if (empty($params['castname']) || empty($params['orgid'])
		    || empty($params['castid'])) {
			return false;
		}

		$table = 'md_cast';
		$bind  = array(
            'org_id' => $params['orgid'],
            'cast_id' => $params['castid'],
            'cast_name' => $params['castname'],
            'is_default' => @$params['isdefault'] ? 1 : 0
		);

		try {
			$this->_db->insert($table, $bind);
		} catch (Zend_Db_Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param array  $params
	 * @return boolean
	 */
	public function updateCast($orgId, $castId, $params)
	{
		if (!$orgId || !$castId) {
            return false;
        }

        $table = 'md_cast';
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND cast_id = ' . $this->_db->quote($castId);
        $bind  = array();

        if (!empty($params['castname'])) {
        	$bind['cast_name'] = $params['castname'];
        }

        if (isset($params['isdefault'])) {
        	$bind['is_default'] = $params['isdefault'] ? 1 : 0;
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
	}

	/**
	 * 删除构架
	 *
	 * @param string $orgId
	 * @param string $castId
	 */
	public function deleteCast($orgId, $castId)
	{
		if (!$orgId || !$castId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'cast_id = ' . $this->_db->quote($castId);

        $where = implode(' AND ' , $where);

        $sqls = array();

        $sqls[] = 'UPDATE md_user SET cast_id = ' . $this->_db->quote(self::ID_DEFAULT) . ' WHERE ' . $where;
        $sqls[] = 'DELETE FROM md_cast_detail WHERE ' . $where;
        $sqls[] = 'DELETE FROM md_cast_dept WHERE ' . $where;
        $sqls[] = 'DELETE FROM md_cast WHERE ' . $where;

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
	 * 添加用户
	 * @param string $orgId
	 * @param string $castId
	 * @param array  $userIds
	 * @return boolean
	 */
	public function addUser($orgId, $castId, array $userIds)
	{
		if (!$orgId || !$castId || !$userIds) {
            return false;
        }

        $table   = 'md_cast_detail';
        $columns = '(org_id, cast_id, user_id)';
        $values  = array();

        $orgId  = $this->_db->quote($orgId);
        $castId = $this->_db->quote($castId);
        foreach ($userIds as $userId) {
        	$values[] = "({$orgId}, {$castId}, " . $this->_db->quote($userId) . ")";
        }
        $values = implode(',', $values);

        $sql = "INSERT INTO {$table} {$columns} VALUES {$values}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
	}

	/**
	 * 添加部门关联
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param array $deptIds
	 * @return boolean
	 */
	public function addDepartment($orgId, $castId, array $deptIds)
	{
		if (!$orgId || !$castId || !$deptIds) {
			return false;
		}

		$table   = 'md_cast_dept';
		$columns = '(org_id, cast_id, dept_id)';
		$values  = array();

		$orgId  = $this->_db->quote($orgId);
		$castId = $this->_db->quote($castId);

		foreach ($deptIds as $deptId) {
			$values[] = "({$orgId}, {$castId}, " . $this->_db->quote($deptId) . ")";
		}
		$values = implode(',', $values);

		$sql = "INSERT INTO {$table} {$columns} VALUES {$values}";

		try {
			$this->_db->query($sql);
		} catch (Zend_Db_Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * 移除构架用户
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param string $userId
	 * @return boolean
	 */
	public function removeUser($orgId, $castId, $userId = null)
	{
        if (!$orgId || !$castId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'cast_id = ' . $this->_db->quote($castId);

        if ($userId) {
        	$where[] = 'user_id = ' . $this->_db->quote($userId);
        }

        $where = implode(' AND ', $where);

        $sql = "DELETE FROM md_cast_detail WHERE {$where}";

        try {
        	$this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
        	return false;
        }

        return true;
	}

	/**
	 * 移除构架部门
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param string $deptId
	 * @return boolean
	 */
	public function removeDepartment($orgId, $castId, $deptId = null)
	{
		if (!$orgId || !$castId) {
            return false;
        }

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'cast_id = ' . $this->_db->quote($castId);

        if ($deptId) {
            $where[] = 'dept_id = ' . $this->_db->quote($deptId);
        }

        $where = implode(' AND ', $where);

        $sql = "DELETE FROM md_cast_dept WHERE {$where}";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            return false;
        }

        return true;
	}

	/**
	 * 去除关联用户
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param string $userId
	 * @return boolean
	 */
	public function removeAssociateUser($orgId, $castId, $userId = null)
	{
		if (!$orgId || !$castId) {
            return false;
        }

        $table = 'md_user';
        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'cast_id = ' . $this->_db->quote($castId);

        if ($userId) {
            $userId = (array) $userId;
            $userId = array_map(array($this->_db, 'quote'), $userId);
            $where[] = 'user_id IN (' . implode(',', $userId) .')';
        }

        $where = implode(' AND ', $where);

        try {
            $this->_db->update($table, array('cast_id' => self::ID_DEFAULT), $where);
        } catch (Zend_Db_Exceptioin $e) {
            return false;
        }

        return true;
	}

	/**
	 * 添加关联用户
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param string $userId
	 * @return boolean
	 */
	public function addAssociateUser($orgId, $castId, $userId)
	{
		if (!$orgId || !$castId || !$userId) {
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
            $this->_db->update($table, array('cast_id' => $castId), $where);
        } catch (Zend_Db_Exceptioin $e) {
            return false;
        }

        return true;
	}

	/**
	 * 配置中某用户是否可见
	 *
	 * @param string $orgId
	 * @param string $castId
	 * @param string $userId
	 * @return boolean
	 */
	public function existsUser($orgId, $castId, $userId)
	{
		if (!$orgId || !$castId || !$userId) {
			return false;
		}

		$sql = 'SELECT COUNT(0) FROM v_cast_user WHERE '
		     . 'org_id = ' . $this->_db->quote($orgId) . ' AND '
		     . 'cast_id = ' . $this->_db->quote($castId) . ' AND '
		     . 'user_id = ' . $this->_db->quote($userId);

	    $count = (int) $this->_db->fetchOne($sql);

		return $count > 0;
	}

    /**
     * 获取构架可见的用户列表
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getCastUsers(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'v_cast_user AS V';
        $columns = 'V.org_id AS orgid, V.true_name AS truename, V.user_id AS userid, V.mobile, V.tel, V.last_update_time AS lastupdatetime, '
                 . 'V.dept_id AS deptid, V.domain_name AS domainname, V.pinyin, V.unique_id AS uniqueid';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (isset($condition['orgid'])) {
            $where[] = 'V.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['castid'])) {
            $where[] = 'V.cast_id = ' . $this->_db->quote($condition['castid']);
        }

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%'.$condition['keyword'].'%');
            $like[] = "V.true_name LIKE {$keyword}";

            if (!Oray_Function::hasCnChar($keyword)) {
                $like[] = "V.pinyin LIKE {$keyword}";
                $like[] = "V.user_id LIKE {$keyword}";
            }

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        if (!empty($condition['deptid'])) {
            if (is_array($condition['deptid'])) {
                $condition['deptid'] = array_map(array($this->_db, 'quote'), $condition['deptid']);
                $where[] = 'V.dept_id IN (' . implode(',', $condition['deptid']) . ')';
            } else {
                $where[] = 'V.dept_id = ' . $this->_db->quote($condition['deptid']);
            }
        }

        if (!empty($condition['pinyin'])) {
            $keyword = $this->_db->quote($condition['pinyin'].'%');
            $where[] = "(V.true_name LIKE {$keyword} OR V.pinyin LIKE {$keyword})";
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, 'Dao_Md_Cast_Record_Users');
    }

    /**
     * 获取配置用户分页
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getCastUserPage($condition, $sort, $page = null, $pageSize = null)
    {
    	if (empty($condition['orgid']) || empty($condition['castid'])) {
    		return new Oray_Dao_Recordset();
    	}

    	$table   = 'v_cast_user AS V '
    	         . 'LEFT JOIN md_user_info AS UI ON V.user_id = UI.user_id AND V.org_id = UI.org_id '
    	         . 'LEFT JOIN md_department AS D ON V.dept_id = D.dept_id ';
    	$columns = 'V.org_id AS orgid, V.unique_id AS uniqueid, V.true_name AS truename, V.user_id AS userid, V.dept_id AS deptid, '
    	         . 'V.domain_name AS domainname, UI.tel, UI.mobile, UI.position, D.dept_name AS deptname';
    	$where   = array();
    	$order   = array();
    	$primary = 'V.user_id';
    	$recordClass = 'Dao_Md_Cast_Record_UserPage';


        $where[] = 'V.org_id = ' . $this->_db->quote($condition['orgid']);
        $where[] = 'V.cast_id = ' . $this->_db->quote($condition['castid']);

        if (!empty($condition['userid'])) {
            $where[] = 'V.user_id = ' . $this->_db->quote($condition['userid']);
        }

        if (!empty($condition['domain'])) {
            $where[] = 'V.domain_name = ' . $this->_db->quote($condition['domain']);
        }

        if (!empty($condition['domainid'])) {
            $where[] = 'U.domain_id = ' . $this->_db->quote($condition['domainid']);
        }

        if (!empty($condition['deptid'])) {
            if (is_array($condition['deptid'])) {
                $condition['deptid'] = array_map(array($this->_db, 'quote'), $condition['deptid']);
                $where[] = 'V.dept_id IN (' . implode(',', $condition['deptid']) . ')';
            } else {
                $where[] = 'V.dept_id = ' . $this->_db->quote($condition['deptid']);
            }
        }

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote('%'.$condition['keyword'].'%');
            $where[] = "(V.true_name LIKE {$keyword} OR V.pinyin LIKE {$keyword} OR V.user_id LIKE {$keyword})";
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                default:
                    continue 2;
                    break;
            }

            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        /**
         * @see Oray_Db_Paginator
         */
        require_once 'Oray/Db/Paginator.php';

        $paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER   => $this->_db,
            Oray_Db_Paginator::TABLE     => $table,
            Oray_Db_Paginator::COLUMNS   => $columns,
            Oray_Db_Paginator::ORDER     => $order,
            Oray_Db_Paginator::PRIMARY   => $primary,
            Oray_Db_Paginator::WHERE     => $where,
            Oray_Db_Paginator::PAGE_SIZE => $pageSize,

            Oray_Db_Paginator::RECORD_CLASS => $recordClass
        ));

        return $paginator->query($page);
    }

    /**
     * 获取构架配置可见的部门
     *
     * @param string $orgId
     * @param string $castId
     * @return boolean
     */
    public function getCastDepartments($orgId, $castId)
    {
        if (!$orgId || !$castId) {
            return new Oray_Dao_Recordset();
        }

        $table   = 'v_cast_department';
        $columns = 'org_id AS orgid, dept_id AS deptid, dept_name AS deptname, parent_dept_id AS parentid, order_num as ordernum, moderators';

        $where = array();

        $where[] = 'org_id = ' . $this->_db->quote($orgId);
        $where[] = 'cast_id = ' . $this->_db->quote($castId);

        $where = implode(' AND ', $where);

        // ORDER
        $order = 'ORDER BY org_id, parent_dept_id, order_num DESC';

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order}";

        $_records = $this->_db->fetchAll($sql);

        if (!$_records) {
            return new Oray_Dao_Recordset();
        }

        if (!class_exists('Dao_Md_Department_Department')) {
            require_once 'Dao/Md/Department/Department.php';
        }

        $records = array();

        $records = Dao_Md_Department_Department::sortRecords($_records);
        $records = Dao_Md_Department_Department::formatRecords($records);

        return new Oray_Dao_Recordset($records, 'Dao_Md_Department_Record_Department');
    }

	/**
	 *
	 * @return string
	 */
	public static function getCastId($orgId, $castName)
	{
		return base_convert(strrev(time()) . rand(0, 999), 10, 32);
	}
}