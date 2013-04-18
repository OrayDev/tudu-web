<?php
/**
 * Task_Httpsqs
 *
 * LICENSE
 *
 *
 * @category   Task_Httpsqs_Im
 * @package    Task_Httpsqs_Im
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Im.php 2491 2012-12-11 11:04:42Z cutecube $
 */

/**
 * Talk、Im通知（用户、组织架构、图度与回复的通知）
 *
 * @category   Task_Httpsqs_Im
 * @package    Task_Httpsqs_Im
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Httpsqs_Im extends Task_Abstract
{
	const USER_MODULE       = 'user';
	const DEPARTMENT_MODULE = 'dept';
	const CAST_MODULE       = 'cast';
	const TUDU_MODULE       = 'tudu';
	const BOARD_MODULE      = 'board';
	const GROUP_MODULE      = 'group';

	/**
	 *
	 * @var array
	 */
	static $_supportModule = array(
        self::USER_MODULE,
        self::DEPARTMENT_MODULE,
        self::CAST_MODULE,
        self::TUDU_MODULE,
        self::GROUP_MODULE,
        self::BOARD_MODULE
    );

    /**
     *
     * @var Oray_Httpsqs
     */
    protected $_httpsqs = null;

    /**
     *
     * @var Oray_Memcache
     */
    protected $_memcache = null;

    /**
     *
     * @var Oray_Im_Client
     */
    protected $_im = null;

    /**
     *
     * @var array
     */
    protected $_tsDbs = array();

    /**
     *
     */
    public function startUp()
    {
    	foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_tsDbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
                continue ;
            }
            Tudu_Dao_Manager::setDb($key, Zend_Db::factory($item['adapter'], $item['params']));
        }

        $this->_memcache = new Oray_Memcache(array(
            'compression'   => $this->_options['memcache']['compression'],
            'compatibility' => $this->_options['memcache']['compatibility']
        ));

        $this->_memcache->addServer(
            $this->_options['memcache']['host'],
            $this->_options['memcache']['port']
        );

        $this->_httpsqs = new Oray_Httpsqs(
            $this->_options['httpsqs']['host'],
            $this->_options['httpsqs']['port'],
            $this->_options['httpsqs']['charset'],
            $this->_options['httpsqs']['names']['im']
        );

        $this->_im = new Oray_Im_Client($this->_options['im']['host'], $this->_options['im']['port']);

        ini_set("memory_limit","-1");
    }

    /**
     *
     */
    public function shutDown()
    {
    	$this->_httpsqs->closeConnection();
    }

    /**
     * 执行
     */
    public function run()
    {
        do {
            $data = $this->_httpsqs->get($this->_options['httpsqs']['names']['im']);

            if (!$data || $data == 'HTTPSQS_GET_END') {
                break ;
            }

            list($module, $action, $sub, $query) = explode(' ', $data);

            if (!in_array($module, self::$_supportModule)) {
                $this->getLogger()->warn("Invalid param \"module\" values {$module}");
                break ;
            }

            switch ($module) {
            	case self::USER_MODULE:
            		$this->onUser($action, $query);
            		break;
            	case self::DEPARTMENT_MODULE:
            		$this->onDept($action, $query);
            		break;
                case self::CAST_MODULE:
                	$this->onCast($action, $query);
                    break;
                case self::TUDU_MODULE:
                	$this->onTudu($action, $query);
                    break;
                case self::GROUP_MODULE:
                case self::BOARD_MODULE:
                default:
                    $this->getLogger()->warn("Invalid param \"module\" values {$module}");
                    break;
            }

        } while (true);
    }

    /**
     * 用户模块
     *
     * @param string $action
     * @param string $query
     */
    public function onUser($action, $query)
    {
    	list($orgId) = explode(':', $query);

    	switch ($action) {
    		case 'delete':
            case 'update':
            case 'create':

            	$this->_refreshCastCache($orgId);

            	$this->_im->updateNotify(Oray_Im_Client::NOTIFY_UPDATE_DEPT, $orgId);

            	if ($action == 'delete') {
            	   $data = implode(' ', array(
                       self::USER_MODULE,
                       $action,
                       null,
                       $query
                   ));
                   $this->_httpsqs->put($data, $this->_options['httpsqs']['names']['admin']);

                   $this->_httpsqs->put(implode(' ', array(
                       'contact',
                       '',
                       null,
                       http_build_queue(array('orgid' => $orgId))
                   )), $this->_options['httpsqs']['names']['notify']);
            	}
            	break;
            default:
            	break;
    	}
    }

    /**
     * 组织部门
     *
     * @param string $action
     * @param string $query
     */
    public function onDept($action, $query)
    {
    	list($orgId, $deptId) = explode(':', $query);

    	switch ($action) {
            case 'delete':
            case 'update':
            case 'create':
            	$this->_refreshCastCache($orgId);

                $this->_im->updateNotify(Oray_Im_Client::NOTIFY_UPDATE_DEPT, $orgId);

            	break;
            default:
                break;
    	}
    }

    /**
     * 组织架构
     *
     * @param string $action
     * @param string $query
     */
    public function onCast($action, $query)
    {
    	list($orgId, $castId) = explode(':', $query);

    	switch ($action) {
            case 'delete':
            case 'update':
            case 'create':
            	$this->_im->updateNotify(Oray_Im_Client::NOTIFY_UPDATE_DEPT, $orgId);
    	        break;
            default:
                break;
        }
    }

    /**
     * 发送IM图度消息
     *
     * @param string $action
     * @param string $query
     */
    public function onTudu($action, $query)
    {
    	parse_str($query, $query);

        switch ($action) {
            case 'create':
            case 'update':
            case 'reply':
            case 'cancel':
                $from = $this->_options['im']['robot'];
                $content = $query['content'];

                foreach (explode(',', $query['to']) as $to) {
                    if (empty($to)) continue;

                    $this->_im->sendMsg($from, $to, $content, true, 'tudu');
                }
                $this->getLogger()->debug("send messge to {$query['to']}");
                break;
            default:
                break;
        }
    }

    /**
     * 刷新im服务器缓存
     *
     * @param string $orgId
     */
    protected function _refreshCastCache($orgId) {
        /* @var $db Zend_Db_Adapter_Abstract */
        $db = Tudu_Dao_Manager::getDb(Tudu_Dao_Manager::DB_MD);

        // 读取所有用户列表
        $userSql = "SELECT * FROM (("
        . "SELECT u.org_id, u.`status`, u.dept_id, u.user_id, u.unique_id, ui.true_name, ui.pinyin, ui.update_time, 0 AS usertype "
        . "FROM md_user AS u "
        . "LEFT JOIN md_user_info AS ui ON u.org_id = ui.org_id AND u.user_id = ui.user_id "
        . "WHERE u.org_id = :orgid) UNION ALL ("
        . "SELECT org_id, 1 AS `status`, '' AS dept_id, user_id, unique_id, true_name, pinyin, update_time, 1 AS usertype FROM v_cast_common_user)) AS T";

        $users = $db->fetchAll($userSql, array('orgid' => $orgId));
/*
        // 读取所有部门
        $deptSql = "SELECT org_id, dept_id, dept_name, IF(parent_dept_id = '^root', NULL, parent_dept_id) AS parent_dept_id, order_num "
                 . "FROM md_department WHERE org_id = :orgid AND dept_id <> '^root'";

        $depts = $db->fetchAll($deptSql, array('orgid' => $orgId));

        // 读取组织所有匹配规则
        $castUserSql = "SELECT org_id, owner_id, user_id FROM md_cast_disable_user WHERE org_id = :orgid";
        $castDeptSql = "SELECT org_id, owner_id, dept_id FROM md_cast_disable_dept WHERE org_id = :orgid";

        $res = $db->query($castUserSql, array('orgid' => $orgId));
        $castUsers = array();
        while (($row = $res->fetch())) {
            $castUsers[$row['owner_id']][] = $row['user_id'];
        }
        $res->closeCursor();

        $res = $db->query($castDeptSql, array('orgid' => $orgId));
        $castDepts = array();
        while (($row = $res->fetch())) {
            $castDepts[$row['owner_id']][] = $row['dept_id'];
        }
        $res->closeCursor();

        unset($res);
*/
        // 更新各用户好友列表
        foreach ($users as $user) {
            // 跳过公共用户
            if (1 == $user['usertype']) {
                continue ;
            }
/*
            $d = isset($castDepts[$user['user_id']]) ? $castDepts[$user['user_id']] : array();
            $u = isset($castUsers[$user['user_id']]) ? $castUsers[$user['user_id']] : array();

            // 更新当前用户部门列表缓存
            $contentDepts = array();

            foreach ($depts as $dept) {
                if (in_array($dept['dept_id'], $d)) {
                    continue ;
                }

                $str = "<item id='{$dept['dept_id']}' name='{$dept['dept_name']}' orgid='{$dept['org_id']}'";
                if (!empty($dept['parent_dept_id'])) {
                    $str .= " parentid='{$dept['parent_dept_id']}'";
                }
                $str .= ' />';

                $contentDepts[] = $str;
            }

            $contentDepts = '<dept>' . implode('', $contentDepts) . '</dept>';
            $this->_memcache->set('im_' . $orgId . '_' . $user['user_id'] . '_depts', $contentDepts, null, null, true);

            // 更新用户列表缓存
            $contentUsers = array();
            foreach ($users as $du) {
                if (in_array($du['user_id'], $u) && 0 == $du['usertype']) {
                    continue ;
                }

                $contentUsers[] = "<user userid='{$du['user_id']}' usertype='{$du['usertype']}' orgid='{$du['org_id']}' deptid='{$du['dept_id']}' "
                                . "name='{$du['true_name']}' host='{$du['org_id']}' updatetime='{$du['update_time']}' satus='{$du['status']}' />";
            }
            $contentUsers = '<Roster>' . implode('', $contentUsers) . '</Roster>';

            $this->_memcache->set('im_' . $orgId . '_' . $user['user_id'] . '_roster', $contentUsers, null, null, true);

            $this->_memcache->delete('TUDU-APP-ROLES-' . $user['user_id'] . '@' . $orgId);
*/

            $this->_memcache->delete('im_' . $user['org_id'] . '_' . $user['user_id'] . '_depts');
            $this->_memcache->delete('im_' . $user['org_id'] . '_' . $user['user_id'] . '_roster');
            $this->getLogger()->debug("refresh cache of {$user['user_id']}@{$orgId}");

            //unset($conentDepts, $contentUsers, $d, $u);
        }

        //unset($users, $castUsers, $castDepts, $depts);
    }
}