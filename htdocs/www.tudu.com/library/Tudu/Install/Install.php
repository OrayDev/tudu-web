<?php
/**
 * Tudu_Install_Install
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @author     Oray-Yongfa
 * @version    $Id: Install.php 2814 2013-04-09 06:02:19Z chenyongfa $
 *
 */
Class Tudu_Install_Install
{
    /**
     *
     * @var array
     */
    protected $_configs;

    /**
     *
     * @var string
     */
    protected $_dataPath;

    /**
     *
     * @var array
     */
    protected $_configPath;

    /**
     *
     * @var array
     */
    protected $_orgParams;

    /**
     *
     * @var Tudu_Install_Install
     */
    protected static $_instance;

    /**
     * 单例模式，隐藏构造函数
     */
    protected function __construct()
    {}

    /**
     * 获取对象实例
     *
     * @return Tudu_Install_Install
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @return Tudu_Install_Install
     */
    public static function newInstance()
    {
        return new self();
    }

    /**
     * 设置配置
     *
     * @param array $config
     */
    public function setConfigs($config)
    {
        $this->_configs = $config;
    }

    /**
     *
     * @param string $path
     */
    public function setDataPath($path)
    {
        $this->_dataPath = $path;
    }

    /**
     *
     * @param array $path
     */
    public function setConfigPaths($path)
    {
        $this->_configPath = $path;
    }

    /**
     *
     * @param array $params
     */
    public function setOrgParams($params)
    {
        $this->_orgParams = $params;
    }

    /**
     * 创建配置文件
     */
    public function saveConfigFile()
    {
        if (empty($this->_configPath)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('config path error');
        }
  
        if (empty($this->_configs)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('empty configs');
        }

        $iniOptions = array(
            'allowModifications' => true, 'nestSeparator' => '###', 'skipExtends' => true
        );

        foreach ($this->_configPath as $configKey => $path) {
            // 创建站点配置文件
            if ($configKey == 'www' || $configKey == 'admin') {

                // 定义常量明称，以便在Writer里可“还原”成常量
                $consts = array('APPLICATION_PATH', 'WWW_ROOT', 'PROTOCOL', 'HOST');

                // 初始化Writer
                require_once 'Oray/Config/Writer/Ini.php';
                require_once 'Oray/Config/Writer/Array.php';
                $writerIni = new Oray_Config_Writer_Ini();
                $writerIni->setConst($consts);
                $writerArray = new Oray_Config_Writer_Array();
                $writerArray->setConst($consts);

                $file = $path . DIRECTORY_SEPARATOR . 'application.ini.dist';

                if(!file_exists($file)) {
                    require_once 'Tudu/Install/Exception.php';
                    throw new Tudu_Install_Exception('File "' . $file . '" exists');
                }

                $dbkeys = array('md', 'ts1', 'app');
                $source = array();

                foreach ($this->_configs as $key => $item) {
                    switch ($key) {
                        case 'mysql':
                            foreach ($item as $k => $value) {
                                foreach ($dbkeys as $db) {
                                    $source["resources.multidb.{$db}.{$k}"] = $value;
                                }
                            }
                            break;
                        case 'httpsqs':
                            foreach ($item as $k => $value) {
                                $source["httpsqs.{$k}"] = $value;
                            }
                            break;
                        case 'memcache':
                            foreach ($item as $k => $value) {
                                $source["resources.memcache.servers.{$k}"] = $value;
                            }
                            break;
                        case 'sphinx':
                            foreach ($item as $k => $value) {
                                $source["sphinx.{$k}"] = $value;
                            }
                            break;
                        default:
                            $source[$key] = $item;
                    }
                }

                require_once 'Zend/Config/Ini.php';
                $target = new Zend_Config_Ini($file, null, $iniOptions);
                // 替换相应配置de值
                foreach ($target->production as $key => $value) {
                    if (isset($source[$key])) {
                        $target->production->{$key} = $source[$key];
                    }
                }

                $iniFile = dirname($file) . DIRECTORY_SEPARATOR . basename($file, ".dist");
                try {
                    $writerIni->write($iniFile, $target);
                } catch (Zend_Config_Exception $e) {
                    require_once 'Tudu/Install/Exception.php';
                    throw new Tudu_Install_Exception($e->getMessage());
                }

            // 创建脚本配置文件
            } elseif ($configKey == 'script') {
                $file = $path . DIRECTORY_SEPARATOR . 'config.ini.dist';

                if(!file_exists($file)) {
                    require_once 'Tudu/Install/Exception.php';
                    throw new Tudu_Install_Exception('File "' . $file . '" exists');
                }

                $dbkeys = array('md', 'ts1', 'app');
                $source = array();

                foreach ($this->_configs as $key => $item) {
                    switch ($key) {
                        case 'mysql':
                            foreach ($item as $k => $value) {
                                foreach ($dbkeys as $db) {
                                    $source["multidb.{$db}.params.{$k}"] = $value;
                                }
                            }
                            break;
                        case 'httpsqs':
                        case 'memcache':
                            foreach ($item as $k => $value) {
                                $source["{$key}.{$k}"] = $value;
                            }
                            break;
                        default:
                            $source[$key] = $item;
                    }
                }

                require_once 'Zend/Config/Ini.php';
                $target = new Zend_Config_Ini($file, null, $iniOptions);
                // 替换相应配置de值
                foreach ($target->production as $key => $value) {
                    if (isset($source[$key])) {
                        $target->production->{$key} = $source[$key];
                    }
                }

                require_once 'Zend/Config/Writer/Ini.php';
                $writerIni = new Zend_Config_Writer_Ini();
                $iniFile = dirname($file) . DIRECTORY_SEPARATOR . basename($file, ".dist");
                try {
                    $writerIni->write($iniFile, $target);
                } catch (Zend_Config_Exception $e) {
                    require_once 'Tudu/Install/Exception.php';
                    throw new Tudu_Install_Exception($e->getMessage());
                }
            }
        }
    }

    /**
     * 创建图度组织
     */
    public function createOrg()
    {
        if (empty($this->_orgParams)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('empty org params');
        }

        if (empty($this->_configs['mysql'])) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('empty configs database');
        }

        $this->_configs['mysql'] = array_merge($this->_configs['mysql'], array('charset' => 'utf8'));

        require_once 'Zend/Db.php';
        require_once 'Zend/Db/Exception.php';
        $db = Zend_Db::factory('pdo_mysql', $this->_configs['mysql']);

        require_once 'Tudu/Dao/Manager.php';
        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD => $db,
            Tudu_Dao_Manager::DB_TS => $db
        ));

        if (!empty($this->_dataPath)) {
            $dataPath = array('data' => array('path' => $this->_dataPath));

            require_once 'Tudu/Model.php';
            require_once 'Tudu/Model/ResourceManager/Registry.php';
            $resourceManager = new Tudu_Model_ResourceManager_Registry();
            $resourceManager->setResource('config', $dataPath);
            Tudu_Model::setResourceManager($resourceManager);
        }

        require_once 'Model/Org/Org.php';
        require_once 'Model/Org/Exception.php';

        /* @var $modelOrg Model_Org_Org */
        $modelOrg = Tudu_Model::factory('Model_Org_Org');
        try {
            $modelOrg->addAction('create', array($modelOrg, 'createAdmin'), 10);
            $modelOrg->addAction('create', array($modelOrg, 'active'), 9);

            $modelOrg->execute('create', array(array(
                'orgid'    => $this->_orgParams['orgid'],
                'orgname'  => $this->_orgParams['orgname'],
                'userid'   => $this->_orgParams['userid'],
                'password' => $this->_orgParams['password'],
                'truename' => $this->_orgParams['userid'],
                'domain'   => $this->_orgParams['domain']
            )));
        } catch (Model_Org_Exception $e) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception($e->getMessage());
        }
    }

    /**
     * 完成安装后锁定
     */
    public function finish()
    {
        if (empty($this->_dataPath)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('Data path error');
        }

        $lockFile = $this->_dataPath . DIRECTORY_SEPARATOR . 'install.lock';
        @touch($lockFile);
    }
}