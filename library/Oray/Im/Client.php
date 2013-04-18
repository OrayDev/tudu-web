<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Im
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Client.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Im
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Im_Client
{
    /**
     * 发送的类型
     * 
     * @var stirng
     */
    const SEND_SINGLE = 'single';
    const SEND_CAST   = 'cast';
    const SEND_DEPT   = 'dept';
    
    /**
     * 更新的类型
     * 
     * @var string
     */
    const NOTIFY_UPDATE_DEPT = 'update_dept';
    const NOTIFY_UPDATE_USER = 'update_user';
    const NOTIFY_DELETE_USER = 'delete_user';
    
    /**
     * 消息的类型
     * 
     * @var string
     */
    const MSG_CHAT = 'chat';

    /**
     * API地址
     * 
     * @var string
     */
    private $_api;
    
    /**
     * 接口返回结果
     * 
     * @var string
     */
    private $_result;
    
    /**
     * 查询的URL
     * 
     * @var string
     */
    private $_request;

    /**
     * Construct
     * 
     * @param $host 主机
     * @param $port 端口
     */
    public function __construct($host, $port)
    {
        $this->_api = 'http://' . $host . ':' . $port;
    }
    
    /**
     * 发送消息
     * 
     * @param $from     发送人地址 
     * @param $to       接收人地址
     * @param $content  内容
     * @param $online   是否只发在线用户
     * @param $msgType  指定xmpp协议中message节的type
     * @param $sendType 指定发送类型
     * @return void
     */
    public function sendMsg($from, $to, $content, $online = true, $msgType = self::MSG_CHAT, $sendType = self::SEND_SINGLE)
    {
        $query = array(
            'sendtype' => $sendType,
            'from' => $from,
            'to' => $to,
            'online' => $online ? 'true' : 'false',
            'msgtype' => $msgType
        );
        
        $url = $this->_api . '/sendmsg?' . http_build_query($query);

        $this->_request($url, $content);
    }
    
    /**
     * 获取用户在线信息
     * 
     * @param array $email
     * @return array
     */
    public function getUserStatus($email)
    {
        if (!is_array($email)) $email = array($email);
        $status = array();
        
        if (empty($email)) {
            return $status;
        }
        
        $url = $this->_api . '/getuserstatus?jids=' . urlencode(implode('|', $email));
        
        $result = $this->_request($url);
        
        $result = strtr($result, array(
            '<show/>' => '<show>chat</show>',
            '<status/>' => '<status></status>',
            '</item>' => "</item>\n"
            ));
        
        if (preg_match_all('/<item jid="([^"]+)">(?:.*?<show>(\w+)<\/show><status>([^<]*)<\/status>.*?|.*?)<\/item>/', $result, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (!empty($matches[2][$i])) {
                    $status[$matches[1][$i]] = array(
                        'show' => $matches[2][$i],
                        'status' => $matches[3][$i]
                        );
                } else {
                    $status[$matches[1][$i]] = false;    
                }
            }
        }
        return $status;
    }
    
    /**
     * 根据key查询用户信息
     * 
     * @param $email
     * @param $clientKey
     * @return array
     */
    public function getUserInfo($email, $clientKey)
    {
    	$info = array();
        $query = array(
            'jid' => $email,
            'clientkey' => $clientKey
        );
        
        $url = $this->_api . '/getuserinfo?' . http_build_query($query);
        
        $result = $this->_request($url);
        if (preg_match_all("/(\w+)='([^']*)'/", $result, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
            	$info[$matches[1][$i]] = $matches[2][$i];
            }
		}
	
		return $info;
    }
     
    /**
     * 客户端通知
     * 
     * @param $type    指定更新类型
     * @param $orgId   组织ID
     * @param $email   用户邮箱地址
     * @return void
     */
    public function updateNotify($type, $orgId, $email = null)
    {
        $query = array(
            'type' => $type,
            'orgid' => $orgId
        );
        
        if (null !== $email) {
            $query['jid'] = $email;
        }
        
        $url = $this->_api . '/updatenotify?' . http_build_query($query);
        
        $this->_request($url);
    }
    
    /**
     * 请求接口
     * 
     * @param $url
     * @param $content
     * @return string
     */
    private function _request($url, $content = null)
    {
        $this->_request = $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($content) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        
        //$header = array(
        //    'Accept: */*',
        //    'Accept-Language: zh-CN',
        //    'User-Agent: Mozilla/4.0 (MSIE 8.0; Windows NT 6.1)',
        //    'Accept-Encoding: gzip, deflate',
        //    'Connection: Keep-Alive'
        //);
        
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        
        $this->_result = curl_exec($ch);
        curl_close($ch);
        return $this->_result;
    }
    
    /**
     * 返回接口最后一次请求的内容
     * 
     * @return string
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    /**
     * 返回接口最后一次查询结果
     * 
     * @return string
     */
    public function getResult()
    {
        return $this->_result;
    }
}