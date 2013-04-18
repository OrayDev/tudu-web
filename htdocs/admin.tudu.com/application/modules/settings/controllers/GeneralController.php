<?php
/**
 * 系统设置常规选择设置控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: GeneralController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Settings_GeneralController extends TuduX_Controller_Admin
{
    /**
     * 时区
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

    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'settings'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = strtolower($this->_request->getActionName());
        if (!$this->_user->isAdminLogined()) {
            if (in_array($action, array('upload', 'save'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 显示页面
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        if (!$org->timezone) {
            $org->timezone = 'Etc/GMT-8';
        }

        $this->view->admin = $this->_user->toArray();
        $this->view->site = $this->_options['sites'];
        $this->view->org = $org->toArray();
        $this->view->timezones  = $this->_timezones;
        $this->view->dateformats= $this->_dateFormats;
    }

    /**
     * 保存设置
     */
    public function saveAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $post = $this->_request->getPost();

        $params = array(
            'timezone'    => $post['timezone'],
            'dateformat'  => $post['dateformat']
        );

        if (isset($post['status'])) {
            $params['status'] = (int) $post['status'];
        }

        $ret = $daoOrg->updateOrg($this->_orgId, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['systeminfo_update_failure']);
        }

        $this->_cleanCache();

        $this->_createLog('org', 'update', 'base', $this->_orgId, null);

        $this->json(true, $this->lang['systeminfo_update_success']);
    }

    /**
     * 显示临时Logo
     */
    public function logoAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $hash = $this->_request->getQuery('hash');

        $options = $this->_options['upload'];

        $fileName = $options['tempdir'] . '/' . $hash;

        $size = filesize($fileName);
        $info = getimagesize($fileName);
        $content = file_get_contents($fileName);

        $this->_response->setHeader('Content-Type', $info['mime']);
        $this->_response->setHeader('Content-Length', $size);
        $this->_response->sendHeaders();

        echo $content;
    }

    /**
     * 上传
     */
    public function uploadAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $file = $_FILES['file'];
        $options = $this->_options['logo'];
        $options = array_merge($options, $this->_options['upload']);
        $scale = 1;

        if (!is_uploaded_file($file['tmp_name'])) {
            $this->json(false, $this->lang['logo_upload_failure'], null, false);
        }

        $mimes = array(
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png'
        );

        $message = null;
        do {
            if (filesize($file['tmp_name']) > $options['sizelimit']) {
                $message = $this->lang['logo_file_too_large'];
                break;
            }

            $info = getimagesize($file['tmp_name']);
            if (!in_array($info['mime'], $mimes)) {
                $message = $this->lang['invalid_image_file'];
                break;
            }
        } while (false);

        if ($message) {
            return $this->json(false, $message, null, false);
        }

        $fileName = md5_file($file['tmp_name']);

        if ($info[1] > $options['height']) {
            $scale = $options['height'] / $info[1];
        } elseif ($info[0] > $options['width']) {
            $scale = $options['width'] / $info[0];
        }

        if ($scale != 1) {
            $type = array_flip($mimes);
            $logoType = $info['mime'];
            $func = 'imagecreatefrom' . $type[$logoType];
            $outputFunc = 'image' . $type[$logoType];

            $width  = $info[0] * $scale;
            $height = $info[1] * $scale;

            $img = imagecreatetruecolor($width, $height);
            $src = $func($file['tmp_name']);

            imagecopyresampled($img, $src, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

            $ret = $outputFunc($img, $options['tempdir'] . '/' . $fileName);

        } else {
            $ret = @move_uploaded_file($file['tmp_name'], $options['tempdir'] . '/' . $fileName);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['logo_upload_failure'], null, false);
        }

        $this->json(true, $this->lang['logo_upload_success'], array('logo' => $fileName), false);
    }

    /**
     *
     */
    private function _cleanCache()
    {
        $key = 'TUDU-ORG-' . $this->_orgId;
        $this->_bootstrap->memcache->delete($key);
        // im 相关缓存
        $this->_bootstrap->memcache->delete('im_' . $this->_orgId . '_orgname');
    }
}