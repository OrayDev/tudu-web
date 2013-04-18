<?php
/**
 * 通讯录控制器
 *
 * @version $Id: ContactController.php 2799 2013-03-29 10:04:06Z chenyongfa $s
 */

class ContactController extends TuduX_Controller_Base
{
	/**
	 * 背景颜色
	 *
	 * @var array
	 */
	private $_bgColors = array(
        '#729c3b', '#58a8b4', '#5883bf', '#6d72ba', '#e3a325',
        '#da8a22', '#b34731', '#bb4c91', '#995aae', '#cc0000',
        '#fcd468', '#ff9966', '#cc99cc', '#cc9999', '#ad855c',
        '#cccc99', '#ff6633', '#cc6666', '#ad33ad', '#855c85',
        '#99cc66', '#66cccc', '#3399ff', '#2b8787', '#855c85',
        '#6699ff', '#3385d6', '#335cad', '#5f27b3', '#262ed7',
        '#d5d2c0', '#b5bfca', '#999999', '#666666', '#333333'
	);

	public function init()
	{
		parent::init();

		$langs = array('common', 'contact');

		$this->lang = Tudu_Lang::getInstance()->load($langs);

        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

    	// IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }

        $this->view->LANG = $this->lang;
	}

	/**
	 *
	 */
	public function preDispatch()
	{

	}

	/**
	 * 显示页面
	 */
	public function indexAction()
	{
		$query   = $this->_request->getQuery();
		$page    = (int) $this->_request->getQuery('page');
		$pageSize= 25;
		$params  = array();

		/* @var $daoGroup Dao_Td_Contact_Group */
		$daoGroup = $this->getDao('Dao_Td_Contact_Group');
        $groups   = $daoGroup->getGroupsByUniqueId($this->_user->uniqueId, null, 'ordernum DESC')->toArray('groupid');

		$users    = array();
		$contacts = array();

		// 联系人列表
		if (isset($query['type']) && $query['type'] == 'contact') {

		    $params['type'] = $query['type'];

		    /* @var $daoContact Dao_Td_Contact_Contact */
		    $daoContact = $this->getDao('Dao_Td_Contact_Contact');

		    $condition = array(
                'uniqueid'  => $this->_user->uniqueId
            );

            if (array_key_exists('groupid', $query)) {
                $params['groupid'] = $query['groupid'];
                if (!empty($query['groupid'])) {

                    if ($query['groupid'] == '^n') {

                        $condition['nearly'] = 5;

                        $group = array(
                            'groupid'   => '^n',
                            'groupname' => $this->lang['group_nearly']
                        );

                    } else {

                        $condition['groupid'] = $query['groupid'];

                        $group = $groups[$query['groupid']];

                        if ($group['issystem']) {
                            $group['groupname'] = $this->lang['group_' . $group['groupname']];
                        }
                    }
                } else {
                    $group = array('groupname' => $this->lang['group_ungroup']);
                    $condition['groupid'] = null;
                }

                $this->view->group = $group;
            }

            $contacts = $daoContact->getContactPage($condition, 'ordernum DESC,userid ASC', $page, $pageSize);

            $pageInfo = array(
                'currpage' => $contacts->currentPage(),
                'pagecount' => $contacts->pageCount(),
                'recordcount' => $contacts->recordCount(),
                'url' => '/contact/',
                'query' => $params
            );

            $contacts = $contacts->toArray();
		// 用户列表
		} else {

    		/* @var Dao_Md_User_Cast */
    		$daoCast = Oray_Dao::factory('Dao_Md_User_Cast', $this->multidb->getDb());

    		/* @var Dao_Md_Department_Department */
    		$daoDepartment = Oray_Dao::factory('Dao_Md_Department_Department', $this->multidb->getDb());

    		$condition = array(
                'orgid'  => $this->_user->orgId,
                'userid' => $this->_user->userId
    		);

    		if (!empty($query['deptid'])) {
    		    if ($query['deptid'] != '^root') {
    		        $dept[] = $query['deptid'];

        			$deptIds = array();
        			$deptIds = $daoDepartment->getChildDeptid($this->_user->orgId, $query['deptid']);
                    $condition['deptid'] = array_merge($dept, $deptIds);
    		    }
    		    $params['deptid']    = $query['deptid'];
    		}

    		$users = $daoCast->getCastUserPage($condition, 'ordernum DESC, truename ASC', $page, $pageSize);

    		$pageInfo  = array(
                'currpage' => $users->currentPage(),
                'pagecount' => $users->pageCount(),
                'recordcount' => $users->recordCount(),
    		    'url' => '/contact/',
    			'query' => $params
    		);

    		$users = $users->toArray();
		}

		$this->view->pageinfo = $pageInfo;
		$this->view->groups   = $groups;
		$this->view->users    = $users;
		$this->view->contacts = $contacts;
		$this->view->params   = $params;
	}

