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
 * @version    $Id: Tudu.php 2070 2012-08-22 09:37:26Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Abstract
 */
require_once 'Model/Tudu/Extension/Abstract.php';

/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Group extends Model_Tudu_Extension_Abstract
{
    /**
     *
     * @var string
     */
    protected $_handlerClass = 'Model_Tudu_Extension_Handler_Group';

    /**
     *
     * @var array
     */
    protected $_children = array();

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function appendChild(Model_Tudu_Tudu &$tudu)
    {
        $this->_children[] = $tudu;

        return $this;
    }

    /**
     *
     * @return array:
     */
    public function & getChildren()
    {
        return $this->_children;
    }

    /**
     *
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->_handlerClass;
    }
}
