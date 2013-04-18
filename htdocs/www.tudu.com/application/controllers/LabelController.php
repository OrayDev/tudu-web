<?php
/**
 *
 * LabelController
 *
 * @version $Id: LabelController.php 1407 2011-12-21 11:02:18Z cutecube $
 */

/**
 *
 */
class LabelController extends TuduX_Controller_Base
{
	/**
	 * 背景颜色
	 *
	 * @var array
	 */
	private $_bgColors = array(
        '#729c3b', '#58a8b4', '#5883bf', '#6d72ba', '#e3a325',
        '#da8a22', '#b34731', '#bb4c91', '#995aae', '#cc0000',
        '#fcd468', '#ff9966', '#cc99cc', '#cc9999', '#ad855c',
        '#cccc99', '#ff6633', '#cc6666', '#ad33ad', '#855c85',
        '#99cc66', '#66cccc', '#3399ff', '#2b8787', '#855c85',
        '#6699ff', '#3385d6', '#335cad', '#5f27b3', '#262ed7',
        '#d5d2c0', '#b5bfca', '#999999', '#666666', '#333333'
	);

	/**
     *
     * @var array
     */
    private $_labelVisible = array(
        '^all' => 0,
        '^i'   => 1,
        '^a'   => 2,
        '^r'   => 1,
        '^e'   => 2,
        '^v'   => 2,
        '^m'   => 2,
        '^f'   => 2,
        '^t'   => 2,
        '^n'   => 0,
        '^d'   => 0,
        '^o'   => 0,
        '^g'   => 0
    );

	/**
	 * 初始化
	 */
	public function init()
	{
		parent::init();

		$this->lang = Tudu_Lang::getInstance()->load(array('common', 'label'));

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

    	// IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }

        $this->view->LANG = $this->lang;
	}

	/**
	 *
	 */
	public function preDispatch()
	{
        if (!$this->_user->isLogined()) {
            $action = $this->_request->getActionName();

            if (in_array($action, array('index'))) {
                $this->_redirect('/');
            } else {
                return $this->json(false, $this->_lang['login_timeout']);
            }
        }
	}

	/**
	 * 标签列表页面
	 */
	public function indexAction()
	{
		$labels = $this->getLabels();
		$array  = array();
		$reLoad = false;

	    // 检查系统标签是否存在
    	$daoLabel = $this->getDao('Dao_Td_Tudu_Label');
    	$config = $this->bootstrap->getOption('tudu');
    	foreach($config['label'] as $alias => $id) {
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

    	if ($reLoad) {
    	    $this->_labels = null;
    	    $labels = $this->getLabels();
    	    $this->view->reloadlabels = $labels;
    	    $this->view->reload       = $reLoad;
    	}

		foreach ($labels as $label) {
			if ($label['issystem']) {
				$label['displayname'] = $this->lang['label_' . $label['labelalias']];
				$array['system'][] = $label;
			} else {
				$label['displayname'] = $label['labelalias'];
				$array['user'][] = $label;
			}
		}

		$access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN)
        );

