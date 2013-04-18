<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Abstract.php 1805 2012-04-19 03:24:44Z cutecube $
 */

/**
 * 图度扩展对象
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
abstract class Tudu_Tudu_Extension_Abstract
{
    /**
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     * @param array                  $params
     * @return array
     */
    public function onPrepare(Tudu_Tudu_Storage_Tudu &$tudu, array $params)
    {}

    public function postCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function preCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function preUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function postUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function onForward(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function onApply(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function onReview(Tudu_Tudu_Storage_Tudu &$tudu, $isAgree)
    {}

    public function onDivide(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function onDelete(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    public function onSend(Tudu_Tudu_Storage_Tudu &$tudu)
    {}

    /**
     *
     * @param $className
     * @return Oray_Dao_Abstract
     */
    public function getDao($className, $key = Tudu_Dao_Manager::DB_TS)
    {
        return Tudu_Dao_Manager::getDao($className, $key);
    }
}