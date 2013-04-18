<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty upper modifier plugin
 *
 * Type:     modifier<br>
 * Name:     upper<br>
 * Purpose:  convert string to uppercase
 * @link http://smarty.php.net/manual/en/language.modifier.upper.php
 *          upper (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return string
 */
function smarty_modifier_filterrule_description($string, $typeid)
{
    if (null === $html) {
		$html = '{name}{condition}{value}{unit}({isp})';
	}
	
	list($name, $condition, $value, $unit, $isp) = explode('|', $string);
	if ($typeid == Oray_Dao_User_Filter::RULE_TYPE_DEF) {
		return $name;
	} else {
		return str_replace(array('{name}', '{condition}', '{value}', '{unit}', '{isp}'),
						   array($name, $condition, $value, $unit, $isp), $html);
	}
}