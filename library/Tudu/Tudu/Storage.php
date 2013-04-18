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
 * @version    $Id: Storage.php 2758 2013-02-27 06:15:56Z cutecube $
 */

/**
 * 图度数据储存对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Storage
{
    /**
     *
     * @var Tudu_Tudu_Storage
     */
    protected static $_instance;

    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     *
     * @var array
     */
    protected $_extension = array();

    /**
     *
     * @var array
     */
    private $_arrDao = array();

    /**
     * 单例模式，隐藏构造函数
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    protected function __construct($db = null)
    {
        if ($db instanceof Zend_Db_Adapter_Abstract) {
            $this->setDb($db);
        }
    }

    /**
     * 获取对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function getInstance($db = null)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($db);
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Tudu_Tudu_Manager
     */
    public static function newInstance($db = null)
    {
        return new self($db);
    }

    /**
     *
     * @param $db
     */
    public function setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
    }

    /**
     *
     * @param $params
     * @param $tudu
     */
    public function prepareTudu(array $params, Dao_Td_Tudu_Record_Tudu $tudu = null)
    {
        if (null !== $tudu) {

            $params['isdraft'] = $tudu->isDraft;
            $params['postid']  = $tudu->postId;
            $params['tuduid']  = $tudu->tuduId;
            $params['from']    = $tudu->from[3] . ' ' . $tudu->from[0];
            $params['sender']  = $tudu->sender;
            $params['stepid']  = $tudu->stepId;
            $params['type']    = $tudu->type;
            $params['flowid']  = $tudu->flowId;


            if (!$tudu->isDraft) {
                $params['boardid'] = $tudu->boardId;
            }

            if ($tudu->parentId) {
                $params['parentid'] = $tudu->parentId;
            }

            if ($tudu->rootId) {
                $params['rootid'] = $tudu->rootId;
            }

            //$params['lastpost'] = ;

            // 继承需要的字段
            if (!$tudu->isDraft) {
                // 抄送人，只添加
                if ($tudu->cc) {
                    $cc = !empty($params['cc']) ? $params['cc'] : array();
                    foreach ($tudu->cc as $key => $item) {

                        if (false !== strpos($key, '@')) {
                            $cc[$key] = array('email' => $key, 'truename' => $item[0]);
                        } else {
                            $cc[$key] = array('groupid' => $key, 'truename' => $item[0]);
                        }
                    }
                    $params['cc'] = $cc;
                }
                if ($tudu->bcc) {
                    $bcc = !empty($params['bcc']) ? $params['bcc'] : array();
                    foreach ($tudu->bcc as $key => $item) {

                        if (false !== strpos($key, '@')) {
                            $bcc[$key] = array('email' => $key, 'truename' => $item[0]);
                        } else {
                            $bcc[$key] = array('groupid' => $key, 'truename' => $item[0]);
                        }
                    }
                    $params['bcc'] = $bcc;
                }
            }

        } else {
            $params['tuduid'] = Dao_Td_Tudu_Tudu::getTuduId();
        }

        if (isset($params['vote'])) {
            if (!Tudu_Tudu_Extension::isRegistered('vote')) {
                Tudu_Tudu_Extension::registerExtension('vote', 'Tudu_Tudu_Extension_Vote');
            }
        }

        $tudu = new Tudu_Tudu_Storage_Tudu($params, $tudu);

        if ($tudu->type == 'task' && $tudu->to && is_array($tudu->to)) {
            $to = array();
            foreach ($tudu->to as $item) {
                foreach ($item as $val) {
                    $to[$val['email']] = array('email' => $val['email'], 'truename' => $val['truename']);
                    if (isset($val['percent'])) {
                        $to[$val['email']]['percent'] = $val['percent'];
                    }
                }
                break;
            }
            $tudu->setAttributes(array('to' => $to, 'stepto' => $tudu->to));
        }

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onPrepare($tudu, $params);
        }

        return $tudu;
    }

    /**
     *
     */
    public function saveTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        if ($tudu->isFromTudu()) {
            return $this->updateTudu($tudu);
        } else {
            return $this->createTudu($tudu);
        }
    }

    /**
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function createTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $params = $tudu->getAttributes();

        if (empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['type'])
            || empty($params['from'])
            || empty($params['poster'])
            || empty($params['uniqueid'])
            || empty($params['email'])
            || !array_key_exists('subject', $params)
            || !array_key_exists('content', $params)) {
            return false;
        }

        $params['postid'] = Dao_Td_Tudu_Post::getPostId($tudu->tuduId);

        $params['to']  = isset($params['to']) ? self::formatReceiver($params['to']) : null;
        $params['cc']  = isset($params['cc']) ? self::formatReceiver($params['cc']) : null;
        $params['bcc'] = isset($params['bcc']) ? self::formatReceiver($params['bcc']) : null;

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->preCreate($tudu);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');
        /* @var $daoTudu Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');

        $tuduId = $daoTudu->createTudu($params);

        if (!$tuduId) {
            return false;
        }

        $attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
                );
            }
            unset($params['attachment']);
        }

        $params['attachnum'] = count($attachment);

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

        $params['isfirst']   = 1;

        if ($params['type'] == 'task') {
            $params['stepid'] = '^head';
        }

        $postId = $daoPost->createPost($params);

        if (!$postId) {
            $daoTudu->deleteTudu($tuduId);

            return false;
        }

        if (!empty($attachment)) {
            $this->addAttachment($tuduId, $postId, $attachment);
        }

        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->postCreate($tudu);
        }

        return $tuduId;
    }

    /**
     *
     * @param $tudu
     */
    public function updateTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->preUpdate($tudu);
        }

        $params = $tudu->getAttributes();

        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $params['to']  = isset($params['to']) ? self::formatReceiver($params['to']) : null;
        $params['cc']  = isset($params['cc']) ? self::formatReceiver($params['cc']) : null;
        $params['bcc'] = isset($params['bcc']) ? self::formatReceiver($params['bcc']) : null;

        if (!empty($params['acceptmode'])) {
            $params['accepttime'] = null;
        }

        unset(
            $params['from'],
            $params['uniqueid'],
            $params['poster'],
            $params['posterinfo']
        );

        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        $postParams = array(
            'attachment' => !empty($params['attachment']) ? (array) $params['attachment'] : array(),
            'file'       => !empty($params['file']) ? (array) $params['file'] : array(),
            'header'     => array()
        );

        if (array_key_exists('content', $params)) {
            $postParams['content'] = $params['content'];
        }

        if (isset($params['lastmodify'])) {
            $postParams['lastmodify'] = $params['lastmodify'];
        }

        if (isset($params['createtime'])) {
            $postParams['createtime'] = $params['createtime'];
        }

        if (!$this->updatePost($tudu->tuduId, $tudu->postId, $postParams)) {
            return false;
        }

        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->postUpdate($tudu);
        }

        return true;
    }

    /**
     * 转发图度
     *
     */
    public function forwardTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $attrs = $tudu->getAttributes();

        $names = array();

        $count = 0;
        foreach ($attrs['to'] as $k => $item) {
            if ($count >= 3) {
                $names[] = '...';
                break;
            }

            $names[] = $item['truename'];
        }

        $params = array(
            'to' => self::formatReceiver($attrs['to'])
        );

        if (isset($attrs['endtime'])) {
            $params['endtime'] = $attrs['endtime'];
        }

        if (!empty($attrs['cc'])) {
            $params['cc'] = self::formatReceiver($attrs['cc']);
        }

        // 任务属性
        $cols = array('subject', 'privacy', 'password', 'priority', 'isauth', 'needconfirm', 'notifyall');
        foreach ($cols as $col) {
            if (isset($attrs[$col])) {
                $params[$col] = $attrs[$col];
            }
        }

        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        if (!$ret) {
            return false;
        }

        $header = array(
            'action'  => 'forward',
            'tudu-to' => implode(',', $names)
        );

        $postParams = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'header'     => $header,
            'content'    => $attrs['content'],
            'email'      => $attrs['email'],
            'poster'     => $tudu->poster,
            'postinfo'   => $tudu->posterInfo,
            'uniqueid'   => $tudu->uniqueId,
            'attachment' => !empty($attrs['attachment']) ? (array) $attrs['attachment'] : array(),
            'file'       => !empty($attrs['file']) ? (array) $attrs['file'] : array()
        );

        $postId = $this->createPost($postParams);

        $this->sendPost($tudu->tuduId, $postId);

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();

        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onForward($tudu);
        }

        return true;
    }

    /**
     * 转发图度
     *
     */
    public function inviteTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $attrs = $tudu->getAttributes();

        $names = array();

        $count = 0;
        foreach ($attrs['to'] as $k => $item) {
            if ($count >= 3) {
                $names[] = '...';
                break;
            }

            $names[] = $item['truename'];
        }

        $params = array(
            'to' => self::formatReceiver($attrs['to'])
        );

        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        if (!$ret) {
            return false;
        }

        $header = array(
            'action'  => 'forward',
            'tudu-to' => implode(',', $names)
        );

        $postParams = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'header'     => $header,
            'content'    => $attrs['content'],
            'email'      => $attrs['email'],
            'poster'     => $tudu->poster,
            'postinfo'   => $tudu->posterInfo,
            'uniqueid'   => $tudu->uniqueId,
            'attachment' => !empty($attrs['attachment']) ? (array) $attrs['attachment'] : array(),
            'file'       => !empty($attrs['file']) ? (array) $attrs['file'] : array()
        );

        $postId = $this->createPost($postParams);

        $this->sendPost($tudu->tuduId, $postId);

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onForward($tudu);
        }

        return true;
    }

    /**
     * 分工
     */
    public function divideTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $attrs = $tudu->getAttributes();

        $params = array();
        if (!empty($attrs['cc'])) {
            $params['cc'] = self::formatReceiver($attrs['cc']);
        }

        $params['lastposttime'] = time();

        if (count($params)) {
            /* @var $tuduDao Dao_Td_Tudu_Tudu */
            $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

            $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

            if (!$ret) {
                return false;
            }
        }

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onDivide($tudu);
        }

        return true;
    }

    /**
     * 申请审批
     *
     * @param $tudu
     */
    public function applyTudu(Tudu_Tudu_Storage_Tudu $tudu)
    {
        $attrs  = $tudu->getAttributes();
        $params = array();
        if (isset($attrs['endtime'])) {
            $params['endtime'] = $attrs['endtime'];
        }

        if (!empty($attrs['cc'])) {
            $params['cc'] = $attrs['cc'];
        }

        $names = array();

        $count = 0;
        foreach ($attrs['reviewer'] as $k => $item) {
            if ($count >= 3) {
                $names[] = '...';
                break;
            }

            $names[] = $item['truename'];
        }

        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        if (!empty($params)) {
            $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

            if (!$ret) {
                return false;
            }
        }

        $header = array(
            'action'        => 'apply',
            'tudu-reviewer' => implode(',', $names)
        );

        $postParams = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'header'     => $header,
            'content'    => $attrs['content'],
            'email'      => $attrs['email'],
            'poster'     => $tudu->poster,
            'postinfo'   => $tudu->posterInfo,
            'uniqueid'   => $tudu->uniqueId,
            'attachment' => !empty($attrs['attachment']) ? (array) $attrs['attachment'] : array(),
            'file'       => !empty($attrs['file']) ? (array) $attrs['file'] : array()
        );

        $postId = $this->createPost($postParams);

        $this->sendPost($tudu->tuduId, $postId);

        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onApply($tudu);
        }

        return true;
    }

    /**
     * 审批图度（同意/不同意）
     *
     * @param $tudu
     */
    public function reviewTudu(Tudu_Tudu_Storage_Tudu $tudu, $isAgree)
    {
        $extensions = Tudu_Tudu_Extension::getRegisteredExtensions();
        foreach ($extensions as $key) {
            Tudu_Tudu_Extension::getExtension($key)->onReview($tudu, $isAgree);
        }

        $attrs = $tudu->getAttributes();

        /* @var $tuduDao Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        $params['to'] = isset($attrs['to']) ? self::formatReceiver($attrs['to']) : null;
        $params['cc'] = isset($attrs['cc']) ? self::formatReceiver($attrs['cc']) : null;

        $params['stepid'] = $attrs['stepid'];

        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        if (!$ret) {
            return false;
        }

        $header = array(
            'action'         => 'review',
            'tudu-act-value' => $isAgree ? 1 : 0,
        );

        $headerKey = $tudu->reviewer ? 'tudu-reviewer' : 'tudu-to';
        $items     = $tudu->reviewer ? $tudu->reviewer : $tudu->to;
        $val       = array();
        if ($tudu->reviewer) {
            $items = $tudu->reviewer;
            $items = array_shift($items);
        } else {
            $items = $tudu->to;
        }

        foreach ($items as $item) {
            if (!empty($attrs['samereview'])) {
                break;
            }

            $val[] = $item['truename'];
        }

        if (!empty($val)) {
            $header[$headerKey] = implode(',', $val);
        }
        if ($tudu->type == 'notice' && empty($val)) {
            unset($header[$headerKey]);
        }

        $postParams = array(
            'orgid'      => $tudu->orgId,
            'boardid'    => $tudu->boardId,
            'tuduid'     => $tudu->tuduId,
            'header'     => $header,
            'content'    => isset($attrs['content']) ? $attrs['content'] : '',
            'poster'     => $tudu->poster,
            'postinfo'   => $tudu->posterInfo,
            'email'      => $attrs['email'],
            'uniqueid'   => $tudu->uniqueId,
            'attachment' => !empty($attrs['attachment']) ? (array) $attrs['attachment'] : array(),
            'file'       => !empty($attrs['file']) ? (array) $attrs['file'] : array()
        );

        $postId = $this->createPost($postParams);

        $this->sendPost($tudu->tuduId, $postId);

        return true;
    }

    /**
     * 创建回复
     *
     * @param $tuduId
     * @param $params
     */
    public function createPost(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['tuduid'])
            || empty($params['uniqueid'])) {
            return false;
        }

        if (!isset($params['postid'])) {
            $params['postid'] = Dao_Td_Tudu_Post::getPostId($params['tuduid']);
        }

        $attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
                );
            }
            unset($params['attachment']);
        }

        $params['attachnum'] = count($attachment);

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

        $params['isfirst'] = false;

        /* @var $postDao Dao_Td_Tudu_Post */
        $postDao = $this->getDao('Dao_Td_Tudu_Post');
        $postId = $postDao->createPost($params);

        if (!$postId) {
            return false;
        }

        /* @var $fileDao Dao_Td_Attachment_File */
        $daoFile = $this->getDao('Dao_Td_Attachment_File');

        if (!empty($attachment)) {
            foreach ($attachment as $attach) {
                $daoFile->addPost($params['tuduid'], $postId, $attach['fileid'], (boolean) $attach['isattach']);
            }
        }

        return $postId;
    }

    /**
     * 更新图度回复
     *
     * @param string $tuduId
     * @param string $postId
     * @param string $params
     */
    public function updatePost($tuduId, $postId, array $params)
    {
        $attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
                );
            }
            unset($params['attachment']);
        }

        $params['attachnum'] = count($attachment);

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

        /* @var $postDao Dao_Td_Tudu_Post */
        $daoPost = $this->getDao('Dao_Td_Tudu_Post');

        if (!$daoPost->updatePost($tuduId, $postId, $params)) {
            return false;
        }

        /* @var $fileDao Dao_Td_Attachment_File */
        $daoFile = $this->getDao('Dao_Td_Attachment_File');

        if (isset($attachment)) {
            $daoFile->deletePost($tuduId, $postId);

            foreach ($attachment as $attach) {
                $daoFile->addPost($tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach']);
            }
        }

        return true;
    }

    /**
     * 发送回复
     *
     */
    public function sendPost($tuduId, $postId)
    {
        return $this->getDao('Dao_Td_Tudu_Post')->sendPost($tuduId, $postId);
    }

    /**
     *
     * @param string $tuduId
     * @param string $postId
     * @param array  $attachments
     * @return boolean
     */
    public function addAttachment($tuduId, $postId, array $attachments)
    {
        $daoAttachment = $this->getDao('Dao_Td_Attachment_File');

        $attachnum = 0;
        foreach ($attachments as $attach) {
            if (false !== $daoAttachment->addPost($tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach'])) {
                $attachnum ++;
            }
        }

        return $attachnum == count($attachments);
    }

    /**
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function savePost(Tudu_Tudu_Storage_Tudu $tudu)
    {

    }

    /**
     * 格式化接收人格式
     *
     * @param string $recipients
     */
    public static function formatRecipients($recipients)
    {
        if (is_array($recipients)) {
            return $recipients;
        }

        $arr = explode("\n", $recipients);
        $ret = array();
        foreach ($arr as $item) {
            if (!trim($item)) {
                continue ;
            }

            list($key, $name) = explode(' ', $item, 2);
            if (false !== strpos($key, '@')) {
                $ret[$key] = array('email' => $key, 'truename' => $name);
            } else {
                $ret[$key] = array('groupid' => $key, 'truename' => $name);
            }
        }

        return $ret;
    }

    /**
     * 格式化审批人
     * @param string $reviewer
     */
    public static function formatReviewer($reviewer) {
        $arr  = explode("\n", $reviewer);
        $asyn = 1;
        $ret  = array();
        foreach ($arr as $item) {
            $item = trim($item);

            if (0 === strpos($item, '>') || 0 === strpos($item, '+')) {
                $asyn = $item == '+';
                continue ;
            }

            list ($userName, $trueName) = explode(' ', $item);
            if (!$asyn) {
                $ret[] = array(array('truename' => $trueName, 'email' => $userName));
            } else {
                end($ret);
                $last = key($ret);

                $ret[$last][] = array('truename' => $trueName, 'email' => $userName);
            }
        }

        return $ret;
    }

    /**
     *
     * @param array $recipients
     */
    public static function formatReceiver($recipients)
    {
        if (is_string($recipients)) {
            return $recipients;
        }

        if (!is_array($recipients) || !$recipients) {
            return null;
        }

        $ret = array();
        foreach ($recipients as $key => $item) {
            $ret[] = $key . ' ' . $item['truename'];
        }

        return implode("\n", $ret);
    }

    /**
     * 从Dao中转换
     *
     * @param array $recipients
     */
    public static function formatRecordRecipients($recipients)
    {
        $ret = array();
        foreach ($recipients as $k => $item) {
            $ret[] = $k . ' ' . $item[0];
        }

        return self::formatRecipients(implode("\n", $ret));
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