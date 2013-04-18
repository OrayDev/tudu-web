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
 * @version    $Id: Cycle.php 1353 2011-12-06 10:18:49Z cutecube $
 */

/**
 * @see Oray_Dao_Abstract
 */
require_once 'Oray/Dao/Abstract.php';

/**
 * @category   Dao
 * @package    Dao_Td
 * @subpackage Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
 class Dao_Td_Tudu_Flow extends Oray_Dao_Abstract
 {
     /**
      *
      * @var int
      */
     const STEP_TYPE_EXECUTE = 0;
     const STEP_TYPE_EXAMINE = 1;
     const STEP_TYPE_CLAIM   = 2;

    /**
    *
    * @param array $condition
    * @param array $filter
    * @return Dao_Td_Record_Flow
    */
    public function getFlow(array $condition, $filter = null)
    {
        $table   = 'td_tudu_flow';
        $columns = 'org_id as orgid, tudu_id as tuduid, flow_id as flowid, current_step_id as currentstepid, steps';
        $where   = array();
        $bind    = array();

        if (isset($condition['tuduid'])) {
            $where[] = 'tudu_id = :tuduid';
            $bind['tuduid'] = $condition['tuduid'];
        }

        if (empty($where)) {
            return null;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT {$columns} FROM {$table} WHERE {$where} LIMIT 1";

        try {
            $record = $this->_db->fetchRow($sql, $bind);

            if (!$record) {
                return null;
            }

            return Oray_Dao::record('Dao_Td_Tudu_Record_Flow', $record);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return null;
        }
    }

    /**
    *
    * @param array $params
    * @return boolean
    */
    public function createFlow(array $params)
    {
        if (empty($params['orgid'])
            || empty($params['tuduid']))
        {
            return false;
        }

        $table = 'td_tudu_flow';
        $bind  = array(
            'org_id'  => $params['orgid'],
            'tudu_id' => $params['tuduid']
        );

        if (!empty($params['flowid'])) {
            $bind['flow_id'] = $params['flowid'];
        }

        if (!empty($params['steps']) && is_array($params['steps'])) {
            $bind['steps'] = self::formatSteps($params['steps']);
        }

        if (!empty($params['currentstepid'])) {
            $bind['current_step_id'] = $params['currentstepid'];
        }

        if (isset($params['stepnum'])) {
            $bind['step_num'] = (int) $params['stepnum'];
        }

        try {
            $this->_db->insert($table, $bind);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return false;
        }

        return true;
    }

    /**
    *
    * @param array $params
    * @return boolean
    */
    public function updateFlow($tuduId, array $params)
    {
        $table = 'td_tudu_flow';
        $bind  = array();
        $where = 'tudu_id = ' . $this->_db->quote($tuduId);

        if (!empty($params['steps']) && is_array($params['steps'])) {
            $bind['steps'] = self::formatSteps($params['steps']);
        }

        if (!empty($params['currentstepid'])) {
            $bind['current_step_id'] = $params['currentstepid'];
        }

        if (isset($params['stepnum'])) {
            $bind['step_num'] = (int) $params['stepnum'];
        }

        if (empty($bind)) {
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
    *
    * @param string $steps
    * @return array
    */
    public static function parseSteps($steps)
    {
        $ret = @json_decode($steps, true);

        if (false == $ret) {
            return array();
        }

        return $ret;
    }

    /**
    *
    * @param array $steps
    * @return string
    */
    public static function formatSteps(array $steps)
    {
        $ret = json_encode($steps);

        return $ret;
    }

    /**
     *
     * @param string $tuduId
     */
    public static function getStepId($tuduId)
    {
        return 'ST-' . base_convert(strrev(time()) . rand(0, 999), 10, 32);
    }
 }