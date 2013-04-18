<?php
/**
 * Attend_Category
 * 考勤分类
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Category.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Category extends Oray_Dao_Abstract
{
    const CATEGORY_STATUS_STOP   = 0;// 停用
    const CATEGORY_STATUS_NORMAL = 1;// 正常

    /**
     * 支持的考勤分类状态
     *
     * @var array
     */
    protected $_supportStatus = array(
        self::CATEGORY_STATUS_STOP,
        self::CATEGORY_STATUS_NORMAL
    );

    /**
     * 获取考勤分类信息
     *
     * SELECT category_id AS categoryid, org_id AS orgid, category_name AS categoryname, status,
     * is_show AS isshow, is_system AS issystem
     * FROM attend_category
     * WHERE org_id = :orgid AND category_id = :categoryid
     * LIMIT 0, 1
     *
     * @param array $condition
     * @param array $filter
     */
    public function getCategory(array $condition, $filter = null)
    {
        if (empty($condition['categoryid'])
            || empty($condition['orgid']))
        {
            return false;
        }

        $table   = 'attend_category';
        $columns = 'category_id AS categoryid, org_id AS orgid, category_name AS categoryname, flow_steps AS flowsteps, status, '
                 . 'is_system AS issystem, create_time AS createtime';
        $where   = array();
        $bing    = array();

        if (isset($condition['categoryid'])) {
            $where[] = 'category_id = :categoryid';
            $bind['categoryid'] = $condition['categoryid'];
        }

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} LIMIT 0, 1";
        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_App_Attend_Record_Category', $record);

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 获取多条考勤分类
     *
     * SELECT category_id AS categoryid, org_id AS orgid, category_name AS categoryname, status,
     * is_show AS isshow, is_system AS issystem
     * FROM attend_category
     * WHERE org_id = :orgid
     * ORDER BY is_system DESC, create_time DESC
     * LIMIT xxx
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getCategories(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'attend_category';
        $columns = 'category_id AS categoryid, org_id AS orgid, category_name AS categoryname, flow_steps AS flowsteps, status, '
                 . 'is_system AS issystem, create_time AS createtime';
        $where   = array();
        $order   = array();
        $bind    = array();
        $limit   = '';

        if (isset($condition['orgid'])) {
            $where[] = 'org_id = :orgid';
            $bind['orgid'] = $condition['orgid'];
        }

        if (!empty($filter)) {
            // 考勤分类状态
            if (array_key_exists('status', $filter) && null !== $filter['status']) {
                $where[] = 'status = :status';
                $bind['status'] = (int) $filter['status'];
            }

            // 是否显示
            if (array_key_exists('isshow', $filter) && null !== $filter['isshow']) {
                $where[] = 'is_show = :isshow';
                $bind['isshow'] = (int) $filter['isshow'];
            }
        }

        if (empty($where)) {
            return new Oray_Dao_Recordset();
        }

        $where = 'WHERE ' . implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'status':
                    $key = 'status';
                    break;
                case 'issystem':
                    $key = 'is_system';
                    break;
                case 'createtime':
                    $key = 'create_time';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_App_Attend_Record_Category');

        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 是否已存在排班方案（名称相同的）
     *
     * @param string $orgId
     * @param string $name
     */
    public function existsCategoryName($orgId, $categoryName, $categoryId = null)
    {
        if (empty($orgId) || empty($categoryName)) {
            return false;
        }

        $sql  = 'SELECT COUNT(0) FROM attend_category WHERE org_id = :orgid AND category_name = :name';
        $bind = array(
            'orgid' => $orgId,
            'name'  => $categoryName
        );

        if (!empty($categoryId)) {
            $sql  .= ' AND category_id <> :id';
            $bind['id'] = $categoryId;
        }

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);
            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param string $orgId
     * @param string $categoryId
     * @return boolean
     */
    public function summaryApply($orgId, $categoryId)
    {
        if (empty($orgId) || empty($categoryId)) {
            return false;
        }

        $sql  = 'SELECT COUNT(0) FROM attend_apply WHERE org_id = :orgid AND category_id = :categoryid';
        $bind = array(
            'orgid'      => $orgId,
            'categoryid' => $categoryId
        );

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);

            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 创建考勤分类
     *
     * @param array $params
     * @return boolean|string
     */
    public function createCategory(array $params)
    {
        if (empty($params['categoryid'])
            || empty($params['orgid'])
            || empty($params['flowsteps'])
            || !array_key_exists('categoryname', $params))
        {
            return false;
        }

        $table = 'attend_category';
        $bind  = array(
            'category_id'   => $params['categoryid'],
            'org_id'        => $params['orgid'],
            'category_name' => $params['categoryname'],
            'flow_steps'    => $params['flowsteps'],
            'is_system'     => empty($params['issystem']) ? 0 : 1,
            'create_time'   => !empty($params['createtime']) ? (int) $params['createtime'] : time()
        );

        if (isset($params['status']) && in_array($params['status'], $this->_supportStatus)) {
            $bind['status'] = (int) $params['status'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {

            // 主键冲突
            if (23000 === $e->getCode()) {
                return true;
            }

            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['categoryid'];
    }

    /**
     * 更新考勤分类
     *
     * @param string $categoryId
     * @param string $orgId
     * @param array  $params
     * @return boolean
     */
    public function updateCategory($categoryId, $orgId, array $params)
    {
        if (empty($categoryId) || empty($orgId)) {
            return false;
        }

        $table = 'attend_category';
        $bind  = array();

        if (isset($params['categoryname'])) {
            $bind['category_name'] = $params['categoryname'];
        }

        if (isset($params['flowsteps'])) {
            $bind['flow_steps'] = $params['flowsteps'];
        }

        if (isset($params['status']) && in_array($params['status'], $this->_supportStatus)) {
            $bind['status'] = (int) $params['status'];
        }

        if (isset($params['isshow']) && is_int($params['isshow'])) {
            $bind['is_show'] = $params['isshow'];
        }

        if (empty($bind)) {
            return false;
        }

        try {
            $where = 'category_id = ' . $this->_db->quote($categoryId) . ' AND org_id = ' . $this->_db->quote($orgId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除考勤分类
     *
     * @param string $categoryId
     * @return boolean
     */
    public function deleteCategory($categoryId, $orgId)
    {
        if (empty($categoryId) || empty($orgId)) {
            return false;
        }

        $table = 'attend_category';
        $bind  = array(
            'categoryid' => $categoryId,
            'orgid'      => $orgId
        );

        $sql = "DELETE FROM {$table} WHERE category_id = :categoryid AND org_id = :orgid";

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 格式XML
     *
     * @param string $flowId
     * @param array  $datas
     * @return string
     */
    public static function formatXml($flowId, array $datas)
    {
        if (empty($flowId) || !is_array($datas) || empty($datas)) {
            return null;
        }

        $xml = array(
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<!DOCTYPE flow PUBLIC "-//TUDU//DTD FLOW//ZH-CN" "http://www.tudu.com/dtd/tudu-flow.dtd">',
            '<flow id="' . $flowId . '"><steps>'
        );

        foreach ($datas as $data) {
            $xml[] = '<step id="' . $data['id'] . '">' . self::formatData($data) . '</step>';
        }

        $xml[] = '</steps></flow>';
        return implode('', $xml);
    }

    /**
     * 格式化审批步骤数据
     *
     * @param array $data
     */
    public static function formatData($data)
    {
        return json_encode($data);
    }

    /**
     * 格式化分支数据
     *
     * @param array $branches
     */
    public static function formatBraches($branches)
    {
        $ret = array();

        foreach ($branches as $key => $branch) {
            $ret[] = '<branch id="' . $key . '">';

            foreach ($branch as $k => $val) {
                if ($k == 'users') {
                    $ret[] = '<'.$k.'><![CDATA[' . $val . ']]></'.$k.'>';
                } else {
                    $ret[] = '<'.$k.'>' . $val . '</'.$k.'>';
                }
            }

            $ret[] = '</branch>';
        }

        return implode('', $ret);
    }

    /**
     * 格式化步骤信息
     *
     * @param string $str
     * @return array
     */
    public static function formatSteps($str)
    {
        return @json_decode($str, true);
    }

    /**
     * 获取考勤分类ID
     *
     * return string
     */
    public static function getCategoryId()
    {
        return base_convert(strrev(microtime(true)) . rand(0, 999), 10, 32);
    }

    /**
     * 获取审批流程ID
     *
     * @return string
     */
    public static function getFlowId()
    {
        return base_convert(strrev(microtime(true)) . rand(0, 999), 10, 32);
    }

    /**
     * 获取步骤ID
     *
     * @return string
     */
    public static function getStepId()
    {
        return 'AF-' . base_convert(strrev(microtime(true)) . rand(0, 999), 10, 32);
    }
}