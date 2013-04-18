<?php
/**
 * Logo Controller
 *
 * @author Hiro
 * @version $Id: FrameIncController.php 1894 2012-05-31 08:02:57Z cutecube $
 */

class FrameIncController extends TuduX_Controller_Base
{

    public function preDispatch()
    {
    	$this->lang = Tudu_Lang::getInstance()->load('common');
		if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    public function indexAction()
    {
        if ($this->_user->initPassword) {
            $this->jump('/frame/initpwd');
        }

        if (!isset($this->session->tips)) {
            $this->session->tips = $this->_loadTips();
        }

    	$labels = $this->getLabels(null);
    	if (!count($labels)) {
        	// 防止新用户点入左导航为空
            $daoLabel = $this->getDao('Dao_Td_Tudu_Label');
            foreach($this->options['tudu']['label'] as $alias => $id) {
                if (!isset($labels[$alias])) {
                    $daoLabel->createLabel(array(
                        'uniqueid' => $this->_user->uniqueId,
                        'labelalias' => $alias,
                        'labelid' => $id,
                        'isshow'  => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                        'issystem' => true,
                        'ordernum' => $this->_labelDefaultSetting[$alias]['ordernum']));
                    $daoLabel->calculateLabel($this->_user->uniqueId, $id);
                    $reLoad = true;
                }
            }

            $labels = $this->getLabels(null);
    	}

    	$mailboxes = array();

    	$access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN),
    	    'flow' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CREATE_FLOW)
    	);

    	// 有权限创建工作流，但仍需判断是否为版主
    	if ($access['flow']) {
    	    $boards = $this->getBoards(true, true);

    	    // 若用户均不是某一板块的负责人或分区负责人，则无权限新建工作流
    	    if (empty($boards)) {
    	        $access['flow'] = false;
    	    }
    	}

    	// 没有权限创建工作流，则读取该用户是否有使用的工作流
    	if (!$access['flow']) {
    	    $flows = $this->_getFlows();

    	    if (!empty($flows)) {
    	        $access['flow'] = true;
    	    }
    	}

    	$daoBoard = $this->getDao('Dao_Td_Board_Board');
    	$boards = $daoBoard->getAttentionBoards($this->_user->orgId, $this->_user->uniqueId);

    	$daoEmail = $this->getMdDao('Dao_Md_User_Email');
    	$mailBoxes = $daoEmail->getEmails(array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId
    	), null, array('ordernum' => 'DESC'));

    	$upload = $this->options['upload'];
    	$upload['cgi']['upload'] .= '?' . session_name() . '=' . Zend_Session::getId()
    	                          . '&email=' . $this->_user->address;

        $daoOrg = $this->getMdDao('Dao_Md_Org_Org');
        $org = $daoOrg->getOrg(array('orgid' => $this->_user->orgId));

    	$this->view->mailboxes = $mailBoxes->toArray();
    	$this->view->upload = $upload;
    	$this->view->im     = $this->options['im'];
    	$this->view->access = $access;
    	$this->view->boards = $boards;
    	$this->view->labels = $labels;
    	$this->view->user   = $this->_user->toArray();
    	$this->view->sid    = Zend_Session::getId();
    	$this->view->LANG   = $this->lang;
    	$this->view->org   = $org->toArray();
    	$this->view->checklog = !empty($this->session->auth['loginlogid']);
    	$this->view->registFunction('format_label', array($this, 'formatLabels'));
    }

/**
     * 提示框
     */
    private function _loadTips()
    {
        $tips = $this->getTips();

        if ($tips) {
            /** @var $daoTips Dao_Md_User_Tips */
            $daoTips = $this->getMdDao('Dao_Md_User_Tips');

            $userTips = $daoTips->getUserTips($this->_user->uniqueId);

            $unread = array();
            foreach ($tips as $item) {
                if (!array_key_exists($item['id'], $userTips)) {
                    $newTips[] = $item['id'];
                    $unread[$item['id']] = $item['path'];
                    continue ;
                }

                if (isset($userTips[$item['id']]) && (int) $userTips[$item['id']]['status'] == 0) {
                    $unread[$item['id']] = $item['path'];
                }
            }

            // 添加新的气泡记录
            if (isset($newTips)) {
                $daoTips->addTips($this->_user->uniqueId, array_unique($newTips));
            }
        }

        return $unread;
    }

    /**
     * 读取用户下是否有可用的工作流
     */
    private function _getFlows()
    {
        /* @var $daoFlow Dao_Td_Flow_Flow */
        $daoFlow = $this->getDao('Dao_Td_Flow_Flow');

        $records = $daoFlow->getFlows(array('orgid' => $this->_user->orgId), null, 'createtime DESC');
        $records = $records->toArray();

        $flows = array();
        foreach($records as $key => $record) {
            if ($record['parentid']) {
                if (!in_array('^all', $record['avaliable'])
                        // 参与人
                        && !(in_array($this->_user->userName, $record['avaliable'], true) || in_array($this->_user->address, $record['avaliable'], true))
                        // 参与人（群组）
                        && !sizeof(array_uintersect($this->_user->groups, $record['avaliable'], "strcasecmp"))
                        // 是否创建者
                        && !($record['uniqueid'] == $this->_user->uniqueId)
                ) {
                    continue;
                }

                $flows[$record['parentid']]['children'][] = &$records[$key];
            }
        }

        unset($records);
        return $flows;
    }
}

