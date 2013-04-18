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
 * @version    $Id: Associate.php 1925 2012-06-13 06:53:44Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Associate extends Oray_Dao_Abstract
{
    /**
     * 创建组织关联
     *
     * @param array $params
     * @return boolean
     */
    public function createAssociate(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['from'])
            || empty($params['uid'])
            || empty($params['createtime']))
        {
            return false;
        }

        $table = 'md_org_associate';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'from'        => $params['from'],
            'uid'         => $params['uid'],
            'create_time' => $params['createtime']
        );

        if (!empty($params['truename'])) {
            $bind['truename'] = $params['truename'];
        }

        if (!empty($params['email'])) {
            $bind['email'] = $params['email'];
        }

        if (!empty($params['tel'])) {
            $bind['tel'] = $params['tel'];
        }

        if (!empty($params['mobile'])) {
            $bind['mobile'] = $params['mobile'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 通过来源用户ID获取组织ID
     *
     * @param $from
     * @param $uId
     */
    public function getOrgIdByUid($from, $uId)
    {
        $sql = "SELECT org_id FROM md_org_associate WHERE `from` = :from AND uid = :uid";

        try {
            $record = $this->_db->fetchRow($sql, array(
                'from' => $from,
                'uid'  => $uId
            ));

            if (!$record) {
                return false;
            }

            return $record['org_id'];
        }  catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     *
     * @param array $condition
     */
    public function getOrgCount($condition = null)
    {
        $sql  = "SELECT COUNT(0) AS count FROM md_org_associate WHERE ";
        $bind = array();

        if (isset($condition['from'])) {
            $sql .= '`from` = :from';
            $bind['from'] = $condition['from'];
        }

        try {
            $row = $this->_db->fetchRow($sql, $bind);

            return (int) $row['count'];

        }  catch (Zend_Db_Execption $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }
}