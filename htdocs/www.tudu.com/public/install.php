<?php
/**
 * 图度开源安装向导
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: install.php 2800 2013-04-01 09:52:42Z chenyongfa $
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
@set_magic_quotes_runtime(0);

define('ROOT_PATH', dirname(__FILE__) . '/../../../');
define('DATA_PATH', ROOT_PATH . 'data/');
define('WWW_PATH', ROOT_PATH . 'htdocs/www.tudu.com/');
define('WWW_CONFIG_PATH', WWW_PATH . 'application/configs/');
define('ADMIN_CONFIG_PATH', ROOT_PATH . 'htdocs/admin.tudu.com/application/configs/');
define('SCRIPT_CONFIG_PATH', ROOT_PATH . 'scripts/task/configs/');

ini_set('date.timezone', 'Asia/Shanghai');
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(WWW_PATH . 'library'),
    realpath(ROOT_PATH . 'library'),
    get_include_path()
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace(array('Oray_', 'Tudu_', 'Dao_'));

require_once 'Tudu/Install/Function.php';
require_once 'Tudu/Install/Exception.php';
/* @var $install Tudu_Install_Function */
$func = Tudu_Install_Function::getInstance();
$func->setTplPath(realpath(DATA_PATH . 'install/templates'));

$url = 'http://' . $_SERVER['HTTP_HOST'] . '/install.php';

$lockFile = realpath(DATA_PATH . 'install.lock');
if(file_exists($lockFile)) {
    $func->error(array('locked' => true, 'url' => $url));
}

$step = intval($func->getgpc('step', 'R')) ? intval($func->getgpc('step', 'R')) : 0;
$func->setStep($step);

$method = $func->getMethod();

