<?php
/**
 * Compose Controller
* 图度发送控制器
*
* @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
* @link       http://www.oray.com/
* @version    $Id: ComposeController.php 2504 2012-12-14 02:04:51Z cutecube $
*/

/**
 * @see Model_Tudu_Tudu
 */
require_once 'Model/Tudu/Tudu.php';

class ComposeTuduController extends TuduX_Controller_Base
{
    const ACTION_SAVE    = 'save';
    const ACTION_SEND    = 'send';
    const ACTION_FORWARD = 'forward';
    const ACTION_REVIEW  = 'review';
    const ACTION_DIVIDE  = 'divide';
    const ACTION_INVITE  = 'invite';

    public function init()
    {
        parent::init();

        if (!$this->_user->isLogined()) {
            return $this->json(false, $this->lang['login_timeout']);
        }

        Tudu_AddressBook::getInstance()->setCache($this->cache);
        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'tudu'));

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD => $this->multidb->getDefaultDb(),
            Tudu_Dao_Manager::DB_TS => $this->getTsDb()
        ));

        $resourceManager = new Tudu_Model_ResourceManager_Registry();
        $resourceManager->setResource(Tudu_Model::RESOURCE_CONFIG, $this->bootstrap->getOptions());

        Tudu_Model::setResourceManager($resourceManager);
    }

    /**
     * 发送操作
     */
    public function sendAction()
    {
        $post   = $this->_request->getPost();
        $action = self::ACTION_SEND;
        $time   = time();

        if (!empty($post['action']) && $post['action'] == 'save') {
            $action = self::ACTION_SAVE;
        }

        if (!empty($post['forward'])) {
            $action = self::ACTION_FORWARD;
        } elseif (!empty($post['divide'])) {
            $action = self::ACTION_DIVIDE;
        } elseif (!empty($post['review'])) {
            $action = self::ACTION_REVIEW;
        } elseif (!empty($post['apply'])) {
            $action = self::ACTION_APPLY;
        } elseif (!empty($post['invite'])) {
            $action = self::ACTION_INVITE;
        }

        /**
         * @see Model_Tudu_Tudu
         */
        require_once 'Model/Tudu/Tudu.php';
        $tudu = new Model_Tudu_Tudu();
        $this->_formatParams($tudu, $post);

        $tudu->setAttributes(array(
            'orgid'    => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'email'    => $this->_user->userName,
            'from'     => $this->_user->userName . ' ' . $this->_user->trueName,
            'poster'   => $this->_user->trueName,
            'createtime' => $time,
            'lastupdatetime' => $time,

            'operation' => $action
        ));

        $className = 'Model_Tudu_Compose_' . ucfirst($action);
        $model = new $className();

        // 添加图度工作流相关流程
        if ($tudu->type == 'task' || $tudu->type == 'notice' || in_array($action, array(self::ACTION_REVIEW, self::ACTION_FORWARD))) {
            $tudu->setExtension(new Model_Tudu_Extension_Flow());
        }

        // 周期任务
        if (in_array($action, array(self::ACTION_SAVE, self::ACTION_SEND)) && ($tudu->type == 'task' || $tudu->type == 'notice')) {
            if ($tudu->cycle) {
                $tudu->setExtension(new Model_Tudu_Extension_Cycle($this->_getCycleParams($post)));
            }
        }

        // 处理投票
        if ($tudu->type == 'discuss') {
            $this->_prepareVoteParams($tudu, $post);
        }

        // 图度组支持
        if (($tudu->type == 'task' && !empty($post['chidx'])) || $action == self::ACTION_DIVIDE) {
            $group = new Model_Tudu_Extension_Group();

            foreach ($post['chidx'] as $suffix) {

                $suffix = '-' . $suffix;

                $child = new Model_Tudu_Tudu();
                $this->_formatParams($child, $post, $suffix);
                $child->setExtension(new Model_Tudu_Extension_Flow());

                if ($child->cycle) {
                    $child->setExtension(new Model_Tudu_Extension_Cycle($this->_getCycleParams($post, $suffix)));
                }

                $group->appendChild($child);
            }

            $tudu->setExtension($group);
        }

        // 处理会议
        if ($tudu->type == 'meeting') {
            $meeting = new Model_Tudu_Extension_Meeting(array(
                'orgid' => $this->_user->orgId,
                'notifytime' => $this->_request->getPost('notifytime'),
                'notifytype' => $this->_request->getPost('notifytype'),
                'location'   => $this->_request->getPost('location'),
                'isallday'   => $this->_request->getPost('isallday')
            ));

            $tudu->setExtension($meeting);
        }

        $params = array(&$tudu);

        try {

            $model->execute('compose', &$params);

            // 保存后添加发送操作
            if ($action != self::ACTION_SAVE) {
                $config   = $this->bootstrap->getOption('httpsqs');
                $tuduconf = $this->bootstrap->getOption('tudu');

                $sendType  = isset($tuduconf['send']) ? ucfirst($tuduconf['send']['class']) : 'Common';
                $sendClass = 'Model_Tudu_Send_' . $sendType;

                $modelSend = new $sendClass(array('httpsqs' => $config));
                $modelSend->send(&$tudu);
            }

        // 捕获流程异常返回错误信息
        } catch (Model_Tudu_Exception $e) {

            $error = null;
            switch ($e->getCode()) {
                case Model_Tudu_Exception::TUDU_NOTEXISTS:
                    // 图度不存在
                    $error = $this->lang['tudu_not_exists'];
                    break;
                case Model_Tudu_Exception::BOARD_NOTEXISTS:
                    $error = $this->lang['board_not_exists'];
                    break;
                case Model_Tudu_Exception::FLOW_USER_NOT_EXISTS:
                    $error = $this->lang['missing_flow_steps_receiver'];
                    break;
                case Model_Tudu_Exception::FLOW_NOT_EXISTS:
                    $error = $this->lang['missing_flow_steps'];
                    break;
                case Model_Tudu_Exception::INVALID_USER:
                case Model_Tudu_Exception::PERMISSION_DENIED:
                    $error = $this->lang['permission_denied_for_tudu'];
                    break;
                default:
                    $error = $action !== self::ACTION_SAVE
                             ? $this->lang['send_failure']
                             : $this->lang['save_failure'];

                    if ($action == self::ACTION_REVIEW) {
                        $error = $this->lang['review_failure'];
                    }
                    break;
            }

            $this->json(false, $error);
        }

        $returnData = array(
            'tuduid' => $tudu->tuduId
        );

        // 投票的返回值
        if (null !== ($vote = $tudu->getExtension('Model_Tudu_Extension_Vote'))) {
            //$votes = $vote->getVotes();
        }

        // 返回图度组
        if (null !== ($group = $tudu->getExtension('Model_Tudu_Extension_Group'))) {
            $returnData['children'] = array();
            $children = $group->getChildren();
            foreach ($children as $item) {
                $returnData['children'][] = $item->tuduId;
            }
        }

        $message = $action !== self::ACTION_SAVE
                 ? $this->lang['send_success']
                 : $this->lang['save_success'];

        $this->json(true, $message, $returnData);
    }

    /**
     *
     */
    private function _formatParams(Model_Tudu_Tudu &$tudu, array $params, $suffix = '')
    {
        $keys = array(
            'tid' => array('type' => 'string', 'column' => 'tuduid'),
            'ftid' => array('type' => 'string', 'column' => 'tuduid'),
            'tuduid' => array('type' => 'string'),

            'starttime' => array('type' => 'date'),
            'endtime' => array('type' => 'date'),

            'priority' => array('type' => 'boolean'),
            'privacy' => array('type' => 'boolean'),
            'notifyall' => array('type' => 'boolean'),
            'cycle' => array('type' => 'boolean'),
            'isauth' => array('type' => 'boolean'),
            'istop' => array('type' => 'boolean'),
            'needconfirm' => array('type' => 'boolean'),
            'appid' => array('type' => 'string'),

            'acceptmode' => array('type' => 'boolean'),
            'subject'    => array('type' => 'string'),
            'classid'    => array('type' => 'string'),
            'flowid'     => array('type' => 'string'),
            'type'       => array('type' => 'string'),
            'classid'    => array('type' => 'string', 'column' => 'classid'),
            'boardid'    => array('type' => 'string'),
            'bid'        => array('type' => 'string', 'column' => 'boardid'),
            'content'    => array('type' => 'html'),

            'password'   => array('type' => 'string', 'depend' => 'privacy'),

            'to' => array('type' => 'receiver'),
            'cc' => array('type' => 'receiver'),
            'bcc' => array('type' => 'receiver'),
            'reviewer' => array('type' => 'receiver'),

            'agree' => array('type' => 'boolean')
        );

        $attributes = array();

        foreach ($keys as $k => $item) {
            if ($k == 'to' && !empty($suffix)) {
                $val = $params['ch-' . $k . $suffix];
            } else{
                if (!isset($params[$k . $suffix])) {
                    continue ;
                }

                $val = $params[$k . $suffix];
            }

            // 有依赖关系字段
            if (isset($item['depend']) && empty($params[$item['depend'] . $suffix])) {
                continue ;
            }


            $col = isset($item['column']) ? $item['column'] : $k;
            switch ($item['type']) {
                case 'date':
                    $attributes[$col] = is_numeric($val) ? (int) $val : strtotime($val);
                    break;
                case 'boolean':
                    $attributes[$col] = (boolean) $val;
                    break;
                case 'html':
                    $t = strip_tags($val, 'img');
                    $attributes[$col] = empty($t) ? '' : $val;
                    break;
                case 'receiver':
                    if (!empty($val)) {
                        $attributes[$col] = $this->_formatReceiver($val, in_array($col, array('to', 'reviewer')));

                        // 填充执行人进度
                        if ($col == 'to' && !empty($params['toidx' . $suffix]) && !empty($attributes['to'])) {
                            $idx = $params['toidx' . $suffix];
                            foreach ($idx as $i) {
                                $csfx = $suffix . '-' . $i;
                                if (isset($params['to' . $csfx]) && isset($params['to-percent' . $csfx])) {
                                    foreach ($attributes[$col][0] as $k => $item) {
                                        if ($item['username'] . ' ' . $item['truename'] == $params['to' . $csfx]) {
                                            $attributes[$col][0][$k]['percent'] = (int) $params['to-percent' . $csfx];
                                            break ;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'string':
                default:
                    $attributes[$col] = trim($val);
                    break;
            }
        }

        if (isset($attributes['type']) && $attributes['type'] == 'notice') {
            $attributes['istop'] = 0;
            if (!empty($attributes['endtime']) && $attributes['endtime'] >= strtotime('today')) {
                $attributes['istop'] = 1;
            }
        }

        $attachments = array();
        if (isset($params['attach' . $suffix]) && is_array($params['attach' . $suffix])) {
            foreach ($params['attach' . $suffix] as $item) {
                $attachments[] = array('fileid' => $item, 'isattachment' => true, 'isnetdisk' => false);
            }
        }

        if (isset($params['file' . $suffix]) && is_array($params['file' . $suffix])) {
            foreach ($params['file' . $suffix] as $item) {
                $attachments[] = array('fileid' => $item, 'isattachment' => false, 'isnetdisk' => false);
            }
        }

        if (!empty($params['nd-attach' . $suffix])) {
            $attachments = array_diff($attachments, $params['nd-attach' . $suffix]);

            $daoNdFile     = Tudu_Dao_Manager::getDao('Dao_Td_Netdisk_File', Tudu_Dao_Manager::DB_TS);
            $daoAttachment = Tudu_Dao_Manager::getDao('Dao_Td_Attachment_File', Tudu_Dao_Manager::DB_TS);

            foreach ($params['nd-attach' . $suffix] as $ndfileId) {
                $fileId = $ndfileId;
                $attach = $daoAttachment->getFile(array('fileid' => $fileId));

                if (null !== $attach) {
                    $attachments[] = array('fileid' => $fileId, 'isattachment' => true, 'isnetdisk' => true);
                    continue ;
                }

                $file = $daoNdFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $ndfileId));
                if ($file->fromFileId) {
                    $fileId = $file->fromFileId;
                }
                if ($file->attachFileId) {
                    $fileId = $file->attachFileId;
                }

                $fid = $daoAttachment->createFile(array(
                    'uniqueid' => $this->_user->uniqueId,
                    'fileid'   => $fileId,
                    'orgid'    => $this->_user->orgId,
                    'filename' => $file->fileName,
                    'path'     => $file->path,
                    'type'     => $file->type,
                    'size'     => $file->size,
                    'createtime' => time()
                ));

                if ($fid) {
                    $attachments[] = array('fileid' => $fileId, 'isattachment' => true, 'isnetdisk' => true);
                }
            }
        }

        $tudu->setAttributes($attributes);

        if (!empty($attachments)) {
            foreach ($attachments as $item) {
                $tudu->addAttachment($item['fileid'], $item['isattachment'], $item['isnetdisk']);
            }
        }
    }

    /**
     *
     * @param array  $params
     * @param string $suffix
     */
    private function _getCycleParams(array $params, $suffix = '')
    {
        $keys = array(
            'cycleid' => array('type' => 'string'),
            'mode'    => array('type' => 'string'),
            'endtype' => array('type' => 'int'),
            'displaydate' => array('type' => 'boolean'),
            'starttime'   => array('type' => 'date'),
            'keepattach' => array('type' => 'boolean'),

            'type' => array('type' => 'int', 'key' => 'type-%mode'),
            'day'  => array('type' => 'int', 'key' => '%mode-%type-day'),
            'week' => array('type' => 'int', 'key' => '%mode-%type-week'),
            'month'=> array('type' => 'int', 'key' => '%mode-%type-month'),
        );

        $ret = array(
            'mode' => 'day',
            'type' => 1
        );
        foreach ($keys as $col => $item) {

            $key = isset($item['key'])
                 ? str_replace(array('%mode', '%type'), array($ret['mode'], $ret['type']), $item['key'])
                 : $col . $suffix;

            $val = isset($params[$key]) ? $params[$key] : null;

            switch ($item['type']) {
                case 'int':
                    $ret[$col] = (int) $val;
                    break;
                case 'date':
                    $ret[$col] = is_numeric($val) ? (int) $val : strtotime($val);
                    break;
                case 'boolean':
                    $ret[$col] = (boolean) $val;
                    break;
                case 'string':
                default:
                    $ret[$col] = trim($val);
                    break;
            }
        }

        if (empty($ret['cycleid'])) {
            $ret['cycleid'] = Dao_Td_Tudu_Cycle::getCycleId();
        }

        $prefix = $ret['mode'] . '-' . $ret['type'] . '-';
        if (isset($params[$prefix . 'weeks'])) {
            $ret['weeks'] = implode(',', $params[$prefix . 'weeks' . $suffix]);
        }

        if (isset($params[$prefix . 'at'])) {
            $ret['at'] = (int) $params[$prefix . 'at' . $suffix];
        }

        if (isset($params[$prefix . 'what'])) {
            $ret['what'] = $params[$prefix . 'what' . $suffix];
        }

        if (!empty($params['starttime' . $suffix]) && !empty($params['endtime' . $suffix])) {
            $ret['period'] = Oray_Function::dateDiff('d', strtotime($params['starttime' . $suffix]), strtotime($params['endtime' . $suffix]));
        }

        return $ret;
    }

    /**
     *
     * @param array  $params
     * @param string $suffix
     */
    private function _prepareVoteParams(Model_Tudu_Tudu &$tudu, array $params, $suffix = '')
    {
        $voteMember = 'votemember' . $suffix;

        if (!empty($params[$voteMember]) && is_array($params[$voteMember])) {
            $vote = new Model_Tudu_Extension_Vote();

            foreach ($params[$voteMember] as $item) {
                $p = array(
                    'title'      => $params['title-' . $item . $suffix],
                    'maxchoices' => (int) $params['maxchoices-' . $item . $suffix],
                    'visible'    => !empty($params['visible-' . $item . $suffix]) ? (int) $params['visible-' . $item . $suffix] : 0,
                    'anonymous'  => !empty($params['anonymous-' . $item . $suffix]) ? (int) $params['anonymous-' . $item . $suffix] : 0,// 创建人显示投票参与人
                    'privacy'    => !empty($params['privacy-' . $item . $suffix]) ? (int) $params['privacy-' . $item . $suffix] : 0,
                    'isreset'    => !empty($params['isreset-' . $item . $suffix]) ? (int) $params['isreset-' . $item . $suffix] : 0,
                    'ordernum'   => $params['voteorder-' . $item . $suffix],
                    'expiretime' => !empty($params['endtime']) ? strtotime($params['endtime']) : null,
                );

                if (isset($params['voteid-' . $item . $suffix])) {
                    $p['voteid'] = $params['voteid-' . $item . $suffix];
                }

                $voteId = $vote->addVote($p);

                $optionMember    = 'optionid-' . $item . $suffix;
                $newOptionMember = 'newoption-' . $item . $suffix;

                if (!empty($params[$optionMember]) && is_array($params[$optionMember])) {
                    foreach ($params[$optionMember] as $option) {
                        $opt = array(
                            'optionid' => $option,
                            'text'     => $params['text-' . $item . '-' . $option . $suffix]
                        );

                        if (isset($params['ordernum-' . $item. '-' . $option . $suffix])) {
                            $opt['ordernum'] = (int) $params['ordernum-' . $item. '-' . $option . $suffix];
                        }

                        $vote->addOption($voteId, $opt);
                    }
                }

                if (!empty($params[$newOptionMember]) && is_array($params[$newOptionMember])) {
                    foreach ($params[$newOptionMember] as $option) {

                        $opt = array(
                            'text'     => $params['text-' . $item . '-' . $option . $suffix],
                            'ordernum' => (int) $params['ordernum-' . $item . '-' . $option . $suffix]
                        );

                        $vote->addOption($voteId, $opt);
                    }
                }
            }

            $tudu->addExtension($vote);
        }
    }

    /**
     *
     * @param string $receiver
     */
    protected function _formatReceiver($receiver, $isFlow = false, $isPercent = false)
    {
        $arr = explode("\n", $receiver);

        $ret = array();
        $section = array();
        foreach ($arr as $line) {
            $line = trim($line);
            if (empty($line) || $line == '+') {
                continue ;
            }

            if ($line == '>' && $isFlow) {
                $ret[] = $section;
                $section = array();
                continue ;
            }

            $pair = explode(' ', $line, 2);

            if (false !== strpos($pair[0], '@')) {
                $trueName = isset($pair[1]) ? $pair[1] : null;

                if (null === $trueName) {
                    list(, $suffix) = explode('@', $pair[0]);
                    $addressbook = Tudu_Addressbook::getInstance();
                    if (false === strpos($suffix, '.')) {
                        $info = $addressbook->searchUser($this->_user->orgId, $pair[0]);

                        if (!$info) {
                            continue ;
                        }

                        $trueName = $info['truename'];
                    } else {
                        $info = $addressbook->searchContact($this->_user->uniqueId, $pair[0], null);

                        if (null === $info) {
                            list($trueName, ) = explode('@', $pair[0]);
                        } else {
                            $trueName = $info['truename'];
                        }
                    }
                }

                if ($isFlow) {
                    $section[$pair[0]] = array('username' => $pair[0], 'truename' => $trueName, 'email' => $pair[0]);
                } else {
                    $ret[$pair[0]]     = array('username' => $pair[0], 'truename' => $trueName, 'email' => $pair[0]);
                }

            } else {

                // 流程不允许群组
                if ($isFlow) {
                    continue ;
                }

                $groupName = isset($pair[1]) ? $pair[1] : null;

                if (null === $groupName) {
                    if (0 === strpos($pair[0], 'XG')) {
                        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Group', Tudu_Dao_Manager::DB_MD);
                        $group = $daoGroup->getGroup(array('uniqueid' => $this->_user->uniqueId, 'groupid' => $pair[0]));

                        if (null === $group) {
                            continue ;
                        }

                        $groupName = $group->groupName;
                    } else {
                        $daoGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);
                        $group = $daoGroup->getGroup(array('orgid' => $this->_user->orgId, 'groupid' => $pair[0]));

                        if (null === $group) {
                            continue ;
                        }

                        $groupName = $group->groupName;
                    }
                }

                $ret[$pair[0]] = array('groupid' => $pair[0], 'truename' => $groupName);
            }
        }

        if (!empty($section) && $isFlow) {
            $ret[] = $section;
        }

        return $ret;
    }
}