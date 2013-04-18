<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: AddressBook.php 2758 2013-02-27 06:15:56Z cutecube $
 */

/**
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_AddressBook
{

    /**
     *
     * @var Memcache
     */
    protected $_cache;

    /**
     *
     * @var array
     */
    protected $_users;

    /**
     *
     * @var array
     */
    protected $_groupUsers;

    /**
     *
     * @var array
     */
    protected $_groupContacts;

    /**
     *
     * @var Tudu_Tudu_Manager
     */
    protected static $_instance;

    /**
     * 单例模式，隐藏构造函数
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function __construct()
    {}

    /**
     * 获取对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function newInstance()
    {
        return new self();
    }

    /**
     *
     * @param Memcache $cache
     */
    public function setCache(Memcache $cache)
    {
        $this->_cache = $cache;
    }


    /**
     * 获取组织用户列表
     *
     * @param string $orgId
     * @return array
     */
    public function getUsers($orgId)
    {
        if (!$this->_users) {
            /*if (null !== $this->_cache) {
                $this->_users = $this->_cache->get('TUDU-USER-LIST-' . $orgId);
            }*/

            if (!$this->_users) {
                /* @var $daoUser Dao_Md_User_User */
                $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

                $this->_users = $daoUser->getUsers(array('orgid' => $orgId))->toArray('username');

                /*if (null !== $this->_cache) {
                    $flag = null;
                    $this->_cache->set('TUDU-USER-LIST-' . $orgId, $this->_users, $flag, 86400);
                }*/
            }
        }

        return $this->_users;
    }

    /**
     * 查找组织用户
     *
     * @param string $email
     * @param string $trueName
     * @return array | null
     */
    public function searchUser($orgId, $email, $trueName = null)
    {
        $users = $this->getUsers($orgId);

        if (!isset($users[$email])) {
            return null;
        }

        $user = $users[$email];

        if (null !== $trueName) {
            if ($trueName != $user['truename']) {
                return null;
            }
        }

        return array(
            'uniqueid' => $user['uniqueid'],
            'deptid'   => $user['deptid'],
            'email'    => $user['username'],
            'truename' => $user['truename'],
            'status'   => $user['status']
        );
    }

    /**
     * 查找联系人
     *
     * @param string $uniqueId
     * @param stirng $email
     * @param string $trueName
     * @return array | null
     */
    public function searchContact($uniqueId, $email, $trueName)
    {
        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        $condition = array(
            'uniqueid' => $uniqueId,
            'truename' => $trueName,
            'email'    => $email
        );

        $contact = $daoContact->getContact($condition);

        if (!$contact) {
            return null;
        }

        return array(
            'uniqueid'     => $contact->contactId,
            'email'        => $email,
            'truename'     => $trueName,
            'isforeign'    => true
        );
    }

    /**
     *
     * @param string $orgId
     * @param string $groupId
     * @return array
     */
    public function getGroupUsers($orgId, $groupId)
    {
        $key = $groupId . '@' . $orgId;

        if (isset($this->_groupUsers[$key])) {
            var_dump($key);
            return $this->_groupUsers[$key];
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

        $members = $daoUser->getUsers(array('orgid' => $orgId, 'groupid' => $groupId))->toArray();

        $users = array();
        foreach ($members as $member) {
            $user = $this->searchUser($orgId, $member['username']);
            if (null !== $user && $user['status'] != 0) {
                $users[$member['uniqueid']] = array(
                    'uniqueid' => $member['uniqueid'],
                    'email'    => $member['username'],
                    'truename' => $member['truename']
                );
            }
        }

        $this->_groupUsers[$key] = $users;

        return $this->_groupUsers[$key];
    }

    /**
     *
     * @param string $uniqueId
     * @param string $groupId
     * @return array
     */
    public function getGroupContacts($orgId, $uniqueId, $groupId)
    {
        $key = $groupId . '-' . $uniqueId;

        if (isset($this->_groupContacts[$key])) {
            return $this->_groupContacts[$key];
        }

        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        $members = $daoContact->getContacts(array('uniqueid' => $uniqueId, 'groupid' => $groupId));

        $users = $this->getUsers($orgId);

        $ret = array();
        foreach ($members as $member) {
            $isForeign = $member->fromUser && isset($users[$member->email]);

            $ret[$member->contactId] = array(
                'uniqueid'  => $member->contactId,
                'email'     => $member->email,
                'truename'  => $member->trueName,
                'isforeign' => $isForeign
            );
        }

        $this->_groupContacts[$key] = $ret;

        return $this->_groupContacts[$key];
    }

    /**
     *
     * @param string $uniqueId
     * @param string $email
     * @param string $trueName
     * @return array
     */
    public function prepareContact($email, $trueName)
    {
        return array(
            'uniqueid'  => Dao_Td_Contact_Contact::getContactId(),
            'email'     => $email,
            'truename'  => $trueName,
            'isforeign' => true
        );
    }
}