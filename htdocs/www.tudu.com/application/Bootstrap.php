<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initView()
    {
        // Initialize view
        $view = new Oray_View_Smarty(null, $this->getOption('smarty'));

        // Add it to the ViewRenderer @see  7.8.4.7. ViewRenderer@Zend_Framework_Zh.chm
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view)
                     ->setViewScriptPathSpec(':controller#:action.:suffix')
                     ->setViewScriptPathNoControllerSpec(':action.:suffix')
                     ->setViewSuffix('tpl');

        // Return it, so that it can be stored by the bootstrap
        return $view;
    }

    protected function _initApplication()
    {
        $defaultDb = $this->multidb->getDb();
        Oray_Dao_Abstract::setDefaultAdapter($defaultDb);
        Oray_Dao_Abstract::registerErrorHandler(array($this, 'daoErrorHandler'));

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD   => $this->multidb->getDb('md')
        ));

        Tudu_User::setMemcache($this->getResource('memcache'));

        //set_error_handler(array($this, 'errorHandler'));
    }

    protected function _initSession()
    {
        $options = $this->_options['resources']['session'];
        $options['bootstrap'] = $this;
        $resource = new Zend_Application_Resource_Session($options);
        $resource->init();
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