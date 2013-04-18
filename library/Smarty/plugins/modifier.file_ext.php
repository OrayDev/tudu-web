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
 * @return string
 */
function smarty_modifier_file_ext($fileName)
{
    return strtolower(array_pop(explode('.', $fileName)));
}
?>