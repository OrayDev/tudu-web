<?php

/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: ChatController.php 1687 2012-03-08 10:27:56Z cutecube $
 */

/**
 * @package    Foreign
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Public_ChatController extends Zend_Controller_Action
{

    /**
     * 初始化
     */
    public function init()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'chat'));
        $this->view->LANG = $this->lang;

        $this->_helper->viewRenderer->view->setBasePath(APPLICATION_PATH . '/modules/public/views');
        $this->_helper->viewRenderer->setViewScriptPathSpec(':module#:controller#:action.:suffix');
    }

    /**
     * 聊天记录
     */
    public function logAction()
    {
        $jid = $this->_request->getQuery('jid');
        $email = $this->_request->getQuery('email');
        $clientKey = $this->_request->getQuery('clientkey');
        $page     = max((int) $this->_request->getQuery('page'), 1);
		$pageSize = 100;

		$bootstrap  = $this->getInvokeArg('bootstrap');
        $multidb    = $bootstrap->getResource('multidb');
        $options    = $bootstrap->getOptions();

        $config = $options['im'];
        $im = new Oray_Im_Client($config['host'], $config['port']);

        $userInfo = $im->getUserInfo($jid, $clientKey);

        if (!$userInfo) {
            //return $this->_redirect('/');
        }

        /* @var $daoChatLog Dao_Im_Chat_Log */
        $daoChatLog = Oray_Dao::factory('Dao_Im_Chat_Log', $multidb->getDb('im'));

        /* @var $daoUdContact Dao_Im_Contact_Contact */
        $daoUdContact = Oray_Dao::factory('Dao_Im_Contact_Contact', $multidb->getDb('im'));

        $jemail = explode('@', $jid);
        $userId = $jemail[0];

        $condition = array(
            'orgid' => 'tudu',
            'userid' => $userId,
            'email' => $email
        );
        $info = $daoUdContact->getContact($condition);

        if (!$info) {
            $info = array(
                'userid' => $userId,
            	'email' => $email,
                'displayname' => $email
            );
        } else {
            $info = $info->toArray();
        }

        $logs = $daoChatLog->getLogPage(array(
            'ownerid' => $jid,
            'otherid' => $email
		), 'createtime DESC', $page, $pageSize);

		$this->view->pageinfo = array(
            'currpage'    => $logs->currentPage(),
            'pagecount'   => $logs->pageCount(),
            'recordcount' => $logs->recordCount()
		);

		$data = array(
            'pageinfo' => array(
                'currpage'    => $logs->currentPage(),
                'pagecount'   => $logs->pageCount(),
                'recordcount' => $logs->recordCount()
            )
		);

		$logs = $logs->toArray();
		$ret  = array();
		foreach ($logs as $log) {
			$ret[strtotime(date('Y-m-d', $log['createtime']))][$log['createtime']] = $log;
		}

		foreach ($ret as &$day) {
			ksort($day);
		}

		ksort($ret);

		$data['logs'] = $ret;

		$this->view->email   = $email;
		$this->view->jid     = $jid;
		$this->view->logs    = $ret;
		$this->view->options = $options;
		$this->view->info    = $info;
    }
}