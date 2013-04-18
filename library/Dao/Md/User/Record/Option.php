<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Option
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Option.php 2320 2012-11-01 02:26:25Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Option
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_User_Record_Option extends Oray_Dao_Record
{

    /**
     *
     * @var string
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $orgId;

    /**
     *
     * @var string
     */
    public $skin;

    /**
     *
     * @var string
     */
    public $timezone;

    /**
     *
     * @var string
     */
    public $language;

    /**
     *
     * @var int
     */
    public $pagesize;

    /**
     *
     * @var string
     */
    public $dateFormat;

    /**
     *
     * @var int
     */
    public $expiredFilter;

    /**
     *
     * @var int
     */
    public $profileMode;

    /**
     *
     * @var int
     */
    public $postSort;

    /**
     *
     * @var string
     */
    public $usualLocal;

    /**
     *
     * @var array
     */
    public $settings;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId        = $record['orgid'];
        $this->userId       = $record['userid'];
        $this->language     = $record['language'];
        $this->skin         = $record['skin'];
        $this->timezone     = $record['timezone'];
        $this->pagesize     = $this->_toInt($record['pagesize']);
        $this->dateFormat   = $record['dateformat'];
        $this->expiredFilter= $this->_toInt($record['expiredfilter']);
        $this->profileMode  = $this->_toInt($record['profilemode']);
        $this->postSort     = $this->_toInt($record['postsort']);
        $this->usualLocal   = $record['usuallocal'];
        $this->settings     = !empty($record['settings']) ? json_decode($record['settings'], true) : array();

        parent::__construct();
    }
}