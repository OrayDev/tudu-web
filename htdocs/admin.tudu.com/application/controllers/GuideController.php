<?php
/**
 * 新手指引控制器
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: GuideController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */
class GuideController extends TuduX_Controller_Admin
{
    /**
     * (non-PHPdoc)
     * @see TuduX_Controller_Admin::init()
     */
    public function init()
    {
        parent::init();
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
            $this->referer($this->_request->getBasePath() . '/login/');
        }
    }

    /**
     * 指引首页
     */
    public function indexAction()
    {}

    /**
     * 更新状态
     */
    public function tipsAction()
    {
        /* @var $daoTips Dao_Md_User_Tips */
        $daoTips = $this->getDao('Dao_Md_User_Tips');
        $ret     = $daoTips->updateTips($this->_user->uniqueId, 'admin-guide', array('status' => 1));

        return $this->json($ret, null);
    }
}