        $this->view->access   = $access;
		$this->view->bgcolors = $this->_bgColors;
		$this->view->labels   = $array;
		$this->view->registFunction('format_label', array($this, 'formatLabels'));
	}

	/**
	 * 创建自定义标签
	 */
	public function createAction()
	{
		$name = trim($this->_request->getPost('name'));

        $this->_checkLabel($name);

		$daoLabel = $this->getDao('Dao_Td_Tudu_Label');

		$labelId = Dao_Td_Tudu_Label::getLabelId();

		$count = $daoLabel->getLabelCount(array(
            'uniqueid' => $this->_user->uniqueId,
            'issystem' => false
		));

		$bgColor = $this->_bgColors[$count % count($this->_bgColors)];
		$ret = $daoLabel->createLabel(array(
            'uniqueid'   => $this->_user->uniqueId,
            'labelid'    => $labelId,
            'labelalias' => $name,
            'color'      => '#000',
            'bgcolor'    => $bgColor,
            'ordernum'   => $count + 1
		));

		if (!$ret) {
			return $this->json(false, $this->lang['label_create_failure']);
		}

        $this->_labels = null;
        $labels = $this->getLabels(null);
        $data   = array();
        foreach ($labels as $index => $label) {
            // 过滤“所有图度”与“已审批”标签
            if ($labels[$index]['labelalias'] == 'all' || $labels[$index]['labelalias'] == 'reviewed') {
                continue ;
            }

            $labels[$index]['labelname'] = $labels[$index]['issystem']
                                         ? $this->lang['label_' . $labels[$index]['labelalias']]
                                         : $labels[$index]['labelalias'];

            $data[] = $labels[$index];
        }

		return $this->json(true, $this->lang['label_create_success'], array('labels' => $data, 'labelid' => $labelId, 'name' => $name, 'bgcolor' => $bgColor, 'ordernum' => $count+1));
	}

	/**
	 * 更新标签
	 */
	public function updateAction()
	{
		$labelId = $this->_request->getPost('labelid');
		$post    = Oray_Function::trim($this->_request->getPost());

		if (!$labelId) {
			return $this->json(false, $this->lang['invalid_labelid']);
		}

		$label = null;
		foreach ($this->getLabels() as $_label) {
		    if ($_label['labelid'] == $labelId) {
		        $label = $_label;
		        break;
		    }
		}
		if (null === $label || $label['issystem']) {
		    return $this->json(false, $this->lang['deny_system_label']);
		}

		$params = array();

		if (!empty($post['name']) && $label['labelalias'] != $post['name']) {
		    $this->_checkLabel($post['name']);
			$params['labelalias'] = $post['name'];
		}

		if (!empty($post['bgcolor'])) {
			$params['bgcolor'] = $post['bgcolor'];
		}

		if (!$params) {
			$this->json(true, $this->lang['label_update_success']);
		}

		$daoLabel = $this->getDao('Dao_Td_Tudu_Label');
		$daoLabel->updateLabel($this->_user->uniqueId, $labelId, $params);

		$this->_labels = null;
		$labels = $this->getLabels();
		return $this->json(true, $this->lang['label_update_success'], $labels);
	}

	/**
	 * 删除标签
	 */
	public function deleteAction()
	{
		$labelId = $this->_request->getPost('labelid');

        if (!$labelId) {
            return $this->json(false, $this->lang['invalid_labelid']);
        }

		$label = null;
		foreach ($this->getLabels() as $_label) {
		    if ($_label['labelid'] == $labelId) {
		        $label = $_label;
		        break;
		    }
		}

		if (null !== $label) {

    		if ($label['issystem']) {
    		    return $this->json(false, $this->lang['deny_system_label']);
    		}

            $daoLabel = $this->getDao('Dao_Td_Tudu_Label');

            $ret = $daoLabel->deleteLabel($this->_user->uniqueId, $labelId);

            if (!$ret) {
            	return $this->json(false, $this->lang['label_delete_failure']);
            }

		}

		$daoLabel->tidyLabelSort($this->_user->uniqueId);

		$this->_labels = null;
		$labels = $this->getLabels();
        return $this->json(true, $this->lang['label_delete_success'], $labels);
	}

    /**
     * 标签排序
     */
    public function sortAction()
    {
        $labelId = $this->_request->getPost('label');
        $type    = $this->_request->getPost('type');
        $labelalias = $this->_request->getPost('labelalias');
        $issystem   = $this->_request->getPost('issystem');

        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');

        $ret = $daoLabel->sortLabel($this->_user->uniqueId, !empty($labelId) ? $labelId : $labelalias, $type, !empty($issystem) ? $issystem : false);

        if (!$ret) {
            $this->json(false, $this->lang['label_sort_failure']);
        }

        $this->_labels = null;
        $labels = $this->getLabels();
        $this->json(true, $this->lang['label_sort_success'], $labels);
    }

    /**
     * 标签的显示方式
     */
    public function showLabelAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        $labelId = $this->_request->getPost('labelid');
        $type    = $this->_request->getPost('type');

        if (!$labelId) {
            return $this->json(false, $this->lang['invalid_labelid']);
        }

        /* @var $daoLabel Dao_Td_Tudu_Label */
        $daoLabel = $this->getDao('Dao_Td_Tudu_Label');

        $ret = $daoLabel->showLabel($this->_user->uniqueId, $labelId, (int) $type);
        if (!$ret) {
            return $this->json(false, $this->lang['operate_failure']);
        }

        // 我审批和已审批效果一致
        if ($labelId == '^e') {
            $daoLabel->showLabel($this->_user->uniqueId, '^v', (int) $type);
        }
        if ($labelId == '^v') {
            $daoLabel->showLabel($this->_user->uniqueId, '^e', (int) $type);
        }

        $this->_labels = null;
        $labels = $this->getLabels();
        return $this->json(true, $this->lang['operate_success'], $labels);
    }

	/**
	 * 检查标签名称是否合法
	 *
	 * @param unknown_type $name
	 * @return void
	 */
	private function _checkLabel($name)
	{
		if (!$name) {
			return $this->json(false, $this->lang['missing_name']);
		}

        if (strpos($name, '^') !== false) {
            return $this->json(false, sprintf($this->lang['params_invalid_char'], '^'));
        }

        $config = $this->bootstrap->getOption('tudu');
        if (array_key_exists($name, $config['label'])) {
            return $this->json(false, sprintf($this->lang['params_invalid_name'], $name));
        }

        $labels = $this->getLabels();
        if (array_key_exists($name, $labels)) {
            return $this->json(false, $this->lang['params_label_exists']);
        }
	}
}