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
 * @version    $Id: Post.php 2758 2013-02-27 06:15:56Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Post extends Oray_Dao_Abstract
{
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {}

    /**
     * Get record
     *
     * SELECT org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, tudu_id AS tuduid, post_id AS postid,
     * is_first AS isfirst, is_log AS islog, is_send AS issend, content, percnet, last_modify AS lastmodify,
     * poster, poster_info AS post_info, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime
     * WHERE tudu_id = ? AND post_id = ?
     *
     * @param array $condition
     * @param array $filter
     * @return Dao_Td_Tudu_Record_Post
     */
    public function getPost(array $condition, $filter = null)
    {
        $table   = "td_post";
        $columns = "org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, email, tudu_id AS tuduid, post_id AS postid, header, "
                 . "is_first AS isfirst, is_log AS islog, is_send AS issend, content, percent, last_modify AS lastmodify, "
                 . "poster, poster_info AS posterinfo, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime, "
                 . "is_foreign AS isforeign";
        $where   = array();

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (isset($condition['postid'])) {
            $where[] = 'post_id = ' . $this->_db->quote($condition['postid']);
        }

        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['issend'])) {
            $where[] = 'is_send = ' . $this->_db->quote($condition['issend']);
        }

        if (empty($where)) {
            return null;
        }

        // WHERE
        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 0, 1";

        $record = $this->_db->fetchRow($sql);

        if (!$record) {
            return null;
        }

        return Oray_Dao::record('Dao_Td_Tudu_Record_Post', $record);
    }

    /**
     *
     * @param $condition
     * @param $filter
     * @param $sort
     * @param $maxCount
     * @return Oray_Dao_Recordset
     */
    public function getPosts(array $condition = null, $filter = null, $sort = null, $maxCount = null)
    {
        $table    = 'td_post';
        $columns  = "org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, email, tudu_id AS tuduid, post_id AS postid, header, "
                  . "is_first AS isfirst, is_log AS islog, is_send AS issend, content, percent, last_modify AS lastmodify, "
                  . "poster, poster_info AS posterinfo, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime, "
                  . "is_foreign AS isforeign";
        $primary  = "postid";
        $recordClass = "Dao_Td_Tudu_Record_Post";
        $where = array();
        $order = array();
        $limit = '';

        // $condition...
        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // is_first排序写死
        $order[] = 'is_first DESC';

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
                    break;
                default:
                    continue 2;
                    break;
            }
            $order[] = $key . ' ' . $val;
        }

        $order = 'ORDER BY ' . implode(', ', $order);

        if (is_int($maxCount) && $maxCount > 0) {
            $limit = 'LIMIT ' . $maxCount;
        }

        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     *
     * @param array $condition
     * @param mixed $sort
     * @param int   $offset
     * @param int   $count
     */
    public function getTuduPosts(array $condition, $sort = null, $offset = null, $count = null)
    {
        $table    = 'td_post';
        $columns  = "org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, email, tudu_id AS tuduid, post_id AS postid, header, "
                  . "is_first AS isfirst, is_log AS islog, is_send AS issend, content, percent, last_modify AS lastmodify, "
                  . "poster, poster_info AS posterinfo, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime, "
                  . "is_foreign AS isforeign";
        $primary  = "postid";
        $recordClass = "Dao_Td_Tudu_Record_Post";
        $where = array();
        $order = array();
        $limit = '';

        // $condition...
        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        if (isset($condition['isfirst'])) {
            $where[] = 'is_first = ' . (int) $condition['isfirst'];
        }

        $where[] = 'is_send = 1';

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // is_first排序写死
        $order[] = 'is_first DESC';

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
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

        if (null !== $count) {
            $limit = 'LIMIT';
            if (null !== $offset) {
                $limit .= ' ' . (int) $offset . ',';
            }

            $limit .= ' ' . (int) $count;
        }

        $sql = "SELECT $columns FROM $table $where $order $limit";

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     * Get record page
     *
     * SELECT
     * org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, tudu_id AS tuduid, post_id AS postid,
     * is_first AS isfirst, is_log AS islog, is_send AS issend, content, percent, last_modify AS lastmodify,
     * poster, poster_info AS posterinfo, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime"
     * FROM td_post ORDER BY is_first DESC, create_time ASC
     *
     * @param array $condition
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getPostPage(array $condition = null, $sort = null, $page = null, $pageSize = null)
    {
        $table    = 'td_post';
        $columns  = "org_id AS orgid, board_id AS boardid, unique_id AS uniqueid, email, tudu_id AS tuduid, post_id AS postid, header, "
                  . "is_first AS isfirst, is_log AS islog, is_send AS issend, content, percent, last_modify AS lastmodify, "
                  . "poster, poster_info AS posterinfo, attach_num AS attachnum, elapsed_time AS elapsedtime, create_time AS createtime, "
                  . "is_foreign AS isforeign";
        $primary  = "postid";
        $recordClass = "Dao_Td_Tudu_Record_Post";
        $where = array();
        $order = array();

        // $condition...
        if (isset($condition['uniqueid'])) {
            $where[] = 'unique_id = ' . $this->_db->quote($condition['uniqueid']);
        }

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = ' . $this->_db->quote($condition['tuduid']);
        }

        $where[] = 'is_send = 1';

        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }

        // is_first排序写死
        $order[] = 'is_first DESC';

        // 格式化排序参数
        $sort = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'createtime':
                    $key = 'create_time';
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

        // 使用默认的分页大小
        if (null === $pageSize) {
            $pageSize = self::$_defaultPageSize;
        }

        if ($page < 1) $page = 1;

        $sql = "SELECT $columns FROM $table $where $order LIMIT " . $pageSize * ($page - 1) . ", " . $pageSize;

        $records = $this->_db->fetchAll($sql);

        return new Oray_Dao_Recordset($records, $recordClass);
    }

    /**
     * 获取回复数
     *
     * @param $tuduId
     * @param $uniqueId
     * @return int
     */
    public function getPostCount($tuduId, $uniqueId)
    {
        $sql = 'SELECT COUNT(tudu_id) FROM td_post'
             . ' WHERE tudu_id = ' . $this->_db->quote($tuduId)
             . ' AND unique_id = ' . $this->_db->quote($uniqueId);
        return (int) $this->_db->fetchOne($sql);
    }

    /**
     * Get post
     *
     * @param string $tuduId
     * @param mixed $sort
     * @param int $page
     * @param int $pageSize
     * @return Oray_Dao_Recordset
     */
    public function getPostPageByTuduId($tuduId, $sort = null, $page = null, $pageSize = null)
    {
        return $this->getPostPage(array('tuduid' => $tuduId), $sort, $page, $pageSize);
    }

    /**
     * Create post
     *
     * @param $params
     * @return string|false
     */
    public function createPost(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['boardid'])
            || empty($params['tuduid'])
            || empty($params['uniqueid'])
            || empty($params['postid'])
            || empty($params['poster'])) {
            return false;
        }

        $createTime = empty($params['createtime']) ? time() : (int) $params['createtime'];

        $table = "td_post";
        $bind = array(
            'org_id'      => $params['orgid'],
            'board_id'    => $params['boardid'],
            'tudu_id'     => $params['tuduid'],
            'unique_id'   => $params['uniqueid'],
            'email'       => $params['email'],
            'post_id'     => $params['postid'],
            'poster'      => $params['poster'],
            'email'       => !empty($params['email']) ? $params['email'] : null,
            'content'     => !empty($params['content']) ? $params['content'] : '',
            'is_first'    => empty($params['isfirst']) ? 0 : 1,
            'is_log'      => empty($params['islog']) ? 0 : 1,
            'is_foreign'  => empty($params['isforeign']) ? 0 : 1,
            'attach_num'  => empty($params['attachnum']) ? 0 : (int) $params['attachnum'],
            'elapsed_time' => empty($params['elapsedtime']) ? 0 : (int) $params['elapsedtime'],
            'create_time' => $createTime
            );

        if (isset($params['posterinfo'])) {
            $bind['poster_info'] = $params['posterinfo'];
        }

        if (isset($params['header'])) {
            $bind['header'] = self::formatHeader($params['header']);
        }

        if (isset($params['percent']) && is_int($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }

        if (isset($params['issend'])) {
            $bind['is_send']  = $params['issend'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return $params['postid'];
    }

    /**
     * Update tudu
     *
     * @param string $tuduId
     * @param string $postId
     * @param array $params
     * @return boolean
     */
    public function updatePost($tuduId, $postId, array $params)
    {
        if (empty($tuduId) || empty($postId)) {
            return false;
        }

        $table = "td_post";
        $bind  = array();
        $where = "tudu_id = " . $this->_db->quote($tuduId)
               . " AND post_id = " . $this->_db->quote($postId);

        if (isset($params['content'])) {
            $bind['content'] = $params['content'];
        }
        if (isset($params['attachnum'])) {
            $bind['attach_num'] = (int) $params['attachnum'];
        }
        if (isset($params['elapsedtime'])) {
            $bind['elapsed_time'] = (int) $params['elapsedtime'];
        }
        if (isset($params['lastmodify'])) {
            $bind['last_modify'] = $params['lastmodify'];
        }
        if (isset($params['createtime'])) {
            $bind['create_time'] = $params['createtime'];
        }
        if (isset($params['islog'])) {
            $bind['is_log'] = $params['islog'] ? 1 : 0;
        }
        if (isset($params['percent']) && is_int($params['percent'])) {
            $bind['percent'] = $params['percent'];
        }
        if (isset($params['issend'])) {
            $bind['is_send']  = $params['issend'];
        }
        if (array_key_exists('header', $params)) {
            $bind['header'] = self::formatHeader($params['header']);
        }

        if (!$bind) {
            return false;
        }

        try {
            $this->_db->update($table, $bind, $where);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 更新附件数量
     *
     * @param $tuduId
     * @param $postId
     */
    public function updateAttachNum($tuduId, $postId)
    {
        $sql = 'UPDATE td_post SET attach_num = (SELECT COUNT(*) FROM td_attach_post WHERE tudu_id = td_post.tudu_id AND post_id = td_post.post_id AND is_attach = 1) '
             . 'WHERE tudu_id = ' . $this->_db->quote($tuduId) .  ' AND post_id = ' . $this->_db->quote($postId) ;

         try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }


    /**
     * Delete post
     *
     * @param string $tuduId
     * @param string $postId
     * @return boolean
     */
    public function deletePost($tuduId, $postId)
    {
        $sql = "call sp_td_delete_post(" . $this->_db->quote($tuduId) . ", " . $this->_db->quote($postId) . ")";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * Send post
     *
     * @param string $tuduId
     * @param string $postId
     * @return boolean
     */
    public function sendPost($tuduId, $postId)
    {
        $sql = "call sp_td_send_post(" . $this->_db->quote($tuduId) . ", " . $this->_db->quote($postId) . ")";

        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        return true;
    }

    /**
     * 统计
     *
     * @param string $uniqueId
     * @param string $labelId
     */
    public function calculatePost($uniqueId, $labelId)
    {
        $sql = "call sp_td_calculate_post(" . $this->_db->quote($uniqueId) . "," . $this->_db->quote($labelId) . ")";
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
     * 获取回复ID
     *
     * @param string $tuduId
     * @return string
     */
    public static function getPostId($tuduId = null)
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }

    /**
     *
     * @param array $header
     */
    public static function formatHeader($header)
    {
        if (is_string($header)) {
            return $header;
        }

        $sb = array();
        foreach ($header as $key => $val) {
            $sb[] = $key . ':' . str_replace(':', '%3A', $val);
        }

        return implode("\n", $sb);
    }

    /**
     *
     * @param string $header
     * @return array
     */
    public static function praseHeader($header)
    {
        if (!is_string($header)) {
            if (is_array($header)) {
                return $header;
            }

            return null;
        }

        if (empty($header)) {
            return null;
        }

        $arr = explode("\n", $header);
        $ret = array();
        foreach ($arr as $row) {
            if (!strpos($row, ':')) {
                continue ;
            }

            list($key, $val) = explode(':', $row, 2);

            $ret[$key] = str_replace('%3A', ':', $val);
        }

        return $ret;
    }
}