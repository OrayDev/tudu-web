<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: PostController.php 2721 2013-01-28 02:01:39Z cutecube $
 */


/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class PostController extends TuduX_Controller_OpenApi
{
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 获取图度回复列表
     */
    public function listAction()
    {
        $tuduId = $this->_request->getQuery('tuduid');
        $offset = (int) $this->_request->getQuery('offset', 0);
        $limit  = (int) $this->_request->getQuery('limit', 20);
        $isHtml = (boolean) $this->_request->getQuery('ishtml');    // 是否直接输出html的图度内容

        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);

        // 获取回复内容
        $tudu    = $daoTudu->getTuduById($this->_user->uniqueId, $tuduId);
        if (null === $tudu) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Tudu not exists or had been deleted';
            return ;
        }

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        /* @var $daoBoard Dao_Td_Board_Board */
        $daoBoard = Tudu_Dao_Manager::getDao('Dao_Td_Board_Board', Tudu_Dao_Manager::DB_TS);
        // 读取板块分区信息
        $boards = $daoBoard->getBoards(array(
            'orgid'   => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId
        ), null, 'ordernum DESC')->toArray('boardid');
        $board = $boards[$tudu->boardId];

        $condition = array(
            'tuduid'  => $tuduId,
            'isfirst' => 0
        );

        // 获取回复内容
        $posts = $daoPost->getTuduPosts($condition, 'createtime DESC', $offset, $limit)->toArray();

        // 回复的相关权限
        $postAccess = array(
            'modify'    => (int) ((!$board['protect'] && $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_POST)) && !$tudu->isDone),
            'delete'    => (int) ((!$board['protect'] && $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_DELETE_POST)) && !$tudu->isDone),
            'reply'     => $tudu->isDone ? 0 : 1,
            'reference' => $tudu->isDone ? 0 : 1
        );
        // 是否发起人
        $isSender = in_array($tudu->sender, array($this->_user->address, $this->_user->userName), true);
        // 当前版块版主
        $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
        // 上级分区负责人
        $isSuperModerator = (!empty($board['parentid']) && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));
        // 是否有修改图度的权限
        $isModify = $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_UPDATE_TUDU) && ($isSender || $isModerators || $isSuperModerator) && !$tudu->isDone;

        foreach ($posts as $key => $post) {
            // 公告过滤不可见的回复
            if ($tudu->type == 'notice' && !$isModify && !$post['isfirst'] && !in_array('^v', $tudu->labels) && !in_array('^e', $tudu->labels)) {
                unset($posts[$key]);
                continue;
            }

            // 权限
            $posts[$key]['permission'] = array(
                'modify'    => (int) (!$post['isfirst'] && $postAccess['modify'] && ($isModerators || $post['uniqueid'] == $this->_user->uniqueId)),
                'delete'    => (int) (!$post['isfirst'] && $postAccess['delete'] && ($isModerators || $post['uniqueid'] == $this->_user->uniqueId)),
                'reply'     => $postAccess['reply'],
                'reference' => $postAccess['reference']
            );

            if (empty($post['header']['client-type'])) {
                $posts[$key]['permission']['modify'] = false;
            }

            // 格式化回复头信息
            if ($post['header']) {
                $posts[$key]['header'] = $this->formatPostHeader($post['header']);
            }

            if (!$isHtml) {
                // 匹配内容中的图片
                $imgs = array();
                preg_match_all('/<img[^>]+src="([^"]+)"[^>]+\/>/i', $post['content'], $imgs);

                $posts[$key]['images'] = $imgs[1];

                $content = $posts[$key]['content'];

                $content = str_replace(array("\n", "\r", "\t", ' '), array('', '', '', ''), $content);
                $content = str_replace(array('&nbsp;', '<br>', '<br />', '</p>', '</tr>', '</td>'), array(' ', "\n", "\n", "\n", "\n", "\t"), $content);
                $content = strip_tags($content);

                $posts[$key]['content'] = (!empty($posts[$key]['header']) ? str_replace('<br />', "\n", $posts[$key]['header']['text']) : '') . $content;
            }
        }

        $return  = array();
        $postNum = $tudu->replyNum;
        foreach ($posts as $idx => $post) {
            $return[] = array(
                'orgid' => $post['orgid'],
                'boardid' => $post['boardid'],
                'tuduid' => $post['tuduid'],
                'postid' => $post['postid'],
                'isfirst' => $post['isfirst'],
                'userid' => $post['email'],
                'poster' => $post['poster'],
                'posterinfo' => $post['posterinfo'],
                'percent' => $post['islog'] ? (int) $post['percent'] : null,
                'elapsedtime' => $post['islog'] ? (int) $post['elapsedtime'] / 3600 : null,
                'content' => $post['content'],
                'images' => $post['images'],
                'attachnum' => (int) $post['attachnum'],
                'createtime' => (int) $post['createtime'],
                'images'     => $post['images'],
                'floor'      => $postNum - $offset - $idx,
                'permission' => $post['permission']
            );
        }

        $this->view->code  = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->posts = $return;
    }

    /**
     * 查看图度回复详细信息
     */
    public function infoAction()
    {
        $tuduId = $this->_request->getQuery('tuduid');
        $postId = $this->_request->getQuery('postid');
        $isHtml = (boolean) $this->_request->getQuery('ishtml');

        /* @var $daoPost Dao_Td_Tudu_Post */
        $daoPost = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Post', Tudu_Dao_Manager::DB_TS);
        $post    = $daoPost->getPost(array('tuduid' => $tuduId, 'postid' => $postId));

        if (null === $post) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Post not exists or had been deleted';
            return ;
        }

        $post = $post->toArray();

        if ($post['header']) {
            $post['header'] = $this->formatPostHeader($post['header']);
        }

        if (!$isHtml) {
            // 匹配内容中的图片
            $imgs = array();
            preg_match_all('/<img[^>]+src="([^"]+)"[^>]+\/>/i', $post['content'], $imgs);

            $post['images']  = $imgs[1];
            $post['content'] = strip_tags($post['content']);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->post = $post;
    }

    /**
     * 发送回复
     */
    public function sendAction()
    {
        $params = $this->_request->getParams();

        // 处理附件图片
        $content     = !empty($params['content']) ? nl2br($params['content']) : '';
        $attachments = array();
        if (isset($params['image'])) {
            $images = $params['image'];

            if (!is_array($params['image'])) {
                $images = explode(',', $images);
            }

            foreach ($images as $fileId) {
                if (!$fileId) {
                    continue ;
                }

                if (false !== strpos($fileId, ',')) {
                    $arr = explode(',', $fileId);
                    foreach ($arr as $fid) {
                        $attachments[] = $fid;
                        $fid = str_replace('AID:', '', $fid);
                        $content .= '<br /><img src="AID:' . $fid . '" _aid="' . $fid . '" />';
                    }

                    continue ;
                }

                $attachments[] = $fileId;
                $fileId = str_replace('AID:', '', $fileId);
                $content .= '<br /><img src="AID:' . $fileId . '" _aid="' . $fileId . '" />';
            }
        }

        $attrs = array(
            'tuduid'  => isset($params['tuduid']) ? $params['tuduid'] : null,
            'content' => $content,
            'percent' => isset($params['percent']) ? max(0, (int) $params['percent']) : null,
            'elapsedtime' => isset($params['elapsedtime']) ? (float) $params['elapsedtime'] * 3600 : null
        );

        $attrs['header'] = array(
            'client-type' => 'iOS'
        );

        if (isset($params['postid'])) {
            $attrs['postid'] = $params['postid'];
        }

        if ((!empty($params['reference']) || !empty($params['reply'])) && !empty($params['tuduid'])) {
            $refId = !empty($params['reference']) ? $params['reference'] : $params['reply'];
            $isRef = !empty($params['reference']);

            $sql = "SELECT post_id AS postid, poster FROM td_post WHERE tudu_id = :tuduid AND is_send = 1 ORDER BY create_time ASC";
            $db  = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_TS);

            $posts = $db->fetchAll($sql, array('tuduid' => $params['tuduid']));
            $floor = 0;
            $poster = null;

            foreach ($posts as $item) {
                if ($item['postid'] == $refId) {
                    $poster = $item['poster'];
                    break ;
                }
                $floor ++;
            }

            $floorText = $floor == 1 ? '楼主' : $floor . '楼';
            if ($poster) {
                if ($isRef) {
                    $sql = "SELECT content FROM td_post WHERE tudu_id = :tuduid AND post_id = :postid";
                    $refPost = $db->fetchRow($sql, array('tuduid' => $params['tuduid'], 'postid' => $refId));

                    if ($refPost) {
                        $refContent = '<div class="cite_wrap">'
                                    . '<strong>引用：</strong><span class="floor_f">' . $poster . '</span><a class="floor_f" style="margin-left:5px;" href="javascript:void(0)" _jumpfloor="' . $floor . '|' . $refId . '">' . $floorText . '</a>'
                                    . '<div>' . $refPost['content'] . '</div>'
                                    . '</div>';

                        $attrs['content'] = $refContent . $attrs['content'];
                    }
                } else {
                    $refContent = '<div class="cite_wrap">'
                                . '<p>'
                                . '<strong>回复</strong><a class="floor_f" style="margin:0 5px;" href="javascript:void(0)" _jumpfloor="FLOOR:' . $floor . '|' . $refId . '" _initfloor="' . $floor . '">' . $floorText . '</a> '
                                . '<span class="floor_f" style="margin-left:5px;">' . $poster . '</span>'
                                . '</p>'
                                . '</div>';

                    $attrs['content'] = $refContent . $attrs['content'];
                }
            }
        }

        $modelPost = Tudu_Model::factory('Model_Tudu_Post_Compose');

        try {

            require_once 'Model/Tudu/Post.php';
            $post = new Model_Tudu_Post($attrs);

            if (count($attachments)) {
                foreach ($attachments as $fid) {
                    $post->addAttachment($fid, false);
                }
            }

            $modelPost->execute('send', array(&$post));
        } catch (Model_Tudu_Exception $e) {
            $code    = TuduX_OpenApi_ResponseCode::SYSTEM_ERROR;

            switch ($e->getCode()) {
                case Model_Tudu_Exception::TUDU_NOTEXISTS:
                case Model_Tudu_Exception::POST_NOTEXISTS:
                    $code = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
                    break;
                case Model_Tudu_Exception::TUDU_IS_DONE:
                    $code = TuduX_OpenApi_ResponseCode::TUDU_CLOSED;
                    break;
                case Model_Tudu_Exception::PERMISSION_DENIED:
                    $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                    break;
                case Model_Tudu_Exception::MISSING_PARAMETER:
                    $code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
                    break;
                default:
            }

            throw new TuduX_OpenApi_Exception($e->getMessage(), $code);
        }

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->postid = $post->postId;
    }

    /**
     * 删除回复
     */
    public function deleteAction()
    {
        $tuduId = $this->_request->getParam('tuduid');
        $postId = $this->_request->getParam('postid');

        if (!$tuduId) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "tuduid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        if (!$postId) {
            throw new TuduX_OpenApi_Exception('Missing or invalid value of parameter "postid"', TuduX_OpenApi_ResponseCode::MISSING_PARAMETER);
        }

        /* @var $modelManage Model_Tudu_Manager_Tudu */
        $modelManage = Tudu_Model::factory('Model_Tudu_Manager_Tudu');

        $params  = array(
            'tuduid'   => $tuduId,
            'postid'   => $postId,
        );

        try {
            $modelManage->deletePost($params);
        } catch (Model_Tudu_Exception $e) {
            switch ($e->getCode()) {
                case Model_Tudu_Exception::INVALID_USER:
                    $code = TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE;
                    break;
                case Model_Tudu_Manager_Tudu::CODE_INVALID_TUDUID:
                case Model_Tudu_Manager_Tudu::CODE_INVALID_POSTID:
                    $code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
                    break;
                case Model_Tudu_Manager_Tudu::CODE_POST_NOTEXISTS:
                    $code = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
                    break;
                case Model_Tudu_Manager_Tudu::CODE_POST_FIRST:
                    $code = TuduX_OpenApi_ResponseCode::CONTENT_POST_FIRST;
                    break;
                case Model_Tudu_Manager_Tudu::CODE_DENY_ROLE:
                    $code = TuduX_OpenApi_ResponseCode::ACCESS_DENIED;
                    break;
                case Model_Tudu_Manager_Tudu::CODE_SAVE_FAILED:
                default:
                    $code = TuduX_OpenApi_ResponseCode::OPERATE_FAILED;
                    break;
            }
            throw new TuduX_OpenApi_Exception($e->getMessage(), $code);
        }

        $this->view->code = TuduX_OpenApi_ResponseCode::SUCCESS;
    }

    /**
     * 格式化回复头信息
     *
     * @param $header
     */
    public function formatPostHeader($header)
    {
        if (!empty($header['action']) && ($header['action'] == 'claim' || $header['action'] == 'review')) {
            $ret = array(
                'action' => $header['action'],
            );
            if ($header['action'] == 'review') {
                if (isset($header['tudu-act-value'])) {
                    $ret['val'] = $header['tudu-act-value'];
                }

                if ($ret['val']) {
                    if (isset($header['tudu-reviewer'])) {
                        $ret['text'] = sprintf('已同意本次申请；<br />转由%s进行审批。', $header['tudu-reviewer']);
                    } elseif (isset($header['tudu-to'])) {
                        $ret['text'] = sprintf('已同意本次申请；<br />转由 %s 继续执行。', $header['tudu-to']);
                    } else {
                        $ret['text'] = '已经同意本次申请。';
                    }
                } else {
                    $ret['text'] = '不同意本次申请；<br />请申请者修改后再重新提交审批。';
                }
            } else if ($header['action'] == 'claim') {
                $ret['val']  = 1;
                $ret['text'] = sprintf('%s认领了该图度。', $header['tudu-claimer']);
            }

            return $ret;
        }

        return null;
    }

    /**
     * 上传附件
     */
    private function _uploadAttachment($data)
    {
        $header = array(
            'POST /orayfile/upload HTTP/1.1',
            'Accept: */*',
            'Host: upload.tudu.com',
            'Content-Type: multipart/form-data;boundary=-----------------------------TUDU-API-UPLOAD',
            'Content-Length: ' . strlen($data),
            'Connection: close'
        );

        $body = array(
            '-----------------------------TUDU-API-UPLOAD',
            'Content-Disposition: form-data; name="file"; filename="tudu-upload-file"',
            'Content-Type: applocation/ocet-stream',
            $data
        );

        $request = implode("\n", $header) . "\n\n" . implode("\n", $body);

        $fp = fsockopen('upload.tudu.com', 80);
        fwrite($request);

    }
}