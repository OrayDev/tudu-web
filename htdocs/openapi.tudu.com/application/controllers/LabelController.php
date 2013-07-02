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
class LabelController extends TuduX_Controller_OpenApi
{
    /**
     * 默认的系统标签设置
     *
     * @var array
     */
    public $_labelDefaultSetting = array(
        'all'       => array('ordernum' => 9999, 'isshow' => 0, 'display' => 1),
        'inbox'     => array('ordernum' => 9998, 'isshow' => 1, 'display' => 1),
        'todo'      => array('ordernum' => 9997, 'isshow' => 2, 'display' => 3),
        'review'    => array('ordernum' => 9996, 'isshow' => 2, 'display' => 3),
        'reviewed'  => array('ordernum' => 9986, 'isshow' => 0, 'display' => 1), //不参与排序，排在最后
        'drafts'    => array('ordernum' => 9995, 'isshow' => 1, 'display' => 3),
        'starred'   => array('ordernum' => 9994, 'isshow' => 2, 'display' => 3),
        'notice'    => array('ordernum' => 9993, 'isshow' => 0, 'display' => 3),
        'discuss'   => array('ordernum' => 9992, 'isshow' => 0, 'display' => 3),
        'meeting'   => array('ordernum' => 9991, 'isshow' => 0, 'display' => 3),
        'sent'      => array('ordernum' => 9990, 'isshow' => 0, 'display' => 3),
        'forwarded' => array('ordernum' => 9989, 'isshow' => 0, 'display' => 3),
        'done'      => array('ordernum' => 9988, 'isshow' => 0, 'display' => 3),
        'ignore'    => array('ordernum' => 9987, 'isshow' => 0, 'display' => 3),
        'wait'      => array('ordernum' => 10001, 'isshow' => 0, 'display' => 2),
        'associate' => array('ordernum' => 9798, 'isshow' => 0, 'display' => 2)
    );

    public $_lang = array(
        'all' => '所有图度',
        'inbox' => '图度箱',
        'todo'  => '我执行',
        'review' => '我审批',
        'reviewed' => '已审批',
        'drafts'   => '草稿箱',
        'starred' => '星标关注',
        'notice' => '公告',
        'discuss' => '讨论',
        'meeting' => '会议',
        'sent'    => '我发起',
        'forwarded' => '我转发',
        'done'      => '已完成',
        'ignore'    => '忽略',
        'wait'      => '待办',
        'associate' => '相关'
    );

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
     * 获取用户标签列表
     */
    public function listAction()
    {
        /* @var $daoLabel Dao_Td_Tudu_Label */
        $daoLabel = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Label', Tudu_Dao_Manager::DB_TS);

        $labels   = $daoLabel->getLabelsByUniqueId($this->_user->uniqueId, null, 'issystem DESC, ordernum DESC, alias ASC')->toArray('labelid');

        // 标签列表为空，创建默认系统标签
        $reLoad = false;

        $configLabels = $this->_bootstrap->getOption('tudu');
        $configLabels = $configLabels['label'];

        foreach ($configLabels as $alias => $labelId) {
            if (!array_key_exists($labelId, $labels)) {
                $daoLabel->createLabel(array(
                    'uniqueid'   => $this->_user->uniqueId,
                    'labelalias' => $alias,
                    'labelid'    => $labelId,
                    'isshow'     => isset($this->_labelDefaultSetting[$alias]['isshow']) ? $this->_labelDefaultSetting[$alias]['isshow'] : 1,
                    'issystem'   => true,
                    'display'    => isset($this->_labelDefaultSetting[$alias]['display']) ? $this->_labelDefaultSetting[$alias]['display'] : 1,
                    'ordernum'   => $this->_labelDefaultSetting[$alias]['ordernum']));
                $daoLabel->calculateLabel($this->_user->uniqueId, $labelId);
                $reLoad = true;
            } elseif (isset($this->_labelDefaultSetting[$alias]) && $this->_labelDefaultSetting[$alias]['display'] != $labels[$labelId]['display']) {
                $daoLabel->updateLabel($this->_user->uniqueId, $labelId, array(
                    'display' => $this->_labelDefaultSetting[$alias]['display']
                ));
                $reLoad = true;
            }
        }

        if ($reLoad) {
            $labels = $daoLabel->getLabelsByUniqueId($this->_user->uniqueId, null, 'issystem DESC, ordernum DESC, alias ASC')->toArray('labelid');
        }

        // 读取图度箱
        $daoTudu = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS);
        $tudus = $daoTudu->getTuduPage(array('uniqueid' => $this->_user->uniqueId, 'label' => '^i'))->toArray();

