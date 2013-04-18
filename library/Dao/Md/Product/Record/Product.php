<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Md
 * @subpackage Product
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Product.php 34 2010-07-19 11:09:58Z cutecube $
 */

/**
 * @category   Dao
 * @package    Md
 * @subpackage Product
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Product_Record_Product extends Oray_Dao_Record
{
	
	/**
	 * 
	 * @var string
	 */
	public $productId;
	
	/**
	 * 
	 * @var string
	 */
	public $productName;
	
    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->productId   = $record['productid'];
        $this->productName = $record['productname'];
          
        parent::__construct();
    }
}