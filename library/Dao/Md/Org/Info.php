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
 * @version    $Id: Info.php 1701 2012-03-15 07:41:46Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Info extends Oray_Dao_Abstract
{
    /**
     * SELECT org_id AS orgid, industry, contact, tel, address, postcode, description
     * FROM md_org_info
     * WHERE org_id = :orgid
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Md_Org_Record_Info
     */
    public function getOrgInfo(array $condition, $filter = null)
    {
        $table = 'md_org_info';
        $columns = 'org_id AS orgid, entire_name AS entirename, industry, contact, tel, fax, province, city, address, postcode, description, realname_status AS realnamestatus ';

        $where   = array();

        if (!empty($condition['orgid'])) {
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

        return Oray_Dao::record('Dao_Md_Org_Record_Info', $record);
    }

    /**
     * 创建企业信息
     *
     * @param array $params
     * @return boolean
     */
    public function create(array $params)
    {
        if (empty($params['orgid'])) {
            return false;
        }

        $table = 'md_org_info';
        $bind = array(
            'org_id' => $params['orgid']
        );

        if (!empty($params['entirename'])) {
            $bind['entire_name'] = $params['entirename'];
        }

        if (!empty($params['industry'])) {
            $bind['industry'] = $params['industry'];
        }

        if (!empty($params['contact'])) {
            $bind['contact'] = $params['contact'];
        }

        if (!empty($params['tel'])) {
            $bind['tel'] = $params['tel'];
        }

        if (!empty($params['fax'])) {
            $bind['fax'] = $params['fax'];
        }

        if (!empty($params['province'])) {
            $bind['province'] = $params['province'];
        }

        if (!empty($params['city'])) {
            $bind['city'] = $params['city'];
        }

        if (!empty($params['address'])) {
            $bind['address'] = $params['address'];
        }

        if (!empty($params['postcode']) && is_int($params['postcode'])) {
            $bind['postcode'] = $params['postcode'];
        }

        if (!empty($params['description'])) {
            $bind['description'] = $params['description'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['orgid'];
    }

    /**
     * 更新企业信息
     *
     * @param string $orgId
     * @param array  $params
     * @return boolean
     */
    public function update($orgId, array $params)
    {
        if (!$orgId) {
            return false;
        }

        $table = 'md_org_info';
        $bind = array();
        $where = 'org_id = ' . $this->_db->quote($orgId);

        if (!empty($params['entirename'])) {
            $bind['entire_name'] = $params['entirename'];
        }

        if (array_key_exists('industry', $params)) {
            $bind['industry'] = $params['industry'];
        }

        if (array_key_exists('contact', $params)) {
            $bind['contact'] = $params['contact'];
        }

        if (array_key_exists('tel', $params)) {
            $bind['tel'] = $params['tel'];
        }

        if (array_key_exists('fax', $params)) {
            $bind['fax'] = $params['fax'];
        }

        if (array_key_exists('province', $params)) {
            $bind['province'] = $params['province'];
        }

        if (array_key_exists('city', $params)) {
            $bind['city'] = $params['city'];
        }

        if (array_key_exists('address', $params)) {
            $bind['address'] = $params['address'];
        }

        if (array_key_exists('postcode', $params)) {
            $bind['postcode'] = $params['postcode'];
        }

        if (array_key_exists('description', $params)) {
            $bind['description'] = $params['description'];
        }

        if (array_key_exists('realnamestatus', $params)) {
            $bind['realname_status'] = $params['realnamestatus'];
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