<?php
/**
 *
 * SettingController
 *
 * @version $Id: SettingController.php 2771 2013-03-11 03:50:01Z cutecube $
 */

/**
 *
 * @author Administrator
 *
 */
class SettingController extends TuduX_Controller_Base
{

    /**
     * 时间区
     *
     * @var array
     */
    private $_timezones = array(
            'GMT+12', 'GMT+11', 'GMT+10', 'GMT+9', 'GMT+8', 'GMT+7', 'GMT+6', 'GMT+5', 'GMT+4', 'GMT+3', 'GMT+2', 'GMT+1', 'GMT+0',
            'GMT-1', 'GMT-2', 'GMT-3', 'GMT-4', 'GMT-5', 'GMT-6', 'GMT-7', 'GMT-8', 'GMT-9', 'GMT-10', 'GMT-11', 'GMT-12'
        );

    /**
     * 日期格式（日期跟时间要用空格分开，日期在前）
     *
     * @var array
     */
    private $_dateFormats = array(
            '%Y/%m/%d %H:%M:%S', '%Y/%m/%d %I:%M:%S %p',
            '%Y-%m-%d %H:%M:%S', '%Y-%m-%d %I:%M:%S %p',
            '%Y年%m月%d日 %H:%M:%S', '%Y年%m月%d日 %I:%M:%S %p'
        );

