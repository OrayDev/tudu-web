<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Log
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Contact_Record_Contact extends Oray_Dao_Record
{

    /**
     *
     * @var string
     */
    public $contactId;

    /**
     *
     * @var string
     */
    public $uniqueId;

    /**
     *
     * @var string
     */
    public $trueName;

    /**
     *
     * @var boolean
     */
    public $fromUser;

    /**
     *
     * @var string
     */
    public $pinyin;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $mobile;

    /**
     *
     * @var array
     */
    public $properties;

    /**
     *
     * @var string
     */
    public $memo;

    /**
     *
     * @var binary
     */
    public $avatars;

    /**
     *
     * @var int
     */
    public $affinity;

    /**
     *
     * @var boolean
     */
    public $isAvatars;

    /**
     *
     * @var int
     */
    public $lastContactTime;

    /**
     *
     * @var array
     */
    public $groups;

    /**
     * Constructor
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->contactId  = $record['contactid'];
        $this->uniqueId   = $record['uniqueid'];
        $this->fromUser   = $this->_toBoolean($record['fromuser']);
        $this->trueName   = $record['truename'];
        $this->pinyin     = $record['pinyin'];
        $this->email      = $record['email'];
        $this->mobile     = $record['mobile'];
        $this->properties = !empty($record['properties'])
                          ? $this->_formatProperties($record['properties'])
                          : array();
        $this->memo       = isset($record['memo']) ? $record['memo'] : null;
        $this->avatars    = isset($record['avatars']) ? $record['avatars'] : null;
        $this->affinity   = $this->_toInt($record['affinity']);
        $this->isAvatars  = isset($record['isavatars']) ? (boolean)$record['isavatars'] : null;
        $this->groups     = !empty($record['groups']) ? explode(',', $record['groups']) : array();

        $this->lastContactTime = $this->_toTimestamp($record['lastcontacttime']);

        parent::__construct();
    }

    /**
     * 格式化联系人相关属性
     *
     * @param $string
     */
    private function _formatProperties($string)
    {
        if (function_exists('json_decode')) {
            $array = get_object_vars(@json_decode($string));
        } else {
            $array = Zend_Json::decode($string, Zend_Json::TYPE_ARRAY);
        }

        if (!$array) {
            $array = array();
        }

        return $array;
    }
}