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
 * @link       http://www.tudu.com/
 * @author     Oray-Yongfa
 * @version    $Id: Flow.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Flow_Record_Flow extends Oray_Dao_Record
{
    /**
     * 工作流ID
     *
     * @var string
     */
    public $flowId;

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     *
     * @var string
     */
    public $classId;

    /**
     * 板块ID
     *
     * @var string
     */
    public $boardId;

    /**
     * 板块父类ID
     *
     * @var string
     */
    public $parentId;

    /**
     * 用户唯一Id
     *
     * @var string
     */
    public $uniqueId;

    /**
     * 标题
     *
     * @var string
     */

    public $subject;

    /**
     * 描述
     *
     * @var string
     */
    public $description;

    /**
     * 可用人群
     *
     * @var string
     */
    public $avaliable;

    /**
     * 抄送
     *
     * @var string
     */
    public $cc;

    /**
     * 是否可用
     *
     * @var boolean
     */
    public $isValid;

    /**
     * int
     *
     * @var 所需时间
     */
    public $elapsedTime;

    /**
     * 内容模板
     *
     * @var string
     */
    public $content;

    /**
     * 流程
     *
     * @var string
     */
    public $steps;

    /**
     * 创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     * 常用权值
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
        $this->orgId       = $record['orgid'];
        $this->flowId      = $record['flowid'];
        $this->boardId     = $record['boardid'];
        $this->classId     = isset($record['classid']) ? $record['classid'] : null;
        $this->parentId    = isset($record['parentid']) ? $record['parentid'] : null;
        $this->uniqueId    = $record['uniqueid'];
        $this->subject     = $record['subject'];
        $this->description = $record['description'];
        $this->isValid     = $record['isvalid'];
        $this->avaliable   = isset($record['avaliable']) ? Dao_Td_Flow_Flow::formatAvaliable($record['avaliable']) : null;
        $this->cc          = isset($record['cc']) ? Dao_Td_Flow_Flow::formatAddress($record['cc']) : null;
        $this->content     = isset($record['content']) ? $record['content'] : null;
        $this->steps       = isset($record['steps']) ? json_decode($record['steps'], true) : null;
        $this->elapsedTime = isset($record['elapsedtime']) ? $this->_toInt($record['elapsedtime']) : null;
        $this->createTime  = $this->_toTimestamp($record['createtime']);
        $this->weight      = isset($record['weight']) ? (int) $record['weight'] : null;

        parent::__construct();
    }
}