<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Dao
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Manager.php 1865 2012-05-17 07:01:19Z web_op $
 */

/**
 * @category   Tudu
 * @package    Tudu_Dao
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Dao_Manager
{

    /**
     *
     * @var string
     */
    const DB_MD   = 'md';
    const DB_TS   = 'ts';
    const DB_IM   = 'im';
    const DB_SITE = 'site';
    const DB_APP  = 'app';

    /**
     *
     * @var array
     */
    protected static $_arrDb = array();

    /**
     *
     * @var string
     */
    protected static $_arrDao = array();

    /**
     *
     * @param string $key
     */
    public static function setDb($key, Zend_Db_Adapter_Abstract $db, $override = false)
    {
        if (isset(self::$_arrDb[$key]) && !$override) {
            //throw new Exception('Db Adapter is already registered');
            return ;
        }

        self::$_arrDb[$key] = $db;
    }

    /**
     *
     * @param array $db
     */
    public static function setDbs(array $params)
    {
        foreach ($params as $key => $db) {
            if (!is_string($key) || !$key) {
                throw new Exception('Invalid "key" params');
            }

            self::setDb($key, $db);
        }
    }

    /**
     *
     */
    public static function getDb($key)
    {
        if (!isset(self::$_arrDb[$key])) {
            throw new Exception("Db Adapter names: {$key} is not been registered");
        }

        return self::$_arrDb[$key];
    }

    /**
     *
     * @param $className
     * @param $dbKey
     */
    public static function getDao($className, $key)
    {
        $db = self::getDb($key);

        if (!isset(self::$_arrDao[$key][$className])) {
            self::$_arrDao[$key][$className] = Oray_Dao::factory($className, $db);
        }

        return self::$_arrDao[$key][$className];
    }
}