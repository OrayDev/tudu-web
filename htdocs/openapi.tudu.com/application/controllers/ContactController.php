<?php
/**
 * Contact
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: ContactController.php 2729 2013-01-30 02:33:32Z cutecube $
 */
class ContactController extends TuduX_Controller_OpenApi
{
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 用户联系人列表
     */
    public function listAction()
    {
        $detail    = $this->_request->getQuery('detail', true);
        $contactId = trim($this->_request->getQuery('contactid'));

        // 获取指定ID的联系人
        $userIds    = array();
        $contactIds = array();
        if (!empty($contactId)) {
            $arr = explode(',', $contactId);
            foreach ($arr as $item) {
                if (false !== strpos($item, '@')) {
                    $userIds[] = $item;
                } else {
                    $contactIds[] = $item;
                }
            }
        }

        $list = array();

        /*=============组织用户列表================*/
        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);

        $condition = array(
            'orgid' => $this->_user->orgId,
            'userid' => $this->_user->userId
        );
        $users = $daoCast->getCastUsers($condition)->toArray('username');
        $depts = $daoCast->getCastDepartments($this->_user->orgId, $this->_user->userId)->toArray('deptid');
        if (!empty($users)) {
            $count = 0;
            foreach ($users as &$user) {
                if ((!empty($contactId) && !in_array($user['username'], $userIds))
                    || ($user['username'] == 'robot@oray'))
                {
                    continue ;
                }

                $item = array(
                    'contactid'      => $user['username'],
                    'lastupdatetime' => isset($user['lastupdatetime']) ? $user['lastupdatetime'] : null,
                    'avatarupdatetime' => isset($user['updatetime']) ? $user['updatetime'] : null
                );

                if ($detail) {
                    $deptId = $this->_getDeptRoot($user['deptid'], $depts);

                    $item['truename'] = $user['truename'];
                    $item['position'] = !empty($user['position']) ? $user['position'] : null;
                    $item['gender']   = (int) $user['gender'];
                    $item['groups']   = $deptId ? array($deptId) : array('^none');
                    $item['mobile']   = $user['mobile'];
                    $item['tel']      = $user['tel'];
                    $item['email']    = $user['username'];
                    $item['isavatars']= $user['isavatars'];
                    $item['status']   = $user['status'];
                }

                $list[] = $item;
                //unset($user);
                $user['num'] = $count;
                $count ++;
            }
        }

