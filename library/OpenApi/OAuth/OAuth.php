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
class OpenApi_OAuth_OAuth
{
    /**
     * 设置项目
     *
     * @var string
     */
    const STORAGE                = 'storageType';            // 存储类型
    const ACCESS_TOKEN_EXPIRES   = 'accessTokenExpires';     // 访问令牌过期时间
    const REFRESH_TOKEN_EXPIRES  = 'refreshTokenExpires';
    const SUPPORT_REFRESH_TOKEN  = 'supportRefreshToken';    // 是否实现刷新令牌
    const TOKEN_TYPE             = 'tokenType';

    /**
     * 获取访问令牌方式类型
     *
     * @var string
     */
    const GRANT_TYPE_AUTH_CODE          = 'authorization_code';
    const GRANT_TYPE_IMPLICIT           = 'token';
    const GRANT_TYPE_USER_CREDENTIALS   = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';
    const GRANT_TYPE_EXTENSIONS         = 'extensions';

    /**
     * 参数名称
     *
     * @var string
     */
    const PARAM_CLIENT_ID     = 'client_id';
    const PARAM_CLIENT_SECRET = 'client_secret';
    const PARAM_REDIRECT_URI  = 'redirect_uri';
    const PARAM_STATE         = 'state';
    const PARAM_SCOPE         = 'scope';

    const PARAM_GRANT_TYPE    = 'grant_type';
    const PARAM_CODE          = 'code';
    const PARAM_USERNAME      = 'username';
    const PARAM_USER_ID       = 'user_id';
    const PARAM_PASSWORD      = 'password';
    const PARAM_REFRESH_TOKEN = 'refresh_token';
    const PARAM_ACCESS_TOKEN  = 'access_token';
    const PARAM_EXPIRES       = 'expires_in';

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-7.1
     *
     * @var string
     */
    const TOKEN_TYPE_BEARER = 'bearer';
    const TOKEN_TYPE_MAC    = 'mac';

    /**
     *
     * @var string
     */
    const REG_GRANT_TYPE = '#^(authorization_code|token|password|client_credentials|refresh_token|http://.*)$#';

    /**
     *
     * @var string
     */
    const ERROR_INTERNAL_EXCEPTION  = 'internal_exception';
    const ERROR_INVALID_REQUEST     = 'invalid_request';
    const ERROR_INVALID_ACCESSTOKEN = 'invalid_access_token';
    const ERROR_ACCESSTOKEN_EXPIRED = 'access_token_expired';

    /**
     *
     * @var array
     */
    protected $_config = array(
        self::STORAGE                => null,
        self::ACCESS_TOKEN_EXPIRES   => 86400,
        self::REFRESH_TOKEN_EXPIRES => 259200,
        self::SUPPORT_REFRESH_TOKEN  => true,
        self::TOKEN_TYPE             => self::TOKEN_TYPE_BEARER
    );

    /**
     *
     * @var array
     */
    protected $_grantClasses = array(
        self::GRANT_TYPE_AUTH_CODE        => 'OpenApi_OAuth_Grant_AuthCode',
        self::GRANT_TYPE_USER_CREDENTIALS => '',
        self::GRANT_TYPE_REFRESH_TOKEN    => 'OpenApi_OAuth_Grant_RefreshToken'
    );

    /**
     *
     * @var OpenApi_OAuth_Storage_Interface
     */
    protected $_storage;

    /**
     * Constructor
     *
     * @param Zend_Config | array $config
     */
    public function __construct($config = null)
    {
        if ($config && $config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (null !== $config && !is_array($config)) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid config parameter for constructor of OAuth", self::ERROR_INTERNAL_EXCEPTION);
        }

        if (isset($config[self::STORAGE])
            && $config[self::STORAGE] instanceof OpenApi_OAuth_Storage_Interface)
        {
            $this->_storage = $config[self::STORAGE];
        }

        $this->_config = array_merge($this->_config, $config);
    }

    /**
     *
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.6
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-4.1.3
	 *
     * @param array $inputDate
     * @param array $authHeaders
     * @throws OpenApi_OAuth_Exception
     */
    public function grantAccessToken(array $inputData, array $authHeaders = null)
    {
        $filters = array(
            self::PARAM_GRANT_TYPE => array("filter" => FILTER_VALIDATE_REGEXP, "options" => array("regexp" => self::REG_GRANT_TYPE), "flags" => FILTER_REQUIRE_SCALAR),
            self::PARAM_SCOPE => array("flags" => FILTER_REQUIRE_SCALAR),
            self::PARAM_CODE => array("flags" => FILTER_REQUIRE_SCALAR),
            self::PARAM_REDIRECT_URI => array("filter" => FILTER_SANITIZE_URL),
            self::PARAM_USERNAME => array("flags" => FILTER_REQUIRE_SCALAR),
            self::PARAM_PASSWORD => array("flags" => FILTER_REQUIRE_SCALAR),
            self::PARAM_REFRESH_TOKEN => array("flags" => FILTER_REQUIRE_SCALAR),
        );

        if (empty($inputData)) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception(self::ERROR_INVALID_REQUEST);
        }

