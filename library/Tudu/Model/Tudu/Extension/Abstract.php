<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 1970 2012-07-05 01:41:34Z cutecube $
 */

/**
 * 扩展业务流程基类
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Tudu_Model_Tudu_Extension_Abstract
{
    /**
     * 调用 Tudu_Model_Tudu_Compose中与当前类名相同的方法
     *
     * @param Tudu_Model_Entity_Tudu $tudu
     */
    public function composeHandler(Tudu_Model_Entity_Tudu $tudu)
    {}

    /**
     * 处理图度保存时扩展的流程
     *
     * @param Tudu_Model_Tudu_Entity_Tudu               $tudu
     * @param Tudu_Model_Tudu_Entity_Extension_Abstract $data
     */
    public function onSave(Tudu_Model_Tudu_Entity_Tudu $tudu, Tudu_Model_Tudu_Entity_Extension_Abstract $data)
    {}

    /**
     * 获取扩展流程对象
     *
     * @param $name
     */
    public function getExtension($name)
    {
        // 未注册或调用，尝试自动加载
        if (!isset(Tudu_Model_Tudu_Abstract::$_extensions[$name])) {
            $clsName = ucfirst(strtolower($name));
            $arr     = Tudu_Model_Tudu_Abstract::$_extensionNameSpaces;

            foreach ($arr as $ns => $path) {
                $className = $ns . '_' . $clsName;
                $fileName  = $path . '/' . $clsName . '.php';

                if (file_exists($fileName)) {
                    require_once $fileName;
                    Tudu_Model_Tudu_Abstract::$_extensions[$name] = new $className();
                    break ;
                }
            }

            // 没有的抛出异常
            if (!isset(Tudu_Model_Tudu_Abstract::$_extensions[$name])) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception("Extension names: {$name} not found");
            }
        }

        return Tudu_Model_Tudu_Abstract::$_extensions[$name];
    }
}