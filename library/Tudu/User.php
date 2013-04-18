<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: User.php 2771 2013-03-11 03:50:01Z cutecube $
 */

/**
 * 图度网站用户后台动作控制器基类
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_User
{

    /**
     * 缓存前缀
     *
     * @var string
     */
    const CACHE_KEY_USER   = 'TUDU-USER-';

    /**
     * 权限缓存前缀
     *
     * @var string
     */
    const CACHE_KEY_ACCESS = 'TUDU-ACCESS-';

    /**
     * 管理员类型
     *
     * @var int
     */
    const ADMIN_SA = 'SA';

    /**
     * 用户属性
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     * 管理员信息
     *
     * @var array
     */
    protected $_admin = null;

    /**
     * 用户权限数据
     *
     * @var array
     */
    protected $_access = null;

    /**
     * 当前对象实例
     *
     * @var Tudu_User
     */
    protected static $_instance;

    /**
     *
     * @var Zend_Session
     */
    protected $_session = null;

    /**
     *
     * @var Oray_Memcache
     */
    protected static $_memcache;

    /**
     *
     */
    protected function __construct()
    {}

    /**
     * 获取对象实例
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     *
     * @param Memcache $cache
     */
    public static function setMemcache(Memcache $cache)
    {
        self::$_memcache = $cache;
    }

    /**
     * 设置用户属性
     */
    public function init(array $identity)
    {
        if (empty($identity['userid']) || empty($identity['orgid']) || empty($identity['tsid'])) {
            return ;
        }

        $key = self::CACHE_KEY_USER . $identity['userid'] . '@' . $identity['orgid'];

        if (!$attrs = self::$_memcache->get($key)) {

            /* @var $daoUser Dao_Md_User_User */
            $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

            $condition = array('orgid' => $identity['orgid'], 'userid' => $identity['userid']);
            $user      = $daoUser->getUser($condition);

            $attrs = array();
            if (null != $user) {
                $userInfo  = $daoUser->getUserInfo($condition);

                /* @var $daoOption Dao_Md_User_Option */
                $daoOption = Tudu_Dao_Manager::getDao('Dao_Md_User_Option', Tudu_Dao_Manager::DB_MD);
                $option    = $daoOption->getOption($condition);

                if (null != $option) {
                    $option = $option->toArray();
                }
                foreach ($option['settings'] as $key => $val) {
                    $option[$key] = $val;
                }
                if (empty($option['settings']['pagesize'])) {
                    $option['settings']['pagesize'] = 25;
                }
                if (empty($option['settings']['replysize'])) {
                    $option['settings']['replysize'] = 20;
                }

                $attrs = array_merge($user->toArray(), $userInfo->toArray());

                $attrs['option']   = $option;
                $attrs['tsid']     = $identity['tsid'];
                $attrs['username'] = $attrs['userid'] . '@' . $attrs['orgid'];

                $attrs['roles']    = $daoUser->getRoleIds($identity['orgid'], $identity['userid']);

                self::$_memcache->set($key, $attrs, null, 86400);
            }
        }

        $this->_attrs = $attrs;
    }

    /**
     * 是否已登录
     *
     * @return boolean
     */
    public function isLogined()
    {
        return !empty($this->_attrs['userid'])
               && !empty($this->_attrs['username'])
               && $this->_attrs['status'] != 0;
    }

    /**
     * 是否管理员
     * 超级管理员（不论群组），系统群组（管理员）
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->isOwner()
               || (!empty($this->_attrs['roles']) && count(array_uintersect($this->_attrs['roles'], array('^administrator', '^admin'), 'strcasecmp')) > 0);
    }

    /**
     * 当前用户是否超级管理员（组织所有者）
     *
     * @return boolean
     */
    public function isOwner()
    {
        return !empty($this->_attrs['admintype'])
               && $this->_attrs['admintype'] == self::ADMIN_SA;
    }

    /**
     * 是否已使用管理员身份登录
     *
     * @return boolean
     */
    public function isAdminLogined()
    {
        return $this->isLogined()
               && !empty($this->_admin);
    }

    /**
     *
     * @param array $identity
     */
    public function initAdmin(array $identity)
    {
        if ($this->isLogined()
            && $this->_attrs['orgid'] == $identity['orgid']
            && $this->_attrs['userid'] == $identity['userid'])
        {
            if (!empty($identity['skin'])) {
                $this->_attrs['option']['skin'] = $identity['skin'];
            }

            $this->_admin = $identity;
        }

        return $this;
    }

    /**
     * 设置Session信息
     *
     * @param Zend_Session $session
     */
    public function setSession(& $session)
    {
        $this->_session = $session;

        return $this;
    }

    /**
     * 修改用户设置
     *
     * @param array $options
     */
    public function setOptions(array $options, $override = false)
    {
        if (!is_array($this->_attrs['option'])) {
            $this->_attrs['option'] = array();
        }

        foreach ($options as $k => $v) {
            if (isset($this->_attrs['option'][$k]) && !$override) {
                continue ;
            }

            $this->_attrs['option'][$k] = $v;
        }

        return $this;
    }

    /**
     *
     * @param $key
     */
    public function getOption($key)
    {
        if (!is_array($this->_attrs['option'])
            || !isset($this->_attrs['option'][$key])) {
            return null;
        }

        return $this->_attrs['option'][$key];
    }

    /**
     * 获取用户权限
     *
     */
    public function getAccess()
    {
        if (null === $this->_access) {
            $key = self::CACHE_KEY_ACCESS . strtolower($this->_attrs['username']);
            $access = self::$_memcache->get($key);
            if (!$access) {
                /* @var $daoAccess Dao_Md_Access_Access */
                $daoAccess  = Tudu_Dao_Manager::getDao('Dao_Md_Access_Access', Tudu_Dao_Manager::DB_MD);
                $access     = $daoAccess->getUserAccess($this->orgId, $this->userId);
                self::$_memcache->set($key, $access, null, 86400);
            }
            $this->_access = new Tudu_Access($access);
        }

        return $this->_access;
    }

    /**
     * 删除用户缓存数据
     *
     */
    public function clearCache($userName)
    {
        self::$_memcache->delete(self::CACHE_KEY_USER . $userName);
        self::$_memcache->delete(self::CACHE_KEY_ACCESS . $userName);
    }

    /**
     *
     * 更新用户设置缓存
     */
    public function updateSetting()
    {
        $key = self::CACHE_KEY_USER . strtolower($this->_attrs['username']);

        self::$_memcache->delete($key);
    }

    /**
     * 返回用户属性数组
     */
    public function toArray()
    {
        $ret = $this->_attrs;

        $ret['isadmin']   = $this->isAdmin();
        $ret['islogined'] = $this->isLogined();
        $ret['isowner']   = $this->isOwner();

        return $ret;
    }

    /**
     *
     * @param $name
     */
    public function __get($name)
    {
        $name = strtolower($name);

        if (isset($this->_attrs[$name])) {
            return $this->_attrs[$name];
        }

        return null;
    }
}