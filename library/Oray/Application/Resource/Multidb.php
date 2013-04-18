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
 * @version    $Id: Multidb.php 8863 2011-12-28 07:03:45Z gxx $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Cache Manager resource
 *
 * Example configuration:
 * <pre>
 *   resources.multidb.db1.adapter = "pdo_mysql"
 *   resources.multidb.db1.host = "localhost"
 *   resources.multidb.db1.username = "webuser"
 *   resources.multidb.db1.password = "password"
 *   resources.multidb.db1.dbname = "db1"
 *   resources.multidb.db1.default = true
 *
 *   resources.multidb.db2.adapter = "pdo_pgsql"
 *   resources.multidb.db2.host = "example.com"
 *   resources.multidb.db2.username = "dba"
 *   resources.multidb.db2.password = "notthatpublic"
 *   resources.multidb.db2.dbname = "db2"
 * </pre>
 *
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Application_Resource_Multidb extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * Associative array containing all configured db's
     *
     * @var array
     */
    protected $_dbs = array();

    /**
     * An instance of the default db, if set
     *
     * @var null|Zend_Db_Adapter_Abstract
     */
    protected $_defaultDb;

    /**
     * Initialize the Database Connections (instances of Zend_Db_Table_Abstract)
     *
     * @return Zend_Application_Resource_Multidb
     */
    public function init()
    {
        $options = $this->getOptions();

        foreach ($options as $id => $params) {
            $default = isset($params['default']) ? (int) $params['default'] : false;
            if ($default
                // For consistency with the Db Resource Plugin
                || (isset($params['isDefaultTableAdapter'])
                    && $params['isDefaultTableAdapter'] == true)
            ) {
                $this->_dbs[$id] = Zend_Db::factory($params['adapter'], $params);
                $this->_setDefault($this->_dbs[$id]);
            }
        }

        return $this;
    }

    /**
     * Determine if the given db(identifier) is the default db.
     *
     * @param  string|Zend_Db_Adapter_Abstract $db The db to determine whether it's set as default
     * @return boolean True if the given parameter is configured as default. False otherwise
     */
    public function isDefault($db)
    {
        if($db instanceof Zend_Db_Adapter_Abstract) {
            return $db === $this->getDb();
        }

        return $db === $this->_defaultDb;
    }

    /**
     * Retrieve the specified database connection
     *
     * @param  null|string|Zend_Db_Adapter_Abstract $db The adapter to retrieve.
     *                                               Null to retrieve the default connection
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Application_Resource_Exception if the given parameter could not be found
     */
    public function getDb($db = null)
    {
        if ($db === null) {
            return $this->getDefaultDb();
        }

        if (isset($this->_dbs[$db])) {
            return $this->_dbs[$db];
        }

        if (isset($this->_options[$db])) {
            $params = $this->_options[$db];
            $this->_dbs[$db] = Zend_Db::factory($params['adapter'], $params);
            return $this->_dbs[$db];
        }

        throw new Zend_Application_Resource_Exception(
            'A DB adapter was tried to retrieve, but was not configured'
        );
    }

    /**
     * Get the default db connection
     *
     * @param  boolean $justPickOne If true, a random (the first one in the stack)
     *                           connection is returned if no default was set.
     *                           If false, null is returned if no default was set.
     * @return null|Zend_Db_Adapter_Abstract
     */
    public function getDefaultDb($justPickOne = true)
    {
        if ($this->_defaultDb !== null) {
            return $this->_defaultDb;
        }

        if ($justPickOne) {
            return reset($this->_dbs); // Return first db in db pool
        }

        return null;
    }

    /**
     * Set the default db adapter
     *
     * @var Zend_Db_Adapter_Abstract $adapter Adapter to set as default
     */
    protected function _setDefault(Zend_Db_Adapter_Abstract $adapter)
    {
        Zend_Db_Table::setDefaultAdapter($adapter);
        $this->_defaultDb = $adapter;
    }
}