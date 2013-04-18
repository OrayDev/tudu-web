<?php
/**
 * Oray Dao
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Db
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Helper.php 13 2010-07-12 10:52:45Z cutecube $
 */

/**
 * @see Oray_Db_Helper
 */
require_once 'Oray/Db/Helper.php';

/**
 * @category   Tudu
 * @package    Db
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Db_Helper extends Oray_Db_Helper
{
    /**
     * 数据库定义
     * 
     * @var string
     */
    const DB_MD = 'tudu-md';
    const DB_UD = 'tudu-ts';
    const DB_TS = 'tudu-ts';
    
    /**
     * 数据库名称
     * 
     * @var string
     */
    const DBNAME_MD = 'tudu-md';
    const DBNAME_TS = 'tudu-ts';
}