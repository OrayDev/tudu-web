<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Model
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Vote.php 2484 2012-12-07 10:36:54Z cutecube $
 */

/**
 * 投票扩展
 *
 * @category   Tudu
 * @package    Tudu_Tudu_Storage
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Tudu_Extension_Vote extends Tudu_Tudu_Extension_Abstract
{
    /**
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     * @param array                  $params
     */
    public function onPrepare(Tudu_Tudu_Storage_Tudu &$tudu, array $params)
    {
        if (empty($params['vote'])) {
            $tudu->vote = null;
            return ;
        }

        foreach ($params['vote'] as $vote) {
            if (empty($vote['title'])) {
                require_once 'Tudu/Tudu/Exception.php';
                throw new Tudu_Tudu_Exception('Missing vote params[title]', Tudu_Tudu_Exception::MISSING_VOTE_TITLE);
            }
            if (empty($vote['options']) && empty($vote['newoptions'])) {
                require_once 'Tudu/Tudu/Exception.php';
                throw new Tudu_Tudu_Exception('Missing vote params[options]', Tudu_Tudu_Exception::MISSING_VOTE_OPTIONS);
            }
            if (!empty($vote['options'])) {
                foreach ($vote['options'] as $option) {
                    if (empty($option['text'])) {
                        require_once 'Tudu/Tudu/Exception.php';
                        throw new Tudu_Tudu_Exception('Missing vote params[options]', Tudu_Tudu_Exception::MISSING_VOTE_OPTIONS);
                    }
                }
            }
            if (!empty($vote['newoptions'])) {
                foreach ($vote['newoptions'] as $option) {
                    if (empty($option['text'])) {
                        require_once 'Tudu/Tudu/Exception.php';
                        throw new Tudu_Tudu_Exception('Missing vote params[options]', Tudu_Tudu_Exception::MISSING_VOTE_OPTIONS);
                    }
                }
            }
        }
    }

    /**
     * 格式化投票参数
     *
     * @param array  $params
     * @param string $suffix
     * @return array
     */
    public function formatParams($params, $suffix = '')
    {
        $vote = array();
        $voteMember = 'votemember' . $suffix;
        if (!empty($params[$voteMember]) && is_array($params[$voteMember])) {
            foreach ($params[$voteMember] as $item) {
                $voteId = !empty($params['voteid-' . $item . $suffix]) ? $params['voteid-' . $item . $suffix] : Dao_Td_Tudu_Vote::getVoteId();
                $vote[$voteId] = array(
                    'voteid'     => $voteId,
                    'title'      => $params['title-' . $item . $suffix],
                    'maxchoices' => (int) $params['maxchoices-' . $item . $suffix],
                    'visible'    => !empty($params['visible-' . $item . $suffix]) ? (int) $params['visible-' . $item . $suffix] : 0,
                    'anonymous'  => !empty($params['anonymous-' . $item . $suffix]) ? (int) $params['anonymous-' . $item . $suffix] : 0,// 创建人显示投票参与人
                    'privacy'    => !empty($params['privacy-' . $item . $suffix]) ? (int) $params['privacy-' . $item . $suffix] : 0,
                    'isreset'    => !empty($params['isreset-' . $item . $suffix]) ? (int) $params['isreset-' . $item . $suffix] : 0,
                    'ordernum'   => $params['voteorder-' . $item . $suffix],
                    'expiretime' => !empty($params['endtime']) ? strtotime($params['endtime']) : null,
                    'options'    => array(),
                    'newoptions' => array()
                );

                $options    = array();
                $newOptions = array();
                $optionMember    = 'optionid-' . $item . $suffix;
                $newOptionMember = 'newoption-' . $item . $suffix;

                if (!empty($params[$optionMember]) && is_array($params[$optionMember])) {
                    foreach ($params[$optionMember] as $option) {
                        //if (empty($params['text-' . $item . '-' . $option . $suffix])) continue;

                        $options[$option] = array(
                            'optionid' => $option,
                            'text'     => $params['text-' . $item . '-' . $option . $suffix]
                        );

                        if (isset($params['ordernum-' . $item. '-' . $option . $suffix])) {
                            $options[$option]['ordernum'] = (int) $params['ordernum-' . $item. '-' . $option . $suffix];
                        }
                    }
                    $vote[$voteId]['options'] = $options;
                }

                if (!empty($params[$newOptionMember]) && is_array($params[$newOptionMember])) {
                    foreach ($params[$newOptionMember] as $option) {
                        //if (empty($params['text-' . $item . '-' . $option . $suffix])) continue;

                        $orderNum = $params['ordernum-' . $item . '-' . $option . $suffix];
                        $optionId = Dao_Td_Tudu_Vote::getOptionId();

                        $newOptions[$optionId] = array(
                            'optionid' => $optionId,
                            'text'     => $params['text-' . $item . '-' . $option . $suffix],
                            'ordernum' => (int) $orderNum
                        );
                    }
                    $vote[$voteId]['newoptions'] = $newOptions;
                }
            }
        }

        return $vote;
    }


    /**
     * 更新投票数据
     *
     * @param strng $tuduId
     * @param array $params
     * @return boolean
     */
    public function updateVote($tuduId, $params)
    {
        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        foreach ($params as $voteId => $vote) {
            $vote['tuduid'] = $tuduId;

            if (!$daoVote->existsVote($tuduId, $voteId)) {
                $ret = $daoVote->createVote($vote);
            } else {
                $ret = $daoVote->updateVote($tuduId, $voteId, $vote);
            }

            if (!$ret) {
                return false;
            }

            if (!empty($vote['newoptions'])) {
                foreach ($vote['newoptions'] as $option) {
                    if (empty($option['text'])) {
                        continue;
                    }
                    $option['tuduid']   = $tuduId;
                    $option['voteid']   = $voteId;
                    $daoVote->createOption($option);
                }
            }

            if (!empty($vote['options'])) {
                foreach ($vote['options'] as $option) {
                    if (empty($option['text'])) {
                        continue;
                    }
                    $daoVote->updateOption($tuduId, $voteId, $option['optionid'], $option);
                }
            }
        }

        return true;
    }

    /**
     * 创建图度
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function postCreate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        if ($tudu->vote) {
            $this->updateVote($tudu->tuduId, $tudu->vote);
        }
    }

    /**
     * 更新图度时执行
     *
     * @param Tudu_Tudu_Storage_Tudu $tudu
     */
    public function preUpdate(Tudu_Tudu_Storage_Tudu &$tudu)
    {
        /* @var $daoVote Dao_Td_Tudu_Vote */
        $daoVote = $this->getDao('Dao_Td_Tudu_Vote');

        $params = $tudu->vote;

        if ($tudu->special == Dao_Td_Tudu_Tudu::SPECIAL_VOTE) {
            $votes    = $daoVote->getVotesByTuduId($tudu->tuduId);

            if ($votes) {
                $votes = $votes->toArray();
                $votes = $daoVote->formatVotes($votes);
                $voteIds = array_keys($votes);
                $paramIds = array_keys($params);

                // 找出需要删除的投票
                $removeVotes = array_diff($voteIds, $paramIds);
                if (count($removeVotes)) {
                    foreach ($removeVotes as $voteId) {
                        $daoVote->deleteVote($tudu->tuduId, $voteId);
                    }
                }

                foreach ($params as $voteId => $vote) {
                    if ($daoVote->existsVote($tudu->tuduId, $voteId)) {
                        $optIds = array_keys($vote['options']);
                        $options = $votes[$voteId]['options'];

                        $removeOptions = array_diff(array_keys($options), $optIds);
                        $offset = 0;
                        if (count($removeOptions)) {
                            $voters = $daoVote->getVoters($tudu->tuduId, $voteId);

                            foreach ($removeOptions as $optionId) {
                                $offset += (int) $options[$optionId]['votecount'];

                                foreach ($voters as $val) {
                                    if (in_array($optionId, $val['options'])) {
                                        $daoVote->deleteVoter($val['uniqueid'], $tudu->tuduId, $voteId);
                                    }
                                }

                                // 删除选项
                                $daoVote->deleteOption($tudu->tuduId, $voteId, $optionId);
                            }
                        }

                        if ($offset > 0) {
                            $params[$voteId]['votecount'] = $votes[$voteId]['votecount'] - (int) $offset;
                        }

                        // 清零
                        if ($vote['isreset']) {
                            $daoVote->clearVote($tudu->tuduId, $voteId);
                        }
                    }
                }
            }
        }

        if ($tudu->vote) {
            $this->updateVote($tudu->tuduId, $params);
        } else {
            $tudu->special = 0;
        }
    }
}