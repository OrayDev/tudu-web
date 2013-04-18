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
 * @version    $Id: Exception.php 2524 2012-12-20 01:30:27Z cutecube $
 */

/**
 * @see Model_Exception
 */
require_once 'Model/Exception.php';

/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_App_Attend_Exception extends Model_Exception
{
    const APPLY_SAVE_FAILED = 1001; // 申请信息保存失败
    const APPLY_MISSING_CATEGORYID = 1002;    // 类型
    const APPLY_INVALID_STARTTIME = 1003;    // 开始或结束时间
    const APPLY_INVALID_ENDTIME   = 1004;
    const APPLY_INVALID_TIME = 1005;        // 时间错误（补签）

    const CATEGORY_NOT_EXISTS = 1010;
}