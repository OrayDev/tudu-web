<?php
/**
 * Job
 *
 * LICENSE
 *
 *
 * @category   Task
 * @package    Task
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Logger.php 1709 2012-03-15 10:02:44Z cutecube $
 */

/**
 * 后台计划任务日志对象包装类
 *
 * @category   Task
 * @package    Task
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Logger
{
    /**
     *
     * @var string
     */
    private $_taskName = null;

    /**
     *
     * @var string
     */
    private $_configs  = null;

    /**
     *
     * @var Zend_Log
     */
    private $_logger = null;

    /**
     *
     * @var Task_Logger
     */
    protected static $_instance = null;

    /**
     *
     * @param string $taskName
     * @param array $configs
     * @return Task_log
     */
    public static function getInstance($taskName = null, array $configs = null)
    {
        if (null == self::$_instance) {
            self::$_instance = new self($taskName, $configs);
        }

        return self::$_instance;
    }

    /**
     *
     * @param string $taskName
     * @return Task_Logger
     */
    public function setTaskName($taskName)
    {
        $this->_taskName = $taskName;
        return $this;
    }

    /**
     *
     * @param array $configs
     * @return Task_Logger
     */
    public function setConfig(array $configs)
    {
        $this->_configs = $configs;
        return $this;
    }

    /**
     *
     */
    public function getLogger()
    {
        if (null === $this->_logger) {
            if (null === $this->_configs || !isset($this->_configs['log'])) {
                require_once dirname(__FILE__) . '/Exception.php';
                throw new Task_Exception("Logger config is null");
            }

            $configs = $this->_configs['log'];

            foreach ($configs as $key => &$config) {
                if ($key == 'debug') {continue ;}
                $params = &$config['writerParams'];

                if (strtolower($key) == 'db') {
                    if (!empty($params['db']) && !empty($params['table'])) {
                        $multidb             = $this->_configs['multidb'];
                        $params['db']        = Zend_Db::factory($multidb[$params['db']]['adapter'], $multidb[$params['db']]['params']);
                        $params['columnMap'] = array(
                            'SEVERITY' => 'priority',
                            'ORIGIN'   => 'from',
                            'MESSAGE'  => 'message'
                        );
                    } else {
                        unset($configs[$key]);
                    }
                }
            }

            unset($configs['debug']);
            $this->_logger = Zend_Log::factory($configs);
        }

        return $this->_logger;
    }

    /**
     * 写入信息日志
     *
     * @param $message
     */
    public function info($message, $extras = array())
    {
        return $this->log($this->_formatMessage($message), Zend_Log::INFO, $this->_formatExtras($extras));
    }

    /**
     *
     * @param $message
     */
    public function warn($message, $extras = array())
    {
        return $this->log($this->_formatMessage($message), Zend_Log::WARN, $this->_formatExtras($extras));
    }

    /**
     * 输出错误日志
     *
     * @param $message
     */
    public function error($message, $extras = array())
    {
        return $this->log($this->_formatMessage($message), Zend_Log::ERR, $this->_formatExtras($extras));
    }

    /**
     * debug 输出
     *
     * @param $message
     */
    public function debug($message, $extras = array())
    {
        if (!empty($this->_configs['log']['debug'])) {
            return $this->log($this->_formatMessage($message), Zend_Log::DEBUG, $this->_formatExtras($extras));
        }

        return true;
    }

    /**
     *
     * @param $message
     * @param $type
     */
    public function log($message, $type, $extras = array())
    {
        return $this->getLogger()->log($message, $type, $extras);
    }

    /**
     *
     * @param string $taskName
     * @param array  $configs
     */
    protected function __construct($taskName = null, array $configs = null)
    {
        if (is_string($taskName) && $taskName) {
            $this->setTaskName($taskName);
        }

        if (null != $configs) {
            $this->setConfig($configs);
        }
    }

    /**
     *
     * @param  string $message
     * @return string
     */
    protected function _formatMessage($message)
    {
        /*if ($this->_taskName) {
            $message = "{$message}";
        }*/

        return $message;
    }

    /**
     *
     * @param array $extras
     */
    protected function _formatExtras($extras = array())
    {
        if (!is_array($extras)) {
            $extras = array($extras);
        }

        if (empty($extras['from'])) {
            $extras['from'] = Task_Task::formatClassPath($this->_configs['task'][$this->_taskName]['classname']);
        }

        return $extras;
    }
}