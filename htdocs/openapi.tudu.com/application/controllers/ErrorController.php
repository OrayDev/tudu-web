<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->code    = TuduX_OpenApi_ResponseCode::BAD_REQUEST;
                $this->view->message = 'API method not found';
                break;

            default:
                // 授权失效或超时
                $exception = $errors->exception;
                if ($exception instanceof OpenApi_OAuth_Exception) {
                    $this->getResponse()->setHttpResponseCode(200);
                    $this->view->code    = $exception->getError() == OpenApi_OAuth_OAuth::ERROR_ACCESSTOKEN_EXPIRED
                                         ? TuduX_OpenApi_ResponseCode::AUTHORIZE_EXPIRED
                                         : TuduX_OpenApi_ResponseCode::INVALID_AUTHORIZE;
                    $this->view->message = $exception->getDescription();
                    break;

                } elseif ($exception instanceof TuduX_OpenApi_Exception) {
                    $this->getResponse()->setHttpResponseCode($exception->getHttpCode());
                    $this->view->code    = $exception->getCode();
                    $this->view->message = $exception->getMessage();
                    break;
                }

                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->code    = TuduX_OpenApi_ResponseCode::SYSTEM_ERROR;
                $this->view->message = 'Application error';
                break;
        }
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        //$this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }


}

