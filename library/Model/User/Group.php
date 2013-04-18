<?php
/**
 * Model User Group
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_User
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Group.php 2048 2012-08-07 09:33:36Z chenyongfa $
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
class Model_User_Group extends Model_Abstract
{
    const CODE_INVALID_ORGID     = 201;
    const CODE_INVALID_GROUPID   = 202;
    const CODE_INVALID_GROUPNAME = 203;
    const CODE_INVALID_UID       = 204;
    const CODE_SAVE_FAILED       = 205;
    const CODE_GROUP_NOTEXISTS   = 206;
    const CODE_INVALID_SORTTYPE  = 207;
    const CODE_GROUP_USER_NOT_NULL = 208;

    /**
     * 创建群组
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

        // 群组名称，必须有
        if (empty($params['groupname'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupname"', self::CODE_INVALID_GROUPNAME);
        }

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        $orgId     = $params['orgid'];
        $groupName = $params['groupname'];
        $groupId   = !empty($params['groupid']) ? $params['groupid'] : Dao_Md_User_Group::getGroupId($orgId, $groupName);
        $orderNum  = $daoGroup->getGroupMaxOrderNum(array('orgid' => $orgId));

        // 创建群组
        $ret = $daoGroup->createGroup(array(
            'orgid'     => $orgId,
            'groupname' => $groupName,
            'groupid'   => $groupId,
            'ordernum'  => $orderNum + 1
        ));

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Create user group failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 更新群组
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

        // 群组ID，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        $orgId   = $params['orgid'];
        $groupId = $params['groupid'];

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        // 群组必须有
        $existGroup = $daoGroup->existsGroup($orgId, $groupId);
        if (!$existGroup) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Group "' . $groupId . '" not exists', self::CODE_GROUP_NOTEXISTS);
        }

        $updateParams = array();
        if (!empty($params['groupname'])) {
            $updateParams['groupname'] = $params['groupname'];
        }

        if (!empty($updateParams)) {
            $ret = $daoGroup->updateGroup($orgId, $groupId, $updateParams);
            if (!$ret) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Update user group failed', self::CODE_SAVE_FAILED);
            }
        }

        return true;
    }

    /**
     * 删除群组
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

        // 群ID，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        $orgId   = $params['orgid'];
        $groupId = $params['groupid'];
        // 是否已验证群组是否存在
        $isVerify= !empty($params['isverify']) ? true : false;

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        if (!$isVerify) {
            // 群组必须有
            $existGroup = $daoGroup->existsGroup($orgId, $groupId);
            if (!$existGroup) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Group "' . $groupId . '" not exists', self::CODE_GROUP_NOTEXISTS);
            }
        }

        if ($daoGroup->countUser($orgId, $groupId) > 0) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Group "' . $groupId . '" user not null', self::CODE_GROUP_USER_NOT_NULL);
        }

        // 删除
        $ret = $daoGroup->deleteGroup($orgId, $groupId);
        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Delete user group failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     *
     * @param array $params
     */
    public function updateMember(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 群组ID，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        $orgId   = $params['orgid'];
        $groupId = $params['groupid'];
        $users   = $params['users'];
        // 是否已验证群组是否存在
        $isVerify= !empty($params['isverify']) ? true : false;

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        if (!$isVerify) {
            // 群组必须有
            if (!$daoGroup->existsGroup($orgId, $groupId)) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Group "' . $groupId . '" not exists', self::CODE_GROUP_NOTEXISTS);
            }
        }

        if ($daoGroup->removeUser($orgId, $groupId)) {
            foreach ($users as $userId) {
                $daoGroup->addUser($orgId, $groupId, $userId);
            }

            return ;
        }

        require_once 'Model/User/Exception.php';
        throw new Model_User_Exception('Update group member failed', self::CODE_SAVE_FAILED);
    }

    /**
     *
     * @param array $params
     */
    public function addMembers($orgId, $groupId, array $users)
    {
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        if (!$daoGroup->existsGroup($orgId, $groupId)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Group "' . $groupId . '" not exists', self::CODE_GROUP_NOTEXISTS);
        }

        foreach ($users as $userId) {
            $daoGroup->addUser($orgId, $groupId, $userId);
        }
    }

    /**
     * 添加群组成员
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

        // 群组ID，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        // 用户ID
        if (empty($params['userid'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "uid"', self::CODE_INVALID_UID);
        }

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        if (!$daoGroup->existsGroup($params['orgid'], $params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Group "' . $params['groupid'] . '" not exists', self::CODE_GROUP_NOTEXISTS);
        }

        $ret = $daoGroup->addUser($params['orgid'], $params['groupid'], $params['userid']);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Add user in group "' . $params['groupid'] . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 移除群组成员
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

        // 群组名称，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        $userId = !empty($params['userid']) ?  $params['userid'] : null;

        $ret = $daoGroup->removeUser($params['orgid'], $params['groupid'], $userId);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Remove user in group "' . $params['groupid'] . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     * 群组排序
     *
     * @param array $params
     */
    public function sort(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        // 群组名称，必须有
        if (empty($params['groupid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "groupid"', self::CODE_INVALID_GROUPID);
        }

        // 排序类型
        if (empty($params['sort'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing sort type', self::CODE_INVALID_SORTTYPE);
        }

        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        $orgId   = $params['orgid'];
        $groupId = $params['groupid'];
        $sort    =$params['sort'];

        // 群组必须有
        $existGroup = $daoGroup->existsGroup($orgId, $groupId);
        if (!$existGroup) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Group "' . $groupId . '" not exists', self::CODE_GROUP_NOTEXISTS);
        }

        $ret = $daoGroup->sortGroup($groupId, $orgId, $sort);

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Sort group "' . $groupId . '" failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }
}