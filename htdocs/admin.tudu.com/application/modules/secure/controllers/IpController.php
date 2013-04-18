<?php
/**
 * IP地址过滤控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: IpController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Secure_IpController extends TuduX_Controller_Admin
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

    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Iprule */
        $daoIprule = $this->getDao('Dao_Md_Org_Iprule');

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $ipRule = $daoIprule->getIprule(array(
            'orgid' => $this->_orgId,
            'type' => 0
        ));

        $rule = array();
        if (null != $ipRule) {
            $rule = $ipRule->toArray();
        }

        $this->view->org = $org->toArray();
        $this->view->iprule = $rule;
    }

    /**
     * 保存IP限制
     */
    public function saveAction()
    {
        $ipfilter = $this->_request->getPost('ipfilter');
        $rule = (array) $this->_request->getPost('iprule');
        $address = (array) $this->_request->getPost('address');
        $groupIds = (array) $this->_request->getPost('groupid');
        $exception = $this->_request->getPost('exception');
        $params = array();
        $param = array();

        if($ipfilter) {
            $param['isiprule'] = 1;
        } else {
            $param['isiprule'] = 0;
        }

        $rule = array_unique($rule);
        $params['rule'] = !empty($rule) && is_array($rule) ? implode("\n", $rule) : null;

        if ($ipfilter && null == $params['rule']) {
            return $this->json(false, '允许访问的IP地址不能为空');
        }

        $exception = array_merge($address, $groupIds);

        $params['exception'] = !empty($exception) && is_array($exception) ? implode("\n", $exception) : null;

        /* @var @daoOrg Dao_Md_Org_Iprule */
        $daoIprule = $this->getDao('Dao_Md_Org_Iprule');

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        if (!$daoIprule->existsIprule(array('orgid' => $this->_orgId))) {
            $params['orgid'] = $this->_orgId;
            $ret = $daoIprule->createIprule($params);
            $daoOrg->updateOrg($this->_orgId, $param);
        } else {
            $ret = $daoIprule->updateIprule($this->_orgId, $params);
            $daoOrg->updateOrg($this->_orgId, $param);
        }

        if (!$ret) {
            return $this->json(false, '保存失败');
        }

        $this->_createLog('secure', 'update', 'ip', $this->_orgId);

        return $this->json(true, '保存成功');
    }
}