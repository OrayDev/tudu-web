<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Function
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Function.php 13139 2013-03-07 09:07:09Z chenxujian $
 */

/**
 * @category   Oray
 * @package    Oray_Function
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Function
{
    /**
     * 最后测试时间
     *
     * @var int
     */
    private static $_lastTime = null;

    /**
     * GBK To UTF-8
     *
     * @param mixed $str Supported array
     * @return mixed
     */
    public static function gbkToUtf8($str)
    {
        if (is_string($str)) {
            return @iconv("GBK", "UTF-8", $str);
        }
        if (is_array($str)) {
            foreach ($str as $key => $v) {
                $str[$key] = self::gbkToUtf8($v);
            }
            return $str;
        }
        return $str;
    }

    /**
     * UTF-8 To GBK
     *
     * @param mixed $str Supported array
     * @return mixed
     */
    public static function utf8ToGbk($str)
    {
        if (is_string($str)) {
            return @iconv("UTF-8", "GBK", $str);
        }
        if (is_array($str)) {
            foreach ($str as $key => $v) {
                $str[$key] = self::utf8ToGbk($v);
            }
            return $str;
        }
        return $str;
    }

    /**
     * 获取字符串长度，一个中文算一个字符
     *
     * @param string $string  字符串
     * @param string $charset 字符编码
     * @return int
     */
    public static function strLen($string, $charset = 'utf-8')
    {
        return mb_strlen($string, $charset);
    }

    /**
     * 获取字符串长度，一个中文算两个字符
     *
     * @param string $string
     * @return int
     */
    public static function strLenWord($string)
    {
        $i = 0;
        $count = 0;
        $len = strlen($string);
        while ($i < $len) {
            $chr = ord($string[$i]);
            $count ++;
            $i ++;
            if ($i >= $len)
                break;

            if ($chr & 0x80) {
                $chr <<= 1;
                while ($chr & 0x80) {
                    $i ++;
                    $chr <<= 1;
                }
                $count ++;
            }
        }
        return $count;
    }

    /**
     * 判字符长度
     *
     * @param string $string
     * @param int $min
     * @param int $max
     * @param boolean $word
     * @return boolean
     */
    public static function checkLen($string, $min, $max, $word = false)
    {
        $len = $word ? self::strLenWord($string) : self::strLen($string);
        if ($min && $len < $min) {
            return false;
        }
        if ($min && $len > $max) {
            return false;
        }
        return true;
    }

    /**
     * 截取字符长度
     *
     * @param string $string
     * @param int $length
     * @param string $etc
     * @param boolean $breakWords
     * @param boolean $middle
     * @param string $charset
     * @return string
     */
    public static function truncate($string, $length, $etc = '...', $breakWords = false, $middle = false, $charset = 'utf-8')
    {
        if ($length == 0) {
            return '';
        }
        if (mb_strlen($string, $charset) > $length) {
            $length -= min($length, mb_strlen($etc, $charset));
            if (!$breakWords && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1, $charset));
            }
            if(!$middle) {
                return mb_substr($string, 0, $length, $charset) . $etc;
            } else {
                return mb_substr($string, 0, $length/2, $charset) . $etc . mb_substr($string, - $length/2, $charset);
            }
        } else {
            return $string;
        }
    }

    /**
     * 获取访问者真实IP
     *
     * @return string
     */
    public static function getTrueIp()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        } else {
            $onlineip = 'unknown';
        }

        preg_match('/[\d\.]{7,15}/', $onlineip, $matches);
        $onlineip = isset($matches[0]) ? $matches[0] : 'unknown';

        return $onlineip;
    }

    /**
     * 获取访问者真实IPs
     *
     * 如果通过代理访问时，会记录多个IP，并且最后一个一定是$_SERVER['REMOTE_ADDR']
     * $_SERVER['REMOTE_ADDR'] 访问端（有可能是用户，有可能是代理的）IP
     * $_SERVER['HTTP_CLIENT_IP'] 代理端的（有可能存在，可伪造）
     * $_SERVER['HTTP_X_FORWARDED_FOR'] 用户是在哪个IP使用的代理（有可能存在，也可以伪造）
     *
     * @param int   $count     返回的最大IP数
     * @param array $filterIps 过滤的IP，主要是过滤CDN转发的IP，也可能是内网的IP
     * @return array
     */
    public static function getTrueIPs($count = 3, $filterIps = null)
    {
        $ips = array();

        if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        }

        if (isset($onlineip)) {
            $ips += explode(',', str_replace(' ', '', $onlineip));
        }

        if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ips[] = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ips[] = $_SERVER['REMOTE_ADDR'];
        }

        // 过滤IP
        foreach($ips as $key => $ip) {
            if (strlen($ip) > 15 || (is_array($filterIps) && in_array($ip, $filterIps))) {
                unset($ips[$key]);
            }
        }

        if ($count > 0 && count($ips) > $count) {
            array_splice($ips, $count - 1, count($ips) - $count);
        }

        return $ips;
    }

    /**
     * Trim string
     *
     * @param mixed $str Supported array
     * @return mixed
     */
    public static function trim($str)
    {
        if (is_string($str)) {
            return trim($str);
        }
        if (is_array($str)) {
            foreach ($str as $key => $v) {
                $str[$key] = self::trim($v);
            }
            return $str;
        }
        return $str;
    }

    /**
     * 是否包含中文，不含符号
     *
     * @param string $str
     * @return boolean
     */
    public static function hasCnChar($str)
    {
        return (bool) preg_match('/[\x{4e00}-\x{9fa5}]/u', $str);
    }

    /**
     * 是否包含英文，不含符号
     *
     * @param string $str
     * @return boolean
     */
    public static function hasEnChar($str)
    {
        return (bool) preg_match('/[a-z]/iu', $str);
    }

    /**
     * 是否纯中文字符
     *
     * @param string $str
     * @return boolean
     */
    public static function isCnChar($str) {
        return (bool) preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $str);
    }

    /**
     * 是否为单字节
     *
     * @param string $str
     * @return boolean
     */
    public static function isByte($str)
    {
        return (bool) preg_match('/^[\x{01}-\x{ff}]+$/u', $str);
    }

    /**
     * 是否为有效IP格式
     *
     * 目前只支持IPv4格式的IP判断
     *
     * @param string $value
     * @return boolean
     */
    public static function isIp($value)
    {
        if ((ip2long($value) === false) || (long2ip(ip2long($value)) !== $value)) {
            return false;
        }
        return true;
    }

    /**
     * 是否域名字符
     *
     * $en == true  只允许英文字符
     * $en == false 必须包含中文字
     * $en == null  可以是英文或中文
     *
     * @param string $str
     * @param mixed  $en
     * @return boolean
     */
    public static function isDomainStr($str, $en = true)
    {

        if ($en === true) {
            $pattern = '/^[0-9a-z]+(\x2d[0-9a-z]+)*$/i';
        } else {
            $pattern = '/^[0-9a-z\x{4e00}-\x{9fa5}]+(\x2d[0-9a-z\x{4e00}-\x{9fa5}]+)*$/iu';
        }

        if (!preg_match($pattern, $str)) {
            return false;
        }

        if ($en === false && !preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)) {
            return false;
        }
        return true;
    }

    /**
     * 判断正确的域名格式
     *
     * 特殊后缀如  .公司 .网络 是必须包含中文域名
     *
     * @see Zend_Validate_Hostname
     * // DomainName characters are: *(label dot)(label dot label); max 254 chars
     * // label: id-prefix [*ldh{61} id-prefix]; max 63 chars
     * // id-prefix: alpha / digit
     * // ldh: alpha / digit / dash
     *
     * @param string  $value  判断的域名字符，支持punycode格式
     * @param boolean $idna   是否支持国际化域名，主要针对中文域名
     * @param boolean $anysub 是否支持泛域名
     * @param array   $tlds   OPTIONAL 有效的TLDs，不包含dot，指定时只有此
     * @return boolean
     */
    public static function isDomainName($value, $idna = true, $anysub = false, $tlds = null)
    {
        $domainParts = explode('.', $value);

        if ((count($domainParts) == 1) || (strlen($value) < 4) || (strlen($value) > 254)) {
            return false;
        }

        $tld = strtolower(array_pop($domainParts));
        if (strpos($tld, 'xn--') === 0) {
            try {
                $tld = Oray_Coder_Punycode::decode($tld);
            } catch (Oray_Coder_Exception $e) {
                return false;
            }
        }

        // 是否中文域名格式的TLD
        $isIdnaTld = in_array($tld, array('中国', '公司', '网络', '香港'));

        // 是否只包含中文
        $isZhTld   = in_array($tld, array('公司', '网络', '香港'));

        if (!preg_match('/[a-z]{2,10}$/', $tld) && ($idna === false || !$isIdnaTld)) {
            return false;
        }

        if (is_array($tlds) && !empty($tlds)) {
            if (!in_array($tld, $tlds)) {
                return false;
            }
        }

        if ($anysub && (current($domainParts) == '*') && count($domainParts) > 1) {
            array_shift($domainParts);
        }

        $en = ($idna === true) ? null : true;

        foreach ($domainParts as $domainPart) {
            if (strpos($domainPart, 'xn--') === 0) {
                try {
                    $domainPart = Oray_Coder_Punycode::decode($domainPart);
                } catch (Oray_Coder_Exception $e) {
                    return false;
                }
            }
            if (!self::isDomainStr($domainPart, $en)) {
                return false;
            }
        }

        // 中文域名最后一个$domainPart一定要包含中文字
        if ($isZhTld && !self::isDomainStr($domainPart, false)) {
            return false;
        }

        return true;
    }

    /**
     * 是否合法URL
     *
     * 完整的URL格式如 http://user:pass@abc.com:8080/index.html?a=b&c=d
     *
     * @param string $value      URL字符串
     * @param array  $protocols  允许协议
     * @return boolean
     */
    public static function isUrl($value, $protocals = null)
    {
        if (null === $protocals || !is_array($protocals)) {
            $protocals = array('http', 'https', 'ftp', 'mailto');
        }

        list($protocal, $url) = array_pad(explode('://', $value, 2), 2, null);

        // 协议判断
        if (!isset($url) || !in_array(strtolower($protocal), $protocals)) {
            return false;
        }

        $arr = explode('/', $url);
        $arr = explode('@', array_shift($arr));
        list($host, $port) = array_pad(explode(':', array_pop($arr)), 2, null);

        // 主机名判断
        //if (!self::isDomainName($host)) {
        //    return false;
        //}

        // 端口号判断
        if (isset($port) && (!is_numeric($port) || intval($port) < 1)) {
            return false;
        }

        return true;
    }

    /**
     * 是否邮件格式
     *
     * @param string $value
     * @return boolean
     */
    public static function isEmail($value)
    {
        if (!is_string($value)) {
            return false;
        }

        $matches = array();

        // Split email address up and disallow '..'
        if ((strpos($value, '..') !== false)
            || (!preg_match('/^([\w\-\.]+)@([^@]+)$/', $value, $matches))) {
            return false;
        }

        $localPart = $matches[1];
        $hostname  = $matches[2];

        if ((strlen($localPart) > 64) || (strlen($hostname) > 255)) {
            return false;
        }

        // Match hostname part
        if (!self::isDomainName($hostname, false)) {
            return false;
        }

        return true;
    }

    /**
     * 是否手机号码
     *
     * @param string $value
     */
    public static function isMobile($value)
    {
        return (bool) preg_match("/^1[3458]{1}[0-9]{9}$|^852[69]{1}[0-9]{7}$|^88609[0-9]{8}$|^853[6]{1}[0-9]{7}$/", $value);
    }

    /**
     * 比较两个时间相差值
     *
     * @param string $datePart  比较类型
     * @param mixed  $startDate 开始时间
     * @param mixed  $endDate   结束时间
     * @return int 如果无法比较时，将返回null值
     */
    public static function dateDiff($datePart, $startDate, $endDate)
    {
        if (!is_int($startDate)) {
            $startDate = strtotime($startDate);
        }

        if (!is_int($endDate)) {
            $endDate = strtotime($endDate);
        }

        if (false === $startDate || false === $endDate) {
            return null;
        }

        $start  = getdate($startDate);
        $end    = getdate($endDate);
        $result = null;

        switch (strtolower($datePart)) {

            // 相差秒数
            case 'sec':
            case 's':
                $result = $endDate - $startDate;
                break;

            // 相关分钟数 60s=1m
            case 'min':
            case 'n':
                $result = (mktime($end['hours'], $end['minutes'], 0, $end['mon'], $end['mday'], $end['year'])
                        - mktime($start['hours'], $start['minutes'], 0, $start['mon'], $start['mday'], $start['year'])) / 60;
                break;

            // 相关小时数 3600s=1h
            case 'hour':
            case 'h':
                $result = (mktime($end['hours'], 0, 0, $end['mon'], $end['mday'], $end['year'])
                        - mktime($start['hours'], 0, 0, $start['mon'], $start['mday'], $start['year'])) / 3600;
                break;

            // 相差天数 86400s=1d
            case 'day':
            case 'd':
                $result = (mktime(0, 0, 0, $end['mon'], $end['mday'], $end['year'])
                        - mktime(0, 0, 0, $start['mon'], $start['mday'], $start['year'])) / 86400;
                break;

            // 相差月数
            case 'month':
            case 'm':
                $result = ($end['year'] - $start['year']) * 12 + $end['mon']- $start['mon'];
                break;

            // 相差年数
            case 'year':
            case 'yyyy':
                $result = $end['year'] - $start['year'];
                break;

            default:
                break;
        }
        return $result;
    }

    /**
     * 日期增加
     *
     * @param string $datePart 日期的部分
     * @param int    $number   增加的数量
     * @param mixed  $date     操作的日期
     * @return int
     */
    public static function dateAdd($datePart, $number, $date)
    {
        if (null === $date) {
            return null;
        }

        if (!is_int($date)) {
            $date = strtotime($date);
        }

        $today   = getdate($date);
        $hours   = $today['hours'];
        $minutes = $today['minutes'];
        $seconds = $today['seconds'];
        $month   = $today['mon'];
        $day     = $today['mday'];
        $year    = $today['year'];

        switch ($datePart) {
            case 'year':
            case 'yyyy':
                $year += $number;
                break;

            case 'month':
            case 'm':
                $month += $number;
                break;

            case 'day':
            case 'd':
                $day += $number;
                break;

            case 'week':
            case 'w':
                $day += ($number * 7);
                break;

            case 'hour':
            case 'h':
                $hours += $number;
                break;

            case 'min':
            case 'n':
                $minutes += $number;
                break;

            case 'sec':
            case 's':
                $seconds += $number;
                break;
        }
        $timestamp = mktime($hours, $minutes, $seconds, $month, $day, $year);
        return $timestamp;
    }

    /**
     * 处理Json输出
     *
     * @param boolean $success 操作是否成功
     * @param mixed   $params  附加参数
     * @param mixed   $data    返回数据
     */
    public static function json($success = false, $params = null, $data = false)
    {
        if (is_string($params) || is_numeric($params)) {
            $params = array('message' => $params);
        }

        $json = array('success' => (boolean) $success);

        if (is_array($params)) {

            // 加上最后的参数是为了防止success被重置
            $json = array_merge($json, $params, $json);
        }

        if (false !== $data) {
            $json['data'] = $data;
        }

        header('Content-Type: application/x-javascript; charset=utf-8');

        echo json_encode($json);
    }

    /**
     * 处理JS弹出提示
     *
     * @param string  $message  提示的信息
     * @param mixed   $location 跳转地址
     * @param boolean $exit     是否退出程序
     */
    public static function alert($message, $location = true, $exit = true)
    {
        $script = 'alert("' . addslashes($message) . '");';

        if (true === $location) {
            $script .= 'history.back()';
        } elseif (is_string($location)) {
            $script .= 'location="' . addslashes($location) . '"';
        }

        if (!headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("Content-type: text/html; charset=utf-8");
        }

        echo '<script type="text/javascript">' . $script . '</script>';

        if ($exit) {
            exit();
        }
    }

    /**
     * HTTP请求内容
     *
     * @param string $url    请求的URL地址，包含GET请求的参数
     * @param mixed $content POST的内容，如果指定内容将使用POST方式提交
     * @param array $headers 文件头
     * @param int $timeout   超时时间
     * @return string
     */
    public static function httpRequest($url, $content = '', $headers = null, $timeout = 30)
    {

        $method = empty($content) ? 'GET' : 'POST';
        $header = '';

        if (is_array($content)) {
            $content = http_build_query($content);
        }

        if ($method == 'POST') {
            $header = "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($content) . "\r\n";
        }

        // 添加标题
        if (is_array($headers)) {
            foreach ($headers as $key => $val) {
                $header .= $key . ': ' . $val . "\r\n";
            }
        }

        $header .= "Connection: Close\r\n\r\n";

        $options = array (
            'http' => array (
                'method' => $method,
                'timeout' => $timeout,
                'header'=> $header,
                'content' => $content
            )
        );

        $context = stream_context_create($options);
        $contents = @file_get_contents($url, false, $context);
        return $contents;
    }

    /**
     * 生成随机码
     *
     * @param int    $length
     * @param string $pattern
     * @return string
     */
    public static function randKeys($length, $pattern = NULL)
    {
        if (!$pattern) {
            $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $key = '';
        for($i = 0; $i < $length; $i++){
            $key .= $pattern{rand(0, (strlen($pattern) - 1))};
        }
        return $key;
    }

    /**
     * 简单的加密
     *
     * @param string $str
     * @return string
     */
    public static function encryptString($str)
    {
        return bin2hex(base64_encode($str));
    }

    /**
     * 简单的解密
     *
     * @param string $str
     * @return string
     */
    public static function decryptString($str)
    {
        return base64_decode(self::hex2bin($str));
    }

    /**
     * Like bin2hex
     *
     * @param string $hex
     * @return string
     */
    public static function hex2bin($hex)
    {
        $bin = '';
        for($i = 0; $i < strlen($hex); $i += 2) {
            $bin .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $bin;
    }

    /**
     * 测试运行时间
     *
     * @return int
     */
    public static function debugTime($flag = '', $output = false)
    {
        if (!self::$_lastTime) self::$_lastTime = array_sum(explode(' ', microtime()));
        $nowTime = array_sum(explode(' ', microtime()));
        $time = $nowTime - self::$_lastTime;
        self::$_lastTime = $nowTime;
        if ($output) {
            echo $flag . $time . "\n";
        }
        return $time;
    }

    /**
     * 转成IP，注意IP需要调转
     *
     * @param int $num
     * @return string
     */
    public static function intToIp($num)
    {
        return long2ip(hexdec(implode(array_reverse(str_split(str_pad(dechex((int) $num & 0xffffffff), 8, '0', STR_PAD_LEFT), 2)))));
    }

    /**
     * 文件大小单位自动转换
     *
     * @param int $size
     * @return string
     */
    public static function formatFilesize($size)
    {
        $count = 0;
        $format = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        while($size > 1024 && $count < 8) {
           $size = $size/1024;
           $count++;
        }
        $decimals = ($count == 0) ? 0 : max(0, 3 - strlen((int) $size));
        $return = number_format($size, $decimals, '.', ',') . " " . $format[$count];
        return $return;
    }

    /**
     * 格式化域名数据
     *
     * @param string $domainName
     * @return array|false
     */
    public static function formatDomainInfo($domainName)
    {
        if (!self::isDomainName($domainName)) {
            return false;
        }

        $domainName = Oray_Coder_Punycode::decode($domainName);
        $punycode   = Oray_Coder_Punycode::encode($domainName);

        list($prefix, $suffix) = explode('.', $domainName, 2);

        $info = array(
            'name'     => $domainName,
            'punycode' => $punycode,
            'prefix'   => $prefix,
            'suffix'   => strstr($domainName, '.'),
            'tld'      => strrchr($domainName, '.'),
            'iscnnic'  => false,
            'iscn'     => false,
        );

        $info['iscnnic'] = in_array($info['tld'], array('.cn', '.中国', '.公司', '.网络'));
        $info['iscn']    = self::hasCnChar($info['prefix']);

        return $info;
    }
}