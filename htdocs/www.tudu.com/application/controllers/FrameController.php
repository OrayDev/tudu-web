<?php
/**
 * Logo Controller
 *
 * @author Hiro
 * @version $Id: FrameController.php 2801 2013-04-02 09:57:31Z chenyongfa $
 */

class FrameController extends TuduX_Controller_Base
{

    public function preDispatch()
    {
    	$this->lang = Tudu_Lang::getInstance()->load('common');
		if (!$this->_user->isLogined()) {
            $this->jump($this->options['sites']['www'], array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    public function indexAction()
    {
        if ($this->_user->initPassword) {
            $this->jump('/frame/initpwd');
        }

        if (!isset($this->session->tips)) {
            $this->session->tips = $this->_loadTips();
        }

    	$labels = $this->getLabels(null);
    	if (!count($labels)) {
        	// 防止新用户点入左导航为空
            $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
            foreach($this->options['tudu']['label'] as $alias => $id) {
                if (!isset($labels[$alias])) {
                    $daoLabel->createLabel(array(
                        'uniqueid' => $this->_user->uniqueId,
                        'labelalias' => $alias,
                        'labelid' => $id,
                        'isshow'  => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                        'issystem' => true,
                        'ordernum' => $this->_labelDefaultSetting[$alias]['ordernum']));
                    $daoLabel->calculateLabel($this->_user->uniqueId, $id);
                    $reLoad = true;
                }
            }

            $labels = $this->getLabels(null);
    	}

    	$mailboxes = array();

    	$access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN),
    	    'flow' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_FLOW)
    	);

    	// 有权限创建工作流，但仍需判断是否为版主
    	if ($access['flow']) {
    	    $boards = $this->getBoards(true, true);

    	    // 若用户均不是某一板块的负责人或分区负责人，则无权限新建工作流
    	    if (empty($boards)) {
    	        $access['flow'] = false;
    	    }
    	}

    	// 没有权限创建工作流，则读取该用户是否有使用的工作流
    	if (!$access['flow']) {
    	    $flows = $this->_getFlows();

    	    if (!empty($flows)) {
    	        $access['flow'] = true;
    	    }
    	}

    	$daoBoard = $this->getDao('Dao_Td_Board_Board');
    	$boards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);