        $waitCount = array('unreadnum' => 0, 'totalnum' => 0);
        $assoCount = array('unreadnum' => 0, 'totalnum' => 0);
        foreach ($tudus as $tudu) {
            if ($tudu['type'] == 'discuss' || $tudu['type'] == 'meeting') {
                continue ;
            }

            if ($tudu['type'] == 'notice' && !in_array('^e', $tudu['labels'])) {
                continue ;
            }

            // 待办 & 进行中
            $isAccepter = in_array($this->_user->address, $tudu['accepter'], true)
                        || in_array($this->_user->userName, $tudu['accepter'], true);
            $isSender   = $tudu['sender'] == $this->_user->address
                        || $tudu['sender'] == $this->_user->userName;

            if ((($isAccepter && !$tudu['selfaccepttime'])
                || ($isSender && !$tudu['accepttime'])
                || in_array('^e', $tudu['labels']))
                && !$tudu['istudugroup']
                && $tudu['status'] < Dao_Td_Tudu_Tudu::STATUS_DONE)
            {

                $waitCount['totalnum']++;
                if (!$tudu['isread']) {
                    $waitCount['unreadnum']++;
                }
                continue ;
            }

            if (!$isAccepter && !$isSender && $tudu['type'] == 'task') {
                $assoCount['totalnum']++;
                if (!$tudu['isread']) {
                    $assoCount['unreadnum']++;
                }
            }
        }

        // 手机客户端输出
        // iOS客户端需要特殊处理 :-(
        if (in_array($this->_clientId, $this->_officalClients)) {
            $sort = array(
                '^td' => 0, '^a' => 1, '^n' => 2, '^m' => 3, '^e' => 4, '^f' => 5, '^w' => 6,
                '^c'  => 7, '^d' => 8, '^o' => 9, '^r' => 10, '^g' => 11, '^t' => 12
            );

            $output = array();

            foreach ($labels as $label) {
                if ($label['issystem']) {
                    if (!isset($sort[$label['labelid']])) {
                        continue ;
                    }
                    $num = $sort[$label['labelid']];

                    $total  = $label['totalnum'];
                    $unread = $label['unreadnum'];

                    if ($label['labelid'] == '^c') {
                        $total  = $assoCount['totalnum'];
                        $unread = $assoCount['unreadnum'];
                    } elseif ($label['labelid'] == '^td') {
                        $total  = $waitCount['totalnum'];
                        $unread = $waitCount['unreadnum'];
                    }

                    $output[$num] = array(
                        'labelid'   => $label['labelid'],
                        'labelname' => $this->_lang[$label['labelalias']],
                        'bgcolor'   => $label['bgcolor'],
                        'issystem'  => $label['issystem'],
                        'unreadnum' => $unread,
                        'totalnum'  => $total,
                        'synctime'  => $label['synctime'],
                        'ordernum'  => 10001 - $num //$label['ordernum']
                    );

                    continue ;
                }

                $output[] = array(
                    'labelid'   => $label['labelid'],
                    'labelname' => $label['labelalias'],
                    'bgcolor'   => $label['bgcolor'],
                    'issystem'  => $label['issystem'],
                    'unreadnum' => $label['unreadnum'],
                    'totalnum'  => $label['totalnum'],
                    'synctime'  => $label['synctime'],
                    'ordernum'  => $label['ordernum']
                );
            }

            ksort($output);
            $labels = array_values($output);
        }

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->labels = $labels;
    }

    /**
     * 获取标签同步时间
     */
    public function getSynctimeAction()
    {
        $labelId = $this->_request->getParam('labelid');

        /* @var $daoLabel Dao_Td_Tudu_Label*/
        $daoLabel = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Label', Tudu_Dao_Manager::DB_TS);

        $label = $daoLabel->getLabel(array(
            'uniqueid' => $this->_user->uniqueId,
            'labelid'  => $labelId
        ));

        if (null === $label) {
            $this->view->code    = TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS;
            $this->view->message = 'Label id "'.$labelId.'" not exists';
            return ;
        }

        $this->view->code     = 0;
        $this->view->labelid  = $labelId;
        $this->view->synctime = $label->syncTime;
    }
}
