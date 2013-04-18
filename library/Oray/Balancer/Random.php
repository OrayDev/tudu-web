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
 * @version    $Id: Random.php 8939 2012-01-04 09:03:14Z cutecube $
 */

/**
 * @category   Oray
 * @package    Oray_Balancer
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Balancer_Random extends Oray_Balancer_Abstract
{

    /**
     * 最大随机因子
     *
     * @var int
     */
    const MAX_RAND_SEED = 10000;

    /**
     * 最小随机因子
     *
     * @var int
     */
    const MIN_RAND_SEED = 0;

    /**
     * 获取分配对象
     *
     * @return mixed
     */
    protected function _select()
    {
        // 增大随机范围，解决rand小范围随机性较差
        $rand = rand(self::MIN_RAND_SEED, self::MAX_RAND_SEED);

        $num = $rand % $this->_count;

        $item = reset($this->_items);
        for ($i = 0; $i < $num; $i++) {
            $item = next($this->_items);
        }

        return $item;
    }
}