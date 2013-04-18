<?php
/**
 * 日志查询控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: LogController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Secure_LogController extends TuduX_Controller_Admin
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
        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
            $this->referer($this->_request->getBasePath() . '/login/');
        }
    }

    /**
     * 后台日志
     */
    public function indexAction()
    {
        $query   = $this->_request->getQuery();

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        /* @var @daoLog Dao_Md_Log_Oplog */
        $daoLog = $this->getDao('Dao_Md_Log_Oplog');

        $page = max(1, (int) $this->_request->getQuery('page'));
        $pageSize = 20;
        $params = array();
        $condition = array(
            'orgid' => $this->_orgId
        );

        if (!empty($query['keywords'])) {
            $params['keywords'] = $query['keywords'];
            if (strpos($query['keywords'], '@')) {
                $keyword = split('@', $query['keywords']);
                $condition['keywords'] = $keyword[0];
            } else {
                $condition['keywords'] = $query['keywords'];
            }
        }

        if (!empty($query['starttime'])) {
            $params['starttime'] = $query['starttime'];
            $condition['createtime'][0] = strtotime($query['starttime']);
        }

        if (!empty($query['endtime'])) {
            $params['endtime'] = $query['endtime'];

            $condition['createtime'][1] = strtotime($query['endtime']) + 86399;
        }

        if (isset($query['module'])) {
            $params['module'] = $query['module'];
            $condition['module'] = $query['module'];
        }

        $logs = $daoLog->getAdminLogPage($condition, 'createtime DESC', $page, $pageSize);

        $this->view->pageinfo = array(
            'currpage'    => $logs->currentPage(),
            'pagecount'   => $logs->pageCount(),
            'recordcount' => $logs->recordCount(),
            'query' => $params,
            'url' => $this->_request->getBasePath() . '/secure/log'
        );
        $this->view->registFunction('format_log_detail', array($this, 'formatLogDetail'));
        $this->view->orgid = $org->orgId;
        $this->view->logs  = $logs->toArray();
        $this->view->params = $params;
    }

    /**
     * 前台登录日志
     */
    public function loginAction()
    {
        $query   = $this->_request->getQuery();

        /* @var $daoOption Dao_Td_Mog_Login */
        $daoLoginLog = $this->getDao('Dao_Md_Log_Login');

        $page = max(1, (int) $this->_request->getQuery('page'));
        $pageSize = 20;
        $params = array();
        $condition = array(
            'orgid' => $this->_orgId
        );

        if (!empty($query['keywords'])) {
            $params['keywords'] = $query['keywords'];
            $condition['keywords'] = $query['keywords'];
        }

        if (!empty($query['starttime'])) {
            $params['starttime'] = $query['starttime'];
            $condition['createtime'][0] = strtotime($query['starttime']);
        }

        if (!empty($query['endtime'])) {
            $params['endtime'] = $query['endtime'];

            $condition['createtime'][1] = strtotime($query['endtime']) + 86399;
        }

        $logs = $daoLoginLog->getLoginLogPage($condition, 'createtime DESC', $page, $pageSize);

        $this->view->pageinfo = array(
            'currpage'    => $logs->currentPage(),
            'pagecount'   => $logs->pageCount(),
            'recordcount' => $logs->recordCount(),
            'query' => $params,
            'url' => $this->_request->getBasePath() . '/secure/log/login'
        );
        $this->view->logs = $logs->toArray();
        $this->view->params = $params;
    }

    /**
     * 格式话输出日志描述
     * @param array $params
     * @param $smarty
     */
    public function formatLogDetail(array $params, &$smarty) {
        $langdescription = $this->lang['newlogdescription'];
        $detail = $params['detail'];
        $action = $params['action'];
        $module = $params['module'];
        $subaction = $params['subaction'];

        if (!$detail) {
            return ;
        }

        $ret = array();

        if ($subaction == 'user' || $subaction == 'moderator' || $subaction == 'access' || $subaction == 'member') {
            $ret[] = $langdescription[$module][$action . '_' . $subaction];
        } else {
            if ($subaction == 'page') {
                $ret[] = $langdescription[$module][$subaction];
            } else {
                $ret[] = $langdescription[$module][$action];
            }
        }

        $detail = @unserialize($detail);

        if (is_array($detail)) {
            foreach ($detail as $key => $val) {
                switch ($key) {
                    case 'truename':
                        $ret[] = $this->lang['truename'] . $val;
                        break;
                    case 'account':
                        $ret[] = $this->lang['account'] . $val;
                        break;
                    case 'templet':
                        $ret[] = $this->lang['templet'] . $this->lang[$val];
                        break;
                    case 'templet_color':
                    case 'templet_pic':
                        $ret[] = $this->lang[$key] . $val;
                        break;
                    case 'deptname':
                    case 'rolename':
                    case 'groupname':
                        $ret[] = $val;
                        break;
                }
            }
        } else {
            $detail = $detail;
        }
        $ret = implode('&nbsp;&nbsp;', $ret);

        if (!empty($params['assign'])) {
            $smarty->assign($params['assign'], $ret);
        } else {
            return $ret;
        }
    }
}