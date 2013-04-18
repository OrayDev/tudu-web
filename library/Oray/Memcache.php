<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Memcache
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Memcache.php 7327 2011-08-22 02:41:58Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Memcache
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Memcache extends Memcache
{
    /**
     * Default Values
     */
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT =  11211;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_WEIGHT  = 1;
    const DEFAULT_TIMEOUT = 1;
    const DEFAULT_RETRY_INTERVAL = 15;
    const DEFAULT_STATUS = true;
    const DEFAULT_FAILURE_CALLBACK = null;

    const MEMCACHE_SERIALIZED = 1;

    /**
     * Available options
     *
     * =====> (array) servers :
     * an array of memcached server ; each memcached server is described by an associative array :
     * 'host' => (string) : the name of the memcached server
     * 'port' => (int) : the port of the memcached server
     * 'persistent' => (bool) : use or not persistent connections to this memcached server
     * 'weight' => (int) : number of buckets to create for this server which in turn control its
     *                     probability of it being selected. The probability is relative to the total
     *                     weight of all servers.
     * 'timeout' => (int) : value in seconds which will be used for connecting to the daemon. Think twice
     *                      before changing the default value of 1 second - you can lose all the
     *                      advantages of caching if your connection is too slow.
     * 'retry_interval' => (int) : controls how often a failed server will be retried, the default value
     *                             is 15 seconds. Setting this parameter to -1 disables automatic retry.
     * 'status' => (bool) : controls if the server should be flagged as online.
     * 'failure_callback' => (callback) : Allows the user to specify a callback function to run upon
     *                                    encountering an error. The callback is run before failover
     *                                    is attempted. The function takes two parameters, the hostname
     *                                    and port of the failed server.
     *
     * =====> (boolean) compression :
     * true if you want to use on-the-fly compression
     *
     * =====> (boolean) compatibility :
     * true if you use ttserver server
     *
     * @var array available options
     */
    protected $_options = array(
        'servers' => array(),
        'compression' => false,
        'compatibility' => true,
        'compress_threshold' => 20000
    );

    /**
     * Is compression available?
     *
     * @var boolean
     */
    private $_haveZlib;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @return void
     */
    public function __construct(array $options = array())
    {
        while (list($name, $value) = each($options)) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }

        $value = $this->_options['servers'];
        if (isset($value['host'])) {
            // in this case, $value seems to be a simple associative array (one server only)
            // let's transform it into a classical array of associative arrays
            $this->_options['servers'] = array($value);
        }

        foreach ($this->_options['servers'] as $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = self::DEFAULT_PORT;
            }
            if (!array_key_exists('persistent', $server)) {
                $server['persistent'] = self::DEFAULT_PERSISTENT;
            }
            if (!array_key_exists('weight', $server)) {
                $server['weight'] = self::DEFAULT_WEIGHT;
            }
            if (!array_key_exists('timeout', $server)) {
                $server['timeout'] = self::DEFAULT_TIMEOUT;
            }
            if (!array_key_exists('retry_interval', $server)) {
                $server['retry_interval'] = self::DEFAULT_RETRY_INTERVAL;
            }
            if (!array_key_exists('status', $server)) {
                $server['status'] = self::DEFAULT_STATUS;
            }
            if (!array_key_exists('failure_callback', $server)) {
                $server['failure_callback'] = self::DEFAULT_FAILURE_CALLBACK;
            }
            $this->addServer($server['host'], $server['port'], $server['persistent'],
                             $server['weight'], $server['timeout'],
                             $server['retry_interval'],
                             $server['status'], $server['failure_callback']);
        }

        $this->_haveZlib = function_exists("gzcompress");
    }

    /**
     * Load cache
     *
     * @param mixed $method
     *      Method to load source data
     *      if it's a common function, it should write as a string like 'func'
     *      if it's a member method of an object, it should write as array($object, 'func')
     *      if it's a static method in classes, it also write as array('Oray_XX', 'func')
     * @param array $params
     *      args needs by source method call
     * @param int $expire
     *      set zero to get the maximal lifetime
     * @return mixed
     */
    public function loadCache($method, $params = null, $expire = null, $reload = false)
    {
        $key = $this->getKey($method, $params);
        if ($reload) {
            $cache = false;
        } else {
            $cache = $this->get($key);
        }
        if (false === $cache) {

            if ($this->_options['compression']) {
                $flag = MEMCACHE_COMPRESSED;
            } else {
                $flag = 0;
            }

            // 过期时间未设置时，不保存cache，避免一些不想保存的情况发生（可能导致cache不过期）
            if (null !== $expire && is_callable($method)) {
                $cache = call_user_func_array($method, $params);
                $this->set($key, $cache, $flag, $expire);
            }
        }
        return $cache;
    }

    /**
     * Delete cacahe
     *
     * @param mixed $method
     * @param array $params
     */
    public function deleteCache($method, $params = null)
    {
        $key = $this->getKey($method, $params);
        return $this->delete($key);
    }

    /**
     * Store data at the server
     *
     * @param string  $key
     * @param mixed   $var
     * @param int     $flag
     * @param int     $expire
     * @param boolean $original
     */
    public function set($key, $var, $flag = null, $expire = null, $original = false)
    {
        if (!$original && $this->_options['compatibility']) {
            $var = $this->_serialize($var, $flag, $expire);
            $flag &= ~MEMCACHE_COMPRESSED;
        }
        return parent::set($key, $var, $flag, $expire);
    }

    /**
     * Retrieve item from the server
     *
     * @param string|array $key
     * @param int|array    &$flag
     * @param boolean      $original
     */
    public function get($key, &$flag = null, $original = false)
    {
        $_flag = 0;
        $data = parent::get($key, $_flag);
        if (false !== $data && !$original && $this->_options['compatibility']) {

            if (is_array($key)) {
                $flag = array();
                foreach ($data as $key => &$_data) {
                    $_data = $this->_unserialize($_data, $_flag[$key]);
                    $flag[$key] = $_flag[$key];
                }
            } else {
                $data = $this->_unserialize($data, $_flag);
            }
        }
        $flag = $_flag;
        return $data;
    }

    /**
     * Replace value of the existing item
     *
     * @param string  $key
     * @param mixed   $var
     * @param int     $flag
     * @param int     $expire
     * @param boolean $original
     */
    public function replace($key, $var, $flag = null, $expire = null, $original = false)
    {
        if (!$original && $this->_options['compatibility']) {
            $var = $this->_serialize($var, $flag, $expire);
            $flag &= ~MEMCACHE_COMPRESSED;
        }
        return parent::replace($key, $var, $flag, $expire);
    }

    /**
     * Add an item to the server
     *
     * @param string  $key
     * @param mixed   $var
     * @param int     $flag
     * @param int     $expire
     * @param boolean $original
     */
    public function add($key, $var, $flag = null, $expire = null, $original = fals)
    {
        if (!$original && $this->_options['compatibility']) {
            $var = $this->_serialize($var, $flag, $expire);
            $flag &= ~MEMCACHE_COMPRESSED;
        }
        return parent::add($key, $var, $flag, $expire);
    }

    /**
     * Delete item from the server
     *
     * @param string $key
     * @param int    $timeout
     */
    public function delete($key, $timeout = null)
    {
        return parent::delete($key, $timeout);
    }

    /**
     * Get key
     *
     * @param mixed $method
     * @param array $params
     */
    public function getKey($method, $params = null)
    {
        if (is_scalar($method)) {
            $key = $method;
        } else {
            if (is_object($method[0])) {
                $key = get_class($method[0]) . '->' . $method[1];
            } else {
                $key = $method[0] . '->' . $method[1];
            }
        }
        $key .= '?' . serialize($params);
        return md5($key);
    }

    /**
     * Serialize
     *
     * @param mixed $var
     * @param int   $flag
     * @param int   $expire
     */
    private function _serialize($var, $flag, $expire)
    {
        $flag = (int) $flag;
        if (!is_scalar($var)
            || $flag & self::MEMCACHE_SERIALIZED) {
            $var = serialize($var);
            $flag |= self::MEMCACHE_SERIALIZED;
        }

        $len = strlen($var);
        if ($flag & MEMCACHE_COMPRESSED) {
            if ($this->_haveZlib && $len > $this->_options['compress_threshold']) {
                $var = gzcompress($var);
            } else {
                $flag &= ~MEMCACHE_COMPRESSED;
            }
        }
        return array($var, time(), $flag, $expire);
    }

    /**
     * Unserialize
     *
     * @param string $data
     * @param int    &$flag
     * @return mixed
     */
    private function _unserialize($data, &$flag)
    {
        $flag = (int) $flag;
        if (0 === ($flag & self::MEMCACHE_SERIALIZED)) {
            $data = unserialize($data);
        }
        if (is_array($data) && count($data) == 4) {
            list($var, $time, $flag, $expire) = $data;
            if ($expire > 0 && time() > ($time + $expire)) {
                return false;
            }
            if ($this->_haveZlib && $flag & MEMCACHE_COMPRESSED) {
                $var = gzuncompress($var);
            }
            if ($flag & self::MEMCACHE_SERIALIZED) {
                $var = unserialize($var);
            }
            return $var;
        }
        return false;
    }
}