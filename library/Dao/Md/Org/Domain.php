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
 * @version    $Id: Domain.php 152 2010-08-11 09:52:45Z gxx $
 */

/**
 * @category   Dao
 * @package    Dao_MD
 * @subpackage Org
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Domain extends Oray_Dao_Abstract
{
    /**
     * 
     * @param $domainName
     * @return int|false
     */
    public function getDomainId($domainName)
    {
        if (empty($domainName)) {
            return false;
        }
        $sql = 'SELECT domain_id FROM md_domain WHERE domain_name = ' . $this->_db->quote($domainName);
        $domainId = (int) $this->_db->fetchOne($sql);
        if (!$domainId) {
            $sql = 'INSERT INTO md_domain(domain_name) VALUES(' . $this->_db->quote(strtolower($domainName)) . ')';
            $this->_db->query($sql);
            $domainId = (int) $this->_db->lastInsertId();
        }
        return $domainId;
    }
    
}