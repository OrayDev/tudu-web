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
 * @version    $Id: TuduGroups.php 2121 2012-09-19 05:15:22Z web_op $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_TuduGroups extends Oray_Dao_Record
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
     * 图度ID
     *
     * @var string
     */
    public $tuduId;
    
    /**
     * 用户唯一ID
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 图度类型
     *
     * @var string
     */
    public $type;

    /**
     * 主题
     *
     * @var string
     */
    public $subject;

    /**
     * 发起人
     *
     * @var string
     */
    public $from;

    /**
     * 执行
     *
     * @var string
     */
    public $to;

    /**
     * 发起人
     *
     * @var string
     */
    public $sender;

    /**
     * 接受人
     *
     * @var string
     */
    public $accepter;

    /**
     * 图度组节点类型
     *
     * @var string
     */
    public $nodeType;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId        = $record['orgid'];
        $this->boardId      = $record['boardid'];
        $this->tuduId       = $record['tuduid'];
        $this->type         = $record['type'];
        $this->subject      = $record['subject'];
        $this->from         = Dao_Td_Tudu_Tudu::formatAddress($record['from'], true);
        $this->to           = Dao_Td_Tudu_Tudu::formatAddress($record['to']);
        $this->uniqueId     = $record['uniqueid'];
        $this->nodeType = isset($record['nodetype']) ? $record['nodetype'] : null;

        if (isset($this->from[3])) {
            $this->sender = $this->from[3];
        }

        $this->accepter = !empty($this->to) ? array_keys($this->to) : array();

        parent::__construct();
    }
}