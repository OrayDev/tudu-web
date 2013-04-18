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
interface OpenApi_OAuth_Storage_Interface
{

    /**
     * 存储访问令牌以及授权数据
     *
     * @param string $accessToken
     * @param array $data
     */
    public function setAccessToken($accessToken, array $data) ;

    /**
     * 删除已被储存的访问令牌数据
     *
     * @param string $accessToken
     * @param array $data
     */
    public function unsetAccessToken($accessToken) ;

    /**
     * 获取访问令牌授权数据
     *
     * @param string $accessToken
     */
    public function getAccessToken($accessToken) ;

    /**
     * 写入刷新令牌以及验证数据
     *
     * @param string $refreshToken
     * @param array $data
     */
    public function setRefreshToken($refreshToken, array $data) ;

    /**
     * 删除刷新令牌
     *
     * @param string $refreshToken
     */
    public function unsetRefreshToken($refreshToken) ;

    /**
     * 获取刷新令牌验证数据
     *
     * @param string $refreshToken
     */
    public function getRefreshToken($refreshToken) ;
}