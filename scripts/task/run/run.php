<?php
/**
 * 图度后台任务统一入口程序 for PHP CLI
 *
 * 参数规则
 * php run.php {$task} [{$param1}[{$param2}...]]
 * 首位参数为 需要执行任务的名称，任务由配置文件中定义，其后的所有参数将作为该任务的参数传入
 */

error_reporting(E_ALL);
set_time_limit(0);
//set_error_handler('errorHandler');

// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__)) . '/../');

// WWW_ROOT
defined('WWW_ROOT') ||
    define('WWW_ROOT', APPLICATION_PATH . '/../../');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../../library'),
    realpath(APPLICATION_PATH . '/library'),
    get_include_path()
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace(array('Oray_', 'Tudu_', 'Dao_', 'Task_', 'Model_'));

// Load config
$config = new Zend_Config_Ini(APPLICATION_PATH . 'configs/config.ini', 'production');
$config = $config->toArray();

$taskName = $argv[1];

// 传入未注册的脚本任务
if (!isset($config['task'][$taskName])) {
    exit("Unregistered task [{$taskName}]\n");
}

// 处理脚本传入参数
$params = array();
foreach ($argv as $val) {
    if (strpos($val, '--') !== 0 && false === strpos($val, '=')) {
        continue ;
    }

    list($k, $v) = explode('=', $val);

    $k = str_replace('--', '', $k);

    if (in_array($k, array('mode', 'limit', 'interval'))) {
        $config['task'][$taskName][$k] = $v;
        continue ;
    }

    $params[$k] = $v;
}

// logger
$logger = Task_Logger::getInstance($taskName, $config);

$className = $config['task'][$taskName]['classname'];

set_error_handler('errorHandler');

try {

    $task = Task_Task::factory($className, $config['task'][$taskName]);
    $task->setName($taskName)
         ->setOptions($config)
         ->setParams($params)
         ->start();

} catch (Task_Exception $e) {
    $logger->error($e->getMessage());
}

function errorHandler($errno, $errstr, $errfile, $errline)
{
    global $logger, $task;
    if (!isset($logger)) return ;

    if (isset($task) && $task instanceof Task_Abstract) {
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_USER_ERROR:
                $logger->error("$errstr $errfile line[$errline]");
                $task->quit();
                break;
            default:
                $logger->warn("$errstr $errfile line[$errline]");
        }

        // httpsqs 错误
        if (false !== strpos($errfile, 'Httpsqs') && false !== strpos($errstr, 'unable to connect')) {
            $task->quit();
        }

        return ;
    }

    $logger->error("$errstr $errfile line[$errline]");
}