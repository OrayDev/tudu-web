<?php
/**
 * 企业组织信息设置控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: InfoController.php 2826 2013-04-16 09:48:07Z chenyongfa $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Org_InfoController extends TuduX_Controller_Admin
{
    public function init()
    {
        if ($cookies = $this->_request->getParam('cookies')) {
            if ($cookies = @unserialize($cookies)) {
                foreach ($cookies as $key => $val) {
                    $_COOKIE[$key] = $val;
                }
            }
        }

        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'org'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        $action = str_replace('.', '-', strtolower($this->_request->getActionName()));

        if (!$this->_user->isAdminLogined()) {
            // ajax 提交的
            if (in_array($action, array('save', 'upload', 'logo-delete', 'logo-save'))) {
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
        /* @var $daoInfo Dao_Md_Org_Info */
        $daoInfo = $this->getDao('Dao_Md_Org_Info');

        $org = $daoOrg->getOrg(array('orgid' => $this->_orgId))->toArray();
        $info = $daoInfo->getOrgInfo(array('orgid' => $this->_orgId));

        if ($info) {
            $action = 'update';
            $info = $info->toArray();
        } else {
            $action = 'create';
        }

        $org['intro'] = strip_tags(str_replace(array('<br>', '<br />', '<br/>'), "\n", $org['intro']));

        $this->view->site   = $this->_options['sites'];
        $this->view->action = $action;
        $this->view->info   = $info;
        $this->view->org    = $org;
    }

    /**
     * 保存设置
     */
    public function saveAction()
    {
        $action = $this->_request->getPost('action');
        $post   = $this->_request->getPost();

        if (empty($post['orgname'])) {
            return $this->json(false, '请输入组织简称');
        }

        if (!empty($post['intro']) && Oray_Function::strLen($post['intro']) > 300) {
            return $this->json(false, '组织简介长度请控制在300字符以内');
        }

        /* @var $modelOrg Model_Org_Org*/
        $modelOrg = Tudu_Model::factory('Model_Org_Org');

        try {
            $modelOrg->execute('info', array(array(
                'orgid'       => $this->_orgId,
                'entirename'  => $post['entirename']
                //'industry'    => $post['industry'],
                //'contact'     => $post['contact'],
                //'tel'         => $post['tel'],
                //'fax'         => $post['fax'],
                //'postcode'    => $post['postcode'],
                //'address'     => $post['address'],
                //'province'    => $post['province'],
                //'city'        => $post['city']
            )));

            $modelOrg->execute('updateOrg', array(array(
                'orgid'       => $this->_orgId,
                'orgname'     => $post['orgname'],
                'intro'       => $post['intro']
            )));
        } catch (Model_Org_Exception $e) {
            switch ($e->getCode()) {
                case Model_Org_Org::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Org_Org::CODE_ORG_NOTEXISTS:
                    $message = '组织不存在或已被删除';
                    break;
                case Model_Org_Org::CODE_INVALID_ORGNAME:
                    $message = '请输入组织简称';
                    break;
                case Model_Org_Org::CODE_SAVE_FAILED:
                    $message = '更新组织信息失败';
                    break;
            }

            return $this->json(false, $message);
        }

        $this->_cleanCache();

        return $this->json(true, '更新企业信息成功');

    }

    /**
     * 组织Logo修改页面
     */
    public function logoAction()
    {
        $cookies = $this->_request->getCookie();

        $this->view->cookies = serialize($cookies);
    }

    /**
     * 保存Logo
     */
    public function logoSaveAction()
    {
        $hash    = $this->_request->getPost('hash');
        $post    = $this->_request->getPost();
        $options = $this->_bootstrap->getOptions();

        $x      = (int) $post['x'];
        $y      = (int) $post['y'];
        $width  = (int) $post['width'];
        $height = (int) $post['height'];

        $logo     = null;
        $logoType = null;

        $mimes = array(
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        );

        $fileName = $options['upload']['tempdir'] . '/' . $hash;
        if (!$hash || !file_exists($fileName)) {
            return $this->json(false, '文件上传失败');
        }

        $info = getimagesize($fileName);
        $logoType = $info['mime'];

        if (!in_array($logoType, $mimes)) {
            $this->json(false, '无效的头像文件格式');
        }

        $type = array_flip($mimes);
        $func = 'imagecreatefrom' . $type[$logoType];
        $outputFunc = 'image' . $type[$logoType];

        // tudutalk 不支持gif，先转成jpg
        if ($outputFunc == 'imagegif') {
            $outputFunc = 'imagejpeg';
        }

        $maxWidth  = 170;
        $maxHeight = 50;

        $img = imagecreatetruecolor($maxWidth, $maxHeight);
        // 填充白色底色
        imagefilledrectangle($img, 0, 0, 170, 50, 16777215);

        $src = $func($fileName);

        $width  = $width <= 0 ? $info[0] : $width;
        $height = $height <= 0 ? $info[1] : $height;

        $dst_x = 0;
        $dst_y = 0;
        $dst_w = $maxWidth;
        $dst_h = $maxHeight;
        if (($maxWidth / $maxHeight - $width / $height) > 0) {
            $dst_w = $width * ( $maxHeight / $height );
            $dst_x = ( $maxWidth - $dst_w ) / 2;
        } elseif (($maxWidth / $maxHeight - $width / $height) < 0) {
            $dst_h = $height * ( $maxWidth / $width );
            $dst_y = ( $maxHeight - $dst_h ) / 2;
        }

        imagecopyresampled($img, $src, $dst_x, $dst_y, $x, $y, $dst_w, $dst_h, $width, $height);

        $outputFunc($img);

        $content = null;
        while ($r = ob_get_contents()) {
            $content .= $r;
            ob_clean();
        }

        /* @var $modelOrg Model_Org_Org*/
        $modelOrg = Tudu_Model::factory('Model_Org_Org');

        try {
            $modelOrg->execute('updateOrg', array(array(
                'orgid' => $this->_orgId,
                'logo'  => $content
            )));
        } catch (Model_Org_Exception $e) {
            switch ($e->getCode()) {
                case Model_Org_Org::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Org_Org::CODE_ORG_NOTEXISTS:
                    $message = '组织不存在或已被删除';
                    break;
                case Model_Org_Org::CODE_SAVE_FAILED:
                    $message = 'Logo更新失败';
                    break;
            }

            return $this->json(false, $message);
        }

        return $this->json(true, 'Logo更新成功');
    }

    /**
     * 显示临时Logo
     */
    public function logoImgAction()
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

        $file = $_FILES['filedata'];
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

        $ret = @move_uploaded_file($file['tmp_name'], $options['tempdir'] . '/' . $fileName);

        if (!$ret) {
            return $this->json(false, $this->lang['logo_upload_failure'], null, false);
        }

        $this->json(true, $this->lang['logo_upload_success'], array('logo' => $fileName), false);
    }

    /**
     * 删除logo
     */
    public function logoDeleteAction()
    {
        /* @var $modelOrg Model_Org_Org*/
        $modelOrg = Tudu_Model::factory('Model_Org_Org');

        try {
            $modelOrg->execute('updateOrg', array(array(
                'orgid' => $this->_orgId,
                'logo'  => null
            )));
        } catch (Model_Org_Exception $e) {
            switch ($e->getCode()) {
                case Model_Org_Org::CODE_INVALID_ORGID:
                    $message = '缺少参数[orgid]';
                    break;
                case Model_Org_Org::CODE_ORG_NOTEXISTS:
                    $message = '组织不存在或已被删除';
                    break;
                case Model_Org_Org::CODE_SAVE_FAILED:
                    $message = '还原默认Logo失败';
                    break;
            }

            return $this->json(false, $message);
        }

        return $this->json(true, $this->lang['logo_revert_success']);
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

        $hosts = $this->getDao('Dao_Md_Org_Org')->getHosts($this->_orgId);
        foreach ($hosts as $host) {
            $this->_bootstrap->memcache->delete('TUDU-HOST-' . $host);
        }
    }
}