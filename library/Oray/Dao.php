<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Dao.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @see Oray_Dao_Abstract
 */
require_once 'Oray/Dao/Abstract.php';

/**
 * @see Oray_Dao_Record
 */
require_once 'Oray/Dao/Record.php';

/**
 * @see Oray_Dao_Recordset
 */
require_once 'Oray/Dao/Recordset.php';

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Oray_Dao
{
    /**
     * Factory for Oray_Dao_Abstract classes.
     * 
     * @param string $className
     * @param mixed $db
     * @return Oray_Dao_Abstract
     */
    public static function factory($className, $db = null)
    {
        Zend_Loader::loadClass($className);
        return new $className($db);
    }
    
    /**
     * Factory for Oray_Dao_Record classes.
     * 
     * @param string $recordClass
     * @param array $fields
     * @param boolen $allowModifications
     * @return Oray_Dao_Record
     */
    public static function record($recordClass, array $fields, $allowModifications = true)
    {
        Zend_Loader::loadClass($recordClass);
        return new $recordClass($fields, $allowModifications);
    }
    
}