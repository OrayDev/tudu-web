<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */


/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class SuggestController extends TuduX_Controller_OpenApi
{
    public function preDispatch()
    {
        // 用户未登录
        /*if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }*/

        Tudu_Dao_Manager::setDb(Tudu_Dao_Manager::DB_SITE, $this->_bootstrap->multidb->getDb('site'));
    }

    /**
     * 获取当前登录用户信息
     */
    public function sendAction()
    {
        $post = $this->_request->getParams();

        if (empty($post['subject'])) {
            $this->view->code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
            $this->view->message = 'Missing parameter "subject" for suggest';
            return ;
        }

        if (empty($post['content'])) {
            $this->view->code = TuduX_OpenApi_ResponseCode::MISSING_PARAMETER;
            $this->view->message = 'Missing parameter "content" for suggest';
            return ;
        }

        /* @var $daoSuggest Dao_Feedback_Suggest */
        $daoSuggest = Tudu_Dao_Manager::getDao('Dao_Feedback_Suggest', Tudu_Dao_Manager::DB_SITE);
        /* @var $daoPost Dao_Feedback_Post */
        $daoPost    = Tudu_Dao_Manager::getDao('Dao_Feedback_Post', Tudu_Dao_Manager::DB_SITE);

        $suggestParams = array(
            'suggestid'    => Dao_Feedback_Suggest::getSuggestId(),
            'subject'      => $post['subject'],
            'isread'       => 1,
            'createtime'   => time(),
            'lastposttime' => time()
        );

        if ($this->_user->isLogined()) {
            $suggestParams['lastposter'] = $this->_user->trueName;
            $suggestParams['author']     = $this->_user->trueName;
            $suggestParams['authorid']   = $this->_user->uniqueId;
        } else {
            $suggestParams['lastposter'] = self::ANONYMOUS_NAME;
            $suggestParams['author']     = self::ANONYMOUS_NAME;
            $suggestParams['authorid']   = self::ANONYMOUS_ID;
        }

        if (!empty($post['tel'])) {
            $suggestParams['tel'] = $post['tel'];
        }

        if (!empty($post['email'])) {
            $suggestParams['email'] = $post['email'];
        }

        // 创建反馈建议
        $suggestId = $daoSuggest->createSuggest($suggestParams);
        if (!$suggestId) {
            return $this->json(false, $this->lang['send_failure']);
        }

        $postParams = array(
            'postid'     => Dao_Feedback_Post::getPostId(),
            'suggestid'  => $suggestId,
            'content'    => $post['content'],
            'isfirst'    => 1,
            'createtime' => time(),
        );

        if ($this->_user->isLogined()) {
            $postParams['poster'] = $this->_user->trueName;
            $postParams['posterid'] = $this->_user->uniqueId;
            $postParams['postertype'] = Dao_Feedback_Post::POSTER_TYPE_TUDU;
        } else {
            $suggestParams['poster'] = self::ANONYMOUS_NAME;
            $suggestParams['posterid'] = self::ANONYMOUS_ID;
        }

        // 附件关联
        if (!empty($post['file'])) {
            $files = array_unique((array) $post['file']);
            $postParams['attachnum'] = count($files);
        }

        // 创建回复
        $postId = $daoPost->createPost($postParams);
        if (!$postId) {
            $daoSuggest->deleteSuggest($suggestId);
            $this->view->code    = TuduX_OpenApi_ResponseCode::SUGGEST_SEND_FAILED;
            $this->view->message = 'Suggest send failed';
            return ;
        }

        // 附件关联
        if (!empty($postParams['attachnum'])) {
            /* @var $daoAttach Dao_Feedback_Attachment */
            $daoAttach = $this->getDao('Dao_Feedback_Attachment', Tudu_Dao_Manager::DB_SITE);

            foreach ($files as $file) {
                $daoAttach->addPost($suggestId, $postId, $file);
            }
        }

        $this->view->code      = 0;
        $this->view->suggestid = $suggestId;
    }
}