<?php
/**
 * 系统安全登录设置控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: LoginController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Secure_LoginController extends TuduX_Controller_Admin
{

    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'secure'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('save'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 显示页面
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $this->view->org = $org->toArray();
    }

    /**
     * 保存设置
     */
    public function saveAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $post = $this->_request->getPost();

        $timeLimit = array();
        $selectAll = 0XFFFFFF;
        $isAll     = 0;
        for ($i = 0; $i < 7; $i++) {
            $section = !empty($post['wd-' . $i]) ? base_convert($post['wd-' . $i], 10, 16) : 0;
            if ($section == $selectAll) {
                $isAll ++;
            }
            $timeLimit[] = $section;
        }

        if ($isAll == 6) {
            $timeLimit = null;
        } else {
            $timeLimit = implode("\n", $timeLimit);
        }

        $params = array(
            'passwordlevel' => (int) $post['pwdlevel'],
            'locktime'      => !empty($post['locktime']) ? (int) $post['locktime'] : 0,
            'ishttps'       => !empty($post['ishttps']) ? 1 : 0,
            'timelimit'     => $timeLimit
        );

        $ret = $daoOrg->updateOrg($this->_orgId, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['org_update_failure']);
        }

        $this->_cleanCache();

        $this->_createLog('secure', 'update', 'login', $this->_orgId, null);

        $this->json(true, $this->lang['org_update_success']);
    }

    /**
     *
     */
    private function _cleanCache()
    {
        $key = 'TUDU-ORG-' . $this->_orgId;
        $this->_bootstrap->memcache->delete('im_' . $this->_orgId . '_orgname');
        $this->_bootstrap->memcache->delete($key);
    }
}
