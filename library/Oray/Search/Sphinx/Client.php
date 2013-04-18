<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Search
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Client.php 7819 2011-10-08 02:03:32Z cutecube $
 */

/**
 *
 * @see oray_Search_Sphinx_Result
 */
require_once 'Oray/Search/Sphinx/Result.php';

/**
 * Client Api
 *
 * @category Oray
 * @package  Oray_Search
 * @author CuTe_CuBe
 */
class Oray_Search_Sphinx_Client
{
    /**
     * Command of sphinx searchd service
     * 1.Execute search
     * 2.Get ecerpt of search service
     * 3.Update
     * 4.List keywords
     * 5.
     * 6.Get serviec status
     * 7.
     *
     * @var int
     */
    const COMMAND_SEARCH   = 0;
    const COMMAND_EXCERPT  = 1;
    const COMMAND_UPDATE   = 2;
    const COMMAND_KEYWORDS = 3;
    const COMMAND_PERSIST  = 4;
    const COMMAND_STATUS   = 5;
    const COMMAND_QUERY    = 6;

    /**
     * Command version of client
     * Base on Client 0.99 Api for PHP
     *
     * @var unknown_type
     */
    const COMMAND_VER_SEARCH   = 0x116;
    const COMMAND_VER_EXCERPT  = 0x100;
    const COMMAND_VER_UPDATE   = 0x102;
    const COMMAND_VER_KEYWORDS = 0x100;
    const COMMAND_VER_STATUS   = 0x100;
    const COMMAND_VER_QUERY    = 0x100;

    /**
     * Searchd status codes
     *
     * @var unknown_type
     */
    const STATUS_OK      = 0;
    const STATUS_ERR     = 1;
    const STATUS_RETRY   = 2;
    const STATUS_WARNING = 3;

    /**
     * Attr type
     *
     * @var int
     */
    const ATTR_INTEGER      = 1;
    const ATTR_TIMESTAMP    = 2;
    const ATTR_ORDINAL      = 3;
    const ATTR_BOOL         = 4;
    const ATTR_FLOAT        = 5;
    const ATTR_BIGINT       = 6;
    const ATTR_MULTI        = 0x40000000;

    /**
     * Client errors
     *
     * @var int
     */
    const ERROR_CONNECTION = -1;

    /**
     *
     * @var array
     */
    protected $_config = array(
        'host' => 'localhost',
        'port' => 9312,
        'path' => null,
        'timeout' => 30,
        'mbencoding' => 'UTF-8'
    );

    /**
     *
     * @var resource
     */
    protected $_socket = null;

    /**
     *
     * @var string
     */
    protected $_error;

    /**
     *
     * @var string
     */
    protected $_warning;

    /**
     * Constructor
     *
     * @param array | Zend_Config $config
     */
    public function __construct($config = null)
    {
        if (null != $config) {
            $this->setConfig($config);
        }
    }

    /**
     *
     * @param array | Zend_Config | string $config
     */
    public function setConfig($config, $value = null)
    {
        if (is_string($config) && null != $value) {
            $config = array($config => $value);
        } elseif ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } elseif (!is_array($config)) {
            return $this;
        }

        $this->_config = array_merge($this->_config, $config);

