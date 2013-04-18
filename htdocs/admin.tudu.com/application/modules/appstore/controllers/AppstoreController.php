<?php
/**
 * 增值应用管理控制器
 *
 * LICENSE
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: AppstoreController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Appstore_AppstoreController extends TuduX_Controller_Admin
{
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'appstore'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('install', 'delete'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 增值应用主页（应用列表）
     */
    public function indexAction()
    {
        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));
        $apps = $daoApp->getAppPage(array('orgid' => $this->_orgId), 'lastupdatetime DESC, createtime DESC', 1, 10);

        $this->view->apps = $apps->toArray();
    }

    /**
     * 本组织已安装的应用列表
     */
    public function listAction()
    {
        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));
        $apps = $daoApp->getAppPage(array('orgid' => $this->_orgId, 'installed' => true), 'lastupdatetime DESC, createtime DESC', 1, 10);

        $this->view->apps = $apps->toArray();
    }

    /**
     * 获取应用列表
     */
    public function loadAppsAction()
    {
        $isInstall = $this->_request->getQuery('isinstall');
        $query     = $this->_request->getQuery();

        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));

        // 已安装应用列表
        if ($isInstall) {
            $apps = $daoApp->getAppPage(array('orgid' => $this->_orgId, 'installed' => true), 'lastupdatetime DESC, createtime DESC', $query['page'], 10)->toArray();
        // 应用列表
        } else {
            $apps = $daoApp->getAppPage(array('orgid' => $this->_orgId), 'lastupdatetime DESC, createtime DESC', $query['page'], 10)->toArray();
        }

        return $this->json(true, null, !empty($apps) ? $apps : null);
    }

    /**
     * 应用介绍信息
     */
    public function appInfoAction() {
        $appId = str_replace('_', '.', $this->_request->getQuery('appid'));

        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));
        $app = $daoApp->getApp(array('orgid' => $this->_orgId, 'appid' => $appId));
        $appAttach = $daoApp->getAppAttachs(array('appid' => $appId));
        $appPermissions = $daoApp->getAppPermissions($appId);

        $data = array(
            'app'         => $this->formatApp($app->toArray()),
            'attach'      => $appAttach,
            'permissions' => $this->formatPermission($appPermissions)
        );

        return $this->json(true, null, $data);
    }

    /**
     * 安装应用
     *
     * @params appid
     * @return json
     */
    public function installAction()
    {
        $appId = $this->_request->getPost('appid');

        // 检查参数是否存在
        if (!$appId) {
            return $this->json(false, '参数错误[appid]');
        }

        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));

        // 检查应用是否存在
        $app = $daoApp->getApp(array('orgid' => $this->_orgId, 'appid' => $appId));
        if (!$app) {
            return $this->json(false, '应用不存在或已被删除');
        }

        // 安装应用
        $ret = $daoApp->installApp(array(
            'orgid' => $this->_orgId,
            'appid' => $appId
        ));

        if (!$ret) {
            return $this->json(false, sprintf('%s 安装失败', $app->appName));
        }

        return $this->json(true, sprintf('%s 安装成功', $app->appName), array('url' => $app->url));
    }

    /**
     * 删除应该
     *
     * @params appid
     * @return json
     */
    public function deleteAction()
    {
        $appId = $this->_request->getPost('appid');

        // 检查参数是否存在
        if (!$appId) {
            return $this->json(false, '参数错误[appid]');
        }

        /* @var $daoApp Dao_App_App_App */
        $daoApp = $this->getDao('Dao_App_App_App', $this->_multidb->getDb('app'));
        $app = $daoApp->getApp(array('orgid' => $this->_orgId, 'appid' => $appId));
        if (!$app) {
            $app->appName = null;
        }

        // 安装应用
        $ret = $daoApp->deleteApp($this->_orgId, $appId);

        if (!$ret) {
            return $this->json(false, sprintf('%s 删除失败', $app->appName));
        }

        return $this->json(true, sprintf('%s 删除成功', $app->appName));
    }

    /**
     * 格式化应用介绍的数据
     *
     * @param array $data
     * @return array
     */
    private function formatApp(array $data)
    {
        if (empty($data)) {
            return null;
        }

        $ret = array();
        $lang = $this->lang['languages'];
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'lastupdatetime':
                    $ret[$key] = date('Y/m/d', $val);
                    break;
                case 'languages':
                    $ret[$key] = isset($lang[$val]) ? $lang[$val] : '';
                    break;
                default:
                    $ret[$key] = $val;
            }
        }

        return $ret;
    }

    /**
     * 格式化应用可调用的数据
     * 模板
     *
     * @param array $data
     * @return array
     */
    private function formatPermission($datas)
    {
        if (empty($datas)) {
            return null;
        }

        $rets = array();
        $ret = array();
        $lang = $this->lang['permissions'];
        foreach ($datas as $data) {
            foreach ($data as $key => $val) {
                switch ($key) {
                    case 'permission':
                        $ret[$key] = $lang[$val];
                        break;
                    default:
                        $ret[$key] = $val;
                }
            }
            $rets[] = $ret;
        }

        return $rets;
    }
}