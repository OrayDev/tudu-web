<?php

class IndexController extends TuduX_Controller_OpenApi
{

    public function indexAction()
    {
        $params = $this->_request->getParams();

        // action body
        $this->view->code    = 101;
        $this->view->message = "Invalid API request";
    }
}