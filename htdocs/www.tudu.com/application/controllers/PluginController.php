<?php
/**
 * Index Controller
 *
 * @author CuTe_CuBe
 * @version $Id: PluginController.php 1383 2011-12-13 10:40:45Z cutecube $
 */

class PluginController extends Zend_Controller_Action
{

    /**
     * 截屏控件安装页面
     */
    public function screencaptureAction()
    {
        $this->view->back = $this->_request->getQuery('back');
    }
}