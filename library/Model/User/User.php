<?php
/**
 * Model User User
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_User
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: User.php 2788 2013-03-20 10:58:18Z chenyongfa $
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
class Model_User_User extends Model_Abstract
{
    const CODE_INVALID_ORGID      = 101;
    const CODE_INVALID_UID        = 102;
    const CODE_USER_NOTEXISTS     = 103;
    const CODE_SAVE_FAILED        = 104;
    const CODE_TOO_MUCH_USER      = 105;
    const CODE_MISSING_UID        = 106;
    const CODE_INVALID_PWD        = 107;
    const CODE_INVALID_USERNAME   = 108;
    const CODE_INVALID_DOMAINID   = 109;
    const CODE_USER_EXISTS        = 110;
    const CODE_NOT_ENOUGH_NDSPACE = 111;
    const CODE_INVALID_BIRTHDAY   = 112;
    const CODE_INVALID_DEPTNAME   = 113;
    const CODE_DEPT_PARENT_NOTEXISTS = 114;
    const CODE_DEPT_EXISTS        = 115;
    const CODE_SAVE_DEPT_FAILED   = 116;
    const CODE_MISSING_UNID       = 117;
    const CODE_LESS_NDSPACE       = 118;
    const CODE_NOT_MODIFY_PWD     = 119;
    const CODE_DELETE_SUPER_ADMIN = 120;
    const CODE_INVALID_NDQUOTA    = 121;
    const CODE_INVALID_EMAIL      = 122;

    /**
     * 创建用户
     */
    public function create(array $params)
    {
        $isImport = !empty($params['import']) ? true : false;

        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }
        $orgId = $params['orgid'];

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        if ($isImport) {
            if (!empty($params['exist-parentid'])) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Parent dept is not exists', self::CODE_DEPT_PARENT_NOTEXISTS);
            }
            if (!empty($params['deptid']) && $params['deptid'] == '^new') {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Create dept failed', self::CODE_SAVE_DEPT_FAILED);
            }

            $domains = $daoOrg->getDomains($orgId);
            $domains = $domains[0];
            $params['domainid'] = (int) $domains['domainid'];
        }

        // 参数错误[domainid]
        if (empty($params['domainid']) || !$daoOrg->existDomain((int) $params['domainid'], $orgId)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "domainid"', self::CODE_INVALID_DOMAINID);
        }

        // 读取组织信息
        $org = $daoOrg->getOrg(array('orgid' => $orgId));
        if ($isImport) {
            $params['password'] = $org->defaultPassword;
        }

        // 组织用户是否已达到上限
        if ($org->maxUsers && $org->maxUsers <= $daoOrg->getUserCount($orgId)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('This organization is too much users', self::CODE_TOO_MUCH_USER);
        }

        // 用户名
        if (empty($params['userid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing the value of parameter "userid"', self::CODE_MISSING_UID);
        }
        $userId   = $params['userid'];
        $address  = $userId . '@' . $orgId;
        $uniqueId = Dao_Md_User_User::getUniqueId($orgId, $userId);

        // 用户名格式验证
        if (!Oray_Function::isDomainStr($userId)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Invalid value of parameter "userid"', self::CODE_INVALID_UID);
        }

        // 密码
        if (empty($params['password'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "password"', self::CODE_INVALID_PWD);
        }

        // 用户真实姓名
        if (empty($params['truename'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "truename"', self::CODE_INVALID_USERNAME);
        }

        // 邮箱格式有误
        if (!empty($params['email']) && !Oray_Function::isEmail($params['email'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Invalid value of parameter "email"', self::CODE_INVALID_EMAIL);
        }

        // 用户已存在
        if ($daoUser->existsUser($orgId, $userId)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('User is exists in this organization', self::CODE_USER_EXISTS);
        }

        // 准备用户参数
        $user = array(
            'orgid'          => $orgId,
            'userid'         => $userId,
            'uniqueid'       => $uniqueId,
            'status'         => isset($params['status']) ? (int) $params['status'] : 1,
            'domainid'       => (int) $params['domainid'],
            'deptid'         => isset($params['deptid']) ? $params['deptid'] : null,
            'isshow'         => !empty($params['isshow']) ? 1 : 0,
            'ordernum'       => isset($params['ordernum']) ? (int) $params['ordernum'] : 0,
            'initpassword'   => 1,
            'lastupdatetime' => time()
        );
        $userInfo = array(
            'orgid'    => $orgId,
            'userid'   => $userId,
            'truename' => $params['truename'],
            'password' => $params['password'],
            'gender'   => (int) $params['gender']
        );
        if (isset($params['position'])) {
            $userInfo['position'] = $params['position'];
        }
        if (isset($params['tel'])) {
            $userInfo['tel']      = $params['tel'];
        }
        if (isset($params['mobile'])) {
            $userInfo['mobile']   = $params['mobile'];
        }
        if (isset($params['email'])) {
            $userInfo['email']   = $params['email'];
        }

        // 网盘空间
        if (!empty($params['maxndquota'])) {
            $ndQuota = (float) $params['maxndquota'] * 1000000;
            if ($org->maxNdQuota * 1000000 - $daoOrg->getUsedNetdiskQuota($orgId) < $ndQuota) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('This organization has not enough netdisk space', self::CODE_NOT_ENOUGH_NDSPACE);
            }
            $user['maxndquota'] = $ndQuota;
        }

        // 无效的出生日期
        if (!empty($params['birthday'])) {
            if (false === $params['birthday']) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Missing or invalid value of parameter "birthday"', self::CODE_INVALID_BIRTHDAY);
            }

            $userInfo['birthday'] = $params['birthday'];
        }

        if (!Oray_Function::isByte($params['truename'])) {
            require_once 'Tudu/Pinyin.php';
            $userInfo['pinyin'] = Tudu_Pinyin::parse($params['truename'], true);
        }

        if (!empty($params['idnumber'])) {
            $userInfo['idnumber'] = $params['idnumber'];
        }

        if (!empty($params['nick'])) {
            $userInfo['nick'] = $params['nick'];
        }

        // 创建用户
        if (!$daoUser->createUser($user)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Create user failed', self::CODE_SAVE_FAILED);
        }

        // 创建用户数据
        if (!$daoUser->createUserInfo($userInfo)) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Create user info failed', self::CODE_SAVE_FAILED);
        }

        // 群组
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);
        $groups   = !empty($params['groupid']) ? $params['groupid'] : array();
        foreach ($groups as $groupId) {
            $daoGroup->addUser($orgId, $groupId, $userId);
        }

        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);
        $roles   = !empty($params['roleid']) ? $params['roleid'] : array();
        // 权限组
        foreach ($roles as $roleId) {
            $daoRole->addUsers($orgId, $roleId, $userId);
        }

        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast   = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
        $castDepts = !empty($params['castdept']) ? $params['castdept'] : array();
        $castUsers = !empty($params['castuser']) ? $params['castuser'] : array();

        // 添加不可见部门
        foreach ($castDepts as $deptId) {
            if (!trim($deptId) || $deptId == '^root') {
                continue ;
            }
            $daoCast->hideDepartment($orgId, $userId, $deptId);
        }

        // 添加不可见用户
        foreach ($castUsers as $uId) {
            if (!trim($uId) || $uId == $userId) {
                continue ;
            }
            $daoCast->hideUser($orgId, $userId, $uId);
        }
        $daoCast->updateDepartment($orgId, $userId, $user['deptid']);

        // 修改企业默认密码
        if ($org->defaultPassword != $params['password']) {
            $daoOrg->updateOrg($orgId, array('defaultpassword' => $params['password']));
        }

        // 发送通知,插入消息队列
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_CREATE,
                    'user',
                    implode(':', array($orgId, $address, $uniqueId, $params['truename']))
                ));

                $httpsqs->put($data);
            }
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_USER,
                Dao_Md_Log_Oplog::OPERATION_CREATE,
                null,
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $address, $uniqueId)),
                array('truename' => $params['truename'], 'account' => $address)
            );
        }
    }

    /**
     * 更新用户
     */
    public function update(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }
        $orgId = $params['orgid'];

        $edit = array(
            'truename' => true,
            'password' => true,
            'dept'     => true,
            'status'   => true,
            'role'     => true,
            'group'    => true,
            'cast'     => true,
            'netdisk'  => true,
            'email'    => true
        );

        if (!empty($params['edit'])) {
            $edit = $params['edit'];
        }

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

        // 用户名
        if (empty($params['userid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing the value of parameter "userid"', self::CODE_MISSING_UID);
        }

        $userId = $params['userid'];
        $user   = $daoUser->getUser(array('orgid' => $orgId, 'userid' => $userId));
        $userIf = $daoUser->getUserInfo(array('orgid' => $orgId, 'userid' => $userId));

        // 用户不存在
        if (null === $user) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing the value of parameter "userid"', self::CODE_USER_NOTEXISTS);
        }

        // 用户真实姓名
        if ($edit['truename'] && empty($params['truename'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "truename"', self::CODE_INVALID_USERNAME);
        }

        // 邮箱格式有误
        if ($edit['email'] && !empty($params['email']) && !Oray_Function::isEmail($params['email'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Invalid value of parameter "email"', self::CODE_INVALID_EMAIL);
        }

        $userParam = array();
        $userInfo  = array();

        if ($edit['status']) {
            $userParam['status'] = isset($params['status']) ? (int) $params['status'] : 1;
        }

        if ($edit['dept']) {
            $userParam['deptid'] = isset($params['deptid']) ? $params['deptid'] : null;
        }

        if (isset($params['ordernum'])) {
            $userParam['ordernum'] = (int) $params['ordernum'];
        }

        $userParam['lastupdatetime'] = time();

        if (isset($params['isshow'])) {
            $userParam['isshow'] = $params['isshow'];
        }

        if (isset($params['truename'])) {
            $userInfo['truename'] = $params['truename'];
        }
        if (isset($params['position'])) {
            $userInfo['position'] = $params['position'];
        }
        if (isset($params['gender'])) {
            $userInfo['gender']   = (int) $params['gender'];
        }
        if (isset($params['tel'])) {
            $userInfo['tel']      = $params['tel'];
        }
        if (isset($params['mobile'])) {
            $userInfo['mobile']   = $params['mobile'];
        }
        if (isset($params['email'])) {
            $userInfo['email']   = $params['email'];
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);

        // 读取组织信息
        $org = $daoOrg->getOrg(array('orgid' => $orgId));

        // 网盘空间
        if ($edit['netdisk'] && !empty($params['maxndquota'])) {
            $ndQuota = (float) $params['maxndquota'] * 1000000;
            if ($ndQuota != $user->maxNdQuota) {
                if ($org->maxNdQuota * 1000000 - ($daoOrg->getUsedNetdiskQuota($orgId) - $user->maxNdQuota) < $ndQuota) {
                    require_once 'Model/User/Exception.php';
                    throw new Model_User_Exception('This organization has not enough netdisk space', self::CODE_NOT_ENOUGH_NDSPACE);
                }

                /* @var $daoFolder Dao_Td_Netdisk_Folder */
                $daoFolder = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_Folder', Tudu_Dao_Manager::DB_TS);

                $folderRoot = $daoFolder->getFolder(array('uniqueid' => $user->uniqueId, 'folderid' => '^root'));
                if (null !== $folderRoot && $ndQuota < $folderRoot->folderSize) {
                    require_once 'Model/User/Exception.php';
                    throw new Model_User_Exception('This netdisk space can not less than the used netdisk space', self::CODE_LESS_NDSPACE);
                }

                // 更新用户网盘跟文件夹空间
                if (null !== $folderRoot) {
                    $daoFolder->updateFolder($user->uniqueId, '^root', array('maxquota' => $ndQuota));
                }

                $userParam['maxndquota'] = $ndQuota;
            }
        }

        // 无效的出生日期
        if (!empty($params['birthday'])) {
            if (false === $params['birthday']) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Missing or invalid value of parameter "birthday"', self::CODE_INVALID_BIRTHDAY);
            }

            $userInfo['birthday'] = $params['birthday'];
        }

        if ($edit['truename'] && !Oray_Function::isByte($params['truename'])) {
            require_once 'Tudu/Pinyin.php';
            $userInfo['pinyin'] = Tudu_Pinyin::parse($params['truename'], true);
        }

        if (!empty($params['nick'])) {
            $userInfo['nick'] = $params['nick'];
        }

        if (!empty($params['idnumber'])) {
            $userInfo['idnumber'] = $params['idnumber'];
        }

        if ($edit['password'] && !empty($params['password'])) {
            if ($daoUser->isAdmin($orgId, $userId)) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Can not modify the administrator password', self::CODE_NOT_MODIFY_PWD);
            }

            $userParam['initpassword'] = 1;
            $userInfo['password'] = $params['password'];
        }

        // 用户头像
        if (!empty($params['avatars'])) {
            $userInfo['avatartype'] = $params['avatartype'];
            $userInfo['avatars']    = $params['avatars'];

            /* @var $daoImContact Dao_Im_Contact_Contact */
            $daoImContact = Tudu_Dao_Manager::getDao('Dao_Im_Contact_Contact', Tudu_Dao_Manager::DB_IM);

            // 需要更新im自定义联系人表的updatetime
            // im通过更新时间判断是否需要获取用户头像
            $daoImContact->updateUser($userId . '@' . $orgId, array('updatetime' => time()));
        }

        // 更新用户数据
        if (!empty($userParam)) {
            if (!$daoUser->updateUser($orgId, $user->userId, $userParam)) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Update user failed', self::CODE_SAVE_FAILED);
            }
        }

        if (!empty($userInfo)) {
            if (!$daoUser->updateUserInfo($orgId, $user->userId, $userInfo)) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Update user info failed', self::CODE_SAVE_FAILED);
            }
        }

        // 群组
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);
        if ($edit['group']) {
            $groups = !empty($params['groupid']) ? $params['groupid'] : array();

            $daoUser->removeGroups($user->orgId, $user->userId);
            foreach ($groups as $groupId) {
                $daoGroup->addUser($orgId, $groupId, $userId);
            }
        }

        // 权限组
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);
        if ($edit['role']) {
            $roles = !empty($params['roleid']) ? $params['roleid'] : array();

            $daoUser->removeRoles($user->orgId, $user->userId);
            foreach ($roles as $roleId) {
                $daoRole->addUsers($orgId, $roleId, $userId);
            }
        }

        // 组织架构
        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast   = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
        if ($edit['cast']) {
            $castDepts = !empty($params['castdept']) ? $params['castdept'] : array();
            $castUsers = !empty($params['castuser']) ? $params['castuser'] : array();

            // 清除组织架构
            $daoCast->clear($orgId, $userId);
            // 隐藏部门
            foreach ($castDepts as $dept) {
                if (!trim($dept) || $dept == '^root' || $dept == $userParam['deptid']) {
                    continue ;
                }
                $daoCast->hideDepartment($orgId, $userId, $dept);
            }

            // 隐藏用户
            foreach ($castUsers as $uId) {
                if (!$uId || $uId == $userId) {
                    continue ;
                }
                $daoCast->hideUser($orgId, $userId, $uId);
            }

            // 更换部门
            if ($user->deptId != $userParam['deptid']) {
                $daoCast->updateDepartment($orgId, $userId, $userParam['deptid']);
            }
        }

        // 修改企业默认密码
        if ($edit['password'] && !empty($params['password']) && $org->defaultPassword != $params['password']) {
            $daoOrg->updateOrg($orgId, array('defaultpassword' => $params['password']));
        }

        // 发送通知,插入消息队列
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    null,
                    implode(':', array($orgId, $user->userName, $user->uniqueId, ''))
                ));

                $httpsqs->put($data);
            }
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $trueName = $edit['truename'] ? $params['truename'] : $userIf->trueName;
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_USER,
                Dao_Md_Log_Oplog::OPERATION_UPDATE,
                null,
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $user->userName, $user->uniqueId)),
                array('truename' => $trueName, 'account' => $user->userName)
            );
        }
    }

    /**
     * 删除用户
     */
    public function delete(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }
        $orgId = $params['orgid'];

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

        // 用户名
        if (empty($params['userid'])) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Missing the value of parameter "userid"', self::CODE_MISSING_UID);
        }

        $userIds = is_array($params['userid']) ? $params['userid'] : (array) $params['userid'];
        $ret     = true;
        $uniqueIds = array();

        foreach ($userIds as $userId) {
            $cuser    = $daoUser->getUser(array('orgid' => $orgId, 'userid' => $userId));
            $infouser = $daoUser->getUserInfo(array('orgid' => $orgId, 'userid' => $userId));

            // 用户已不存在
            if (null == $cuser) {
                continue;
            }

            // 是否超级管理员
            if ($daoUser->isAdmin($orgId, $userId)) {
                require_once 'Model/User/Exception.php';
                throw new Model_User_Exception('Can not delete super administrator "'.$userId.'@'.$orgId.'"', self::CODE_DELETE_SUPER_ADMIN);
            }

            if (!$daoUser->deleteUser($orgId, $userId)) {
                $ret = false;
                continue;
            }

            $uniqueIds[] = $cuser->uniqueId;

            // 添加操作日志
            if (!empty($params['operator']) && !empty($params['clientip'])) {
                $params['local'] = empty($params['local']) ? null : $params['local'];
                $this->_createLog(
                    Dao_Md_Log_Oplog::MODULE_USER,
                    Dao_Md_Log_Oplog::OPERATION_DELETE,
                    null,
                    array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                    implode(':', array($cuser->orgId, $cuser->userName, $cuser->uniqueId)),
                    array('truename' => $infouser->trueName, 'account' => $cuser->userName)
                );
            }
        }

        if (!$ret) {
            require_once 'Model/User/Exception.php';
            throw new Model_User_Exception('Delete user failed', self::CODE_SAVE_FAILED);
        }

        // 发送通知,插入消息队列
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_DELETE,
                    null,
                    implode(':', array($orgId, implode(',', $uniqueIds)))
                ));

                $httpsqs->put($data);
            }
        }
    }

    /**
     * 创建管理日志
     *
     * @param string $module
     * @param string $action
     * @param string $subAction
     * @param string $description
     * @return int
     */
     protected function _createLog($module, $action, $subAction = null, $params = null, $target = null, array $detail = null)
     {
         if (null !== $detail) {
            $detail = serialize($detail);
         }

         /* @var $daoLog Dao_Md_Log_Oplog */
         $daoLog = Tudu_Dao_Manager::getDao('Dao_Md_Log_Oplog', Tudu_Dao_Manager::DB_MD);

         $ret = $daoLog->createAdminLog(array(
             'orgid'     => $params['orgid'],
             'userid'    => $params['operator'],
             'ip'        => $params['clientip'],
             'module'    => $module,
             'action'    => $action,
             'subaction' => $subAction,
             'target'    => $target,
             'local'     => $params['local'],
             'detail'    => $detail
         ));
     }
}