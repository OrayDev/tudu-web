<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Permission.php 1349 2011-12-05 02:57:03Z cutecube $
 */

/**
 * @category   Tudu
 * @package    Tudu_App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_App_Permission
{

    // 访问组织信息
    const ORG_ACCESS   = 101;

    // 访问组织架构
    const CAST_ACCESS  = 201;

    // 访问用户权限
    const USER_ACCESS  = 301;
    const USER_CREATE  = 302;
    const USER_UPDATE  = 303;
    const USER_DELETE  = 304;

    // 用户权限组访问权限
    const ROLE_ACCESS  = 401;
    const ROLE_CREATE  = 402;
    const ROLE_UPDATE  = 403;
    const ROLE_DELETE  = 404;

    // 用户群组访问权限
    const GROUP_ACCESS = 501;
    const GROUP_CREATE = 502;
    const GROUP_UPDATE = 503;
    const GROUP_DELETE = 504;

    // 版块权限
    const BOARD_ACCESS = 601;
    const BOARD_CREATE = 602;
    const BOARD_UPDATE = 603;
    const BOARD_DELETE = 604;

    // 图度访问权限
    const TUDU_ACCESS = 701;
    const TUDU_CREATE = 702;
    const TUDU_UPDATE = 703;
    //const TUDU_DELETE = 704;

    const CONTACT_ACCESS = 801;
    const CONTACT_CREATE = 802;
    const CONTACT_UPDATE = 803;
    //const CONTACT_DELETE = 804;

    // 聊天记录访问
    const CHAT_ACCESS = 1000;
}