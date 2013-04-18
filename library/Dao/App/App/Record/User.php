<?php
/**
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: User.php 1863 2012-05-16 10:05:22Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_App
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_App_Record_User extends Oray_Dao_Record
{

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 应用ID
     *
     * @var string
     */
    public $appId;

    /**
     * 用户 / 群组ID
     *
     * @var string
     */
    public $itemId;

    /**
     * 角色ID
     *
     * @var string
     */
    public $role;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->appId      = $record['appid'];
        $this->itemId     = $record['itemid'];
        $this->role       = $record['role'];

        parent::__construct();
    }
}