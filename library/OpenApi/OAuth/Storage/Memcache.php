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
 * @category   OpenApi
 * @package    OpenApi_OAuth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class OpenApi_OAuth_Storage_Memcache implements OpenApi_OAuth_Storage_Interface
{

    /**
     *
     * @var Oray_Memcache
     */
    protected $_memcache = null;

    protected $_accessTokenPrefix  = 'ACCESS-TOKEN-';

    protected $_refreshTokenPrefix = 'REFRESH-TOKEN-';

    /**
     *
     * @param array $config
     */
    public function __constructor(array $config = array())
    {
        if (!empty($config['memcache']) && $config['memcache'] instanceof Oray_Memcache) {
            $this->setMemcache($config['memcache']);
        }
    }

    /**
     *
     * @param Memcache $memcache
     */
    public function setMemcache(Memcache $memcache)
    {
        $this->_memcache = $memcache;
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::setAccessToken()
     */
    public function setAccessToken($accessToken, array $data, $timeout = null)
    {
        return $this->_memcache->set($this->_makeAccessTokenKey($accessToken), $data, null, $timeout);
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::unsetAccessToken()
     */
    public function unsetAccessToken($accessToken)
    {
        return $this->_memcache->delete($this->_makeAccessTokenKey($accessToken));
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::getAccessToken()
     */
    public function getAccessToken($accessToken)
    {
        return $this->_memcache->get($this->_makeAccessTokenKey($accessToken));
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::setRefreshToken()
     */
    public function setRefreshToken($refreshToken, array $data, $timeout = null)
    {
        return $this->_memcache->set($this->_makeRefreshTokenKey($refreshToken), $data, null, $timeout);
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::unsetRefreshToken()
     */
    public function unsetRefreshToken($refreshToken)
    {
        return $this->_memcache->delete($this->_makeRefreshTokenKey($refreshToken));
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_OAuth_Storage_Interface::getRefreshToken()
     */
    public function getRefreshToken($refreshToken)
    {
        return $this->_memcache->get($this->_makeRefreshTokenKey($refreshToken));
    }

    /**
     *
     * @param array $accessToken
     */
    private function _makeAccessTokenKey($accessToken)
    {
        return $this->_accessTokenPrefix . $accessToken;
    }

    /**
     *
     * @param array $accessToken
     */
    private function _makeRefreshTokenKey($accessToken)
    {
        return $this->_refreshTokenPrefix . $accessToken;
    }
}