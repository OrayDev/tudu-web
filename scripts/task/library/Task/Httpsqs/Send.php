<?php
/**
 * Task_Httpsqs
 *
 * LICENSE
 *
 *
 * @category   Task_Httpsqs_Send
 * @package    Task_Httpsqs_Send
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Send.php 2733 2013-01-31 01:41:03Z cutecube $
 */

/**
 * 图度后台程序，处理图度发送后流程
 * * * 邮件外发
 * * * 最近联系人维护
 *
 * @category   Task_Httpsqs_Send
 * @package    Task_Httpsqs_Send
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Httpsqs_Send extends Task_Abstract
{
	/**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     *
     * @var Oray_Balancer_Abstract
     */
    protected $_balancer = null;

    /**
     *
     * @var array
     */
    protected $_tsDbs = array();

    /**
     *
     * @var 类型列表
     */
    protected $_typeNames = array(
        'tudu'    => '图度',
        'task'    => '图度',
        'discuss' => '讨论',
        'notice'  => '公告',
        'meeting' => '会议'
    );

	/**
     *
     */
    public function startUp()
    {
        foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_tsDbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
                continue ;
            }
            Tudu_Dao_Manager::setDb($key, Zend_Db::factory($item['adapter'], $item['params']));
        }

    	$this->_httpsqs = new Oray_Httpsqs(
            $this->_options['httpsqs']['host'],
            $this->_options['httpsqs']['port'],
            $this->_options['httpsqs']['charset'],
            $this->_options['httpsqs']['names']['send']
        );

        $this->_balancer = new Oray_Balancer_Rotation();
        // 准备邮件发送服务列表
        if (!empty($this->_options['smtp']['hosts'])) {
            $hosts = $this->_options['smtp']['hosts'];
            foreach ($hosts as $key => $host) {
                $this->_balancer->addItem($host, $key);
            }
        } else {
            $this->getLogger()->warn("Undefined smtp configs");
            exit;
        }
    }

    /**
     *
     */
    public function shutDown()
    {
        $this->_httpsqs->closeConnection();
    }

    /**
     * 执行
     */
    public function run()
    {
        do {
            $data = $this->_httpsqs->get($this->_options['httpsqs']['names']['send']);

            if (!$data || $data == 'HTTPSQS_GET_END') {
                break ;
            }

            list($module, $action, $sub, $query) = explode(' ', $data);

            if ($module != 'send') {
                $this->getLogger()->warn("Invalid param \"module\" values {$module}");
            }

            parse_str($query, $query);

            if (empty($query['tsid'])) {
                $this->getLogger()->warn("Missing param \"tsid\"");
                continue ;
            }

            if (!isset($this->_tsDbs['ts' . $query['tsid']])) {
                return ;
            }

            $tsId = $query['tsid'];
            Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_TS, $this->_tsDbs['ts' . $tsId]);

            switch ($action) {
                case 'tudu':
                    $this->sendTudu($query);
                    break;
                case 'reply':
                    $this->sendReply($query);
                    break;
                case 'meeting':
                    $this->sendMeeting($query);
                    break;
                case 'email':
                    $this->sendEmail($query);
                    break;
                default:
                    break;
            }
    	} while (true);
    }

    /**
     * 外发图度任务
     *
     * @param array $params
     */
    public function sendTudu($params)
    {
        if (empty($params['tuduid'])
            || empty($params['tsid'])
            || empty($params['uniqueid']))
        {
           return ;
        }

        $tuduId   = $params['tuduid'];
        $uniqueId = $params['uniqueid'];
        $tsId     = $params['tsid'];
        $to       = !empty($params['to']) ? explode(',', $params['to']) : null;

        /* @var $manager Tudu_Tudu_Manager */
        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $uniqueId);

        if (null == $tudu) {
            $this->getLogger()->warn("Tudu id:{$tuduId} is not exists");
            return ;
        }

        // 当前发送人信息
        $sender = $manager->getUser($tuduId, $uniqueId);

        // 获取接收人
        $receivers = $manager->getTuduUsers($tudu->tuduId);
        $emails = array();

        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        // 处理接收人数据
        foreach ($receivers as $receiver) {
            $info  = explode(' ', $receiver['accepterinfo'], 2);
            $email = $info[0];
            $name  = !empty($info[1]) ? $info[1] : null;
            $unId  = $receiver['uniqueid'];

            if ($name == null && $email) {
                $arr  = explode('@', $email);
                $name = array_shift($arr);
            }

            if (!$email && !$name) {
                continue ;
            }

            if (!empty($to) && !in_array($email, $to)) {
                continue ;
            }

            // 发送人联系人处理
            if (!$sender['isforeign']) {
                $condition = array('uniqueid' => $uniqueId, 'contactid' => $unId);
                $contact = $daoContact->getContact($condition);

                // 加入最近联系人
                if (!$email || (array_key_exists($email, $tudu->to) || array_key_exists($email, $tudu->cc)
                    || ($tudu->bcc && array_key_exists($email, $tudu->bcc)))) {
                    if (null == $contact) {
                        $contactId = $receiver['isforeign'] ? $receiver['uniqueid'] : Dao_Td_Contact_Contact::getContactId();
                        $params = array(
                            'contactid' => $unId,
                            'uniqueid'  => $uniqueId,
                            'fromuser'  => $receiver['isforeign'] ? 0 : 1,
                            'truename'  => $name,
                            'pinyin'    => Tudu_Pinyin::parse($name),
                            'email'     => $email,
                            'lastcontacttime' => time()
                        );

                        $contactId = $daoContact->createContact($params);
                        if (!$contactId) {
                            $data = serialize($params);
                            $this->getLogger()->warn("Create Contact failed:{$data}");
                        }

                    } else {
                        $ret = $daoContact->updateContact($contact->contactId, $uniqueId, array(
                            'lastcontacttime' => time()
                        ));

                        if (!$ret) {
                            $data = serialize(array('contactid' => $contact->contactId, 'uniqueid' => $uniqueId, 'lastcontacttime' => time()));
                            $this->getLogger()->warn("Update Contact failed:{$data}");
                        }
                        $contactId = $contact->contactId;
                    }
                }
            }

            if ($receiver['isforeign']) {
                $auth = $receiver['authcode'];

                if (Oray_Function::isEmail($info[0])) {
                    $array = array(
                        'address'  => $info[0],
                        'name'     => empty($info[1]) ? $info[1] : $info[0],
                        'authinfo' => '',
                        'url'      => 'http://'.$tudu->orgId.'.tudu.com/foreign/tudu?ts=' . $tsId . '&tid=' . $tudu->tuduId . '&fid=' . $unId
                    );

                    if ($auth) {
                        $array['authinfo'] = '<p style="margin:10px 0">打开任务链接后需要输入以下验证码：<strong style="color:#f00">'.$auth.'</strong></p>';
                    }

                    $emails[] = $array;
                }
            }
        }

        // 执行外发
        $tpl = $this->_options['data']['path'] . '/templates/tudu/mail_tudu_notify.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            $this->getLogger()->warn("Tpl file:\"mail_tudu_notify.tpl\" is not exists");
            return ;
        }

        // 公用信息
        $common = array(
            'subject'    => $tudu->subject,
            'sender'     => $tudu->from[0],
            'lastupdate' => date('Y-m-d H:i:s', $tudu->lastPostTime),
            'content'    => mb_substr(strip_tags($tudu->content), 0, 20, 'utf-8'),
            'type'       => $this->_typeNames[$tudu->type]
        );

        $mailTransport = $this->getMailTransport($this->_balancer->select());
        $template      = $this->_assignTpl(file_get_contents($tpl), $common);

        foreach ($emails as $email) {
            try {
                $mail = new Zend_Mail('utf-8');
                $mail->setFrom($this->_options['smtp']['from']['alert'], urldecode($this->_options['smtp']['fromname']));
                $mail->addTo($email['address'], $email['name']);
                $mail->addHeader('tid', $tudu->tuduId);
                $mail->setSubject("图度{$this->_typeNames[$tudu->type]}——" . $tudu->subject);
                $mail->setBodyHtml($this->_assignTpl($template, $email));
                $mail->send($mailTransport);
            } catch (Zend_Mail_Exception $ex) {
                $this->getLogger()->warn("[Failed] Email send type:{$this->_typeNames[$tudu->type]} TuduId:{$tuduId} retry\n{$ex}");
                continue ;
            }
        }

        $this->getLogger()->debug("Send Tudu id:{$tuduId} done");
    }

    /**
     * 外发回复
     *
     * @param array $params
     */
    public function sendReply($params)
    {
        if (empty($params['tuduid'])
            || empty($params['tsid'])
            || empty($params['uniqueid'])
            || empty($params['from'])
            || empty($params['content']))
        {
           return ;
        }

        $tuduId   = $params['tuduid'];
        $uniqueId = $params['uniqueid'];
        $tsId     = $params['tsid'];
        $to       = !empty($params['to']) ? explode(',', $params['to']) : null;
        $sender   = $params['from'];
        $content  = $params['content'];

        /* @var $manager Tudu_Tudu_Manager */
        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $uniqueId);

        if (null == $tudu) {
            $this->getLogger()->warn("Tudu id:{$tuduId} is not exists");
            return ;
        }

        // 获取接收人
        $receivers = $manager->getTuduUsers($tudu->tuduId);
        $emails = array();

        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        // 处理接收人数据
        foreach ($receivers as $receiver) {
            $info      = explode(' ', $receiver['accepterinfo'], 2);
            $email     = $info[0];
            $name      = !empty($info[1]) ? $info[1] : null;
            $contactId = $receiver['uniqueid'];

            if ($name == null && $email) {
                $arr  = explode('@', $email);
                $name = array_shift($arr);
            }

            if (!$email && !$name) {
                continue ;
            }

            if (!empty($to) && !in_array($email, $to)) {
                continue ;
            }

            if ($receiver['isforeign']) {
                $auth = $receiver['authcode'];

                if (Oray_Function::isEmail($email) && $uniqueId != $receiver['uniqueid']) {
                    $array = array(
	                    'address'  => $email,
                        'name'     => $name,
                        'authinfo' => '',
                        'url'     => 'http://'.$tudu->orgId.'.tudu.com/foreign/tudu?ts=' . $tsId . '&tid=' . $tudu->tuduId . '&fid=' . $receiver['uniqueid']
                    );

                    if ($auth) {
                        $array['authinfo'] = '<p style="margin:10px 0">打开任务链接后需要输入以下验证码：<strong style="color:#f00">'.$auth.'</strong></p>';
                    }

                    $emails[] = $array;
                }
            }
        }

        // 执行外发
        $tpl = $this->_options['data']['path'] . '/templates/tudu/mail_reply_notify.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            $this->getLogger()->warn("Tpl file:\"mail_reply_notify.tpl\" is not exists");
            return ;
        }

        // 公用信息
        $common = array(
            'subject'    => $tudu->subject,
            'sender'     => $sender,
            'lastupdate' => date('Y-m-d H:i:s', $tudu->lastPostTime),
            'content'    => mb_substr(strip_tags($content), 0, 20, 'utf-8'),
            'type'       => $this->_typeNames[$tudu->type]
        );

        $mailTransport = $this->getMailTransport($this->_balancer->select());
        $template      = $this->_assignTpl(file_get_contents($tpl), $common);

        foreach ($emails as $email) {
            try {
                $mail = new Zend_Mail('utf-8');
                $mail->setFrom($this->_options['smtp']['from']['alert'], urldecode($this->_options['smtp']['fromname']));
                $mail->addTo($email['address'], $email['name']);
                $mail->addHeader('tid', $tudu->tuduId);
                $mail->setSubject("图度{$this->_typeNames[$tudu->type]}——" . $tudu->subject . '[新回复]');
                $mail->setBodyHtml($this->_assignTpl($template, $email));
                $mail->send($mailTransport);
            } catch (Zend_Mail_Exception $ex) {
                $this->getLogger()->warn("[Failed] Email send type:{$this->_typeNames[$tudu->type]} TuduId:{$tuduId} retry\n{$ex}");
                continue ;
            }
        }

        $this->getLogger()->debug("Send Reply id:{$tuduId} done");
    }

    /**
     * 外发会议
     *
     * @param array $params
     */
    public function sendMeeting($params)
    {
       if (empty($params['tuduid'])
            || empty($params['tsid'])
            || empty($params['uniqueid'])
            || empty($params['from'])
            || empty($params['content'])
            || empty($params['location']))
        {
           return ;
        }

        $tuduId   = $params['tuduid'];
        $uniqueId = $params['uniqueid'];
        $tsId     = $params['tsid'];
        $to       = !empty($params['to']) ? explode(',', $params['to']) : null;
        $sender   = $params['from'];
        $content  = $params['content'];
        $location = $params['location'];

        /* @var $manager Tudu_Tudu_Manager */
        $manager = Tudu_Tudu_Manager::getInstance(Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS));

        $tudu = $manager->getTuduById($tuduId, $uniqueId);

        if (null == $tudu) {
            $this->getLogger()->warn("Tudu id:{$tuduId} is not exists");
            return ;
        }

        // 获取接收人
        $receivers = $manager->getTuduUsers($tudu->tuduId);
        $emails = array();

        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        // 处理接收人数据
        foreach ($receivers as $receiver) {
            $info      = explode(' ', $receiver['accepterinfo'], 3);
            $email     = $info[0];
            $name      = !empty($info[1]) ? $info[1] : null;
            $contactId = isset($info[2]) ? $info[2] : null;

            if ($name == null && $email) {
                $arr  = explode('@', $email);
                $name = array_shift($arr);
            }

            if (!$email && !$name) {
                continue ;
            }

            if (!empty($to) && !in_array($email, $to)) {
                continue ;
            }

            if ($receiver['isforeign']) {
                $auth = $receiver['authcode'];

                if (Oray_Function::isEmail($email) && $uniqueId != $receiver['uniqueid']) {
                    $array = array(
                       'address' => $email,
                        'name'    => $name,
	                    'authinfo'=> '',
                        'url'     => 'http://'.$tudu->orgId.'.com/foreign/tudu?ts=' . $tsId . '&tid=' . $tudu->tuduId . '&fid=' . $receiver['uniqueid']
                    );

                    if ($auth) {
                        $array['authinfo'] = '<p style="margin:10px 0">打开任务链接后需要输入以下验证码：<strong style="color:#f00">'.$auth.'</strong></p>';
                    }

                    $emails[] = $array;
                }
            }
        }

        // 执行外发
        $tpl = $this->_options['data']['path'] . '/templates/tudu/mail_meeting_notify.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            $this->getLogger()->warn("Tpl file:\"mail_meeting_notify.tpl\" is not exists");
            return ;
        }

        // 公用信息
        $common = array(
            'subject'    => $tudu->subject,
            'sender'     => $sender,
            'lastupdate' => date('Y-m-d H:i:s', $tudu->lastPostTime),
            'content'    => mb_substr(strip_tags($content), 0, 20, 'utf-8'),
            'type'       => $this->_typeNames[$tudu->type]
        );

        $mailTransport = $this->getMailTransport($this->_balancer->select());
        $template      = $this->_assignTpl(file_get_contents($tpl), $common);

        foreach ($emails as $email) {
            try {
                $mail = new Zend_Mail('utf-8');
                $mail->setFrom($this->_options['smtp']['from']['alert'], urldecode($this->_options['smtp']['fromname']));
                $mail->addTo($email['address'], $email['name']);
                $mail->addHeader('tid', $tudu->tuduId);
                $mail->setSubject("图度{$this->_typeNames[$tudu->type]}——" . $tudu->subject . '[会议提醒]');
                $mail->setBodyHtml($this->_assignTpl($template, $email));
                $mail->send($mailTransport);
            } catch (Zend_Mail_Exception $ex) {
                $this->getLogger()->warn("[Failed] Email send type:{$this->_typeNames[$tudu->type]} TuduId:{$tuduId} retry\n{$ex}");
                continue ;
            }
        }

        $this->getLogger()->debug("Send Meeting id:{$tuduId} done");
    }

    /**
     * 发送邮件
     *
     * @param array $params
     */
    public function sendEmail($params)
    {
        if (empty($params['emails'])
            || empty($params['content'])
            || empty($params['subject'])
            || empty($params['url'])
            || empty($params['sender'])
            || empty($params['type'])
            || empty($params['lastupdate'])
            || empty($params['tuduid']))
        {
            return ;
        }

        $tpl = $this->_options['data']['path'] . '/templates/tudu/rule_mail_notify.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            $this->getLogger()->warn("Tpl file:\"rule_mail_notify.tpl\" is not exists");
            return ;
        }

        $tuduId        = $params['tuduid'];
        $emails        = $params['emails'];
        $common        = $params;
        unset($common['emails']);
        unset($common['tuduid']);

        $mailTransport = $this->getMailTransport($this->_balancer->select());
        $template      = $this->_assignTpl(file_get_contents($tpl), $common);

        foreach ($emails as $email) {
            try {
                $mail = new Zend_Mail('utf-8');
                $mail->setFrom($this->_options['smtp']['from']['alert'], urldecode($this->_options['smtp']['fromname']));
                $mail->addTo($email);
                $mail->addHeader('tid', $tuduId);
                $mail->setSubject("邮件主题：" . $params['subject']);
                $mail->setBodyHtml($template);
                $mail->send($mailTransport);
            } catch (Zend_Mail_Exception $ex) {
                $this->getLogger()->warn("[Failed] Send rule email notify TuduId:{$tuduId} retry\n{$ex}");
                continue ;
            }
        }

        $this->getLogger()->debug("Send rule email notify id:{$tuduId} done");
    }

    /**
     * 获取邮件传输
     */
    public function getMailTransport($transport)
    {
        if (!$transport instanceof Zend_Mail_Transport_Interface) {
            if (isset($this->_options['smtp']['params'])) {
                $params = $this->_options['smtp']['params'];
            } else {
                $params = array();
            }

            $params['name'] = $transport;

            $transport = new Zend_Mail_Transport_Smtp($transport, $params);
        }

        return $transport;
    }

    /**
     * 解析模板
     *
     * @param $tpl
     * @param $data
     */
    public function _assignTpl($tpl, $data)
    {
    	$ret = $tpl;
        foreach ($data as $key => $val) {
            $ret = str_replace('{$' . $key . '}', $val, $ret);
        }
        return $ret;
    }
}