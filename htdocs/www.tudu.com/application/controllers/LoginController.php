<?php
/**
 * Login Controller
 *
 * @author Hiro
 * @version $Id: LoginController.php 2801 2013-04-02 09:57:31Z chenyongfa $
 */

class LoginController extends TuduX_Controller_Base
{
    /**
     * 是否允许摧毁Session
     *
     * @var boolean
     */
    protected $_allowDestroySession = false;

    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->lang = Tudu_Lang::getInstance()->load('login');
    }

    /**
     * 登陆接口
     */
    public function indexAction()
    {
        // 关闭缓存
        $this->_response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                        ->setHeader('Pragma', 'no-cache', true);

        $userId   = trim($this->_request->getPost('uid'));
        $orgId    = trim($this->_request->getPost('orgid'));
        $domain   = $this->_request->getPost('domain');
        $password = trim($this->_request->getPost('password'));
        $seccode  = $this->_request->getPost('seccode');
        $remember = (boolean) $this->_request->getPost('remember');

        // 来源地址
        $referer  = $this->_request->getServer('HTTP_REFERER', $this->options['sites']['www']);
        $referer  = preg_replace('/[\#\?].*/i', '', $referer);
        $redirect = $this->_request->getPost('redirect');

        $error = null;
        do {

            if (empty($userId) || empty($password)) {
                $error = 'params';
                break;
            }

            if (false === strpos($userId, '@')) {
                $userId .= '@' . $orgId;
            }

            list(, $suffix) = explode('@', $userId);

            // 验证码
            if (Oray_Seccode::getInstance()->getCode('login')) {
                if (empty($seccode) || !Oray_Seccode::isValid($seccode, 'login')) {
                    $error = 'seccode';
                    break;
                }

                Oray_Seccode::clear('login');
            }

            $adapter = new Tudu_Auth_Adapter_User(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD));
            $adapter->setUsername($userId)
                    ->setPassword($password);

            $result = $adapter->authenticate();

            if (!$result->isValid()) {
                $message = $result->getMessages();
                $error = isset($message[0]) ? $message[0] : 'failure';
                break;
            }

        } while (false);

        // 登陆失败
        if (null !== $error) {
            $referer .= '?error=' . $error;
            if ($redirect) {
                $referer .= '&redirect=' . urlencode($redirect);
            }
            $this->referer($referer);
            return;
        }

        $identity = $result->getIdentity();
        $identity['referer'] = $referer;

        $isHttps = $identity['ishttps'];

        // 检测登陆IP写入登录日志
        $clientIp = $this->_request->getClientIp();
        $daoIp    = Tudu_Dao_Manager::getDao('Dao_Md_Ip_Info', Tudu_Dao_Manager::DB_MD);
        $ipInfo   = $daoIp->getInfoByIp($clientIp);

        if (null !== $ipInfo) {
            $identity['local'] = $ipInfo->city;
        }

        $logId = $this->_loginLog(array(
            'orgid'    => $identity['orgid'],
            'uniqueid' => $identity['uniqueid'],
            'address'  => $identity['username'],
            'truename' => $identity['truename'],
            'ip'       => $clientIp,
            'local'    => !empty($identity['local']) ? $identity['local'] : null
        ));

        $identity['loginlogid'] = $logId;

        $identity = $this->_loginFilter($identity);

        $this->_user->clearCache($identity['username']);

        if (Zend_Session::isStarted()) {
            session_unset();
            Zend_Session::namespaceUnset(self::SESSION_NAMESPACE);
            Zend_Session::regenerateId();
        }

        $this->session = new Zend_Session_Namespace(self::SESSION_NAMESPACE, true);
        $this->session->auth = array_merge($identity, array('logintime' => $this->_timestamp));

        $server = $this->getServer($identity['orgid']);
        $names  = $this->options['cookies'];

        // 验证相关的Cookies
        $this->_setCookies(array(
            $names['username'] => $identity['username'],
            $names['server']   => $server
        ), $remember ? $this->_timestamp + 86400 * 30 : null);
        // 其它场合要用到的Cookies，永久。
        $this->_setCookies(array($names['track'] => base64_encode($referer)), $this->_timestamp + 86400 * 365);

        // 记住自动登录信息
        if ($remember) {
            $daoSession = Tudu_Dao_Manager::getDao('Dao_Md_User_Session', Tudu_Dao_Manager::DB_MD);

            $authId = Dao_Md_User_Session::getSessionId($identity['userid'] . '@' . $identity['orgid']);

            $daoSession->createSession(array(
                'sessionid'  => $authId,
                'orgid'      => $identity['orgid'],
                'userid'     => $identity['userid'],
                'logintime'  => $this->_timestamp,
                'loginip'    => $clientIp,
                'expiretime' => $this->_timestamp + 86400 * 30
            ));

            // 自动登录Cookies，一个月。
            $this->_setCookies(array(
                $names['auth']     => $authId
            ), $this->_timestamp + 86400 * 30);

            $this->session->auth['authid'] = $authId;
        }

        // 是否使用ssl
        if (!$redirect) {
            $protocol = $identity['ishttps'] ? 'https:' : 'http:';
            $redirect = $protocol . '//' . $server . '/frame';
        }

        $this->referer($redirect);
    }

    /**
     * 管理员登录流程
     *
     * /login/login-admin
     */
    public function loginAdminAction()
    {
        // 未登录前台
        if (!$this->_user->isLogined()) {
            $referer = $this->options['sites']['www'];
            if (!empty($this->session->auth['referer'])) {
                $referer = $this->session->auth['referer'];
            }

            return $this->referer($referer);
        }

        // 非管理员身份
        if (!$this->_user->isAdmin() && !$this->_user->isOwner()) {
            PROTOCOL . '//' . $this->getServer($this->_user->orgId) . '/admin/login/?err=timeout';
        }

        $email    = $this->_request->getPost('email');
        $password = $this->_request->getPost('password');
        $seccode  = $this->_request->getPost('seccode');
        $error    = null;

        do {
            if (empty($email)) {
                $error = 'invalid email';
                break;
            }

            if (empty($password)) {
                $error = 'invalid password';
                break;
            }

            if (empty($seccode)) {
                $error = 'unvalid seccode';
                break;
            }

            if (!Oray_Seccode::isValid($seccode, 'adlogin')) {
                $error = 'invalid seccode';
                break;
            }
            Oray_Seccode::clear('adlogin');

            $adapter = new Tudu_Auth_Adapter_Admin(array('db' => Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD)));
            $adapter->setUsername($email)
            ->setPassword($password);

            $result = $adapter->authenticate();

            if (!$result->isValid()) {
                $message = $result->getMessages();
                $error = isset($message[0]) ? $message[0] : 'failure';
                break;
            }
        } while (false);

        if (null !== $error) {
            return $this->referer(PROTOCOL . '//' . $this->getServer($this->_user->orgId) . '/admin/login/?err=' . $error);
        }

        $this->session->admin = array_merge($result->getIdentity(), array('logintime' => time()));

        //$this->_user->initAdmin($this->session->admin);

        // 添加登入日志
        $daoLog = Tudu_Dao_Manager::getDao('Dao_Md_Log_Oplog', Tudu_Dao_Manager::DB_MD);

        $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();

        $ret = $daoLog->createAdminLog(array(
                'orgid'     => $this->_user->orgId,
                'userid'    => $this->_user->userId,
                'ip'        => $clientIp,
                'module'    => Dao_Md_Log_Oplog::MODULE_LOGIN,
                'action'    => Dao_Md_Log_Oplog::OPERATION_LOGIN,
                'subaction' => null,
                'target'    => implode(':', array($this->_user->orgId, $this->_user->address, $this->_user->uniqueId)),
                'local'     => !empty($this->session->auth['local']) ? $this->session->auth['local'] : null,
                'detail'    => serialize(array('account' => $this->_user->userName))
        ));


        return $this->referer(PROTOCOL . '//' . $this->getServer($this->_user->orgId) . '/admin/');
    }

    /**
     * 注销接口
     */
    public function logoutAction()
    {
        if (Zend_Session::isStarted()) {
            $this->_allowDestroySession = true;
            $this->_destroySession();
        }

        $url = $this->getLoginUrl();

        if ($this->_user && $this->_user->isLogined()) {
            $this->_user->clearCache($this->_user->email);
        }

        setcookie('lockscreen', 0, 1, '/');

        $this->referer($url);
    }


    /**
     * 退出管理员登录
     *
     * /login/logout-admin
     */
    public function logoutAdminAction()
    {
        if ($this->_user->isLogined() && $this->_user->isAdmin()) {
            // 添加登出日志
            $daoLog = Tudu_Dao_Manager::getDao('Dao_Md_Log_Oplog', Tudu_Dao_Manager::DB_MD);

            $clientIp = isset($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $this->_request->getClientIp();
            $target   = implode(':', $this->_user->email
                    ? array($this->_user->orgId, $this->_user->email, $this->_user->uniqueId)
                    : array($this->_user->orgId, $this->_user->email));
            $detail   = array('account' => $this->_user->userId);

            $ret = $daoLog->createAdminLog(array(
                    'orgid'     => $this->_user->_orgId,
                    'userid'    => $this->_user->userId,
                    'ip'        => $clientIp,
                    'module'    => Dao_Md_Log_Oplog::MODULE_LOGIN,
                    'action'    => Dao_Md_Log_Oplog::OPERATION_LOGOUT,
                    'subaction' => null,
                    'target'    => $target,
                    'local'     => !empty($this->session->auth['local']) ? $this->session->auth['local'] : null,
                    'detail'    => serialize($detail)
            ));

            $this->session->admin = null;

            $this->referer($this->_getLoginUrl($this->_user->orgId) . 'admin/login/');
        }

        $this->referer($this->options['sites']['www']);
    }


    /**
     * 获取组织对应的服务地址
     *
     * @param $orgId
     */
    public function getServer($orgId)
    {
        return $this->options['tudu']['domain'];
    }

    /**
     * 登录拦截
     *
     * @param array $identity
     */
    protected function _loginFilter(array $identity)
    {
        $clientIp = $this->_request->getClientIp();

        // 登录时段判断
        if (!empty($identity['timelimit'])) {
            if (!empty($identity['timezone'])) {
                date_default_timezone_set($identity['timezone']);
            }
            $arr = explode('-', date('w-H', $this->_timestamp));
            $weekDay = (int) $arr[0];
            $hour    = (int) $arr[1];

            if (!empty($identity['timelimit'][$weekDay])) {
                $code = pow(2, $hour);
                if (($identity['timelimit'][$weekDay] & $code) != $code) {
                    $identity['invalid']['time'] = true;
                }
            } else {
                $identity['invalid']['time'] = true;
            }

            unset($identity['timelimit'], $identity['timezone']);
        }

        // 登录IP过滤
        if (!empty($identity['iprule'])) {
            /* @var $daoIpRule Dao_Md_Org_Iprule */
            $daoIpRule = Tudu_Dao_Manager::getDao('Dao_Md_Org_Iprule', Tudu_Dao_Manager::DB_MD);

            $rules = $daoIpRule->getIprules(array('orgid' => $identity['orgid']));

            while ($rule = $rules->current()) {
                $isException = $rule->exception
                            && (in_array($identity['username'], $rule->exception, true)
                            || in_array($identity['address'], $rule->exception, true)
                            || (boolean) sizeof(array_uintersect($identity['groups'], $rule->exception, "strcasecmp")));

                if (!$isException && !$rule->isMatch($clientIp)) {
                    $identity['invalid']['ip'] = true;
                }

                $rules->next();
            }

            unset($identity['iplimit']);
        }
        unset($identity['groups']);

        return $identity;
    }

    /**
     *
     * @param array $params
     */
    protected function _loginLog(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['uniqueid'])
            || empty($params['address'])
            || empty($params['truename']))
        {
            return false;
        }

        $daoLog = Tudu_Dao_Manager::getDao('Dao_Md_Log_Login', Tudu_Dao_Manager::DB_MD);

        if (!empty($params['clientkey'])) {
            if (null !== $daoLog->getLoginLog(array('orgid' => $params['orgid'], 'uniqueid' => $params['uniqueid'], 'clientkey' => $params['clientkey']))) {
                return null;
            }
        }

        $params['loginlogid'] = Dao_Md_Log_Login::getLoginLogId();
        $params['createtime'] = $this->_timestamp;
        $params['clientinfo'] = $this->_request->getServer('HTTP_USER_AGENT');

        $logId = $daoLog->createLog($params);

        return $logId;
    }

    /**
     *
     * @param string $orgId
     */
    protected function _getLoginUrl($orgId)
    {
        return $this->options['sites']['tudu'] . '/';
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
     * 销毁session
     *
     */
    protected function _destroySession()
    {
        //if (!$this->_allowDestroySession) return;

        $names = $this->options['cookies'];
        //Zend_Session::destroy();
        if ($this->session && $this->session->auth) {
            $this->session->auth = null;
        }
        $this->_setCookies(array(
            $names['username'] => false
            ));
    }
}