    	$daoEmail = $this->getMdDao('Dao_Md_User_Email');
    	$mailBoxes = $daoEmail->getEmails(array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
    	), null, array('ordernum' => 'DESC'));

    	$upload = $this->options['upload'];
    	$upload['cgi']['upload'] .= '?' . session_name() . '=' . Zend_Session::getId()
    	                          . '&email=' . $this->_user->address;

        $daoOrg = $this->getMdDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_user->orgId));

    	$this->view->mailboxes = $mailBoxes->toArray();
    	$this->view->upload = $upload;
    	//$this->view->im     = $this->options['im'];
    	$this->view->access = $access;
    	$this->view->boards = $boards;
    	$this->view->labels = $labels;
    	$this->view->user   = $this->_user->toArray();
    	$this->view->sid    = Zend_Session::getId();
    	$this->view->LANG   = $this->lang;
    	$this->view->org   = $org->toArray();
    	$this->view->checklog = !empty($this->session->auth['loginlogid']);
    	$this->view->registFunction('format_label', array($this, 'formatLabels'));
    }

    public function homeAction()
    {
    	$daoLabel = $this->getDao('Dao_Td_Tudu_Label');
    	$daoTudu  = $this->getDao('Dao_Td_Tudu_Tudu');

    	$labels = $this->getLabels();

    	$notices = $daoTudu->getTuduPage(array('uniqueid' => $this->_user->uniqueId, 'label' => '^n'), 'lastposttime DESC', 1, 5);
    	$tudus   = $daoTudu->getTuduPage(array('uniqueid' => $this->_user->uniqueId, 'label' => '^i'), 'lastposttime DESC', 1, 4);

    	$this->view->labels  = $labels;
    	$this->view->LANG    = $this->lang;
    	$this->view->notices = $notices->toArray();
    	$this->view->tudus   = $tudus->toArray();
    	//$this->view->im      = $this->options['im'];
    	//$this->view->user   = $this->_user->toArray();
    	$this->view->registFunction('format_label', array($this, 'formatLabels'));
    }

    public function initpwdAction()
    {
        if (!$this->_user->initPassword) {
            $this->referer('/frame/');
        }

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'setting'));

        $this->view->user = $this->_user->toArray();
        $this->view->LANG = $this->lang;
    }

    /**
     *
     */
    public function initpwdUpdateAction()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'setting'));

        $pwd   = $this->_request->getPost('password');
        $repwd = $this->_request->getPost('repassword');


        if ($pwd != $repwd) {
            return $this->json(false, $this->lang['confirm_password_unmatch']);
        }

        // 安全级别匹配
        $regs = array(
            1 => '/[0-9a-zA-Z]/',
            2 => '/[0-9a-zA-Z^a-zA-Z0-9]/'
        );

        $pwdLevel = isset($this->_user->option['passwordlevel']) ? $this->_user->option['passwordlevel'] : 0;
        if ($pwdLevel > 0 && !preg_match($regs[$pwdLevel], $pwd)) {
            return $this->json(false, $this->lang['password_level_not_match_' . $pwdLevel]);
        }

        $daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        $params = array('password' => $pwd);

        $ret = $daoUser->updateUserInfo($this->_user->orgId, $this->_user->userId, $params);
        $ret = $daoUser->updateUser($this->_user->orgId, $this->_user->userId, array('initpassword' => 0));

        $this->_user->clearCache($this->_user->userName);

        if (!$ret) {
            return $this->json(false, $this->lang['password_update_failure']);
        }

        // 消息队列
        $config  = $this->bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        $httpsqs->put(implode(' ', array(
            'user',
            'create',
            '',
            implode(':', array($this->_user->orgId, $this->_user->userName, $this->_user->uniqueId, $this->_user->trueName))
        )), 'admin');

        return $this->json(true, $this->lang['password_update_success']);
    }

    /**
     * 输出组织架构数据
     *
     */
    public function castAction()
    {
    	$castDao = Oray_Dao::factory('Dao_Md_User_Cast', $this->multidb->getDb());

    	$users       = $castDao->getCastUsers(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId), null, 'ordernum DESC');
    	$departments = $castDao->getCastDepartments($this->_user->orgId, $this->_user->userId);

    	$daoGroup = Oray_Dao::factory('Dao_Md_User_Group', $this->multidb->getDb());

    	$groups = $daoGroup->getGroups(array('orgid' => $this->_user->orgId));

    	$data = array(
            'users' => $users->toArray(),
            'depts' => $departments->toArray(),
            'groups' => $groups->toArray()
    	);

    	$this->json(true, null, $data);
    }

    /**
     * 输出自定义联系人列表
     */
    public function contactAction()
    {
        /** @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = $this->getDao('Dao_Td_Contact_Contact');
        /** @var $daoGroup Dao_Td_Contact_Group */
        $daoGroup   = $this->getDao('Dao_Td_Contact_Group');

        //$this->cache->deleteCache(array($daoContact, 'getContactsByUniqueId'), array($this->_user->uniqueId));
        $contacts = $daoContact->getContactsByUniqueId($this->_user->uniqueId);
        //$this->cache->deleteCache(array($daoGroup, 'getGroupsByUniqueId'), array($this->_user->uniqueId));
        $groups = $daoGroup->getGroupsByUniqueId($this->_user->uniqueId)->toArray();

        $lastContacts = array();
        $contacts = $contacts->toArray();

        foreach ($contacts as $contact) {
            if (!empty($contact['lastcontacttime'])
                && $contact['lastcontacttime'] >= strtotime('-5 days')) {
                $lastContacts[] = $contact;
            }
        }

        $data = array(
            'contacts' => $contacts,
            'groups'   => $groups,
            'lastcontact' => $lastContacts
        );

        $this->json(true, null, $data, false);
    }

    /**
     * 输出群组列表
     *
     */
    public function groupAction()
    {
    	$daoGroup = Oray_Dao::factory('Dao_Md_User_Group', $this->multidb->getDb());

    	$groups = $daoGroup->getGroups(array('orgid' => $this->_user->orgId), null, 'issystem DESC');

    	//$this->view->groups = $groups->toArray();

    	$this->json(true, null, array('groups' => $groups->toArray()));
    }

    /**
     * 输出搜索表单
     *
     */
    public function searchAction()
    {
    	$castDao = Oray_Dao::factory('Dao_Md_User_Cast', $this->multidb->getDb());

    	$users  = $castDao->getCastUsers(array('orgid' => $this->_user->orgId, 'castid' => $this->_user->castId));
    	$boards = $this->getBoards();
    	$labels = $this->getLabels();

    	foreach ($labels as &$label) {
    		if ($label['issystem']) {
    			$label['displayname'] = $this->lang['label_' . $label['labelalias']];
    		} else {
    			$label['displayname'] = $label['labelalias'];
    		}
    	}

    	$this->view->boards = $boards;
    	$this->view->labels = $labels;
    	$this->view->users  = $users->toArray();
    	$this->view->LANG   = $this->lang;
    }

    /**
     * 天气预报
     */
    public function weatherAction()
    {
    	$loc       = $this->_request->getQuery('loc');
    	$lang      = $this->_request->getQuery('lang');

    	if (!$lang) {
    		$lang = empty($this->_user->option['language']) ? 'zh_CN' : $this->_user->option['language'];
    	}

    	/**
    	 * @see Tudu_Api
    	 */
    	require_once 'Tudu/Api.php';
    	$option = $this->bootstrap->getOption('api');
    	$api = new Tudu_Api($option['tudu']);

    	if (!$loc) {
    		$loc = $api->getLocation(Oray_Function::getTrueIp());
    	}

    	$weather = $api->getWeather($lang, $loc);

    	$this->json(true, null, $weather);
    }

    /**
     * IP地址信息检测
     */
    public function ipAction()
    {
        if (empty($this->session->auth['loginlogid']) || empty($this->session->auth['local'])) {
            $this->session->auth['loginlogid'] = null;
            return $this->json(true);
        }

        $local      = $this->session->auth['local'];
        $loginLogId = $this->session->auth['loginlogid'];

        /* @var $daoLoginLog Dao_Md_Log_Login */
        $daoLoginLog = $this->getMdDao('Dao_Md_Log_Login');

        $logId = $this->session->auth['loginlogid'];

        $message = null;

        // 当前地点与用户常用地理位置不符
        if ($local != $this->_user->option['usuallocal']) {
            // 当前位置是否有连续登陆七天
            $logs = $daoLoginLog->getLoginLogs(array(
                'orgid' => $this->_user->orgId,
                'uniqueid' => $this->_user->uniqueId,
                'createtime' => array(0 => strtotime('-10 days'))
            ), null, 'createtime DESC')->toArray();

            $usualLocal = null;
            $lastLocal  = null;
            $lastDate = null;
            $dateCount = 0;
            foreach ($logs as $log) {
                if (null === $lastLocal) {
                    $lastLocal = $local;
                }

                if ($lastLocal != $local) {
                    $lastLocal = $local;
                    continue ;
                }

                if ($dateCount >= 5) {
                    $usualLocal = $local;
                    break;
                }

                if (date('Y-m-d', $log['createtime']) != $lastDate) {
                    $lastDate = date('Y-m-d', $log['createtime']);
                    $dateCount ++;
                }
            }

            if (null != $usualLocal) {
                /* @var $daoOption Dao_Md_User_Option */
                $daoOption = $this->getMdDao('Dao_Md_User_Option');

                $daoOption->updateOption($this->_user->orgId, $this->_user->userId, array(
                    'usuallocal' => $usualLocal
                ));
            }

        // 获取上一次登陆日志
        } elseif ($this->_user->option['usuallocal']) {
            /*$prevLog = $daoLoginLog->getPrevLog(
                array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId),
                array('prev' => $logId)
            );*/

            if ($prevLog->local != $this->_user->option['usallocal']) {
                $message = sprintf('上次登录的IP所在地为%s，如有需要请查看日志', $prevLog->local);
            }
        }

        $this->session->auth['loginlogid'] = null;

        return $this->json(true, $message);
    }

    /**
     * 屏幕解锁
     */
    public function unlockAction()
    {
    	$password = $this->_request->getPost('password');

    	if (!$password) {
    		return $this->json(false, $this->lang['invalid_password']);
    	}

    	$uid = $this->_user->userName;

    	$adapter = new Tudu_Auth_Adapter_User($this->multidb->getDb(), null, null, array('skiplock' => true));

    	$result = $adapter->setUsername($uid)
    	                  ->setPassword($password)
    	                  ->authenticate();

    	if (!$result->isValid()) {
    		$msg = $result->getMessages();
    		return $this->json(false, $this->lang['unlock_auth_' . $msg[0]]);
    	}

    	$this->json(true, $this->lang['unlock_auth_success']);
    }

    /**
     *
     */
    public function tipsAction()
    {
        /** @var $daoTips Dao_Md_User_Tips */
        $daoTips = $this->getMdDao('Dao_Md_User_Tips');

        $status = (int) $this->_request->getQuery('status');
        $tipsId = $this->_request->getQuery('tipsid');

        $ret = $daoTips->updateTips($this->_user->uniqueId, $tipsId, array('status' => $status == 1 ? 1 : 0));

        unset($this->session->tips[$tipsId]);

        return $this->json($ret, null);
    }

    /**
     * 获取用户im在线状态
     */
    public function imStatusAction()
    {
        $email = $this->_request->getQuery('email');
        $config = $this->bootstrap->getOption('im');
        $im = new Oray_Im_Client($config['host'], $config['port']);
        $imStatus = $im->getUserStatus($email);

        $this->json(true, null, $imStatus[$email]['show'] ? $imStatus[$email]['show'] : null);
    }

	/**
     * 用户名片
     */
    public function userCardAction()
    {
        $userId = $this->_request->getQuery('userid');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        $user = $daoUser->getUserCard(array(
            'orgid'  => $this->_user->orgId,
            'userid' => $userId
        ));

        return $this->json(true, null, $user);
    }

    /**
     * 提示框
     */
    private function _loadTips()
    {
        $tips = $this->getTips();
        $unread = array();

        if ($tips) {
            /** @var $daoTips Dao_Md_User_Tips */
            $daoTips = $this->getMdDao('Dao_Md_User_Tips');

            $userTips = $daoTips->getUserTips($this->_user->uniqueId);

            foreach ($tips as $item) {
                if (!array_key_exists($item['id'], $userTips)) {
                    $newTips[] = $item['id'];
                    $unread[$item['id']] = $item['path'];
                    continue ;
                }

                if (isset($userTips[$item['id']]) && (int) $userTips[$item['id']]['status'] == 0) {
                    $unread[$item['id']] = $item['path'];
                }
            }

            // 添加新的气泡记录
            if (isset($newTips)) {
                $daoTips->addTips($this->_user->uniqueId, array_unique($newTips));
            }
        }

        return $unread;
    }

    /**
     * 读取用户下是否有可用的工作流
     */
    private function _getFlows()
    {
        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $records = $daoFlow->getFlows(array('orgid' => $this->_user->orgId), null, 'createtime DESC');
        $records = $records->toArray();

        $flows = array();
        foreach($records as $key => $record) {
            if ($record['parentid']) {
                if (!in_array('^all', $record['avaliable'])
                        // 参与人
                        && !(in_array($this->_user->userName, $record['avaliable'], true) || in_array($this->_user->address, $record['avaliable'], true))
                        // 参与人（群组）
                        && !sizeof(array_uintersect($this->_user->groups, $record['avaliable'], "strcasecmp"))
                        // 是否创建者
                        && !($record['uniqueid'] == $this->_user->uniqueId)
                ) {
                    continue;
                }

                $flows[$record['parentid']]['children'][] = &$records[$key];
            }
        }

        unset($records);
        return $flows;
    }
}

