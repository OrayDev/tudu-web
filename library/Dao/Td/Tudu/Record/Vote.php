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
 * @version    $Id: Vote.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Record_Vote extends Oray_Dao_Record
{
    /**
     *
     * @var Dao_Td_Tudu_Vote
     */
    private static $_daoVote;

    /**
     *
     * @var string
     */
    public $tuduId;

    /**
     *
     * @var string
     */
    public $voteId;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var int
     */
    public $maxChoices;

    /**
     *
     * @var int
     */
    public $voteCount;

    /**
     * 私密（不记名）
     *
     * @var boolean
     */
    public $privacy;

    /**
     * 结果是否可见
     *
     * @var boolean
     */
    public $visible;

    /**
     * 是否匿名设置（发起人可见投票参与人）
     *
     * @var boolean
     */
    public $anonymous;

    /**
     * 更新时是否重置(清0)
     *
     * @var boolean
     */
    public $isReset;

    /**
     *
     * @var int
     */
    public $expireTime;

    /**
     *
     * @var int
     */
    public $optionCount;

    /**
     * 
     * @var string
     */
    public $optionId;

    /**
     *
     * @var string
     */
    public $text;

    /**
     *
     * @var int
     */
    public $optionOrder;

    /**
     *
     * @var int
     */
    public $voteOrder;

    /**
     *
     * @var array
     */
    public $options;

    /**
     *
     * @var int
     */
    public $countVoter;

    /**
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->tuduId      = $record['tuduid'];
        $this->voteId      = $record['voteid'];
        $this->title       = $record['title'];
        $this->optionId    = isset($record['optionid']) ? $record['optionid'] : null;
        $this->text        = isset($record['text']) ? $record['text'] : null;
        $this->optionOrder = isset($record['optionorder']) ? $this->_toInt($record['optionorder']) : null;
        $this->maxChoices  = $this->_toInt($record['maxchoices']);
        $this->voteCount   = $this->_toInt($record['votecount']);
        $this->optionCount = isset($record['optioncount']) ? $this->_toInt($record['optioncount']) : null;
        $this->privacy     = $this->_toBoolean($record['privacy']);
        $this->visible     = $this->_toBoolean($record['visible']);
        $this->anonymous   = $this->_toBoolean($record['anonymous']);
        $this->isReset     = $this->_toBoolean($record['isreset']);
        $this->voteOrder   = isset($record['voteorder']) ? $this->_toInt($record['voteorder']) : null;
        $this->expireTime  = $this->_toTimestamp($record['expiretime']);

        parent::__construct();
    }

    /**
     *
     * @return void
     */
    public function getOptions() {
        if (!$this->tuduId || !$this->voteId) {
            return null;
        }

        $records = $this->getDao()->getOptions(array('tuduid' => $this->tuduId, 'voteid' => $this->voteId), null, 'ordernum ASC');

        $this->options = $records->toArray();
    }

    /**
     *
     * @return Int
     */
    public function countVoter() {
        if (!$this->tuduId || !$this->voteId) {
            return null;
        }

        $this->countVoter = $this->getDao()->countVoter($this->tuduId, $this->voteId);
    }

    /**
     *
     * @param Dao_Td_Tudu_Vote $dao
     */
    public static function setDao(Dao_Td_Tudu_Vote $dao)
    {
        self::$_daoVote = $dao;
    }

    /**
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Dao_Td_Tudu_Vote
     */
    public function getDao($db = null)
    {
        if (self::$_daoVote == null) {
            self::$_daoVote = Oray_Dao::factory('Dao_Td_Tudu_Vote', $db);
        }

        return self::$_daoVote;
    }
}