	/**
	 * 联系人搜索
	 */
	public function searchAction()
	{
		$keyword    = $this->_request->getQuery('keyword');
		$pinyin     = $this->_request->getQuery('pinyin');
		$params     = array();
		$daoUser    = $this->getMdDao('Dao_Md_User_Cast');
		$daoContact = $this->getDao('Dao_Td_Contact_Contact');

		$condition = array(
            'orgid'  => $this->_user->orgId,
            'userid' => $this->_user->userId,
			'uniqueid' => $this->_user->uniqueId
		);

		if ($keyword) {
			$condition['keyword'] = $keyword;
			$params['keyword'] = $keyword;
		}

		if ($pinyin) {
		    $condition['pinyin'] = $pinyin;
            $params['pinyin'] = $pinyin;
		}
		$users = $daoUser->getCastUsers($condition);
		$personal = $daoContact->getContacts($condition);

		$daoGroup = $this->getDao('Dao_Td_Contact_Group');
		$groups = $daoGroup->getGroupsByUniqueId($this->_user->uniqueId, null, 'ordernum DESC');
		$this->view->groups    = $groups->toArray();

		$this->view->users     = $users->toArray();
		$this->view->personal  = $personal->toArray();
		$this->view->keyword   = $keyword;
		$this->view->params    = $params;
	}

	/**
	 * 查看页面
	 */
	public function viewAction()
	{

		$email  = $this->_request->getQuery('email');
		$this->view->email = $email;
		// 企业联系人
		if ($email) {

			if (!$email || false === strpos($email, '@')) {
				$this->_redirect($_SERVER['HTTP_REFERER']);
			}

			$userId = array_shift(explode('@', $email));
	        $orgId  = array_pop(explode('@', $email));

			$daoCast = Oray_Dao::factory('Dao_Md_User_Cast', $this->multidb->getDb());

			// 不可见
			if ($userId != $this->_user->userId &&
			    !$daoCast->existsUser($this->_user->orgId, $this->_user->userId, $userId))
			{
				$this->_redirect($_SERVER['HTTP_REFERER']);
			}

			/* @var $daoUser Dao_Md_User_User */
     		$daoUser = Oray_Dao::factory('Dao_Md_User_User', $this->multidb->getDb());
			$daoDept = Oray_Dao::factory('Dao_Md_Department_Department', $this->multidb->getDb());

			$profile  = $daoUser->getUser(array('orgid' => $orgId, 'userid' => $userId));

			if (!$profile) {
				$this->_redirect($_SERVER['HTTP_REFERER']);
			}

			$dept = $daoDept->getDepartment(array('orgid' => $this->_user->orgId, 'deptid' => $profile->deptId));

			$userinfo = $daoUser->getUserInfo(array('orgid' => $this->_user->orgId, 'userid' => $userId));

			$this->view->back     = $this->_request->getQuery('back');
			$this->view->dept     = $dept ? $dept->toArray() : null;
			$this->view->profile  = $profile->toArray();
			$this->view->userinfo = $userinfo->toArray();

		} else {
    		// 个人通讯录
    		$contactId = $this->_request->getQuery('ctid');

		    if (empty($contactId)) {
                $this->_redirect($_SERVER['HTTP_REFERER']);
            }

			$daoContact = $this->getDao('Dao_Td_Contact_Contact');
			$contact = $daoContact->getContactById($contactId, $this->_user->uniqueId);
			$groupId = $contact->groups;
			$count = count($groupId);

			$daoGroup = $this->getDao('Dao_Td_Contact_Group');
			$groups = $daoGroup->getGroupsByUniqueId($this->_user->uniqueId);

			$this->view->groups    = $groups->toArray();
			$this->view->groupid   = $groupId;
			$this->view->count     = $count;
			$this->view->back      = $this->_request->getQuery('back');
			$this->view->contact   = $contact->toArray();
		}
	}

