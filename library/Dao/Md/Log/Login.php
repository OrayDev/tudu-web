<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Login.php 1045 2011-08-04 08:58:35Z cutecube $
 */
class Dao_Md_Log_Login extends Oray_Dao_Abstract
{

    /**
     * 获取一个月30天的日志
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $page
     * @param int   $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getMonthLoginLogsPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'md_login_log';
        $columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
                 . 'truename, ip, local, isp, `clientkey`, client_info AS clientinfo, create_time AS createtime';
        $primary  = 'login_log_id';
        $recordClass = "Dao_Md_Log_Record_Login";
        $where   = array();
        $order   = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
        	$where[] = 'create_time >= ' . $this->_db->quote($condition['starttime']) . ' AND create_time <= ' . $this->_db->quote($condition['endtime']);
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
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
     * 获取登陆日志
     *
     * @param $condition
     * @param $filter
     */
    public function getLoginLog(array $condition, $filter = null)
    {
        $table   = 'md_login_log';
        $columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
                 . 'truename, ip, local, isp, `clientkey`, client_info AS clientinfo, create_time AS createtime';
        $where   = array();
        $order   = '';

        if (!empty($condition['loginlogid'])) {
            $where[] = 'login_log_id = ' . $this->_db->quote($condition['loginlogid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['clientkey'])) {
            $where[] = 'clientkey = ' . $this->_db->quote($condition['clientkey']);
        }

        if (empty($where)) {
            return null;
        }

        if (!empty($filter['prev'])) {
            $prevId = $filter['prev'];

            $where[] = 'create_time < (SELECT create_time FROM md_login_log WHERE login_log_id = ' . $this->_db->quote($prevId) . ')';
            $order   = 'create_time DESC';
        }

        $where = implode(' AND ', $where);

        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} {$order} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Md_Log_Record_Login', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 获取多条日志
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getLoginLogs($condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'md_login_log';
        $columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
                 . 'truename, ip, local, isp, `clientkey`, client_info AS clientinfo, create_time AS createtime';
        $where   = array();
        $order   = array();
        $limit   = '';

        if (!empty($condition['loginlogid'])) {
            $where[] = 'login_log_id = ' . $this->_db->quote($condition['loginlogid']);
        }

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['address'])) {
            $where[] = 'address = ' . $this->_db->quote($condition['address']);
        }

        if (!empty($condition['truename'])) {
            $where[] = 'truename = ' . $this->_db->quote($condition['truename']);
        }

        if (!empty($condition['createtime'])) {
            if (is_array($condition['createtime'])) {
                if (!empty($condition['createtime'][0]) && is_int($condition['createtime'][0])) {
                    $where[] = 'create_time >= ' . $condition['createtime'][0];
                }

                if (!empty($condition['createtime'][1]) && is_int($condition['createtime'][1])) {
                    $where[] = 'create_time <= ' . $condition['createtime'][1];
                }
            } else {
                $where[] = 'create_time = ' . $this->_db->quote($condition['createtime']);
            }
        }

        if (!empty($condition['starttime']) && !empty($condition['endtime'])) {
        	$where[] = 'create_time >= ' . $this->_db->quote($condition['starttime']) . ' AND create_time <= ' . $this->_db->quote($condition['endtime']);
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

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

        return new Oray_Dao_Recordset($records, 'Dao_Md_Log_Record_Login');
    }

    /**
     * Get record page 前台登录日志列表
     *
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getLoginLogPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'md_login_log';
        $columns = 'login_log_id AS loginlogid, org_id AS orgid, unique_id AS uniqueid, address, '
                 . 'truename, ip, local, isp, `clientkey`, client_info AS clientinfo, create_time AS createtime';
        $primary  = 'create_time';
        $recordClass = "Dao_Md_Log_Record_Login";
        $where   = array();
        $order   = array();

        if (!empty($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['address'])) {
            $where[] = 'address = ' . $this->_db->quote($condition['address']);
        }

        if (!empty($condition['truename'])) {
            $where[] = 'truename = ' . $this->_db->quote($condition['truename']);
        }

        if (isset($condition['createtime'])) {
            if (is_int($condition['createtime'])) {
                $where[] = 'create_time = ' . $condition['createtime'];
            } elseif (is_array($condition['createtime'])) {
                $arr = $condition['createtime'];
                if (isset($arr[0]) && is_int($arr[0])) {
                    $where[] = 'create_time >= ' . $arr[0];
                }

                if (isset($arr[1]) && is_int($arr[1])) {
                    $where[] = 'create_time <=' . $arr[1];
                }
            }
        }

        if (!empty($condition['keywords'])) {
            $keyword = $this->_db->quote('%'.$condition['keywords'].'%');
            $like[] = "truename LIKE {$keyword}";

            if (Oray_Function::isByte($condition['keywords'])) {
                $like[] = "address LIKE {$keyword}";
                $like[] = "ip LIKE {$keyword}";
            }

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
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
     *
     * @param array $params
     * @return boolean
     */
    public function createLog(array $params)
    {
        if (empty($params['loginlogid'])
            || empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['address'])
            || empty($params['truename']))
        {
            return false;
        }

        $table = 'md_login_log';
        $bind  = array(
            'login_log_id' => $params['loginlogid'],
            'org_id'       => $params['orgid'],
            'unique_id'    => $params['uniqueid'],
            'address'      => $params['address'],
            'truename'     => $params['truename']
        );

        if (!empty($params['ip'])) {
            $bind['ip'] = $params['ip'];
        }

        if (!empty($params['local'])) {
            $bind['local'] = $params['local'];
        }

        if (!empty($params['isp'])) {
            $bind['isp'] = $params['isp'];
        }

        if (!empty($params['clientkey'])) {
            $bind['clientkey'] = $params['clientkey'];
        }

        if (!empty($params['clientinfo'])) {
            $bind['client_info'] = $params['clientinfo'];
        }

        if (!empty($params['createtime']) && is_int($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['loginlogid'];
    }


    /**
     * 获取日志ID
     *
     * @return string
     */
    public static function getLoginLogId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}