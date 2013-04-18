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
 * @version    $Id: Label.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Label extends Oray_Dao_Record
{
    /**
     * 用户唯一ID
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 标签ID
     *
     * @var string
     */
    public $labelId;

    /**
     * 标签别名
     *
     * @var string
     */
    public $labelAlias;

    /**
     * 是否系统标签
     *
     * @var boolean
     */
    public $isSystem;

    /**
     * 标签显示
     *
     * @var int
     */
    public $isShow;

    /**
     * 标签字条颜色
     *
     * @var string
     */
    public $color;

    /**
     * 背景颜色
     *
     * @var string
     */
    public $bgcolor;

    /**
     * 图度总数
     *
     * @var int
     */
    public $totalNum;

    /**
     * 未读数
     *
     * @var int
     */
    public $unreadNum;

    /**
     *
     * @var int
     */
    public $display;

    /**
     * 同步时间
     *
     * @var int
     */
    public $syncTime;

    /**
     * 排序索引
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
        $this->uniqueId   = $record['uniqueid'];
        $this->labelId    = $record['labelid'];
        $this->labelAlias = $record['labelalias'];
        $this->isSystem   = $this->_toBoolean($record['issystem']);
        $this->isShow     = $this->_toInt($record['isshow']);
        $this->unreadNum  = $this->_toInt($record['unreadnum']);
        $this->totalNum   = $this->_toInt($record['totalnum']);
        $this->color      = $record['color'];
        $this->bgcolor    = $record['bgcolor'];
        $this->display    = isset($record['display']) ? $this->_toInt($record['display']) : null;
        $this->syncTime   = $this->_toTimestamp($record['synctime']);
        $this->orderNum   = $this->_toInt($record['ordernum']);

        parent::__construct();
    }
}