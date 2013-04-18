<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Divide.php 1828 2012-04-28 09:48:32Z cutecube $
 */

require_once 'Tudu/Model/Tudu/Extension/Abstract.php';

/**
 * 任务分工流程
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Extension_Divide extends Tudu_Model_Tudu_Extension_Abstract
{

    /**
     * 调用 Tudu_Model_Tudu_Compose中与当前类名相同的方法
     * 实现分工图度的流程
     *
     * @param Tudu_Model_Entity_Tudu $tudu
     */
    public function composeHandler(Tudu_Model_Entity_Tudu $tudu)
    {}
}