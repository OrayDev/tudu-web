<?php
/**
 * Oray Framework
 * 
 * @category   Oray
 * @package    Oray_Unzip_Entry
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Entry.php 2801 2013-04-02 09:57:31Z chenyongfa $
 */
class Oray_Unzip_Entry
{
    /**
     * 文件内容
     *
     * @var string
     */
    public $data = '';

    /**
     * 错误代码
     *
     * @var int
     */
    public $error = 0;

    /**
     * 错误信息
     *
     * @var string
     */
    public $errormsg = '';

    /**
     * 文件名
     *
     * @var string
     */
    public $name = '';

    /**
     * 文件路径
     *
     * @var string
     */
    public $path = '';

    /**
     * 文件创建时间
     *
     * @var int
     */
    public $time = 0;

    /**
     *
     * @param array $in_Entry
     */
    public function __construct($in_Entry)
    {
        $this->data     = $in_Entry['D'];
        $this->error    = $in_Entry['E'];
        $this->errormsg = $in_Entry['EM'];
        $this->name     = $in_Entry['N'];
        $this->path     = $in_Entry['P'];
        $this->time     = $in_Entry['T'];
    }
}