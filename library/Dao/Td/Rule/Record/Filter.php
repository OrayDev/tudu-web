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
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Rule
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Rule_Record_Filter extends Oray_Dao_Record
{
    
    /**
     * 
     * @var string
     */
    public $filterId;
    
    /**
     * 
     * @var string
     */
    public $ruleId;
    
    /**
     * 
     * @var string
     */
    public $what;
    
    /**
     * 
     * @var string
     */
    public $type;
    
    /**
     * 
     * @var mixed
     */
    public $value;
    
    /**
     * 
     * @var string
     */
    public $valueString;
    
    /**
     * 
     * @var boolean
     */
    public $isValid;
    
    /**
     * Constructor
     * 
     * @param $record
     */
    public function __construct(array $record)
    {
        $this->filterId = $record['filterid'];
        $this->ruleId   = $record['ruleid'];
        $this->what     = $record['what'];
        $this->type     = $record['type'];
        
        $this->valueString = $record['value'];
        $this->value       = $this->_formatValue($record['value'], $this->what);
        $this->isValid     = $this->_toBoolean($record['isvalid']);
        
        parent::__construct();
    }
    
    /**
     * 
     * @param $value
     * @param $wlat
     * @return boolean
     */
    private function _formatValue($value, $what)
    {
        $ret = array();
        switch ($what) {
            case 'subject':
                $ret = $value;
                break;
            case 'to':
            case 'cc':
            case 'from':
                $array = explode("\n", $value);
                foreach ($array as $item) {
                    $item = explode(' ', $item);
                    $ret[] = $item[0];
                }
                break;
        }
        
        return $ret;
    }
}