        return $this;
    }

    /**
     *
     * @param string $query
     * @param string $index
     * @param string $comment
     * @param Oray_Search_Sphinx_Optios $option
     * @return Oray_Search_Result
     */
    public function query($query, Oray_Search_Sphinx_Option $option, $index = "*", $comment = "")
    {
        $this->_mbPush();

        $request = $option->buildSearchRequest($query, $index, $comment);

        if (null == $this->_getSocket()) {
            $this->_MBPop();
            require_once 'Oray/Search/Sphinx/Exception.php';
            throw new Oray_Search_Sphinx_Exception('Connection error may be timeout');
        }

        // add Header
        $length  = strlen($request) + 4;
        $request = pack("nnNN", self::COMMAND_SEARCH, self::COMMAND_VER_SEARCH, $length, 1) . $request;

        if (!$this->_send($request, $length + 8) || !($response = $this->_getResponse(self::COMMAND_VER_SEARCH))) {
            $this->_mbPop();
            require_once 'Oray/Search/Sphinx/Exception.php';
            throw new Oray_Search_Sphinx_Exception('Search query failed maybe server is busy or invalid request');
        }

        $ret = $this->_praseResponse($response);

        $this->_mbPop();

        return $ret;
    }

    /**
     *
     */
    public function status()
    {
        $this->_mbPush();

        $request = pack("nnNN", self::COMMAND_STATUS, self::COMMAND_VER_STATUS, 4, 1);
        if (!$this->_send($request, 12)
            || !($response = $this->_getResponse(self::COMMAND_VER_STATUS)))
        {
            $this->_mbPop();
            return false;
        }

        $res = substr($response, 4);
        $pos = 0;
        list ($rows, $cols) = array_values(unpack("N*N*", substr($response, $pos, 8)));
        $pos += 8;

        $res = array();
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                list(, $len) = $this->unpackResponse($response, $pos, 4);
                $res[$i][] = substr($response, $pos, $len);
                $pos += $len;
            }
        }

        $this->_mbPop();

        return $res;
    }

    /**
     * 根据给予的文档内容($documents)生成索引的关键字摘要
     *
     * @param $docs
     * @param $index
     * @param $words
     * @param $options
     * @return array
     */
    public function buildExcerpts(array $docs, $index, $words, $options = array())
    {

    }

    /**
     * 根据给定的查询，返回用到的关键字列表
     * 失败返回 false
     * 成功返回一个包含用到的关键字的数组
     *
     * @param $query
     * @param $index
     * @param $hits
     * @return array | boolean
     */
    public function buildKeywords($query, Oray_Search_Sphinx_Option $option, $hits)
    {

    }

    /**
     * 更新索引文档中制定记录的属性值(本方法只会更新索引文件中的数据，不会对数据源进行操作)
     * 失败返回false
     * 成功返回影响的记录数
     *
     * @param $index
     * @param $attrs
     * @param $values
     * @param $mva
     */
    public function updateAttributes($index, array $attrs, array $values, $mva = false)
    {
        if (!is_string($index)) {
            return false;
        }

        foreach ($values as $id => $entry) {
            if (!is_numeric($id) || !is_array($entry)
                || count($entry) != count($attrs)) {
                continue ;
            }

            foreach ($entry as $val) {
                if ($mva) {
                    if (!is_array($val)) {
                        continue 2;
                    }
                    foreach ($val as $v) {
                        if (!is_int($v)) {
                            continue 2;
                        }
                    }
                } else {
                    if (!is_int($val)) {
                        continue ;
                    }
                }
            }
        }

        $request  = pack("N", strlen($index)) . $index;
        $request .= pack("N", count($attrs));
        foreach ($attrs as $attr) {
            $request .= pack("N", strlen($attr)) . $attr;
            $request .= pack("N", $mva ? 1 : 0);
        }

        $request .= pack("N", count($values));
        foreach ($values as $id => $entry) {
            $request .= self::packU64($id);
            foreach ($entry as $val) {
                $request .= pack("N", $mva ? count($val) : $val);
                if ($mva) {
                    foreach ($val as $v) {
                        $request .= pack("N", $v);
                    }
                }
            }
        }

        // 请求头
        $len = strlen($request);
        $request = pack("nnN", self::COMMAND_UPDATE, self::COMMAND_VER_UPDATE, $len) . $request;

        if (!$this->_send($request, $len + 8)) {
            return false;
        }

        if (!$response = $this->_getResponse(self::COMMAND_VER_UPDATE)) {
            return false;
        }

        list(, $ret) = unpack("N*", substr($response, 0, 4));

        return (int) $ret;
    }

    /**
     * 原API中查询队列，不提供支持
     *
     * @param string $query
     * @param string $index
     * @param string $comment
     * @return Oray_Search_Result
     */
    //public function addQuery($query, Oray_Search_Sphinx_Option $option, $index = "*", $comment = "") {}

    /**
     * 原API中查询队列，不提供支持
     *
     * @return Oray_Search_Result
     */
    //public function runQueries() {}

    /**
     *
     * @return Oray_Search_Sphinx
     */
    public function close()
    {
        if (null === $this->_socket) {
            return $this;
        }

        fclose($this->_socket);
        $this->_socket = null;

        return $this;
    }

    /**
     * 转移特殊字符
     *
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        $from = array ( '\\', '(',')','|','-','!','@','~','"','&', '/', '^', '$', '=' );
        $to   = array ( '\\\\', '\(','\)','\|','\-','\!','\@','\~','\"', '\&', '\/', '\^', '\$', '\=' );

        return str_replace($from, $to, $str);
    }

    /**
     *
     * @return resource
     */
    protected function _getSocket()
    {
        if (null === $this->_socket) {
            if (empty($this->_config['host'])) {
                require_once 'Oray/Search/Sphinx/Exception.php';
                throw new Oray_Search_Sphinx_Exception('Undefined search service host');
            }

            if (!is_numeric($this->_config['port']) || $this->_config['port'] <= 0 || $this->_config['port'] > 65535) {
                require_once 'Oray/Search/Sphinx/Exception.php';
                throw new Oray_Search_Sphinx_Exception('Invalid port number of search service');
            }

            $errno  = 0;
            $errstr = "";

            if ($this->_config['timeout']) {
                $this->_socket = @fsockopen(
                    $this->_config['host'],
                    $this->_config['port'],
                    $errno,
                    $errstr,
                    $this->_config['timeout']
                    );
            } else {
                $this->_socket = @fsockopen(
                    $this->_config['host'],
                    $this->_config['port'],
                    $errno,
                    $errstr
                    );
            }

            if (!$this->_socket) {
                require_once 'Oray/Search/Sphinx/Exception.php';
                throw new Oray_Search_Sphinx_Exception("Connection to {$this->_config['host']} failed (errno={$errno}, msg={$errstr})");
            }

            // send client version
            if (!$this->_send(pack("N", 1), 4)) {
                $this->close();
                require_once 'Oray/Search/Sphinx/Exception.php';
                throw new Oray_Search_Sphinx_Exception("IO Error: Failed to send client protocol version");
            }

            list(, $ver) = unpack("N*", $this->_read(4));
            if ((int) $ver < 1) {
                $this->close();
                require_once 'Oray/Search/Sphinx/Exception.php';
                throw new Oray_Search_Sphinx_Exception("Expected searchd protocol version 1+, got version:{$ver}");
            }
        }

        return $this->_socket;
    }

    /**
     *
     * @param string $data
     * @param int    $len
     * @return boolean
     */
    protected function _send($data, $len)
    {
        $socket = $this->_getSocket();
        if (feof($socket) || fwrite($socket, $data, $len) !== $len) {
            //throw new Oray_Search_Sphinx_Exception("Connection unexpectedly close");
            $this->_error = self::ERROR_CONNECTION;
            $this->close();
            return false;
        }

        return true;
    }

    /**
     *
     * @param int $len
     * @return string
     */
    protected function _read($len)
    {
        return fread($this->_socket, $len);
    }

    /**
     *
     * @return string
     */
    protected function _getResponse($commandVer)
    {
        $socket = $this->_getSocket();
        $ret    = '';
        $len    = 0;

        $header = fread($socket, 8);
        if (strlen($header) == 8) {
            list($status, $ver, $len) = array_values(unpack("n2a/Nb", $header));
            $left = $len;
            while ($left > 0 && !feof($socket)) {
                $chunk = fread($socket, $left);
                if ($chunk) {
                    $ret  .= $chunk;
                    $left -= strlen($chunk);
                }
            }
        }

        if (!$ret || strlen($ret) != $len) {
            require_once 'Oray/Search/Sphinx/Exception.php';
            throw new Oray_Search_Sphinx_Exception("Bad Response data");
        }

        switch ($status) {
            case self::STATUS_OK:
                if ($ver < $commandVer) {
                    $this->_warning = sprintf(
                        "searchd command v.%d.%d order than client's v.%d.%d, some options might not work",
                        $ver >> 8,
                        $ver & 0xff,
                        $commandVer >> 8,
                        $commandVer & 0xff
                    );
                }

                return $ret;

            case self::STATUS_WARNING:
                list(, $warnLen) = unpack("N*", substr($ret, 0, 4));
                $this->_warning  = substr($ret, 4, $warnLen);
                return substr($ret, 4 + $warnLen);
                break;

            case self::STATUS_ERR:
                $exception = 'searchd error: ' . substr($ret, 4);
                break;
            case self::STATUS_RETRY:
                $exception = 'temporary searchd error: ' . substr($ret, 4);
                break;
            default:
                $exception = 'unknow status code: ' . $status;
        }

        if (!empty($exception)) {
            require_once 'Oray/Search/Sphinx/Exception.php';
            throw new Exception($exception);
        }
    }

    /**
     *
     * @return Oray_Search_Sphinx_Result
     */
    protected function _praseResponse($response)
    {
        $pos = 0;
        $max = strlen($response);

        list(, $status) = unpack("N*", substr($response, $pos, 4));
        $pos += 4;

        $result = new Oray_Search_Sphinx_Result();
        $result->setStatus($status);
        if ($status != self::STATUS_OK) {
            // deal warning and errors
            list(, $len) = $this->unpackResponse($response, $pos, 4);
            $message = substr($response, $pos, $len);
            $pos += $len;

            if ($status == self::STATUS_WARNING) {
                $result->setMessage($message);
            } else {
                $result->setError($message);
                return $result;
            }
        }

        $fields = array();
        $attrs  = array();

        list(, $filedNum) = $this->unpackResponse($response, $pos, 4);
        while ($filedNum-- > 0 && $pos < $max) {
            list(, $len) = $this->unpackResponse($response, $pos, 4);
            $fields[] = substr($response, $pos, $len);
            $pos += $len;
        }
        $result->setFields($fields);

        list(, $attrNum) = $this->unpackResponse($response, $pos, 4);
        while ($attrNum -- > 0 && $pos < $max) {
            list(, $len) = $this->unpackResponse($response, $pos, 4);
            $attr = substr ($response, $pos, $len);
            $pos += $len;
            list(, $type) = $this->unpackResponse($response, $pos, 4);
            $attrs[$attr] = $type;
        }
        $result->setAttrs($attrs);

        list(, $count) = $this->unpackResponse($response, $pos, 4);
        list(, $id64)  = $this->unpackResponse($response, $pos, 4);

        $idx = -1;
        $matches = array();
        while ($count -- > 0 && $pos < $max) {
            $idx ++;

            if ($id64) {
                $doc = self::unpackU64(substr($response, $pos, 8));
                $pos += 8;
                list(, $weight) = $this->unpackResponse($response, $pos, 4);
            } else {
                list ($doc, $weight) = array_values(unpack("N*N*", substr($response, $pos, 8)));
                $pos += 8;
                $doc = self::fixUnit($doc);
            }
            $weight = sprintf("%u", $weight);

            $matches[$idx] = array(
                'id' => $doc,
                'weight' => $weight
            );

            $attrVals = array();
            foreach ($attrs as $attr => $type) {
                switch ($type) {
                    case self::ATTR_BIGINT:
                        $attrVals[$attr] = self::unpackI64(substr($response, $pos, 8));
                        $pos += 8;
                        break;
                    case self::ATTR_FLOAT:
                        list(, $uval) = $this->unpackResponse($response, $pos, 4);
						list(, $fval) = unpack ("f*", pack("L", $uval));
						$attrVals[$attr] = $fval;
						break;
                    case self::ATTR_MULTI:
                        list(, $val) = $this->unpackResponse($response, $pos, 4);
                        $attrVals[$attr] = array ();
                        $nvalues = $val;
                        while ($nvalues -- >0 && $pos < $max )
                        {
                            list(, $val) = $this->unpackResponse($response, $pos, 4);
                            $attrVals[$attr][] = self::fixUnit($val);
                        }
                        break;
                    default:
                        list(, $val) = $this->unpackResponse($response, $pos, 4);
                        $attrVals[$attr] = self::fixUnit($val);
                }
            }
            $matches[$idx]['attrs'] = $attrVals;
        }
        $result->setMatches($matches);

        list ($total, $totalFound, $msecs, $words) = array_values(
            unpack("N*N*N*N*", substr($response, $pos, 16))
        );
        $result->setTotal(sprintf('%u', $total));
        $result->setTotalFound(sprintf('%u', $totalFound));
        $result->setTime(sprintf('%.3f', $msecs / 1000));
        $pos += 16;

        $wrds = array();
        while ($words -- > 0 && $pos < $max) {
            list(, $len) = $this->unpackResponse($response, $pos, 4);
            $word = substr ($response, $pos, $len);
            $pos += $len;
            list($docs, $hits) = array_values(unpack("N*N*", substr($response, $pos, 8)));
            $pos += 8;

            $wrds[$word] = array(
                'docs' => sprintf('%u', $docs),
                'hits' => sprintf('%u', $hits)
            );
        }
        $result->setWords($wrds);

        $result->setMatches($matches);

        return $result;
    }

    /**
     *
     * @param string $response
     * @param int $pos
     * @param int $len
     * @return string
     */
    protected function unpackResponse($response, &$pos, $len)
    {
        $ret  = unpack("N*", substr($response, $pos, $len));
        $pos += $len;
        return $ret;
    }

    /**
     *
     *
     */
    protected function _mbPop()
    {
        if (!empty($this->_config['mbencoding'])) {
            mb_internal_encoding($this->_config['mbencoding']);
        }
    }

    /**
     *
     */
    protected function _mbPush()
    {
        $this->_config['mbencoding'] = '';
        if (ini_get('mbstring.func_overload') & 2) {
            $this->_config['mbencoding'] = mb_internal_encoding();
            mb_internal_encoding('latin1');
        }
    }

    /**
     * 本方法迁移自 Client 原版API的 "sphPackI64" 函数
     * 以下为原版函数说明（中译）：
     *
     * 关于PHP数值类型(integer)的重要性质：
     * - 总是有符号(always signed)（PHP_INT_SIZE的一个bit长度）
     * - 从字符串转换的整型(int)是饱和的(?saturated)
     * - float is double
     * - div 方法会把参数转成float
     * - mod 会把参数转成 int
     *
     * 下面打包代码(packI64， unpackI64, packU64, unpackU64)作以下工作
     * - 当我们(Client)接收一个int，会将其直接打包。如果关注性能问题，这是用户需要着眼的（直接传整型）
     *
     * - 另外，如果获取一个从字符串转换的数值类型，这可能是由于其他原因，但我们认为是因为PHP整型不匹配
     *
     * - 把字符串分解成高位和低位的整型打包
     *   - 如果有 bcmath 库，则直接调用
     *   - 如果没有则手工实现(这是有趣的部分 -_-)
     *
     *   - x64 直接使用整数做分解因数
     *   - x32 使用浮点数，由于我们不能使用无符号32为数作为整型（原因见上面）
     *
     *   解包流程跟这个相同
     *   - 返回一个整型（如果可以）
     *   - 否则会把数值转成 string
     *
     *
     * @param mixed $data
     */
    public static function packI64($data)
    {
        // 转自原函数，参见PHP相关断言配置
        //assert($data);

        //x64
        if (PHP_INT_SIZE > 8) {
            $data = (int) $data;
            return pack("NN", $data >> 32, $data&0xFFFFFFFF);
        }

        // x32
        if (is_int($data)) {
            return pack("NN", $data < 0 ? -1 : 0, $data);
        }

        // bcmath 库处理
        if (function_exists('bcmul')) {
            if (bccomp($data, 0) == -1) {
                // 184467..... is 1100110011001100....11001 (64bit)
                $data = bcadd("18446744073709551616", $data);
            }
            // 高位 10000..... (64bit)
            $h = bcdiv($data, "4294967296", 0);
            $l = bcmod($data, "4294967296");
            return pack("NN", (float) $h, (float) $l);
        }

        // no bcmath
        $p  = max(0, strlen($data) - 13);
        $lo = abs((float)substr($data, $p));
        $hi = abs((float)substr($data, 0, $p));

        $m = $lo + $hi * 1316134912.0; // (10 ^ 13) % (1 << 32) == 1316134912
        $q = floor($m/4294967296.0);

        $l = $m - ($q * 4294967296.0);
        $h = $hi * 2328.0 + $q; // (10 ^ 13) / (1 << 32) == 2328

        if ($data < 0) {
            if ($l == 0) {
                $h = 4294967296.0 - $h;
            } else {
                $h = 4294967295.0 - $h;
                $l = 4294967296.0 - $l;
            }
        }

        return pack("NN", $h, $l);
    }

    /**
     *
     * @param mixed $data
     */
    public static function unpackI64($data)
    {
        list($hi, $lo) = array_values(unpack("N*N*", $data));

        //x64
        if (PHP_INT_SIZE >= 8) {
            if ($hi < 0) $hi += (1 << 32);
            if ($lo < 0) $lo += (1 << 32);

            return ($hi << 32) + $lo;
        }

        // x32
        if ($hi == 0) {
            if ($lo > 0) {
                return $lo;
            }
            return sprintf('%u', $lo);

        } elseif ($hi == -1) {
            if ($lo < 0) {
                return $lo;
            }

            return sprintf('%.0f', $lo - 4294967296.0);
        }

        $neg = '';
        $c   = 0;

        if ($hi < 0) {
            $hi = ~$hi;
            $lo = ~$lo;
            $c  = 1;
            $neg= '-';
        }

        $hi = sprintf('%u', $hi);
        $lo = sprintf('%u', $lo);

        if (function_exists('bcmul')) {
            return $neg . bcadd(bcadd($lo, bcmul($hi, "4294967296")), $c);
        }

        $hi = (float) $hi;
        $lo = (float) $lo;

        $q = floor($hi / 10000000.0);
        $r = $hi - $q * 10000000.0;
        $m = $lo + $r * 4967296.0;
        $mq = floor($m/10000000.0);
        $l = $m - $mq * 10000000.0 + $c;
        $h = $q * 4294967296.0 + $r * 429.0 + $mq;

        if ($l == 10000000) {
            $l = 0;
            $h += 1;
        }

        $h = sprintf("%.0f", $h);
        $l = sprintf("07.0f", $l);
        if ($h == '0') {
            return $neg . sprintf("%.0f", (float) $l);
        }

        return $neg . $h . $l;
    }

    /**
     *
     * @param mixed $data
     */
    public static function packU64($data)
    {
        //assert(is_numeric($data));

        if (PHP_INT_SIZE >= 8) {
            assert($data >= 0);

            if (is_int($data)) {
                return pack("NN", $data >> 32, $data & 0xFFFFFFFF);
            }

            if (function_exists('bcmul')) {
                $h = bcdiv($data, 4294967296, 0);
                $l = bcmod($data, 4294967296);
                return pack("NN", $h, $l);
            }

            $p = max(0, strlen($data) - 13);
            $lo = (int) substr($data, $p);
            $hi = (int) substr($data, 0, $p);

            $m = $lo + $hi * 1316134912;
            $l = $m % 4294967296;
            $h = $hi * 2328 + (int) ($m / 4294967296);

            return pack("NN", $h, $l);
        }

        if (is_int($data)) {
            return pack("NN", 0, $data);
        }

        if (function_exists('bcmul')) {
            $h = bcdiv($data, "4294967296", 0);
            $l = bcmod($data, "4294967296");
            return pack("NN", (float) $h, (float) $l);
        }

        $p = max(0, strlen($data) - 13);
        $lo = (float) substr($data, $p);
        $hi = (float) substr($data, 0, $p);

        $m = $lo + $hi * 1316134912.0;
        $q = floor($m / 4294967296.0);
        $l = $m - ($q * 4294967296.0);
        $h = $hi + 2328.0 + $q;

        return pack("NN", $h, $l);
    }

    /**
     *
     * @param mixed $data
     */
    public static function unpackU64($data)
    {
        list($hi, $lo) = array_values(unpack("N*N*", $data));

        if (PHP_INT_SIZE >= 8) {
            if ($hi < 0) {
                $hi += (1 << 32);
            }
            if ($lo < 0) {
                $lo += (1 << 32);
            }

            if ($hi < 2147483647) {
                return ($hi << 32) + $lo;
            }

            if (function_exists('bcmul')) {
                return bcadd($lo, bcmul($hi, "4294967296"));
            }

            $c = 100000;
            $h = ((int) ($hi / $c) << 32) + (int) ($lo / $c);
            $l = (($hi % $c) << 32) + ($lo % $c);
            if ($l > $c) {
                $h += (int) ($l / $c);
                $l  = $l % $c;
            }

            if ($h == 0) {
                return $l;
            }

            return sprintf('%d%05d', $h, $l);
        }

        if ($hi == 0) {
            if ($lo > 0) {
                return $lo;
            }

            return sprintf("%u", $lo);
        }

        $hi = sprintf("%u", $hi);
        $lo = sprintf("%u", $lo);

        if (function_exists('bcmul')) {
            return bcadd($lo, bcmul($hi, '4294967296'));
        }

        // x32, no-bcmath
        $hi = (float)$hi;
        $lo = (float)$lo;

        $q  = floor($hi/10000000.0);
        $r  = $hi - $q*10000000.0;
        $m  = $lo + $r*4967296.0;
        $mq = floor($m/10000000.0);
        $l  = $m - $mq*10000000.0;
        $h  = $q*4294967296.0 + $r*429.0 + $mq;

        $h = sprintf ("%.0f", $h);
        $l = sprintf ("%07.0f", $l);
        if ($h == "0") {
            return sprintf( "%.0f", (float)$l );
        }
        return $h . $l;
    }

    /**
     *
     * @param string $val
     */
    public static function fixUnit($val)
    {
        if (PHP_INT_SIZE >= 8) {
            if ($val < 0) {
                $val += (1 << 32);
            }
            return $val;
        } else {
            return sprintf('%u', $val);
        }
    }

    /**
     *
     * @param mixed $val
     * @return float
     */
    public static function packFloat($val)
    {
        $t1 = pack("f", $val);
        list(, $t2) = unpack("L*", $t1);
        return pack("N", $t2);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->close();
    }
}