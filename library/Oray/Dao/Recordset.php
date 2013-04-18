<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Recordset.php 6776 2011-07-06 05:41:55Z gxx $
 */

/**
 * @category   Oray
 * @package    Oray_Dao
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Dao_Recordset implements Countable, Iterator
{
    const RECORD_CLASS = 'recordClass';
    const RECORD_COUNT = 'recordCount';
    const PAGE_SIZE    = 'pageSize';
    const PAGE_COUNT   = 'pageCount';
    const CURRENT_PAGE = 'currentPage';

    /**
     * The original data for each record.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * The current array key
     *
     * @var maxed
     */
    protected $_key = null;

    /**
     * Oray_Dao_Record class name.
     *
     * @var string
     */
    protected $_recordClass = 'Oray_Dao_Record';

    /**
     * Number of elements in records
     *
     * @var integer
     */
    protected $_count;

    /**
     * @var integer
     */
    protected $_recordCount;

    /**
     * @var integer
     */
    protected $_pageSize;

    /**
     * @var integer
     */
    protected $_pageCount;

    /**
     * @var integer
     */
    protected $_currentPage;

    /**
     * Collection of instantiated Oray_Dao_Record objects.
     *
     * @var array
     */
    private $_records = array();

    /**
     * 记录集构造
     *
     * @param array $data
     * @param mixed $config
     */
    public function __construct(array $data = array(), $config = null)
    {
        // 分页设置参数，必须包含所需的分页参数
        if (is_array($config)) {
            $exception = null;
            do {
                if (!isset($config[self::RECORD_COUNT])) {
                    $exception = "Missing config 'recordCount'";
                    break;
                }
                if (!isset($config[self::PAGE_COUNT])) {
                    $exception = "Missing config 'pageCount'";
                    break;
                }
                if (!isset($config[self::PAGE_SIZE])) {
                    $exception = "Missing config 'pageSize'";
                    break;
                }
                if (!isset($config[self::CURRENT_PAGE])) {
                    $exception = "Missing config 'currentPage'";
                    break;
                }
            } while (false);

            if (null !== $exception) {
                require_once 'Oray/Dao/Exception.php';
                throw new Oray_Dao_Exception($exception);
            }

            if (isset($config[self::RECORD_CLASS])) {
                $this->_recordClass = $config[self::RECORD_CLASS];
            }

            $this->_recordCount = (int) $config[self::RECORD_COUNT];
            $this->_pageSize    = (int) $config[self::PAGE_SIZE];
            $this->_currentPage = (int) $config[self::CURRENT_PAGE];
            $this->_pageCount   = (int) $config[self::PAGE_COUNT];
        }

        // 仅设置Oray_Dao_Record
        if (is_string($config)) {
            $this->_recordClass = $config;
        }

        if (!class_exists($this->_recordClass)) {
            /**
             * @see Zend_Loader
             */
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($this->_recordClass);
        }

        $this->_data = $data;
        $this->_count = count($this->_data);

        // Rewind array back to the start
        $this->rewind();
    }

    /**
     * 返回记录总数
     *
     * @return integer
     */
    public function recordCount()
    {
        return $this->_recordCount;
    }

    /**
     * 返回分页大小
     *
     * @return integer
     */
    public function pageSize()
    {
        return $this->_pageSize;
    }

    /**
     * 返回分页大小
     *
     * @return integer
     */
    public function pageCount()
    {
        if (!$this->_pageCount) {
            $this->_pageCount = $this->_calculatePageCount();
        }
        return $this->_pageCount;
    }

    /**
     * 返回当前页数
     *
     * @return integer
     */
    public function currentPage()
    {
        return $this->_currentPage;
    }

    /**
     * 计算分页数
     *
     * @return integer
     */
    private function _calculatePageCount()
    {
        if (!$this->_recordCount || !$this->_pageSize) {
            return 0;
        }
        return (integer) ceil($this->_recordCount / $this->_pageSize);
    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return Oray_Dao_Resultset
     */
    public function rewind()
    {
        reset($this->_data);
        $this->_key = key($this->_data);
        return $this;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Oray_Dao_Record
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        return $this->get($this->key());
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return maxied
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function next()
    {
        next($this->_data);
        $this->_key = key($this->_data);
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    public function valid()
    {
        return null !== $this->_key;
    }

    /**
     * Returns the number of elements in the collection.
     *
     * Implements Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * 获取指定索引的数据
     *
     * @param int $index
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            return null;
        }

        // do we already have a row object for this position?
        if (!array_key_exists($key, $this->_records)) {
            $this->_records[$key] = new $this->_recordClass(
                $this->_data[$key]
            );
        }

        // return the record object
        return $this->_records[$key];
    }

    /**
     * 转成数组输出
     *
     * @param string $indexColumn
     * @return array
     */
    public function toArray($indexColumn = null)
    {
        $records = array();
        $this->rewind();
        while ($this->valid()) {
            $record = $this->current();
            if ($record instanceof Oray_Dao_Record) {
                $record = $record->toArray();
            }
            if (null === $indexColumn) {
                $records[] = $record;
            } else {
                $records[$record[$indexColumn]] = $record;
            }
            $this->next();
        }
        return $records;
    }

    /**
     * call when object unserialize
     * make recordset serializable
     *
     */
    public function __wakeup()
    {
        if (!class_exists($this->_recordClass)) {
            /**
             * @see Zend_Loader
             */
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($this->_recordClass);
        }
    }
}