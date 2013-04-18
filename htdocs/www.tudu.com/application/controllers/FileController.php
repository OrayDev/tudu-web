<?php
/**
 * File Controller
 *
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @author     Oray-Yongfa
 * @version    $Id: FileController.php 2787 2013-03-20 10:52:53Z chenyongfa $
 */
class FileController extends Zend_Controller_Action
{
    /**
     * 输出图片（登陆页背景图）
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $fid      = $this->_request->getQuery('fid');
        $type     = $this->_request->getQuery('type');
        $upload   = $this->getInvokeArg('bootstrap')->getOption('upload');
        $fileName = null;

        if ($type == 'loginpic') {
            $hash = $this->_request->getQuery('hash');

            // 登陆页图片目录
            $loginPicPath = $upload['path'] . '/loginpic';

            // 登陆页图片目录不存在
            if (!is_dir($loginPicPath)) {
                return ;
            }

            $fileName = $loginPicPath . '/' . $hash;
        }

        if (!empty($fileName)) {
            $size = filesize($fileName);
            $info = getimagesize($fileName);
            $content = file_get_contents($fileName);

            $this->_response->setHeader('Content-Type', $info['mime']);
            $this->_response->setHeader('Content-Length', $size);
            $this->_response->sendHeaders();

            echo $content;
        }
    }
}
