<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Option.php 2320 2012-11-01 02:26:25Z web_op $
 */

/**
 * @category   Dao
 * @package    Md
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Option extends Oray_Dao_Abstract
{
    /**
     * 获取用户配置信息
     *
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_User_Record_Option
     */
    public function getOption(array $condition, $filter = null)
    {
        $table   = 'md_user_data';
        $columns = 'org_id AS orgid, user_id AS userid, skin, timezone, language, pagesize, settings, '
                 . 'date_format AS dateformat, profile_mode AS profilemode, expired_filter AS expiredfilter, '
                 . 'post_sort AS postsort, usual_local AS usuallocal';
        $where = array();

        if (!empty($condition['orgid']) && !empty($condition['userid'])) {
            $where[] = 'user_id = ' . $this->_db->quote($condition['userid']);
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Md_User_Record_Option', $record);
    }

    /**
     *
     * @param array $params
     * @return boolean
     */
    public function createOption(array $params)
    {
        $table = 'md_user_data';
        $bind  = array();

        if (empty($params['userid']) || empty($params['orgid'])) {
            return false;
        }

        $bind['userid'] = $params['userid'];
        $bind['orgid']  = $params['orgid'];

        if (isset($params['im'])) {
            $bind['im'] = $params['im'];
        }

        if (!empty($params['skin'])) {
            $bind['skin'] = $params['skin'];
        }

        if (!empty($params['timezone'])) {
            $bind['timezone'] = $params['timezone'];
        }

        if (!empty($params['language'])) {
            $bind['language'] = $params['language'];
        }

        if (isset($params['pagesize']) && is_int($params['pagesize'])) {
            $bind['pagesize'] = $params['pagesize'];
        }

        if (isset($params['profilemode']) && is_int($params['profilemode'])) {
            $bind['profile_mode'] = $params['profilemode'];
        }

        if (isset($params['expiredfilter']) && is_int($params['expiredfilter'])) {
            $bind['expired_filter'] = $params['expiredfilter'];
        }

        if (isset($params['postsort'])) {
            $bind['post_sort'] = (int) $params['postsort'] == 1 ? 1 : 0;
        }

        if (isset($params['settings'])) {
            $bind['settings'] = $params['settings'];
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
     *
     * @param string $orgId
     * @param stirng $userId
     * @param array  $params
     * @return boolean
     */
    public function updateOption($orgId, $userId, array $params)
    {
        if (!$orgId || !$userId) {
            return false;
        }

        $table = 'md_user_data';
        $bind  = array();
        $where = 'org_id = ' . $this->_db->quote($orgId) . ' AND user_id = ' . $this->_db->quote($userId);

        //if (isset($params['im'])) {
        //    $bind['im'] = $params['im'];
        //}

        if (isset($params['skin'])) {
            $bind['skin'] = $params['skin'];
        }

        if (!empty($params['language'])) {
            $bind['language'] = $params['language'];
        }

        if (!empty($params['timezone'])) {
            $bind['timezone'] = $params['timezone'];
        }

        if (!empty($params['dateformat'])) {
            $bind['date_format'] = $params['dateformat'];
        }

        if (isset($params['pagesize']) && is_int($params['pagesize'])) {
            $bind['pagesize'] = $params['pagesize'];
        }

        if (isset($params['profilemode']) && is_int($params['profilemode'])) {
            $bind['profile_mode'] = $params['profilemode'];
        }

        if (isset($params['expiredfilter']) && is_int($params['expiredfilter'])) {
            $bind['expired_filter'] = $params['expiredfilter'];
        }

        if (isset($params['postsort'])) {
            $bind['post_sort'] = (int) $params['postsort'] == 1 ? 1 : 0;
        }

        if (array_key_exists('usuallocal', $params)) {
            $bind['usual_local'] = $params['usuallocal'];
        }

        if (isset($params['settings'])) {
            $bind['settings'] = $params['settings'];
        }

        if (empty($bind)) {
            return false;
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