<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Vote.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * @see Dao_Td_Tudu_Record_Vote
 */
require_once 'Dao/Td/Tudu/Record/Vote.php';

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Vote extends Oray_Dao_Abstract
{
    /**
     *
     */
    public function init()
    {
        Dao_Td_Tudu_Record_Vote::setDao($this);
    }

    /**
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Tudu_Record_Vote
     */
    public function getVote(array $condition, $filter = null)
    {
        $table   = 'td_vote';
        $columns = 'tudu_id AS tuduid, vote_id AS voteid, title, max_choices AS maxchoices, vote_count as votecount, privacy, '
                 . 'visible, anonymous, is_reset AS isreset, expire_time AS expiretime';
        $where   = array();
        $bind    = array();

        if (!empty($condition['tuduid'])) {
            $where[] = 'tudu_id = :tuduid';
            $bind['tuduid'] = $condition['tuduid'];
        }

        if (!empty($condition['voteid'])) {
            $where[] = 'vote_id = :voteid';
            $bind['voteid'] = $condition['voteid'];
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        $record = $this->_db->fetchRow($sql, $bind);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Tudu_Record_Vote', $record);
    }

    /**
     * 
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getVotes(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        if (empty($condition['tuduid'])) {
            return new Oray_Dao_Recordset();
        }

        $table   = 'td_vote_option AS vo '
                 . 'LEFT JOIN td_vote AS v ON v.tudu_id = vo.tudu_id AND v.vote_id = vo.vote_id';
        $columns = 'v.tudu_id AS tuduid, v.vote_id AS voteid, v.title, v.max_choices AS maxchoices, v.vote_count AS votecount, v.privacy, '
                 . 'v.visible, v.anonymous, v.is_reset AS isreset, v.order_num AS voteorder, v.expire_time AS expiretime, '
                 . 'vo.option_id AS optionid, vo.text, vo.order_num AS optionorder, vo.vote_count As optioncount';
        $bind    = array('tuduid' => $condition['tuduid']);
        $order   = array();
        $limit   = '';

        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'voteorder':
                    $key = 'v.order_num';
                    break;
                case 'optionorder':
                    $key = 'vo.order_num';
                    break;
                default:
                    continue 2;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        // LIMIT
        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} WHERE v.tudu_id = :tuduid {$order} {$limit}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            if (!$records) {
                return new Oray_Dao_Recordset();
            }
        }  catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }

        return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Vote');
    }

    /**
     * 创建投票
     *
     * @param array $params
     * @return boolean
     */
    public function createVote(array $params)
    {
        if (empty($params['tuduid']) || empty($params['voteid'])) {
            return false;
        }

        $table = 'td_vote';
        $bind  = array();

        $bind['tudu_id'] = $params['tuduid'];
        $bind['vote_id'] = $params['voteid'];

        if (isset($params['title'])) {
            $bind['title'] = $params['title'];
        }

        if (isset($params['maxchoices']) && is_int($params['maxchoices'])) {
            $bind['max_choices'] = max(0, $params['maxchoices']);
        }

        if (isset($params['privacy'])) {
            $bind['privacy'] = $params['privacy'] ? 1 : 0;
        }

        if (isset($params['visible'])) {
            $bind['visible'] = $params['visible'] ? 1 : 0;
        }

        if (isset($params['anonymous'])) {
            $bind['anonymous'] = $params['anonymous'] ? 1 : 0;
        }

        if (isset($params['isreset'])) {
            $bind['is_reset'] = $params['isreset'] ? 1 : 0;
        }

        if (isset($params['ordernum'])) {
            $bind['order_num'] = (int) $params['ordernum'];
        }

        if (isset($params['expiretime']) && is_int($params['expiretime'])) {
            $bind['expire_time'] = $params['expiretime'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新投票设置
     *
     * @param string $tuduId
     * @param array  $params
     * @return boolean
     */
    public function updateVote($tuduId, $voteId, array $params)
    {
        if (!$tuduId || !$voteId) {
            return false;
        }

        $table = 'td_vote';
        $bind  = array();

        if (isset($params['title'])) {
            $bind['title'] = $params['title'];
        }

        if (isset($params['maxchoices']) && is_int($params['maxchoices'])) {
            $bind['max_choices'] = max(0, $params['maxchoices']);
        }

        if (isset($params['privacy'])) {
            $bind['privacy'] = $params['privacy'] ? 1 : 0;
        }

        if (isset($params['visible'])) {
            $bind['visible'] = $params['visible'] ? 1 : 0;
        }

        if (isset($params['anonymous'])) {
            $bind['anonymous'] = $params['anonymous'] ? 1 : 0;
        }

        if (isset($params['isreset'])) {
            $bind['is_reset'] = $params['isreset'] ? 1 : 0;
        }

        if (isset($params['expiretime']) && is_int($params['expiretime'])) {
            $bind['expire_time'] = $params['expiretime'];
        }

        if (isset($params['votecount'])) {
            $bind['vote_count'] = (int) $params['votecount'];
        }

        if (isset($params['ordernum'])) {
            $bind['order_num'] = (int) $params['ordernum'];
        }

        try {
            $where = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND vote_id = ' . $this->_db->quote($voteId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }
    
    /**
     * 删除投票
     *
     * @param string $tuduId
     * @param string $voteId
     */
    public function deleteVote($tuduId, $voteId)
    {
        if (!$tuduId || !$voteId) {
            return false;
        }

        $bind = array('tuduid' => $tuduId, 'voteid' => $voteId);
        $sqls = array();

        $sqls[] = 'DELETE FROM td_voter WHERE tudu_id = :tuduid AND vote_id = :voteid';
        $sqls[] = 'DELETE FROM td_vote_option WHERE tudu_id = :tuduid AND vote_id = :voteid';
        $sqls[] = 'DELETE FROM td_vote WHERE tudu_id = :tuduid AND vote_id = :voteid';

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql, $bind);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }

    /**
     *
     * @param string $tuduId
     */
    public function existsVote($tuduId, $voteId = null)
    {
        if (!$tuduId) {
            return false;
        }

        $bind = array('tuduid' => $tuduId);

        $sql = 'SELECT COUNT(0) FROM td_vote WHERE tudu_id = :tuduid';
        if (!empty($voteId)) {
            $bind['voteid'] = $voteId;
            $sql .= ' AND vote_id = :voteid';
        }

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);
            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 获取投票项目
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Tudu_Record_VoteOption
     */
    public function getOption(array $condition, $filter = null)
    {
        $table   = 'td_vote_option';
        $columns = 'tudu_id AS tuduid, option_id As optionid, vote_id AS voteid, text, vote_count AS votecount, order_num AS ordernum, voters';
        $where   = array();
        $bind    = array();

        if (!empty($condition['tuduid'])) {
            $where[] = 'tudu_id = :tuduid';
            $bind['tuduid'] = $condition['tuduid'];
        }

        if (!empty($condition['voteid'])) {
            $where[] = 'vote_id =:voteid';
            $bind['voteid'] = $condition['voteid'];
        }

        if (!empty($condition['optionid'])) {
            $where[] = 'option_id = :optionid';
            $bind['optionid'] = $condition['optionid'];
        }

        if (!$where) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Tudu_Record_Option', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 获取投票项目列表
     *
     * @param array $condition
     * @param array $filter
     * @param mixed $sort
     * @param int   $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getOptions(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_vote_option';
        $columns = 'tudu_id AS tuduid, option_id As optionid, vote_id AS voteid, text, vote_count AS votecount, order_num AS ordernum, voters';
        $where   = array();
        $bind    = array();
        $order   = array();

        if (!empty($condition['tuduid'])) {
            $where[] = 'tudu_id = :tuduid';
            $bind['tuduid'] = $condition['tuduid'];
        }

        if (!empty($condition['voteid'])) {
            $where[] = 'vote_id =:voteid';
            $bind['voteid'] = $condition['voteid'];
        }

        if (!$where) {
            return new Oray_Dao_Recordset();
        }

        // WHERE
        $where = 'WHERE ' . implode(' AND ', $where);

        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'order_num';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        // ORDER
        $order = implode(', ', $order);
        if ($order) {
            $order = 'ORDER BY ' . $order;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order}";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Option');
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }

    /**
     * 创建选项
     *
     * @param array $params
     * @return boolean
     */
    public function createOption(array $params)
    {
        if (empty($params['tuduid'])
            || empty($params['optionid'])
            || empty($params['voteid'])
            || empty($params['text']))
        {
            return false;
        }

        $table = 'td_vote_option';
        $bind  = array();

        $bind['tudu_id']   = $params['tuduid'];
        $bind['vote_id']   = $params['voteid'];
        $bind['option_id'] = $params['optionid'];
        $bind['text']      = $params['text'];

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 修改选项内容
     *
     * @param string $tuduId
     * @param string $optionId
     * @param array  $params
     * @return boolean
     */
    public function updateOption($tuduId, $voteId, $optionId, array $params)
    {
        if (!$tuduId || !$optionId || !$voteId) {
            return false;
        }

        $table = 'td_vote_option';
        $bind  = array();

        if (!empty($params['text'])) {
            $bind['text'] = $params['text'];
        }

        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
            $bind['order_num'] = $params['ordernum'];
        }

        if (!$bind) {
            return false;
        }

        try {
            $where = 'tudu_id = ' . $this->_db->quote($tuduId) . ' AND vote_id = ' . $this->_db->quote($voteId) . ' AND option_id = ' . $this->_db->quote($optionId);

            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 删除投票选项
     *
     * @param string $tuduId
     * @param string $optionId
     */
    public function deleteOption($tuduId, $voteId, $optionId)
    {
        if (!$tuduId || !$optionId || !$voteId) {
            return false;
        }

        $bind  = array(
            'tuduid' => $tuduId,
            'voteid' => $voteId,
            'optionid' => $optionId
        );

        $table = 'td_vote_option';
        $where = 'tudu_id = :tuduid AND option_id = :optionid AND vote_id = :voteid';

        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $where;

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $tuduId
     */
    public function resetOptionsVoteCount($tuduId, $voteId = null)
    {
        if (!$tuduId) {
            return false;
        }

        $table = 'td_vote_option';
        $bind  = array('tuduid' => $tuduId);

        $sql = 'UPDATE ' . $table . ' SET vote_count = 0 WHERE tudu_id = :tuduid';
        if (!empty($voteId)) {
            $bind['voteid'] = $voteId;
            $sql .= ' AND vote_id = :voteid';
        }

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 投票
     *
     * @param string $tuduId
     * @param mixed  $options
     * @param string $uniqueId
     * @param string $voter
     * @return boolean
     */
    public function vote($tuduId, $voteId, $options, $uniqueId, $voter)
    {
        if (!$tuduId || !$voteId || !$options || !$uniqueId) {
            return false;
        }

        if (!is_array($options)) {
            $options = array($options);
        }

        $sql = 'UPDATE td_vote_option SET vote_count = vote_count + 1, voters = IF(voters IS NULL, '
             . $this->_db->quote($voter . "\n") . ', CONCAT(voters, ' . $this->_db->quote($voter . "\n") . ')) '
             . 'WHERE tudu_id = ? AND option_id = ? AND vote_id = ?';

        $bind = array(
            'unique_id'   => $uniqueId,
            'tudu_id'     => $tuduId,
            'vote_id'     => $voteId,
            'options'     => implode("\n", $options),
            'create_time' => time()
        );
        try {
            $this->_db->insert('td_voter', $bind);

            foreach ($options as $optionId) {
                $this->_db->query($sql, array($tuduId, $optionId, $voteId));
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        $this->updateVoteCount($tuduId, $voteId);

        return true;
    }

    /**
     *
     * @param string $tuduId
     */
    public function updateVoteCount($tuduId, $voteId)
    {
        if (!$tuduId || !$voteId) {
            return false;
        }

        $sql = "UPDATE td_vote SET vote_count = (SELECT SUM(vote_count) FROM td_vote_option WHERE tudu_id = '{$tuduId}' AND vote_id = '{$voteId}') WHERE tudu_id = '{$tuduId}' AND vote_id = '{$voteId}'";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 是否参与过投票
     *
     * @param string $tuduId
     * @param string $uniqueId
     * @return boolean
     */
    public function hasVote($tuduId, $voteId, $uniqueId)
    {
        if (!$tuduId || !$voteId || !$uniqueId) {
            return false;
        }

        $bind = array(
            'tuduid'   => $tuduId,
            'voteid'   => $voteId,
            'uniqueid' => $uniqueId
        );
        $sql = 'SELECT COUNT(0) FROM td_voter WHERE tudu_id = :tuduid AND vote_id = :voteid AND unique_id = :uniqueid';

        try {
            $count = (int) $this->_db->fetchOne($sql, $bind);
            return $count > 0;
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
    }

    /**
     * 统计参与人
     *
     * @param string $tuduId
     */
    public function countVoter($tuduId, $voteId)
    {
        if (!$tuduId || !$voteId) {
            return null;
        }

        $bind = array(
            'tuduid' => $tuduId,
            'voteid' => $voteId
        );
        $sql  = 'SELECT COUNT(0) FROM td_voter WHERE tudu_id = :tuduid AND vote_id = :voteid';

        try {
            return $count = (int) $this->_db->fetchOne($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return 0;
        }
    }
    
    /**
     *
     * @param array $condition
     */
    public function getVoters($tuduId, $voteId)
    {
        if (!$tuduId || !$voteId) {
            return null;
        }

        $table = 'td_voter';
        $columns = 'unique_id AS uniqueid, tudu_id AS tuduid, vote_id AS voteid, options, create_time AS createtime';

        $bind = array('tuduid' => $tuduId, 'voteid' => $voteId);

        $sql = "SELECT {$columns} FROM {$table} WHERE tudu_id = :tuduid AND vote_id = :voteid";

        try {
            $records = $this->_db->fetchAll($sql, $bind);

            if (!$records) {
                return array();
            }

            return self::formatRecords($records);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return array();
        }
    }

    /**
     *
     * @param string $uniqueId
     * @param string $tuduId
     */
    public function deleteVoter($uniqueId, $tuduId, $voteId)
    {
        if (!$uniqueId || !$tuduId || !$voteId) {
            return false;
        }

        $table = 'td_voter';
        $bind = array(
            'tuduid'   => $tuduId,
            'uniqueid' => $uniqueId,
            'voteid'   => $voteId
        );

        $sql = 'DELETE FROM ' . $table . ' WHERE tudu_id = :tuduid AND unique_id = uniqueid AND vote_id = :voteid';

        try {
            $this->_db->query($sql, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 重置投票
     *
     * @param string $tuduId
     */
    public function clearVote($tuduId, $voteId)
    {
        if (!$tuduId || !$voteId) {
            return false;
        }

        $sqls = array();

        $bind = array(
            'tuduid' => $tuduId,
            'voteid' => $voteId
        );
        $sqls[]  = "DELETE FROM td_voter WHERE tudu_id = :tuduid AND vote_id = :voteid";
        $sqls[]  = "UPDATE td_vote_option SET vote_count = 0, voters = NULL WHERE tudu_id = :tuduid AND vote_id = :voteid";
        $sqls[]  = "UPDATE td_vote SET vote_count = 0 WHERE tudu_id = :tuduid AND vote_id = :voteid";

        try {
            foreach ($sqls as $sql) {
                $this->_db->query($sql, $bind);
            }
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     *
     * @param string $tuduId
     * @return Oray_Dao_Recordset
     */
    public function getVotesByTuduId($tuduId)
    {
        return $this->getVotes(array('tuduid' => $tuduId), null, 'voteorder ASC, optionorder ASC');
    }

    /**
     * 选项ID
     */
    public static function getOptionId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }

    /**
     * 投票ID
     */
    public static function getVoteId()
    {
        return base_convert(strrev(microtime(true)) . rand(0, 9999), 10, 32);
    }

    /**
     * 
     * @param array $records
     */
    public function formatVotes(array $records)
    {
        $ret     = array();
        $options = array();

        if (empty($records)) {
            return $ret;
        }

        foreach ($records as $record) {
            $ret[$record['voteid']] = array(
                'tuduid'     => $record['tuduid'],
                'voteid'     => $record['voteid'],
                'title'      => $record['title'],
                'maxchoices' => $record['maxchoices'],
                'privacy'    => $record['privacy'],
                'visible'    => $record['visible'],
                'anonymous'  => $record['anonymous'],
                'isreset'    => $record['isreset'],
                'votecount'  => $record['votecount'],
                'ordernum'   => $record['voteorder']
            );

            $options[$record['voteid']][$record['optionid']] = array(
                'optionid' => $record['optionid'],
                'text'     => $record['text'],
                'votecount'=> $record['optioncount'],
                'ordernum' => $record['optionorder']
            );
            
        }

        foreach ($ret as $voteId => $vote) {
            $ret[$voteId]['options'] = $options[$voteId];
        }

        return $ret;
    }

    /**
     *
     * @param array $records
     */
    public static function formatRecords(array $records)
    {
        $ret = array();
        foreach ($records as &$record) {
            $record['options'] = explode("\n", $record['options']);
            $ret[$record['uniqueid']] = $record;
        }

        return $ret;
    }
}