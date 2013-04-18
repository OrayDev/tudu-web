<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Whois
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Whois.php 9483 2012-03-06 10:01:23Z yiqinfei $
 */

/**
 * @category   Oray
 * @package    Oray_Whois
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Whois
{
    /**
     * Api
     *
     * @var array
     */
    private $_apis;

    public function __construct(array $apis)
    {
        $this->_apis = $apis;
    }

    /**
     * 获取WHOIS信息
     *
     * @param string $domainName
     * @param int    $detail
     * @param int    $raw
     */
    public function whois($domainName, $raw = 1, $detail = 1)
    {
        $query = array(
            'names' => Oray_Function::utf8ToGbk($domainName),
            'detail' => $detail,
            'raw' => $raw
        );

        $url = $this->_apis[array_rand($this->_apis)] . '?' . http_build_query($query);
        $content = $this->_request('GET', $url);

        $whois = array();
        $pattern = "/<tag name='([\w ]+)'>([\s\S]*?)<\/tag>/";
        if (preg_match_all($pattern, $content, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i ++) {
                $value = $matches[2][$i];
                if (strpos($value, '<![CDATA[') === 0) {
                    $value = substr($value, 9, -3);
                }
                switch (strtoupper($matches[1][$i])) {

                    // 域名
                    case 'DOMAIN NAME':
                        $key = 'domainname';
                        break;

                    // 注册机构(顶级注册商)
                    case 'REGISTER':
                        $key = 'registrar';
                        break;

                    // 注册单位
                    case 'REGISTRANT ORG':
                    case 'DETAIL_REGISTANT_ORG':
                        $key = 'registrant';
                        break;

                    // 注册人(单位或个人)
                    case 'REGISTRANT NAME':
                    case 'DETAIL_REGISTANT':
                        $key = 'registrantname';
                        break;

                    // 管理员邮箱
                    case 'ADMIN EMAIL':
                    case 'DETAIL_ADMIN_EMAIL':
                        $key = 'adminemail';
                        break;

                    // WHOIS服务器
                    case 'WHOIS SERVER':
                        $key = 'whoisserver';
                        $value = $value ? explode(',', $value) : '';
                        break;

                    // 解析服务器
                    case 'NAME SERVER':
                        $key = 'nameserver';
                        $value = $value ? explode(',', $value) : '';
                        break;

                    // 域名状态
                    case 'STATUS':
                        $key = 'status';
                        $value = $value ? explode(',', $value) : '';
                        break;

                    // 注册时间
                    case 'CREATE DATE':
                        $key = 'creationdate';
                        $value = strtotime($value);
                        break;

                    // 更新时间
                    case 'UPDATE DATE':
                        $key = 'updateddate';
                        $value = strtotime($value);
                        break;

                    // 过期时间
                    case 'EXPIRE DATE':
                        $key = 'expirationdate';
                        $value = strtotime($value);
                        break;

                    // WHOIS详细信息
                    case 'WHOIS_RAW_DATA':
                        $key = 'whois';
                        break;
                    default:
                        $key = $matches[1][$i];
                        break;
                }
                $whois[$key] = $value;
            }
        }

        return $whois;
    }

    /**
     * HTTP请求
     *
     * @param string $data
     * @return void
     */
    protected function _request($method, $url, $data = null, $header = array())
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        if ('POST' === $method) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $content = (string) curl_exec($ch);
        curl_close($ch);

        return $content;
    }
}