<?php
/**
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id$
 */

/**
 * @see Tudu_Model_ResouceManager_Abstract
 */
require_once 'Tudu/Model/ResourceManager/Abstract.php';

/**
 * 后台计划任务基类
 *
 * @category   Tudu
 * @package    Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_ResourceManager_Bootstrap extends Tudu_Model_ResourceManager_Abstract
{
    /**
     *
     * @var Zend_Application_Bootstrap_BootstrapAbstract
     */
    protected $_bootstrap = null;

    /**
     * 构造函数
     *
     * @param Zend_Application_Bootstrap_BootstrapAbstract $bootstrap
     */
    public function __construct(Zend_Application_Bootstrap_BootstrapAbstract $bootstrap)
    {
        $this->_bootstrap = $bootstrap;
    }

    /**
     * (non-PHPdoc)
     * @see Tudu_Model_ResourceManager_Abstract::getResource()
     */
    public function getResource($name)
    {
        try {
            $res = $this->_bootstrap->getResource($name);
        } catch (Zend_Application_Bootstrap_Exception $e) {
            require 'Tudu/Model/ResourceManager/Exception.php';
            throw new Tudu_Model_ResourceManager_Exception('Undefined resource named: ' . $name);
        }

        return $res;
    }

    /**
     * (non-PHPdoc)
     * @see Tudu_Model_ResourceManager_Abstract::hasResource()
     */
    public function hasResource($name)
    {
        $bool = $this->_bootstrap->hasResource($name);

        if (!$bool) {
            try {
                $this->_bootstrap->bootstrap($name);

                $bool = true;
            } catch (Zend_Application_Bootstrap_Exception $e) {
                return false;
            }
        }

        return $bool;
    }
}