	/**
	 * 编辑
	 */
	public function modifyAction()
	{
	    $contactId = $this->_request->getQuery('ctid');
	    $back    = $this->_request->getQuery('back');

	    $daoContact = $this->getDao('Dao_Td_Contact_Contact');
	    $groupid = array();
	    if($contactId){
			$contact = $daoContact->getContactById($contactId, $this->_user->uniqueId);

			if(!empty($contact->properties['birthday'])) {
				$birthday = $contact->properties['birthday'] ? explode('-', date('Y-m-d', $contact->properties['birthday'])) : array(null, null, null);
		        $birthdayinfo['birthyear']  = $birthday[0];
		        $birthdayinfo['birthmonth'] = $birthday[1];
		        $birthdayinfo['birthdate']  = $birthday[2];

		        $this->view->birthdayinfo  = $birthdayinfo;
			}

			$groupid = $contact->groups;
			$this->view->contact  = $contact->toArray();

	    }
	    $daoGroup = $this->getDao('Dao_Td_Contact_Group');
		$groups = $daoGroup->getGroupsByUniqueId($this->_user->uniqueId);
		$this->view->groups  = $groups->toArray();
		$this->view->groupid = $groupid;
		$this->view->back = $back;
	}

	/**
	 * 编辑组
	 */
	public function groupModifyAction()
	{
		$groupId = $this->_request->getQuery('gid');
		$back    = $this->_request->getQuery('back');

		if ($groupId) {
			$daoContact = $this->getDao('Dao_Td_Contact_Contact');

		    $daoGroup = $this->getDao('Dao_Td_Contact_Group');

		    $condition = array(
		    	'groupid' => $groupId,
		    	'uniqueid' => $this->_user->uniqueId
		    );
		    $group = $daoGroup->getGroup($condition);
		    $this->view->group  = $group->toArray();

		    // 查找联系人
			$contacts = $daoContact->getContacts(array(
                'uniqueid'  => $this->_user->uniqueId,
                'groupid' => $groupId
            ));

			$this->view->contacts = $contacts->toArray();
		}

		$this->view->back = $back;
	}

	/**
	 * 群组列表
	 */
	public function groupListAction()
	{
	    /* @var $daoGroup Dao_Td_Contact_Group */
	    $daoGroup = $this->getDao('Dao_Td_Contact_Group');

	    $groups = $daoGroup->getGroups(array(
            'uniqueid' => $this->_user->uniqueId
	    ), null, 'ordernum DESC');

	    $access = array(
            'skin' => $this->_user->getAccess()->isAllowed(Tudu_Access::PERM_CUSTOM_SKIN)
        );

        $this->view->access   = $access;
	    $this->view->bgcolors = $this->_bgColors;
	    $this->view->groups   = $groups->toArray();
	}

