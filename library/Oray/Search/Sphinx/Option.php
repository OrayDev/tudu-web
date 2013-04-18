<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Search
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Option.php 7896 2011-10-12 10:28:14Z cutecube $
 */


/**
 * Sphinx Api
 *
 * @category Oray
 * @package  Oray_Search
 * @author CuTe_CuBe
 */
class Oray_Search_Sphinx_Option
{

    /**
     * Match mode
     *
     * @var int
     */
    const MATCH_ALL       = 0;
    const MATCH_ANY       = 1;
    const MATCH_PHRASE    = 2;
    const MATCH_BOOLEAN   = 3;
    const MATCH_EXTENDED  = 4;
    const MATCH_FULLSCAN  = 5;
    const MATCH_EXTENDED2 = 6;

    /**
     * Sort mode
     *
     * @var int
     */
    const SORT_RELEVANCE     = 0;
    const SORT_ATTR_DESC     = 1;
    const SORT_ATTR_ASC      = 2;
    const SORT_TIME_SEGMENTS = 3;
    const SORT_EXTENDED      = 4;
    const SORT_EXPR          = 5;

    /**
     * Filter mode
     *
     * @var int
     */
    const FILTER_VALUES     = 0;
    const FILTER_RANGE      = 1;
    const FILTER_FLOATRANGE = 2;

    /**
     * Grouping functions
     *
     * @var int
     */
    const GROUPBY_DAY       = 0;
    const GROUPBY_WEEK      = 1;
    const GROUPBY_MONTH     = 2;
    const GROUPBY_YEAR      = 3;
    const GROUPBY_ATTR      = 4;
    const GROUPBY_ATTRPAIR  = 5;

    /**
     *
     * @var int
     */
    const RANK_PROXIMITY_BM25 = 0;
    const RANK_BM25           = 1;
    const RANK_NON            = 2;
    const RANK_WORDCOUNT      = 3;
    const RANK_PROXIMITY      = 4;
    const RANK_MATCHANY       = 5;
    const RANK_FIELDMASK      = 6;

    /**
     *
     * @var array
     */
    protected $_option = array(
        'limit'         => 20,
        'offset'        => 0,
        'mode'          => self::MATCH_ALL,         // 匹配模式
        'weights'       => array(),                 // 权重设置
        'sort'          => self::SORT_RELEVANCE,    // 排序模式
        'sortby'        => '',                      // 排序设置
        'minid'         => 0,                       // 检索范围，最小ID
        'maxid'         => 0,                       // 最大ID
        'filters'       => array(),                 // 过滤规则
        'groupby'       => '',                      // 分组设置
        'groupfunc'     => self::GROUPBY_DAY,       // 分组方式
        'groupdistinct' => '',                      //
        'groupsort'     => '@group desc',
        'maxmatches'    => 1000,                    // 最大匹配
        'cutoff'        => 0,                       // 中止
        'anchor'        => array(),                 // 锚
        'indexweights'  => array(),                 // 索引权重
        'ranker'        => self::RANK_PROXIMITY_BM25,// 等级排列
        'fieldweights'  => array(),                 //
        'overrides'     => array(),
        'select'        => '*',
        'retrycount'    => 0,
        'retrydelay'    => 0,
        'maxquerytime'  => 0
    );

    /**
     * Construct
     *
     * @param $option
     */
    public function __construct(array $option = null)
    {
        if ($option) {
            $this->_option = array_merge($this->_option, $option);
        }
    }