        /*=============用户联系人列表================*/
        /* @var $daoContact Dao_Td_Contact_Contact */
        $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);

        $condition = array(
            'uniqueid'  => $this->_user->uniqueid
        );
        $contacts = $daoContact->getContacts($condition);
        if (!empty($contacts)) {
            $contacts = $contacts->toArray();
            foreach ($contacts as &$contact) {
                if (!empty($contactId)
                    && !in_array($contact['contactid'], $contactIds)
                    && !in_array($contact['email'], $userIds))
                {
                    continue ;
                }

                if ($contact['fromuser'] || (!empty($contact['email']) && !Oray_Function::isEmail($contact['email']))) {
                    /*if ($detail) {
                        $list[$users[$contact['email']]['num']]['groups'] = array_merge($list[$users[$contact['email']]['num']]['groups'], $contact['groups']);
                    }*/
                    continue ;
                }

                $item = array(
                    'contactid'      => $contact['contactid'],
                    'lastupdatetime' => !empty($contact['lastupdatetime']) ? (int) $contact['lastupdatetime'] : time(),
                    'avatarupdatetime' => isset($contact['lastupdatetime']) ? $contact['lastupdatetime'] : time()
                );

                if ($detail) {
                    $properties = $contact['properties'];

                    $item['truename'] = $contact['truename'];
                    $item['position'] = !empty($contact['position']) ? $contact['position'] : null;
                    $item['gender']   = isset($properties['gender']) ? (int) $properties['gender'] : null;
                    $item['groups']   = array('^none');//$contact['groups'];
                    $item['mobile']   = $contact['mobile'];
                    $item['tel']      = isset($properties['tel']) ? $properties['tel'] : null;
                    $item['email']    = $contact['email'];
                    $item['isavatars']= $contact['isavatars'];
                }

                $list[] = $item;
                unset($contact);
            }
            unset($contacts);
        }

        $this->view->code     = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->contacts = $list;
    }

    /**
     * 联系人详细信息
     */
    public function infoAction()
    {
        $contactId = $this->_request->getParam('contactid');
        $info      = array();

        // 缺少参数
        if (empty($contactId)) {
            throw new TuduX_OpenApi_Exception("Missing parameter \"contactid\"", TuduX_OpenApi_ResponseCode::MISSING_PARAMETER, 200);
        }

        // 用户联系人
        if (false === strpos($contactId, '@')) {
            /* @var $daoContact Dao_Td_Contact_Contact */
            $daoContact = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Contact', Tudu_Dao_Manager::DB_TS);
            $contact = $daoContact->getContactById($contactId, $this->_user->uniqueId);
            if (null === $contact) {
                throw new TuduX_OpenApi_Exception("Contact does not exist or has been deleted", TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS, 200);
            }

            $properties = $contact->properties;
            $info = array(
                'contactid'      => $contact->userName,
                'truename'       => $contact->trueName,
                'position'       => isset($properties['position']) ? $properties['position'] : null,
                'gender'         => isset($properties['gender']) ? $properties['gender'] : null,
                'groups'         => $contact->grups,
                'email'          => $contact->email,
                'tel'            => isset($properties['tel']) ? $properties['tel'] : null,
                'mobile'         => $contact->mobile,
                'lastupdatetime' => $contact->lastUpdateTime,
                'avatarupdatetime' => $contact->lastUpdateTime
            );

        // 组织联系人
        } else {
            $userId = array_shift(explode('@', $contactId));
            $orgId  = array_pop(explode('@', $contactId));

            /* @var $daoUser Dao_Md_User_User */
            $daoUser = Tudu_Dao_Manager::getDao('Dao_Md_User_User', Tudu_Dao_Manager::DB_MD);
            /* @var $daoDept Dao_Md_Department_Department */
            $daoDept = Tudu_Dao_Manager::getDao('Dao_Md_Department_Department', Tudu_Dao_Manager::DB_MD);

            $user = $daoUser->getUser(array('orgid' => $orgId, 'userid' => $userId));
            if (null === $user) {
                throw new TuduX_OpenApi_Exception("Contact does not exist or has been deleted", TuduX_OpenApi_ResponseCode::RESOURCE_NOT_EXISTS, 200);
            }

            $userInfo  = $daoUser->getUserInfo(array('orgid' => $orgId, 'userid' => $userId));
            $depts     = $daoDept->getDepartments(array('orgid' => $orgId))->toArray('deptid');

            $deptId = $this->_getDeptRoot($user->deptId, $depts);
            $info = array(
                'contactid'      => $userId . '@' . $orgId,
                'truename'       => $userInfo->trueName,
                'position'       => $userInfo->position,
                'gender'         => (int) $userInfo->gender,
                'groups'         => $deptId ? array($deptId) : array(),
                'email'          => $user->userName,
                'tel'            => $userInfo->tel,
                'mobile'         => $userInfo->mobile,
                'lastupdatetime' => $user->lastUpdateTime,
                'avatarupdatetime' => $userInfo->updateTime
            );
        }

        $this->view->code    = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->contact = $info;
    }

    /**
     * 联系人分组列表
     */
    public function groupsAction()
    {
        $deep   = (boolean) $this->_request->getParam('deep');
        $groups = array();

        /*=============用户可见组织部门================*/
        /* @var $daoCast Dao_Md_User_Cast */
        $daoCast = Tudu_Dao_Manager::getDao('Dao_Md_User_Cast', Tudu_Dao_Manager::DB_MD);
        $depts   = $daoCast->getCastDepartments($this->_user->orgid, $this->_user->userid)->toArray('deptid');
        // 移除跟部门
        unset($depts['^root']);

        $now = time();

        //$depts = $this->formatDept($depts, $deep);
        foreach ($depts as $dept) {
            if (empty($dept['parentdeptid']) || $dept['parentdeptid'] == '^root') {
                $groups[] = array(
                    'groupid'        => $dept['deptid'],
                    'groupname'      => $dept['deptname'],
                    'ordernum'       => $dept['ordernum'],
                    'lastupdatetime' => $now
                );
            }
        }

        $groups[] = array(
            'groupid'        => '^none',
            'groupname'      => '我的好友',
            'ordernum'       => -1,
            'lastupdatetime' => null
        );

        /*=============用户联系组================*/
        /* @var $daoGroup Dao_Td_Contact_Group */
        //$daoGroup      = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Group', Tudu_Dao_Manager::DB_TS);
        //$contactGroups = $daoGroup->getGroupsByUniqueId($this->_user->uniqueid, null, 'ordernum DESC')->toArray();

        // 移除不需要的数据
        /*foreach ($contactGroups as $group) {
            $groups[] = array(
                'groupid'   => $group['groupid'],
                'gruopname' => $group['groupname'],
                'lastupdatetime' => isset($group['lastupdatetime']) ? (int) $group['lastupdatetime'] : null
            );
        }
        unset($contactGroups);*/

        // 数据合并
        //$groups = array_merge($depts, $groups);

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->groups = $groups;
    }

    /**
     *
     */
    public function contactGroupAction()
    {
        /* @var $adoUserGroup Dao_Md_Group_Group */
        $daoUserGroup = Tudu_Dao_Manager::getDao('Dao_Md_User_Group', Tudu_Dao_Manager::DB_MD);

        /* @var $daoContactGroup Dao_Td_Contact_Group */
        $daoContactGroup = Tudu_Dao_Manager::getDao('Dao_Td_Contact_Group', Tudu_Dao_Manager::DB_MD);

        $ug = $daoUserGroup->getGroups(array('orgid' => $this->_user->orgId))->toArray();
        $cg = $daoContactGroup->getGroups(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId))->toArray();

        $time = time();

        $groups = array();
        foreach ($ug as $item) {
            $groups[] = array(
                'groupid'   => $item['groupid'],
                'groupname' => $item['groupname'],
                'ordernum'  => $item['ordernum'] + 10000,
                'lastupdatetime' => $time
            );
        }

        foreach ($cg as $item) {
            $groups[] = array(
                'groupid'   => $item['groupid'],
                'groupname' => $item['groupname'],
                'ordernum'  => $item['ordernum'],
                'lastupdatetime' => $time
            );
        }

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->groups = $groups;
    }

    /**
     *
     * @param unknown_type $deptId
     * @param unknown_type $depts
     */
    private function _getDeptRoot($deptId, array $depts)
    {
        if (!$deptId || empty($depts) || !isset($depts[$deptId])) {
            return null;
        }

        $dept = $depts[$deptId];
        do {
            if (empty($dept['parentid']) || $dept['parentid'] == '^root') {
                break ;
            }

            if (!isset($depts[$dept['parentid']])) {
                return null;
            }

            $dept = $depts[$dept['parentid']];

        } while (true);

        return $dept['deptid'];
    }
}