    /**
     *
     * @var array
     */
    private $_access = null;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'setting'));

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
            $action = $this->_request->getActionName();

            if (in_array($action, array('index', 'account', 'skin'))) {
                $this->jump(null, array('error' => 'timeout'));
            } else {
                return $this->json(false, $this->_lang['login_timeout']);
            }
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     * 基础设置
     */
    public function indexAction()
    {
        $this->view->timezones   = $this->_timezones;
        $this->view->dateformats = $this->_dateFormats;
    }

    /**
     * 保存基本信息
     *
     */
    public function saveAction()
    {
        $post     = $this->_request->getPost();
        $settings = array();

        if (isset($post['language'])) {
            $settings['language'] = $post['language'];
        }

        if (isset($post['fontfamily'])) {
            $settings['fontfamily'] = $post['fontfamily'];
        }

        if (isset($post['fontsize'])) {
            $settings['fontsize'] = $post['fontsize'];
        }

        if (isset($post['timezone']) && in_array(str_replace('Etc/', '', $post['timezone']), $this->_timezones)) {
            $settings['timezone'] = $post['timezone'];
        }

        if (isset($post['dateformat']) && in_array($post['dateformat'], $this->_dateFormats)) {
            $settings['dateformat'] = $post['dateformat'];
        }

        if (isset($post['pagesize'])) {
            $settings['pagesize'] = (int) $post['pagesize'];
        }

        if (isset($post['replysize'])) {
            $settings['replysize'] = (int) $post['replysize'];
        }

        if (isset($post['expiredfilter'])) {
            $settings['expiredfilter'] = (int) $post['expiredfilter'];
        }

        if (isset($post['postsort'])) {
            $settings['postsort'] = (int) $post['postsort'];
        }

        if (isset($post['skin'])) {
            if (!$this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN)) {
                return $this->json(false, $this->lang['deny_change_skin']);
            }

            $settings['skin'] = (int) $post['skin'];
        }

        $diff     = array_diff_key($this->_user->option['settings'], $settings);
        $settings = array_merge($settings, $diff);

        if (!empty($settings)) {
            $post['settings'] = json_encode($settings);
        }

        /* @var $daoOption Dao_Md_User_Option */
        $daoOption = Oray_Dao::factory('Dao_Md_User_Option', $this->multidb->getDb());

        $daoOption->updateOption($this->_user->orgId, $this->_user->userId, $post);

        $this->_user->updateSetting();
        $this->json(true, $this->lang['update_config_success']);
    }

    /**
     * 保存帐户信息
     *
     */
    public function accountAction()
    {
        $type = $this->_request->getQuery('type');

        if ($type == 'password') {
            $this->render('password');
            return ;
        }

        $daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        $user = $daoUser->getUser(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId));

        if ($user) {
            $userinfo = $user->toArray();

            $info = $daoUser->getUserInfo(array('orgid' => $this->_user->orgId, 'userid' => $this->_user->userId));

            if ($info) {
                $info = $info->toArray();

                $userinfo = array_merge($userinfo, $info);
            }

            $birthday = $userinfo['birthday'] ? explode('-', date('Y-m-d', $userinfo['birthday'])) : array(null, null, null);

            $userinfo['birthyear']  = $birthday[0];
            $userinfo['birthmonth'] = $birthday[1];
            $userinfo['birthdate']  = $birthday[2];

            $this->view->userinfo = $userinfo;
        }
    }

    /**
     * 更新用户信息
     */
    public function userinfoAction()
    {
        $post = $this->_request->getPost();

        $info = array(
            'nick'     => $post['nick'],
            'gender'   => (int) $post['gender'],
            'idnumber' => $post['idnumber'],
            'tel'      => $post['tel'],
            'mobile'   => $post['mobile'],
            'email'    => $post['email']
        );

        // 邮箱格式有误
        if (!empty($info['email']) && !Oray_Function::isEmail($info['email'])) {
            return $this->json(false, $this->lang['invalid_email']);
        }

        if (!empty($post['bir-year']) && !empty($post['bir-month']) && !empty($post['bir-day'])) {
            $birthday = $post['bir-year'] . '-' . $post['bir-month'] . '-' . $post['bir-day'];

            $info['birthday'] = @strtotime($birthday);

            if (false == $info['birthday']) {
                return $this->json(false, $this->lang['invalid_birthday_date']);
            }
        }

        if (!empty($post['avatars'])) {
            $options = $this->getInvokeArg('bootstrap')->getOption('avatar');
            $fileName = $options['tempdir'] . '/' . $post['avatars'];

            if (file_exists($fileName)) {
                $imginfo = getimagesize($fileName);

                $info['avatartype'] = $imginfo['mime'];
                $info['avatars']    = base64_encode(file_get_contents($fileName));
            }

            @unlink($fileName);
        }

        $daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        $daoUser->updateUserInfo($this->_user->orgId, $this->_user->userId, $info);
        $daoUser->updateUser($this->_user->orgId, $this->_user->userId, array('lastupdatetime' => time()));

        $this->json(true, $this->lang['userinfo_update_success']);
    }

    /**
     * 修改密码
     */
    public function passwordAction()
    {
        $pwd   = $this->_request->getPost('password');
        $opwd  = $this->_request->getPost('opassword');
        $repwd = $this->_request->getPost('repassword');

        if ($this->session->isdemo) {
            return $this->json(false, $this->lang['password_deny_to_demoaccount']);
        }

        // 验证原密码
        $auth = Tudu_Auth::getInstance();
        $auth->setAdapter(new Tudu_Auth_Adapter_User($this->multidb->getDb(), null, null, array(
            'ignorelock' => true,
            'skiplock'   => true
        )));

        $result = $auth->checkPassword($this->_user->userName, $opwd);
        if (!$result->isValid()) {
            return $this->json(false, $this->lang['old_password_unmatch']);
        }

        if ($pwd != $repwd) {
            return $this->json(false, $this->lang['confirm_password_unmatch']);
        }

        // 安全级别匹配
        $regs = array(
            1 => '/[0-9a-zA-Z]/',
            2 => '/[0-9a-zA-Z^a-zA-Z0-9]/'
        );

        $pwdLevel = isset($this->_user->option['passwordlevel']) ? $this->_user->option['passwordlevel'] : 0;
        if ($pwdLevel > 0 && !preg_match($regs[$pwdLevel], $pwd)) {
            return $this->json(false, $this->lang['password_level_not_match_' . $pwdLevel]);
        }

        $daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());

        $params = array('password' => $pwd);

        $ret = $daoUser->updateUserInfo($this->_user->orgId, $this->_user->userId, $params);

        $ret = $daoUser->updateUser($this->_user->orgId, $this->_user->userId, array('initpassword' => 0));

        if (!$ret) {
            return $this->json(false, $this->lang['password_update_failure']);
        }

        $this->cache->deleteCache('TUDU-USER-' . $this->_user->userId . '@' . $this->_user->orgId);

        $this->json(true, $this->lang['password_update_success']);
    }

    /**
     * 头像上传
     */
    public function uploadAction()
    {
        $file = $_FILES['avatar-file'];
        $options = $this->getInvokeArg('bootstrap')->getOption('avatar');

        if (!$file || !is_uploaded_file($file['tmp_name'])) {
            return $this->json(false, $this->lang['avatar_upload_failure'], null, false);
        }

        $mt   = explode(' ', microtime());
        $hash = md5_file($file['tmp_name']);
        $uploadName = $options['tempdir'] . '/' . $hash;

        $mimes = array(
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        );

        if (!file_exists($uploadName)) {
            $message = null;
            do {
                if (!is_uploaded_file($file['tmp_name'])) {
                    $message = $this->lang['avatar_upload_failure'];
                    break;
                }

                $info = getimagesize($file['tmp_name']);
                if (!in_array($info['mime'], $mimes)) {
                    $message = $this->lang['invalid_img_type'];
                    break;
                }

                if ($file['size'] > $options['sizelimit']) {
                    $message = $this->lang['avatar_filesize_too_large'];
                    break;
                }
            } while (false);

            if ($message) {
                return $this->json(false, $message, null, false);
            }

            $ret = @move_uploaded_file($file['tmp_name'], $uploadName);
        }

        $this->json(true, $this->lang['avatar_upload_success'], array('hash' => $hash), false);
    }

    /**
     * 显示上传图片
     */
    public function avatarAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $hash = $this->_request->getQuery('hash');

        $options = $this->getInvokeArg('bootstrap')->getOption('avatar');
        $fileName = $options['tempdir'] . '/' . $hash;

        if (file_exists($fileName)) {
            $info = getimagesize($fileName);

            $this->_response->setHeader('Content-Type: ', $info['mime']);

            $content = file_get_contents($fileName);

            echo $content;
        }
    }

    /**
     * 更新头像设置
     */
    public function updateavatarAction()
    {
        $hash = $this->_request->getPost('hash');
        $post = $this->_request->getPost();
        $options = $this->getInvokeArg('bootstrap')->getOptions();

        $userId = @$post['userid'];
        $x      = (int) $post['x'];
        $y      = (int) $post['y'];
        $width  = (int) $post['width'];
        $height = (int) $post['height'];

        $avatar = null;
        $avatarType = null;

        $mimes = array(
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        );

        if ($userId && !$this->getUserDao()->existsUser($this->_user->orgId, $userId)) {
            return $this->json(false, $this->lang['user_not_exists']);
        }

        $fileName = $options['avatar']['tempdir'] . '/' . $hash;
        if (!$hash || !file_exists($fileName)) {
            return $this->json(false, $this->lang['avatar_upload_failure']);
        }

        $info = getimagesize($fileName);
        $avatarType = $info['mime'];

        if (!in_array($avatarType, $mimes)) {
            $this->json(false, $this->lang['avatar_upload_failure']);
        }

        $type = array_flip($mimes);
        $func = 'imagecreatefrom' . $type[$avatarType];
        $outputFunc = 'image' . $type[$avatarType];

        // tudutalk 不支持gif，先转成jpg
        if ($outputFunc == 'imagegif') {
            $outputFunc = 'imagejpeg';
        }

        $img = imagecreatetruecolor($options['avatar']['width'], $options['avatar']['height']);
        $src = $func($fileName);

        $width  = $width <= 0 ? $info[0] : $width;
        $height = $height <= 0 ? $info[1] : $height;

        imagecopyresampled($img, $src, 0, 0, $x, $y, $options['avatar']['width'], $options['avatar']['height'], $width, $height);

        $ret = $outputFunc($img, $fileName . '_thumb');

        if (!$ret) {
            $this->json(false, $this->lang['avatar_edit_failure']);
        }

        return $this->json(true, $this->lang['avatar_edit_success'], array('avatar' => $hash . '_thumb'));
    }

    /**
     * 保存风格设置
     *
     */
    public function skinAction()
    {

    }

    /**
     * 绑定邮箱
     */
    public function mailboxAction()
    {
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        $mailbox = $daoUser->getMailbox($this->_user->orgId, $this->_user->userId);

        if (null !== $mailbox) {
            $mailbox = $mailbox->toArray();
        }

        $this->view->supports = json_encode($this->_supportMailboxes);
        $this->view->mailbox  = $mailbox;
    }

    /**
     * 保存邮箱
     */
    public function savemailboxAction()
    {
        $post = $this->_request->getPost();
        $bind = false;

        $daoUser = $this->getMdDao('Dao_Md_User_User');
        $mailBox = $daoUser->getMailbox($this->_user->orgId, $this->_user->userId);

        if (!empty($post['address']) || !empty($post['password']) || !empty($post['imaphost'])) {
            $bind = true;
            if (empty($post['address']) || !Oray_Function::isEmail($post['address'])) {
                return $this->json(false, $this->lang['invalid_email_address']);
            }

            if (empty($post['password']) && !$mailBox) {
                return $this->json(false, $this->lang['missing_email_password']);
            }

            if (!empty($post['imaphost']) && (!Oray_Function::isDomainName($post['imaphost']) && !Oray_Function::isIp($post['imaphost']))) {
                return $this->json(false, $this->lang['invalid_imap_host']);
            }

            if ($post['port'] != '') {
                if ((int) $post['port'] <= 0 || (int) $post['port'] > 65535) {
                    return $this->json(false, $this->lang['invalid_imap_port']);
                }
            }
        }

        if ($bind) {
            $isSsl = isset($post['isssl']) && $post['isssl'] == 1 ? 1 : 0;
            $type  = isset($post['type']) ? (int) $post['type'] : 0;
            if (null !== $mailBox) {
                $params = array(
                    'address'  => $post['address'],
                    'imaphost' => $post['imaphost'],
                    'port'     => (int) $post['port'] > 0 ? (int) $post['port'] : null,
                    'isssl'    => $isSsl,
                    'type'     => $type
                );

                if (!empty($post['password'])) {
                    $params['password'] = $post['password'];
                }

                $ret = $daoUser->updateMailbox($this->_user->orgId, $this->_user->userId, $params);
            } else {
                $ret = $daoUser->addMailbox(array(
                    'orgid' => $this->_user->orgId,
                    'userid' => $this->_user->userId,
                    'address'  => $post['address'],
                    'imaphost' => $post['imaphost'],
                    'password' => $post['password'],
                    'port'     => (int) $post['port'] > 0 ? (int) $post['port'] : null,
                    'isssl'    => $isSsl,
                    'type'     => $type
                ));
            }
        } else {
            $ret = $daoUser->removeMailbox($this->_user->orgId, $this->_user->userId);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['update_mailbox_failure']);
        }

        return $this->json(true, $this->lang['update_mailbox_success']);
    }

    /**
     * 登录日志
     *
     */
    public function logAction()
    {
        /* @var $daoOption Dao_Md_Log_Login */
        $daoLoginLog = $this->getMdDao('Dao_Md_Log_Login');

        $page = max(1, (int) $this->_request->getQuery('page'));
        $pageSize = 25;

        $condition = array(
            'orgid' => $this->_user->orgId,
            'uniqueid' => $this->_user->uniqueId,
            'starttime' => $this->_timestamp - 30 * 86400,
            'endtime' => $this->_timestamp
        );

        $logs = $daoLoginLog->getMonthLoginLogsPage($condition, 'createtime DESC', $page, $pageSize);

        $this->view->pageinfo = array(
            'currpage'    => $logs->currentPage(),
            'pagecount'   => $logs->pageCount(),
            'recordcount' => $logs->recordCount(),
            'url' => '/setting/log'
        );
        $this->view->logs = $logs->toArray();
    }
}