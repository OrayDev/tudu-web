<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Deliver
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Deliver.php 1171 2011-10-08 01:50:09Z cutecube $
 */

/**
 * @category   Tudu
 * @package    Tudu_Deliver
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Deliver
{
	private $_db;

	/**
	 *
	 * @var Dao_Td_Tudu_Tudu
	 */
	private $_tuduDao;

	/**
	 *
	 * @var Dao_Td_Tudu_Post
	 */
	private $_postDao;

	/**
	 *
	 * @var Dao_Td_Tudu_Label
	 */
	private $_labelDao;

	/**
	 *
	 * @var Dao_Td_Attachment_File
	 */
	private $_fileDao;

	/**
	 *
	 * @var string
	 */
	private $_tuduId;

	/**
	 *
	 * @var string
	 */
	private $_message;

	public function __construct(Zend_Db_Adapter_Abstract $db)
	{
		$this->_db = $db;
		$this->_tuduDao = Oray_Dao::factory('Dao_Td_Tudu_Tudu', $this->_db);
		$this->_postDao = Oray_Dao::factory('Dao_Td_Tudu_Post', $this->_db);
		$this->_labelDao = Oray_Dao::factory('Dao_Td_Tudu_Label', $this->_db);
		$this->_fileDao = Oray_Dao::factory('Dao_Td_Attachment_File', $this->_db);
	}

	/**
	 *
	 * @param string $tuduId
	 * @return Dao_Td_Tudu_Record_Tudu
	 */
	public function getTuduById($tuduId, $uniqueId)
	{
		return $this->_tuduDao->getTuduById($uniqueId, $tuduId);
	}

	/**
	 *
	 * @param $tuduIds
	 * @return Oray_Dao_Recordset
	 */
	public function getTudusByIds(array $tuduIds)
	{
	    return $this->getTudus(array('tuduids' => $tuduIds));
	}

	/**
	 *
	 * @param array $condition
	 * @param array $filter
	 * @param mixed $sort
	 * @param int   $maxCount
	 */
	public function getTudus(array $condition, $filter = null, $sort = null, $maxCount = null)
	{
	    return $this->_tuduDao->getTudus($condition, $filter, $sort, $maxCount);
	}

	/**
	 * 获取tudu相关人员
	 *
	 * @param string $tuduId
	 * @return array
	 */
	public function getTuduUsers($tuduId, $filter = null)
	{
		return $this->_tuduDao->getUsers($tuduId, $filter);
	}

	/**
	 * 获取图度接受人
	 *
	 * @param string $tuduId
	 * @return array
	 */
	public function getTuduAccepters($tuduId)
	{
	    return $this->_tuduDao->getAccepters($tuduId);
	}

	/**
	 *
	 * @param string $tuduId
	 * @param array  $uniqueIds
	 * @return boolean
	 */
	public function removeTuduAccepter($tuduId, array $uniqueIds)
	{
	    foreach ($uniqueIds as $uniqueId) {
	        $this->_tuduDao->removeAccepter($tuduId, $uniqueId);

	        $this->_tuduDao->deleteLabel($tuduId, $uniqueId, '^a');
	    }

	    return true;
	}

	/**
	 *
	 * @param string $tuduId
	 * @param array  $uniqueIds
	 * @return boolean
	 */
	public function removeMeetingAttendee($tuduId, array $uniqueIds)
	{
	    foreach ($uniqueIds as $uniqueId) {
	        $this->_tuduDao->removeAccepter($tuduId, $uniqueId);

	        $this->_tuduDao->deleteLabel($tuduId, $uniqueId, '^i');

	        $this->_tuduDao->deleteLabel($tuduId, $uniqueId, '^a');
	    }

	    return true;
	}

	/**
	 * 创建图度
	 *
	 * @param array $params
	 * @return string|false
	 */
	public function createTudu(array $params)
	{
		if (empty($params['orgid'])
			|| empty($params['boardid'])
			|| empty($params['type'])
			|| empty($params['from'])
			|| empty($params['poster'])
			|| empty($params['uniqueid'])
			|| empty($params['email'])
			|| !isset($params['subject'])
			|| !isset($params['content'])) {
			$this->_message = 'missing params';
			return false;
		}

		$attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
            	$attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
            	);
            }
            unset($params['attachment']);
        }

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

        if (!empty($params['vote'])) {
        	$params['special'] = Dao_Td_Tudu_Tudu::SPECIAL_VOTE;
        }

		$tuduId = isset($params['tuduid']) ? $params['tuduid'] : $this->_getTuduId();
		$postId = $this->_getPostId($tuduId);

		$params['tuduid']  = $tuduId;
		$params['postid']  = $postId;
		$params['isfirst'] = true;

		// 处理周期任务
		if (!empty($params['cycle'])) {
			$params['special'] = Dao_Td_Tudu_Tudu::SPECIAL_CYCLE;

			$daoCycle = new Dao_Td_Tudu_Cycle($this->_db);
			$cycleId = Dao_Td_Tudu_Cycle::getCycleId();
			$params['cycle']['cycleid'] = $cycleId;

			if (!$daoCycle->createCycle($params['cycle'])) {
				$this->_message = 'create cycle fail';
                return false;
			}

			$params['cycleid'] = $cycleId;
		}

		// 会议
        if (!empty($params['meeting'])) {
            $params['meeting']['tuduid'] = $tuduId;
            $params['meeting']['orgid']  = $params['orgid'];
            $this->updateMeeting($params['meeting']);
        }

		if (!$this->_tuduDao->createTudu($params)) {
			$this->_message = 'create tudu fail';
			return false;
		}

		unset($params['percent']);
		if (!$this->_postDao->createPost($params)) {
			$this->_tuduDao->deleteTudu($tuduId);
			$this->_message = 'create post fail';
			return false;
		}

		if (!empty($params['vote'])) {
			$params['vote']['tuduid'] = $tuduId;
            $this->updateVote($params['vote']);
        }

		if (!empty($attachment)) {
		    $this->addAttachment($tuduId, $postId, $attachment);
		}

		return $tuduId;
	}

	/**
	 * 更新图度
	 *
	 * @param string $tuduId
	 * @param array $params
	 * @return boolean
	 */
	public function updateTudu($tuduId, array $params)
	{
        if (!empty($params['vote'])) {
            $params['special'] = Dao_Td_Tudu_Tudu::SPECIAL_VOTE;
            $params['vote']['tuduid'] = $tuduId;
        }

        // 处理周期任务
        if (!empty($params['cycle'])) {
            $params['special'] = Dao_Td_Tudu_Tudu::SPECIAL_CYCLE;

            $daoCycle = new Dao_Td_Tudu_Cycle($this->_db);
            $cycleId = isset($params['cycle']['cycleid']) ? $params['cycle']['cycleid'] : null;

            if (!$cycleId) {
            	$cycleId = Dao_Td_Tudu_Cycle::getCycleId();
                $params['cycle']['cycleid'] = $cycleId;

            	if (!$daoCycle->createCycle($params['cycle'])) {
            		return false;
            	}
            } else {
            	$daoCycle->updateCycle($cycleId, $params['cycle']);
            }

            $params['cycleid'] = $cycleId;
        }

        if (!empty($params['meeting'])) {
            $params['meeting']['tuduid'] = $tuduId;
            $this->updateMeeting($params['meeting']);
        }

		if (!$this->_tuduDao->updateTudu($tuduId, $params)) {
			return false;
		}

        if (!empty($params['vote'])) {
            $params['vote']['tuduid'] = $tuduId;
            $this->updateVote($params['vote']);
        }

		return true;
	}

	/**
	 * 删除图度
	 *
     * @param string $tuduId
     * @return boolean
	 */
    public function deleteTudu($tuduId)
    {
        return $this->_tuduDao->deleteTudu($tuduId);
    }

    /**
     * 完成图度
     *
     * @param string  $tuduId
     * @param boolean $isDone
     * @param int	  $score
     * @param boolean $isChild 是否图度组里的图度
     * @return boolean
     */
    public function doneTudu($tuduId, $isDone, $score, $isChild = false, $type = 'task')
    {
    	$ret = $this->_tuduDao->updateTudu($tuduId, array('isdone' => $isDone, 'score' => $score));

    	if (!$ret) {
    		$this->_message = 'update tudu failure';
    		return false;
    	}

    	$users = $this->_tuduDao->getUsers($tuduId);

    	// 移除图度箱标签，添加已完成
    	foreach ($users as $user) {
    	   if ($isDone) {
                $this->_tuduDao->deleteLabel($tuduId, $user['uniqueid'], '^i');
                $this->_tuduDao->addLabel($tuduId, $user['uniqueid'], '^o');
                $this->_tuduDao->deleteLabel($tuduId, $user['uniqueid'], '^a');
                $this->_tuduDao->deleteLabel($tuduId, $user['uniqueid'], '^e');
            } else {
                if (!($isChild && $user['role'] == Dao_Td_Tudu_Tudu::ROLE_SENDER)) {
                    $this->_tuduDao->addLabel($tuduId, $user['uniqueid'], '^i');
                }
                if ($user['role'] == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER && $type == 'task') {
                    $this->_tuduDao->addLabel($tuduId, $user['uniqueid'], '^a');
                }
                $this->_tuduDao->deleteLabel($tuduId, $user['uniqueid'], '^o');
            }
    	}

    	return true;
    }

    /**
     * 关闭图度（同done，但不添加已完成标签）
     *
     * @param int $tuduId
     * @param boolean $isClose
     * @return boolean
     */
    public function closeTudu($tuduId, $isClose)
    {
    	$params = array('isdone' => $isClose);

    	if (!$isClose) {
    		$params['lastposttime'] = time();
    	}

        $ret = $this->_tuduDao->updateTudu($tuduId, $params);

        if (!$ret) {
            $this->_message = 'update tudu failure';
            return false;
        }

        $users = $this->_tuduDao->getUsers($tuduId);

        // 移除图度箱标签，添加已完成
        $func = $isClose ? 'deleteLabel' : 'addLabel';
        foreach ($users as $user) {
            $this->_tuduDao->{$func}($tuduId, $user['uniqueid'], '^i');
        }

        return true;
    }

    /**
     * 创建投票
     *
     * @param $params
     * @return boolean
     */
    public function updateVote(array $params)
    {
    	if (empty($params['tuduid'])
    	    || (empty($params['newoptions']) && empty($params['options']))) {
    		return false;
    	}

    	$daoVote = Oray_Dao::factory('Dao_Td_Tudu_Vote', $this->_db);
    	$tuduId  = $params['tuduid'];
    	$vote    = array(
            'tuduid' => $tuduId
    	);

    	if (isset($params['maxchoices']) && is_int($params['maxchoices'])) {
    		$vote['maxchoices'] = $params['maxchoices'];
    	}
        if (isset($params['privacy'])) {
            $vote['privacy'] = $params['privacy'] ? 1 : 0;
        }
        if (isset($params['visible'])) {
            $vote['visible'] = $params['visible'] ? 1 : 0;
        }
        if (isset($params['expiretime']) && is_int($params['expiretime'])) {
            $vote['expiretime'] = $params['expiretime'];
        }

        if ($daoVote->existsVote($tuduId)) {
        	$daoVote->updateVote($tuduId, $vote);
        	$ret = true;
        } else {
            $ret = $daoVote->createVote($vote);
        }

        if (!$ret) {
        	return false;
        }

        if (!empty($params['newoptions'])) {
	        foreach ($params['newoptions'] as $option) {
	        	$option['tuduid']   = $tuduId;
	        	//$option['optionid'] = $daoVote->getOptionId();
	        	$daoVote->createOption($option);
	        }
        }

        if (!empty($params['options'])) {
	        foreach ($params['options'] as $option) {
	            $daoVote->updateOption($tuduId, $option['optionid'], $option);
	        }
        }

        return true;
    }

    /**
     * 更新会议信息
     * @param $params
     */
    public function updateMeeting(array $params)
    {
        if (empty($params['tuduid'])) {
            return false;
        }

        $daoMeeting = Oray_Dao::factory('Dao_Td_Tudu_Meeting', $this->_db);

        if ($daoMeeting->existsMeeting($params['tuduid'])) {
            return $daoMeeting->updateMeeting($params['tuduid'], $params);
        } else {
            return $daoMeeting->createMeeting($params);
        }
    }

	/**
	 * 获取回复信息
	 *
	 * @param string $tuduId
	 * @param string $postId
	 * @return Dao_Td_Tudu_Record_Post
	 */
	public function getPostById($tuduId, $postId)
	{
	    return $this->_postDao->getPost(array('tuduid' => $tuduId, 'postid' => $postId));
	}

	/**
	 * 创建回复
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function createPost(array $params)
	{
        if (empty($params['orgid'])
            || empty($params['boardid'])
        	|| empty($params['tuduid'])
        	|| empty($params['uniqueid'])) {
        	return false;
        }

        $attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
                );
            }
            unset($params['attachment']);
        }

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

        $tuduId = $params['tuduid'];
        $postId = $this->_getPostId($params['tuduid']);

        $params['postid']  = $postId;
        $params['isfirst'] = false;

		if (!$this->_postDao->createPost($params)) {
		    return false;
		}

		if (!empty($attachment)) {
		    $this->addAttachment($tuduId, $postId, $attachment);
		}

		return $postId;
	}

	/**
	 * 更新回复
	 *
	 * @param string $tuduId
	 * @param string $postId
	 * @param array $params
	 * @return boolean
	 */
	public function updatePost($tuduId, $postId, array $params)
	{
	    // 判断是否更新附件信息
        $attachment = array();
        if (!empty($params['attachment']) && is_array($params['attachment'])) {
            foreach ($params['attachment'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => true
                );
            }
            unset($params['attachment']);
        }

        if (!empty($params['file']) && is_array($params['file'])) {
            foreach ($params['file'] as $item) {
                $attachment[] = array(
                    'fileid'   => $item,
                    'isattach' => false
                );
            }
            unset($params['file']);
        }

		if (!$this->_postDao->updatePost($tuduId, $postId, $params)) {
		    return false;
		}

		if (isset($attachment)) {
		    $this->deleteAttachment($tuduId, $postId);
		    $this->addAttachment($tuduId, $postId, $attachment);
		}

		return true;
	}

	/**
	 * 删除回复信息
	 *
	 * @param string $tuduId
	 * @param string $postId
	 * @return boolean
	 */
	public function deletePost($tuduId, $postId)
	{
	    return $this->_postDao->deletePost($tuduId, $postId);
	}

	/**
	 * 发送图度
	 *
	 * @param string $tuduId
	 * @param array $recipients
	 * @return boolean
	 */
	public function sendTudu($tuduId, array $recipients, $params = null)
	{
		foreach ($recipients as $uniqueId => $recipient) {

			$ret = $this->addRecipient($tuduId, $uniqueId, $params);
			if (false !== $ret) {

			    if (empty($recipient['isforeign'])) {
    				// 投递到图度箱
    				$this->addLabel($tuduId, $uniqueId, '^all');

    				if (!$ret || !in_array('^g', explode(',', $ret))) {
    				    $this->addLabel($tuduId, $uniqueId, '^i');
    				}

    				// 分配相关标签
    				// 公告
    				if (isset($params['notice']) && $params['notice']) {
    					$this->addLabel($tuduId, $uniqueId, '^n');

    				// 讨论
    				} elseif (isset($params['discuss']) && $params['discuss']) {
    					$this->addLabel($tuduId, $uniqueId, '^d');

                    // 会议
    				} elseif (isset($params['meeting']) && $params['meeting']) {
    				    $this->addLabel($tuduId, $uniqueId, '^m');

    				// 我执行
    				} elseif (isset($recipient['role']) && $recipient['role'] == Dao_Td_Tudu_Tudu::ROLE_ACCEPTER && empty($params['meeting'])) {
    				    $this->addLabel($tuduId, $uniqueId, '^a');
    				}
			    }

				if (is_array($recipient)) {
				    $this->_tuduDao->updateTuduUser($tuduId, $uniqueId, $recipient);
				}
			}
		}

		// 发送图度 - 取消草稿标识，更新版块统计等
		$this->_tuduDao->sendTudu($tuduId);

		return true;
	}

	/**
	 * 增加收图人
	 *
	 * 增加关联用户并投递到 ^all 标签，图度投递到用户的图度箱时，仅增加 ^all 标签
	 * 执行发送时才根据策略附加其它的标签或丢弃
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @param array $params
	 * @return boolean
	 */
	public function addRecipient($tuduId, $uniqueId, $params = null)
	{
		return $this->addUser($tuduId, $uniqueId, $params);
	}

	/**
	 * 增加关联用户
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @param array $params
	 */
	public function addUser($tuduId, $uniqueId, $params)
	{
		return $this->_tuduDao->addUser($tuduId, $uniqueId, $params);
	}

	/**
	 * 获取用户
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @return array
	 */
	public function getUser($tuduId, $uniqueId)
	{
	    return $this->_tuduDao->getUser($tuduId, $uniqueId);
	}

	/**
	 * 删除用户
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @return array
	 */
	public function deleteUser($tuduId, $uniqueId)
	{
	    return $this->_tuduDao->deleteUser($tuduId, $uniqueId);
	}

	/**
	 * 增加关联标签
	 *
	 * @param $tuduId
	 * @param $uniqueId
	 * @param $labelId
	 * @return boolean
	 */
	public function addLabel($tuduId, $uniqueId, $labelId)
	{
		return $this->_tuduDao->addLabel($tuduId, $uniqueId, $labelId);
	}

	/**
	 * 增加附件
	 *
	 * @param string $tuduId
	 * @param string $postId
	 * @param array $attachment
	 * @return boolean
	 */
	public function addAttachment($tuduId, $postId, array $attachment)
	{
	    $attachnum = 0;
        foreach ($attachment as $attach) {
            if (false !== $this->_fileDao->addPost($tuduId, $postId, $attach['fileid'], (boolean) $attach['isattach'])) {
                $attachnum ++;
            }
        }
        return $this->_postDao->updateAttachNum($tuduId, $postId);
	}

	/**
	 * 删除关联附件
	 *
	 * @param string $tuduId
	 * @param string $postId
	 * @return boolean
	 */
	public function deleteAttachment($tuduId, $postId)
	{
	    return $this->_fileDao->deletePost($tuduId, $postId);
	}

    /**
     * 删除图度标签
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labelId
     * @return boolean
     */
    public function deleteLabel($tuduId, $uniqueId, $labelId)
	{
		return $this->_tuduDao->deleteLabel($tuduId, $uniqueId, $labelId);
	}

    /**
     * 更新图度的标签标记
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @param string $labels
     * @return boolean
     */
    public function updateTuduLabels($tuduId, $uniqueId, $labels = null)
	{
		return $this->_tuduDao->updateTuduLabels($tuduId, $uniqueId, $labels);
	}

	/**
	 * 更新任务进度
	 *
	 * @param string $tuduId
	 * @param int $percent
	 * @return int
	 */
	public function updateProgress($tuduId, $uniqueId, $percent)
	{
	    /*
	    if (100 === $percent) {
	        $status = Dao_Td_Tudu_Tudu::STATUS_DONE;
	    } elseif ($percent > 0) {
	        $status = Dao_Td_Tudu_Tudu::STATUS_DOING;
	    } else {
	        $status = Dao_Td_Tudu_Tudu::STATUS_UNSTART;
	    }

        $this->_tuduDao->updateTudu($tuduId, array(
            'percent' => $percent,
            'status' => $status
            ));
        */

	    $percent = $this->_tuduDao->updateProgress($tuduId, $uniqueId, $percent);

	    $this->_tuduDao->calcElapsedTime($tuduId);

        return $percent;
	}

	/**
	 * 计算父级图度进度
	 *
	 * @param string $tuduId
	 */
	public function calParentsProgress($tuduId)
	{
	    return $this->_tuduDao->calParentsProgress($tuduId);
	}

	/**
	 * 设置所有关联用户为未读状态
	 *
	 * @param string $tuduId
	 * @return boolean
	 */
	public function markAllUnread($tuduId)
	{
	    return $this->_tuduDao->markAllUnRead($tuduId);
	}

	/**
	 * 设置图度关联用户已读状态
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @return boolean
	 */
	public function markRead($tuduId, $uniqueId, $isRead = true)
	{
		return $this->_tuduDao->markRead($tuduId, $uniqueId, $isRead);
	}

	/**
	 * 标记标签内所有图度为已读
	 *
	 * @param string  $labelId
	 * @param string  $uniqueId
	 * @param boolean $isRead
	 * @return boolean
	 */
	public function markLabelRead($labelId, $uniqueId, $isRead = true)
	{
	    return $this->_tuduDao->markLabelRead($labelId, $uniqueId, $isRead);
	}

    /**
     * 标志为转发状态
     *
     * @param string $tuduId
     * @param string $uniqueId
     */
    public function forwardTudu($tuduId, $uniqueId)
    {
    	return $this->_tuduDao->markForward($tuduId, $uniqueId);
    }

	/**
	 * 取消草稿标识
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @return boolean
	 */
	public function cancelDraft($tuduId, $uniqueId)
	{
		$this->deleteLabel($tuduId, $uniqueId, '^r');
		return true;
	}

	/**
	 *
	 * @param string $tuduId
	 * @param string $uniqueId
	 * @return boolean
	 */
	public function acceptTudu($tuduId, $uniqueId, $percent = null)
	{
	    $params = array(
            'accepttime' => time(),
            'tudustatus' => Dao_Td_Tudu_Tudu::STATUS_DOING
        );

        if (is_int($percent)) {
            $params['percent'] = $percent;
        }

        $ret = $this->_tuduDao->updateTuduUser($tuduId, $uniqueId, $params);

        if ($ret) {
            $this->updateLastAcceptTime($tuduId);
        }

        return $ret;
	}

	/**
	 * 更新最后接受时间(更新非接收人看到的图度接受状态)
	 *
	 * @param string $tuduId
	 */
	public function updateLastAcceptTime($tuduId)
	{
	    $this->_tuduDao->updateLastAcceptTime($tuduId);
	}


	public function discardDraft($tuduIds, $uniqueId)
	{
	    $tuduUsers = $this->_tuduDao->getTuduUsers($tuduIds, $uniqueId);
	}

	/**
	 * 获取图度ID
	 *
	 * @return string
	 */
	private function _getTuduId()
	{
		return $this->_tuduDao->getTuduId();
	}

	/**
	 * 获取回复ID
	 *
	 * @param string $tuduId
	 * @return string
	 */
	private function _getPostId($tuduId)
	{
		return $this->_postDao->getPostId($tuduId);
	}

	/**
	 * 格式化地址
	 *
	 * @param string $address
	 */
	private function _formatAddress($address)
	{
		if (!is_array($address) && count($address) >= 1) {
			$address = $address[0] . ' ' . $address[1];
		}
		return $address;
	}

	/**
	 * 获取出错的消息
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->_message;
	}
}