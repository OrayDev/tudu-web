<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty list pagenavigation modifier plugin
 *
 */
function smarty_function_tudu_list_pagenav(array $params, &$smarty)
{
	$pageCount   = isset($params['pagecount']) ? (int) $params['pagecount'] : null;
	$pageSize    = isset($params['pagesize']) ? (int) $params['pagesize'] : 0;
	$recordCount = isset($params['recordcount']) ? (int) $params['recordcount'] : 0;
	
	
	if (!$pageCount) {
		$pageCount = $pageSize > 0 ? intval(($recordCount - 1) / $pageSize) + 1 : null;
	}
	
	if ($pageCount <= 1) {
		return ;
	}
	
	$numCount = isset($params['numcount']) ? (int) $params['numcount'] : 3;
	$url      = !empty($params['url']) ? $params['url'] : '';
	$query    = !empty($params['query']) ? $params['query'] : null;
	$target   = !empty($params['target']) ? $params['target'] : '_self';
	
	$ret = array('...');
	for ($i = 1, $l = min($pageCount, $numCount); $i <= $l; $i++) {
		$href  = $url . '?' . ($query ? $query . '&page=' . $i : 'page=' . $i);
		$ret[] = "<a href=\"{$href}\" target=\"{$target}\">{$i}</a>";
	}
	
	if ($pageCount > $numCount) {
		if ($pageCount - $numCount > 1) {
			$ret[] = '...';
		}
		
		$href  = $url . '?' . ($query ? $query . '&page=' . $pageCount : 'page=' . $pageCount);
		$ret[] = "<a href=\"{$href}\" target=\"{$target}\">{$pageCount}</a>";
	}
	
	return '<div class="pages">' . implode('', $ret) . '</div>';
}

?>