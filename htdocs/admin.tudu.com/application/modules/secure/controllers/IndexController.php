<?php
/**
 * 系统安全首页
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: IndexController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Secure_IndexController extends TuduX_Controller_Admin
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

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'secure'));
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
     *
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId))->toArray();

        $secureItems = array();

        $total = 0;
        foreach ($this->_secureOptions as $key => $val) {
            if (is_array($val)) {
                $score = $val[$org[$key]];
            } else {
                $score = !empty($org[$key]) ? $val : 0;
            }

            $total += $score;
            $secureItems[$key] = $score;
        }

        $this->view->total  = $total;
        $this->view->secure = $secureItems;
    }
}