<?php
/**
 * Attend_Record_User
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: User.php 1808 2012-04-19 10:00:32Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_Attend_Record_User extends Oray_Dao_Record
{
    /**
     * 企业组织ID
     * @var string
     */
    public $orgId;

    /**
     * 用户唯一ID
     * @var string
     */
    public $uniqueId;

    /**
     * 部门ID
     * @var string
     */
    public $deptId;

    /**
     * 部门名称
     * @var string
     */
    public $deptName;

    /**
     * 用户真实姓名
     * @var string
     */
    public $trueName;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->deptId     = $record['deptid'];
        $this->trueName   = $record['truename'];
        $this->deptName   = $record['deptname'];

        parent::__construct();
    }
}