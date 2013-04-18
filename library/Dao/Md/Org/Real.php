<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Real.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Real extends Oray_Dao_Abstract
{
    /**
     * 实名认证状态：申请
     *
     * @var int
     */
    const REAL_STATUS_APPLY = 0;

    /**
     * 实名认证状态：通过
     *
     * @var int
     */
    const REAL_STATUS_PASS = 1;

    /**
     * 实名认证状态：失败
     *
     * @var int
     */
    const REAL_STATUS_FAIL = 2;

    /**
     * 实名认证状态（支持的结果）
     *
     * @var array
     */
    static $supportStatus = array(
        self::REAL_STATUS_APPLY,
        self::REAL_STATUS_PASS,
        self::REAL_STATUS_FAIL
    );

    /**
     * SELECT realname_id AS realnameid, org_id AS orgid, file_url AS fileurl, status, create_time AS createtime
     * FROM md_org_realname
     * WHERE realname_id = :realnameid
     * [AND org_id = :orgid]
     * LIMIT 0, 1
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Org_Record_Real
     */
    public function getRealName(array $condition, $filter = null)
    {
        $table   = 'md_org_realname';
        $columns = 'realname_id AS realnameid, org_id AS orgid, file_url AS fileurl, status, memo, create_time AS createtime, update_time AS updatetime';
        $recordClass = 'Dao_Md_Org_Record_Real';
        $where = array();

        if (!empty($condition['realnameid'])) {
            $where[] = 'realname_id = ' . $this->_db->quote($condition['realnameid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!$where) {
            return null;
        }

        if ($filter && !empty($filter['status'])) {
            $where[] = 'status = ' . (int) $filter['status'];
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} ORDER BY create_time DESC LIMIT 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record($recordClass, $record);
    }

    /**
     * SELECT realname_id AS realnameid, org_id AS orgid, file_url AS fileurl, status, create_time AS createtime
     * FROM md_org_realname
     * WHERE org_id = :orgid
     * [AND status = :status]
     * ORDER BY xxx
     * LIMIT xxx
     *
     * @param array $condition
     * @param array $filter
     * @param array $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getRealNames(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_org_realname';
        $columns = 'realname_id AS realnameid, org_id AS orgid, file_url AS fileurl, status, create_time AS createtime';
        $recordClass = 'Dao_Md_Org_Record_Real';
        $where = array();
        $order = array();
        $limit   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 'status = ' . $this->_db->quote($condition['status']);
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     *
     * @param array $condition
     * @param mixd  $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getRealNamePage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'md_org_realname r '
                 . 'LEFT JOIN md_organization o ON r.org_id = o.org_id '
                 . 'LEFT JOIN md_org_info oi ON r.org_id = oi.org_id ';

        $columns = 'r.realname_id AS realnameid, r.org_id AS orgid, r.status, r.create_time AS createtime, '
                 . 'r.update_time AS updatetime, o.org_name AS orgname, oi.entire_name AS entirename';

        $recordClass = 'Dao_Md_Org_Record_Real';
        $primary = 'r.realname_id';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['orgid'])) {
            $where[] = 'r.org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['orgname'])) {
            $where[] = 'o.org_name LIKE ' . $this->_db->quote('%'.$condition['orgname'].'%');
        }

        if (!empty($condition['entirename'])) {
            $where[] = 'oi.entire_name LIKE ' . $this->_db->quote('%'.$condition['entirename'].'%');
        }

        if (!empty($condition['domainname'])) {
            $where[] = 'd.domain_name = ' . $this->_db->quote($condition['domainname']);
        }

        if (isset($condition['status']) && is_int($condition['status'])) {
            $where[] = 'r.status = ' . $this->_db->quote($condition['status']);
        }

        if (isset($condition['createtime']) && is_array($condition['createtime'])) {
            $c = array();
            if (isset($condition['createtime']['start'])) {
                $c[] = 'r.create_time >= ' . $condition['createtime']['start'];
            }

            if (isset($condition['createtime']['end'])) {
                $c[] = 'r.create_time <= ' . $condition['createtime']['end'];
            }

            if ($c) {
                $where[] = '(' . implode(' AND ', $c) . ')';
            }
        }

        if (isset($condition['updatetime']) && is_array($condition['updatetime'])) {
            $u = array();
            if (isset($condition['updatetime']['start'])) {
                $u[] = 'r.update_time >= ' . $condition['updatetime']['start'];
            }

            if (isset($condition['updatetime']['end'])) {
                $u[] = 'r.update_time <= ' . $condition['updatetime']['end'];
            }

            if ($u) {
                $where[] = '(' . implode(' AND ', $u) . ')';
            }
        }

        if (!empty($where)) {
            $where = implode(' AND ', $where);
        } else {
            $where = '';
        }

        // 排序
        $sort  = $this->_formatSort($sort);
        foreach ($sort AS $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'r.create_time';
                    break;
                case 'updatetime':
                    $key = 'r.update_time';
                    break;
                case 'status':
                    $key = 'r.status';
                    break;
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

        // 初始化分页器
        $paginator = new Oray_Db_Paginator(array(
            Oray_Db_Paginator::ADAPTER      => $this->_db,
            Oray_Db_Paginator::RECORD_CLASS => $recordClass,
            Oray_Db_Paginator::PAGE_SIZE    => $pageSize,
            Oray_Db_Paginator::TABLE        => $table,
            Oray_Db_Paginator::PRIMARY      => $primary,
            Oray_Db_Paginator::COLUMNS      => $columns,
            Oray_Db_Paginator::WHERE        => $where,
            Oray_Db_Paginator::ORDER        => $order
        ));

        // 返回查询结果
        return $paginator->query($page);
    }

    /**
     * 统计总数
     */
    public function countRealName(array $condition)
    {
        $table = 'md_org_realname';
        $where = array();

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT COUNT(0) FROM {$table} {$where}";

        $count = (int) $this->_db->fetchOne($sql);

        return $count;
    }

    /**
     * 创建认证信息
     *
     * @param array $params
     * @return string|boolean
     */
    public function create(array $params)
    {
        if (empty($params['realnameid']) || empty($params['orgid'])) {
            return false;
        }

        $table = 'md_org_realname';
        $bind = array(
            'realname_id' => $params['realnameid'],
            'org_id'      => $params['orgid']
        );

        if (!empty($params['fileurl'])) {
            $bind['file_url'] = $params['fileurl'];
        }

        if (!empty($params['status']) && in_array($params['status'], self::$supportStatus)) {
            $bind['status'] = $params['status'];
        }

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['realnameid'];
    }

    /**
     * 更新认证信息
     *
     * @param string $realNameId
     * @param array  $params
     * @return boolean
     */
    public function update($realNameId, array $params)
    {
        if (!$realNameId) {
            return false;
        }

        $table = 'md_org_realname';
        $where = 'realname_id = ' . $this->_db->quote($realNameId);
        $bind = array();

        if (isset($params['fileurl'])) {
            $bind['file_url'] = $params['fileurl'];
        }

        if (isset($params['status']) && in_array($params['status'], self::$supportStatus)) {
            $bind['status'] = $params['status'];
        }

        if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['updatetime'])) {
            $bind['update_time'] = $params['updatetime'];
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
     * 删除实名记录
     *
     * @param string $realNameId
     */
    public function delete($realNameId)
    {
        $sql = "DELETE FROM md_org_realname WHERE realname_id = :realnameid";

        try {
            $this->_db->query($sql, array('realnameid' => $realNameId));
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return false;
    }

    /**
     * 获取实名认证记录ID
     *
     * @param  string $orgId
     * @return string
     */
    public static function getRealNameId($orgId)
    {
        return substr(md5($orgId . '-' . time()), 6, 16);
    }
}