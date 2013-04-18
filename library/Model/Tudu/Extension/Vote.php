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
 * @version    $Id: Tudu.php 2070 2012-08-22 09:37:26Z cutecube $
 */

/**
 * @see Dao_Td_Tudu_Vote
 */
require_once 'Dao/Td/Tudu/Vote.php';


/**
 * @category   Model
 * @package    Model_Exception
 * @copyright  Copyright (c) 2009-2012 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Extension_Vote extends Model_Tudu_Extension_Abstract
{

    /**
     *
     * @var string
     */
    protected $_handlerClass = 'Model_Tudu_Extension_Handler_Vote';

    /**
     *
     * @var array
     */
    protected $_attrs = array();

    /**
     *
     * @var array
     */
    protected $_votes = array();

    /**
     *
     * @var array
     */
    protected $_deleted = array();

    /**
     *
     * @var int
     */
    protected $_newCounter = 0;


    /**
     * Constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = null)
    {
        if (!empty($attributes)) {
            $this->setAttributes($attributes);
        }
    }

    /**
     *
     * @param string $name
     */
    public function getAttribute($name)
    {
        $name = strtolower($name);

        if (empty($this->_attrs[$name])) {
            return null;
        }

        return $this->_attrs[$name];
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     *
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $k => $val) {
            if ($k == 'steps') {
                $this->_steps = $val;
                continue ;
            }

            $this->setAttribute($k, $val);
        }

        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed  $value
     * @return Model_Tudu_Extension_Flow
     */
    public function setAttribute($key, $value)
    {
        $key = strtolower($key);

        if ($key == 'steps') {
            $this->_steps = $value;

            return $this;
        }

        $this->_attrs[$key] = $value;

        return $this;
    }

    /**
     *
     */
    public function addVote(array $vote)
    {

        $item = array(
            'title'      => null,
            'maxchoice'  => 1,
            'privacy'    => 0,
            'visible'    => 0,
            'anonymous'  => 0,
            'isreset'    => 0,
            'ordernum'   => 0,
            'options'    => array(),
            'removeoptions' => array(),
            'expiretime' => null
        );

        foreach ($item as $key => $val) {
            if (isset($vote[$key])) {
                $item[$key] = $val;
            }
        }

        if (!isset($item['voteid'])) {
            $voteId = Dao_Td_Tudu_Vote::getVoteId();
            $item['voteid'] = $voteId;
            $item['isnew']  = true;
        } else {
            $voteId = $item['voteid'];
        }

        $this->_votes[$voteId] = $item;

        if (isset($vote['options']) && is_array($vote['options'])) {
            foreach ($vote['options'] as $item) {
                $this->addOption($voteId, $item);
            }
        }

        return $voteId;
    }

    /**
     *
     * @param string $voteId
     */
    public function deleteVote($voteId)
    {
        if (!isset($this->_votes[$voteId])) {
            return ;
        }

        unset($this->_votes[$voteId]);
        $this->_deleted[] = $voteId;
    }

    /**
     *
     * @param string $voteId
     * @param string $optionId
     */
    public function deleteOption($voteId, $optionId)
    {
        if (!isset($this->_votes[$voteId]['options'][$optionId])) {
            return ;
        }

        unset($this->_votes[$voteId]['options'][$optionId]);
        $this->_votes[$voteId]['removeoptions'][] = $optionId;
    }

    /**
     *
     * @return array
     */
    public function getVotes()
    {
        return $this->_votes;
    }

    /**
     *
     * @return array
     */
    public function getDeleteVotes()
    {
        return $this->_deleted;
    }

    /**
     *
     * @param string $voteId
     * @param array  $option
     */
    public function addOption($voteId, array $option)
    {
        if (!isset($this->_votes[$voteId])) {
            return ;
        }

        $item = array(
            'optionid'  => isset($option['optionid']) ? $option['ordernum'] : Dao_Td_Tudu_Vote::getOptionId(),
            'text'      => isset($option['text']) ? $option['text'] : '',
            'ordernum'  => isset($option['ordernum']) ? $option['ordernum'] : 0,
            'votecount' => isset($option['votecount']) ? (int) $option['votecount'] : 0,
            'isnew'     => !isset($option['optionid'])
        );

        $this->_votes['options'][$item['optionid']] = $item;

        return $item['optionid'];
    }

    /**
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     *
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->_handlerClass;
    }
}