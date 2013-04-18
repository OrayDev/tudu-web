<?php
/**
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: IndexController.php 2829 2013-04-17 08:25:05Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class IndexController extends TuduX_Controller_Admin
{

    /**
     * 安全选项
     *
     * @var array
     */
    private $_secureOptions = array(
        'passwordlevel' => array(0, 15, 30),
        'locktime'  => 20,
        'ishttps'   => 30,
        'isiprule'  => 20,
        'timelimit' => 1
    );

    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'index'));
        $this->view->LANG   = $this->lang;
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
     * 首页
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        /* @var $daoOrg Dao_Md_User_User */
        $daoUser = $this->getDao('Dao_Md_User_User');
        $usercount = $daoUser->getUserCount(array('orgid' => $this->_orgId));

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $org = $org->toArray();
        $secure = 0;
        foreach ($this->_secureOptions as $key => $val) {
            if (is_array($val)) {
                $score = $val[$org[$key]];
            } else {
                $score = !empty($org[$key]) ? $val : 0;
            }

            $secure += $score;
        }

        $daoDept = $this->getDao('Dao_Md_Department_Department');
        $daoRole = $this->getDao('Dao_Md_User_Role');
        $daoGroup = $this->getDao('Dao_Md_User_Group');
        $daoBoard = $this->getDao('Dao_Td_Board_Board', $this->_multidb->getDb('ts' . $org['tsid']));
        $count = array(
            'user'  => $usercount,
            'dept'  => $daoDept->getDepartmentCount($this->_orgId),
            'role'  => $daoRole->getRoleCount($this->_orgId),
            'group' => $daoGroup->getGroupCount($this->_orgId),
            'board' => $daoBoard->getBoardCount(array(
                'orgid' => $this->_orgId,
                'type'  => 'zone'
            ))
        );

        $host = isset($_COOKIE['server']) ? $_COOKIE['server'] : $_SERVER['SERVER_NAME'];

        // 是否需要新手指引
        $guideTips = $this->isNeedGuide();

        $this->view->guidetips  = $guideTips;
        $this->view->secure     = $secure;
        $this->view->count      = $count;
        $this->view->host       = $host;
        $this->view->org        = $org;

        $this->view->site = $this->_options['sites'];
    }

    /**
     * 是否需要新手指引提示
     */
    private function isNeedGuide()
    {
        /* @var $daoTips Dao_Md_User_Tips */
        $daoTips = $this->getDao('Dao_Md_User_Tips');
        $tipsId  = 'admin-guide';

        $tip = $daoTips->getUserTip($this->_user->uniqueId, $tipsId);
        if ($tip === null) {
            $daoTips->addTips($this->_user->uniqueId, (array) $tipsId);
            return true;
        } else if ($tip !== null && !$tip['status']) {
            return true;
        }

        return false;
    }
}
