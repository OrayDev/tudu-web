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
 * @version    $Id: Exception.php 1894 2012-05-31 08:02:57Z cutecube $
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
class Model_Tudu_Extension_Handler_Cycle extends Model_Tudu_Extension_Handler_Abstract
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
        $vote = $tudu->getExtension('Model_Tudu_Extension_Vote');

        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);
        $tuduId  = $tudu->tuduId;

        if (!$vote) {
            return ;
        }

        $votes   = $vote->getVotes();
        $deleted = $vote->getDeleteVotes();

        foreach ($votes as $vote) {
            if (!empty($vote['isnew'])) {
                $vote['tuduid'] = $tuduId;
                $ret = $daoVote->createVote($vote);

                if (!$ret) {
                    continue ;
                }

                foreach ($vote['options'] as $opt) {
                    $daoVote->createOption($opt);
                }

            } else {
                // 删除选项
                if (!empty($vote['deleteoptions'])) {

                    $sv     = $this->_getTuduVote($vote['voteid']);
                    $voters = $daoVote->getVoters($tuduId, $vote['voteid']);
                    $offset = 0;
                    if (null !== $sv) {
                        foreach ($vote['deleteoptions'] as $optionId) {
                            $offset += (int) $sv['options'][$optionId]['votecount'];

                            foreach ($voters as $val) {
                                if (in_array($optionId, $val['options'])) {
                                    $daoVote->deleteVoter($val['uniqueid'], $tuduId, $vote['voteid']);
                                }
                            }

                            // 删除选项
                            $daoVote->deleteOption($tuduId, $vote['voteid'], $optionId);
                        }

                        if ($offset > 0) {
                            $vote['votecount'] = $sv['votecount'] - (int) $offset;
                        }

                        // 清零
                        if ($vote['isreset']) {
                            $daoVote->clearVote($tuduId, $vote['voteid']);
                        }
                    }
                }

                $daoVote->updateVote($tuduId, $vote['voteid'], $vote);

                foreach ($vote['deleteoptions'] as $dopt) {
                    $daoVote->deleteOption($tuduId, $vote['voteid'], $dopt);
                }

                foreach ($vote['options'] as $opt) {
                    if ($opt['isnew']) {
                        $daoVote->createOption($opt);
                    } else {
                        $daoVote->updateOption($tuduId, $vote['voteid'], $opt['optionid'], $opt);
                    }
                }
            }
        }

        foreach ($deleted as $id) {
            $daoVote->deleteVote($tuduId, $id);
        }
    }

    private function _getTuduVote($voteId)
    {
        if (null === $this->_votes) {
            /* @var $daoVote Dao_Td_Tudu_Vote */
            $daoVote = Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Vote', Tudu_Dao_Manager::DB_TS);

            $this->_votes = $daoVote->getVotesByTuduId($tudu->tuduId)->toArray('voteid');
        }

        return isset($this->_votes[$voteId]) ? $this->_votes[$voteId] : null;
    }
}