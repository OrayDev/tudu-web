<?php
/**
 * Email Controller
 *
 * @version $Id: EmailController.php 2829 2013-04-17 08:25:05Z chenyongfa $
 */

class EmailController extends TuduX_Controller_Base
{

    /**
     *
     * @var Dao_Md_User_Email
     */
    private $_daoEmail;

    /**
     * 支持的邮箱域名
     *
     * @var array
     */
    private $_supportMailboxes = array(
            'oray.com' => array('imaphost' => 'imap.vip.olivemail.net', 'pop3host' => 'pop3.vip.olivemail.net', 'type' => 1, 'protocol' => 'imap'),
            '163.com'  => array('imaphost' => 'imap.163.com', 'pop3host' => 'pop.163.com', 'protocol' => 'imap'),
            '126.com'  => array('imaphost' => 'imap.126.com', 'pop3host' => 'pop.126.com', 'protocol' => 'imap'),
            'vip.163.com'  => array('imaphost' => 'imap.vip.163.com', 'pop3host' => 'pop.vip.163.com', 'protocol' => 'imap'),
            'vip.126.com'  => array('imaphost' => 'imap.vip.126.com', 'pop3host' => 'pop.vip.126.com', 'protocol' => 'imap'),
            'yeah.net' => array('imaphost' => 'imap.yeah.net', 'pop3host' => 'pop.yeah.net', 'protocol' => 'imap'),
            '188.com'  => array('imaphost' => 'imap.188.com', 'pop3host' => 'pop.188.com', 'protocol' => 'imap'),
            'gmail.com' => array('imaphost' => 'imap.gmail.com', 'pop3host' => 'pop.gmail.com', 'isssl' => 1, 'protocol' => 'pop3'),
            'qq.com' => array('imaphost' => 'imap.qq.com', 'pop3host' => 'pop.qq.com', 'protocol' => 'imap'),
            'vip.qq.com' => array('imaphost' => 'imap.qq.com', 'pop3host' => 'pop.qq.com', 'protocol' => 'imap'),
            'foxmail.com' => array('imaphost' => 'imap.foxmail.com', 'pop3host' => 'pop.foxmail.com', 'protocol' => 'pop3'),
            '139.com' => array('imaphost' => 'imap.139.com', 'pop3host' => 'pop.139.com', 'protocol' => 'imap'),
            '21cn.com' => array('imaphost' => 'pop.21cn.com', 'protocol' => 'pop3'),
            'yahoo.cn' => array('pop3host' => 'mail.pop.yahoo.cn', 'protocol' => 'pop3'),
            'yahoo.com' => array('pop3host' => 'mail.pop.yahoo.cn', 'protocol' => 'pop3'),
            'yahoo.com.cn' => array('pop3host' => 'mail.pop.yahoo.cn', 'protocol' => 'pop3'),
            'sina.com' => array('pop3host' => 'pop.sina.com', 'protocol' => 'pop3'),
            'sina.cn' => array('pop3host' => 'pop.sina.com', 'protocol' => 'pop3'),
            'vip.sina.com' => array('pop3host' => 'pop.vip.sina.com', 'protocol' => 'pop3'),
            'sohu.com' => array('pop3host' => 'pop3.sohu.com', 'protocol' => 'pop3'),
            'vip.sohu.com' => array('pop3host' => 'pop3.vip.sohu.com', 'protocol' => 'pop3'),
            'sogou.com' => array('pop3host' => 'pop.sogou.com', 'protocol' => 'pop3'),
            'tom.com' => array('pop3host' => 'pop.tom.com', 'protocol' => 'pop3'),
            '163.net' => array('pop3host' => 'pop.163.net', 'protocol' => 'pop3'),
            'eyou.com' => array('pop3host' => 'pop.eyou.com', 'protocol' => 'pop3'),
            'vip.tom.com' => array('pop3host' => 'pop.vip.tom.com', 'protocol' => 'pop3'),
            'wo.com.cn' => array('pop3host' => 'pop.wo.com.cn', 'protocol' => 'pop3'),
            '189.cn' => array('pop3host' => 'pop.189.cn', 'protocol' => 'pop3'),
            'hotmail.com' => array('pop3host' => 'pop3.live.com', 'isssl' => 1, 'protocol' => 'pop3'),
            'msn.com' => array('pop3host' => 'pop3.live.com', 'isssl' => 1, 'protocol' => 'pop3'),
            'live.cn' => array('pop3host' => 'pop3.live.com', 'isssl' => 1, 'protocol' => 'pop3'),
            'live.com' => array('pop3host' => 'pop3.live.com', 'isssl' => 1, 'protocol' => 'pop3'),
            '263.net' => array('pop3host' => 'pop.263.net', 'protocol' => 'pop3'),
            'x263.net' => array('pop3host' => 'pop.263.net', 'protocol' => 'pop3'),
            '263.net.cn' => array('pop3host' => 'pop.263.net', 'protocol' => 'pop3')
        );

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        $action = strtolower($this->_request->getActionName());