    /**
     * 保存联系人
     */
    public function saveAction()
    {
    	$post = $this->_request->getPost();
    	$groupIds = (array) $this->_request->getParam('group');
    	// 当前用户唯一ID
    	$uniqueId    = $this->_user->uniqueId;

        $daoContact = $this->getDao('Dao_Td_Contact_Contact');
		$daoGroup = $this->getDao('Dao_Td_Contact_Group');

        $contactId = $post['contactid'] ? $post['contactid'] : Dao_Td_Contact_Contact::getContactId();

        $contacts = $daoContact->getContactById($contactId, $uniqueId);

        $trueName = trim($post['truename']);
        if(empty($trueName)) {
        	return $this->json(false, $this->lang['missing_name']);
        }
        $email = $this->_request->getPost('email');
        if(!empty($email) && false === strpos($email, '@')) {
        	return $this->json(false, $this->lang['email_error']);
        }

        $params = array(
        	'contactid' => $contactId,
        	'uniqueid'  => $uniqueId,
        	'truename'  => $trueName,
        	'pinyin'    => Tudu_Pinyin::parse($post['truename'], true),
        	'email'     => $email,
            'mobile'    => trim($post['mobile']),
            'lastupdatetime' => time(),
        );
    	if (isset($post['corporation'])) {
        	$params['properties']['corporation'] = $post['corporation'];
        }
    	if (isset($post['position'])) {
        	$params['properties']['position'] = $post['position'];
        }
    	if (isset($post['tel'])) {
        	$params['properties']['tel'] = $post['tel'];
        }
    	if (isset($post['fax'])) {
        	$params['properties']['fax'] = $post['fax'];
        }
        if (!empty($post['bir-year']) && !empty($post['bir-month']) && !empty($post['bir-day'])) {
            $birthday = $post['bir-year'] . '-' . $post['bir-month'] . '-' . $post['bir-day'];
            $params['properties']['birthday'] = strtotime($birthday);
        }
    	if (isset($post['im'])) {
        	$params['properties']['im'] = $post['im'];
        }
    	if (isset($post['mailbox'])) {
        	$params['properties']['mailbox'] = $post['mailbox'];
        }
    	if (isset($post['address'])) {
        	$params['properties']['address'] = $post['address'];
        }
    	if (isset($post['memo'])) {
        	$params['memo'] = $post['memo'];
        }
    	if (!empty($post['avatars'])) {   // 头像
            $options = $this->getInvokeArg('bootstrap')->getOption('avatar');
            $fileName = $options['tempdir'] . '/' . $post['avatars'];

            if (file_exists($fileName)) {
                $imginfo = getimagesize($fileName);

                $params['avatarstype'] = $imginfo['mime'];
                $params['avatars']    = base64_encode(file_get_contents($fileName));
            }

            unlink($fileName);
        }
        if(!empty($post['contactid'])) {
        	// 修改个人通讯录联系人时，用户判断
	        if($contacts->uniqueId != $uniqueId) {
	        	return $this->json(false, $this->lang['invalid']);
	        }

			if(!$daoContact->updateContact($contactId, $uniqueId, $params)){
				return $this->json(false, $this->lang['save_fail']);
			}

			$oldGroupIds = $contacts->groups;

			if($oldGroupIds) {
				$add = array_diff($groupIds, $oldGroupIds);  // 计算出要插入的联系组
				$remove = array_diff($oldGroupIds, $groupIds);  // 计算出要删除的联系组
				if($add){
					foreach ($add as $groupId) {
		        		$daoGroup->addMember($groupId, $uniqueId, $contactId);
					}
				}
				if($remove) {
					foreach ($remove as $groupId) {
		        		$daoGroup->removeMember($groupId, $uniqueId, $contactId);
					}
				}
			} else {
				foreach ($groupIds as $groupId) {
		        	$daoGroup->addMember($groupId, $uniqueId, $contactId);
				}
			}

			$config  = $this->bootstrap->getOption('httpsqs');
			$httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
			$httpsqs->put(implode(' ', array(
    			'contact',
    			'',
    			'',
    			http_build_query(array('username' => $this->_user->userName))
			)), 'notify');

			return $this->json(true, $this->lang['save_success'], array('contactid' => $contactId));
        }

        if(!$daoContact->createContact($params)) {

        	return $this->json(false, $this->lang['save_fail']);
        } else {

        	foreach ($groupIds as $groupId) {
        		$daoGroup->addMember($groupId, $uniqueId, $contactId);
			}

        	//return $this->json(true, $this->lang['save_success'], array('contactid' => $contactId));
        }

        $this->cache->deleteCache(array($daoContact, 'getContactsByUniqueId'), array($this->_user->uniqueId));

        $config  = $this->bootstrap->getOption('httpsqs');
        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);
        $httpsqs->put(implode(' ', array(
            'contact',
            '',
            '',
            http_build_query(array('username' => $this->_user->userName))
        )), 'notify');

