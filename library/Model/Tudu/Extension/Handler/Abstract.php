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
abstract class Model_Tudu_Extension_Handler_Abstract
{
    /**
     * Override by subclass
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {}

    /**
     * Override by subclass
     *
     * @param Model_Tudu_TUdu $tudu
     */
    public function action(Model_Tudu_TUdu &$tudu)
    {}
}