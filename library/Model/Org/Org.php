<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Org.php 2826 2013-04-16 09:48:07Z chenyongfa $
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
 * 图度组织相关业务流程封装
 *
 * @category   Model
 * @package    Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Org_Org extends Model_Abstract
{

    const CODE_INVALID_ORGID = 101;
    const CODE_INVALID_PWD   = 102;
    const CODE_INVALID_UID   = 103;
    const CODE_ORG_EXISTS    = 104;
    const CODE_SAVE_FAILED   = 105;
    const CODE_ORG_NOTEXISTS = 106;
    const CODE_INVALID_ORGNAME = 107;

    /**
     * 创建组织默认参数
     *
     * @var array
     */
    protected static $_defaultOrgParams = array(
        'tsid'     => 1,
        'cosid'    => 1,
        'isactive' => 1,
        'defaultpassword' => '123456',
        'passwordlevel'   => 1,
        'ishttps' => 0
    );

    /**
     * 默认群组列表
     *
     * @var array
     */
    protected static $_defaultGroups = array(
        array('groupid' => "^all", 'groupname' => "全体人员", 'issystem' => 1)
    );

    /**
     * 默认权限组 列表
     *
     * @var array
     */
    protected static $_defaultRoles = array(
        array('roleid' => '^admin', 'rolename' => '高级管理员', 'issystem' => 1, 'islocked' => 0, 'adminlevel' => 3),
        array('roleid' => "^advanced", 'rolename' => "高级用户", 'issystem' => 1, 'islocked' => 0, 'adminlevel' => 0),
        array('roleid' => "^user", 'rolename' => "普通用户", 'issystem' => 1, 'islocked' => 0, 'adminlevel' => 0)
    );

    /**
     * 默认权限设置
     *
     * @var array
     */
    protected static $_defaultAccess = array(
        'common' => array(
            'a101' => 1,
            'a102' => 1,
            'a301' => 1,
            'a302' => 1,
            'a303' => 1,
            'a304' => 1,
            'a306' => 1,
            'a307' => 1,
            'a308' => 1,
            'a501' => 1,
            'a503' => 1,
            'a504' => 1,
            'a401' => 1,
            'a402' => 1,
            'a1001' => 1,
            'a1002' => 1,
            'a1003' => 1
        ),
        '^user' => array(
            'a502' => 1
        ),
        '^advanced' => array(
            'a305' => 1,
            'a201' => 1,
            'a202' => 1,
            'a203' => 1,
            'a204' => 1,
            'a511' => 1,
            'a512' => 1,
            'a513' => 1
        ),
        '^admin' => array(
            'a305' => 1,
            'a201' => 1,
            'a202' => 1,
            'a203' => 1,
            'a502' => 1,
            'a204' => 1,
            'a511' => 1,
            'a512' => 1,
            'a513' => 1
        )
    );

    /**
     * 默认板块
     *
     * @var array
     */
    protected static $_defaultBoards = array(
        array('boardid' => '^zone', 'boardname' => '默认分区', 'type' => 'zone'),
        array('boardid' => '^system', 'boardname' => '系统分区', 'type' => 'system'),
        array('boardid' => '^board', 'boardname' => '默认版块', 'parentid' => '^zone', 'type' => 'board')
    );

    /**
     * 创建图度组织
     *
     * 创建组织域名记录
     * 创建组织主数据
     * 创建组织信息数据
     * 添加组织主机绑定
     * 创建权限组
     * 创建用户分组
     * 创建根部门
     * 创建默认分区和版块
     *
     * @param array  $params 用户信息
     * @return void
     */
    public function create(array $params)
    {
        do {
            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg    = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);

            if (empty($params['orgid'])) {
                require_once 'Model/Org/Exception.php';
                throw new Model_Org_Exception('Missing parameter [orgid]', self::CODE_INVALID_ORGID);
            }

            $orgId = $params['orgid'];

            $orgParams = array_merge(self::$_defaultOrgParams, array('orgid' => $orgId));
            if (!empty($params['orgname'])) {
                $orgParams['orgname'] = $params['orgname'];
            }

            $orgId = $daoOrg->createOrg($orgParams);

            // 组织创建失败
            if (!$orgId) {
                require_once 'Model/Org/Exception.php';
                throw new Model_Org_Exception('Create organization data failed', self::CODE_SAVE_FAILED);
            }

            /* @var $daoOrgInfo Dao_Md_Org_Info */
            $daoOrgInfo = Tudu_Dao_Manager::getDao('Dao_Md_Org_Info', Tudu_Dao_Manager::DB_MD);
            $daoOrgInfo->create(array('orgid' => $orgId, 'entirename' => $params['orgname']));

            // 主机头
            if (!$daoOrg->addHost($orgId, $params['domain'])) {
                require_once 'Model/Org/Exception.php';
                throw new Model_Org_Exception('Create org host failed', self::CODE_SAVE_FAILED);
            }

            // 创建默认群组
            /* @var $daoGroup Dao_Md_User_Group */
            $daoGroup  = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

            // 创建群组
            foreach (self::$_defaultGroups as $group) {
                $group['orgid'] = $orgId;
                $daoGroup->createGroup($group);
            }

            // 创建默认权限组
            /* @var $daoRole Dao_Md_User_Role */
            $daoRole   = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);
            foreach (self::$_defaultRoles as $role) {
                $role['orgid'] = $orgId;
                $daoRole->createRole($role);

                // 配置权限
                $access = self::$_defaultAccess['common'];
                if (array_key_exists($role['roleid'], self::$_defaultAccess)) {
                    $access = array_merge($access, self::$_defaultAccess[$role['roleid']]);
                }

                foreach ($access as $key => $val) {
                    $key = (int) str_replace('a', '', $key);
                    $daoRole->addAccess($orgId, $role['roleid'], array(
                        'accessid' => $key,
                        'value'    => $val
                    ));
                }
            }

            /* @var $daoDepartment Dao_Md_Department_Department */
            $daoDepartment = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $deptId = $daoDepartment->createDepartment(array(
                'orgid' => $orgId,
                'deptid' => '^root',
                'deptname' => '^rootname'
            ));

            // 创建默认版块
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

            foreach (self::$_defaultBoards as $board) {
                $board['orgid'] = $orgId;
                if ($board['type'] != 'system') {
                    $board['groups'] = '^all';
                }
                if (isset($params['userid']) && isset($params['truename'])) {
                    $board['moderators'] = $params['userid'] . ' ' . $params['truename'];
                }
                $daoBoard->createBoard($board);
            }

            return true;

        } while (false);

        require_once 'Model/Org/Exception.php';
        throw new Model_Org_Exception('Create tudu org failed', self::CODE_SAVE_FAILED);
    }

    /**
     * 创建组织超级管理员
     *
     * @param array $params
     */
    public function createAdmin(array $params)
    {
        //echo 'create admin', "\n";

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);

        if (empty($params['userid'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Missing or invalid value of parameter "uid"', self::CODE_INVALID_UID);
        }

        if (empty($params['orgid'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        if (empty($params['password'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Missing or invalid value of parameter "password"', self::CODE_INVALID_PWD);
        }

        $orgId    = $params['orgid'];
        $userId   = $params['userid'];
        $password = $params['password'];
        $trueName = $params['truename'];
        $uniqueId = Dao_Md_User_User::getUniqueId($orgId, $userId);

        /* @var $daoUser Dao_Md_User_User */
        $daoUser   = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
        /* @var $daoGroup Dao_Md_User_Group */
        $daoGroup  = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);
        /* @var $daoRole Dao_Md_User_Role */
        $daoRole   = Tudu_Dao_Manager::getDao('Dao_Md_User_Role', Tudu_Dao_Manager::DB_MD);
        /* @var $daoOrg Dao_Md_Org_Org*/
        $daoOrg    = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);

        $org = $daoOrg->getOrgById($orgId);

        if (!$org) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Org id "' . $orgId . '" not exists', self::CODE_ORG_NOTEXISTS);
        }

        // 创建超级管理员用户
        $user = array(
            'orgid'    => $orgId,
            'userid'   => $userId,
            'uniqueid' => $uniqueId,
            'status'   => 1,
            'isshow'   => 1
        );
        $userInfo = array(
            'orgid'    => $orgId,
            'userid'   => $userId,
            'truename' => $trueName,
            'ismd5'    => true,
            'password' => $password
        );

        $ret = $daoUser->createUser($user);

        if (!$ret) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Create user data failed', self::CODE_SAVE_FAILED);
        }

        $ret = $daoUser->createUserInfo($userInfo);

        if (!$ret) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Create user info failed', self::CODE_SAVE_FAILED);
        }

        // 添加群组 - 全体员工
        $daoGroup->addUser($orgId, '^all', $userId);
        // 添加权限 - 管理员
        $daoRole->addUsers($orgId, '^admin', $userId);
        // 添加管理员
        $daoOrg->addAdmin($orgId, $userId, 'SA', 3);

        if (!empty($params['email'])) {
            // 绑定邮箱
            $daoUser->createEmail(array(
                'orgid'  => $orgId,
                'userid' => $userId,
                'email'  => $params['email']
            ));
        }

        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
        // 看到自己
        $daoCast->addUser($orgId, $userId, $userId);
        // 看到根部门
        $daoCast->addDepartment($orgId, $userId, '^root');

        // 创建欢迎公告
        try {
            $config = Tudu_Model::getResource('config');
            if (!empty($config['path']['data']) || !empty($config['data']['path'])) {
                $tplFile = !empty($config['path']['data']) ? $config['path']['data'] : $config['data']['path'];

                $content = @file_get_contents($tplFile . '/templates/tudu/welcome.tpl');

                if (!empty($content)) {
                    require_once 'Tudu/Deliver.php';
                    $deliver = new Tudu_Deliver(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

                    $tudu = array(
                        'orgid'  => $orgId,
                        'tuduid' => md5($orgId . '-welcome'),
                        'boardid' => '^system',
                        'uniqueid' => '^system',
                        'type' => 'notice',
                        'subject' => '欢迎使用图度工作管理系统！！',
                        'email' => 'robot@oray.com',
                        'from' => '^system 图度系统',
                        'to' => null,
                        'cc' => null,
                        'priority' => 0,
                        'privacy' => 0,
                        'issend' => 1,
                        'status' => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
                        'content' => $content,
                        'poster' => '图度系统',
                        'posterinfo' => '',
                        'lastposter' => '图度系统',
                        'lastposttime' => time(),
                        'createtime' => time(),
                        'attachment' => array()
                    );

                    $deliver->createTudu($tudu);
                    $deliver->sendTudu($tudu['tuduid'], array());

                    if (!empty($uniqueId)) {
                        $deliver->addRecipient($tudu['tuduid'], $uniqueId);
                        $deliver->addLabel($tudu['tuduid'],  $uniqueId, '^all');
                        $deliver->addLabel($tudu['tuduid'],  $uniqueId, '^i');
                        $deliver->addLabel($tudu['tuduid'],  $uniqueId, '^n');
                    }
                }

            }

        } catch (Exception $e) {}
    }

    /**
     * 激活组织
     *
     * @param string $orgId
     * @param array  $params
     */
    public function active($params)
    {
        //echo 'active', "\n";

        do {

            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);

            if (empty($params['orgid'])) {
                require_once 'Model/Org/Exception.php';
                throw new Model_Org_Exception('Invalid parameter orgid ', self::CODE_INVALID_ORGID);
            }

            $orgId = $params['orgid'];

            if (!$daoOrg->updateOrg($orgId, array('isactive' => 1))) {
                break;
            }

            return ;

        } while (false);

        require_once 'Model/Org/Exception.php';
        throw new Model_Org_Exception('Active tudu org failed', self::CODE_SAVE_FAILED);
    }

    /**
     * 企业组织信息
     *
     * @param array $params
     */
    public function info(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
        // 检查组织是否存在
        $org = $daoOrg->getOrgById($params['orgid']);
        if (!$org) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Org id "' . $params['orgid'] . '" not exists', self::CODE_ORG_NOTEXISTS);
        }

        /* @var $daoInfo Dao_Md_Org_Info */
        $daoInfo = Tudu_Dao_Manager::getDao('Dao_Md_Org_Info', Tudu_Dao_Manager::DB_MD);

        $infos = array(
            'entirename'  => $params['entirename']
            //'industry'    => $params['industry'],
            //'contact'     => $params['contact'],
            //'tel'         => $params['tel'],
            //'fax'         => $params['fax'],
            //'postcode'    => $params['postcode'],
            //'address'     => $params['address'],
            //'province'    => $params['province'],
            //'city'        => $params['city']
        );

        // 判断组织信息是否已存在
        $info = $daoInfo->getOrgInfo(array('orgid' => $params['orgid']));
        if ($info) {
            $ret = $daoInfo->update($params['orgid'], $infos);
        } else {
            $infos['orgid'] = $params['orgid'];
            $ret = $daoInfo->create($infos);
        }

        if (!$ret) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Update org info failed', self::CODE_SAVE_FAILED);
        }

        return true;
    }

    /**
     *
     * @param array $params
     */
    public function updateOrg(array $params)
    {
        // 组织ID必须有
        if (empty($params['orgid'])) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Missing or invalid value of parameter "orgid"', self::CODE_INVALID_ORGID);
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);
        // 检查组织是否存在
        $org = $daoOrg->getOrgById($params['orgid']);
        if (!$org) {
            require_once 'Model/Org/Exception.php';
            throw new Model_Org_Exception('Org id "' . $org->orgId . '" not exists', self::CODE_ORG_NOTEXISTS);
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg  = Tudu_Dao_Manager::getDao('Dao_Md_Org_Org', Tudu_Dao_Manager::DB_MD);

        $updateParams = array();

        // 组织简称
        if (!empty($params['orgname'])) {
            $updateParams['orgname'] = $params['orgname'];
        }

        // 组织简介
        if (array_key_exists('intro', $params)) {
            $updateParams['intro'] = $params['intro'];
        }

        // 组织LOGO
        if (array_key_exists('logo', $params)) {
            $updateParams['logo'] = $params['logo'];
        }

        if (!empty($updateParams)) {
            $ret = $daoOrg->updateOrg($params['orgid'], $updateParams);
            if (!$ret) {
                require_once 'Model/Org/Exception.php';
                throw new Model_Org_Exception('Update org info failed', self::CODE_SAVE_FAILED);
            }
        }

        return true;
    }
}