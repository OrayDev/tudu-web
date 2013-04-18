<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
interface Model_Tudu_Send_Interface
{
    /**
     * 发送图度操作
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function send(Model_Tudu_Tudu &$tudu) ;
}