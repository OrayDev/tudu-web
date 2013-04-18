<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Search
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Result.php 7751 2011-09-27 10:47:59Z cutecube $
 */


/**
 * Sphinx Api
 *
 * @category Oray
 * @package  Oray_Search
 * @author CuTe_CuBe
 */
class Oray_Search_Sphinx_Result
{
    /**
     *
     * @var int
     */
    protected $_status;

    /**
     *
     * @var string
     */
    protected $_warning;

    /**
     *
     * @var string
     */
    protected $_error;

    /**
     * 匹配结果列表
     *
     * @var array
     */
    protected $_matches = array();

    /**
     * 搜索时间
     *
     * @var int
     */
    protected $_time;

    /**
     * 匹配记录总数
     *
     * @var int
     */
    protected $_total;

    /**
     *
     * @var array
     */
    protected $_fields;

    /**
     *
     * @var array
     */
    protected $_attrs;

    /**
     *
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        $act = substr($name, 0, 3);
        $key = '_' . strtolower(substr($name, 3));

        if ($act == 'get') {
            return $this->{$key};
        } elseif ($act == 'set') {
            $this->{$key} = $args[0];
            return $this;
        }
    }
}