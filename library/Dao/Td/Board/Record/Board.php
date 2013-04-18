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
 * @version    $Id: Board.php 1807 2012-04-19 07:32:10Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Board_Record_Board extends Oray_Dao_Record
{

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 版块ID
     *
     * @var string
     */
    public $boardId;

    /**
     * 版块类型
     *
     * @var string
     */
    public $type;

    /**
     * 上级版块ID
     *
     * @var string
     */
    public $parentId;

    /**
     * 版块名称
     *
     * @var string
     */
    public $boardName;

    /**
     * 所有者ID（负责人）
     *
     * @var string
     */
    public $ownerId;

    /**
     * 版块说明
     *
     * @var string
     */
    public $memo;

    /**
     * 版主
     *
     * 以用户ID形式保存
     *
     * @var array
     */
    public $moderators;

    /**
     * 参与的用户组
     *
     * 以用户组ID形式保存
     *
     * @var array
     */
    public $groups;

    /**
     * 状态
     *
     * @var int
     */
    public $status;

    /**
     *
     * @var boolean
     */
    public $privacy;

    /**
     *
     * @var boolean
     */
    public $isClassify;

    /**
     *
     * @var boolean
     */
    public $protect;

    /**
     *
     * @var int
     */
    public $orderNum;

    /**
     * 是否系统版块
     *
     * @var boolean
     */
    public $isSystem;

    /**
     * 是否已添加快捷
     *
     * @var boolean
     */
    public $isAttention;

    /**
     * 是否完成需确认
     *
     * @var boolean
     */
    public $needConfirm;

    /**
     * 仅用于工作流
     *
     * @var boolean
     */
    public $flowOnly;

    /**
     * 权重（仅当读取常用版块时存在）
     *
     * @var int
     */
    public $weight;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId      = $record['orgid'];
        $this->boardId    = $record['boardid'];
        $this->type       = $record['type'];
        $this->parentId   = $record['parentid'];
        $this->boardName  = $record['boardname'];
        $this->ownerId    = $record['ownerid'];
        $this->memo       = $record['memo'];
        $this->moderators = Dao_Td_Board_Board::formatModerator(trim($record['moderators']), ',');
        $this->groups     = Dao_Td_Board_Board::formatGroups($record['groups']);
        $this->status     = $this->_toInt($record['status']);
        $this->privacy    = $this->_toBoolean($record['privacy']);
        $this->protect    = $this->_toBoolean($record['protect']);
        $this->isClassify = $this->_toBoolean($record['isclassify']);
        $this->needConfirm= $this->_toBoolean($record['needconfirm']);
        $this->flowOnly   = $this->_toBoolean($record['flowonly']);
        $this->orderNum   = $this->_toInt($record['ordernum']);
        $this->isSystem   = ('system' == $record['type']);
        $this->weight     = isset($record['weight']) ? $this->_toInt($record['weight']) : null;

        $this->isAttention = array_key_exists('uniqueid', $record)
                             ? (boolean) $record['uniqueid']
                             : null;

        parent::__construct();
    }
}