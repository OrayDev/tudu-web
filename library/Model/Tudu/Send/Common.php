<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 *
 */
require_once 'Model/Tudu/Send/Interface.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Send_Common extends Model_Abstract implements Model_Tudu_Send_Interface
{

    /**
     *
     * @var array
     */
    protected $_options = array();

    /**
     *
     * @var Tudu_User
     */
    protected $_user = null;

    /**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     * Constructor
     */
    public function __construct(array $options = null)
    {
        if (!empty($options)) {
            $this->_options = $options;
        }

        $this->_user = Tudu_User::getInstance();
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Send_Interface::send()
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {
        $recipients = $this->_getRecipients($tudu);

        $this->_sendTudu($tudu, $recipients);

        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        if ($tudu->operation == 'send') {
            // 更新进度
            if ($tudu->flowId) {
                $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');
                if ($flow) {
                    $daoTudu->updateFlowProgress($tudu->tuduId, null, $flow->currentStepId);
                }
            } else {
                if (!$tudu->fromTudu || !$tudu->fromTudu->nodeType || $tudu->fromTudu->nodeType == 'leaf') {
                    $daoTudu->updateProgress($tudu->tuduId, $this->_user->uniqueId, null);
                }
            }

            if ($tudu->parentId) {
                $daoTudu->calParentsProgress($tudu->parentId);
            }

            $daoTudu->sendTudu($tudu->tuduId);
        }

        $daoTudu->markAllUnread($tudu->tuduId);
        $daoTudu->markRead($tudu->tuduId, $this->_user->uniqueId, true);
        $daoTudu->updateTudu($tudu->tuduId, array('sendstatus' => 2));

        $httpsqs = $this->_getHttpsqs();
        if ($httpsqs) {
            $sqsAction = $tudu->_fromTudu && !$tudu->_fromTudu->isDraft ? 'update' : 'create';
            $type      = $tudu->_fromTudu ? $tudu->fromTudu->type : $tudu->type;

            $sqsParam = array(
                'tsid'        => $this->_user->tsId,
                'tuduid'      => $tudu->tuduId,
                'from'        => $this->_user->userName,
                'uniqueid'    => $this->_user->uniqueId,
                'server'      => $_SERVER['HTTP_HOST'],
                'type'        => $type,
                'ischangedCc' => true
            );

            if ($tudu->operation == 'send' && $tudu->flowId && $sqsAction == 'create') {
                $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');

                if ($flow) {
                    $sqsParam['nstepid'] = $flow->currentStepId;
                    $sqsParam['flowid']  = $tudu->flowId;
                }
            }

            if ($tudu->operation == 'review') {
                $sqsAction = 'review';
                $sqsParam['stepid'] = $tudu->fromTudu->stepId;
                $sqsParam['agree']  = $tudu->agree;

                if ($tudu->flowId) {
                    $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');

                    if ($flow) {
                        $sqsParam['nstepid']     = $flow->currentStepId;
                        $sqsParam['flowid']      = $tudu->flowId;
                        $sqsParam['stepstatus']  = $flow->currentStepId != $tudu->fromTudu->stepId ? 1 : 0;
                    }
                }

                if ($tudu->type == 'notice' && $tudu->stepId = '^end') {
                    $sqsAction = 'create';
                }
            }

            if ($sqsAction != 'reply') {
                $httpsqs->put(implode(' ', array(
                    'tudu',
                    $sqsAction,
                    '',
                    http_build_query($sqsParam)
                )), 'tudu');
            }
        }
    }

    /**
     *
     */
    protected function _getHttpsqs()
    {
        if (null === $this->_httpsqs && isset($this->_options['httpsqs'])) {
            $config         = $this->_options['httpsqs'];
            $this->_httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
        }

        return $this->_httpsqs;
    }

    /**
     * 发送图度
     */
    protected function _sendTudu(Model_Tudu_Tudu &$tudu, array $recipients)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $isAccepted = false;
        $to         = $tudu->to;
        $timestemp  = time();

        if ($tudu->operation == 'send' && ($tudu->type == 'task' || $tudu->type == 'notice')) {
            $reviewers = $daoTudu->getUsers($tudu->tuduId, array(
                'labelid' => '^e'
            ));

            foreach ($reviewers as $reviewer) {
                $daoTudu->deleteLabel($tudu->tuduId, $reviewer['uniqueid'], '^e');
            }
        }

        foreach ($recipients as $unId => $recipient) {
            if (!is_array($recipient)) {
                continue ;
            }

            // 跳过已发送的外发执行人
            if (!empty($recipient['isforeign']) && !empty($to) && array_key_exists($recipient['email'], $to)) {
                continue ;
            }

            // 需要验证的外部访问人员
            if (!empty($recipient['isforeign'])) {
                $recipient['authcode'] = $tudu->isAuth ? Oray_Function::randKeys(4) : null;
            }

            if (!isset($recipient['accepterinfo']) && isset($recipient['email']) && isset($recipient['truename'])) {
                $recipient['accepterinfo'] = $recipient['email'] . ' ' . $recipient['truename'];
            }

            /*if ($tudu->flowId && isset($recipient['role']) && $recipient['role'] == 'to') {
                $recipients[$unId]['tudustatus'] = 1;
                $recipients[$unId]['percent']    = 0;
            }*/

            $params = $recipient;
            if (array_key_exists('percent', $params) /*|| (!empty($params['role']) && $params['role'] == 'to')*/) {
                $params['percent'] = isset($params['percent']) ? (int) $params['percent'] : 0;
            }

            $labels = $daoTudu->addUser($tudu->tuduId, $unId, $params);

            if (false !== $labels) {
                if (is_string($labels) && !empty($recipient)) {
                    if ($tudu->operation == 'forward') {
                        unset($params['percent'], $params['tudustatus']);
                    }

                    $daoTudu->updateTuduUser($tudu->tuduId, $unId, $params);
                }

                if (!empty($recipient['role']) && $recipient['role'] === 'to') {
                    $to[] = $unId;
                }

                if (is_string($labels)) {
                    $labels = explode(',', $labels);
                } else {
                    $labels = array();
                }

                // 所有图度标签
                if (!in_array('^all', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $unId, '^all');
                }

                // 图度箱
                if (!in_array('^i', $labels) && !in_array('^g', $labels))
                {
                    if (!isset($recipient['role']) || $recipient['role'] != 'from' || !$tudu->parentId) {
                        $daoTudu->addLabel($tudu->tuduId, $unId, '^i');
                    }
                }

                // 类型标签
                if ($tudu->type == 'notice' && !in_array('^n', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $unId, '^n');
                }

                if ($tudu->type == 'discuss' && !in_array('^d', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $unId, '^d');
                }

                if ($tudu->type == 'meeting' && !in_array('^m', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $unId, '^m');
                }

                // 我执行
                if (!empty($recipient['role']) && $recipient['role'] == 'to' && $tudu->type == 'task') {
                    if (!in_array('^a', $labels)) {
                        $daoTudu->addLabel($tudu->tuduId, $unId, '^a');
                    }

                    if (($tudu->flowId || $tudu->uniqueId == $unId) && !$tudu->acceptMode) {
                        $isAccepted = true;
                        // 更新最后接受时间
                        $daoTudu->updateTuduUser($tudu->tuduId, $unId, array('tudustatus' => 1, 'accepttime' => $timestemp));
                    }
                }

                // 审批
                if (!empty($recipient['isreview']) && !in_array('^e', $labels))
                {
                    if ($tudu->operation != 'review' || $recipient['uniqueid'] != $tudu->uniqueId) {
                        $daoTudu->addLabel($tudu->tuduId, $unId, '^e');
                    }
                }

                if (isset($this->_typeLabels[$tudu->type])) {
                    $labelId = $this->_typeLabels[$tudu->type];
                    if (!in_array($labelId, $labels)) {
                        $daoTudu->addLabel($tudu->tuduId, $unId, $labelId);
                    }
                }
            }
        }

        if ($tudu->type == 'task') {
            if ($isAccepted) {
                $daoTudu->updateLastAcceptTime($tudu->tuduId);
            } else {
                foreach ($recipients as $unId => $u) {
                    if (!empty($u['role'])) {
                        $daoTudu->addLabel($tudu->tuduId, $unId, '^td');
                    }
                }
            }
        }

        if (!$daoTudu->sendTudu($tudu->tuduId)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu send failed', Model_Tudu_Exception::SAVE_FAILED);
        }
    }

    /**
     * 付哦去图度发送人列表
     *
     * @param Model_Tudu_Tudu $tudu
     * @return array
     */
    protected function _getRecipients(Model_Tudu_Tudu &$tudu)
    {
        $uniqueId = $this->_user->uniqueId;
        $orgId    = $this->_user->orgId;

        require_once 'Tudu/AddressBook.php';
        /* @var $addressBook Tudu_AddressBook */
        $addressBook = Tudu_AddressBook::getInstance();

        $recipients = array();
        if ($tudu->reviewer) {
            foreach ($tudu->reviewer as $item) {

                if (!isset($item['email']) && !isset($item['username'])) {
                    continue ;
                }

                $userName = isset($item['email']) ? $item['email'] : $item['username'];
                if (empty($item['uniqueid'])) {
                    $user = $addressBook->searchUser($orgId, $userName);
                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $userName, $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($userName, $item['truename']);
                        }
                    }
                } else {
                    $user = $item;
                }

                $user['accepterinfo'] = $userName . ' ' . $user['truename'];
                $user['isreview']     = true;

                $recipients[$user['uniqueid']] = $user;

            }

            // 接收人
        } elseif ($tudu->to) {
            $arrayTo = $tudu->to;

            foreach ($arrayTo as $key => $item) {

                if (isset($item['groupid']) && $tudu->type == 'meeting') {

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    $to = array();
                    foreach ($users as $key => $user) {
                        $users[$key]['role']         = 'to';
                        $users[$key]['accepterinfo'] = $users[$key]['email'] . ' ' . $users[$key]['truename'];
                        $users[$key]['issender']     = $users[$key]['email'] == $tudu->sender;

                        $to[] = $users[$key]['accepterinfo'];

                        $recipients[$key] = $users[$key];
                    }

                    $tudu->to = implode("\n", $to);
                } else {

                    if (empty($item['uniqueid'])) {

                        if (empty($item['email']) && empty($item['username'])) {
                            continue ;
                        }

                        $email = isset($item['email']) ? $item['email'] : $item['username'];
                        $user = $addressBook->searchUser($orgId, $email);

                        if (null === $user) {
                            $trueName = isset($item['truename']) ? $item['truename'] : $email;
                            $user = $addressBook->searchContact($uniqueId, $email, $item['truename']);

                            if (null === $user) {
                                $user = $addressBook->prepareContact($email, $item['truename']);
                            }
                        }
                    } else {
                        $user = $item;
                    }

                    $userName = isset($user['username']) ? $user['username'] : $user['email'];

                    $user['role']         = 'to';

                    $user['accepterinfo'] = $userName . ' ' . $user['truename'];
                    $user['issender']     = $userName == $tudu->sender;

                    $percent            = isset($item['percent']) ? (int) $item['percent'] : 0;
                    $user['percent']    = $percent;
                    $user['tudustatus'] = $percent >= 100 ? 2 : ($percent == 0 ? 0 : 1);

                    $recipients[$user['uniqueid']] = $user;
                }
            }
        }

        // 有审批人的公告
        if ($tudu->type == 'notice' && $tudu->reviewer) {
            return $recipients;
        }

        if ($tudu->cc) {
            foreach ($tudu->cc as $key => $item) {
                if (isset($item['groupid'])) {

                    if (is_int($item['groupid']) || empty($item['groupid'])) {
                        continue ;
                    }

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    $recipients = array_merge($users, $recipients);

                } else {
                    $userName = isset($item['username']) ? $item['username'] : $item['email'];
                    $user = $addressBook->searchUser($orgId, $userName);

                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $userName, $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($userName, $item['truename']);
                        }
                    }

                    if (!isset($recipients[$user['uniqueid']])) {
                        $recipients[$user['uniqueid']] = $user;
                    }
                }
            }
        }

        if ($tudu->bcc) {
            foreach ($tudu->bcc as $key => $item) {
                if (isset($item['groupid'])) {

                    if (is_int($item['groupid']) || empty($item['groupid'])) {
                        continue ;
                    }

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    $recipients = array_merge($users, $recipients);

                } else {
                    $userName = isset($item['username']) ? $item['username'] : $item['email'];
                    $user = $addressBook->searchUser($orgId, $userName);

                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $userName, $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($userName, $item['truename']);
                        }
                    }

                    if (!isset($recipients[$user['uniqueid']])) {
                        $recipients[$user['uniqueid']] = $user;
                    }
                }
            }
        }

        return $recipients;
    }
}