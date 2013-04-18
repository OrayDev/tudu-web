<?php

class TuduX_OpenApi_Exception extends Exception
{
    /**
     *
     * @var int
     */
    private $_httpCode = 200;

    /**
     *
     * @param string $message
     * @param int    $code
     * @param int    $httpCode
     */
    public function __construct($message, $code = 0, $httpCode = 200)
    {
        $this->_httpCode = $httpCode;
        parent::__construct($message, $code);
    }

    /**
     *
     * return int
     */
    public function getHttpCode()
    {
        return $this->_httpCode;
    }
}