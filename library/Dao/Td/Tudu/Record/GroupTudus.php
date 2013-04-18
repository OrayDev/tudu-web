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
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_GroupTudus extends Oray_Dao_Record
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
	 * 主题分类ID
	 * 
	 * @var string
	 */
	public $classId;
	
	/**
	 * 主题分类名称
	 * 
	 * @var string
	 */
	public $className;
	
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
	 * 抄送
	 * 
	 * @var array
	 */
	public $cc;
	
	/**
	 * 优先级
	 * 
	 * @var int
	 */
	public $priority;
	
	/**
	 * 隐私
	 * 
	 * @var int
	 */
	public $privacy;
	
	/**
	 * 内容
	 * 
	 * @var string
	 */
	public $content;
	
	/**
	 * 附件数量
	 * 
	 * @var string
	 */
	public $attachNum;
	
	/**
	 * 最后回复时间
	 * 
	 * @var int
	 */
	public $lastPostTime;
	
	/**
	 * 最后回复人
	 * 
	 * @var string
	 */
	public $lastPoster;
	
	/**
	 * 最后转发人
	 * 
	 * @var string
	 */
	public $lastForwarder;
	
	/**
	 * 最后转发时间
	 * 
	 * @var int
	 */
	public $lastForwardTime;
	
	/**
	 * 回复人信息
	 * 
	 * @var string
	 */
	public $posterInfo;
	
	/**
	 * 浏览次数
	 * 
	 * @var int
	 */
	public $viewNum;
	
	/**
	 * 回复数
	 * 
	 * @var int
	 */
	public $replyNum;
	
	/**
	 * 日志（进度）数
	 * 
	 * @var int
	 */
	public $logNum;
	
	/**
	 * 开始时间
	 * 
	 * @var int
	 */
	public $startTime;
	
	/**
	 * 结束时间
	 * 
	 * @var int
	 */
	public $endTime;
	
	/**
	 * 接受时间
	 * 
	 * @var int
	 */
	public $acceptTime;
	
	/**
	 * 创建时间
	 * 
	 * @var int
	 */
	public $createTime;
	
	/**
	 * 完成率
	 * 
	 * @var int
	 */
	public $percent;
	
	/**
	 * 图度状态
	 * 
	 * @var int
	 */
	public $status;
	
	/**
	 * 
	 * @var int
	 */
	public $special;
	
	/**
	 * 用户唯一ID
	 * 
	 * @var string
	 */
	public $uniqueId;
	
	/**
	 * 是否已读
	 * 
	 * @var boolean
	 */
	public $isRead;
	
	/**
	 * 是否转发
	 * 
	 * @var boolean
	 */
	public $isForward;
	
	/**
	 * 当前用户角色
	 * 
	 * @var boolean | null
	 */
	public $role;
	
	/**
	 * 当前用户跟进进度
	 * 
	 * @var int | null
	 */
	public $selfPercent;
	
	/**
	 * 当前用户图度接收状态
	 * 
	 * @var int | null
	 */
	public $selfTuduStatus;
	
	/**
	 * 当前用户图度接受时间
	 * 
	 * @var int | null
	 */
	public $selfAcceptTime;
	
	/**
	 * 是否已完结
	 * 
	 * @var boolean
	 */
	public $isDone;
	
	/**
	 * 是否已超期
	 * 
	 * @var boolean
	 */
	public $isExpired;
	
	/**
	 * 所有标签
	 * 
	 * @var array
	 */
	public $labels;
	
	/**
	 * 图度组节点类型
	 * 
	 * @var string
	 */
	public $nodeType;
	
	/**
	 * 是否图度组
	 * 
	 * @var string
	 */
	public $isTuduGroup;
	
	/**
	 * 所属图度组ID
	 * 
	 * @var string
	 */
	public $parentId;
	
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
		$this->classId      = $record['classid'];
		$this->className    = $record['classname'];
		$this->type         = $record['type'];
		$this->subject      = $record['subject'];
		$this->from         = Dao_Td_Tudu_Tudu::formatAddress($record['from'], true);
		$this->to           = Dao_Td_Tudu_Tudu::formatAddress($record['to']);
		$this->cc           = Dao_Td_Tudu_Tudu::formatAddress($record['cc']);
		$this->priority     = $this->_toInt($record['priority']);
		$this->privacy      = $this->_toInt($record['privacy']);
		$this->attachNum    = $this->_toInt($record['attachnum']);
		$this->lastPostTime = $this->_toTimestamp($record['lastposttime']);
		$this->lastPoster   = $record['lastposter'];
		$this->posterInfo   = $record['posterinfo'];
		$this->viewNum      = $record['viewnum'];
		$this->replyNum     = $this->_toInt($record['replynum']);
		$this->logNum       = $this->_toInt($record['lognum']);
		$this->startTime    = $this->_toTimestamp($record['starttime']);
		$this->endTime      = $this->_toTimestamp($record['endtime']);
		$this->acceptTime   = $this->_toTimestamp($record['accepttime']);
		$this->createTime   = $this->_toTimestamp($record['createtime']);
		$this->percent      = $this->_toInt($record['percent']);
		$this->status       = $this->_toInt($record['status']);
		$this->special      = $this->_toInt($record['special']);
		$this->uniqueId     = $record['uniqueid'];
		$this->isRead       = $this->_toBoolean($record['isread']);
		$this->isForward    = $this->_toBoolean($record['isforward']);
		$this->isDone       = $this->_toBoolean($record['isdone']);
		$this->labels       = $this->_toArray($record['labels']);
		
		$this->nodeType = isset($record['nodetype']) ? $record['nodetype'] : null;
		$this->parentId = isset($record['parentid']) ? $record['parentid'] : null;
		$this->isTuduGroup = in_array($this->nodeType, array(Dao_Td_Tudu_Group::TYPE_NODE, Dao_Td_Tudu_Group::TYPE_ROOT));
		
		$this->selfPercent    = isset($record['selfpercent']) ? $this->_toInt($record['selfpercent']) : null;
		$this->role           = !empty($record['role']) ? $record['role'] : null;
		$this->selfTuduStatus = isset($record['selftudustatus']) ? $this->_toInt($record['selftudustatus']) : null;
		$this->selfAcceptTime = isset($record['selfaccepttime']) ? $this->_toTimestamp($record['selfaccepttime']) : null;
		
		$this->isExpired = ($this->endTime
		                 && $this->status <= Dao_Td_Tudu_Tudu::STATUS_DOING
		                 && Oray_Function::dateDiff('d', $this->endTime, time()) > 0);
		                       
    	if (isset($this->from[3])) {
    	    $this->sender = $this->from[3];
    	}
    	
    	$this->accepter = !empty($this->to) ? array_keys($this->to) : array();
    	
    	
        if (isset($record['forwardinfo']) || isset($record['lastforward'])) {
            $forwardInfo = count($this->accepter) > 1 ? $record['forwardinfo'] : $record['lastforward'];
			$array = explode("\n", $forwardInfo);
			
			if (count($array) == 2) {
				$this->lastForwarder   = $array[0];
		        $this->lastForwardTime = $this->_toInt($array[1]);
			}
		}

        parent::__construct();
    }
}