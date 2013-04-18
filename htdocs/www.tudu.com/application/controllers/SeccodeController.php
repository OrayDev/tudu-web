<?php
/**
 * 验证码接口
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: SeccodeController.php 1573 2012-02-13 10:37:27Z cutecube $
 */

/**
 *
 * @author CuTe_CuBe
 *
 */
class SeccodeController extends Zend_Controller_Action
{

    /**
     * 检查验证码是否显示
     */
    public function checkAction()
    {
        // Don't auto render this action
        $this->_helper->viewRenderer->setNoRender();

        $callback  = $this->_request->getQuery('cb');
        $namespace = $this->_request->getQuery('ns', 'default');

        $code = Oray_Seccode::getInstance()->getCode($namespace);

        $isShow = !empty($code) ? 'true' : 'false';
        $body   = '';

        if ($callback) {
            $body = <<<JS
if (typeof({$callback}) == 'function') {
if (window.addEventListener) {
window.addEventListener('load', function(){{$callback}({$isShow});}, false);
} else {
window.attachEvent('onload', function(){{$callback}({$isShow});});
}
}
JS;
        }

        echo $body;
    }

    /**
     * 验证码输出
     */
    public function indexAction()
    {
        $namespace = $this->_request->getQuery('ns', 'default');
        $size      = $this->_request->getQuery('sz');

        $bootstrap = $this->getInvokeArg('bootstrap');

        // Don't auto render this action
        $this->_helper->viewRenderer->setNoRender();

        $options = $bootstrap->getOption('seccode');

        // 设置大小
        if (false !== strpos($size, 'x')) {
            list($w, $h) = explode('x', $size, 2);
            if (abs((int) $w) && abs((int) $h)) {
                $options['image']['width']  = abs((int) $w);
                $options['image']['height'] = abs((int) $h);
            }
        }

        Oray_Seccode::getInstance()->setConfig($options)
                                   ->display(Oray_Seccode::TYPE_IMAGE, Oray_Seccode::LENGTH_DEFAULT, $namespace);
    }

}