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
 * @version    $Id: Exception.php 1928 2012-06-14 06:32:01Z cutecube $
 */

class Tudu_Tudu_Exception extends Exception
{
    // 工作流步骤为空
    const CODE_FLOW_STEP_NULL = 501;
    // 上级不存在
    const CODE_NOT_EXISTS_UPPER = 502;

    const CODE_NOT_EXISTS_USER  = 503;
}