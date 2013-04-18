<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     *
     * Enter description here ...
     */
    protected function _initView()
    {
        require_once 'OpenApi/Response/Response.php';

        $view = new OpenApi_Response_Response();

        // Add it to the ViewRenderer @see  7.8.4.7. ViewRenderer@Zend_Framework_Zh.chm
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'ViewRenderer'
        );
        $viewRenderer->setView($view);

        return $view;
    }

    /**
     *
     */
    public function _initDb()
    {
        $multidb = $this->bootstrap('multidb');

        Tudu_Dao_Manager::setDbs(array(
            Tudu_Dao_Manager::DB_MD   => $this->multidb->getDb('md')
        ));
    }
}

