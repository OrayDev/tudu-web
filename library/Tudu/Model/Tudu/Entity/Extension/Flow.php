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
 * @version    $Id: Flow.php 1837 2012-05-04 09:36:23Z cutecube $
 */

/**
 * 图度工作流扩展数据
 *
 * @category   Tudu
 * @package    Tudu_Model_Entity_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Model_Tudu_Entity_Extension_Flow extends Tudu_Model_Tudu_Entity_Extension_Abstract
{

    /**
     * 步骤列表
     *
     * @var string
     */
    private $_steps = array();

    /**
     * 从图度中获取初始化数据
     *
     * @param Tudu_Model_Tudu_Entity_Tudu $tudu
     */
    public function init(Tudu_Model_Tudu_Entity_Tudu &$tudu)
    {}

    /**
     * 添加步骤
     *
     * @return void
     */
    public function addStep(Tudu_Model_Tudu_Entity_Step $step)
    {
        $this->_steps[] = $step;
    }

    /**
     * 获取步骤列表
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }
}