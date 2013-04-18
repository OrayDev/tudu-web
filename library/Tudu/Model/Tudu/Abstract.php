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
 * @version    $Id: Abstract.php 1957 2012-07-02 06:54:25Z web_op $
 */

/**
 * @see Tudu_Model_Abstract
 */
require_once 'Tudu/Model/Abstract.php';

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * 图度业务模型基类
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Abstract
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Abstract extends Tudu_Model_Abstract
{
    /**
     * 定义资源名称
     *
     * @var string
     */
    const RESOURCE_NAME_USER    = 'user';
    const RESOURCE_NAME_CACHE   = 'cache';
    const RESOURCE_NAME_HTTPSQS = 'httpsqs';

    /**
     * 扩展流程插件列表
     *
     * @var array
     */
    public static $_extensions = array();

    /**
     * 扩展命名空间列表
     *
     * @var array
     */
    public static $_extensionNameSpaces = array();

    /**
     *
     */
    public function __construct()
    {
        self::registerExtensionNameSpace('Tudu_Model_Tudu_Extension');
    }

    /**
     *
     * @param string $name
     * @param Tudu_Model_Tudu_Extension_Abstract $extension
     * @return Tudu_Model_Tudu_Compose
     */
    public static function registerExtension($name, Tudu_Model_Tudu_Extension_Abstract $extension)
    {
        $this->_extensions[$name] = $extension;
    }

    /**
     * 获取扩展流程对象
     *
     * @param $name
     */
    public function getExtension($name)
    {
        // 未注册或调用，尝试自动加载
        if (!isset(self::$_extensions[$name])) {
            $clsName = ucfirst(strtolower($name));
            $arr     = self::$_extensionNameSpaces;

            foreach ($arr as $ns => $path) {
                $className = $ns . '_' . $clsName;
                $fileName  = $path . '/' . $clsName . '.php';

                if (file_exists($fileName)) {
                    require_once $fileName;
                    self::$_extensions[$name] = new $className();
                    break ;
                }
            }

            // 没有的抛出异常
            if (!isset(self::$_extensions[$name])) {
                require_once 'Tudu/Model/Tudu/Exception.php';
                throw new Tudu_Model_Tudu_Exception("Extension names: {$name} not found");
            }
        }

        return self::$_extensions[$name];
    }

    /**
     *
     * @param string $nameSpace
     */
    public static function registerExtensionNameSpace($nameSpace, $path = null)
    {
        if (!is_string($nameSpace)) {
            require_once 'Tudu/Model/Tudu/Exception.php';
            throw new Tudu_Model_Tudu_Exception('Extension namespace must be a string');
        }

        if (null === $path) {
            $path = realpath(dirname(__FILE__) . '/../../../') . '/' . str_replace('_', DIRECTORY_SEPARATOR, $nameSpace);
        }

        self::$_extensionNameSpaces[$nameSpace] = $path;
    }

    /**
     * 获取Dao实例
     *
     * @param string $className
     * @param Zend_Db_Adapter_Abstract $db
     * @return Oray_Dao_Abstract
     */
    public function getDao($className, Zend_Db_Adapter_Abstract $db = null)
    {
        if (!isset($this->_dao[$className])) {
            if (null === $db) {
                $db = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::TS);
            }

            $this->_dao[$className] = Tudu_Dao_Manager::getDao($className, $db);
        }

        return $this->_dao[$className];
    }

    /**
     * 创建日志
     *
     * @param string  $targetType
     * @param string  $targetId
     * @param string  $action
     * @param array   $detail
     * @param boolean $privacy
     * @param boolean $isSystem
     * @return void
     */
    public function createLog($targetType, $targetId, $action, $detail = null, $privacy = false, $isSystem = false)
    {
        if (null !== $detail) {
            $detail = serialize($detail);
        }

        $user  = self::getResource(self::RESOURCE_NAME_USER);

        $daoLog = Tudu_Dao_Manager::getDao('Dao_Td_Log_Log', Tudu_Dao_Manager::DB_TS);
        return $daoLog->createLog(array(
            'orgid' => $user->orgId,
            'uniqueid' => $user->uniqueId,
            'operator' => $isSystem ? '^system 图度系统' : $user->userName . ' ' . $user->trueName,
            'logtime'  => time(),
            'targettype' => $targetType,
            'targetid' => $targetId,
            'action' => $action,
            'detail' => $detail,
            'privacy' => $privacy ? 1 : 0
        ));
    }

    /**
     * 获取日志详细信息
     *
     * @param $tudu
     * @param $fromTudu
     */
    protected function _getLogDetails($params, $fromTudu)
    {
        if (null === $fromTudu || $fromTudu->isDraft) {
            $params['to'] = !empty($params['to']) ? Tudu_Tudu_Storage::formatReceiver($params['to']) : null;
            $params['cc'] = !empty($params['cc']) ? Tudu_Tudu_Storage::formatReceiver($params['cc']) : null;
            $params['bcc'] = !empty($params['bcc']) ? Tudu_Tudu_Storage::formatReceiver($params['bcc']) : null;
            //$params['reviewer'] = !empty($params['reviewer']) ? Tudu_Tudu_Storage::formatReceiver($params['reviewer']) : null;

            foreach ($params as $key => $val) {
                if (null === $val || '' === $val || $key == 'attach') {
                    unset($params[$key]);
                }
            }

            return $params;
        }

        $excepts = array('attach', 'uniqueid', 'status', 'poster', 'posterinfo', 'lastposter', 'issend');

        $tudu = $fromTudu->toArray();
        $ret  = array();
        foreach ($params as $key => $val) {
            if (in_array($key, $excepts) || empty($val)) {
                continue ;
            }

            if ($key == 'to') {
                if (count($params[$key]) != count($tudu['accepter'])) {
                    $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                } else {
                    foreach ($params[$key] as $k => $val) {
                        if (!in_array($k, $tudu['accepter'])) {
                            $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
                        }
                    }
                }
                continue ;
            }

            if ($key == 'cc' || $key == 'bcc'/* || $key == 'reviewer'*/) {
                $val = Tudu_Tudu_Storage::formatReceiver($params[$key]);
            }

            if (array_key_exists($key, $tudu) && $params[$key] != $tudu[$key]) {
                $ret[$key] = $val;
            }
        }

        return $ret;
    }
}