    /**
     *
     * @param $query
     * @param $index
     * @param $comment
     * @return string
     */
    public function buildSearchRequest($query, $index = "*", $comment = "")
    {
        $ret = pack(
            "NNNNN",
            $this->_option['offset'],
            $this->_option['limit'],
            $this->_option['mode'],
            $this->_option['ranker'],
            $this->_option['sort']
        );
        $ret .= pack("N", strlen($this->_option['sortby'])) . $this->_option['sortby'];
        $ret .= pack("N", strlen($query)) . $query;

        $ret .= pack("N", count($this->_option['weights']));
        foreach ($this->_option['weights'] as $weight) {
            $ret .= pack("N", (int) $weight);
        }

        $ret .= pack("N", strlen($index)) . $index;
        $ret .= pack("N", 1);
        $ret .= Oray_Search_Sphinx_Client::packU64($this->_option['minid'])
              . Oray_Search_Sphinx_Client::packU64($this->_option['maxid']);

        $ret .= pack("N", count($this->_option['filters']));
        foreach ($this->_option['filters'] as $filter) {
            $ret .= pack("N", strlen($filter['attr'])) . $filter['attr'];
            $ret .= pack("N", strlen($filter['type']));

            switch ($filter['type']) {
                case self::FILTER_VALUES:
                    $ret .= pack("N", count($filter['values']));
                    foreach ($filter['values'] as $value) {
                        $ret .= Oray_Search_Sphinx_Client::packI64($value);
                    }
                    break;
                case self::FILTER_RANGE:
                    $ret .= Oray_Search_Sphinx_Client::packI64($filter['min'])
                          . Oray_Search_Sphinx_Client::packI64($filter['max']);
                    break;
                case self::FILTER_FLOATRANGE:
                    $ret .= Oray_Search_Sphinx_Client::packFloat($filter['min'])
                          . Oray_Search_Sphinx_Client::packFloat($filter['max']);
                    break;
                default:
                    require_once 'Oray/Search/Sphinx/Exception.php';
                    throw new Oray_Search_Sphinx_Exception('Undefined filter type');
            }

            $ret .= pack("N", $filter['exclude']);
        }

        $ret .= pack("NN", $this->_option['groupfunc'], strlen($this->_option['groupby'])) . $this->_option['groupby'];
        $ret .= pack("N", $this->_option['maxmatches']);
        $ret .= pack("N", strlen($this->_option['groupsort'])) . $this->_option['groupsort'];
        $ret .= pack("NNN", $this->_option['cutoff'], $this->_option['retrycount'], $this->_option['retrydelay']);
        $ret .= pack("N", strlen($this->_option['groupdistinct'])) . $this->_option['groupdistinct'];

        if (empty($this->_option['anchor'])) {
            $ret .= pack("N", 0);
        } else {
            $a = $this->_option['anchor'];
            $ret .= pack("N", 1);
            $ret .= pack("N", strlen($a['attrlat'])) . $a['attrlat'];
            $ret .= pack("N", strlen($a['attrlong'])) . $a['attrlong'];
            $ret .= Oray_Search_Sphinx_Client::packFloat($a['lat'])
                  . Oray_Search_Sphinx_Client::packFloat($a['long']);
        }

        $ret .= pack("N", count($this->_option['indexweights']));
        foreach ($this->_option['indexweights'] as $idx => $weight) {
            $ret .= pack("N", strlen($idx)) . $idx . pack("N", $weight);
        }

        $ret .= pack("N", $this->_option['maxquerytime']);

        $ret .= pack("N", count($this->_option['fieldweights']));
        foreach ($this->_option['fieldweights'] as $field => $weight) {
            $ret .= pack("N", strlen($field)) . $field . pack("N", $weight);
        }

        $ret .= pack("N", strlen($comment)) . $comment;

        $ret .= pack("N", count($this->_option['overrides']));
        foreach ($this->_option['overrides'] as $key => $entry) {
            $ret .= pack("N", strlen($entry['attr'])) . $entry['attr'];
            $ret .= pack("NN", $entry['type'], count($entry['values']));

            foreach ($entry['values'] as $id => $val) {
                $ret .= Oray_Search_Sphinx_Client::packU64($id);

                switch ($entry['type']) {
                    case Oray_Search_Sphinx_Client::ATTR_FLOAT:
                        $ret .= Oray_Search_Sphinx_Client::packFloat($val);
                        break;
                    case Oray_Search_Sphinx_Client::ATTR_BIGINT:
                        $ret .= Oray_Search_Sphinx_Client::packI64($val);
                        break;
                    default:
                        $ret .= pack("N", $val);
                        break;
                }
            }
        }

        $ret .= pack("N", strlen($this->_option['select'])) . $this->_option['select'];

        return $ret;
    }

    /**
     * 添加过滤
     *
     * @param string $attr
     * @param mixed  $values
     * @return Oray_Search_Sphinx_Option
     */
    public function addFilter($attr, $values, $exclude = false)
    {
        $this->_option['filters'][] = array(
            'attr' => $attr,
            'type' => self::FILTER_VALUES,
            'values' => $values,
            'exclude' => $exclude
        );

        return $this;
    }

    /**
     * 添加范围过滤
     *
     * @param string $attr
     * @param int | float $min
     * @param int | float $max
     * @param int $type
     * @param boolean $exclude
     * @return Oray_Search_Sphinx_Option
     */
    public function addRangeFilter($attr, $min, $max, $type = self::FILTER_RANGE, $exclude = false)
    {
        if (!in_array($type, array(self::FILTER_RANGE, self::FILTER_FLOATRANGE))) {
            require_once 'Oray/Search/Sphinx/Exception.php';
            throw new Oray_Search_Sphinx_Exception("Invalid type for range filter in query option");
        }

        $this->_option['filters'][] = array(
            'attr' => $attr,
            'type' => $type,
            'min'  => $min,
            'max'  => $max,
            'exclude' => $exclude
        );

        return $this;
    }

    /**
     * 添加锚
     *
     * @param $anchor
     * @return Oray_Search_Sphinx_Option
     */
    public function addAnchor(array $anchor)
    {

    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_option;
    }

    /**
     *
     * @param $name
     * @param $args
     */
    public function __call($name, $args)
    {
        $act = substr($name, 0, 3);
        $key = strtolower(substr($name, 3));

        if (!array_key_exists($key, $this->_option)) {
            throw new Exception('Invalid property: ' . $key);
        }

        if ($act == 'get') {
            if (isset($this->_option[$key])) {
                return $this->_option[$key];
            }
            return null;

        } elseif ($act == 'set') {
            $this->_option[$key] = $args[0];
            return $this;
        }
    }
}