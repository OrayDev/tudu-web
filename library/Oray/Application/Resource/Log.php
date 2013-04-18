<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Log.php 8863 2011-12-28 07:03:45Z gxx $
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for initializing the locale
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Oray
 * @package    Oray_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Application_Resource_Log extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Log
     */
    protected $_log;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Log
     */
    public function init()
    {
        return $this->getLog();
    }

    /**
     * Attach logger
     *
     * @param  Zend_Log $log
     * @return Zend_Application_Resource_Log
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    public function getLog()
    {
        if (null === $this->_log) {
            $options = $this->getOptions();
            foreach ($options as $key => $writer) {
                if ($writer['writerName'] == "Db" && is_array($writer['writerParams']['db'])) {
                    $params = $writer['writerParams']['db'];
                    $options[$key]['writerParams']['db'] = Zend_Db::factory($params['adapter'], $params);
                }
            }

            $log = Zend_Log::factory($options);
            $log->setTimestampFormat('Y-m-d H:i:s');;
            $this->setLog($log);
        }
        return $this->_log;
    }
}
