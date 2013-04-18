<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @param boolean
 * @return string
 */
 
/* 原程序
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (strlen($string) > $length) {
        $length -= min($length, strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        }
        if(!$middle) {
            return substr($string, 0, $length) . $etc;
        } else {
            return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
        }
    } else {
        return $string;
    }
}
*/

// 修改支持utf-8中文
// 09-7-27 增加参数 iscnchar 为真时非单字节字符截取一半长度
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false, $iscnchar = false)
{
    if ($length == 0)
        return '';
	
	if ($iscnchar)
		$length = isbyte($string) ? $length : max(1, intval($length/2));

    if (mb_strlen($string, 'utf-8') > $length) {
        $length -= min($length, mb_strlen($etc, 'utf-8'));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, 'utf-8'));
        }
        if(!$middle) {
            return mb_substr($string, 0, $length, 'utf-8') . $etc;
        } else {
            return mb_substr($string, 0, $length/2, 'utf-8') . $etc . mb_substr($string, -$length/2, 'utf-8');
        }
    } else {
        return $string;
    }
}

/* vim: set expandtab: */

?>
