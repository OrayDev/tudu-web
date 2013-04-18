<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Deliver.php 2751 2013-02-20 01:41:45Z cutecube $
 */

/**
 * 图度分发对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Deliver
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Deliver
{

    /**
     *
     * @var Tudu_Tudu_Manager
     */
    protected static $_instance;

    /**
     *
     * @var Tudu_AddressBook
     */
    protected $_addressBook;

    /**
     *
     * @var array
     */
    private $_arrDao = array();

    /**
     *
     * @var array
     */
    private $_typeLabels = array(
        'notice'  => '^n',
        'discuss' => '^d',
        'meeting' => '^m'
    );

    /**
     * 单例模式，隐藏构造函数
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function __construct()
    {
    }

    /**
     * 获取对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function newInstance()
    {
        return new self();
    }

    /**
     *
     * 准备接收人列表
     */
    public function prepareRecipients($uniqueId, $userId, $tudu, $action = Tudu_Tudu_Manager::ACTION_SEND)
    {
        $orgId       = $tudu->orgId;
        $addressBook = $this->getAddressBook();

        $recipients  = array();

        // 审批人
        if ($tudu->reviewer) {
            foreach ($tudu->reviewer as $key => $reviewers) {

                foreach ($reviewers as $item) {
                    $user = $addressBook->searchUser($orgId, $item['email']);
                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $item['email'], $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
                        }
                    }

                    $user['accepterinfo'] = $user['email'] . ' ' . $user['truename'];
                    $user['isreview']     = true;

                    $recipients[$user['uniqueid']] = $user;
                }
                // 顺序取首个
                break;
            }

        // 接收人
        } elseif ($tudu->to) {
            foreach ($tudu->to as $key => $item) {
                if (isset($item['groupid']) && $tudu->type == 'meeting') {

                    if (empty($item['groupid'])) {
                        continue ;
                    }

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    foreach ($users as $key => $user) {
                        $users[$key]['role']         = 'to';
                        $users[$key]['accepterinfo'] = $users[$key]['email'] . ' ' . $users[$key]['truename'];
                        $users[$key]['issender']     = $users[$key]['email'] == $tudu->sender;

                        $recipients[$key] = $users[$key];
                    }
                } else {
                    $user = $addressBook->searchUser($orgId, $item['email']);

                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $item['email'], $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
                        }
                    }

                    $user['role']         = 'to';
                    $user['accepterinfo'] = $user['email'] . ' ' . $user['truename'];
                    $user['issender']     = $item['email'] == $tudu->sender;

                    if (isset($item['percent'])) {
                        $user['percent'] = (int) $item['percent'];
                    }

                    $percent            = isset($item['percent']) ? (int) $item['percent'] : 0;
                    $user['tudustatus'] = $percent >= 100 ? 2 : ($percent == 0 ? 0 : 1);

                    $recipients[$user['uniqueid']] = $user;
                }
            }
        }

        if ($tudu->cc) {
            foreach ($tudu->cc as $key => $item) {
                if (isset($item['groupid'])) {

                    if (empty($item['groupid'])) {
                        continue ;
                    }

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    $recipients = array_merge($users, $recipients);

                } else {
                    $user = $addressBook->searchUser($orgId, $item['email']);

                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $item['email'], $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
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

                    if (empty($item['groupid'])) {
                        continue ;
                    }

                    if (0 === strpos($item['groupid'], 'XG')) {
                        $users = $addressBook->getGroupContacts($orgId, $uniqueId, $item['groupid']);
                    } else {
                        $users = $addressBook->getGroupUsers($orgId, $item['groupid']);
                    }

                    $recipients = array_merge($users, $recipients);

                } else {
                    $user = $addressBook->searchUser($orgId, $item['email']);

                    if (null === $user) {
                        $user = $addressBook->searchContact($uniqueId, $item['email'], $item['truename']);

                        if (null === $user) {
                            $user = $addressBook->prepareContact($item['email'], $item['truename']);
                        }
                    }

                    if (!isset($recipients[$user['uniqueid']])) {
                        $recipients[$user['uniqueid']] = $user;
                    }
                }
            }
        }

        /*if (!isset($recipients[$uniqueId])) {
            $recipients[$uniqueId] = array(
                'uniqueid' => $uniqueId,
                'role'     => 'from'
            );
        }*/

        return $recipients;
    }

    /**
     * 发送图度
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     * @param array $recipients
     * @return boolean
     */
    public function sendTudu($tudu, $recipients, $action = Tudu_Tudu_Manager::ACTION_SEND)
    {
        //list($userId, $domainName) = explode('@', $email);

        //$recipients = $this->prepareRecipients($uniqueId, $userId, $tudu);

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 移除原有审批
        if (!$tudu->isDraft && !$tudu->flowId) {
            $reviewers = $daoTudu->getUsers($tudu->tuduId, array(
                'labelid' => '^e'
            ));

            foreach ($reviewers as $reviewer) {
                $daoTudu->deleteLabel($tudu->tuduId, $reviewer['uniqueid'], '^e');
            }
        }

        $to = array();
        foreach ($recipients as $unId => $recipient) {
            if (!isset($recipient['accepterinfo']) && isset($recipient['email']) && isset($recipient['truename'])) {
                $recipient['accepterinfo'] = $recipient['email'] . ' ' . $recipient['truename'];
            }

            $params = $recipient;

            if (array_key_exists('percent', $params) || (!empty($params['role']) && $params['role'] == 'to')) {
                $params['percent'] = isset($params['percent']) ? (int) $params['percent'] : 0;
            }

            $labels = $daoTudu->addUser($tudu->tuduId, $recipient['uniqueid'], $params);

            if (false !== $labels) {
                if (is_string($labels) && !empty($recipient)) {
                    $daoTudu->updateTuduUser($tudu->tuduId, $recipient['uniqueid'], $recipient);
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
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^all');
                }

                // 图度箱
                if (!in_array('^i', $labels) && !in_array('^g', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^i');
                }

                // 相关
                if (empty($recipient['role']) || $tudu->uniqueId != $recipient['uniqueid']) {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^c');
                } else if ($tudu->type == 'task' && in_array('^c', $labels)) {
                    $daoTudu->deleteLabel($tudu->tuduId, $recipient['uniqueid'], '^c');
                }

                // 待办
                if ((!empty($recipient['role']) && empty($recipient['accepttime'])) || ($tudu->uqnieuId == $recipient['uniqueid'] && !$tudu->acceptTime)) {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^td');
                }

                // 我执行
                if (!empty($recipient['role']) && $recipient['role'] === 'to' && !in_array('^a', $labels) && $tudu->type == 'task') {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^a');
                }

                // 已发送
                if ($tudu->uniqueId == $recipient['uniqueid'] && !in_array('^f', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^f');
                }

                // 审批
                if (!empty($recipient['isreview']) && !in_array('^e', $labels)) {
                    $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], '^e');
                }

                if (isset($this->_typeLabels[$tudu->type])) {
                    $labelId = $this->_typeLabels[$tudu->type];
                    if (!in_array($labelId, $labels)) {
                        $daoTudu->addLabel($tudu->tuduId, $recipient['uniqueid'], $labelId);
                    }
                }
            }
        }

        $ret = $daoTudu->sendTudu($tudu->tuduId);

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onSend($tudu);
        }

        return $ret;
    }

    /**
     *
     * @param $tudu
     */
    public function removeAccepters(Tudu_Tudu_Storage_Tudu $tudu, array $excepts)
    {
        // 获取图度原有接收人
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $accepters = $daoTudu->getAccepters($tudu->tuduId);

        foreach ($accepters as $item) {
            if (!in_array($item['uniqueid'], $excepts)) {
                $daoTudu->removeAccepter($tudu->tuduId, $item['uniqueid']);
            }
        }

        return true;
    }

    /**
     * 移除执行人
     *
     * @param $tuduId
     * @param $uniqueId
     */
    public function removeAccepter($tuduId, $uniqueId)
    {
        return $this->getDao('Dao_Td_Tudu_Tudu')->removeAccepter($tuduId, $uniqueId);
    }

    /**
     *
     */
    public function saveDraft(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        // 发送到当前用户草稿箱子
        $labels = $daoTudu->addUser($tudu->tuduId, $tudu->uniqueId, array());

        if (false !== $labels) {
            if (is_string($labels)) {
                $labels = explode(',', $labels);
            } else {
                $labels = array();
            }

            // 添加到草稿箱
            if (!in_array('^r', $labels)) {
                $daoTudu->addLabel($tudu->tuduId, $tudu->uniqueId, '^r');
            }

            return true;
        }

        return false;
    }

    /**
     *
     * @return Tudu_AddressBook
     */
    public function getAddressBook()
    {
        if (null === $this->_addressBook) {
            $this->_addressBook = Tudu_AddressBook::getInstance();
        }

        return $this->_addressBook;
    }

    /**
     *
     * @param $className
     * @return Oray_Dao_Abstract
     */
    private function getDao($className)
    {
        return Tudu_Dao_Manager::getDao($className, Tudu_Dao_Manager::DB_TS);
    }

}