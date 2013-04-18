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
 * @version    $Id: Memcache.php 7327 2011-08-22 02:41:58Z gxx $
 */

/**
 * Resource for creating memcache adapter
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Application_Resource_Memcache extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Oray_Memcache
     */
    protected $_memcache;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Oray_Memcache|null
     */
    public function init()
    {
        return $this->getMemcache();
    }

    /**
     * Retrieve memcache object
     *
     * @return Oray_Memcache
     */
    public function getMemcache()
    {
        if (null === $this->_memcache) {
            $this->_memcache = new Oray_Memcache($this->_options);
        }
        return $this->_memcache;
    }
}