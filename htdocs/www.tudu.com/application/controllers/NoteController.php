<?php
/**
 * Note Controller
 *
 * @version $Id: NoteController.php 2733 2013-01-31 01:41:03Z cutecube $
 */

class NoteController extends TuduX_Controller_Base
{
    /**
     * 背景颜色
     *
     * @var array
     */
    private $_bgColors = array(
        'yellow' => '#ffff99',
        'red' => '#ff9966',
        'blue' => '#66cccc',
        'green' => '#99cc66',
        'purple' => '#cc99cc',
        'gray' => '#cccccc'
    );

    /**
     *
     * @var array
     */
    private $_randColors = array('ffff99', 'ff9966', '66cccc', '99cc66', 'cc99cc', 'cccccc');

    /**
     *
     */
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'note'));

        $this->view->LANG = $this->lang;
    }

    /**
     *
     */
    public function preDispatch()
    {
        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     * 便签首页
     */
    public function indexAction()
    {
        $uniqueId = $this->_user->uniqueId;
        $daoNote = $this->getDao('Dao_Td_Note_Note');
        $notes = $daoNote->getNotesByUniqueId($uniqueId, '', 'createtime DESC', '');
        $this->view->notes = $notes->toArray();
        $this->view->noteCount = $notes->count();
        $this->view->bgcolors = $this->_bgColors;
    }

    /**
     * 获取图度便签
     */
    public function getNoteAction()
    {
        $tuduId = $this->_request->getParam('tid');

        /* @var $daoNote Dao_Td_Note_Note */
        $daoNote = $this->getDao('Dao_Td_Note_Note');

        $note = $daoNote->getNote(array('tuduid' => $tuduId, 'uniqueid' => $this->_user->uniqueId));
        $note = $note !== null ? $note->toArray() : null;

        return $this->json(true, null, $note);
    }

    /**
     * 创建便签
     */
    public function createAction()
    {
        $post     = $this->_request->getPost();
        $orgId    = $this->_user->orgId;
        $uniqueId = $this->_user->uniqueId;

        /* @var $daoNote Dao_Td_Note_Note */
        $daoNote = $this->getDao('Dao_Td_Note_Note');

        $noteId = Dao_Td_Note_Note::getNoteId();

        $color = !empty($post['color']) ? $post['color'] : $this->_randColors[array_rand($this->_randColors)];;

        $createTime = time();
        $params = array(
            'orgid'      => $orgId,
            'uniqueid'   => $uniqueId,
            'noteid'     => $noteId,
            'content'    => $post['content'],
            'color'      => (int) base_convert($color, 16, 10),
            'createtime' => $createTime
        );

        if (!empty($post['tid'])) {
            $params['tuduid'] = $post['tid'];
        }

        $ret = $daoNote->createNote($params);
        // 标记图度有便签
        if ($ret && !empty($post['tid'])) {
            /* @var $daoTudu Dao_Td_Tudu_Tudu */
            $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

            $tudu = $daoTudu->getTuduById($uniqueId, $post['tid']);
            if (null !== $tudu) {
                $daoTudu->updateTuduUser($post['tid'], $uniqueId, array('mark' => 1));
            }
        }

        return $this->json(true, '操作成功', array('noteid' => $noteId, 'updatetime' => date('Y.m.d H:i', $createTime)));
    }

    /**
     * 更新便签
     */
    public function updateAction()
    {
        $noteId  = $this->_request->getParam('nid');
        $tuduId  = $this->_request->getParam('tid');
        $color   = $this->_request->getParam('color');
        $content = $this->_request->getParam('content');

        $uniqueId = $this->_user->uniqueId;

        /* @var $daoNote Dao_Td_Note_Note */
        $daoNote = $this->getDao('Dao_Td_Note_Note');

        $updateTime = time();

        if(empty($noteId)){
            return $this->json(false, "参数错误nid");
        }

        $params = array(
           'content' => $content
        );
        if(!empty($color)){
            $params['color'] = (int) base_convert($color, 16, 10);
        }

        $params['updatetime'] = $updateTime;

        $daoNote->updateNote($noteId, $uniqueId, $params, !empty($tuduId) ? $tuduId : null);

        return $this->json(true, "更新成功", array('noteid' => $noteId, 'updatetime' => date('Y.m.d H:i', $updateTime)));

    }

    /**
     * 删除便签
     */
    public function deleteAction()
    {
        $noteIds  = explode(',', $this->_request->getParam('nid'));
        $uniqueId = $this->_user->uniqueId;

        /* @var $daoNote Dao_Td_Note_Note */
        $daoNote = $this->getDao('Dao_Td_Note_Note');
        /* @var $daoTudu Dao_Td_Tudu_Tudu */
        $daoTudu = $this->getDao('Dao_Td_Tudu_Tudu');

        foreach ($noteIds as $noteId) {
            $params = array(
                'content' => '',
                'status' => 2
            );

            $note = $daoNote->getNote(array('uniqueid' => $this->_user->uniqueId, 'noteid' => $noteId));
            if (null === $note) {
                continue ;
            }

            if(!$daoNote->updateNote($noteId, $uniqueId, $params)){
                continue ;
            }

            if (!empty($note->tuduId)) {
                $tudu = $daoTudu->getTuduById($uniqueId, $note->tuduId);
                if (null !== $tudu) {
                    $daoTudu->updateTuduUser($note->tuduId, $uniqueId, array('mark' => 0));
                }
            }
        }
        return $this->json(true, "删除成功");

    }
}