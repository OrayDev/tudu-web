<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Base.php 2801 2013-04-02 09:57:31Z chenyongfa $
 */

/**
 * @see Zend_Controller_Action
 */
require_once 'Zend/Controller/Action.php';

/**
 * @category   TuduX
 * @package    TuduX_Controller
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class TuduX_Controller_Base extends Zend_Controller_Action
{
    /**
     * 图度系统版本号
     *
     * @var string
     */
    const TUDU_VERSION = '1.9.1';

    /**
     *
     * @var string
     */
    const SESSION_NAMESPACE = 'SESSION_TUDU';

    /**
     * 是否允许摧毁Session
     *
     * @var boolean
     */
    protected $_allowDestroySession = true;

    /**
     * 默认的系统标签设置
     *
     * @var array
     */
    public $_labelDefaultSetting = array(
        'all'       => array('ordernum' => 9999, 'isshow' => 0, 'display' => 1),
        'inbox'     => array('ordernum' => 9998, 'isshow' => 1, 'display' => 1),
        'todo'      => array('ordernum' => 9997, 'isshow' => 2, 'display' => 3),
        'review'    => array('ordernum' => 9996, 'isshow' => 2, 'display' => 3),
        'reviewed'  => array('ordernum' => 9986, 'isshow' => 0, 'display' => 1), //不参与排序，排在最后
        'drafts'    => array('ordernum' => 9995, 'isshow' => 1, 'display' => 3),
        'starred'   => array('ordernum' => 9994, 'isshow' => 2, 'display' => 3),
        'notice'    => array('ordernum' => 9993, 'isshow' => 0, 'display' => 3),
        'discuss'   => array('ordernum' => 9992, 'isshow' => 0, 'display' => 3),
        'meeting'   => array('ordernum' => 9991, 'isshow' => 0, 'display' => 3),
        'sent'      => array('ordernum' => 9990, 'isshow' => 0, 'display' => 3),
        'forwarded' => array('ordernum' => 9989, 'isshow' => 0, 'display' => 3),
        'done'      => array('ordernum' => 9988, 'isshow' => 0, 'display' => 3),
        'ignore'    => array('ordernum' => 9987, 'isshow' => 0, 'display' => 3),
        'wait'      => array('ordernum' => 9799, 'isshow' => 0, 'display' => 2),
        'associate' => array('ordernum' => 9798, 'isshow' => 0, 'display' => 2)
    );

    /**
     * @var int
     */
    protected $_timestamp;

    /**
     * 登陆的来源地址
     *
     * @var string
     */
    protected $_refererUrl;

    /**
     *
     * @var string
     */
    protected $_sessionId;

    /**
     *
     * @var Tudu_User
     */
    protected $_user;

    /**
     *
     * @var Bootstrap
     */
    public $bootstrap;

    /**
     *
     * @var Zend_Session_Namespace
     */
    public $session;

    /**
     *
     * @var array
     */
    public $options;

    /**
     * 组织信息
     *
     * @var array
     */
    public $org;

    /**
     * 语言
     *
     * @var array
     */
    public $lang;

    /**
     *
     * @var Zend_Application_Resource_Multidb
     */
    public $multidb;

    /**
     *
     * @var Oray_Memcache
     */
    public $cache;

    /**
     *
     * @var array
     */
    protected $_labels;

    /**
     *
     * @var array
     */
    protected $_boards;

    /**
     *
     * @var array
     */
    protected $_tips;

    /**
     * 初始化
     */
    public function init()
    {
        $this->bootstrap  = $this->getInvokeArg('bootstrap');
        $this->multidb    = $this->bootstrap->getResource('multidb');
        $this->cache      = $this->bootstrap->getResource('memcache');
        $this->options    = $this->bootstrap->getOptions();

        $this->_user      = Tudu_User::getInstance();

        $this->_timestamp = time();

        if (Zend_Session::sessionExists() || !empty($this->_sessionId)) {

            if (!$this->session) {
                $this->session = new Zend_Session_Namespace(self::SESSION_NAMESPACE, false);
            }

            $this->_sessionId = Zend_Session::getId();

            do {
                // 登陆信息验证
                $names = $this->options['cookies'];

                if (!isset($this->session->auth) || !$this->_request->getCookie($names['username'])) {

                    $this->_destroySession();
                    break;
                }

                if (isset($this->session->auth['referer'])) {
                    $this->_refererUrl = $this->session->auth['referer'];
                }

                if ($this->session->auth['username'] != $this->_request->getCookie($names['username'])) {

                    $this->_destroySession();
                    break;
                }

                $this->session->auth['lasttime'] = $this->_timestamp;

                $this->_user->init($this->session->auth);
                if (!$this->_user->isLogined()) {

                    $this->_destroySession();
                }

                // 体验帐号
                if (in_array($this->_user->orgId, array('win', 'tuduoffice'))) {
                    $this->session->isdemo = true;
                }

                if ($this->_user->orgId == 'online-app') {
                    header('P3P: CP=”CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR”');
                }

                $this->org = $this->getOrg($this->_user->orgId);

                $this->_user->setOptions(array(
                    'timezone'      => !empty($this->org['timezone']) ? $this->org['timezone'] : 'Etc/GMT-8',
                    'dateformat'    => !empty($this->org['dateformat']) ? $this->org['dateformat'] : '%Y-%m-%d %H:%M:%S',
                    'passwordlevel' => $this->org['passwordlevel'],
                    'skin'          => $this->org['skin']
                ));

                if (!empty($this->_user->option['language'])) {
                    Tudu_Lang::getInstance()->setLanguage($this->_user->option['language']);
                }

                // 禁止访问
                if (Dao_Md_Org_Org::STATUS_FORBID == $this->org['status']) {
                    $controllerName = $this->_request->getControllerName();
                    if ($controllerName != 'forbid') {
                       $this->jump('/forbid');
                    }
                }

                // 设置默认时区
                if (!empty($this->_user->option['timezone'])) {
                    date_default_timezone_set($this->_user->option['timezone']);
                }

                // 注册TS数据库
                Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $this->multidb->getDb('ts' . $this->org['tsid']), true);

            } while (false);
        } else {

            $authId = $this->_request->getCookie($this->options['cookies']['auth']);

            if (!empty($authId)) {
                $query = $this->_request->getServer('HTTP_QUERY_STRING');

                return $this->jump($this->options['sites']['www'] . '/login/auto?referer=%referer', array(), array('referer' => true));
            }
        }

        $this->view->version = self::TUDU_VERSION;
        $this->view->user = $this->_user->toArray();
        $this->view->options = array('sites' => $this->options['sites'], 'tudu' => $this->options['tudu']);
    }

    /**
     *
     */
    public function postDispatch()
    {
        //$this->view->user = $this->_user->toArray();

        $this->_prepareTips();
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
     * 摧毁Session
     */
    protected function _destroySession()
    {
        if (!$this->_allowDestroySession) return;

        $names = $this->options['cookies'];
        Zend_Session::destroy();
        $this->_setCookies(array(
            $names['username'] => false
            ));
    }

    /**
     * 处理Json输出
     *
     * @param boolean $success    操作是否成功
     * @param mixed   $params     附加参数
     * @param mixed   $data       返回数据
     * @param boolean $sendHeader 是否发送json文件头
     */
    public function json($success = false, $params = null, $data = false, $sendHeader = true)
    {
        if (is_string($params) || is_numeric($params)) {
            $params = array('message' => $params);
        }

        $json = array('success' => (boolean) $success);

        if (is_array($params)) {
            unset($params['success']);
            $json = array_merge($json, $params); // 可以让success优化显示
        }

        if (false !== $data) {
            $json['data'] = $data;
        }

        $content = json_encode($json);

        $response = $this->getResponse();
        if ($sendHeader) {
            $response->setHeader('Content-Type', 'application/json');
        }
        $response->setBody($content);
        $response->sendResponse();

        exit;
    }

    /**
     * 显示提示信息
     *
     * @param string $message
     */
    public function warning($message)
    {
        $data = "<label class=\"warning\">$message</label>";

        $response = $this->getResponse();
        $response->setBody($data);
        $response->sendResponse();
        exit;
    }

    /**
     * 跳转页面
     *
     * 默认跳转到登陆页面
     *
     * @param string $url
     */
    public function jump($url = null, array $params = array(), $options = array())
    {
        $response  = $this->getResponse();

        $isReferer = !empty($options['referer']);

        if (null === $url) {
            $url = $this->getLoginUrl();
            $isReferer = true;
        }

        if ($isReferer) {
            $url .= '?redirect=%referer';
        }

        $this->view->referer = $isReferer;
        $this->view->url     = $url
                             . (!empty($params) ? (false === strpos($url, '?') ? '?' : '&') . http_build_query($params) : '');

        $this->_request->setModuleName($module = $this->getFrontController()->getDispatcher()->getDefaultModule());
        $this->render('jump', null, true);

        $response->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                 ->setHeader('Pragma', 'no-cache', true)
                 ->sendResponse();
        exit;
    }

    /**
     * 跳转页面，另一种方式
     *
     * @param string $url
     */
    public function referer($url)
    {
        $response = $this->getResponse();
        $this->view->url = $url;
        $this->_request->setModuleName($module = $this->getFrontController()->getDispatcher()->getDefaultModule());
        $this->render('referer', null, true);
        $response->setHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT')
                 ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true)
                 ->setHeader('Pragma', 'no-cache', true)
                 ->sendResponse();
        exit;
    }

    /**
     * 获取登陆的地址
     *
     * 先从cookies获取用户最后一次的登陆地址，如果没有，使用默认的登陆地址。
     *
     * @param string $orgId
     * @return string
     */
    public function getLoginUrl($orgId = null)
    {
        if (!empty($this->_refererUrl)) {
            return $this->_refererUrl;
        }

        $names = $this->options['cookies'];
        $track = $this->_request->getCookie($names['track']);
        if (!empty($track)) {
            $track = base64_decode($track);
        }
        $url = $this->_request->getServer('HTTP_HOST');
        return $url;
    }

    /**
     * 获取用户的标签
     *
     * @return array
     */
    public function getLabels($indexColumn = 'labelalias')
    {
        if (null === $this->_labels) {
            $labelDao = $this->getDao('Dao_Td_Tudu_Label');

            $result = $labelDao->getLabelsByUniqueId($this->_user->uniqueId, null, 'issystem DESC, ordernum DESC, alias ASC')->toArray($indexColumn);
            $labels = array();
            foreach ($result as $label) {
                if (isset($label['display']) && ($label['display'] & 1) != 1) {

                    continue ;
                }

                if (null !== $indexColumn) {
                    $labels[$label[$indexColumn]] = $label;
                } else {
                    $labels[] = $label;
                }
            }

            $this->_labels = $labels;
        }
        return $this->_labels;
    }

    /**
     * 格式化标签数据
     *
     * @param $params
     * @param $smarty
     */
    public function formatLabels(array $params, &$smarty)
    {
        $lang = $this->lang;//Tudu_Lang::getInstance()->load(array('common'));
        if (empty($params['labels'])) {
            $ret = array();

            if (isset($params['issystem']) && !$params['issystem']) {
                foreach ($this->_labelDefaultSetting as $alias => $item) {
                    if (!isset($this->_options['label'][$alias])) {
                        continue ;
                    }

                    $item = array(
                        'labelid'    => $this->_options['label'][$alias],
                        'labelname'  => $lang['label_' . $alias],
                        'labelalias' => $alias,
                        'ordernum'   => $item['ordernum'],
                        'issystem'   => 1,
                        'isshow'     => $item['isshow']
                    );

                    if (!empty($params['key'])) {
                        $ret[$item[$params['key']]] = $item;
                    } else {
                        $ret[] = $item;
                    }
                }
            }

            return json_encode($ret);
        }

        $labels = $params['labels'];

        $rs = array();

        foreach ($labels as $label) {
            if (isset($params['issystem']) && false === $params['issystem'] && $label['issystem']) {
                continue ;
            }

            // 过滤“所有图度”与“已审批”标签
            if ($label['labelalias'] == 'all' || $label['labelalias'] == 'reviewed') {
                continue ;
            }
            // 处理标签名称
            if ($label['issystem']) {
                $labelname = $lang['label_' . $label['labelalias']];
            } else {
                $labelname = $label['labelalias'];
            }

            $item = array(
                'labelname' => $labelname,
                'labelid' => $label['labelid'],
                'labelalias' => $label['labelalias'],
                'totalnum' => $label['totalnum'],
                'unreadnum' => $label['unreadnum'],
                'isshow' => $label['isshow'],
                'issystem' => $label['issystem'],
                'bgcolor' => $label['bgcolor'],
                'ordernum' => $label['ordernum'],
            );

            if (!empty($params['key'])) {
                $ret[$item[$params['key']]] = $item;
            } else {
                $ret[] = $item;
            }
        }

        return json_encode($ret);
    }

    /**
     * 获取用户有权限的版块数据
     *
     * @param boolean $format
     * @param boolean $isModerator
     * @return array
     */
    public function getBoards($format = true, $isModerator = false)
    {
        $records = $this->_loadBoards();

        if (!$format) {
            return $records;
        }

        $boards = array();
        foreach($records as $key => $val) {
            if ($val['issystem']) continue;
            if ($val['parentid']) {

                if (!array_key_exists($val['parentid'], $records)) {
                    continue;
                }

			    // 版主下的板块
			    if ($isModerator) {
			        if (!array_key_exists($this->_user->userId, $val['moderators'])// 是否版主
		                && !($val['ownerid'] == $this->_user->userId)// 是否创建者
		                && !array_key_exists($this->_user->userId, $records[$val['parentid']]['moderators'])// 是否上级分区版主
		                ) {
		                continue;
		            }
			    } else {
    				if (!in_array('^all', $val['groups'])
                        // 参与人
    				    && !(in_array($this->_user->userName, $val['groups'], true) || in_array($this->_user->address, $val['groups'], true))

                        // 参与人（群组）
    				    && !sizeof(array_uintersect($this->_user->groups, $val['groups'], "strcasecmp"))

    				    // 是否版主
    				    && !array_key_exists($this->_user->userId, $val['moderators'])

    					// 是否创建者
    					&& !($val['ownerid'] == $this->_user->userId)

    					// 是否上级分区版主
    					&& !array_key_exists($this->_user->userId, $records[$val['parentid']]['moderators'])
    					) {
    					continue;
    				}
			    }

                $records[$val['parentid']]['children'][] = &$records[$key];

            } else {
                $boards[$val['boardid']] = &$records[$val['boardid']];
            }
        }
        unset($records);
		// 移除非版主的分区
		if ($isModerator) {
		    foreach ($boards as $key => $board) {
		        if (!isset($board['children'])) {
		            unset($boards[$key]);
		        }
		    }
		}

        return $boards;
    }

    /**
     *
     */
    public function getBoardNav($boardId)
    {
        $records = $this->_loadBoards();
        $boards = array();
        while ($boardId && isset($records[$boardId]) && !$records[$boardId]['issystem'] && $records[$boardId]['status'] !== 1) {
            $boards[] = array($records[$boardId]['boardname'], $boardId);
            $boardId = $records[$boardId]['parentid'];
        }
        $boards[] = array($this->lang['board_home']);
        return array_reverse($boards);
    }

    /**
     * 获取组织信息
     *
     * @param $orgId
     */
    public function getOrg($orgId)
    {
        $key = 'TUDU-ORG-' . $orgId;
        $org = $this->cache->get($key);
        if (!$org) {
            $daoOrg = Oray_Dao::factory('Dao_Md_Org_Org', $this->multidb->getDb());
            $org = $daoOrg->getOrgById($orgId);
            if ($org) {
                $org = $org->toArray();
                $this->cache->set($key, $org);
            }
        }
        return $org;
    }

    /**
     * 获取系统提示数据
     */
    public function getTips()
    {
        if ($this->_tips) {
            return $this->_tips;
        }

        $lang = !empty($this->_user->option['language']) ? $this->_user->option['language'] : 'zh_CN';

        $this->_tips = $this->cache->get('TUDU-TIPS-' . $lang);

        if (!$this->_tips) {
            $dataFile = $this->options['data']['path'] . '/' . 'tudu_tips-' . $lang . '.xml';
            if (!file_exists($dataFile) || !is_readable($dataFile)) {
                return null;
            }

            $xml = @simplexml_load_file($dataFile);

            if (!$xml) {
                return null;
            }

            foreach ($xml as $tip) {
                if (!isset($tip->id)) {
                    continue ;
                }

                if (isset($tip->expiretime) && strtotime($tip->expiretime) < time()) {
                    continue ;
                }

                $item = array();
                foreach ($tip as $k => $val) {
                    $item[$k] = (string) $val;
                }

                $this->_tips[$item['id']] = $item;
            }

            $this->cache->set('TUDU-TIPS-' . $lang, $this->_tips, null, 86400);
        }

        return $this->_tips;
    }

    /**
     *
     */
    private function _loadBoards()
    {
        if (null === $this->_boards) {
            /* @var $boardDao Dao_Td_Board_Board */
            $boardDao = $this->getDao('Dao_Td_Board_Board');
            $boards   = $boardDao->getBoards(array(
                'orgid'    => $this->_user->orgId,
                'uniqueid' => $this->_user->uniqueId
            ), null, 'ordernum DESC')->toArray('boardid');

            // 用户板块排序处理
            $boardSort = $boardDao->getBoardSort($this->_user->uniqueId);
            if (null !== $boardSort && !empty($boardSort['sort'])) {
                $boards = $this->_sortBoards($boards, $boardSort['sort']);
            }

            $this->_boards = $boards;
        }

        return $this->_boards;
    }

    /**
     *
     * @param array $boards
     * @param array $sort
     */
    private function _sortBoards($boards, $sort)
    {
        $afterBoards= array();
        $records    = array();
        $sortZone   = array();
        $sortBoard  = array();
        $other      = array();
        $sortKeys   = array_keys($sort);

        foreach ($boards as $boardId => $board) {
            if (!in_array($boardId, $sortKeys)) {
                $other[] = $board;
                continue;
            }
            if ($board['type'] == 'zone') {
                foreach ($sort as $key => $orderNum) {
                    if ($boardId == $key) {
                        $sortZone[$orderNum] = $board;
                        break;
                    }
                }
                continue;
            }

            if ($board['type'] == 'board') {
                foreach ($sort as $key => $orderNum) {
                    if ($boardId == $key) {
                        $sortBoard[$orderNum] = $board;
                        break;
                    }
                }
                continue;
            }
        }

        unset($boards);
        krsort($sortZone);
        krsort($sortBoard);
        $records = array_merge($sortZone, $sortBoard, $other);

        foreach ($records as $record) {
            $afterBoards[$record['boardid']] = $record;
        }
        unset($records);

        return $afterBoards;
    }

    /**
     *
     */
    private function _prepareTips()
    {
        $path = $this->_request->getServer('REQUEST_URI');
        $viewRenderer = $this->getHelper('viewRenderer');

        $path = explode('?', $path);
        $path = preg_replace('/\/$/', '', $path[0]);

        if ($this->session && $this->session->tips &&
            (!$viewRenderer->getNeverRender() || !$viewRenderer->getNoRender()))
        {
            $tips = $this->session->tips;

            $data = array();
            foreach ($tips as $key => $value) {
                if ($path == $value) {
                    $tipsData = $this->getTips();

                    if (isset($tipsData[$key])) {
                        $data[] = $tipsData[$key];
                    }
                }
            }

            if (!empty($data)) {
                $data = json_encode($data);
                print <<<HTML
<script type="text/javascript">
if (TOP && typeof(TOP.Tips) == 'object') {
    window.onload = function(){TOP.Tips.showTips({$data});};
}
</script>
HTML;
            }
        }
    }

    /**
     * 写入操作日志
     *
     * @param string  $targetType 操作对象类型
     * @param string  $targetId 对象ID
     * @param string  $action 操作
     * @param array   $detail 修改内容
     * @param boolean $privacy
     * @return boolean
     */
    protected function _writeLog($targetType, $targetId, $action, $detail = null, $privacy = false, $isSystem = false)
    {
        if (null !== $detail) {
            $detail = serialize($detail);
        }

        $daoLog = $this->getDao('Dao_Td_Log_Log');
        return $daoLog->createLog(array(
            'orgid' => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'operator' => $isSystem ? '^system 图度系统' : $this->_user->userName . ' ' . $this->_user->trueName,
            'logtime'  => time(),
            'targettype' => $targetType,
            'targetid' => $targetId,
            'action' => $action,
            'detail' => $detail,
            'privacy' => $privacy ? 1 : 0
        ));
    }

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getTsDb()
    {
        return $this->multidb->getDb('ts' . $this->_user->tsId);
    }

    /**
     *
     * @param string $className
     * @return Oray_Dao_Abstract
     */
    public function getDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->getTsDb()));
        }
        return Zend_Registry::get($className);
    }

    /**
     * Get dao
     *
     * @param string $className
     * @return Oray_Dao_Abstract
     */
    public function getMdDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->multidb->getDefaultDb()));
        }
        return Zend_Registry::get($className);
    }

    /**
     * Get dao
     *
     * @param string $className
     * @return Oray_Dao_Abstract
     */
    public function getImDao($className)
    {
        if (!Zend_Registry::isRegistered($className)) {
            Zend_Registry::set($className, Oray_Dao::factory($className, $this->multidb->getDb('im')));
        }
        return Zend_Registry::get($className);
    }
}