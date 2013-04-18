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
 * @version    $Id: Abstract.php 2024 2012-07-25 05:31:08Z cutecube $
 */

/**
 * 后台计划任务基类
 *
 * @category   Task
 * @package    Task
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Task_Abstract
{

    /**
     * 任务执行模式
     * 0.单次执行
     * 1.周期执行
     *
     * @var int
     */
    const MODE_ONCE  = 0;
    const MODE_CYCLE = 1;

    /**
     * 日志对象
     *
     * @var Zend_Log
     */
    protected $_log = null;

    /**
     * 执行参数
     *
     * @var array
     */
    protected $_params = null;

    /**
     * 程序配置项目
     *
     * @var array
     */
    protected $_options = null;

    /**
     * 执行时间限制
     *
     * @var int
     */
    protected $_timeLimit = 60;

    /**
     * 循环执行延时
     *
     * @var int
     */
    protected $_interval = 0;

    /**
     * 任务名称
     *
     * @var string
     */
    protected $_name = null;

    /**
     * 当前任务执行模式
     *
     * @var int
     */
    protected $_mode = self::MODE_ONCE;

    /**
     *
     * @var Task_Logger
     */
    protected $_logger = null;

    /**
     *
     * @var boolean
     */
    protected $_exitFlag = false;

    /**
     *
     * Constructor
     */
    public function __construct($options = null, array $params = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        if (is_array($params)) {
            $this->setParams($params);
        }
    }

    /**
     * 设置运行参数
     *
     * @param array $params
     * @return Task_Abstract
     */
    public function setParams(array $params)
    {
        $this->_params = $params;

        return $this;
    }

    /**
     * 设置执行模式
     *
     * @param int $mode
     * @return Task_Abstract
     */
    public function setMode($mode)
    {
        if (in_array($mode, array(0, 1), true)) {
            $this->_mode = $mode;
        }
        return $this;
    }

    /**
     *
     * @param int $limit
     * @return Task_Abstract
     */
    public function setLimit($limit)
    {
        if (is_int($limit) && $limit >= 0) {
            $this->_timeLimit = $limit;
        }
        return $this;
    }

    /**
     * 设置任务名称
     *
     * @param string $name
     * @return Task_Abstract
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * 设置执行间隔
     *
     * @param int $interval
     * @return Task_Abstract
     */
    public function setInterval($interval)
    {
        if (is_int($interval) && $interval >= 0) {
            $this->_interval = $interval;
        }
        return $this;
    }

    /**
     * 获取运行参数
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     *
     * @param array $options
     * @return Task_Abstract
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     *
     */
    public function getLogger()
    {
        if (null === $this->_logger) {
            $this->_logger = Task_Logger::getInstance($this->_name, $this->_options['log']);
        }

        return $this->_logger;
    }

    /**
     * 开始任务执行
     *
     */
    public final function start()
    {
        $this->getLogger()->debug("Start");

        $this->startUp();

        if ($this->_mode == self::MODE_CYCLE) {
            $startTime   = time();
            $elapsedTime = 0;
            do {
                $time = time();

                $this->run();

                $elapsedTime = time() - $time;
                if ($elapsedTime < $this->_interval) {
                    sleep($this->_interval - $elapsedTime);
                }

                if ($this->_timeLimit > 0 && time() - $startTime > $this->_timeLimit) {
                    break;
                }

                $this->getLogger()->debug("Round complete");

                if ($this->_exitFlag) {
                    break ;
                }

            } while (true);

        } else {
            $this->run();
        }

        $this->shutDown();

        $this->getLogger()->debug("End");

        return $this;
    }

    /**
     * 任务开启时调用函数
     *
     */
    public function startUp()
    {}

    /**
     * 执行完成后调用
     *
     */
    public function shutDown()
    {}

    /**
     * 执行流程，需要子类重写实现
     *
     */
    abstract public function run() ;

    /**
     * 退出
     */
    final public function quit()
    {
        $this->_exitFlag = true;
        echo 'b';exit();
    }
}