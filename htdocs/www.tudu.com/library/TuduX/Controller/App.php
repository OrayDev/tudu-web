<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_Dispatcher
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: App.php 1387 2011-12-14 02:07:40Z web_op $
 */

/**
 * 图度APP基础控制器
 *
 * @author CuTe_CuBe
 *
 */
abstract class TuduX_Controller_App extends TuduX_Controller_Base
{

    /**
     *
     * @var string
     */
    private $_appId;

    /**
     * 初始化
     *
     * 检测用户登录状态
     * 检查APP用户权限
     */
    public final function init()
    {
        parent::init();

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD => $this->multidb->getDefaultDb(),
            Tudu_Dao_Manager::DB_TS => $this->getTsDb()
        ));

        $appInfo = $this->_getAppInfo();

        $actionName = $this->_request->getActionName();

        // 没有使用权限
        if ($actionName != 'disable' &&
            (null === $appInfo
            || $appInfo->orgId != $this->_user->orgId))
        {
            return $this->jump('./disable');
        }

        $this->_init();
    }

    /**
     * 各应用私有初始化方法
     *
     */
    protected function _init()
    {}

    /**
     *
     */
    public function preDispatch()
    {}

    /**
     * 当不可用时显示内容
     *
     */
    public final function disableAction()
    {

    }

    /**
     * 获取当前应用信息
     */
    protected final function _getAppInfo()
    {
        if (!is_string($this->_appId)) {
            return null;
        }

        $key = 'TUDU-APP-' . $this->_user->orgId . '@' . $this->_appId;

        $info = $this->cache->get($key);

        if (!$info) {
            /* @var $daoApp Dao_Md_App_App */
            $daoApp = Tudu_Dao_Manager::getDao('Dao_Md_App_App');

            $info = $daoApp->getApp($this->_appId, $this->_user->orgId);

            if (null == $info) {
                return null;
            }

            if ($info->orgId != $this->_user->orgId) {
                return null;
            }

            $this->cache->set($key, $info, null, 86400);
        }

        return $info;
    }
}