<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @version $Id: function.query.php 6860 2011-07-13 10:47:59Z gxx $
 */

/**
 * Smarty {paginator} function plugin
 *
 * Type:     function<br>
 * Name:     query<br>
 * Purpose:  生成查询URL参数
 * @author   Hiro <hiro@oray.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */
function smarty_function_query($params, &$smarty)
{
    if (!isset($params['query'])) {
        return '';
    }

    $query = $params['query'];
    if (is_string($query)) {
        parse_str($query, $query);
    }

    if (isset($params['sort'])) {
        $key = $params['sort'];
        $desc = isset($params['desc']) ? $params['desc'] : 'desc';
        $asc = isset($params['asc']) ? $params['asc'] : 'asc';
        $default = isset($params['default']) ? $params['default'] : 'asc';
        $default = $default == 'asc' ? $asc : $desc;

        if (isset($query[$key])) {
            if ($query[$key] == $asc) {
                $query[$key] = $desc;
            } elseif ($query[$key] == $desc) {
                $query[$key] = $asc;
            } else {
                $query[$key] = $default;
            }
        } else {
            $query[$key] = $default;
        }
    }

    if (empty($query)) {
        return '';
    }

    return '?' . http_build_query($query);
}
