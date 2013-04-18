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
 * @version    $Id: Tudu.php 2809 2013-04-07 09:57:05Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Tudu extends Oray_Dao_Record
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
     * 发送流程ID
     *
     * @var string
     */
    public $appId;

    /**
     * 内容ID
     *
     * @var string
     */
    public $postId;

    /**
     * 周期ID
     *
     * @var string
     */
    public $cycleId;

    /**
     * 当前执行步骤ID
     *
     * @var string
     */
    public $stepId;

    /**
     * 主题分类ID
     *
     * @var string
     */
    public $classId;

    /**
     * 工作流ID
     *
     * @var string
     */
    public $flowId;

    /**
     * 前置任务ID
     *
     * @var string
     */
    public $prevTuduId;

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
     * 密送
     *
     * @var array
     */
    public $bcc;

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
     * 私密任务密码
     *
     * @var string
     */
    public $password;

    /**
     * 外部验证码
     *
     * @var string
     */
    public $authCode;

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
     *
     * @var int
     */
    public $stepNum;

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
     * 预计耗时
     *
     * @var int
     */
    public $totalTime;

    /**
     * 已耗时
     *
     * @var int
     */
    public $elapsedTime;

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
     * 分值（任务完成后打分）
     *
     * @var int
     */
    public $score;

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
     * 是否草稿
     *
     * @var boolean
     */
    public $isDraft;

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
     * 是否置顶
     *
     * @var boolean
     */
    public $isTop;

    /**
     *
     * @var int
     */
    public $mark;

    /**
     * 是否通知所有相关人员
     *
     * @var boolean
     */
    public $notifyAll;

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
     * 当前用户执行步骤
     *
     * @var int | null
     */
    public $selfStepStatus;

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
     * 是否已发送
     *
     * @var boolean
     */
    public $isSend;

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
     * 是否需要外部验证码
     *
     * @var boolean
     */
    public $isAuth;

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
     * 根图度组ID
     *
     * @var string
     */
    public $rootId;

    /**
     * 是否完成需确认
     *
     * @var boolean
     */
    public $needConfirm;

    /**
     * 周期任务循环序号
     *
     * @var int
     */
    public $cycleNum;

    /**
     * 标题是否显示开始日期
     *
     * @var int
     */
    public $displayDate;

    /**
     *
     * @var int
     */
    public $completeTime;

    /**
     *
     * @var int
     */
    public $stepType;

    /**
     * 图度模式（正常 或 认领）
     *
     * @var boolean
     */
    public $acceptMode;

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
        $this->postId       = $record['postid'];
        $this->cycleId      = $record['cycleid'];
        $this->stepId       = isset($record['stepid']) ? $record['stepid'] : null;
        $this->appId        = isset($record['appid']) ? $record['appid'] : null;
        $this->flowId       = isset($record['flowid']) ? $record['flowid'] : null;
        $this->classId      = $record['classid'];
        $this->prevTuduId   = !empty($record['prevtuduid']) ? $record['prevtuduid'] : null;
        $this->className    = $record['classname'];
        $this->type         = $record['type'];
        $this->subject      = $record['subject'];
        $this->from         = Dao_Td_Tudu_Tudu::formatAddress($record['from'], true);
        $this->to           = Dao_Td_Tudu_Tudu::formatAddress($record['to']);
        $this->cc           = Dao_Td_Tudu_Tudu::formatAddress($record['cc']);
        $this->bcc          = isset($record['bcc']) ? Dao_Td_Tudu_Tudu::formatAddress($record['bcc']) : null;
        $this->content      = $record['content'];
        $this->priority     = $this->_toInt($record['priority']);
        $this->privacy      = $this->_toInt($record['privacy']);
        $this->needConfirm  = isset($record['needconfirm']) ? $this->_toInt($record['needconfirm']) : null;
        $this->password     = $record['password'];
        $this->isAuth       = isset($record['isauth']) ? $this->_toBoolean($record['isauth']) : null;
        $this->authCode     = isset($record['authcode']) ? $record['authcode'] : null;
        $this->attachNum    = $this->_toInt($record['attachnum']);
        $this->lastPostTime = $this->_toTimestamp($record['lastposttime']);
        $this->lastPoster   = $record['lastposter'];
        $this->posterInfo   = $record['posterinfo'];
        $this->viewNum      = $record['viewnum'];
        $this->cycleNum     = isset($record['cyclenum']) ? $this->_toInt($record['cyclenum']) : null;
        $this->replyNum     = $this->_toInt($record['replynum']);
        $this->logNum       = $this->_toInt($record['lognum']);
        $this->stepNum      = isset($record['stepnum']) ? $this->_toInt($record['stepnum']) : null;
        $this->startTime    = $this->_toTimestamp($record['starttime']);
        $this->endTime      = $this->_toTimestamp($record['endtime']);
        $this->completeTime = isset($record['completetime']) ? $this->_toTimestamp($record['completetime']) : null;
        $this->totalTime    = $this->_toInt($record['totaltime']);
        $this->elapsedTime  = $this->_toInt($record['elapsedtime']);
        $this->acceptTime   = $this->_toTimestamp($record['accepttime']);
        $this->createTime   = $this->_toTimestamp($record['createtime']);
        $this->percent      = $this->_toInt($record['percent']);
        $this->score        = $this->_toInt($record['score']);
        $this->status       = $this->_toInt($record['status']);
        $this->special      = $this->_toInt($record['special']);
        $this->uniqueId     = $record['uniqueid'];
        $this->isRead       = $this->_toBoolean($record['isread']);
        $this->isDraft      = $this->_toBoolean($record['isdraft']);
        $this->isForward    = $this->_toBoolean($record['isforward']);
        $this->isTop        = $this->_toBoolean($record['istop']);
        $this->isDone       = $this->_toBoolean($record['isdone']);
        $this->mark         = isset($record['mark']) ? $this->_toInt($record['mark']) : null;
        $this->isSend       = isset($record['issend']) ? $this->_toBoolean($record['issend']) : null;
        $this->notifyAll    = isset($record['notifyall']) ? $this->_toBoolean($record['notifyall']) : null;
        $this->acceptMode   = isset($record['accepmode']) ? $this->_toBoolean($record['accepmode']) : null;
        $this->labels       = $this->_toArray($record['labels']);

        $this->nodeType = isset($record['nodetype']) ? $record['nodetype'] : null;
        $this->parentId = isset($record['parentid']) ? $record['parentid'] : null;
        $this->rootId   = isset($record['rootid']) ? $record['rootid'] : null;
        $this->isTuduGroup = in_array($this->nodeType, array(Dao_Td_Tudu_Group::TYPE_NODE, Dao_Td_Tudu_Group::TYPE_ROOT));

        $this->selfPercent    = isset($record['selfpercent']) ? $this->_toInt($record['selfpercent']) : null;
        $this->role           = !empty($record['role']) ? $record['role'] : null;
        $this->selfTuduStatus = isset($record['selftudustatus']) ? $this->_toInt($record['selftudustatus']) : null;
        $this->selfAcceptTime = isset($record['selfaccepttime']) ? $this->_toTimestamp($record['selfaccepttime']) : null;

        $this->selfStepStatus = isset($record['selfstepstatus']) ? $this->_toInt($record['selfstepstatus']) : null;
        $this->displayDate    = isset($record['displaydate']) ? $this->_toInt($record['displaydate']) : null;
        $this->stepType       = isset($record['steptype']) ? $this->_toInt($record['steptype']) : null;

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