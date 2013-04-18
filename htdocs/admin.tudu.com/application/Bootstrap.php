<?php
/**
 *
 * @version $Id: Bootstrap.php 2076 2012-08-28 00:54:32Z web_op $
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
    * 初始化视图
    */
    protected function _initView()
    {
        // Initialize view
        require_once 'Oray/View/Smarty.php';
        $view = new Oray_View_Smarty(null, $this->getOption('smarty'));

        // Add it to the ViewRenderer @see  7.8.4.7. ViewRenderer@Zend_Framework_Zh.chm
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view)
                     ->setViewScriptPathSpec(':module/:controller#:action.:suffix')
                     ->setViewScriptPathNoControllerSpec(':module/:action.:suffix')
                     ->setViewSuffix('tpl');
        return $view;
    }

    /**
     * 初始化后台用户
     */
    protected function _initAdmin()
    {

    }

    /**
     * 初始化 Session
     */
    protected function _initSession()
    {
        $options = $this->_options['resources']['session'];
        $options['bootstrap'] = $this;
        $resource = new Zend_Application_Resource_Session($options);
        $resource->init();
    }

    /**
     *
     */
    protected function _initApplication()
    {
        //$defaultDb = $this->multidb->getDb();

        //Oray_Db_Helper::getInstance()->set('tudu-md', $defaultDb);

        Oray_Dao_Abstract::setDefaultAdapter($this->multidb->getDb());
        Oray_Dao_Abstract::registerErrorHandler(array($this, 'daoErrorHandler'));

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD   => $this->multidb->getDb('md')
        ));

        Tudu_User::setMemcache($this->getResource('memcache'));

        $resourceManager = new Tudu_Model_ResourceManager_Registry();
        $resourceManager->setResource('config', $this->_options);
        Tudu_Model::setResourceManager($resourceManager);

        //set_error_handler(array($this, 'errorHandler'));
    }

	/**
     * 初始化Log
     *
     * @return Zend_Log
     */
    public function _initLog()
    {
        $configs = $this->_options['resources']['log'];

        foreach ($configs as $key => &$config) {
            $params = &$config['writerParams'];
            // 初始化db写入对象参数
            if (strtolower($key) == 'db') {
                if (!empty($params['db']) && !empty($params['table'])) {
                    $params['db']        = $this->getResource('multidb')->getDb($params['db']);
                    $params['columnMap'] = array(
                        'SEVERITY' => 'priority',
                        'ORIGIN'   => 'from',
                        'DATA'     => 'data',
                        'MESSAGE'  => 'message'
                    );
                } else {
                    unset($configs[$key]);
                }
            }
        }

        $logger = Zend_Log::factory($configs);

        return $logger;
    }

    /**
     * Error handler
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!$this->hasPluginResource('Log')) {
            return false;
        }

        $this->bootstrap('Log');
        $this->getResource('Log')->log("$errstr $errfile line[$errline]", Zend_Log::WARN, array('from' => $_SERVER['HTTP_HOST'], 'data' => null));
    }

    /**
     * Dao error handler
     *
     * @param Exception $e
     * @param string $method
     */
    public function daoErrorHandler(Exception $e, $method)
    {
        if (!$this->hasPluginResource('Log')) {
            return false;
        }

        $this->bootstrap('Log');
        $this->getResource('Log')->log("$method - " . $e->getMessage(), Zend_Log::CRIT, array('from' => $_SERVER['HTTP_HOST'], 'data' => null));
    }
}