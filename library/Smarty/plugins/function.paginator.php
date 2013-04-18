<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @version $Id: function.paginator.php 5588 2011-01-21 17:40:05Z gxx $
 */

/**
 * Smarty {paginator} function plugin
 *
 * Type:     function<br>
 * Name:     paginator<br>
 * Purpose:  生成网站公用分页导航模板
 * @author   Hiro <hiro@oray.com>
 * @param    array
 *           key : 要替换的key，把 ?page=#page#中替换成 ?page=1
 *           name: page的参数变量名，用于自动解析时使用
 *           maxlink: 快速链接显示的最大数量
 *           offset: 偏移量，可不传
 *           url: 分页链接地址，如 xxx.php?page=#page#，可不传
 * @param    Smarty
 * @return   string
 */
function smarty_function_paginator($params, &$smarty)
{
    if (empty($params['template'])) {
        $smarty->trigger_error("paginator: missing 'template' parameter", E_USER_NOTICE);
    }
    
    if (!isset($params['data']['recordcount'])
        || empty($params['data']['pagesize'])
        || !isset($params['data']['current'])) {
        $smarty->trigger_error("paginator: missing 'data' parameter", E_USER_NOTICE);
    }
    
    $params = array_merge(array(
        'key'     => '#page#',
        'query'   => '#query#',
        'name'    => 'page',
        'format'  => 'string',
        'maxlink' => 10,
        //'offset' => 5,
        'url'     => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''
    ), $params);

    if (!isset($params['data']['query']) || !is_array($params['data']['query'])) {
        $params['data']['query'] = array();
    }

    // 替换查询参数，构造js参数比较有用
    if (false !== strpos($params['url'], $params['query'])) {
        $query = smarty_function_paginator_get_query($params['data']['query'], $params['name'], $params['key'], $params['format']);
        $params['url'] = str_replace($params['query'], $query, $params['url']);
    }
    
    // 自动解析分页参数
    if (false === strpos($params['url'], $params['key'])) {
        list($script, $query) = explode('?', $params['url'] . '?');
        parse_str($query, $query);
        $query = smarty_function_paginator_get_query(array_merge($query, $params['data']['query']), $params['name'], $params['key']);
        $params['url'] = $script . '?' . $query;
    }
    
    $quick = array();
    $page_count = ceil($params['data']['recordcount'] / $params['data']['pagesize']);
    $current = (int) $params['data']['current'];
    if ($current < 1 || $current > $page_count) {
        $current = min(1, $page_count);
    }

    // 生成快速分页链接
    if ($page_count > 1 && $params['maxlink'] > 0) {
        $offset = isset($params['offset']) ? $params['offset'] : (int) ($params['maxlink'] / 2);
		if ($params['maxlink'] > $page_count) {
			$from = 1;
			$to = $page_count;
		} else {
			$from = $current - $offset;
			$to = $from + $params['maxlink'] - 1;
			if ($from < 1) {
				$to = $current + 1 - $from;
				$from = 1;
				if(($to - $from) < $params['maxlink']) {
					$to = $params['maxlink'];
				}
			} elseif($to > $page_count) {
				$from = $page_count - $params['maxlink'] + 1;
				$to = $page_count;
			}
		}
		for($i = $from; $i <= $to; $i++) {
		    $quick[] = $i;
		}
    }

    $_data = array_merge($params['data'], array(
        'pagecount' => $page_count,
        'current'   => $current,
        'first'     => $current == 1 || !$page_count,
        'last'      => $current == $page_count || !$page_count,
        'prev'      => $current > 1 ? $current - 1 : null,
        'next'      => $current < $page_count ? $current + 1 : null,
        'quick'     => $quick,
        'url'       => $params['url'],
        'key'       => $params['key']
    ));
    
    $array = array(
        'smarty_include_vars'     => array('_data' => $_data),
        'smarty_include_tpl_file' => $params['template']
    );
    
    $smarty->_smarty_include($array);
}

function smarty_function_paginator_get_query(array $query, $name, $key, $format = 'string')
{
    $query[$name] = '_TMEP_KEY_';
    if ($format == 'json') {
        $query = str_replace('_TMEP_KEY_', $key, htmlentities(json_encode($query)));
    } else {
        $query = str_replace('_TMEP_KEY_', $key, http_build_query($query));
    }
    return $query;
}