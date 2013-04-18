<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Template.php 774 2011-05-10 11:18:13Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Template extends Oray_Dao_Record
{
    /**
     * 
     * @var string
     */
    public $orgId;
    
    /**
     * 板块ID
     * 
     * @var string
     */
    public $boardId;
    
    /**
     * 模板ID
     * 
     * @var string
     */
    public $templateId;
    
    /**
     * 创建人uniqueId
     * 
     * @var string
     */
    public $creator;
    
    /**
     * 
     * @var string
     */
    public $name;
    
    /**
     * 
     * @var string
     */
    public $content;
    
    
    /**
     * 
     * @var int
     */
    public $orderNum;
    
    
    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->boardId    = $record['boardid'];
        $this->templateId = $record['templateid'];
        $this->creator    = $record['creator'];
        $this->name       = $record['name'];
        $this->content    = isset($record['content']) ? $record['content'] : null;
        $this->orderNum   = $this->_toInt($record['ordernum']);

        parent::__construct();
    }
}