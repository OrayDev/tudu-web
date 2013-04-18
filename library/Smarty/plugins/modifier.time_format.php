<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @version $Id: modifier.time_format.php 4497 2010-08-12 05:46:38Z cutecube $
 */

/**
 * Smarty 时间格式化显示
 *
 * @param int $time 原始时间数量(单位：秒)
 * @param string $format 格式
 * @param string $skipZero 是否去除0值
 * @param string $separator 分割符
 *  			: %d 日
 *				: %h 小时
 *				: %m 分钟
 *				: %s 秒
 *				: %u 毫秒
 * @return string
 */
function smarty_modifier_time_format($time, $format = '%d days %h hours %m minutes', $skipZero = true, $separator = '|')
{
	$steps = array('d' => 86400,
				   'h' => 3600,
				   'm' => 60,
				   's' => 1,
				   'u' => 0.001);

    if (null === $time || !is_numeric($time)) {
        return;
    }

    // 暂时不支持负数的显示
    $time = abs($time);

    $sec = (int) $time;
    $usec = $time - $sec;

    $ret = $format;
    foreach ($steps as $key => $step) {
        if (false === strpos($format, '%' . $key)) continue;

		$value = 0;
        if ($key == 'u') {
            $value = sprintf('%.2f', round(($sec + $usec) / $step, 2));
        } elseif ($sec >= $step) {
            $value = (int) ($sec / $step);
            $sec = $sec % $step;
        } else {
            $value = 0;
        }

        if ($skipZero && $value == 0) {
            $ret = preg_replace('/(%' . $key . '[^%]+)/', '', $ret);
            continue;
        }

        $ret = str_replace('%' . $key, $value, $ret);
    }

    return $ret;
}
?>