<?php
/**
 * Model User Role
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_User
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Role.php 2048 2012-08-07 09:33:36Z chenyongfa $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * @category   Model
 * @package    Model_User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 */
class Model_User_Role extends Model_Abstract
{
    const CODE_INVALID_ORGID     = 301;
    const CODE_INVALID_ROLEID    = 302;
    const CODE_INVALID_UID       = 303;
    const CODE_INVALID_ROLENAME  = 304;
    const CODE_SAVE_FAILED       = 305;
    const CODE_ROLE_NOTEXISTS    = 306;
    const CODE_INVALID_ACCESSID  = 307;
    const CODE_INVALID_ACCESSVAL = 308;
    const CODE_ROLE_USER_NOT_NULL = 309;

    /**
     * 创建权限组
     *
     * @param array $params
     */
    public function create(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组名称，必须有
        if (empty($params['rolename'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLENAME);
        }

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        $orgId    = $params['orgid'];
        $roleName = $params['rolename'];
        $roleId   = !empty($params['roleid']) ? $params['roleid'] : Dao_Md_User_Role::getRoleId($orgId, $roleName);

        // 创建权限组
        $ret = $daoRole->createRole(array(
            'orgid'    => $orgId,
            'roleid'   => $roleId,
            'rolename' => $roleName
        ));

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Create role failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 更新权限组
     *
     * @param array $params
     */
    public function update(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        $orgId  = $params['orgid'];
        $roleId = $params['roleid'];

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        // 权限组必须有
        $existRole = $daoRole->existsRole($orgId, $roleId);
        if (!$existRole) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Role "' . $roleId . '" not exists', self::CODE_ROLE_NOTEXISTS);
        }

        $updateParams = array();
        if (!empty($params['rolename'])) {
            $updateParams['rolename'] = $params['rolename'];
        }

        if (!empty($updateParams)) {
            $ret = $daoRole->updateRole($orgId, $roleId, $updateParams);
            if (!$ret) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Update role failed', self::CODE_SAVE_FAILED);
            }
        }

        return true;
    }

    /**
     * 删除权限组
     *
     * @param array $params
     */
    public function delete(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        $orgId  = $params['orgid'];
        $roleId = $params['roleid'];
        // 是否已验证权限组是否存在
        $isVerify= !empty($params['isverify']) ? true : false;

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        if (!$isVerify) {
            $existRole = $daoRole->existsRole($orgId, $roleId);
            if (!$existRole) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Role "' . $roleId . '" not exists', self::CODE_ROLE_NOTEXISTS);
            }
        }

        if ($daoRole->countUser($orgId, $roleId) > 0) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Role "' . $roleId . '" user not null', self::CODE_ROLE_USER_NOT_NULL);
        }

        // 删除
        $ret = $daoRole->deleteRole($orgId, $roleId);
        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Delete role failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 添加权限用户
     *
     * @param array $params
     */
    public function addUser(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        // 用户ID
        if (empty($params['userid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "uid"', self::CODE_INVALID_UID);
        }

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        $ret = $daoRole->addUsers($params['orgid'], $params['roleid'], $params['userid']);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Add user in role "' . $params['roleid'] . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 移除权限用户
     *
     * @param array $params
     */
    public function removeUser(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        $orgId  = $params['orgid'];
        $roleId = $params['roleid'];
        $userId = !empty($params['userid']) ?  $params['userid'] : null;

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        $ret = $daoRole->removeUser($orgId, $roleId, $userId);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Remove user in role "' . $roleId . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }
    
    /**
     * 更新权限组成员
     */
    public function updateMember(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        $orgId  = $params['orgid'];
        $roleId = $params['roleid'];
        $users  = $params['users'];
        // 是否已验证权限组是否存在
        $isVerify= !empty($params['isverify']) ? true : false;

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        if (!$isVerify) {
            $existRole = $daoRole->existsRole($orgId, $roleId);
            if (!$existRole) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Role "' . $roleId . '" not exists', self::CODE_ROLE_NOTEXISTS);
            }
        }

        if ($daoRole->removeUser($orgId, $roleId)) {
            if (!empty($users)) {
                $daoRole->addUsers($orgId, $roleId, $users);
            }

            return ;
        }

        require_once 'Model/User/Exception.php';
        throw new Model_User_Exception('Update role member failed', self::CODE_SAVE_FAILED);
    }

    /**
     * 添加权限
     *
     * @param array $params
     */
    public function addAccess(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        // 权限ID，必须有
        if (empty($params['accessid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "accessid"', self::CODE_INVALID_ACCESSID);
        }

        // 权限值
        if (!isset($params['value'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "value"', self::CODE_INVALID_ACCESSVAL);
        }

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        $ret = $daoRole->addAccess($params['orgid'], $params['roleid'], array(
            'accessid' => $params['accessid'],
            'value'    => $params['value']
        ));

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Add access in role "' . $params['roleid'] . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 移除权限
     *
     * @param array $params
     */
    public function removeAccess(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        $orgId    = $params['orgid'];
        $roleId   = $params['roleid'];
        $accessId = !empty($params['accessid']) ?  $params['accessid'] : null;

        $ret = $daoRole->removeAccess($orgId, $roleId, $accessId);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Remove access in role "' . $roleId . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 更新权限组权限
     */
    public function updateAccess(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 权限组ID，必须有
        if (empty($params['roleid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_ROLEID);
        }

        $orgId    = $params['orgid'];
        $roleId   = $params['roleid'];
        $accesses = $params['access'];
        // 是否已验证权限组是否存在
        $isVerify= !empty($params['isverify']) ? true : false;

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);

        if (!$isVerify) {
            $existRole = $daoRole->existsRole($orgId, $roleId);
            if (!$existRole) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Role "' . $roleId . '" not exists', self::CODE_ROLE_NOTEXISTS);
            }
        }

        if ($daoRole->removeAccess($orgId, $roleId)) {
            foreach ($accesses as $access) {
                $daoRole->addAccess($orgId, $roleId, array('accessid' => $access['accessid'], 'value' => $access['value']));
            }

            return ;
        }

        require_once 'Model/User/Exception.php';
        throw new Model_User_Exception('Update role access failed', self::CODE_SAVE_FAILED);
    }
}