<?php
/**
 *
 * LICENSE
 *
 *
 * @category   Admin
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: Admin.php 1166 2011-09-28 04:02:24Z cutecube $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @category  Admin
 * @package   Admin
 */
class Admin_Admin
{
    /**
     * 组织ID
     * @var string
     */
    public $orgId;

    /**
     * TSID
     * @var string
     */
    public $tsId;

    /**
     * 帐号名
     * @var string
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $trueName;

    /**
     *
     * Email格式帐号名
     * @var string
     */
    public $address;

    /**
     *
     * @var int
     */
    public $adminLevel;

    /**
     *
     * @var string
     */
    public $adminType;

    /**
     *
     * 是否Oray护照登录
     * @var boolean
     */
    public $isPassport;

    /**
     * 皮肤
     * @var string
     */
    public $skin;

    /**
     *
     * 权限
     * @var boolean
     */
    protected $_access;

    /**
     *
     * 当前对象实例
     * @var TuduX_Admin_Admin
     */
    protected static $_instance;

    /**
     *
     * @var array
     */
    protected $_attributes;


    /**
     *
     * Constructor
     */
    protected function __construct()
    {
        $attrs = get_object_vars($this);
        foreach ($attrs as $key => $value) {
            if ('_' != substr($key, 0, 1)) {
                $this->_attributes[strtolower($key)] = $value;
                unset($this->$key);
            }
        }
    }

    /**
     *
     */
    public static function getInstance()
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * @param $identity
     */
    public function init(array $identity)
    {
        if ((empty($identity['orgid']) || empty($identity['userid']))
            && empty($identity['ispassport']))
        {
            return ;
        }

        if (empty($identity['tsid'])) {
            return ;
        }

        $this->setAttributes($identity);
    }

    /**
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $val) {
            if (array_key_exists($key, $this->_attributes)) {
                $this->_attributes[$key] = $val;
            }
        }
    }

    /**
     * 是否已登录
     */
    public function isLogin()
    {
        return $this->userId && $this->orgId;
    }

    /**
     *
     */
    public function toArray()
    {
        return $this->_attributes;
    }

    /**
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name];
        }

        return null;
    }
}