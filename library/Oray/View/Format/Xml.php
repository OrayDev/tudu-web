<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Oray_View_Format_Interface
 */
require_once 'Oray/View/Format/Interface.php';

/**
 * @category   Oray
 * @package    Oray_View
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_View_Format_Xml implements Oray_View_Format_Interface
{
    /**
     * 返回xml文档根节点名称
     *
     * @var string
     */
    protected static $_rootNode = 'response';

    /**
     * 设置根节点名称
     *
     * @param string $name
     */
    public static function setRootNode($node)
    {
        self::$_rootNode = $node;
    }

    /**
     * 格式化输出内容
     *
     * @param array $data
     * @param array $options
     */
    public function format($data = array(), $options = array())
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?><' . self::$_rootNode . '>';
        $xml .= $this->_format($data);
        $xml .= '</' . self::$_rootNode . '>';

        return $xml;
    }

    /**
     * (non-PHPdoc)
     * @see OpenApi_Response_Formatter_Interface::getContentType()
     */
    public function getContentType()
    {
        return 'text/xml;charset=utf-8';
    }

    /**
     *
     * @param array $data
     */
    protected function _format(array $data)
    {
        $ret = '';
        foreach ($data as $key => $item) {
            $ret .= "<{$key}>";

            if (is_string($item)) {
                $ret .= '<![CDATA[' . $item . ']]>';
            } elseif (is_bool($item)) {
                $ret .= $item ? 'true' : 'false';
            } elseif (is_array($item)) {
                $ret .= $this->_format($item);
            } else {
                $ret .= $item;
            }

            $ret .= "</{$key}>";
        }

        return $ret;
    }
}