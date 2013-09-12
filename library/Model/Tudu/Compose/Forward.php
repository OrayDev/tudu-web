<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Model_Tudu_Compose_Abstract
 */
require_once 'Model/Tudu/Compose/Abstract.php';

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Compose_Forward extends Model_Tudu_Compose_Abstract
{
    /**
     * 当前操作人是否允许在转发时修改图度内容
     *
     * @var boolean
     */
    protected $_isModified = true;

    /**
     * 过滤转发条件
     * 1.当前用户具有图度转发权限
     * 2.图度必须存在且已被发送
     * 3.当前操作用户必须为图度执行人
     * 4.当前图度不能是图度组
     *
     * @see Model_Tudu_Compose_Abstract::filter()
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 权限
        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to forward tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        // 无效的图度
        if (!$tudu->tuduId) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        $this->_fromTudu = $daoTudu->getTuduById($this->_user->uniqueId, $tudu->tuduId);

        // 图度不存在或已被删除
        if (null === $this->_fromTudu) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Tudu not exists', Model_Tudu_Exception::TUDU_NOTEXISTS);
        }

        // 草稿
        if ($this->_fromTudu->isDraft) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Could not forward a draft tudu', Model_Tudu_Exception::TUDU_IS_DRAFT);
        }

        // 不是执行人
        if (!in_array($this->_user->userName, $this->_fromTudu->accepter)) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception('Not allow to forward tudu', Model_Tudu_Exception::PERMISSION_DENIED);
        }

        // 是图度组
        if ($this->_fromTudu->isTuduGroup) {
            $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

            if ($daoGroup->getChildrenCount($this->_fromTudu->tuduId, $this->_user->uniqueId)) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Could not forward Tudu Group', Model_Tudu_Exception::TUDU_IS_TUDUGROUP);
            }
        }

        // 是否允许修改
        $boards = $this->_getBoards();
        $board  = $boards[$this->_fromTudu->boardId];
        $isSender         = $this->_fromTudu->sender == $this->_user->userName;
        $isModerator      = array_key_exists($this->_user->userId, $board['moderators']);
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

        if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)
            || (!$isSender && !$isModerator && !$isSuperModerator))
        {
            $this->_isModified = false;
        }

        $tudu->flowId = $this->_fromTudu->flowId;

        $accepters = $daoTudu->getAccepters($tudu->tuduId);
        $to        = $tudu->to[0];

        foreach ($accepters as $accepter) {
            list($username, $truename) = explode(' ', $accepter['accepterinfo'], 2);

            if ((!isset($to[$username]) && $username != $this->_user->userName) || $accepter['percent'] >= 100) {
                $to[$username] = array(
                    'username' => $username,
                    'truename' => $truename,
                    'email'    => $username,
                    'uniqueid' => $accepter['uniqueid'],
                    'status'   => (int) $accepter['tudustatus'],
                    'percent'  => (int) $accepter['percent']
                );
            }
        }

        $tudu->to = array($to);
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::compose()
     */
    public function compose(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $user = Tudu_User::getInstance();

        $attrs   = $tudu->getStorageParams();

        if ($this->_isModified) {
            $params = array();

            foreach ($attrs as $key => $val) {
                if (in_array($key, array('content', 'attach', 'attachment', 'subject', 'flowid', 'from'))) {
                    continue ;
                }

                $params[$key] = $val;
            }

            if (!empty($attrs['stepid'])) {
                $params['stepid'] = $attrs['stepid'];
            }


            $time = time();
            $params['lastposttime'] = $time;
            $params['lastposter']   = $user->trueName;

            if (!$daoTudu->updateTudu($tudu->tuduId, $params)) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Tudu save failed', Model_Tudu_Exception::SAVE_FAILED);
            }

        } else {
            $params = array(
                'to'  => $attrs['to']
            );

            if (!empty($attrs['cc'])) {
                $params['cc'] = $attrs['cc'];
            }

            if (!empty($attrs['bcc'])) {
                $params['bcc'] = $attrs['bcc'];
            }

            if (!empty($attrs['stepid'])) {
                $params['stepid'] = $attrs['stepid'];
            }

            if (!$daoTudu->updateTudu($tudu->tuduId, $params)) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception('Tudu save failed', Model_Tudu_Exception::SAVE_FAILED);
            }
        }

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        /* @var $daoAttach Dao_Td_Attachment_File */
        $daoAttach = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);

        $toArr = array();
        foreach ($tudu->to as $sec) {
            foreach ($sec as $item) {
                $toArr[] = array('username' => $item['username'], 'truename' => $item['truename']);
            }
            break ;
        }

        $header = array(
            'action' => 'forward',
            'to'     => $toArr
        );

        if ($tudu->reviewer) {
            $header['reviewer'] = array();
            foreach ($tudu->reviewer as $sec) {
                foreach($sec as $item) {
                    $header['reviewer'][] = array('username' => $item['username'], 'truename' => $item['truename']);
                }
            }
        }

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

                $file = $daoFile->getFile(array('uniqueid' => $user->uniqueId, 'fileid' => $fileId));

                if (null === $file) {
                    continue ;
                }

                $fileId = $file->fromFileId ? $file->fromFileId : $file->attachFileId;

                $ret = $daoAttach->createFile(array(
                    'uniqueid'   => $user->uniqueId,
                    'fileid'     => $fileId,
                    'orgid'      => $user->orgId,
                    'filename'   => $file->fileName,
                    'path'       => $file->path,
                    'type'       => $file->type,
                    'size'       => $file->size,
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

        $postParams = array(
            'orgid'   => $tudu->orgId,
            'tuduid'  => $tudu->tuduId,
            'boardid' => $tudu->boardId,
            'postid'  => Dao_Td_Tudu_Post::getPostId($tudu->tuduId),
            'uniqueid' => $this->_user->uniqueId,
            'poster'  => $this->_user->trueName,
            'email'   => $this->_user->userName,
            'content' => $tudu->content,
            'attachnum' => $attachNum,
            'createtime' => time(),
            'header'  => $header
        );

        $postId = $daoPost->createPost($postParams);
        $daoPost->sendPost($tudu->tuduId, $postId);

        $attachments = $tudu->getAttachments();
        foreach ($attachments as $id => $attach) {
            $daoAttach->addPost($tudu->tuduId, $postId, $attach['fileid'], $attach['isattach']);
        }

        $this->_tuduLog('forward', $tudu);
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Compose_Abstract::send()
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        $user    = Tudu_User::getInstance();

        // 发送图度
        if ($this->_fromTudu->type == 'task' && !$tudu->reviewer && !$tudu->isDraft) {
            // 移除当前执行人
            $daoTudu->removeAccepter($tudu->tuduId, $this->_user->uniqueId);
            $daoTudu->deleteLabel($tudu->tuduId, $this->_user->uniqueId, '^a');
        }
        $daoTudu->addLabel($tudu->tuduId, $this->_user->uniqueId, '^w');
    }
}