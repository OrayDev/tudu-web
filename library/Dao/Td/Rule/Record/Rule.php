<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Rule
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Rule.php 2569 2012-12-27 10:19:17Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Rule
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Rule_Record_Rule extends Oray_Dao_Record
{
    /**
     *
     * @var string
     */
    public $ruleId;

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var string
     */
    public $operation;

    /**
     *
     * @var array
     */
    public $mailRemind;

    /**
     *
     * @var string
     */
    public $value;

    /**
     *
     * @var boolean
     */
    public $isValid;

    /**
     *
     * @var array
     */
    private $_filters;

    /**
     *
     * @var Dao_Td_Rule_Rule
     */
    private static $_daoRule;

    /**
     * Constructor
     *
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->ruleId      = $record['ruleid'];
        $this->uniqueId    = $record['uniqueid'];
        $this->description = $record['description'];
        $this->operation   = $record['operation'];
        $this->mailRemind  = isset($record['mailremind']) ? json_decode($record['mailremind'], true) : null;
        $this->value       = $record['value'];
        $this->isValid     = $this->_toBoolean($record['isvalid']);

        parent::__construct();
    }

    /**
     *
     * @param $dao
     */
    public static function setDao(Dao_Td_Rule_Rule $dao)
    {
        self::$_daoRule = $dao;
    }

    /**
     *
     * @return Oray_Dao_Recordset
     */
    public function getFilters()
    {
        if (null === self::$_daoRule) {
            return null;
        }

        if (null === $this->_filters) {
            $this->_filters = self::$_daoRule->getFiltersByRuleId($this->ruleId, array('isvalid' => 1));
        }

        return $this->_filters;
    }
}