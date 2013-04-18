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
 * @version    $Id: Task.php 1365 2011-12-08 10:15:18Z cutecube $
 */

/**
 * 后台计划任务创建对象
 *
 * @category   Task
 * @package    Task
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Task
{
    /**
     *
     * @param striong $className
     * @param array  $configs
     * @return Task_Abstract
     */
    public static function factory($className, array $configs = null)
    {
        $fileName = self::formatClassPath($className);

        // 文件不存在
        if (!file_exists(APPLICATION_PATH . '/library/' .$fileName)) {
            require_once 'Task/Exception.php';
            throw new Task_Exception("Task Class: {$className} not founded");
        }

        $task = new $className;

        // 对象不是有效的任务对象
        if (!$task instanceof Task_Abstract) {
            require_once 'Task/Exception.php';
            throw new Task_Exception("Task Class: {$className} is not inherit from Task_Abstract");
        }

        // 设置参数
        if (null !== $configs) {
            foreach ($configs as $key => $val) {
                switch ($key) {
                    case 'limit':
                    case 'interval':
                    case 'mode':
                        $func = 'set' . ucfirst($key);
                        $task->{$func}((int) $val);
                        break;
                }
            }
        }

        return $task;
    }

    /**
     * 从类名获取所在文件
     *
     * @param string $className
     */
    public static function formatClassPath($className)
    {
        return str_replace('_', '/', $className) . '.php';
    }
}