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
 * @version    $Id: Exception.php 2809 2013-04-07 09:57:05Z cutecube $
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
class Model_Tudu_Exception extends Model_Exception
{
    const INVALID_USER      = 1001;
    const PERMISSION_DENIED = 1002;    // 用户权限不允许当前操作

    const TUDU_NOTEXISTS    = 2001;    // 目标图度不存在
    const TUDU_HADBEEN_SENT = 2002;    // 图度已发送（保存已发送草稿）
    const BOARD_NOTEXISTS   = 2003;    // 指定版块不存在
    const TUDU_IS_DRAFT     = 2004;    // 当前操作图度是草稿
    const TUDU_IS_TUDUGROUP = 2005;
    const TUDU_IS_DONE      = 2006;

    const POST_NOTEXISTS    = 2101;

    const LABEL_OPERATION_FAILED = 2201;

    const FLOW_USER_NOT_EXISTS = 2301;
    const FLOW_NOT_EXISTS      = 2302;

    const SAVE_FAILED = 3001;          // 保存失败


}