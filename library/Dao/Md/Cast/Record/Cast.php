<?php
/**
 * Tudu
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Cast.php 62 2010-07-27 11:09:29Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Cast
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Cast_Record_Cast extends Oray_Dao_Record
{
    /**
     * 
     * @var string
     */
	const ID_DEFAULT = '^default';
	
    /**
     * 
     * @var string
     */
    public $orgId;
    
    /**
     * 
     * @var string
     */
    public $castId;
    
    /**
     * 
     * @var string
     */
    public $castName;
    
    /**
     * 
     * @var boolean
     */
    public $isDefault;
    
    /**
     * Construct
     * 
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId     = $record['orgid'];
        $this->castId    = $record['castid'];
        $this->castName  = $record['castname'];
        $this->isDefault = $record['isdefault'];
        
        parent::__construct();
    }
}