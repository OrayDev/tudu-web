<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Access.php 29 2010-07-16 11:24:06Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao
 * @subpackage User
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Access_Record_Access extends Oray_Dao_Record
{
    
	/**
	 * 
	 * @var int
	 */
    public $accessId;
    
    /**
     * 
     * @var string
     */
    public $accessName;
    
    /**
     * 
     * @var string
     */
    public $valueType;
    
    /**
     * 
     * @var string
     */
    public $formType;
    
    /**
     * 
     * @var string
     */
    public $defaultValue;
    
    /**
     * 
     * @param array $record
     */
    public function __construct(array $record)
    {
    	$this->accessId     = $this->_toInt($record['accessid']);
    	$this->accessName   = $record['accessname'];
    	$this->valueType    = $record['valuetype'];
    	$this->formType     = $record['formtype'];
    	$this->defaultValue = $record['defaultvalue'];
    	
    	parent::__construct();
    }
}