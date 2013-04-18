<?php
/**
 * Model User Group
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Department
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Department.php 2446 2012-12-03 10:03:57Z cutecube $
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
 * @package    Model_Department
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 */
class Model_Department_Department extends Model_Abstract
{

    /**
     *
     * @var string
     */
    const DEPT_ID_ROOT = '^root';

    /**
     *
     * @var int
     */
    const CODE_INVALID_ORGID    = 101;
    const CODE_INVALID_DEPTNAME = 102;
    const CODE_INVALID_DEPTID   = 103;
    const CODE_INVALID_PARENT   = 104;

    const CODE_SAVE_FAILED      = 105;

    const CODE_PARENT_NOTEXISTS     = 110;
    const CODE_DEPARTMENT_NOTEXISTS = 111;
    const CODE_DELETE_NOTNULL       = 112; // 删除非空部门
    const CODE_DELETE_PARENT        = 113; // 删除带子部门的

    /**
     * 创建部门
     *
     * @param array $params
     * @return void
     */
    public function create(array $params)
    {
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        $parentId = !empty($params['parentid']) ? $params['parentid'] : self::DEPT_ID_ROOT;

        if (empty($params['orgid'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        if (empty($params['deptname'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "deptname"', self::CODE_INVALID_DEPTNAME);
        }

        $orgId    = $params['orgid'];
        $deptName = $params['deptname'];

        if (!$daoDept->existsDepartment($orgId, $parentId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Parent department not exists', self::CODE_PARENT_NOTEXISTS);
        }

        $deptId = $daoDept->getDeptId($orgId);
        $params['deptid'] = $deptId;

        $array = array(
            'orgid'    => $orgId,
            'deptid'   => $deptId,
            'deptname' => $deptName,
            'parentid' => $parentId,
            'ordernum' => $daoDept->getMaxOrderNum($orgId, $parentId) + 1
        );

        if (!$daoDept->createDepartment($array)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Save department data failed', self::CODE_SAVE_FAILED);
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_CREATE,
                null,
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId)),
                array('deptname' => $deptName)
            );
        }

        // 发送通知
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    'user',
                    implode(':', array($orgId, $deptId))
                ));

                $httpsqs->put($data);
            }
        }

