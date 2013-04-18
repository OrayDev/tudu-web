<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Cache
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cache.php 5679 2011-01-28 06:58:00Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Cache
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Cache
{
    /**
     * Oray_Memcache
     * 
     * @var Oray_Memcache
     */
    private $_storage;
    
    /**
     *
     * @param $storage
     */
    public function __construct(Oray_Memcache $storage)
    {
        $this->_storage = $storage;
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
    public function loadCache($method, $params = null, $expire = null)
    {
        $key = $this->getKey($method, $params);
        $cache = $this->get($key);
        if (false === $cache) {
            
            // 过期时间未设置时，不保存cache，避免一些不想保存的情况发生（可能导致cache不过期）
            if (null !== $expire && is_callable($method)) {
                $cache = call_user_func_array($method, $params);
                $this->set($key, $cache, $expire);
            }
        }
        return $cache;
    }
    
    /**
     * 删除Cacahe
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
     * Retrieve item from the storage
     * 
     * @param string $key
     */
    public function get($key)
    {
        return $this->_storage->get($key);
    }
    
    /**
     * Store data at the storage
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expire
     */
    public function set($key, $value, $expire = null)
    {
        return $this->_storage->set($key, $value, 0, $expire);
    }
    
    /**
     * Delete item from the storage
     * 
     * @param string $key
     */
    public function delete($key)
    {
        return $this->_storage->delete($key);
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
                $key = $method[0] . '::' . $method[1];
            }
        }
        $key .= '?' . serialize($params);
        return md5($key);
    }
}