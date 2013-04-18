<?php
ini_set('include_path', dirname(__FILE__) . '/../../../library:' . ini_get('include_path'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Define path to www root directory
defined('WWW_ROOT')
    || define('WWW_ROOT', realpath(APPLICATION_PATH . '/../../../'));

// Define language pack path
defined('LANG_PATH')
	|| define('LANG_PATH', realpath(APPLICATION_PATH . '/../lang'));

// PROTOCOL
defined('PROTOCOL')
    || define('PROTOCOL', (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https:' : 'http:');

    // PROTOCOL
defined('HOST')
    || define('HOST', $_SERVER['HTTP_HOST']);

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path()
)));

// load version
require_once 'Tudu/Version.php';

/** Zend_Exception */
require_once 'Zend/Exception.php';
/** Zend_Config_Ini */
require_once 'Zend/Config/Ini.php';
try {
    $cfg = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
} catch (Zend_Exception $e) {
    if (0 != strpos($e->getMessage(), 'application.ini') || 0 != strpos($e->getFile(), 'Zend/Config/Ini.php')) {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/install.php';
        header("location: $url");
    }
}

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $cfg
);
$application->bootstrap(array('FrontController', 'view', 'multidb', 'session', 'memcache', 'application'))
            ->run();