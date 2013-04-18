<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Dns.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * Resource for creating dns api
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Application_Resource_Dns extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Oray_Dns
     */
    protected $_dns;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Oray_Dns
     */
    public function init()
    {
        return $this->getDns();
    }
    
    /**
     * Retrieve memcache object
     *
     * @return Oray_Memcache
     */
    public function getDns()
    {
        if (null === $this->_dns) {
            $this->_dns = new Oray_Dns($this->_options);
        }
        return $this->_dns;
    }
}