        $input = filter_var_array($inputData, $filters);

        if (empty($input[self::PARAM_GRANT_TYPE])) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception('Invalid parameter "grant_type"', self::ERROR_INVALID_REQUEST);
        }

        $grant = $this->getGrantObject($input[self::PARAM_GRANT_TYPE]);

        if (!$grant instanceof OpenApi_OAuth_Grant_Abstract) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception('Grant class must extends from class "OpenApi_OAuth_Grant_Abstract"');
        }

        $data = $grant->grant($inputData);

        $data[self::PARAM_CLIENT_ID] = $inputData[self::PARAM_CLIENT_ID];

        return $this->createAccessToken($data);
    }

    /**
     * 设置验证模式对象类
     *
     * @param string $grantType
     * @param string $className
     * @return OpenApi_OAuth_OAuth
     */
    public function setGrantClass($grantType, $className)
    {
        if (empty($className)) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid classname for grant type");
        }

        $this->_grantClasses[$grantType] = $className;
    }

    /**
     *
     * @param string $grantType
     */
    public function getGrantObject($grantType)
    {
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($this->_grantClasses[$grantType]);

        return new $this->_grantClasses[$grantType]($this);
    }

    /**
     *
     */
    public function createAccessToken(array $params)
    {
        $accessToken = $this->_getToken();

        $ret = array(
            'access_token' => $accessToken,
            'expires_in'   => $this->_config[self::ACCESS_TOKEN_EXPIRES],
            'token_type'   => $this->_config[self::TOKEN_TYPE],
            'scope'        => $params[self::PARAM_SCOPE]
        );

        $token = array(
            self::PARAM_CLIENT_ID => $params[self::PARAM_CLIENT_ID],
            self::PARAM_USER_ID   => $params[self::PARAM_USER_ID],
            self::PARAM_EXPIRES   => time() + $this->_config[self::ACCESS_TOKEN_EXPIRES],
            self::PARAM_SCOPE     => $params[self::PARAM_SCOPE]
        );

        $token = array_merge($params, $token);

        $this->getStorage()->setAccessToken($accessToken, $token);
        $a = $this->getStorage()->getAccessToken($accessToken);

        if ($this->_config[self::SUPPORT_REFRESH_TOKEN]) {
            $refreshToken = $this->_getToken();
            $ret['refresh_token'] = $refreshToken;

            $token[self::PARAM_EXPIRES] = time() + $this->_config[self::REFRESH_TOKEN_EXPIRES];
            $this->getStorage()->setRefreshToken($refreshToken, $token);
        }

        return $ret;
    }

    /**
     * 销毁指定的访问令牌
     *
     * @param string $accessToken
     */
    public function destroyAccessToken($accessToken)
    {
        $storage = $this->getStorage();

        $token = $storage->getAccessToken($accessToken);

        if (null === $token) {
            return ;
        }

        if (!empty($token[self::PARAM_REFRESH_TOKEN])) {
            $storage->unsetRefreshToken($token[self::PARAM_REFRESH_TOKEN]);
        }

        $storage->unsetAccessToken($accessToken);
    }

    /**
     *
     * @param string $accessToken
     * @param string $scope
     */
    public function verifyAccessToken($accessToken, $scope = null)
    {
        $token = $this->getStorage()->getAccessToken($accessToken);

        if (empty($token)) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid access token provided", self::ERROR_INVALID_ACCESSTOKEN);
        }

        if (empty($token[self::PARAM_EXPIRES]) || empty($token[self::PARAM_CLIENT_ID])) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid access token provided", self::ERROR_INVALID_ACCESSTOKEN);
        }

        if ($token[self::PARAM_EXPIRES] < time()) {
            require_once 'OpenApi/OAuth/Exception.php';
            throw new OpenApi_OAuth_Exception("Invalid access token provided", self::ERROR_ACCESSTOKEN_EXPIRED);
        }

        // 授权范围，未实现
        if (null != $scope) {

        }

        return $token;
    }

    /**
     *
     * 获取保存对象
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * 生成新的令牌字符串
     */
    protected function _getToken()
    {
        $tokenLen = 40;
        if (file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), true);
        } else {
            $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        }
        return substr(hash('sha512', $randomData), 0, $tokenLen);
    }
}