        if ($action !== 'status') {
            $this->lang = Tudu_Lang::getInstance()->load(array('common', 'email'));

            $this->view->access = array('skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN));
            $this->view->LANG   = $this->lang;

        } else {
            $this->lang = Tudu_Lang::getInstance()->load(array('common'));
        }

        $this->_daoEmail = $this->getMdDao('Dao_Md_User_Email');
    }

    public function preDispatch()
    {
        if (!$this->_user->isLogined()) {
            $this->json(false, $this->lang['login_timeout'], array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     * 显示邮箱列表
     */
    public function indexAction()
    {
        $emails = $this->_daoEmail->getEmails(
            array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId),
            null,
            array('ordernum' => 'DESC')
        );

        $this->view->emails = $emails->toArray();
    }

    /**
     * 编辑邮箱表单
     */
    public function modifyAction()
    {
        $address = $this->_request->getQuery('address');
        $action  = 'create';

        if (!empty($address)) {
            $email = $this->_daoEmail->getEmail(array(
                'orgid'   => $this->_user->orgId,
                'userid'  => $this->_user->userId,
                'address' => $address
            ));

            if (null !== $email) {
                $action = 'update';
                $this->view->email = $email->toArray();
            }
        }

        $this->view->supports = json_encode($this->_supportMailboxes);
        $this->view->action   = $action;
    }

    /**
     * 创建邮箱绑定
     */
    public function createAction()
    {
        $post = $this->_request->getPost();
        $port = !empty($post['port']) ? $post['port'] : null;

        if (empty($post['address']) || !Oray_Function::isEmail($post['address'])) {
            return $this->json(false, $this->lang['invalid_email_address']);
        }

        if (!trim($post['password'])) {
            return $this->json(false, $this->lang['missing_email_password']);
        }

        if (empty($post['host'])) {
            return $this->json(false, $this->lang['missing_email_host'], array('advance' => true));
        }

        if (!Oray_Function::isDomainName($post['host']) && !Oray_Function::isIp($post['host'])) {
            return $this->json(false, sprintf($this->lang['invalid_imap_host'], strtoupper($post['protocol'])), array('advance' => true));
        }

        if ($port != null) {
            if ($port <= 0 || $port > 65535) {
                return $this->json(false, sprintf($this->lang['invalid_imap_port'], strtoupper($post['protocol'])));
            }
        }

        $address = $post['address'];

        if (null !== $this->_daoEmail->getEmailByAddress($this->_user->orgId, $this->_user->userId, $address)) {
            $this->json(false, sprintf($this->lang['already_binded'], $address));
        }

        $isSsl   = isset($post['isssl']) && $post['isssl'] == 1 ? 1 : 0;
        $type    = isset($post['type']) ? (int) $post['type'] : 0;

        /**
         * 验证邮箱密码
         */
        if (!$this->_validMailbox($address, $post['password'], $post['host'], $port, (boolean) $isSsl, $post['protocol'])) {
            return $this->json(false);
        }

        $params = array(
            'orgid'    => $this->_user->orgId,
            'userid'   => $this->_user->userId,
            'address'  => $post['address'],
            'password' => $post['password'],
            'protocol' => $post['protocol'],
            'host'     => $post['host'],
            'port'     => (int) $post['port'] > 0 ? (int) $post['port'] : null,
            'isssl'    => $isSsl,
            'type'     => $type,
            'ordernum' => $this->_daoEmail->getMaxOrderNum($this->_user->orgId, $this->_user->userId) + 1
        );

        $ret = $this->_daoEmail->createEmail($params);

        if (!$ret) {
            return $this->json(false, $this->lang['create_mailbox_failure']);
        }

        return $this->json(true, $this->lang['create_mailbox_success']);
    }

    /**
     * 更新绑定邮箱
     */
    public function updateAction()
    {
        $post     = $this->_request->getPost();
        $address  = isset($post['address']) ? $post['address'] : null;
        $password = trim($post['password']);
        $port = !empty($post['port']) ? $post['port'] : null;

        if (!trim($post['host'])) {
            return $this->json(false, $this->lang['missing_email_host'], array('advance' => true));
        }

        if (!Oray_Function::isDomainName($post['host']) && !Oray_Function::isIp($post['host'])) {
            return $this->json(false, sprintf($this->lang['invalid_imap_host'], strtoupper($post['protocol'])), array('advance' => true));
        }

        if ($port != null) {
            if ($port <= 0 || $port > 65535) {
                return $this->json(false, sprintf($this->lang['invalid_imap_port'], strtoupper($post['protocol'])));
            }
        }

        $email = $this->_daoEmail->getEmailByAddress($this->_user->orgId, $this->_user->userId, $address);

        if (null === $email) {
            return $this->json(false, $this->lang['mailbox_not_exists']);
        }

        $checkPwd = $email->password;

        $isSsl = isset($post['isssl']) && $post['isssl'] == 1 ? 1 : 0;
        $type  = isset($post['type']) ? (int) $post['type'] : 0;

        $params = array(
            'protocol' => $post['protocol'],
            'host'  => $post['host'],
            'port'  => (int) $post['port'] > 0 ? (int) $post['port'] : null,
            'isssl' => $isSsl,
            'type'  => $type
        );

        if (!empty($password)) {
            $params['password'] = $password;
            $checkPwd = $password;
        }

        /**
         * 验证邮箱密码
         */
        if (!$this->_validMailbox($address, $checkPwd, $params['host'], $params['port'], (boolean) $isSsl, $post['protocol'])) {
            return $this->json(false);
        }

        $ret = $this->_daoEmail->updateEmail($this->_user->orgId, $this->_user->userId, $address, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['update_mailbox_failure']);
        }

        return $this->json(true, $this->lang['update_mailbox_success']);
    }

    /**
     * 邮箱排序
     */
    public function sortAction()
    {
        $address = $this->_request->getPost('address');
        $type    = $this->_request->getPost('type');

        $ret = $this->_daoEmail->sortEmail($this->_user->orgId, $this->_user->userId, $address, $type);

        $this->json($ret, null);
    }

    /**
     * 删除绑定邮箱
     */
    public function deleteAction()
    {
        $address = $this->_request->getPost('address');

        if (empty($address)) {
            return $this->json(false, $this->lang['invalid_email_address']);
        }

        $ret = $this->_daoEmail->deleteEmail($this->_user->orgId, $this->_user->userId, $address);

        // 整理email排序ID
        $this->_daoEmail->tidyEmailSort($this->_user->orgId, $this->_user->userId);

        if (!$ret) {
            $this->json(false, $this->lang['delete_mailbox_failure']);
        }

        $this->json(true, $this->lang['delete_mailbox_success']);
    }

    /**
     * 检查邮箱
     */
    /*
    public function statusAction()
    {
        $emails = $this->_daoEmail->getEmails(array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
        ));

        $result = array();
        foreach ($emails AS $email) {
            if ($email && $email->imapHost && $email->address && $email->password) {

                $port = empty($email->port) ? ($email->isSsl ? 993 : 143) : (int) $email->port;

                $errno  = null;
                $errstr = null;
                $result = '';
                $unread = 0;
                $socket = @fsockopen($email->imapHost, $port, $errno, $errstr, 10);

                if (!$socket) {
                    $this->json(false, null);
                }

                $result = fgets($socket);
                if (strpos($result, 'OK') === FALSE) {
                    //$this->json(false, $this->lang['mailbox_login_failure']);
                    continue ;
                }

                $tagId = 0;
                $tag = 'TAG' . (++$tagId);
                $login = "{$tag} LOGIN \"{$email->address}\" \"{$email->password}\"\r\n";
                fputs($socket, $login);
                $result = fgets($socket);
                if (strpos($result, 'OK') === FALSE) {
                    $this->json(false, $this->lang['mailbox_login_failure']);
                }

                $tag = 'TAG' . (++$tagId);
                $status = "{$tag} STATUS INBOX (UNSEEN)\r\n";
                fputs($socket, $status);
                $result = fgets($socket);

                if (!$result) {
                    $this->json(false, null);
                }

                $arr = array();
                preg_match_all('/UNSEEN (\d+)/', $result, $arr);
                if (!empty($arr[1])) {
                    $unread = (int) $arr[1][0];
                }

                fclose($socket);

                if (!$result) {
                    $this->json(false, $this->lang['select_inbox_failure']);
                }

                $result[$email->address] = (int) $unread;

                $this->_daoEmail->updateEmail($this->_user->orgId, $this->_user->userId, $email->address, array(
                    'lastcheckinfo' => implode("\n", array($unread, $email->lastMailId, $email->lastMailSubject, $email->lastMailFrom)),
                    'lastchecktime' => time()
                ));
            }
        }

        if (count($result)) {
            $this->json(true, null, $result);
        }

        $this->json(false, '', array('notbind' => 1));
    }
    */

    /**
     * 验证邮箱密码
     */
    private function _validMailbox($address, $password, $host, $port, $isSsl, $protocol)
    {
        if ($protocol == 'pop3') {
            $mail = new Zend_Mail_Protocol_Pop3();
        } else {
            $mail = new Zend_Mail_Protocol_Imap();
        }

        try {
            $mail->connect($host, $port, $isSsl);
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->json(false, $this->lang['mailbox_connect_error']);
            return false;
        }

        $isValid = true;
        try {
            $mail->login($address, $password);
        } catch (Zend_Mail_Protocol_Exception $e) {
            $isValid = false;
        }

        if (!$isValid) {
            try {
                list($uid, ) = explode('@', $address, 2);
                $mail->login($uid, $password);
                $isValid = true;
            } catch (Zend_Mail_Protocol_Exception $e) {
                $this->json(false, $this->lang['mailbox_login_error']);
                return false;
            }
        }

        if ($protocol == 'pop3' && $isValid) {
            $msg = null;
            $octects = null;
            $isValid = true;

            try {
                $mail->status($msg, $octects);
            } catch (Zend_Mail_Protocol_Exception $e) {
                $this->json(false, $this->lang['mailbox_login_error']);
                $isValid = false;
                return false;
            }
        }

        if (!$isValid) {
            $this->json(false, $this->lang['mailbox_login_error']);
            return false;
        }

        //$this->json(true);
        return true;
    }

    /**
     * 检查未读邮件
     */
    public function checkAction()
    {
        //$address = $this->_request->getQuery('address');

        $emails = $this->_daoEmail->getEmails(array(
            'orgid'   => $this->_user->orgId,
            'userid'  => $this->_user->userId
        ));

        $result = array();
        foreach ($emails as $email) {

            if (null != $email && $email->protocol == 'imap' && $email->host) {

                $port = $email->port ? $email->port : ($email->isSsl ? 993 : 143);

                $imap = new Zend_Mail_Protocol_Imap();

                try {
                    $connect = $imap->connect($email->host, $port, $email->isSsl);
                } catch (Zend_Mail_Protocol_Exception $e) {
                    continue ;
                }

                $login = $imap->login($email->address, $email->password);

                // 登录邮箱
                if (!$login) {
                    $result[$email->address] = array('msg' => sprintf($this->lang['mailbox_login_failure'], $email->address, $email->address));
                    continue ;
                }

                // 打开INBOX
                $ret = $imap->examineOrSelect();

                if (false !== $ret && isset($ret['recent'])) {
                    $email->unreadNum = (int) $ret['recent'];

                    // 查找新邮件
                    /*$records = $imap->search('RECENT');

                    if (false !== $records) {

                    }*/

                    $result[$email->address] = array('recent' => $email->unreadNum);
                    $this->_daoEmail->updateEmail($email->orgId, $email->userId, $email->address, array(
                        'lastcheckinfo' => implode("\n", array($email->unreadNum, $email->lastMailId, $email->lastMailSubject, $email->lastMailFrom)),
                        'lastchecktime' => time()
                    ));
                }

                $imap->logout();
            }
        }

        $this->json(true, null, $result);
    }

    /**
     * 登录邮箱
     */
    public function loginAction()
    {
        $address = $this->_request->getQuery('address');

        $email = $this->_daoEmail->getEmail(array(
            'orgid'   => $this->_user->orgId,
            'userid'  => $this->_user->userId,
            'address' => $address
        ));

        do {
            if (null !== $email) {
                if ($email->type == 2) {
                    break;
                }

                $email = $email->toArray();
                $conf = simplexml_load_file($this->options['data']['path'] . '/mail_login.xml');

                if ($email['type'] == 1) {
                    $arr = $conf->xpath('/maillogin/mail[@type="olivemail"]');
                } else {
                    $arr = $conf->xpath('/maillogin/mail[@domain="' . $email['domainname'] . '"]');
                }

                if (!$arr) {
                    break;
                }

                $obj = $arr[0];

                $action = $obj->url;
                $html   = '<form action="' . $action . '"';
                foreach ($obj->url->attributes() as $key => $val) {
                    $html .= " {$key}=\"{$val}\"";
                }
                $html .= '>';

                foreach ($obj->params->children() as $key => $param) {
                    $name  = $key;
                    $value = $param;
                    foreach ($param->attributes() as $k => $val) {
                        switch ($k) {
                            case 'referer':
                                $value = $email[(string) $val];
                                break;
                            case 'name':
                                $name = $val;
                                break;
                        }
                        if ($k == 'encrypt') {
                            $func = (string) $val;
                            if (function_exists($func)) {
                                $value = $func($val);
                            }
                        }
                    }
                    $html .= '<input type="hidden" name="'. $name .'" value="' . $value . '" />';
                }

                $html .= '</form>';
                $this->view->loginform = $html;

            } else {
                $email = array('address' => $address);
            }
        } while (false);

        $this->view->email = $email;
    }
}