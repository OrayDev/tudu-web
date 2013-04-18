<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * 格式化输出文件大小
 *
 *
 * @param int
 * @param string 指定单位
 * @return string
 */
function smarty_modifier_format_filesize($size, $unit = null)
{
    $format = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");

    // 负数情况
    $sign = 1;
    if ($size < 0) {
        $size = abs($size);
        $sign = -1;
    }

    if ($unit !== null) {
        $k = array_search(strtoupper($unit), $format);
        return $sign * ($size / pow(1024, $k));
    }

    $count = 0;
    while($size > 1024 && $count < 8) {
       $size = $size/1024;
       $count++;
    }
    $decimals = ($count == 0) ? 0 : max(0, 3 - strlen((int) $size));
    $return = number_format($size * $sign, $decimals, '.', ',') . " " . $format[$count];
    return $return;
}

?>