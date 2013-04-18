<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 * @category   OpenApi
 * @package    OpenApi_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see OpenApi_OAuth_OAuth
 */
require_once 'OpenApi/OAuth/OAuth.php';

/**
 * @category   OpenApi
 * @package    OpenApi_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class OpenApi_OAuth_Grant_Abstract
{

    /**
     *
     * @var OpenApi_OAuth_OAuth
     */
    protected $_oauth = null;

    /**
     *
     * @param OpenApi_OAuth_OAuth $oauth
     */
    public function __construct(OpenApi_OAuth_OAuth $oauth = null)
    {
        if (null !== $oauth) {
            $this->setOAuthInstance($oauth);
        }
    }

    /**
     *
     * @param OpenApi_OAuth_OAuth $oauth
     * return OpenApi_OAuth_Grant_Abstract
     */
    public function setOAuthInstance(OpenApi_OAuth_OAuth $oauth)
    {
        $this->_oauth = $oauth;
        return $this;
    }

    /**
     *
     * @return OpenApi_OAuth_OAuth
     */
    public function getOAuthInstance()
    {
        return $this->_oauth;
    }

    /**
     *
     * @param array $inputData
     */
    abstract public function grant(array $inputData) ;
}