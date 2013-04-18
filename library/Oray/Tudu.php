<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Tudu
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Tudu.php 6326 2011-05-19 01:47:07Z cutecube $
 */


/**
 * @category   Oray
 * @package    Oray_Tudu
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Tudu
{
    /**
     * API地址
     * 
     * @var string
     */
    private $_api;
    
    /**
     * Key
     * 
     * @var string
     */
    private $_key;
    
	/**
	 * 请求的内容
	 *
	 * @var mixed
	 */
	protected $_request;

	/**
	 * 响应的内容 - 格式化后的对象
	 *
	 * @var mixed
	 */
	protected $_response;

	/**
	 * 响应的内容 - 原始信息
	 *
	 * @var string
	 */
	protected $_responseText;
    
    public function __construct(array $options)
    {
        $this->_api = $options['api'];
        $this->_key = $options['key'];
    }
    
    /**
     * 获取系统信息
     */
    public function systemInfo()
    {
        $data = array(
            't'          => time()
        );
        $data['k'] = md5($data['t'] . $this->_key);
        
        $this->request('POST', '/system/info', $data);
        return $this->_response; 
    }
    
    /**
     * 创建组织
     * 
     * @param string $orgId
     * @param array $params
     * @return array
     */
    public function createOrg($orgId, array $params)
    {
        $data = array(
            'orgid'      => $orgId,
            'orgname'    => $params['orgname'],
            'domainname' => $params['domainname'],
            'host'       => $params['host'],
            'cosid'      => $params['cosid'],
            'maxusers'   => $params['maxusers'],
            'maxquota'   => $params['maxquota'],
            'maxndquota' => $params['maxndquota'],
            'expiredate' => $params['expiredate'],
            't'          => time()
        );
        $data['k'] = md5($data['orgid'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/org/create', $data);
        return $this->_response; 
    }
    
    /**
     * 更新组织信息
     * 
     * @param $orgId
     * @param $params
     * @return array
     */
    public function updateOrg($orgId, array $params)
    {
    	$data = array();
    	
    	if (empty($params)) {
    		return array('success' => false);
    	}
    	
    	if (isset($params['maxusers']) && is_int($params['maxusers'])) {
    		$data['maxusers'] = $params['maxusers'];
    	}
    	
    	if (isset($params['maxquota']) && is_int($params['maxquota'])) {
    		$data['maxquota'] = $params['maxquota'];
    	}
    	
    	if (isset($params['expiredate']) && is_int($params['expiredate'])) {
    		$data['expiredate'] = $params['expiredate'];
    	}
    	
    	$data['orgid'] = $orgId;
    	$data['t']     = time();
    	$data['k']     = md5($orgId . $data['t'] . $this->_key);
        
        $this->request('POST', '/org/update', $data);
        return $this->_response;
    }
    
    /**
     * 是否有效邀请码
     * 
     * @param string $code
     * @return boolean
     */
    public function isValidInvite($code)
    {
        $data = array(
            'code' => $code,
            't'    => time()
        );
        $data['k'] = md5($data['code'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/invite/auth', $data);
        return $this->_response['success'];
    }

    /**
     * 更新邀请码
     * 
     * @param string $code
     * @param array $params
     */
    public function updateInvite($code, array $params)
    {
        $data = array(
            'code'   => $code,
            'status'  => $params['status'],
            'toorgid' => $params['orgid'],
            'usetime' => $params['usetime'],
            't'       => time()
        );
        $data['k'] = md5($data['code'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/invite/update', $data);
        return $this->_response; 
    }
    
    /**
     * 获取组织信息
     * 
     * @param $orgId
     * @return array
     */
    public function getOrg($orgId)
    {
        $data = array(
            'orgid' => $orgId,
            't'     => time()
        );
        $data['k'] = md5($data['orgid'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/org/info', $data);
        
        if ($this->_response['success']) {
            return $this->_response['data'];
        }
        
        return array();
    }
    
    /**
     * 更新用户信息
     * 
     * @param $orgId
     * @param $userId
     * @param $params
     * @return array
     */
    public function updateUser($orgId, $userId, $params)
    {
        $data = array(
            'orgid'   => $orgId,
            'userid'  => $userId,
            'password' => $params['password'],
            't'          => time()
        );
        $data['k'] = md5($data['orgid'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/user/password', $data);
        return $this->_response;
    }
    
    /**
     * 更新管理员信息
     * 
     * @param $orgId
     * @param $userId
     * @param $params
     * @return array
     */
    public function updateAdmin($orgId, $userId, $params)
    {
    	$data = array(
            'orgid'    => $orgId,
            'userid'   => $userId,
            'password' => $params['password'],
            'status'   => $params['status'],
            't'        => time()
        );
        $data['k'] = md5($data['orgid'] . $data['t'] . $this->_key);
        
        $this->request('POST', '/org/updateadmin', $data);
        return $this->_response;
    }
    
	/**
	 * 请求接口操作
	 *
	 */
	public function request($method, $url, array $data = null)
	{
	    $url = $this->_api . $url;
	    $ch = curl_init();
	    
        if (!empty($data)) {
            $data = http_build_query($data);
        }

	    $this->_request = $url . "?" . $data;

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
	    
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $header = array(
            'Accept: */*',
            //'Accept-Encoding: gzip, deflate',
            //'Connection: Keep-Alive'
        );
        
        $this->_responseText = (string) curl_exec($ch);
        curl_close($ch);

        $response = @json_decode($this->_responseText, true);
        
        if ($response) {
            $this->_response = $response;
        } else {
            $this->_response = array('success' => false, 'message' => 'timeout');
        }
	}
	
	/**
	 * 获取最后一次提交的文本内容
	 *
	 * @return string
	 */
	public function getRequestText()
	{
	    return $this->_request;
	}
	
	/**
	 * 获取接口最后一次响应的文本内容
	 *
	 * @return string
	 */
	public function getResponseText()
	{
	    return $this->_responseText;
	}
}