<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Exception.php 1894 2012-05-31 08:02:57Z cutecube $
 */

/**
 * @see Model_Abstract
 */
require_once 'Model/Abstract.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Group_Group extends Model_Abstract
{
    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function prepareGroup(Model_Tudu_Tudu &$tudu)
    {

    }

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function saveGroup(Model_Tudu_Tudu &$tudu)
    {
        $group = $tudu->getExtension('Model_Tudu_Extension_Group');

        if (null == $group) {
            return ;
        }

        $children = $group->getChildren();
        foreach ($children as $child) {

        }
    }
}