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
 * @version    $Id: Template.php 911 2011-06-16 07:27:40Z cutecube $
 */

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_Td_Tudu_Template extends Oray_Dao_Abstract
{
    
    /**
     * 获取一条模板记录
     * 
     * @param $condition
     * @param $filter
     * @return Dao_Td_Tudu_Template
     */
    public function getTemplate(array $condition, $filter = null)
    {
        $table   = 'td_template';
        $columns = 'org_id AS orgid, board_id AS boardid, template_id AS templateid, creator, name, content, order_num AS ordernum';
        $where   = array();
        
        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (isset($condition['boardid'])) {
            $where[] = 'board_id = ' . $this->_db->quote($condition['boardid']);
        }
        
        if (isset($condition['templateid'])) {
            $where[] = 'template_id = ' . $this->_db->quote($condition['templateid']);
        }
        
        if (!$where) {
            return null;
        }
        
        // WHERE
        $where = implode(' AND ', $where);
        
        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";
        
        try {
            $record = $this->_db->fetchRow($sql);
            
            if (!$record) {
                return null;
            }
            
            return Oray_Dao::record('Dao_Td_Tudu_Record_Template', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return null;
        }
    }
    
    /**
     * 获取模板列表
     * 
     * @param $conditions
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getTemplates(array $condition, $filter = null, $sort = null, $maxCount = null)
    {
        $table   = 'td_template';
        $columns = 'org_id AS orgid, board_id AS boardid, template_id AS templateid, creator, name, content, order_num AS ordernum';
        $where   = array();
        $order   = array();
        $limit   = '';
        
        if (isset($condition['orgid'])) {
            $where[] = 'org_id = ' . $this->_db->quote($condition['orgid']);
        }
        
        if (isset($condition['boardid'])) {
            $where[] = 'board_id = ' . $this->_db->quote($condition['boardid']);
        }
        
        if (!$where) {
            return new Oray_Dao_Recordset();
        }
        
        // WHERE
        $where = implode(' AND ', $where);
        if ($where) {
            $where = 'WHERE ' . $where;
        }
        
        $sort  = $this->_formatSort($sort);
        foreach ($sort as $key => $val) {
            switch ($key) {
                case 'ordernum':
                    $key = 'order_num';
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
        
        $sql = "SELECT {$columns} FROM {$table} {$where} {$order} {$limit}";
        
        try {
            $records = $this->_db->fetchAll($sql);
            
            return new Oray_Dao_Recordset($records, 'Dao_Td_Tudu_Record_Template');
        } catch (Zend_Db_Exception $e) {
            $this->_catchExecption($e, __METHOD__);
            return new Oray_Dao_Recordset();
        }
    }
    
    /**
     * getTemplate的快捷调用方式
     * 
     * @param $boardId
     * @param $filter
     * @param $sort
     * @param $maxCount
     */
    public function getTemplatesByBoardId($orgId, $boardId, $filter = null, $sort = null, $maxCount = null)
    {
        return $this->getTemplates(array(
            'orgid'   => $orgId,
            'boardid' => $boardId
        ), $filter, $sort, $maxCount);
    }
    
    /**
     * 创建模板
     * 
     * @param $params
     * @return boolean
     */
    public function createTemplate(array $params)
    {
        if (empty($params['orgid']) 
           || empty($params['boardid']) 
           || empty($params['templateid'])
           || empty($params['name'])) 
        {
            return false;
        }
        
        $table = 'td_template';
        $bind  = array(
            'org_id'      => $params['orgid'],
            'board_id'    => $params['boardid'],
            'template_id' => $params['templateid'],
            'name'        => $params['name'],
        	'creator'     => $params['creator']
        );
        
        if (!empty($params['content'])) {
            $bind['content'] = $params['content'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$bind['order_num'] = $params['ordernum'];
		}
        
        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return $params['templateid'];
    }
    
    /**
     * 更新模板数据
     * 
     * @param $templateId
     * @param $boardId
     * @param $params
     * @return boolean
     */
    public function updateTemplate($templateId, $boardId, array $params)
    {
        if (empty($templateId) || empty($boardId)) {
            return false;
        }
        
        $table = 'td_template';
        $bind  = array();
        $where = 'template_id = ' . $this->_db->quote($templateId) . ' AND '
               . 'board_id = ' . $this->_db->quote($boardId);
        
        if (!empty($params['content'])) {
            $bind['content'] = $params['content'];
        }
        
        if (!empty($params['name'])) {
            $bind['name'] = $params['name'];
        }
        
        if (isset($params['ordernum']) && is_int($params['ordernum'])) {
			$bind['order_num'] = $params['ordernum'];
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
     * 删除模板
     * 
     * @param $templateId
     * @param $boardId
     * @return boolean
     */
    public function deleteTemplate($templateId, $boardId)
    {
        if (empty($templateId) || empty($boardId)) {
            return false;
        }
        
        $sql = 'DELETE FROM td_template WHERe template_id = ' . $this->_db->quote($templateId) . ' AND '
             . 'board_id = ' . $this->_db->quote($boardId);
        
        try {
            $this->_db->query($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取模板ID
     * 
     * 生成规则：微秒级时间戳转16位 + 0xfffff最大值随机数转16位
     * 格式如 129fcd77e2043a86，类似gmail生成格式
     * 
     * @return string
     */
    public static function getTemplateId()
    {
        return base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
}