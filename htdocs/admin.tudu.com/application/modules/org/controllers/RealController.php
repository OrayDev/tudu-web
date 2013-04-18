<?php
/**
 * 企业实名认证控制器
 *
 * LICENSE
 *
 *
 * @package    Admin
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @license    NULL
 * @version    $Id: RealController.php 2713 2013-01-23 10:17:49Z cutecube $
 */

/**
 * @copyright Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @package   Admin
 */
class Org_RealController extends TuduX_Controller_Admin
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

        $this->lang = Tudu_Lang::getInstance()->load(array('common'));
        $this->view->LANG   = $this->lang;
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
            $this->referer($this->_request->getBasePath() . '/login/');
        }

        $action = strtolower($this->_request->getActionName());
        /*if (!$this->_user->isOwner()) {
            if (in_array($action, array('save', 'upload'))) {
                return $this->json(false, '非超级管理员帐户不能进行该操作');
            } else {
                Oray_Function::alert('非超级管理员帐户不能进行该操作');
            }
        }*/

        if (in_array($this->_orgId, $this->_demoOrg)) {
            if (in_array($action, array('save', 'upload'))) {
                return $this->json(false, '体验帐号不能更改后台设置');
            }
        }
    }

    /**
     * 企业实名认证页面
     */
    public function indexAction()
    {
        /* @var $daoReal Dao_Md_Org_Real */
        $daoInfo = $this->getDao('Dao_Md_Org_Info');

        $info = $daoInfo->getOrgInfo(array('orgid' => $this->_orgId));

        if (null == $info) {
            $ret = $daoInfo->create(array(
                'orgid'          => $this->_orgId,
                'orgname'        => $this->_orgId,
                'realnamestatus' => 0
            ));

            $info = array('realnamestatus' => 0);
        } else {
            $info = $info->toArray();

            $daoRealName = $this->getDao('Dao_Md_Org_Real');

            $realname = $daoRealName->getRealName(array('orgid' => $this->_orgId));

            if (null !== $realname) {
                $this->view->realname = $realname->toArray();
            }
        }

        $this->view->cookies = serialize($this->_request->getCookie());
        $this->view->info = $info;
    }

    /**
     * 提交企业实名认证申请
     */
    public function saveAction()
    {
        $fileUrl = $this->_request->getPost('fileurl');

        if (empty($fileUrl)) {
            return $this->json(false, '获取图片路径失败，请重试');
        }

        $params = array(
            'realnameid' => Dao_Md_Org_Real::getRealNameId($this->_orgId),
            'orgid'      => $this->_orgId,
            'fileurl'    => $fileUrl,
            'createtime' => time()
        );

        /* @var $daoReal Dao_Md_Org_Real */
        $daoInfo = $this->getDao('Dao_Md_Org_Info');

        /* @var $daoReal Dao_Md_Org_Real */
        $daoReal = $this->getDao('Dao_Md_Org_Real');

        $ret = $daoReal->create($params);

        if (!$ret) {
            return $this->json(false, '您的实名认证申请提交失败');
        }

        $daoInfo->update($this->_orgId, array('realnamestatus' => 1));

        return $this->json(true, '您的实名认证申请已成功提交');
    }

    /**
     * 显示img
     */
    public function fileAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $hash = $this->_request->getQuery('hash');

        $options = $this->_options['upload'];

        $fileName = $options['savepath'] . '/realname/' . $hash;

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
            return $this->json(false, '网站实名认证文件路径配置有误', null, false);
        }

        // 打开实名认证目录
        $path    = $path . '/realname';
        $fileUrl = null;
        if (!is_dir($path)) {
            @mkdir($path, 0777);
        }

        if (is_dir($path)) {
            $orgPath = $path . '/' . $this->_orgId;

            // 实名认证组织目录是否存在，不存在则创建
            if (!is_dir($orgPath)) {
                mkdir($orgPath, 0777);
            }

            // 移动文件
            $ret = move_uploaded_file($file['tmp_name'], $orgPath . '/' . $fileName);
            if (!$ret) {
                return $this->json(false, '上传实名认证文件失败', null, false);
            }

            // 文件路径
            $fileUrl = $this->_orgId . '/' . $fileName;

        }

        if (!$fileUrl) {
            return $this->json(true, '上传实名认证文件失败', null, false);
        }

        return $this->json(true, '上传实名认证文件成功', array('fileurl' => $fileUrl), false);
    }
}