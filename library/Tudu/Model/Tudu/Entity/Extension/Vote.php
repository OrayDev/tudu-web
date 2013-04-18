<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Vote.php 1826 2012-04-27 09:47:39Z cutecube $
 */

/**
 * 图度投票扩展数据
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Entity_Extension_Vote extends Tudu_Model_Tudu_Entity_Extension_Abstract
{

    /**
     * 选项列表
     *
     * @var array
     */
    protected $_options = array();

    /**
     * 添加选项
     *
     * @param string $text
     * @param int    $orderNum
     */
    public function addOption(array $option)
    {
        $this->_options[] = $option;
    }

    /**
     *
     */
    public function getOptions()
    {
        return $this->_options;
    }
}