<?php
/**
 * 
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * @version $Id
 */

/**
 * 处理分页导航模板
 * 
 * 输出的模板变量
 * $pagenav {array}
 * {{$pagenav.~}}
 * 
 * nums: {array} 页码列表
 * currpage: {int} 当前页面
 * recordcount: {int} 记录数
 * pagecount: {int} 总页数
 * query: {array} 页面传递参数
 * next: {int} 下一页的页码
 * prev: {int} 前一页页码
 * 
 * @param array $params
 *      currpage：当前分页
 *      recordcount: 记录总数
 *      pagecount: 分页总数
 *      template: 模板文件
 *      numcount: 分页数字的个数
 *      url: 跳转url
 *      query: 页面跳转传递的参数
 * @param Smarty $smarty
 * @return void
 */
function smarty_function_pagenavigator($params, &$smarty)
{
    if (empty($params['template'])) {
        $smarty->trigger_error('Undefined pagenavigator template file');
    }
    
    if (!isset($params['recordcount'])) {
        //$smarty->trigger_error('Undefined recordcount');
        return ;
    }
    
    if (!isset($params['pagecount'])) {
        //$smarty->trigger_error('Undefined pagecount');
        return ;
    }
    
    $currpage    = max(1, (int) @$params['currpage']);
    $pagecount   = (int) $params['pagecount'];
    $recordcount = (int) $params['recordcount'];
    $numcount    = (int) @$params['numcount'];
    
    // 模板变量
    $tplvar = array(
        'pagecount' => $pagecount,
        'recordcount' => $recordcount,
        'currpage' => $currpage,
        'next' => $currpage < $pagecount ? $currpage + 1 : null,
        'prev' => $currpage > 1 ? $currpage - 1 : null,
        'url'  => empty($params['url']) ? $_SERVER['SCRIPT_NAME'] : $params['url'],
        'jsfunc' => @$params['jsfunc']
    );
    
    // 输出页码
    if ($numcount > 0) {
        $lbound = $currpage > intval($numcount / 2) ? $currpage - intval($numcount / 2) : 1;
        $ubound = $lbound + $numcount - 1 > $pagecount 
                ? $pagecount 
                : $lbound + $numcount - 1;
        
        $nums = array();
        for ($i = $lbound; $i <= $ubound; $i++) {
            $nums[] = $i;
        }
        
        $tplvar['nums'] = $nums;
    }
    
    if (!empty($params['query']) && is_array($params['query'])) {
        $tplvar['query'] = $params['query'];
    }
    
    $array = array(
        'smarty_include_vars'     => array('pagenav' => $tplvar),
        'smarty_include_tpl_file' => $params['template']
    );
    
    $smarty->register_function('page_url', 'smarty_function_pagenavigator_buildurl');
    $smarty->register_function('page_jsfunc', 'smarty_function_pagenavigator_buildjsfunc');
    $smarty->_smarty_include($array);
}

/**
 * 处理页面跳转的URL
 * 
 * @param $params
 * @param $smarty
 */
function smarty_function_pagenavigator_buildurl($params, &$smarty)
{
    if (!isset($params['url'])) {
        $smarty->trigger_error('Undefined url path');
    }
    
    $path = $params['url'];
    
    if (!isset($params['query']) || !is_array($params['query'])) {
        $params['query'] = array();
    }
    
    $params['query']['page'] = $params['page'];
    
    foreach ($params['query'] as $key => $val) {
        if (strpos($path, '$' . $key)) {
            $path = str_replace('$' . $key, $val, $path);
            unset($params['query'][$key]);
        }
    }
    
    $qs = $params['query'] ? '?' . http_build_query($params['query']) : '';
    
    return $path . $qs;
}

/**
 * 输出js函数
 * 
 * @param $params
 * @param $smarty
 */
function smarty_function_pagenavigator_buildjsfunc($params, &$smarty)
{
	if (empty($params['func'])) {
		return ;
	}
	
    if (!isset($params['page'])) {
        $params['page'] = 1;
    }
	
    $ret = $params['func'];
	$arr = array();
	preg_match_all('/\{(\d+)\}/', $params['func'], $arr);
	
    foreach ($arr[1] as $k) {
        $arg = isset($params[$k]) ? $params[$k] : null;
    	
    	if (is_array($arg)) {
    		$arg = json_encode($arg);
    	} elseif (is_string($arg)) {
    		$arg = "'{$arg}'";
    	} elseif (!is_numeric($arg)) {
    		$arg = 'null';
    	}
    	
    	$ret = str_replace('{'. $k .'}', $arg, $ret);
    }
    
    $ret = str_replace('$page', $params['page'], $ret);
    
    return $ret;
}