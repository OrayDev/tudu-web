<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     json<br>
 * Date:     Feb 26, 2003
 * Purpose:  convert to json format
 * Example:  {$text|json}
 * @version  1.0
 * @author   Hiro <hiro at oray dot com>
 * @param string
 * @return string
 */
function smarty_modifier_json($string, $value)
{
    return json_encode($value);
}

/* vim: set expandtab: */

?>
