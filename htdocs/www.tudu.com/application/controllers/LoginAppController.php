<?php
/**
 * 图度帐号外部登陆接口
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: LoginAppController.php 1920 2012-06-12 01:16:10Z cutecube $
 */

/**
 *
 * @author CuTe_CuBe
 *
 */

class LoginAppController extends Zend_Controller_Action
{
    /**
     *
     * @var string
     */
    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     * 初始化
     */
    public function init()
    {
        $this->getHelper('viewRenderer')->setNeverRender();
    }

    /**
     * 接收参数，通过接口方 OpenApi 进行验证
     * 登录图度
     */
    public function indexAction()
    {
        $query   = $this->_request->getQuery();
        $config  = $this->getInvokeArg('bootstrap')->getOptions();
        $multidb = $this->getInvokeArg('bootstrap')->getResource('multidb');
        $time    = time();

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_TS => $multidb->getDb('ts1')
        ));

        // 缺少验证接口标识参数
        if (empty($query['from'])) {
            return $this->_redirect('http://www.tudu.com/');
        }

        $from = $query['from'];

        $className = 'Model_OpenApi_' . ucfirst($query['from']);
        $classFile = 'Model/OpenApi/' . ucfirst($query['from']) . '.php';

        // 缺少配置参数
        if (empty($config['openapi'][strtolower($from)])) {
            return $this->_redirect('http://www.tudu.com/');
        }

        $params = array_merge($config['openapi'][strtolower($from)], $query);

        header('P3P: CP=”CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR”');

        try {

            require_once ($classFile);

            // 进行登录验证
            call_user_func(array($className, 'auth'), $params);

            // 查找应用组织关联表
            $daoAssociate = Tudu_Dao_Manager::getDao('Dao_Md_Org_Associate', Tudu_Dao_Manager::DB_MD);

            // 获取用户信息
            $params   = array_merge($config['openapi'][strtolower($from)], array('uid' => $query['uu_id']));
            $userInfo = call_user_func(array($className, 'getUserInfo'), $params);

            $orgId = $daoAssociate->getOrgIdByUid($from, $userInfo['uid']);

            if (false === $orgId) {
                $orgId = $this->_getOrgId($from);

                // 创建组织
                require_once 'Model/Org/Org.php';
                Model_Org_Org::setResource('config', $config);
                Model_Org_Org::createOrg($orgId, array(
                    'userid'   => 'admin',
                    'password' => md5(Oray_Function::randKeys(16)),
                    'truename' => $userInfo['truename'],
                    'orgname'  => $userInfo['orgname']
                ));

                // 创建关联
                $daoAssociate->createAssociate(array(
                    'orgid' => $orgId,
                    'from'  => $from,
                    'uid'   => $userInfo['uid'],
                    'truename' => $userInfo['truename'],
                    'email'    => $userInfo['email'],
                    'mobile'   => $userInfo['mobile'],
                    'tel'      => $userInfo['tel'],
                    'createtime' => time()
                ));
            }

            // 获取用户信息
            $adapter = new Tudu_Auth_Adapter_User(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD));

            $adapter->setUsername('admin@' . $orgId)
                    ->setAuto(true);

            $result = $adapter->authenticate();

            $names = $config['cookies'];
            if (!$result->isValid()) {
                $this->_setCookies(array(
                    $names['auth']     => false,
                    $names['username'] => false
                ));

                return $this->_redirect('http://www.tudu.com/');
            }

            $identity = $result->getIdentity();

            // 登录
            if (Zend_Session::isStarted()) {
                session_unset();
                Zend_Session::namespaceUnset(self::SESSION_NAMESPACE);
                Zend_Session::regenerateId();
            }

            $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE, true);
            $session->auth = array_merge($identity, array('logintime' => $time));
            $session->auth['appinvoker'] = $from;

            // 验证相关的Cookies
            $this->_setCookies(array(
                $names['username'] => $identity['username'],
                $names['server']   => $orgId . '.tudu.com'
            ), null);
            // 其它场合要用到的Cookies，永久。
            $this->_setCookies(array($names['track'] => base64_encode('http://www.tudu.com/login')), $time + 86400 * 365);

            // 同时要登录后台
            $adapter = new Tudu_Auth_Adapter_Admin(array('db' => Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD)));
            $adapter->setUsername($identity['username'])
                    ->setAuto(true);

            $result = $adapter->authenticate();
            if ($result->isValid()) {
                $session->admin = array_merge($result->getIdentity(), array('logintime' => $time));
            }

        // 操作失败
        } catch (Exception $e) {
            return $this->_redirect('http://www.tudu.com/');
        }

        return $this->_redirect('http://online-app.tudu.com/frame-inc/');
    }

    /**
     * 设置Cookies
     *
     * 315554400 = strtotime('1980-01-01'),
     *
     * @param array $cookies
     * @param int $lifetime
     */
    protected function _setCookies(array $cookies, $lifetime = 315554400)
    {
        $cookieParams = session_get_cookie_params();
        if (null === $lifetime) {
            $lifetime = $cookieParams['lifetime'];
        }
        if (!empty($this->_options['unittest'])) {
            return ;
        }
        foreach ($cookies as $key => $value) {
            setcookie(
                $key,
                $value,
                $lifetime,
                $cookieParams['path'],
                $cookieParams['domain'],
                $cookieParams['secure']
                );
        }
    }

    /**
     * 生成组织ID
     *
     * @param unknown_type $from
     */
    protected function _getOrgId($from)
    {
        // 查找应用组织关联表
        $daoAssociate = Tudu_Dao_Manager::getDao('Dao_Md_Org_Associate', Tudu_Dao_Manager::DB_MD);
        $count = $daoAssociate->getOrgCount(array('from' => $from));

        $pfx = $base = ord($from);
        $base = ($base * $base * 10);

        return $pfx . $base . ($count + 1);
    }
}