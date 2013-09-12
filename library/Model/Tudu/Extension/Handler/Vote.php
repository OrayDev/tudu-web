<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Vote.php 2886 2013-06-18 01:11:54Z cutecube $
 */

/**
 * @see Model_Tudu_Extension_Handler_Abstract
 */
require_once 'Model/Tudu/Extension/Handler/Abstract.php';

/**
 * @see Tudu_Dao_Manager
 */
require_once 'Tudu/Dao/Manager.php';

/**
 * 业务模型抛出异常基类
 *
 * @category   Model
 * @package    Model_Auth
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Handler_Vote extends Model_Tudu_Extension_Handler_Abstract
{
    /**
     *
     * @var array
     */
    private $_votes;

    /**
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function filter(Model_Tudu_Tudu &$tudu)
    {
        $vote = $tudu->getExtension('Model_Tudu_Extension_Vote');

        $votes = $vote->getVotes();
        $tudu->special = Dao_Td_Tudu_Tudu::SPECIAL_VOTE;

        if (count($votes) <= 0) {
            $tudu->special = 0;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Model_Tudu_Extension_Handler_Abstract::action()
     */
    public function action(Model_Tudu_Tudu &$tudu)
    {
        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);
        $tuduId  = $tudu->tuduId;

        if ($tudu->special != Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            $daoVote->deleteVotes($tuduId);
            return ;
        }

        // 草稿时候删除投票后重建
        if ($tudu->operation == 'save' || (!empty($tudu->fromtudu) && $tudu->fromtudu->isDraft)) {
            $daoVote->deleteVotes($tuduId);
        }

        $vote = $tudu->getExtension('Model_Tudu_Extension_Vote');

        if (!$vote) {
            return ;
        }

        $votes     = $vote->getVotes();
        $fromVotes = $this->_getTuduVotes($tuduId);
        $delVotes  = array();

        if ($fromVotes) {
            $currVoteIds = array_keys($votes);
            $fromVoteIds = array_keys($fromVotes);

            // 找出需要删除的投票
            $delVotes = array_diff($fromVoteIds, $currVoteIds);var_dump($delVotes);
            foreach ($delVotes as $id) {
                $daoVote->deleteVote($tuduId, $id);
            }

            foreach ($votes as $voteId => $item) {
                if ($daoVote->existsVote($tuduId, $voteId)) {
                    $optIds  = array_keys($item['options']);
                    $options = $fromVotes[$voteId]['options'];
                    $votes[$voteId]['removeoptions'] = array_diff(array_keys($options), $optIds);
                }
            }
        }
var_dump($votes);
        foreach ($votes as $voteId => $vote) {
            if (!empty($vote['isnew'])) {
                $vote['tuduid'] = $tuduId;
                $ret = $daoVote->createVote($vote);
var_dump($ret);
                if (!$ret) {
                    continue ;
                }

                foreach ($vote['options'] as $opt) {
                    $opt['tuduid'] = $tuduId;
                    $opt['voteid'] = $voteId;
                    $daoVote->createOption($opt);
                }

            } else {
                // 删除选项
                if (!empty($vote['removeoptions'])) {
                    $voters = $daoVote->getVoters($tuduId, $voteId);
                    $offset = 0;

                    foreach ($vote['removeoptions'] as $optionId) {
                        $offset += (int) $fromVotes[$voteId]['options'][$optionId]['votecount'];

                        foreach ($voters as $val) {
                            if (in_array($optionId, $val['options'])) {
                                $daoVote->deleteVoter($val['uniqueid'], $tuduId, $voteId);
                            }
                        }

                        // 删除选项
                        $daoVote->deleteOption($tuduId, $voteId, $optionId);
                    }

                    if ($offset > 0) {
                        $vote[$voteId]['votecount'] = $fromVotes[$voteId]['votecount'] - (int) $offset;
                    }
                }

                // 清零
                if (!empty($vote['isreset'])) {
                    $daoVote->clearVote($tuduId, $voteId);
                }
var_dump($vote);
                if (!empty($vote['options'])) {

                    $daoVote->updateVote($tuduId, $voteId, $vote);

                    foreach ($vote['options'] as $opt) {
                        if ($opt['isnew']) {
                            $opt['tuduid'] = $tuduId;
                            $opt['voteid'] = $voteId;
                            $daoVote->createOption($opt);
                        } else {
                            $daoVote->updateOption($tuduId, $voteId, $opt['optionid'], $opt);
                        }
                    }
                }
            }
        }

        echo 'x';exit;
    }

    /**
     * 获取图度投票
     *
     * @param string $tuduId
     */
    private function _getTuduVotes($tuduId)
    {
        if (null === $this->_votes) {
            /* @var $daoVote Dao_Td_Tudu_Vote */
            $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);

            $votes = $daoVote->getVotesByTuduId($tuduId);
            if ($votes) {
                $votes = $votes->toArray();
                $votes = $daoVote->formatVotes($votes);
            } else {
                $votes = null;
            }

            $this->_votes = $votes;
        }

        return isset($this->_votes) ? $this->_votes : null;
    }
}