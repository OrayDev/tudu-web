<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Tudu.php 2070 2012-08-22 09:37:26Z cutecube $
 */

/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Model_Tudu_Extension_Abstract
{
    /**
     *
     * @var string
     */
    protected $_handlerClass = null;

    /**
     *
     * @var array
     */
    protected static $_handlers = array();

    /**
     *
     * @return string
     */
    abstract public function getHandlerClass() ;

    /**
     *
     */
    public function getHandler($className)
    {
        if (!isset(self::$_handlers[$className])) {
            Zend_Loader::loadClass($className);
            self::$_handlers[$className] = new $className();
        }

        return self::$_handlers[$className];
    }
}