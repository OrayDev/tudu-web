<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 * @category   OpenApi
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   OpenApi
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_OAuth_Exception extends Exception
{
    protected $_code = null;

    protected $_error = null;

    protected $_message = null;

    public function __construct($description, $error, $code = null)
    {
        $this->_code = $code;
        $this->setDescription($description);
        $this->setError($error);
    }

    public function setError($error)
    {
        $this->_error = $error;
    }

    public function setDescription($message)
    {
        $this->_message = $message;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function getDescription()
    {
        return $this->_message;
    }
}