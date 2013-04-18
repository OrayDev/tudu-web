<?php
/**
 * Rule Controller
 *
 * @version $Id: RuleController.php 2733 2013-01-31 01:41:03Z cutecube $
 */
class RuleController extends TuduX_Controller_Base
{

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'rule'));

        $this->_access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN)
        );

        $this->view->access = $this->_access;
        $this->view->LANG   = $this->lang;
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
     *
     */
    public function indexAction()
    {
        $rules = $this->getDao('Dao_Td_Rule_Rule')->getRulesByUniqueId($this->_user->uniqueId);

        $this->view->registModifier('format_rule_description', array($this, '_formatDescription'));

        $this->view->rules = $rules->toArray();
    }

    /**
     *
     */
    public function modifyAction()
    {
        $ruleId = $this->_request->getQuery('ruleid');
        $action = 'create';

        /* @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = $this->getDao('Dao_Td_Rule_Rule');

        if ($ruleId) {
            $rule = $daoRule->getRuleById($ruleId);

            if (null === $rule || $rule->uniqueId != $this->_user->uniqueId) {
                return $this->json(false, $this->lang['rule_not_exists']);
            }

            $action = 'update';
            $filter = $daoRule->getFiltersByRuleId($rule->ruleId)->toArray();

            $filters = array();
            foreach ($filter as $item) {
                $filters[$item['what']] = $item;
            }

            $rule = $rule->toArray();
            if (!empty($rule['mailremind'])) {
                $mailbox = implode(';', $rule['mailremind']['mailbox']);
                $this->view->mailbox    = $mailbox;
            }

            $this->view->rule    = $rule;
            $this->view->filters = $filters;
        }

        $labels = $this->getLabels();

        $this->view->action = $action;
        $this->view->labels = $labels;
    }

    /**
     * 创建规则
     */
    public function createAction()
    {
        $post = $this->_request->getPost();

        /* @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = $this->getDao('Dao_Td_Rule_Rule');

        $ruleId = Dao_Td_Rule_Rule::getRuleId();

        $params = array(
            'ruleid'    => $ruleId,
            'uniqueid'  => $this->_user->uniqueId,
            'operation' => $post['operation'],
            'isvalid'   => ($post['isvalid'] ? 1 : 0)
        );

        if (!empty($post['value'])) {
            $params['value'] = $post['value'];
        }

        // 邮件提醒部分
        if (!empty($post['mailremind'])) {
            $remind = array('isvalid' => true);

            // 邮箱
            $vowels  = array(',', '；', '，', '、');
            $mailbox = str_replace($vowels, ';', $post['mailbox']);
            $mailbox = explode(';', $mailbox);
            if (count($mailbox) <= 0) {
                return $this->json(false, $this->lang['mailbox_null_tips']);
            }

            foreach ($mailbox as $key => $item) {
                if (strlen($item) <= 0) {
                    unset($mailbox[$key]);
                    continue;
                }

                if (!Oray_Function::isEmail($item)) {
                    return $this->json(false, $this->lang['invalid_mailbox_address']);
                }
            }
            $remind['mailbox'] = $mailbox;

            // 应用板块
            $boards = array();
            foreach ($post['member'] as $key) {
                $boards[] = str_replace('_', '^', $post['boardid-' . $key]);
            }
            if (!empty($boards)) {
                $remind['boards'] = $boards;
            }
            $params['mailremind'] = json_encode($remind);
        }

        $filterIdx = $post['filters'];
        $filters   = array();

        foreach ($filterIdx as $index) {
            $item = array(
                'ruleid' => $ruleId
            );

            $item['filterid'] = Dao_Td_Rule_Rule::getFilterId();
            $item['value']    = $post['value-' . $index];
            $item['type']     = $post['type-' . $index];
            $item['isvalid']  = isset($post['isvalid-' . $index]) ? $post['isvalid-' . $index] : false;
            $item['what']     = $post['what-' . $index];

            if (!$item['isvalid'] && empty($item['value'])) {
                continue ;
            }

            // 生成规则描述内容
            $strValue = null;
            switch ($item['what']) {
                case 'from':
                case 'to':
                case 'cc':
                    $array = explode("\n", $item['value']);

                    $valueStr = array();
                    foreach ($array as $user) {
                        $arr = explode(' ', $user);
                        if (isset($arr[1])) {
                            $valueStr[] = $arr[1];
                        }
                    }

                    $strValue = implode(',', $valueStr);

                    break;
                case 'subject':
                default:
                    $strValue = str_replace("\t", '', $item['value']);
                    break;
            }

            if ($item['isvalid']) {
                $subject[] = implode("\t", array(
                    $item['what'], $item['type'], $strValue
                ));
            }

            $filters[$index] = $item;
        }

        $params['description'] = implode("\n", $subject);

        $ruleId = $daoRule->createRule($params);

        if (!$ruleId) {
            return $this->json(true, $this->lang['rule_create_failure']);
        }

        foreach ($filters as $filter) {
            $daoRule->addFilter($filter);
        }
        //Memcache
        $rule = $daoRule->getRuleById($ruleId);
        $uniqueId = $rule->uniqueId;
        $rules = $this->cache->deleteCache(array($daoRule, 'getRulesByUniqueId'), array($uniqueId, array('isvalid' => true)));

        return $this->json(true, $this->lang['rule_create_success'], array('ruleid' => $ruleId));
    }

    /**
     * 更新规则
     */
    public function updateAction()
    {
        $post   = $this->_request->getPost();
        $ruleId = $this->_request->getPost('ruleid');

        /* @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = $this->getDao('Dao_Td_Rule_Rule');

        $rule = $daoRule->getRuleById($ruleId);

        if (null === $rule) {
            return $this->json(false, $this->lang['rule_not_exists']);
        }

        $params = array(
            'operation' => $post['operation'],
            'isvalid'   => ($post['isvalid'] ? 1 : 0)
        );

        if (!empty($post['value'])) {
            $params['value'] = $post['value'];
        }

        // 邮件提醒部分
        if (!empty($post['mailremind'])) {
            $remind = array('isvalid' => true);

            // 邮箱
            $vowels  = array(',', '；', '，', '、');
            $mailbox = str_replace($vowels, ';', $post['mailbox']);
            $mailbox = explode(';', $mailbox);
            if (count($mailbox) <= 0) {
                return $this->json(false, $this->lang['mailbox_null_tips']);
            }

            foreach ($mailbox as $key => $item) {
                if (strlen($item) <= 0) {
                    unset($mailbox[$key]);
                    continue;
                }

                if (!Oray_Function::isEmail($item)) {
                    return $this->json(false, $this->lang['invalid_mailbox_address']);
                }
            }
            $remind['mailbox'] = $mailbox;

            // 应用板块
            $boards = array();
            foreach ($post['member'] as $key) {
                $boards[] = str_replace('_', '^', $post['boardid-' . $key]);
            }
            if (!empty($boards)) {
                $remind['boards'] = $boards;
            }
            $params['mailremind'] = json_encode($remind);
        } else {
            if (!empty($rule->mailRemind)) {
                $remind = $rule->mailRemind;

                $remind['isvalid']    = false;
                $params['mailremind'] = json_encode($remind);
            }
        }

        $filterIdx = $post['filters'];
        $filters   = array();
        $subject   = array();

        foreach ($filterIdx as $index) {
            $item = array(
                'ruleid' => $ruleId
            );

            $item['action']   = isset($post['filterid-' . $index]) ? 'update' : 'create';
            $item['filterid'] = $item['action'] == 'update'
                                ? $post['filterid-' . $index]
                                : Dao_Td_Rule_Rule::getFilterId();
            $item['value']    = $post['value-' . $index];
            $item['type']     = $post['type-' . $index];
            $item['isvalid']  = isset($post['isvalid-' . $index]) ? $post['isvalid-' . $index] : false;
            $item['what']     = $post['what-' . $index];

            if ($item['action'] == 'create' && !$item['isvalid'] && empty($item['value'])) {
                continue ;
            }

            // 生成规则描述内容
            $strValue = null;
            switch ($item['what']) {
                case 'from':
                case 'to':
                case 'cc':
                    $array = explode("\n", $item['value']);

                    $valueStr = array();
                    foreach ($array as $user) {
                        $arr = explode(' ', $user);
                        if (isset($arr[1])) {
                            $valueStr[] = $arr[1];
                        }
                    }

                    $strValue = implode(',', $valueStr);

                    break;
                case 'subject':
                default:
                    $strValue = str_replace("\t", '', $item['value']);
                    break;
            }

            if ($item['isvalid']) {
                $subject[] = implode("\t", array(
                    $item['what'], $item['type'], $strValue
                ));
            }

            $filters[$index] = $item;
        }

        $params['description'] = implode("\n", $subject);

        $ret = $daoRule->updateRule($ruleId, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['rule_update_failure']);
        }

        foreach ($filters as $filter) {
            if ($filter['action'] == 'create') {
                $daoRule->addFilter($filter);
            } else {
                $daoRule->updateFilter($filter['filterid'], $filter);
            }
        }
        //Memcache
        $rules = $this->cache->deleteCache(array($daoRule, 'getRulesByUniqueId'), array($rule->uniqueId, array('isvalid' => true)));

        return $this->json(true, $this->lang['rule_update_success'], array('ruleid' => $ruleId));

    }

    /**
     * 删除规则
     */
    public function deleteAction()
    {
        $ruleId = $this->_request->getPost('ruleid');

        /** @var $daoRule Dao_Td_Rule_Rule */
        $daoRule = $this->getDao('Dao_Td_Rule_Rule');
        //Memcache,key
        $rule = $daoRule->getRuleById($ruleId);

        $ret = $daoRule->deleteRule($ruleId);

        if (!$ret) {
            return $this->json(false, $this->lang['rule_delete_failure']);
        }

        // Cache
        $rules = $this->cache->deleteCache(array($daoRule, 'getRulesByUniqueId'), array($rule->uniqueId, array('isvalid' => true)));

        return $this->json(true, $this->lang['rule_delete_success']);
    }

    /**
     *
     * 作用与当前存在的图度
     */
    public function affectAction()
    {
        $ruleId = $this->_request->getPost('ruleid');

        $rule = $this->getDao('Dao_Td_Rule_Rule')->getRuleById($ruleId);

        if (null === $rule || $rule->uniqueId != $this->_user->uniqueId || !$rule->isValid) {
            return $this->json(false, null);
        }

        $config = $this->bootstrap->getOption('httpsqs');
        // 插入消息队列
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], 'tudu');

        $httpsqs->put(implode(' ', array(
            'tudu',
            'rule',
            '',
            http_build_query(array('tsid' => $this->_user->tsId, 'ruleid' => $ruleId))
        )));

        return $this->json(true, $this->lang['wait_for_rule_affect']);
    }

    /**
     * 格式化规则描述
     *
     * @param $description
     */
    public function _formatDescription($description, $operation, $value)
    {
        $array = explode("\n", $description);

        $condition = array();
        foreach ($array as $idx => $item) {
            $item = explode("\t", $item, 3);

            if (count($item) < 3) {
                continue ;
            }

            $condition[$idx] = array();
            for ($i = 0; $i < 2; $i++) {
                $condition[$idx][] = $this->lang['rule_keyword'][$item[$i]];
            }

            $condition[$idx][] = ' ' . $item[2];

            $condition[$idx] = implode('', $condition[$idx]);
        }

        $condition = implode('; ', $condition);

        $opera = $this->lang['rule_keyword'][$operation];

        if ($operation == 'label' && $value) {
            $labels = $this->getLabels();
            $labelName = null;
            foreach ($labels as $alias => $label) {
                if ($label['labelid'] == $value) {
                    $labelName = $alias;
                    break;
                }
            }

            if (null === $labelName) {
                $labelName = '[' . $this->lang['label_deleted'] . ']';
            }

            $opera .= ' ' . $labelName;
        }

        return $this->lang['rule_keyword']['if'] . $this->lang['cln'] . '"' . $condition . '"' .
               $this->lang['comma'] . $this->lang['rule_keyword']['then'] . $this->lang['cln'] . '"' . $opera . '"';
    }
}