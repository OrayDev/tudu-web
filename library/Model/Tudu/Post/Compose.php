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
 * @version    $Id: Tudu.php 2196 2012-10-10 01:48:56Z cutecube $
 */

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Post_Compose extends Model_Abstract
{

    /**
     *
     * @var array
     */
    protected $_boards = array();

    /**
     *
     * @var Dao_Td_Tudu_Record_Tudu
     */
    protected $_tudu   = null;

    /**
     *
     * @var Dao_Td_Tudu_Record_Post
     */
    protected $_fromPost = null;

    /**
     *
     * @var Tudu_User
     */
    protected $_user = null;

    /**
     *
     */
    public function __construct()
    {
        $this->_time = time();

        $this->addFilter('save', array($this, 'filter'), Model_Abstract::HOOK_WEIGHT_MAX);
        $this->addFilter('send', array($this, 'filter'), Model_Abstract::HOOK_WEIGHT_MAX);
        $this->addAction('send', array($this, 'pushBackgroundQueue'), 1);

        /* @var $this->_user Tudu_User */
        $this->_user = Tudu_User::getInstance();
    }

    /**
     *
     * @param Model_Tudu_Post $post
     * @throws Model_Tudu_Exception
     */
    public function filter(Model_Tudu_Post &$post)
    {
        // 缺少图度ID
        if (!$post->tuduId) {
            require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        $this->_user = Tudu_User::getInstance();
        // 没有权限
        if (!$this->_user->isLogined() || !$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_POST)) {
            require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Denied to do current action', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $this->_tudu = $daoTudu->getTuduById($this->_user->uniqueId, $post->tuduId);

        if (null === $this->_tudu || $this->_tudu->orgId != $this->_user->orgId) {
            require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        if ($this->_tudu->isDone) {
            require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Denied to do current action', Model_Tudu_Exception::TUDU_IS_DONE);
        }

        $isReceiver   = ($this->_user->uniqueId == $this->_tudu->uniqueId) && count($this->_tudu->labels);
        $isAccepter   = in_array($this->_user->userName, $this->_tudu->accepter, true);
        $isSender     = in_array($this->_tudu->sender, array($this->_user->userName, $this->_user->account));

        // 编辑已存在回复
        if ($post->postId) {
            /* @var $daoPost Dao_Td_Tudu_Post */
            $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

            $this->_fromPost = $daoPost->getPost(array('tuduid' => $post->tuduId, 'postid' => $post->postId));

            if (null === $this->_fromPost) {
                require 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Post not exists', Model_Tudu_Exception::POST_NOTEXISTS);
            }

            // 编辑回复权限
            if ($this->_fromPost->uniqueId != $this->_user->uniqueId) {
                $boards = $this->_getBoards();
                $board  = $boards[$this->_tudu->boardId];

                if (!array_key_exists($this->_user->userId, $board['moderators'])) {
                    require 'Model/Tudu/Exception.php';
                    throw new Model_Tudu_Exception('Denied to do current action', Model_Tudu_Exception::PERMISSION_DENIED);
                }
            }
        } else {

            /*if (!$isReceiver) {
             require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Denied to do current action', Model_Tudu_Exception::PERMISSION_DENIED);
            }*/
        }
    }


    /**
     *
     * @param Model_Tudu_Post $post
     */
    public function send(Model_Tudu_Post &$post)
    {

        if (!$post->content) {
            require 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Missing parameter "content" for post', Model_Tudu_Exception::MISSING_PARAMETER);
        }

        $this->save($post);

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        /* @var $daoTudu Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        // 未读
        if (!$this->_fromPost || !$this->_fromPost->isSend) {
            $daoTudu->markAllUnread($this->_tudu->tuduId);
        }

        if ($this->_tudu->isRead) {
            $daoTudu->markRead($this->_tudu->tuduId, $this->_user->uniqueId, true);
        }

        /* @var $daoLog Dao_Td_Log_Log */
        $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);

        $tuduPercent = null;
        $flowPercent = null;
        if ($post->percent > 0 && $this->_tudu->selfPercent < 100) {

            if ($this->_tudu->flowId) {
                $tuduPercent = $daoTudu->updateFlowProgress($this->_tudu->tuduId, $this->_tudu->uniqueId, $this->_tudu->stepId, $post->percent,  $flowPercent);
            } else {
                $tuduPercent = $daoTudu->updateProgress($this->_tudu->tuduId, $this->_tudu->uniqueId, $post->percent);
            }

            if (!$this->_fromPost || !$this->_fromPost->isSend || $this->_fromTudu->selfPercent != $post->percent) {
                // 添加操作日志
                $daoLog->createLog(array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $this->_user->uniqueId,
                    'operator'   => $this->_user->userName . ' ' . $this->_user->trueName,
                    'logtime'    => time(),
                    'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                    'targetid'   => $this->_tudu->tuduId,
                    'action'     => Dao_Td_Log_Log::ACTION_TUDU_PROGRESS,
                    'detail'     => serialize(array('percent' => $tuduPercent, 'elapsedtime' => $this->_tudu->elapsedTime + (int) $post->elapsedtime)),
                    'privacy'    => 0
                ));
            }
        }

        // 自动完成
        if ($post->percent && (($tuduPercent >= 100 && null === $flowPercent) || ($flowPercent >= 100))) {
            if (in_array($this->_tudu->sender, array($this->_user->userName, $this->_user->account)) || !$this->_tudu->needConfirm) {
                $daoTudu->doneTudu($this->_tudu->tuduId, true, 0);

                // 添加操作日志
                $daoLog->createLog(array(
                    'orgid'      => $this->_user->orgId,
                    'uniqueid'   => $this->_user->uniqueId,
                    'operator'   => $this->_user->userName . ' ' . $this->_user->trueName,
                    'logtime'    => time(),
                    'targettype' => Dao_Td_Log_Log::TYPE_TUDU,
                    'targetid'   => $this->_tudu->tuduId,
                    'action'     => Dao_Td_Log_Log::ACTION_TUDU_PROGRESS,
                    'detail'     => serialize(array('percent' => $tuduPercent, 'elapsedtime' => $this->_tudu->elapsedTime + (int) $post->elapsedTime)),
                    'privacy'    => 0
                ));

                // 添加到发起人图度箱 -- 待确认中
            } else {
                /* @var $addressBook Tudu_AddressBook */
                $addressBook = Tudu_AddressBook::getInstance();
                $sender = $addressBook->searchUser($this->_user->orgId, $this->_tudu->sender);
                $daoTudu->addLabel($this->_tudu->tuduId, $sender['uniqueid'], '^i');
            }
        }

        // 计算父级图度进度  及 图度组达到100%时，确认
        if ($this->_tudu->parentId) {
            $parentPercent = $daoTudu->calParentsProgress($this->_tudu->parentId);

            if ($parentPercent >= 100) {
                $sendParam['confirm'] = true;
            }
        }

        // 发送回复
        $daoPost->sendPost($this->_tudu->tuduId, $post->postId);

        // 统计时间
        if ($post->percent) {
            $daoTudu->calcElapsedTime($this->_tudu->tuduId);
        }

        // 周期任务
        if ($post->percent && $this->_tudu->cycleId && $tuduPercent >= 100) {
            $daoCycle = $this->getDao('Dao_Td_Tudu_Cycle');
            $cycle = $daoCycle->getCycle(array('cycleid' => $this->_tudu->cycleId));

            if ($cycle->count == $this->_tudu->cycleNum) {
                $sendParam['cycle'] = true;
            }
        }

        if ($this->_tudu->flowId && $tuduPercent >= 100) {
            $this->addAction('send', array($this, 'tuduFlowStepDone'), 1);
        }
    }

    /**
     *
     * @param Model_Tudu_Post $post
     */
    public function save(Model_Tudu_Post &$post)
    {
        $time = time();

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        $params = array(
            'content'    => $post->content
        );

        if (null !== $post->percent) {
            $params['percent'] = $post->percent;
        }

        if (null !== $post->elapsedTime) {
            $params['elapsedtime'] = $post->elapsedTime;
        }

        if (null === $this->_fromPost) {

            $post->postId = Dao_Td_Tudu_Post::getPostId($this->_tudu->tuduId);
            $params['postid']   = $post->postId;
            $params['orgid']    = $this->_tudu->orgId;
            $params['boardid']  = $this->_tudu->boardId;
            $params['tuduid']   = $this->_tudu->tuduId;
            $params['uniqueid'] = $this->_user->uniqueId;
            $params['email']    = $this->_user->userName;
            $params['poster']   = $this->_user->trueName;
            $params['posterinfo'] = $this->_user->position;

            if (!$daoPost->createPost($params)) {
                require 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Save post failed', Model_Tudu_Exception::PERMISSION_DENIED);
            }

        } else {
            // 增加最后编辑信息
            if ($this->_fromPost->isSend) {
                $params['lastmodify'] = implode(chr(9), array($this->_user->uniqueId, $time, $this->_user->trueName));
            } else {
                $params['createtime'] = $time;
            }

            if (!$daoPost->updatePost($this->_tudu->tuduId, $this->_fromPost->postId, $params)) {
                require 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Save post failed', Model_Tudu_Exception::PERMISSION_DENIED);
            }
        }

        $attachments = $post->getAttachments();
        if (count($attachments)) {

            /* @var $daoFile Td_Attachment_File */
            $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
            /* @var $daoNdFile Td_Netdisk_File */
            $daoNdFile = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);

            foreach ($attachments as $attach) {
                if ($attach['isnetdisk']) {
                    $fileId = $attach['fileid'];
                    if (null !== $daoFile->getFile(array('fileid' => $fileId))) {
                        $ret['attachment'][] = $fileId;
                        continue ;
                    }

                    $file = $daoNdFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $fileId));

                    if (null === $file) {
                        continue ;
                    }

                    $fileId = $file->fromFileId ? $file->fromFileId : $file->attachFileId;

                    $ret = $daoFile->createFile(array(
                        'uniqueid'   => $this->_user->uniqueId,
                        'fileid'     => $fileId,
                        'orgid'      => $this->_user->orgId,
                        'filename'   => $file->fileName,
                        'path'       => $file->path,
                        'type'       => $file->type,
                        'size'       => $file->size,
                        'createtime' => $time
                    ));

                    if (!$ret) {
                        continue ;
                    }

                    $attach['fileid'] = $fileId;
                }

                $daoFile->addPost($this->_tudu->tuduId, $post->postId, $attach['fileid'], $attach['isattach']);
            }
        }

        $updates = array();
        if (null !== $this->_fromPost) {
        $arrFromPost = $this->_fromPost->toArray();
        foreach ($params as $key => $val) {
            if (in_array($key, array('file', 'attachment'))) {
                continue ;
            }

            if ($val != $arrFromPost[$key]) {
                $updates[$key] = $val;
            }
        }
        } else {
            $updates = $params;
        }

        /* @var $daoLog Dao_Td_Log_Log */
        $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);
        $daoLog->createLog(array(
            'orgid'      => $this->_user->orgId,
            'uniqueid'   => $this->_user->uniqueId,
            'operator'   => $this->_user->userName . ' ' . $this->_user->trueName,
            'logtime'    => $time,
            'targettype' => Dao_Td_Log_Log::TYPE_POST,
            'targetid'   => $post->postId,
            'action'     => $this->_fromPost ? Dao_Td_Log_Log::ACTION_CREATE : Dao_Td_Log_Log::ACTION_UPDATE,
            'detail'     => serialize($updates),
            'privacy'    => 0
        ));
    }

    /**
     *
     * @param Model_Tudu_Post $post
     */
    public function pushBackgroundQueue(Model_Tudu_Post &$post)
    {

    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function tuduFlowStepDone()
    {
        /**
         * @see Model_Tudu_Flow
         */
        require_once 'Model/Tudu/Tudu.php';
        /**
         * @see Model_Tudu_Flow
         */
        require_once 'Model/Tudu/Flow-delete.php';

        $to = array();
        foreach ($this->_tudu->to as $item) {
            $to[$item[3]] = array('email' => $item[3], 'truename' => $item[0]);
        }

        $tudu = new Model_Tudu_Tudu(array(
            'from'   => array('email' => $this->_tudu->from[3], 'truename' => $this->_tudu->from[0]),
            'to'     => $to,
            'tuduid' => $this->_tudu->tuduId,
            'stepid' => $this->_tudu->stepId,
            'parentid' => $this->_tudu->parentId,
            'flowid'   => $this->_tudu->flowId
        ));

        $flow = new Model_Tudu_Flow();

        $flow->flowTo($tudu);
    }

    /**
     *
     * @return array
     */
    protected function _getBoards()
    {
        if (empty($this->_boards)) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

            $this->_boards = $daoBoard->getBoards(array('orgid' => $this->_user->orgId))->toArray('boardid');
        }

        return $this->_boards;
    }
}