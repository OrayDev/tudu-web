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
 * @see Tudu_User
 */
require_once 'Tudu/User.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Model_Tudu_Compose_Abstract extends Model_Abstract
{
    /**
     *
     * @var mixed
     */
    protected $_fromTudu = null;

    /**
     *
     * @var Tudu_User
     */
    protected $_user = null;

    /**
     *
     * @var int
     */
    protected $_time = null;

    /**
     *
     * @var array
     */
    protected static $_boards = array();

    /**
     *
     */
    public function __construct()
    {
        $this->_time = time();

        /* @var $user Tudu_User */
        $this->_user = Tudu_User::getInstance();

        // 缺少身份认证的用户
        if (!$this->_user->isLogined()) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Invalid user to execute current operation', Model_Tudu_Exception::INVALID_USER);
        }
    }

    /**
     *
     * @param $action
     * @param $params
     */
    public function execute($action, array $params)
    {
        $arr = explode('-', $action);
        foreach ($arr as $idx => $item) {
            if ($idx > 0) {
                $arr[$idx] = ucfirst($arr[$idx]);
            }
        }
        $action = implode('', $arr);

        if (!method_exists($this, $action)) {
            require_once 'Tudu/Model/Exception.php';
            throw new Tudu_Model_Exception("Undefined action named: {$action}");
        }

        $this->reset()->prepareTudu($action, $params[0]);

        try {

            $this->applyHooksFunc($action, 'filter', $params);

            call_user_func_array(array($this, $action), $params);

            $this->applyHooksFunc($action, 'action', $params);

        } catch (Exception $e) {

            $this->_catchException($action, $e, $params);

        }
    }

    /**
     *
     * @param string          $action
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareTudu($action, Model_Tudu_Tudu &$tudu)
    {
        $this->addFilter($action, array($this, 'filter'), Model_Abstract::HOOK_WEIGHT_MAX);
        $this->addAction($action, array($this, 'send'), 1);

        $extensions = $tudu->getExtensions();
        foreach ($extensions as $className => $extension) {
            $handler = $extension->getHandler($extension->getHandlerClass());

            $this->addFilter($action, array($handler, 'filter'));
            $this->addAction($action, array($handler, 'action'));
        }

        return $this;
    }

    /**
     * 图度参数过滤
     *
     * @param Model_Tudu_Tudu $tudu
     */
    abstract public function filter(Model_Tudu_Tudu &$tudu) ;

    /**
     * 发送操作
     *
     * @param Model_Tudu_Tudu $tudu
     * @throws Model_Tudu_Exception
     */
    abstract public function compose(Model_Tudu_Tudu &$tudu) ;

    /**
     * 发送图度
     *
     * @param Model_Tudu_Tudu $tudu
     * @throws Model_Tudu_Exception
     */
    abstract public function send(Model_Tudu_Tudu &$tudu) ;

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @throws Model_Tudu_Exception
     */
    protected function _createTudu(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        /* @var $daoAttach Dao_Td_Attachment_File */
        $daoAttach = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);

        if (!$tudu->tuduId) {
            $tudu->tuduId = Dao_Td_Tudu_Tudu::getTuduId();
        }

        if (!$tudu->postId) {
            $tudu->postId = Dao_Td_Tudu_Post::getPostId($tudu->tuduId);
        }

        $params = $tudu->getStorageParams();

        $attachments = $tudu->getAttachments();

        // 处理网盘附件
        $attachNum = 0;
        foreach ($attachments as $k => $attach) {
            if ($attach['isnetdisk']) {
                $fileId = $attach['fileid'];
                if (null !== $daoAttach->getFile(array('fileid' => $fileId))) {
                    $ret['attachment'][] = $fileId;
                    continue ;
                }

                $file = $daoFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $fileId));

                if (null === $file) {
                    continue ;
                }

                $fileId = $file->fromFileId ? $file->fromFileId : $file->attachFileId;

                $ret = $daoAttach->createFile(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'fileid'   => $fileId,
                    'orgid'    => $this->_user->orgId,
                    'filename' => $file->fileName,
                    'path'     => $file->path,
                    'type'     => $file->type,
                    'size'     => $file->size,
                    'createtime' => $this->_time
                ));

                if ($ret) {
                    $attachments[$k]['fileid'] = $fileId;
                } else {
                    unset($attachments[$k]);
                }
            }

            if ($attach['isattach']) {
                $attachNum ++;
            }
        }

        $params['attachnum'] = $attachNum;
        $params['isfirst']   = 1;

        $tuduId = $daoTudu->createTudu($params);

        if (!$tuduId) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu save failed', Model_Tudu_Exception::SAVE_FAILED);
        }

        $params['issend'] = 1;
        $postId = $daoPost->createPost($params);
        if (!$postId) {
            $daoTudu->deleteTudu($tuduId);

            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu content save failed', Model_Tudu_Exception::SAVE_FAILED);
        }

        $tudu->postId = $postId;

        foreach ($attachments as $attach) {
            $daoAttach->addPost($tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach']);
        }
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     * @throws Model_Tudu_Exception
     */
    protected function _updateTudu(Model_Tudu_Tudu &$tudu, $updates = null)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        /* @var $daoPost Dao_Td_Post_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        /* @var $daoAttach Dao_Td_Attachment_File */
        $daoAttach = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);

        $params = $tudu->getStorageParams();

        $attachments = $tudu->getAttachments();

        // 处理网盘附件
        $attachNum = 0;
        foreach ($attachments as $k => $attach) {
            if ($attach['isnetdisk']) {
                $fileId = $attach['fileid'];
                if (null !== $daoAttach->getFile(array('fileid' => $fileId))) {
                    $ret['attachment'][] = $fileId;
                    continue ;
                }

                $file = $daoFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $fileId));

                if (null === $file) {
                    continue ;
                }

                $fileId = $file->fromFileId ? $file->fromFileId : $file->attachFileId;

                $ret = $daoAttach->createFile(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'fileid'   => $fileId,
                    'orgid'    => $this->_user->orgId,
                    'filename' => $file->fileName,
                    'path'     => $file->path,
                    'type'     => $file->type,
                    'size'     => $file->size,
                    'createtime' => $this->_time
                ));

                if ($ret) {
                    $attachments[$k]['fileid'] = $fileId;
                } else {
                    unset($attachments[$k]);
                }
            }

            if ($attach['isattach']) {
                $attachNum ++;
            }
        }

        $params['attachnum'] = $attachNum;
        
        if (!empty($params['acceptmode'])) {
        	$params['accepttime'] = null;
        	$params['status']     = 0;
        }

        if (!$daoTudu->updateTudu($tudu->tuduId, $params)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu save failed', Model_Tudu_Exception::SAVE_FAILED);
        }

        if (!$daoPost->updatePost($tudu->tuduId, $tudu->postId, $params)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu content save failed', Model_Tudu_Exception::SAVE_FAILED);
        }

        // 添加附件关联
        $daoAttach->deletePost($tudu->tuduId, $tudu->postId);
        foreach ($attachments as $attach) {
            $daoAttach->addPost($tudu->tuduId, $tudu->postId, $attach['fileid'], (boolean) $attach['isattach']);
        }
    }

    /**
     *
     * @return array
     */
    protected function _getBoards()
    {
        if (empty(self::$_boards)) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

            self::$_boards = $daoBoard->getBoards(array('orgid' => $this->_user->orgId))->toArray('boardid');
        }

        return self::$_boards;
    }

    /**
     * 记录图度日志
     * @param Model_Tudu_Tudu $tudu
     */
    protected function _tuduLog($action, Model_Tudu_Tudu $tudu)
    {
        $detail = $this->_getLogDetail($tudu);
        $detail = serialize($detail);

        $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);
        return $daoLog->createLog(array(
            'orgid'      => $this->_user->orgId,
            'uniqueid'   => $this->_user->uniqueId,
            'operator'   => $this->_user->userName . ' ' . $this->_user->trueName,
            'logtime'    => time(),
            'targettype' => 'tudu',
            'targetid'   => $tudu->tuduId,
            'action'     => $action,
            'detail'     => $detail,
            'privacy'    => 0
        ));
    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    protected function _getLogDetail(Model_Tudu_Tudu $tudu)
    {
        $detail = array();
        $params = $tudu->getStorageParams();

        if (!$this->_fromTudu || $this->_fromTudu->isDraft) {
            foreach ($params as $key => $value) {
                if (!empty($value)) {
                    $detail[$key] = $value;
                }
            }

            return $detail;
        }

        $excepts = array('attach', 'uniqueid', 'status', 'poster', 'posterinfo', 'lastposter', 'issend');

        $tudu = $this->_fromTudu->toArray();
        $ret  = array();
        foreach ($params as $key => $val) {
            if (in_array($key, $excepts) || empty($val)) {
                continue ;
            }

            if ($key == 'to') {
                if (count($params[$key]) != count($tudu['accepter'])) {
                    $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                } elseif (is_array($params[$key])) {
                    foreach ($params[$key] as $k => $val) {
                        if (!in_array($k, $tudu['accepter'])) {
                            $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                        }
                    }
                }
                continue ;
            }

            if ($key == 'cc' || $key == 'bcc'/* || $key == 'reviewer'*/) {
                $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
            }

            if (array_key_exists($key, $tudu) && $params[$key] != $tudu[$key]) {
                $detail[$key] = $val;
            }
        }

        return $detail;
    }
}