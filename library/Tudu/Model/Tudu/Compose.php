<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Compose.php 2764 2013-03-01 10:13:53Z chenyongfa $
 */

/**
 * @see Tudu_Model_Abstract
 */
require_once 'Tudu/Model/Abstract.php';
/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * 图度发送业务流程封装
 * 本对象仅处理图度几个主要的发送流程，保存，发送，转发，其余类型的操作交由扩展实现
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Compose extends Tudu_Model_Tudu_Abstract
{

    /**
     * 版块列表
     *
     * @var array
     */
    protected $_boards;

    /**
     * 保存图度数据
     *
     * @param Tudu_Model_Tudu_Tudu $tudu
     */
    public function save(Tudu_Model_Tudu_Entity_Tudu $tudu, &$output)
    {

        $user  = self::getResource(self::RESOURCE_NAME_USER);
        $error = null;
        $code  = 0;

        $fromTudu = null;

        /* @var Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 创建权限
        if (!$user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('perm deny create tudu');
        }

        if ($tudu->tuduId) {
            $fromTudu = $daoTudu->getTuduById($user->uniqueId, $tudu->tuduId);

            // 已存在图度，需进行参数过滤
            do {
                // 不存在，或不是当前用户保存的图度
                if (null === $fromTudu || $fromTudu->uniqueId != $user->uniqueId) {
                    $error = 'tudu not exists';
                    break;
                }

                // 非草稿状态不能保存
                if (!$fromTudu->isDraft) {
                    $error = 'forbid save sent';
                    break;
                } else {
                    $tudu->setAttribute('createtime', time());
                }

                if (null !== $error) {
                    require_once 'Tudu/Model/Tudu/Exception.php';
                    throw new Tudu_Model_Tudu_Exception($error);
                }

                $tudu->setAttribute('postid', $fromTudu->postId);
            } while (false);

        } else {
            $tudu->setAttribute(array(
                'tuduid'     => Dao_Td_Tudu_Tudu::getTuduId(),
                'isdraft'    => true,
                'uniqueid'   => $user->uniqueId,
                'from'       => $user->userName . ' ' . $user->trueName,
                'email'      => $user->userName,
                'sender'     => $user->trueName,
                'createtime' => time()
            ));
        }

        // 处理扩展数据内容
        $extensions = $tudu->getExtensions();
        foreach ($extensions as $item) {
            if ($item instanceof Tudu_Model_Tudu_Entity_Extension_Flow) {
                $this->getExtension($item->getHandler())->prevSave($tudu, $item);
            }
        }

        if (null !== $fromTudu) {
            $tuduId = $this->updateTudu($tudu);
        } else {
            $tuduId = $this->createTudu($tudu);
        }

        foreach ($extensions as $name => $item) {
            if (!$item instanceof Tudu_Model_Tudu_Entity_Extension_Flow
               || $item instanceof Tudu_Model_App_Attend_Tudu_Apply) {
                $this->getExtension($item->getHandler())->onSave($tudu, $item);
            }
        }

        $tudu->setAttribute('tuduid', $tuduId);

        // 发送到当前用户草稿箱
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
        }

        $output['tuduid'] = $tudu->tuduId;

        return $tudu->tuduId;
    }

    /**
     * 发送图度
     *
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    public function send(Tudu_Model_Tudu_Entity_Tudu &$tudu, &$output)
    {
        $user = self::getResource(self::RESOURCE_NAME_USER);
        $error = null;
        $code  = 0;

        $isSender   = true;
        $isAccepter = false;
        $fromTudu   = null;

        $logDetail = array();
        $logAction = 'create';

        /* @var Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        if ($tudu->tuduId) {
            $fromTudu = $daoTudu->getTuduById($user->uniqueId, $tudu->tuduId);

            $isSender = true;

            do {
                if (null === $fromTudu) {
                    $error = 'tudu not exists';
                    break;
                }

                $isSender = $fromTudu->sender == $user->userName;

                // 草稿，权限上属新建操作
                if ($tudu->isDraft) {
                    if (!$tudu->uniqueId || !$user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
                        $error = 'perm deny create tudu';
                        break;
                    }

                // 编辑图度权限
                // 有权限 && (发起人 || 版主 || 分区负责人)
                } else {
                    if (!$user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU)) {
                        $error = 'perm deny create tudu';
                        break;
                    }

                    if (!$isSender) {
                        // 版主权限
                        $boards = $this->_getBoards($user->orgId);
                        $board  = $boards[$fromTudu->boardId];

                        $isModerator      = array_key_exists($user->userName, $board['moderators']);
                        $isSuperModerator = null;
                        // 不是版主，看看是不是分区负责人
                        if (!$isModerator && isset($boards[$board['parentid']])) {
                            $parentBoard = $boards[$board['parentid']];
                            $isSuperModerator = array_key_exists($user->userName, $parentBoard['moderators']);
                        }

                        if (!$isModerator && !$isSuperModerator) {
                            $error = 'perm deny create tudu';
                            break;
                        }
                    }

                    $logAction = 'update';
                }

            } while (false);

            if (null !== $error) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception($error);
            }

            $params = array(
                'stepid'     => $fromTudu->stepId,
                'postid'     => $fromTudu->postId,
                'from'       => $fromTudu->sender . ' ' . $fromTudu->from[0],
                'email'      => $fromTudu->from[0],
                'sender'     => $fromTudu->sender
            );

            if ($tudu->isDraft) {
                $params['lastmodify'] = implode(chr(9), array($user->uniqueId, time(), $user->trueName));
            } else {
                $params['createtime'] = time();
            }

            $tudu->setAttribute($params);

            $logDetail = $this->_getLogDetails($params, $fromTudu);
        } else {
            // 创建权限
            if (!$user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_TUDU)) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception('perm deny create tudu');
            }

            $tudu->setAttribute(array(
                'tuduid'     => Dao_Td_Tudu_Tudu::getTuduId(),
                'isdraft'    => true,
                'from'       => $user->userName . ' ' . $user->trueName,
                'email'      => $user->userName,
                'sender'     => $user->trueName,
                'createtime' => time()
            ));

            $logDetail = $this->_getLogDetails($tudu->getAttributes(), null);
        }

        // 处理扩展数据内容
        $extensions = $tudu->getExtensions();

        foreach ($extensions as $item) {
            if ($item instanceof Tudu_Model_Tudu_Entity_Extension_Flow) {
                $this->getExtension($item->getHandler())->prevSave($tudu, $item);
            }
        }

        // 保存图度数据
        if (null !== $fromTudu) {
            $tuduId = $this->updateTudu($tudu);
        } else {
            $tuduId = $this->createTudu($tudu);
        }

        foreach ($extensions as $item) {
            if ($item instanceof Tudu_Model_Tudu_Entity_Extension_Flow
                || $item instanceof Tudu_Model_App_Attend_Tudu_Apply)
            {
                $this->getExtension($item->getHandler())->onSave($tudu, $item);
            }
        }

        $output['tuduid'] = $tudu->tuduId;

        // 执行发送操作
        $this->sendTudu($tudu);
        foreach ($extensions as $item) {
            if ($item instanceof Tudu_Model_App_Attend_Tudu_Apply) {
                $this->getExtension($item->getHandler())->onSend($tudu, $item);
            }
        }

        $sqsParam = array(
            'tsid'        => $user->tsId,
            'tuduid'      => $tudu->tuduId,
            'from'        => $user->userName,
            'uniqueid'    => $user->uniqueId,
            'server'      => $_SERVER['HTTP_HOST'],
            'type'        => $tudu->type,
            'ischangedCc' => isset($fromTudu) && !$fromTudu->isDraft && $tudu->cc ? true : false
        );
        $httpsqs = self::getResource(self::RESOURCE_NAME_HTTPSQS);
        $httpsqs->put(implode(' ', array(
            'tudu',
            (isset($fromTudu) ? 'update' : 'create'),
            '',
            http_build_query($sqsParam)
        )), 'tudu');

        $this->createLog(Dao_Td_Log_Log::TYPE_TUDU, $tudu->tuduId, $logAction, $logDetail, 0);

        return $tuduId;
    }

    /**
     * 转发图度
     *
     * @param $tudu
     * @param $output
     */
    public function forward(Tudu_Model_Tudu_Entity_Tudu $tudu, &$output)
    {
        $user  = self::getResource(self::RESOURCE_NAME_USER);
        $error = null;
        $time  = time();

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        do {
            if (!$tudu->tuduId) {
                $error = 'tudu not exists';
                break;
            }

            $fromTudu = $daoTudu->getTuduById($user->uniqueId, $tudu->tuduId);

            $isSender   = $fromTudu->sender == $user->userName;
            $isAccepter = in_array($user->userName, $fromTudu->accepter);

            if (null === $fromTudu) {
                $error = 'tudu not exists';
                break;
            }

            // 转发权限
            if (!$user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
                $error = 'perm deny forward tudu';
                break;
            }

            // 图度组
            if ($fromTudu->isTuduGroup) {
                $error = 'deny forward tudugroup';
                break;
            }

            // 不是执行人
            if (!$isAccepter) {
                $error = 'forbid non accepter forward';
                break;
            }
        } while (false);

        if (null !== $error) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception($error);
        }

        // 转发同时修改部分图度属性
        $modify = false;
        if ($user->getAccess()->isAllowed(Tudu_Access::PERM_FORWARD_TUDU)) {
            if ($isSender) {
                $modify = true;
            } else {
                $boards = $this->_getBoards($user->orgId);
                $board  = $boards[$tudu->boardId];
                $isModerator = array_key_exists($this->_user->userId, $board['moderators']);
                $isSuperModerator = !empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']);

                if ($isModerator || $isSuperModerator) {
                    $modify = true;
                }
            }
        }

        $tuduParams = array(
            'lastposttime' => time(),
            'lastposter'   => $user->trueName,
            'lastforward'  => implode("\n", array($user->trueName, $time))
        );

        // 允许修改图度参数
        if ($modify) {
            $tuduParams = array_merge($tudu->getAttributes(), $tuduParams);
        }

        // 处理执行人
        $to = $tudu->to;
        foreach ($to as $k => $item) {
            if ($fromTudu->selfPercent < 100) {
                $to[$k]['percent'] = $fromTudu->selfPercent;
            }
        }

        $tudu->to = $to;

        $to = array();
        foreach ($fromTudu->to as $k => $item) {
            if ($k != $user->userName) {
                $to[] = $k . ' ' . $item[0];
            }
        }

        $tudu->to = array_merge(
            $tudu->to,
            Tudu_Tudu_Storage::formatRecipients(implode("\n", $to))
        );

        if ($fromTudu->selfPercent >= 100) {
            $tudu->to = array_merge($tudu->to, array(
                $user->userName => array('email' => $user->userName, 'truename' => $user->trueName, 'percent' => $fromTudu->selfPercent)
            ));
        }

        $tuduParams['to']  = Tudu_Model_Entity_Tudu::formatReceiver($tudu->to);
        $tuduParams['cc']  = Tudu_Model_Entity_Tudu::formatReceiver($tuduParams['cc']);
        $tuduParams['bcc'] = Tudu_Model_Entity_Tudu::formatReceiver($tuduParams['bcc']);

        // 更新图度
        if (!$daoTudu->updateTudu($tudu->tuduId, $tuduParams)) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save tudu failed');
        }

        // 发送转发回复
        $attachments = $tudu->getAttachments();
        $attachNum   = 0;
        foreach ($attachments as $attach) {
            if ($attach['isattach']) $attachNum ++;
        }
        $postId = $daoPost->createPost(array(
            'orgid'        => $tudu->orgId,
            'boardid'      => $tudu->boardId,
            'tuduid'       => $tudu->tuduId,
            'uniqueid'     => $user->uniqueId,
            'postid'       => Dao_Td_Tudu_Post::getPostId(),
            'poster'       => $user->trueName,
            'email'        => $user->userName,
            'content'      => $tudu->content,
            'attachnum'    => $attachNum,
            'isfirst'      => 0
        ));

        if (!$postId) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save post failed');
        }

        $daoPost->sendPost($tudu->tuduId, $postId);

        // 添加附件
        /* @var $daoFile Dao_Td_Attachment_File */
        $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
        foreach ($attachments as $attach) {
            $daoFile->addPost($tudu->tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach']);
        }

        // 发送图度
        $this->sendTudu($tudu);

        // 加上转发标签
        $daoTudu->addLabel($tudu->tuduId, $user->uniqueId, '^w');

        $sqsParam = array(
            'tsid'        => $user->tsId,
            'tuduid'      => $tudu->tuduId,
            'from'        => $user->userName,
            'uniqueid'    => $user->uniqueId,
            'server'      => $_SERVER['HTTP_HOST'],
            'type'        => $tudu->type,
            'ischangedCc' => $tudu->cc ? true : false
        );
        $httpsqs = self::getResource(self::RESOURCE_NAME_HTTPSQS);
        $httpsqs->put(implode(' ', array(
            'tudu',
            'update',
            '',
            http_build_query($sqsParam)
        )), 'tudu');

        return $tudu->tuduId;
    }

    /**
     * 保存图度数据
     *
     * @param $tudu
     */
    public static function updateTudu(Tudu_Model_Tudu_Entity_Tudu $tudu)
    {
        $params = $tudu->getAttributes();

        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);

        $params['to']  = !empty($params['to']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['to']) : null;
        $params['cc']  = !empty($params['cc']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['cc']) : null;
        $params['bcc'] = !empty($params['bcc']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['bcc']) : null;

        $ret = $daoTudu->updateTudu($tudu->tuduId, $params);

        if (!$ret) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save failed');
        }

        $attachment = $tudu->getAttachments();
        $attachNum   = 0;
        foreach ($attachment as $attach) {
            if ($attach['isattach']) $attachNum ++;
        }
        $post = array(
            'attachnum' => $attachNum
        );

        if (array_key_exists('content', $params)) {
            $post['content'] = $params['content'];
        }

        if (isset($params['lastmodify'])) {
            $post['lastmodify'] = $params['lastmodify'];
        }

        if (isset($params['createtime'])) {
            $post['createtime'] = $params['createtime'];
        }

        $ret = $daoPost->updatePost($tudu->tuduId, $tudu->postId, $post);
        if (!$ret) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save failed');
        }
        if (count($attachment) > 0) {
            foreach ($attachment as $attach) {
                $daoFile->addPost($tudu->tuduId, $tudu->postId, $attach['fileid'], (boolean) $attach['isattach']);
            }
        }

        return $tudu->tuduId;
    }

    /**
     * 创建图度
     *
     * @param $tudu
     */
    public static function createTudu(Tudu_Model_Tudu_Entity_Tudu &$tudu)
    {
        $params = $tudu->getAttributes();

        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);

        $params['to']  = !empty($params['to']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['to']) : null;
        $params['cc']  = !empty($params['cc']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['cc']) : null;
        $params['bcc'] = !empty($params['bcc']) ? Tudu_Model_Entity_Tudu::formatReceiver($params['bcc']) : null;

        $tuduId = $daoTudu->createTudu($params);

        if (!$tuduId) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save tudu failed');
        }

        $post = array(
            'orgid'        => $params['orgid'],
            'boardid'      => $params['boardid'],
            'tuduid'       => $tuduId,
            'uniqueid'     => $params['uniqueid'],
            'postid'       => Dao_Td_Tudu_Post::getPostId(),
            'poster'       => $params['poster'],
            'email'        => $params['email'],
            'content'      => !empty($params['content']) ? $params['content'] : '',
            'isfirst'      => 1,
            'issend'       => 1
        );

        $attachments = $tudu->getAttachments();
        $attachNum   = 0;
        foreach ($attachments as $attach) {
            if ($attach['isattach']) $attachNum ++;
        }
        $post['attachnum'] = $attachNum;

        $postId = $daoPost->createPost($post);
        if (!$postId) {
            $daoTudu->deleteTudu($tuduId);

            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Save tudu failed');
        }

        if (count($attachments) > 0) {
            $daoFile = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);
            foreach ($attachments as $attach) {
                $daoFile->addPost($tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach']);
            }
        }

        $output['tuduid'] = $tuduId;

        $tudu->setAttribute(array(
            'tuduid' => $tuduId,
            'postid' => $post['postid']
        ));

        return $tuduId;
    }

    /**
     * 发送图度
     *
     * @param $tudu
     */
    public function sendTudu(Tudu_Model_Tudu_Entity_Tudu &$tudu)
    {
        /* @var Tudu_Deliver $deliver */
        $deliver = Tudu_Tudu_Deliver::getInstance();
        $user    = self::getResource(self::RESOURCE_NAME_USER);
        $daoTudu  = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Group', Tudu_Dao_Manager::DB_TS);

        $recipients = $deliver->prepareRecipients($user->uniqueId, $user->userId, $tudu);

        // 公告，发送包含审批，则过滤接收人
        if ($tudu->type == 'notice' && $tudu->reviewer) {
            foreach ($recipients as $uniqueId => $recipient) {
                if (!array_key_exists($recipient['email'], $tudu->reviewer)) {
                    unset($recipients[$uniqueId]);
                }
            }
        }

        // 不含审批的修改，需要重新关联执行人
        if (!$tudu->isDraft && $tudu->type == 'task' && !$tudu->reviewer) {
            $accepters = $daoTudu->getAccepters($tudu->tuduId);
            $to        = $tudu->to;
            foreach ($accepters as $item) {
                list($email, ) = explode(' ', $item['accepterinfo'], 2);

                // 移除执行人角色，我执行标签
                if (!array_key_exists($email, $to)
                    && $daoGroup->getChildrenCount($tudu->tuduId, $item['uniqueid']) <= 0)
                {
                    $daoTudu->removeAccepter($tudu->tuduId, $item['uniqueid']);

                    $daoTudu->deleteLabel($tudu->tuduId, $item['uniqueid'], '^a');
                }
            }
        }

        // 发送图度到接收人
        $deliver->sendTudu($tudu, $recipients);

        // 计算进度
        $progress = $daoTudu->updateProgress($tudu->tuduId, null, null);

        // 自己发送的任务/会议，自动接受
        if ($tudu->type == 'task' || $tudu->type == 'meeting') {
            if ($tudu->to && array_key_exists($user->userName, $tudu->to) && !$tudu->acceptMode) {
                $daoTudu->updateTuduUser($tudu->tuduId, $user->uniqueId, array(
                    'accepttime' => time(),
                    'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
                ));

                $daoTudu->updateLastAcceptTime($tudu->tuduId);
            }
        }

        $daoTudu->addUser($tudu->tuduId, $user->uniqueId);

        // 移除草稿
        if ($tudu->isDraft) {
            $daoTudu->deleteLabel($tudu->tuduId, $user->uniqueId, '^r');
        }

        $daoTudu->addLabel($tudu->tuduId, $user->uniqueId, '^all');
        $daoTudu->addLabel($tudu->tuduId, $user->uniqueId, '^i');
        $daoTudu->addLabel($tudu->tuduId, $user->uniqueId, '^f');

        $tudu->setAttribute('recipient', $recipients);

        return true;
    }

    /**
     * 获取版块列表
     * 不进行格式化
     *
     * @param string $orgId
     */
    protected function _getBoards($orgId)
    {
        if (null === $this->_boards) {
            /* @var $daoBoard Dao_Td_Board_Board */
            $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);

            $boards  = array();
            $this->_boards = $daoBoard->getBoards(array('orgid' => $orgId));
        }

        return $this->_boards;
    }

    /**
     * 某些额外操作时可通过插件扩展
     * 函数名为扩展类名如  Compose->review 则查找注册插件 review，调用其 composeHandler 方法
     *
     * @param string $name
     * @param array  $args
     */
    public function __call($name, array $args)
    {
        try {
            $ext = $this->getExtension($name);

            call_user_func_array(array($ext, 'composeHandler'), $args);

        } catch (Tudu_Model_Exception $e) {
            throw $e;
        }
    }
}