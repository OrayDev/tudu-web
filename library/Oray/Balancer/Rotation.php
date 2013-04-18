<?php
/**
 * Oray Framework
 * 任务负载均衡调节器接口虚类
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Balancer
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Rotation.php 8939 2012-01-04 09:03:14Z cutecube $
 */

/**
 * @category   Oray
 * @package    Oray_Balancer
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Balancer_Rotation extends Oray_Balancer_Abstract
{
    /**
     * 当前指针位置
     *
     * @var int
     */
    protected $_current = 0;

    /**
     * 存储退出前最后一次顺序ID
     *
     * @var Oray_Memcache
     */
    protected $_memcache = null;

    /**
     * 获取分配对象
     *
     * @return mixed
     */
    protected function _select()
    {
        if ($this->_current + 1 >= $this->_count) {
            $item = reset($this->_items);
            $this->_current = 0;
        } else {
            $item = next($this->_items);
            $this->_current++;
        }

        if (empty($item)) {
            $item = current($this->_items);
        }

        return $item;
    }

    /**
     * 重写父类方法
     *
     * @param string $idx
     * @return Oray_Balancer_Rotation
     */
    public function setCurrent($idx)
    {
        parent::setCurrent($idx);

        $item = reset($this->_items);
        do {
            if (key($this->_items) === $idx) {
                break;
            }
        } while (false !== ($item = next($this->_items)));

        return $this;
    }
}