        return $this->json(true, $this->lang['save_success']);

    }

    /**
     * 删除联系人
     */
    public function deleteAction()
    {
        $contactIds = explode(',', $this->_request->getParam('ctid'));

        foreach ($contactIds as $contactId) {
            if(!$contactId) {
            	return $this->json(false, $this->lang['invalid']);
            }

            $daoContact = $this->getDao('Dao_Td_Contact_Contact');

            if(!$daoContact->deleteContact($contactId, $this->_user->uniqueId)) {
            	//return $this->json(false, $this->lang['delete_fail']);
            	continue ;
            }
        }

        $this->cache->deleteCache(array($daoContact, 'getContactsByUniqueId'), array($this->_user->uniqueId));

        return $this->json(true, $this->lang['delete_success']);
    }

	/**
	 * 编辑群组
	 */
    public function groupSaveAction()
	{
	    $member    = (array) $this->_request->getParam('member');
	    $post      = $this->_request->getPost();
	    $uniqueId    = $this->_user->uniqueId;
	    $daoContact = $this->getDao('Dao_Td_Contact_Contact');
	    $daoGroup = $this->getDao('Dao_Td_Contact_Group');
	    $daoCast = $this->getMdDao('Dao_Md_User_Cast');
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        // 组Id  添加联系人时添加联系组  与  新建联系组、更新联系组
        if(!empty($post['groupid'])) {
        	$groupId = $post['groupid'];
        } else {
        	$groupId = Dao_Td_Contact_Group::getGroupId();
        }

        if(array_key_exists('groupname', $post) && empty($post['groupname'])) {
        	return $this->json(false, $this->lang['empty_group_name']);
        }

	    $params = array(
	    	'groupid' => $groupId,
	    	'uniqueid' => $uniqueId,
	    );

	    if (!empty($post['groupname'])) {
	        $params['groupname'] = $post['groupname'];
	    }

		if (!empty($post['bgcolor'])) {
	        $params['bgcolor'] = $post['bgcolor'];
	    }

	    // 更新 与 添加 联系组
	    if(!empty($post['groupid'])) {
	    	if(!$daoGroup->updateGroup($groupId, $uniqueId, $params)) {
	    		return $this->json(false, $this->lang['update_fail']);
	    	}
	    } else {
	    	$count = $daoGroup->getGroupCount(array(
	            'uniqueid' => $uniqueId
			));

	        $colorIndex = $count % count($this->_bgColors);
	        $colorIndex = $colorIndex < count($this->_bgColors) ? $colorIndex + 1 : $colorIndex;
	        $params['bgcolor'] = !empty($post['bgcolor']) ? $post['bgcolor'] : $this->_bgColors[$count % count($this->_bgColors)];
	        $params['ordernum'] = $daoGroup->getMaxOrderNum($this->_user->uniqueId) + 1;

		    if(!$daoGroup->createGroup($params)) {
		    	return $this->json(false, $this->lang['add_fail']);
		    }
	    }

	    // 个人联系组 组成员操作
	    if(!empty($post['editmember'])){
		    // 获取原有成员
		    if ($groupId) {
	            $members = $daoContact->getContacts(array('uniqueid' => $this->_user->uniqueId, 'groupid' => $groupId))->toArray('contactid');
		    } else {
		        $members = array();
		    }

			if(count($member) > 0) {
	        	foreach($member as $number) {
	        		$contactId = !empty($post['contactid-' . $number]) ? $post['contactid-' . $number] : null;

	        		if(null !== $contactId) {

	        		    if (!isset($members[$contactId])) {
	                        $daoGroup->addMember($groupId, $uniqueId, $contactId);
	        		    } else {
	        		        unset($members[$contactId]);
	        		    }

	        		// 检查图度用户，复制到个人通讯录
	        		} else {
	        			$email = !empty($post['email-' . $number]) ? $post['email-' . $number] : null;

	        			$condition = array(
				            'uniqueid'  => $uniqueId,
	        				'email'     => $email,
	        				'fromuser'  => 1
						);

						$contact = $daoContact->getContact($condition);
				        if($contact) {

				        	$daoGroup->addMember($groupId, $uniqueId, $contact->contactId);

				        } else {

				        	$this->_addSystemMember($email, $groupId);
				        }
	        		}
	        	}
	        }

	        // 删除被去除的联系人
	        foreach ($members as $contactId => $member) {
	            $daoGroup->removeMember($groupId, $uniqueId, $contactId);
	        }
	    }
        // 删除缓存
        $this->cache->deleteCache(array($daoGroup, 'getGroupsByUniqueId'), array($this->_user->uniqueId));

	    return $this->json(true, $this->lang['save_success'], array('groupid' => $groupId, 'groupname' => $post['groupname']));
	}

	/**
	 * 分组排序
	 */
	public function groupSortAction()
	{
	    $groupId = $this->_request->getPost('groupid');
        $type    = $this->_request->getPost('type');

        $ret = $this->getDao('Dao_Td_Contact_Group')->sortGroup($groupId, $this->_user->uniqueId, $type);

        $this->json($ret, null);
	}

	/**
	 * 删除群组
	 */
	public function groupDeleteAction()
	{
	    $groupId = $this->_request->getParam('gid');
		if(!$groupId) {
        	return $this->json(false, $this->lang['invalid']);
        }
        $daoGroup = $this->getDao('Dao_Td_Contact_Group');

        if(!$daoGroup->deleteGroup($groupId, $this->_user->uniqueId)) {
        	return $this->json(false, $this->lang['delete_fail']);
        }

        // 删除缓存
        $this->cache->deleteCache(array($daoGroup, 'getGroupsByUniqueId'), array($this->_user->uniqueId));

        return $this->json(true, $this->lang['delete_success']);
	}

	/**
	 * 联系人加入群组
	 */
	public function groupAction()
	{
	    $groupId = $this->_request->getParam('gid');
 	    $contactId = $this->_request->getParam('ctid');
 	    $keys = (array) $this->_request->getParam('key');
 	    $type = $this->_request->getParam('type');

        $daoGroup = $this->getDao('Dao_Td_Contact_Group');
        $daoContact = $this->getDao('Dao_Td_Contact_Contact');
        $daoCast = $this->getMdDao('Dao_Md_User_Cast');
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        $uniqueId    = $this->_user->uniqueId;

        switch ($type) {
        	case 'add':
        		if (!$keys) {
        			return $this->json(false, $this->lang['invalid']);
        		}
        		foreach ($keys as $key) {
	        		if (!$key) {
	        			continue;
					}
					if (false !== strpos($key, '@')) { //key 为Email格式

						$condition = array(
				            'uniqueid'  => $uniqueId,
	        				'email'     => $key,
	        				'fromuser'  => 1
						);

						$contact = $daoContact->getContact($condition);
				        if($contact) {
				        	$daoGroup->addMember($groupId, $uniqueId, $contact->contactId);
				        } else {
							$this->_addSystemMember($key, $groupId);

				        }
					} else {
						// key 不是Email格式
						$daoGroup->addMember($groupId, $uniqueId, $key);
					}
        		}
        		break;

        	case 'remove':
        		$daoGroup->removeMember($groupId, $uniqueId, $contactId);
        		break;
        }

        // 删除缓存
        $this->cache->deleteCache(array($daoContact, 'getContactsByUniqueId'), array($this->_user->uniqueId));

        return $this->json(true, $this->lang['operate_success']);
	}

	/**
	 * 头像上传
	 */
	public function uploadAction()
	{
		$file = $_FILES['avatar-file'];
        $options = $this->getInvokeArg('bootstrap')->getOption('avatar');

        if (!$file || !is_uploaded_file($file['tmp_name'])) {
        	return $this->json(false, $this->lang['avatar_upload_failure'], null, false);
        }

        $mt   = explode(' ', microtime());
        $hash = md5_file($file['tmp_name']);
        $uploadName = $options['tempdir'] . '/' . $hash;

        $mimes = array(
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        );

        if (!file_exists($uploadName)) {
	        $message = null;
	        do {
	            if (!is_uploaded_file($file['tmp_name'])) {
	                $message = $this->lang['avatar_upload_failure'];
	                break;
	            }

	            $info = getimagesize($file['tmp_name']);
	            if (!in_array($info['mime'], $mimes)) {
	                $message = $this->lang['invalid_img_type'];
	                break;
	            }

	            if ($file['size'] > $options['sizelimit']) {
	                $message = $this->lang['avatar_filesize_too_large'];
	                break;
	            }
	        } while (false);

	        if ($message) {
	            return $this->json(false, $message, null, false);
	        }

	        $ret = @move_uploaded_file($file['tmp_name'], $uploadName);
        }

        return $this->json(true, $this->lang['avatar_upload_success'], array('hash' => $hash), false);
	}

	/**
	 * 显示上传图片
	 */
	public function avatarAction()
	{
		$this->_helper->viewRenderer->setNeverRender();

		$hash = $this->_request->getQuery('hash');

        $options = $this->getInvokeArg('bootstrap')->getOption('avatar');
        $fileName = $options['tempdir'] . '/' . $hash;

		if (file_exists($fileName)) {
			$info = getimagesize($fileName);

			$this->_response->setHeader('Content-Type: ', $info['mime']);

			$content = file_get_contents($fileName);

			echo $content;
		}
	}

	/**
	 * 更新头像设置
	 */
	public function updateavatarAction()
	{
		$hash = $this->_request->getPost('hash');
        $post = $this->_request->getPost();
        $options = $this->getInvokeArg('bootstrap')->getOptions();

        //$userId = @$post['userid'];
        $x      = (int) $post['x'];
        $y      = (int) $post['y'];
        $width  = (int) $post['width'];
        $height = (int) $post['height'];

        $avatar = null;
        $avatarType = null;

        $mimes = array(
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        );

        $fileName = $options['avatar']['tempdir'] . '/' . $hash;
        if (!$hash || !file_exists($fileName)) {
            return $this->json(false, $this->lang['avatar_upload_failure']);
        }

        $info = getimagesize($fileName);
        $avatarType = $info['mime'];

        if (!in_array($avatarType, $mimes)) {
            return $this->json(false, $this->lang['avatar_upload_failure']);
        }

        $type = array_flip($mimes);
        $func = 'imagecreatefrom' . $type[$avatarType];
        $outputFunc = 'image' . $type[$avatarType];

        // tudutalk 不支持gif，先转成jpg
        if ($outputFunc == 'imagegif') {
        	$outputFunc = 'imagejpeg';
        }

        $img = imagecreatetruecolor($options['avatar']['width'], $options['avatar']['height']);
        $src = $func($fileName);

        $width  = $width <= 0 ? $info[0] : $width;
        $height = $height <= 0 ? $info[1] : $height;

        imagecopyresampled($img, $src, 0, 0, $x, $y, $options['avatar']['width'], $options['avatar']['height'], $width, $height);

        $ret = $outputFunc($img, $fileName . '_thumb');

        if (!$ret) {
            return $this->json(false, $this->lang['avatar_edit_failure']);
        }

        return $this->json(true, $this->lang['avatar_edit_success'], array('avatar' => $hash . '_thumb'));
	}

	/**
	 * 联系人 - 从公共通讯录复制过去个人通讯录和添加到组的操作
	 *
	 * @param $email
	 * @param $groupId
	 */
	private function _addSystemMember($email, $groupId)
	{
	    $daoContact = $this->getDao('Dao_Td_Contact_Contact');
	    $daoGroup = $this->getDao('Dao_Td_Contact_Group');
	    $daoCast = $this->getMdDao('Dao_Md_User_Cast');
        $daoUser = $this->getMdDao('Dao_Md_User_User');

		$userId = array_shift(explode('@', $email));
        $domain = array_pop(explode('@', $email));

        if (!$daoCast->existsUser($this->_user->orgId, $this->_user->userId, $userId)) {
            return;
        }

        $profile  = $daoUser->getUser(array('domainname' => $domain, 'userid' => $userId));
        $userinfo = $daoUser->getUserInfo(array('orgid' => $this->_user->orgId, 'userid' => $userId));

		$ctid = Dao_Td_Contact_Contact::getContactId();
		$params = array(
			'contactid' => $ctid,
        	'uniqueid'  => $this->_user->uniqueId,
        	'truename'  => $userinfo->trueName,
        	'pinyin'    => Tudu_Pinyin::parse($userinfo->trueName, true),
        	'email'     => $email,
            'mobile'    => $userinfo->mobile,
			'fromuser'  => 1
		);

        $daoContact->createContact($params);
        $daoGroup->addMember($groupId, $this->_user->uniqueId, $ctid);
	}
}