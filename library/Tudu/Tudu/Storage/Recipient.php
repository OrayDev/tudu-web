<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Recipient.php 1292 2011-11-15 10:10:57Z cutecube $
 */

/**
 * 图度数据储存对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Recipient
{

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $trueName;

    /**
     *
     * @var boolean
     */
    public $isForeign;

    /**
     *
     * @var int
     */
    public $tuduStatus;

    /**
     *
     * @var int
     */
    public $percent;

    /**
     *
     * @var boolean
     */
    public $isForward;
}