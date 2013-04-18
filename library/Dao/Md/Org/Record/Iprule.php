<?php
/**
 * Tudu Dao
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Iprule.php 1031 2011-07-28 10:17:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage Record
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Md_Org_Record_Iprule extends Oray_Dao_Record
{

    /**
     * 组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 类型
     *
     * @var int
     */
    public $type;

    /**
     * 设置规则
     *
     * @var array
     */
    public $rule;

    /**
     * 是否有效
     *
     * @var boolean
     */
    public $isValid;

    /**
     * 例外
     *
     * @var array
     */
    public $exception;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->orgId     = $record['orgid'];
        $this->type      = $this->_toInt($record['type']);
        $this->rule      = $this->_formatRule($record['rule']);
        $this->isValid   = $this->_toBoolean($record['isvalid']);
        $this->exception = !empty($record['exception']) ? explode("\n", $record['exception']) : null;

        parent::__construct();
    }

    /**
     * 格式化IP规则
     *
     * @param $rule
     */
    private function _formatRule($rule)
    {
        if (!$rule) {
            return array();
        }

        return explode("\n", $rule);
    }

    /**
     * 是否属于规则匹配
     *
     * @param $ip
     */
    public function isMatch($ip)
    {
        $arrIp = explode('.', $ip);

        foreach ($this->rule as $ipaddr) {
            if ($ip == $ipaddr) {
                return true;
            }

            $arr  = explode('.', $ipaddr);
            $diff = array_diff_assoc($arr, $arrIp);

            foreach ($diff as $item) {
                if ($item != '*') {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }
}