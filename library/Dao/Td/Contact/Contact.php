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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Contact_Contact extends Oray_Dao_Abstract
{
    /**
     * 获取联系人记录
     * SELECT contact_id AS contactid, unique_id AS uniqueid, true_name AS truename, pinyin, email, mobile,
     * properties, memo, affinity, last_contact_time AS lastcontacttime, groups
     * FROM td_contact
     * WHERE contact_id = ? AND unique_id = ?
     * LIMIT 1
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Contact_Record_Contact
     */
    public function getContact(array $condition, $filter = null)
    {
        $table   = 'td_contact';
        $columns = 'contact_id AS contactid, unique_id AS uniqueid, true_name AS truename, pinyin, email, mobile, '
                 . 'properties, memo, affinity, last_contact_time AS lastcontacttime, groups, from_user AS fromuser';
        $where   = array();

        if (!empty($condition['contactid'])) {
            $where[] = 'contact_id = ' . $this->_db->quote($condition['contactid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['email'])) {
            $where[] = 'email = ' . $this->_db->quote($condition['email']);
        }

        if (!empty($condition['truename'])) {
            $where[] = 'true_name = ' . $this->_db->quote($condition['truename']);
        }

        if (!empty($condition['fromuser'])) {
            $where[] = 'from_user = ' . $condition['fromuser'] ? 1 : 0;
        }

        if (!$where) {
            return false;
        }

        if (isset($filter['isshow'])) {
            $where[] = 'is_show = ' . $filter['isshow'] ? 1 : 0;
        } else {
            $where[] = 'is_show = 1';
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Contact_Record_Contact', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
     * 读取用户列表
     * SELECT contact_id AS contactid, unique_id AS uniqueid, true_name AS truename, pinyin, email, mobile,
     * affinity, last_contact_time AS lastcontacttime, groups
     * FROM td_contact
     * WHERE ..
     * ORDER BY
     * LIMIT ..
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getContacts(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_contact AS c ';
        $columns = 'c.contact_id AS contactid, c.unique_id AS uniqueid, true_name AS truename, pinyin, email, mobile, '
                 . 'affinity, last_contact_time AS lastcontacttime, groups, from_user AS fromuser, ISNULL(c.avatars) AS isavatars';
        $where   = array();
        $order   = '';
        $limit   = '';
        $recordClass = 'Dao_Td_Contact_Record_Contact';

        if (!empty($condition['contactid'])) {
            $where[] = 'c.contact_id = ' . $this->_db->quote($condition['contactid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'c.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['keyword'])) {
            $keyword = $this->_db->quote("%{$condition['keyword']}%");
            $like[] = "true_name LIKE {$keyword}";

            if (!Oray_Function::hasCnChar($keyword)) {
                $like[] = "pinyin LIKE {$keyword}";
                $like[] = "email LIKE {$keyword}";
            }

            $where[] = '(' . implode(' OR ', $like) . ')';
        }

        if (!empty($condition['pinyin'])) {
            $keyword = $this->_db->quote($condition['pinyin'].'%');
            $where[] = "(true_name LIKE {$keyword} OR pinyin LIKE {$keyword})";
        }

        if (array_key_exists('groupid', $condition)) {
            if (!empty($condition['groupid'])) {
                $table .= ' LEFT JOIN td_contact_group_member AS gm ON c.contact_id = gm.contact_id AND c.unique_id = gm.unique_id';
                $where[] = 'gm.group_id = ' . $this->_db->quote($condition['groupid']);
            } else {
                $where[] = 'c.groups = \'\'';
            }
        }

        if (isset($filter['isshow'])) {
            if (null !== $condition['isshow']) {
                $where[] = 'c.is_show = ' . $condition['isshow'] ? 1 : 0;
            }
        } else {
            $where[] = 'c.is_show = 1';
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'lastcontacttime':
                    $key = 'last_contact_time';
                    break;
                case 'affinity':
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        if ($order) {
            $order = 'ORDER BY ' . implode(', ', $order);
        }

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql);

            return new Oray_Dao_Recordset($records, $recordClass);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 分页读取联系人列表
     *
     * @param $condition
     * @param $sort
     * @param $page
     * @param $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getContactPage(array $condition, $sort = null, $page = null, $pageSize = null)
    {
        $table   = 'td_contact AS c';
        $columns = 'c.contact_id AS contactid, c.unique_id AS uniqueid, true_name AS truename, pinyin, email, mobile, '
                 . 'affinity, last_contact_time AS lastcontacttime, groups, from_user AS fromuser';
        $where   = array();
        $order   = array();
        $limit   = '';
        $primary = 'c.contact_id';

        $recordClass = 'Dao_Td_Contact_Record_Contact';

        if (!empty($condition['contactid'])) {
            $where[] = 'c.contact_id = ' . $this->_db->quote($condition['contactid']);
        }

        if (!empty($condition['uniqueid'])) {
            $where[] = 'c.unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (!empty($condition['keywrod'])) {
            $keyword = $this->_db->quote("%{$condition['keyword']}%");
            $where[] = "(true_name LIKE {$keyword} OR pinyin LIKE {$keyword} OR email LIKE {$keyword})";
        }

        if (!empty($condition['nearly']) && is_int($condition['nearly'])) {
            $where[] = 'c.last_contact_time >= ' . strtotime('-' . $condition['nearly'] . ' days');
        }

        if (array_key_exists('isshow', $condition)) {
            if (null !== $condition['isshow']) {
                $where[] = 'is_show = ' . $condition['isshow'] ? 1 : 0;
            }
        } else {
            $where[] = 'is_show = 1';
        }

        if (array_key_exists('groupid', $condition)) {
            if (!empty($condition['groupid'])) {
                $table .= ' LEFT JOIN td_contact_group_member AS gm ON c.contact_id = gm.contact_id AND c.unique_id = gm.unique_id';
                $where[] = 'gm.group_id = ' . $this->_db->quote($condition['groupid']);
            } else {
                $where[] = 'c.groups = \'\'';
            }
        }

        // WHERE
        $where = implode(' AND ', $where);

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'lastcontacttime':
                    $key = 'last_contact_time';
                    break;
                case 'affinity':
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

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
     *
     * @param $uniqueId
     * @param $filter
     * @param $sort
     * @param $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getContactsByUniqueId($uniqueId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getContacts(array('uniqueid' => $uniqueId), $filter, $sort, $maxCount);
    }

    /**
     *
     * @param $contactId
     * @param $uniqueId
     */
    public function getContactById($contactId, $uniqueId, $filter = null)
    {
        return $this->getContact(array(
            'uniqueid'  => $uniqueId,
            'contactid' => $contactId
        ), $filter);
    }

    /**
     *
     * @param $uniqueId
     */
    public function getContactCount($uniqueId)
    {

    }

    /**
     *
     * @param $params
     * @return boolean
     */
    public function createContact(array $params)
    {
        if (empty($params['contactid'])
            || empty($params['uniqueid'])
            || empty($params['truename']))
        {
            return false;
        }

        $table = 'td_contact';
        $bind  = array(
            'contact_id' => $params['contactid'],
            'unique_id'  => $params['uniqueid'],
            'true_name'  => $params['truename']
        );

        if (!empty($params['pinyin'])) {
            $bind['pinyin'] = $params['pinyin'];
        }

        if (!empty($params['email'])) {
            $bind['email'] = $params['email'];
        }

        if (!empty($params['mobile'])) {
            $bind['mobile'] = $params['mobile'];
        }

        if (array_key_exists('properties', $params) && is_array($params['properties'])) {
            $arr = array();
            foreach ($params['properties'] as $k => $val) {
                if (!empty($val)) {
                    $arr[$k] = $val;
                }
            }
            $bind['properties'] = count($arr) ? json_encode($params['properties']) : null;
        }

        if (!empty($params['avatarstype'])) {
            $bind['avatars_type'] = $params['avatarstype'];
        }

        if (!empty($params['avatars'])) {
            $bind['avatars'] = $params['avatars'];
        }

    	if (!empty($params['memo'])) {
            $bind['memo'] = $params['memo'];
        }

        if (!empty($params['fromuser'])) {
            $bind['from_user'] = $params['fromuser'];
        }

        if (isset($params['lastcontacttime']) && is_int($params['lastcontacttime'])) {
            $bind['last_contact_time'] = $params['lastcontacttime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);

            return false;
        }

        return $params['contactid'];
    }

    /**
     * 更新联系人数据
     *
     * @param $contactId
     * @param $params
     */
    public function updateContact($contactId, $uniqueId, array $params)
    {
        if (empty($contactId) || empty($uniqueId)) {
            return false;
        }

        $table = 'td_contact';
        $bind  = array();
        $where = 'contact_id = ' . $this->_db->quote($contactId) . ' AND '
               . 'unique_id = ' . $this->_db->quote($uniqueId);

        if (!empty($params['truename'])) {
            $bind['true_name'] = $params['truename'];
        }

        if (!empty($params['pinyin'])) {
            $bind['pinyin'] = $params['pinyin'];
        }

        if (array_key_exists('email', $params)) {
            $bind['email'] = $params['email'];
        }

        if (array_key_exists('mobile', $params)) {
            $bind['mobile'] = $params['mobile'];
        }

        if (array_key_exists('memo', $params)) {
            $bind['memo'] = $params['memo'];
        }

        if (array_key_exists('properties', $params) && is_array($params['properties'])) {
            $arr = array();
            foreach ($params['properties'] as $k => $val) {
                if (!empty($val)) {
                    $arr[$k] = $val;
                }
            }
            $bind['properties'] = count($arr) ? json_encode($params['properties']) : null;
        }

        if (array_key_exists('avatars', $params)) {
            $bind['avatars'] = $params['avatars'];
        }

        if (array_key_exists('avatarstype', $params)) {
            $bind['avatars_type'] = $params['avatarstype'];
        }

        if (isset($params['lastcontacttime']) && is_int($params['lastcontacttime'])) {
            $bind['last_contact_time'] = $params['lastcontacttime'];
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
     * 删除联系人
     *
     * @param $contactId
     * @param $uniqueId
     * @return boolean
     */
    public function deleteContact($contactId, $uniqueId)
    {
        $contactId = $this->_db->quote($contactId);
        $uniqueId  = $this->_db->quote($uniqueId);

        $sqls = array();

        $sqls[] = "DELETE FROM td_contact_group_member WHERE contact_id = {$contactId} AND unique_id = {$uniqueId}";
        $sqls[] = "DELETE FROM td_contact WHERE contact_id = {$contactId} AND unique_id = {$uniqueId}";

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
     * 获取联系人头像
     *
     * @param $contactId
     */
    public function getAvatars($contactId)
    {
        $sql = 'SELECT avatars_type AS avatarstype, avatars FROM td_contact WHERE contact_id = ' . $this->_db->quote($contactId) . ' LIMIT 1';

        try {
            $record = $this->_db->fetchRow($sql);

            if (!$record) {
                return null;
            }

            $record['avatars'] = base64_decode($record['avatars']);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }

        return $record;
    }

    /**
     *
     */
    public static function getContactId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32) . 'x';
    }
}