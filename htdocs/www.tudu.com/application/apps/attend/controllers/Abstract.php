<?php
/**
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Abstract.php 2769 2013-03-07 10:09:47Z chenyongfa $
 */
abstract class Apps_Attend_Abstract extends TuduX_App_Abstract
{

    /**
     * 当前用户角色
     *
     * @var array
     */
    protected $_roles = null;

    /**
     *
     * @var string
     */
    protected $_appId = 'attend';

    /**
     *
     * @var array
     */
    protected $_settings = array();

    /**
     * 组织部门列表
     *
     * @var array
     */
    protected $_depts = null;

    public function init()
    {
        $this->view = $this->_this->view;

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'attend'));
        $this->view->LANG  = $this->lang;
    }

    /**
     * 验证APP
     */
    public function checkApp() 
    {
        /* @var $daoApp Dao_App_App_App */
        $daoApp = Tudu_Dao_Manager::getDao('Dao_App_App_App', Tudu_Dao_Manager::DB_APP);
        $app = $daoApp->getApp(array('orgid' => $this->_user->orgId, 'appid' => $this->_appId));

        if ($app === null) {
            Oray_Function::alert($this->lang['warn_app_not_exists']);
        }
        if ($app->status == 0) {
            Oray_Function::alert($this->lang['warn_app_initialization']);
        }
        if ($app->status == 2) {
            Oray_Function::alert($this->lang['warn_app_status_stop']);
        }
        if ($app->activeTime === null || $app->activeTime > time()) {
            Oray_Function::alert($this->lang['warn_app_active_time']);
        }

        $this->_settings = $app->settings;
    }

    public function postRun()
    {
        $roles = $this->getRoles();

        $this->view->roles = $roles;
    }

    /**
     *
     */
    public function getRoles()
    {
        if (null === $this->_roles) {
            $memcache = $this->getResource('memcache');

            $roles = $memcache->get('TUDU-APP-ROLES-' . $this->_user->userName);

            if (!$roles) {
                $roles = array();

                // 读取应用权限设置
                $daoAppUser = Tudu_Dao_Manager::getDao('Dao_App_App_User', Tudu_Dao_Manager::DB_APP);

                $itemId = array_merge(array($this->_user->userName), $this->_user->groups);

                $userRoles = $daoAppUser->getAppUsers(
                    array('orgid' => $this->_user->orgId, 'appid' => $this->_appId, 'itemid' => $itemId)
                )->toArray();

                foreach ($userRoles as $role) {
                    $roles[$role['role']] = true;
                }

                // 查询是否部门负责人
                $depts = $this->getModerateDepts(false);

                if (count($depts)) {
                    $roles['sum'] = true;
                    $roles['sc']  = true;
                    $roles['moderator'] = true;
                }

                $memcache->set('TUDU-APP-ROLES-' . $this->_user->userName, $this->_roles, null, 3600);
            }

            if (!empty($roles['admin'])) {
                $roles['sum'] = true;
                $roles['sc']  = true;
                $roles['moderator'] = true;
            }

            $this->_roles = $roles;
        }

        return $this->_roles;
    }

    /**
     * 获取当前用户作为负责人的部门列表
     */
    public function getModerateDepts($isDeep = true, $idOnly = false)
    {
        $depts = $this->getDepts();

        $moderated = array();
        $tempDepth = null;
        foreach ($depts as $dept) {
            if (in_array($this->_user->userId, $dept['moderators'])) {
                $moderated[$dept['deptid']] = $idOnly ? $dept['deptid'] : $dept;
                $tempDepth   = $dept['depth'];
                continue ;
            }

            // 获取下级所有
            if ($isDeep && null !== $tempDepth && $tempDepth < $dept['depth']) {
                $moderated[$dept['deptid']] = $idOnly ? $dept['deptid'] : $dept;
            }

            if (null !== $tempDepth && $tempDepth >= $dept['depth']) {
                $tempDepth = null;
            }
        }

        return $moderated;
    }

    /**
     * 获取当前用户作为考勤应用权限用户的部门列表
     */
    public function getRoleDepts($isDeep = true, $idOnly = false)
    {
        $depts = $this->getDepts();

        $moderated = array();
        if (!$this->_user->deptId) {
            return $moderated;
        }

        $tempDepth = null;
        foreach ($depts as $dept) {
            if ($this->_user->deptId == $dept['deptid']) {
                $moderated[$dept['deptid']] = $idOnly ? $dept['deptid'] : $dept;
                $tempDepth   = $dept['depth'];
                continue ;
            }

            // 获取下级所有
            if ($isDeep && null !== $tempDepth && $tempDepth < $dept['depth']) {
                $moderated[$dept['deptid']] = $idOnly ? $dept['deptid'] : $dept;
            }

            if (null !== $tempDepth && $tempDepth >= $dept['depth']) {
                $tempDepth = null;
            }
        }

        return $moderated;
    }

    /**
     * 获取组织部门列表
     *
     */
    public function getDepts()
    {
        if (null === $this->_depts) {
            $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $depts = $daoDept->getDepartments(array('orgid' => $this->_user->orgId));

            $this->_depts = $depts->toArray('deptid');

            unset($this->_depts['^root']);
        }

        return $this->_depts;
    }

    /**
     * 格式化标签数据
     *
     * @param $params
     * @param $smarty
     */
    public function formatLabels(array $params, &$smarty)
    {
        $labels = $params['labels'];

        foreach ($labels as $label) {
            if (isset($params['issystem']) && false === $params['issystem'] && $label['issystem']) {
                continue ;
            }

            // 过滤“所有图度”与“已审批”标签
            if ($label['labelalias'] == 'all' || $label['labelalias'] == 'reviewed') {
                continue ;
            }
            // 处理标签名称
            if ($label['issystem']) {
                $labelname = $this->lang['label_' . $label['labelalias']];
            } else {
                $labelname = $label['labelalias'];
            }

            $item = array(
                'labelname' => $labelname,
                'labelid' => $label['labelid'],
                'labelalias' => $label['labelalias'],
                'totalnum' => $label['totalnum'],
                'unreadnum' => $label['unreadnum'],
                'isshow' => $label['isshow'],
                'issystem' => $label['issystem'],
                'bgcolor' => $label['bgcolor'],
                'ordernum' => $label['ordernum'],
            );

            if (!empty($params['key'])) {
                $ret[$item[$params['key']]] = $item;
            } else {
                $ret[] = $item;
            }
        }

        return json_encode($ret);
    }
}