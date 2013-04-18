<?php
/**
 * 系统设置图度登录页面设置控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: PageController.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Settings_PageController extends TuduX_Controller_Admin
{
    /**
     * 系统登陆模板 key=>value
     * 用于写入日志
     *
     * @var array
     */
    protected $_sysLog = array(
        'sys_default' => 'SYS:default',
        'sys_58789e'  => 'SYS:#58789e',
        'sys_c57592'  => 'SYS:#c57592',
        'sys_99ac71'  => 'SYS:#99ac71',
        'sys_bg01'    => 'SYS:bg_01.jpg',
        'sys_bg02'    => 'SYS:bg_02.jpg',
        'sys_bg03'    => 'SYS:bg_03.jpg',
        'sys_bg04'    => 'SYS:bg_04.jpg'
    );

    /**
     * (non-PHPdoc)
     * @see TuduX_Controller_Admin::init()
     */
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
            if (in_array($action, array('upload', 'save', 'save.color', 'save.image'))) {
                return $this->json(false, '登陆超时，请重新登陆');
            } else {
                $this->destroySession();
                $this->referer($this->_request->getBasePath() . '/login/');
            }
        }
    }

    /**
     * 显示页面信息
     */
    public function indexAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId));

        $this->view->loginskin = !empty($org->loginSkin) ? $org->loginSkin : array();
    }

    /**
     * 预览
     */
    public function previewAction()
    {
        $type  = $this->_request->getQuery('type');
        $value = $this->_request->getQuery('value');

        $memcache = $this->_bootstrap->memcache;
        $orgInfo  = $memcache->get('TUDU-HOST-' . $this->_orgId . '.tudu.com');

        if (!$orgInfo) {
            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg  = $this->getDao('Dao_Md_Org_Org');
            $orgInfo = $daoOrg->getOrgByHost($this->_orgId . '.tudu.com');
        }

        if ($orgInfo instanceof Dao_Md_Org_Record_Org) {
            $orgInfo = $orgInfo->toArray();
        }

        $this->view->org     = $orgInfo;
        $this->view->address = $this->_user->userName;

        if (!$type || !$value) {
            $this->view->loginskin = array(
                'type'     => 'color',
                'value'    => 'SYS:default',
                'color'    => 'default',
                'issystem' => true,
            );

            return ;
        }

        $loginSkin             = array('type' => $type, 'value' => $value);
        $info                  = explode(':', $value);
        $loginSkin['issystem'] = $info[0] == 'SYS' ? true : false;
        $loginSkin[$type]      = $info[1];

        $this->view->loginskin = $loginSkin;
    }

    /**
     * 保存设置
     */
    public function saveAction()
    {
        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        $type  = $this->_request->getPost('type');
        $value = $this->_request->getPost('value');

        if (!$type || !$value) {
            return $this->json(false, '请选择登陆页面模板');
        }

        $org      = $daoOrg->getOrg(array('orgid' => $this->_orgId));
        $skinData = !empty($org->loginSkin) ? $org->loginSkin : array();
        if (isset($skinData['selected'])) {
            unset($skinData['selected']);
        }

        $selected  = array('selected' => array('type' => $type,'value' => $value));
        $loginSkin = array_merge($selected, $skinData);
        $loginSkin = json_encode($loginSkin);

        $ret = $daoOrg->updateOrg($this->_orgId, array('loginskin' => $loginSkin));

        if (!$ret) {
            return $this->json(false, '更新登陆页面模板失败');
        }

        $this->_cleanCache();

        // 日志部分
        $detail = $this->getSysLogKey($type, $value);
        $logDetail = array();
        if (!empty($detail)) {
            $logDetail['templet'] = $detail['skin'];
            if (!$detail['sys'] && !empty($detail['value'])) {
                $logDetail['templet_' . $type] = $detail['value'];
            }
        }
        $this->_createLog('org', 'update', 'page', $this->_orgId, $logDetail);

        $this->json(true, '更新登陆页面模板成功');
    }

    /**
     * 保存登陆页自定义颜色
     */
    public function saveColorAction()
    {
        $color = $this->_request->getPost('color');

        if (empty($color)) {
            return $this->json(false, '请先选择颜色');
        }

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg   = $this->getDao('Dao_Md_Org_Org');
        $org      = $daoOrg->getOrg(array('orgid' => $this->_orgId));
        $skinData = !empty($org->loginSkin) ? $org->loginSkin : array();
        if (isset($skinData['color'])) {
            unset($skinData['color']);
        }
        if (isset($skinData['selected'])) {
            unset($skinData['selected']['issystem']);
            unset($skinData['selected'][$skinData['selected']['type']]);
        }

        $skinColor = array('color' => array('value' => $color));
        $loginSkin = array_merge($skinData, $skinColor);
        $loginSkin = json_encode($loginSkin);

        $ret = $daoOrg->updateOrg($this->_orgId, array('loginskin' => $loginSkin));
        if (!$ret) {
            return $this->json(false, '保存登陆页背景颜色失败');
        }

        $this->json(true, '保存登陆页背景颜色成功', array('color' => $color));
    }

    /**
     * 保存上传的图片
     */
    public function saveImageAction()
    {
        $fileUrl   = $this->_request->getPost('fileurl');
        $fileType  = $this->_request->getPost('filetype');

        if (empty($fileUrl)) {
            return $this->json(false, '获取图片路径失败，请重试');
        }

        /* @var @daoOrg Dao_Md_Org_Org */
        $daoOrg   = $this->getDao('Dao_Md_Org_Org');
        $org      = $daoOrg->getOrg(array('orgid' => $this->_orgId));
        $skinData = !empty($org->loginSkin) ? $org->loginSkin : array();
        if (!is_array($skinData)) {
            $skinData = array();
        }
        if (isset($skinData['image'])) {
            unset($skinData['image']);
        }
        if (isset($skinData['selected'])) {
            unset($skinData['selected']['issystem']);
            unset($skinData['selected'][$skinData['selected']['type']]);
        }

        $image     = array('image' => array('filetype' => $fileType, 'fileurl' => $fileUrl));
        $loginSkin = array_merge($skinData, $image);
        $loginSkin = json_encode($loginSkin);

        $ret = $daoOrg->updateOrg($this->_orgId, array('loginskin' => $loginSkin));
        if (!$ret) {
            return $this->json(false, '保存图片数据失败');
        }

        $this->json(true, '保存图片数据成功', array('fileurl' => $fileUrl));
    }

    /**
     * 显示img
     */
    public function fileAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $hash = $this->_request->getQuery('hash');

        $options = $this->_options['upload'];

        $fileName = $options['savepath'] . '/loginpic/' . $hash;

        if (!file_exists($fileName) || !is_readable($fileName)) {
            return ;
        }

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
        $file = $_FILES['file'];
        $options = $this->_options['upload'];

        if (!is_uploaded_file($file['tmp_name'])) {
            return $this->json(false, '上传失败', null, false);
        }

        $mimes = array(
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png'
        );

        $message = null;
        do {
            // 文件大小判断
            if (filesize($file['tmp_name'])/1024 > $options['sizelimit']) {
                $message = '文件不能大于2MB';
                break;
            }

            // 文件类型判断
            $info = getimagesize($file['tmp_name']);
            if (!in_array($info['mime'], $mimes)) {
                $message = '图片类型有误';
                break;
            }
        } while (false);

        if ($message) {
            return $this->json(false, $message, null, false);
        }

        // 重新取名
        $fileName = md5_file($file['tmp_name']);

        // 实名认证目录
        $path = $options['savepath'];

        if (!is_dir($path)) {
            return $this->json(false, '文件保存路径配置有误', null, false);
        }

        // 打开实名认证目录
        $path    = $path . '/loginpic';
        $fileUrl = null;
        if (!is_dir($path)) {
            @mkdir($path, 0777);
        }

        if (is_dir($path)) {
            // 移动文件
            $ret = move_uploaded_file($file['tmp_name'], $path . '/' . $fileName);
            if (!$ret) {
                return $this->json(false, '上传登陆页背景图片失败', null, false);
            }

            // 文件路径
            $fileUrl = $fileName;

        }

        if (!$fileUrl) {
            return $this->json(true, '上传登陆页背景图片失败', null, false);
        }

        return $this->json(true, '上传登陆页背景图片成功', array('fileurl' => $fileUrl, 'filetype' => $info['mime']), false);
    }

    /**
     *
     * @param string $value
     */
    public function getSysLogKey($type, $skin)
    {
        $arr = $this->_sysLog;
        $ret = array();

        $skinInfo = explode(':', $skin);
        if ($skinInfo[0] != 'SYS') {
            $ret['sys']  = false;
            $ret['skin'] = 'custom_' . $type;
            $ret['value']  = $skinInfo[1];
        } else {
            $ret['sys'] = true;
            foreach ($arr as $key => $value) {
                if ($value == $skin) {
                    $ret['skin'] = $key;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     *
     */
    private function _cleanCache()
    {
        $key = 'TUDU-ORG-' . $this->_orgId;
        $this->_bootstrap->memcache->delete($key);

        $hosts = $this->getDao('Dao_Md_Org_Org')->getHosts($this->_orgId);
        foreach ($hosts as $host) {
            $this->_bootstrap->memcache->delete('TUDU-HOST-' . $host);
        }
    }
}