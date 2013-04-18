<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        $isLog = true;
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                $url = $this->_request->getRequestUri();

                // 跳转到talk下载页面
                if (preg_match('/tudutalk.+exe$/', $url, $arr)) {
                    return $this->_redirect('http://www.oray.com/tudu/talk.php');
                }

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $message = $this->_request->getRequestUri() . ' ' . $errors->exception->getMessage();

                $bootstrap = $this->getInvokeArg('bootstrap');
                $options   = $bootstrap->getOptions();
                if (isset($options['resources']['log']['db'])) {
                    $multidb = $bootstrap->getResource('multidb');
                    try {
                        $dbLog = $multidb->getDb('log');
                        $host  = $this->_request->getServer('HTTP_HOST');
                        $date  = date('d-M-y');
                        $row = $dbLog->fetchRow("SELECT * FROM (SELECT MESSAGE FROM TUDU_LOG WHERE ORIGIN = '{$host}' AND LOG_TIME >= '{$date}' ORDER BY LOG_TIME DESC) WHERE ROWNUM = 1");

                        // 不重复记录
                        if (!empty($row) && $row['MESSAGE'] == $message) {
                            $isLog = false;
                        }
                    } catch (Exception $e) {}
                }
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $message = $errors->exception->getMessage();
                break;
        }

        // Log exception, if logger available
        $log = $this->getLog();
        if ($log && $isLog) {
            //$log->crit($message, $errors->exception);
            $clientIp = $this->_request->getClientIp();
            $log->log($message, Zend_Log::CRIT, array(
                'from' => $this->_request->getServer('HTTP_HOST'),
                'data' => $this->_request->getServer('HTTP_USER_AGENT') . "\n" . $clientIp . "\n" . $this->_request->getServer('HTTP_COOKIE')
            ));
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
        $this->view->registFunction('var_export', array($this, 'varExport'));
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }

        $bootstrap->bootstrap('Log');

        $logger = $bootstrap->getResource('Log');

        return $logger;
    }

    public function varExport($params) {
        echo var_export($params['_params'], true);
    }
}