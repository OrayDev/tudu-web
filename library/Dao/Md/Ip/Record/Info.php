<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md_Log_Record_AdminLog
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Info.php 1031 2011-07-28 10:17:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md_Log_Record_AdminLog
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Ip_Record_Info extends Oray_Dao_Record
{

    /**
     *
     * @var int
     */
    public $startIp;

    /**
     *
     * @var int
     */
    public $endIp;

    /**
     *
     * @var string
     */
    public $province;

    /**
     *
     * @var string
     */
    public $city;

    /**
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->startIp  = $this->_toInt($record['startip']);
        $this->endIp    = $this->_toInt($record['endip']);
        $this->province = $record['province'];
        $this->city     = $record['city'];

        parent::__construct();
    }
}