if(empty($method)) {
    $func->error(array('message' => '未知操作，无法进行安装', 'url' => $url));
}
// 显示授权协议
if($method == 'show_license') {
    $func->sendTemplate();
// 安装环境检测
} elseif ($method == 'env_check') {
    $func->setRootPath(realpath(ROOT_PATH));
    $options = array(
        'default' => $func->_defaultEnv,
        'env'     => $func->getBaseEnv(),
        'exts'    => $func->formatHtml('ext', $func->checkExtensions()),
        'func'    => $func->formatHtml('func', $func->checkFunction()),
        'dirfile' => $func->formatHtml('dirfile', $func->checkDirOrFile())
    );
    $func->sendTemplate($options);
// 设置各种配置
} elseif ($method == 'set_config') {
    $configFile = realpath(ROOT_PATH . '/install/config.ini');
    // 设置默认值
    $config     = array(
        'mysql' => array(
            'host'     => 'localhost',
            'port'     => '3306',
            'database' => 'opentudu',
            'user'     => '',
            'password' => ''
        ),
        'memcache' => array(
            'host' => '',
            'port' => ''
        ),
        'httpsqs' => array(
            'host' => '',
            'port' => ''
        )
    );

    if(file_exists($configFile)) {
        require_once 'Zend/Config/Ini.php';
        $cfg = new Zend_Config_Ini($configFile);
        $config = array_merge($config, $cfg->toArray());
    }

    $func->sendTemplate($config);
// 安装组织
} elseif ($method == 'install') {
    $message = null;
    // 验证数据完整性
    do {
        if (empty($_POST['dbinfo']['host'])) {
            $message = '请输入数据库服务器地址';
            break;
        }
        if (empty($_POST['dbinfo']['port'])) {
            $message = '请输入数据库服务器端口号';
            break;
        }
        if (empty($_POST['dbinfo']['database'])) {
            $message = '请输入数据库名';
            break;
        }
        if (empty($_POST['dbinfo']['user'])) {
            $message = '请输入数据库用户名';
            break;
        }
        if (empty($_POST['httpsqs']['host'])) {
            $message = '请输入HttpSQS服务器地址';
            break;
        }
        if (empty($_POST['httpsqs']['port'])) {
            $message = '请输入HttpSQS服务器端口号';
            break;
        }
        if (empty($_POST['memcache']['host'])) {
            $message = '请输入Memcache服务器地址';
            break;
        }
        if (empty($_POST['memcache']['port'])) {
            $message = '请输入Memcache服务器端口号';
            break;
        }
        if (empty($_POST['tudu']['orgid'])) {
            $message = '请输入云办公系统ID';
            break;
        }
        $orgId = strtolower($_POST['tudu']['orgid']);
        if (!preg_match("/^[a-z0-9]{1,60}$/", $orgId)) {
            $message = '云办公系统ID只接受英文或数字的格式';
            break;
        }
        if (empty($_POST['tudu']['orgname'])) {
            $message = '请输入公司名称';
            break;
        }
        if (empty($_POST['tudu']['userid'])) {
            $message = '请输入超级管理员账号';
            break;
        }
        $userId = strtolower($_POST['tudu']['userid']);
        if (!preg_match("/^[a-z0-9]{1,60}$/", $userId)) {
            $message = '超级管理员账号只接受英文或数字的格式';
            break;
        }
        if (empty($_POST['tudu']['password'])) {
            $message = '请输入超级管理员密码';
            break;
        }
        if ($_POST['tudu']['password'] != $_POST['tudu']['password2']) {
            $message = '两次输入的密码不一致';
            break;
        }
    } while (false);

    if (!empty($message)) {
        return $func->json(false, $message);
    }

    $config = array();

    $config['mysql'] = array(
        'host'     => $_POST['dbinfo']['host'],
        'port'     => $_POST['dbinfo']['port'],
        'username' => $_POST['dbinfo']['user'],
        'password' => $_POST['dbinfo']['password'],
        'dbname'   => $_POST['dbinfo']['database']
    );

    try {
        $func->checkMysqlConnect($config['mysql'], true);
    } catch (Tudu_Install_Exception $e) {
        return $func->json(false, '连接MySQL不成功或数据库不存在，请检查配置！');
    }

    $config['httpsqs'] = array(
        'host' => $_POST['httpsqs']['host'],
        'port' => $_POST['httpsqs']['port']
    );

    try {
        $func->checkHttpsqsConnect($config['httpsqs']);
    } catch (Tudu_Install_Exception $e) {
        return $func->json(false, '连接HttpSQS不成功，请检查配置！');
    }

    $config['memcache'] = array(
        'host' => $_POST['memcache']['host'],
        'port' => $_POST['memcache']['port']
    );

    $link = $func->checkMemcacheConnect($config['memcache']);
    if (!$link) {
        return $func->json(false, '连接Memcache不成功，请检查配置！');
    }

    $config['tudu.domain'] = $_SERVER['HTTP_HOST'];

    $configFile = realpath(ROOT_PATH . '/install/config.ini');
    if(file_exists($configFile)) {
        require_once 'Zend/Config/Ini.php';
        $cfg = new Zend_Config_Ini($configFile);
        $cfg = $cfg->toArray();

        $configKeys = array_keys($config);
        foreach ($cfg as $key => $val) {
            if (!in_array($key, $configKeys)) {
                $config[$key] = $val;
            }
        }
    }

    require_once 'Tudu/Install/Install.php';
    /* @var $install Tudu_Install_Install */
    $install = Tudu_Install_Install::getInstance();
    $install->setConfigs($config);
    $configPath = array(
        'www'    => realpath(WWW_CONFIG_PATH),
        'admin'  => realpath(ADMIN_CONFIG_PATH),
        'script' => realpath(SCRIPT_CONFIG_PATH),
    );
    $install->setConfigPaths($configPath);

    // 创建配置文件
    try {
        $install->saveConfigFile();
    } catch (Tudu_Install_Exception $e) {
        return $func->json(false, '创建站点配置文件不成功，请重试！');
    }

    $orgParams = array(
        'orgid'    => strtolower($_POST['tudu']['orgid']),
        'orgname'  => $_POST['tudu']['orgname'],
        'userid'   => strtolower($_POST['tudu']['userid']),
        'password' => md5($_POST['tudu']['password']),
        'domain'   => $_SERVER['HTTP_HOST']
    );

    $install->setOrgParams($orgParams);
    $install->setDataPath(realpath(DATA_PATH));

    // 创建组织
    try {
        $install->createOrg();
    } catch (Tudu_Install_Exception $e) {
        return $func->json(false, '初始图度云办公系统失败，请重新！');
    }

    return $func->json(true, 'success', array('url' => $url.'?step=4'));
// 完成安装
} elseif ($method == 'finish') {
    /* @var $install Tudu_Install_Install */
    $install = Tudu_Install_Install::getInstance();
    // 创建安装锁定文件
    $install->setDataPath(realpath(DATA_PATH));
    $install->finish();

    $func->sendTemplate(array('url' => 'http://' . $_SERVER['HTTP_HOST'], 'domain' => $_SERVER['HTTP_HOST']));
}