        // 清除缓存
        try {
            $memcache = Tudu_Model::getResource('memcache');
            $memcache->set('TUDU-CAST-UPDATE-' . $orgId, time(), 0);
        } catch (Exception $e) {}
    }

    /**
     * 更新部门
     *
     * @param array $params
     */
    public function update(array $params)
    {
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        if (empty($params['deptid'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing parameter "deptid"', self::CODE_INVALID_DEPTID);
        }

        if (empty($params['orgid'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        $orgId  = $params['orgid'];
        $deptId = $params['deptid'];

        // 没有任何更新
        if (empty($params['deptname']) && empty($params['parentid'])) {
            return ;
        }

        $department = $daoDept->getDepartment(array('orgid' => $orgId, 'deptid' => $deptId));

        if (!$department) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Department to update is not exists', self::CODE_DEPARTMENT_NOTEXISTS);
        }

        if (!empty($params['parentid'])) {
            $parentId = $params['parentid'];
            // 检查父部门是否存在
            if (!$daoDept->existsDepartment($orgId, $parentId)) {
                require_once 'Model/Department/Exception.php';
                throw new Model_Department_Exception('Parent department not exists', self::CODE_PARENT_NOTEXISTS);
            }

            // 指定当前部门为父部门
            if ($parentId == $department->deptId) {
                require_once 'Model/Department/Exception.php';
                throw new Model_Department_Exception('Could not specific parent of itself', self::CODE_INVALID_PARENT);
            }

            $departments = $daoDept->getDepartments(array('orgid' => $orgId), null, array('ordernum' => 'DESC'))->toArray();
            $depth       = null;
            // 检查父部门循环引用的情况
            foreach ($departments as $dept) {
                if ($dept['deptid'] == $department->deptId) {
                    $depth = $dept['depth'];
                    continue ;
                }

                if (null !== $depth && $depth >= $dept['depth']) {
                    break;
                }

                if (null !== $depth && $dept['deptid'] == $parentId) {
                    require_once 'Model/Department/Exception.php';
                    throw new Model_Department_Exception('Could not specific parent of itself', self::CODE_INVALID_PARENT);
                }
            }
        }

        $array = array();
        $detail = array();

        if (!empty($params['deptname'])) {
            $array['deptname'] = $params['deptname'];
            $detail['deptname'] = $params['deptname'];
        }

        $array['parentid'] = !empty($params['parentid']) ? $params['parentid'] : self::DEPT_ID_ROOT;

        if (!$daoDept->updateDepartment($orgId, $deptId, $array)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Save department data failed', self::CODE_SAVE_FAILED);
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_UPDATE,
                'dept',
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId)),
                $detail
            );
        }

        // 发送通知
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    'user',
                    implode(':', array($orgId, $deptId))
                ));

                $httpsqs->put($data);
            }
        }
    }

    /**
     *
     * @param string $orgId
     * @param string $deptId
     */
    public function delete(array $params)
    {
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        if (empty($params['deptid'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing parameter "deptid"', self::CODE_INVALID_DEPTID);
        }

        if (empty($params['orgid'])) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        $orgId  = $params['orgid'];
        $deptId = $params['deptid'];

        $department = $daoDept->getDepartment(array('orgid' => $orgId, 'deptid' => $deptId));
        if (!$department) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Parent department not exists', self::CODE_DEPARTMENT_NOTEXISTS);
        }

        if ($daoDept->getUserCount($orgId, $deptId) > 0) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Could not delete department which has user', self::CODE_DELETE_NOTNULL);
        }

        if ($daoDept->getChildCount($orgId, $deptId) > 0) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Could not delete department which has child', self::CODE_DELETE_PARENT);
        }

        if (!$daoDept->deleteDepartment($orgId, $deptId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Delete department failed', self::CODE_SAVE_FAILED);
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_DELETE,
                null,
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId)),
                array('deptname' => $department->deptName)
            );
        }

        // 发送通知
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    'user',
                    implode(':', array($orgId, $deptId))
                ));

                $httpsqs->put($data);
            }
        }
    }

    /**
     * 排序
     *
     *
     */
    public function sort($orgId, $deptId, $type, array $params)
    {
        if (empty($deptId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing parameter "deptid"', self::CODE_INVALID_DEPTID);
        }

        if (empty($orgId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        if (!$daoDept->sortDepartment($orgId, $deptId, $type)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Save department data failed', self::CODE_SAVE_FAILED);
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_UPDATE,
                'dept',
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId))
            );
        }

        // 发送通知
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_DEPT,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    'dept',
                    implode(':', array($orgId, $deptId))
                ));

                $httpsqs->put($data);
            }
        }
    }

    /**
     * 更新部门负责人
     *
     * @param string $deptId
     * @param array $moderators
     * @return void
     */
    public function updateModerator($orgId, $deptId, array $moderators, array $params)
    {
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        if (empty($deptId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing parameter "deptid"', self::CODE_INVALID_DEPTID);
        }

        if (empty($orgId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        $department = $daoDept->getDepartment(array('orgid' => $orgId, 'deptid' => $deptId));

        if (!$department) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Parent department not exists', self::CODE_DEPARTMENT_NOTEXISTS);
        }

        if (!empty($moderators)) {
            $users = array();
            foreach ($moderators as $userId) {
                $users[] = $userId;
            }
            $moderators = implode(',', $users);
        } else {
            $moderators = null;
        }

        if (!$daoDept->updateDepartment($orgId, $deptId, array('moderators' => $moderators))) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Update department failed', self::CODE_SAVE_FAILED);
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_UPDATE,
                'moderator',
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId)),
                array('deptname' => $department->deptName)
            );
        }
    }

    /**
     * 部门成员
     *
     * @param string $orgId
     * @param string $deptId
     * @param array  $member
     * @param array  $params
     */
    public function updateMember($orgId, $deptId, array $member, array $params)
    {
        /* @var $daoDept Dao_Md_Department_Department */
        $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

        if (empty($deptId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing parameter "deptid"', self::CODE_INVALID_DEPTID);
        }

        if (empty($orgId)) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Invalid or missing params "orgid"', self::CODE_INVALID_ORGID);
        }

        $department = $daoDept->getDepartment(array('orgid' => $orgId, 'deptid' => $deptId));

        if (!$department) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Parent department not exists', self::CODE_DEPARTMENT_NOTEXISTS);
        }

        $ret = $daoDept->removeUser($orgId, $deptId);
        if (!$ret) {
            require_once 'Model/Department/Exception.php';
            throw new Model_Department_Exception('Update department failed', self::CODE_SAVE_FAILED);
        }

        if (!empty($member)) {
            $ret = $daoDept->addUser($orgId, $deptId, $member);
            if (!$ret) {
                require_once 'Model/Department/Exception.php';
                throw new Model_Department_Exception('Update department failed', self::CODE_SAVE_FAILED);
            }

            /* @var $daoCast Dao_Md_User_Cast */
            $daoCast   = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
            foreach ($member as $userId) {
                $daoCast->updateCastDept($orgId, $userId, $deptId);
            }
        }

        // 添加后台操作日志
        if (!empty($params['operator']) && !empty($params['clientip'])) {
            $params['local'] = empty($params['local']) ? null : $params['local'];
            $this->_createLog(
                Dao_Md_Log_Oplog::MODULE_DEPT,
                Dao_Md_Log_Oplog::OPERATION_UPDATE,
                'user',
                array('orgid' => $orgId, 'operator' => $params['operator'], 'clientip' => $params['clientip'], 'local' => $params['local']),
                implode(':', array($orgId, $deptId)),
                array('deptname' => $department->deptName)
            );
        }

        // 发送通知
        if (Tudu_Model::hasResource(Tudu_Model::RESOURCE_CONFIG)) {
            $config = Tudu_Model::getResource(Tudu_Model::RESOURCE_CONFIG);

            if ($config['httpsqs']) {
                $options = $config['httpsqs'];
                $httpsqs = new Oray_Httpsqs($options['host'], $options['port'], $options['charset'], $options['name']);

                $data = implode(' ', array(
                    Dao_Md_Log_Oplog::MODULE_USER,
                    Dao_Md_Log_Oplog::OPERATION_UPDATE,
                    null,
                    implode(':', array($orgId, $deptId))
                ));
                $ret = $httpsqs->put($data);
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