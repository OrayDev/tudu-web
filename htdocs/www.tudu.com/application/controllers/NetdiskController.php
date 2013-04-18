<?php
/**
 * Netdisk Controller
 *
 * @version $Id: NetdiskController.php 2401 2012-11-21 09:57:24Z cutecube $
 */

class NetdiskController extends TuduX_Controller_Base
{

    private $_sysFolders = array(
        '^root' => array(
            'folderid'   => '^root',
            'foldername' => 'root',
            'issystem'   => 1,
            'maxquota'   => 10000000
        ),
        '^doc' => array(
            'folderid' => '^doc',
            'foldername' => 'doc',
            'parentfolderid' => '^root',
            'issystem' => 1
        ),
        '^pic' => array(
            'folderid' => '^pic',
            'foldername' => 'pic',
            'parentfolderid' => '^root',
            'issystem' => 1
        )
    );

    /**
     * 保留目录名
     * @var array
     */
    protected $_denyFolderNames = array(
        '我的文档', '我的文檔', 'doc', 'My Documents',
        '我的图片', '我的圖片', 'pic', 'My Pictures', 'root',
    );

    public function init()
    {
        parent::init();

        $this->lang = Tudu_Lang::getInstance()->load(array('common', 'netdisk'));
        $this->view->LANG = $this->lang;
    }

    /**
     *
     */
    public function preDispatch()
    {
        if (!$this->_user->isLogined()) {
            $this->jump(null, array('error' => 'timeout'));
        }

        // IP或登录时间无效
        if (!empty($this->session->auth['invalid'])) {
            $this->jump('/forbid/');
        }
    }

    /**
     * 文件列表
     */
    public function indexAction()
    {
        $folderId = $this->_request->getQuery('folderid', '^root');

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        $reloadFolder = false;

        $folders = $daoFolder->getFolders(array('uniqueid' => $this->_user->uniqueId))->toArray('folderid');

        // 创建系统目录
        if (!array_key_exists('^root', $folders)) {
            foreach ($this->_sysFolders as $fid => $item) {
                $item['uniqueid'] = $this->_user->uniqueId;
                $item['orgid']    = $this->_user->orgId;
                $item['createtime'] = $this->_timestamp;

                if ($fid == '^root') {
                    $item['maxquota'] = $this->_user->maxNdQuota;
                }

                $daoFolder->createFolder($item);
            }

            $reloadFolder = true;
        } else {
            // 更新空间大小限制
            if ($folders['^root']['maxquota'] != $this->_user->maxNdQuota) {
                $daoFolder->updateFolder($this->_user->uniqueId, '^root', array('maxquota' => (int) $this->_user->maxNdQuota));
                $reloadFolder = true;
            }
        }

        if ($reloadFolder) {
            $folders = $daoFolder->getFolders(array('uniqueid' => $this->_user->uniqueId))->toArray('folderid');
        }

        $groupIds = $daoUser->getGroupIds($this->_user->orgId, $this->_user->userId);
        if ($groupIds) {
            $targetIds = array_merge($groupIds, (array) $this->_user->userName);
        } else {
            $targetIds = (array) $this->_user->userName;
        }

        $existShare = 0;
        foreach ($targetIds as $targetId) {
            $existShare += $daoShare->existShare($targetId, $this->_user->orgId);
        }

        if (!array_key_exists($folderId, $folders)) {
            $this->jump('/netdisk');
        }

        $files = $daoFile->getFiles(array('uniqueid' => $this->_user->uniqueId, 'folderid' => $folderId))->toArray();

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));
        $this->view->registModifier('get_file_url', array($this, 'getFileUrl'));

        $this->view->folder  = $folders[$folderId];
        $this->view->root    = $folders['^root'];
        $this->view->folders = $folders;
        $this->view->files   = $files;
        $this->view->existshare = $existShare;
    }

    /**
     *
     */
    public function shareAction()
    {
        $folderId = $this->_request->getQuery('folderid');
        $ownerId  = $this->_request->getQuery('ownerid');
        $back     = $this->_request->getQuery('back');

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        /* @var $daoUser Dao_Md_User_User */
        $daoUser = $this->getMdDao('Dao_Md_User_User');

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        $folders = $daoFolder->getFolders(array('uniqueid' => $this->_user->uniqueId))->toArray('folderid');

        $shareUsers   = array();
        $shareFolders = array();
        $shareFiles   = array();

        if ($folderId == '^share') {
            $groupIds   = $daoUser->getGroupIds($this->_user->orgId, $this->_user->userId);
            $targetIds  = array_merge($groupIds, (array) $this->_user->userName);
            $shareUsers = $daoShare->getShareUsers(array('targetid' => $targetIds, 'orgid' => $this->_user->orgId));
            if ($shareUsers !== null) {
                $shareUsers = $shareUsers->toArray();
            }
        } else {
            if ($ownerId && $folderId) {
                $shareFiles = $daoFile->getFiles(array('uniqueid' => $ownerId, 'folderid' => $folderId))->toArray();
                if (!empty($folders[$folderId])) {
                    $this->view->folder  = $folders[$folderId];
                }
            } else {
                $groupIds = $daoUser->getGroupIds($this->_user->orgId, $this->_user->userId);
                if ($groupIds) {
                    $targetIds = array_merge($groupIds, (array) $this->_user->userName);
                } else {
                    $targetIds = (array) $this->_user->userName;
                }

                foreach ($targetIds as $targetId) {
                    $folder = $daoShare->getShareFolders(array('ownerid' => $ownerId, 'targetid' => $targetId, 'objecttype' => 'folder'));
                    if ($folder) {
                        $shareFolders = array_merge($shareFolders, $folder->toArray());
                    }
                    $file = $daoShare->getShareFiles(array('ownerid' => $ownerId, 'targetid' => $targetId, 'objecttype' => 'file'));
                    if ($file) {
                        $shareFiles = array_merge($shareFiles, $file->toArray());
                    }
                }
            }
            $user = $daoUser->getUserCard(array('uniqueid' => $ownerId));
            $this->view->username = $user['truename'];
        }

        $this->view->registModifier('format_file_size', array($this, 'formatFileSize'));
        $this->view->registModifier('get_file_url', array($this, 'getFileUrl'));

        $this->view->root     = $folders['^root'];
        $this->view->folderid = $folderId;
        $this->view->ownerid  = $ownerId;
        $this->view->users    = $shareUsers;
        $this->view->files    = $shareFiles;
        $this->view->folders  = $shareFolders;
        $this->view->back     = $back;
    }

    /**
     * 显示上传页面
     */
    public function uploadAction()
    {
        $folderId = $this->_request->getQuery('folderid');
        $type     = $this->_request->getQuery('type');

        $type = $type == 'flash' ? 'flash' : 'ajax';

        $upload = $this->options['upload'];
        $uploadUrl = $upload['cgi']['upload'] . '?' . session_name() . '=' . Zend_Session::getId()
                   . '&email=' . $this->_user->userName
                   . '&mod=netdisk&folderid=' . $folderId;

        $this->view->uploadurl = $uploadUrl;

        $this->render('upload_' . $type);
    }

    /**
     * 文件重命名
     */
    public function fileRenameAction()
    {
        $fileId = $this->_request->getPost('fileid');
        $name   = $this->_request->getPost('filename');

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        $file = $daoFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $fileId));

        if (null === $file) {
            return $this->json(false, $this->lang['file_not_exists']);
        }

        if ($file->fileName == $name) {
            return $this->json(true, sprintf($this->lang['file_exists'], $name));
        }

        if (preg_match('/[\?\*\/\\\\<\>\:\'"]/', $name) || mb_strlen($name, 'utf-8') > 255) {
            return $this->json(false, $this->lang['invalid_file_name']);
        }

        if (!$daoFile->updateFile($this->_user->uniqueId, $fileId, array('filename' => $name))) {
            return $this->json(false, $this->lang['file_rename_failure']);
        }

        return $this->json(true, $this->lang['file_rename_success']);
    }

    /**
     * 文件移动
     */
    public function fileMoveAction()
    {
        $fileId   = explode(',', $this->_request->getPost('fileid'));
        $folderId = trim($this->_request->getPost('folderid'));

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        // 目录不存在
        if (!$daoFolder->existsFolder(array('uniqueid' => $this->_user->uniqueId, 'folderid' => $folderId))) {
            return $this->json(false, $this->lang['folder_not_exists']);
        }

        $success = 0;

        foreach ($fileId as $id) {
            $file = $daoFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $id));

            if (null === $file) {
                continue ;
            }

            if ($file->folderId == $folderId) {
                continue ;
            }

            if ($daoFile->moveFile($this->_user->uniqueId, $id, $folderId)) {
                $success ++;
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['file_move_failure']);
        }

        return $this->json(true, sprintf($this->lang['file_move_success'], $folderId));
    }

    /**
     * 获取共享人群
     */
    public function getMemberAction()
    {
        $objectId = $this->_request->getPost('objid');
        $objType  = $this->_request->getPost('objtype');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        $condition = array(
            'ownerid'    => $this->_user->uniqueId,
            'objectid'   => $objectId,
            'objecttype' => $objType
        );

        $shares = $daoShare->getShare($condition);

        if (!$shares) {
            return $this->json(true, null, null);
        }

        return $this->json(true, null, $shares->toArray());
    }

    /**
     * 更新共享人群
     */
    public function updateMemberAction()
    {
        $objectId = $this->_request->getPost('objid');
        $objType  = $this->_request->getPost('objtype');
        $isShare   = $this->_request->getPost('isshare');
        $targetIds = (array) $this->_request->getPost('targetid');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        $daoShare->deleteShare($objectId, $this->_user->uniqueId);

        $params = array(
            'objectid' => $objectId,
            'ownerid'  => $this->_user->uniqueId,
            'orgid'    => $this->_user->orgId,
            'objecttype' => $objType,
            'ownerinfo'  => $this->_user->userName . "\n" . $this->_user->trueName
        );

        $success = 0;

        foreach ($targetIds as $targetId) {
            $params['targetid'] = $targetId;
            $ret = $daoShare->createShare($params);
            if (!$ret) {
                continue ;
            }
            $success++;
        }

         if (!$success) {
            return $this->json(false, $this->lang['config'] . $this->lang['share_failure']);
        }

        return $this->json(true, $this->lang['config'] . $this->lang['share_success']);

    }

    /**
     * 文件共享
     */
    public function fileShareAction()
    {
        $fileId    = $this->_request->getPost('objid');
        $isShare   = $this->_request->getPost('isshare');
        $targetIds = (array) $this->_request->getPost('targetid');

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        if (!$daoFile->existFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $fileId))) {
            return $this->json(false);
        }

        if ($isShare) {
            $params = array(
                'objectid' => $fileId,
                'ownerid'  => $this->_user->uniqueId,
                'orgid'    => $this->_user->orgId,
                'objecttype' => 'file',
                'ownerinfo'  => $this->_user->userName . "\n" . $this->_user->trueName
            );

            $success = 0;

            foreach ($targetIds as $targetId) {
                $params['targetid'] = $targetId;
                $ret = $daoShare->createShare($params);
                if (!$ret) {
                    continue ;
                }
                $success++;
            }

            $share = $this->lang['config'];
        } else {
            $success = $daoShare->deleteShare($fileId, $this->_user->uniqueId);
            $share = $this->lang['cancel'];
        }

        if (!$success) {
            return $this->json(false, $share . $this->lang['share_failure']);
        }

        $daoFile->updateFile($this->_user->uniqueId, $fileId, array('isshare' => $isShare));

        return $this->json(true, $share . $this->lang['share_success']);
    }

    /**
     * 文件夹共享
     */
    public function folderShareAction()
    {
        $folderId   = $this->_request->getPost('objid');
        $isShare    = $this->_request->getPost('isshare');
        $targetIds  = (array) $this->_request->getPost('targetid');

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        if (!$daoFolder->existsFolder(array('uniqueid' => $this->_user->uniqueId, 'folderid' => $folderId))) {
            return $this->json(false);
        }

        if ($isShare) {
            $params = array(
                'objectid' => $folderId,
                'ownerid'  => $this->_user->uniqueId,
                'orgid'    => $this->_user->orgId,
                'objecttype' => 'folder',
                'ownerinfo'  => $this->_user->userName . "\n" . $this->_user->trueName
            );

            $success = 0;

            foreach ($targetIds as $targetId) {
                $params['targetid'] = $targetId;
                $ret = $daoShare->createShare($params);
                if (!$ret) {
                    continue ;
                }
                $success++;
            }
            $share = $this->lang['config'];
        } else {
            $success = $daoShare->deleteShare($folderId, $this->_user->uniqueId);
            $share = $this->lang['cancel'];
        }

        if (!$success) {
            return $this->json(false, $share . $this->lang['share_failure']);
        }

        $daoFolder->updateFolder($this->_user->uniqueId, $folderId, array('isshare' => $isShare));

        return $this->json(true, $share . $this->lang['share_success']);
    }

    /**
     * 保存到网盘
     */
    public function toNetdiskAction()
    {
        $post     = $this->_request->getPost();
        $isAttach = $this->_request->getPost('isattach');

        /* @var $daoAttachment Dao_Td_Attachment_File */
        $daoAttachment = $this->getDao('Dao_Td_Attachment_File');
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile   = $this->getDao('Dao_Td_Netdisk_File');

        if ($isAttach) {
            $file = $daoAttachment->getFile(array(
                'fileid' => $post['fileid']
            ));
        } else {
            $file = $daoFile->getFile(array('uniqueid' => $post['ownerid'], 'fileid' => $post['fileid']));
        }

        if (!$file) {
            return $this->json(false, $this->lang['file_not_exists']);
        }

        $params = array(
            'uniqueid' => $this->_user->uniqueId,
            'orgid'    => $this->_user->orgId,
            'fileid'   => Dao_Td_Netdisk_File::getFileId(),
            'folderid' => $post['folderid'],
            'filename' => $file->fileName,
            'size'     => $file->size,
            'path'     => $file->path,
            'type'     => $file->type
        );

        // 创建网盘文件记录
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = $this->getDao('Dao_Td_Netdisk_File');
        $existFile = $daoFile->existFile(array(
        	'uniqueid'   => $this->_user->uniqueId,
        	'folderid'   => $post['folderid'],
            'fromfileid' => $post['fileid']
        ));

        if ($existFile) {
            return $this->json(false, $this->lang['file_is_in_here']);
        }

        $ret = $daoFile->createFile($params);

        if ($ret == -1) {
            return $this->json(false, $this->lang['space_not_enough']);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['save_failure']);
        }

        $fromParams = array();
        $fromParams['fromuniqueid'] = $file->fromUniqueId ? $file->fromUniqueId : $file->uniqueId;

        if ($isAttach) {
            $fromParams['attachfileid'] = $post['fileid'];
            $fromParams['fromfileid']   = $post['fileid'];
            $fromParams['isfromattach'] = 1;
        } else {
            if ($file->attachFileId && !$file->fromFileId) {
                $fromParams['fromfileid']   = $file->attachFileId;
                $fromParams['isfromattach'] = 1;
                $fromParams['attachfileid'] = $file->attachFileId;
            } elseif (!$file->attachFileId && $file->fromFileId) {
                $fromParams['fromfileid'] = $file->fromFileId;
            } else {
                $fromParams['fromfileid'] = $post['fileid'];
            }
        }

        $daoFile->updateFile($this->_user->uniqueId, $params['fileid'], $fromParams);

        return $this->json(true, $this->lang['save_success']);
    }

    /**
     * 删除文件
     */
    public function fileDeleteAction()
    {
        $fileId = trim($this->_request->getPost('fileid'));

        $fileId = explode(',', $fileId);

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = $this->getDao('Dao_Td_Netdisk_File');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        $success = 0;
        foreach ($fileId as $id) {
            $file = $daoFile->getFile(array('uniqueid' => $this->_user->uniqueId, 'fileid' => $id));

            if (null === $file) {
                continue ;
            }

            // 如若是共享文件则先删除共享的记录
            if ($file->isShare) {
                $daoShare->deleteShare($id, $this->_user->uniqueId);
            }

            if ($daoFile->deleteFile($this->_user->uniqueId, $id)) {
                $success ++;
            }
        }

        if ($success <= 0) {
            return $this->json(false, $this->lang['file_delete_failure']);
        }

        return $this->json(true, $this->lang['file_delete_success']);
    }

    /**
     * 创建目录
     */
    public function folderCreateAction()
    {
        $folderName = $this->_request->getPost('foldername');

        if (empty($folderName)) {
            return $this->json(false, $this->lang['missing_folder_name']);
        }

        if (in_array($folderName, $this->_denyFolderNames)) {
            return $this->json(false, sprintf($this->lang['folder_exists'], $folderName));
        }

        if (preg_match('/[\?\*\/\\\\<\>\:,\'"]/', $folderName) || mb_strlen($folderName, 'utf-8') > 255) {
            return $this->json(false, $this->lang['invalid_file_name']);
        }

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        if ($daoFolder->existsFolder(array('foldername' => $folderName, 'uniqueid' => $this->_user->uniqueId))) {
            return $this->json(false, sprintf($this->lang['folder_exists'], $folderName));
        }

        $folderId = Dao_Td_Netdisk_Folder::getFolderId();

        $params = array(
            'orgid'          => $this->_user->orgId,
            'uniqueid'       => $this->_user->uniqueId,
            'folderid'       => $folderId,
            'foldername'     => $folderName,
            'parentfolderid' => '^root',
            'createtime'     => time()
        );

        $folderId = $daoFolder->createFolder($params);

        if (!$folderId) {
            return $this->json(false, $this->lang['folder_create_failure']);
        }

        return $this->json(true, $this->lang['folder_create_success']);
    }

    /**
     * 重命名目录
     */
    public function folderRenameAction()
    {
        $folderId   = $this->_request->getPost('folderid');
        $folderName = $this->_request->getPost('foldername');

        if (empty($folderName)) {
            return $this->json(false, $this->lang['missing_folder_name']);
        }

        if (in_array($folderName, $this->_denyFolderNames)) {
            return $this->json(false, sprintf($this->lang['folder_exists'], $folderName));
        }

        if (preg_match('/[\?\*\/\\\\<\>\:,\'"]/', $folderName) || mb_strlen($folderName, 'utf-8') > 255) {
            return $this->json(false, $this->lang['invalid_file_name']);
        }

        /* @var $daoFolder Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        if ($daoFolder->existsFolder(array('foldername' => $folderName))) {
            return $this->json(false);
        }

        $params = array(
            'foldername' => $folderName
        );

        $ret = $daoFolder->updateFolder($this->_user->uniqueId, $folderId, $params);

        if (!$ret) {
            return $this->json(false, $this->lang['folder_rename_failure']);
        }

        return $this->json(true, $this->lang['folder_rename_success']);
    }

    /**
     * 删除目录
     */
    public function folderDeleteAction()
    {
        $folderId = $this->_request->getPost('folderid');

        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = $this->getDao('Dao_Td_Netdisk_File');

        if ($daoFile->getFileCount(array('uniqueid' => $this->_user->uniqueId, 'folderid' => $folderId)) > 0) {
            return $this->json(false, $this->lang['delete_not_null_folder']);
        }

        /* @var $daoFile Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        /* @var $daoShare Dao_Td_Netdisk_Share */
        $daoShare = $this->getDao('Dao_Td_Netdisk_Share');

        $folder = $daoFolder->getFolder(array('uniqueid' => $this->_user->uniqueId, 'orgid' => $this->_user->orgId, 'folderid' => $folderId));

        // 如若是共享文件夹则先删除共享的记录
        if ($folder->isShare) {
            $daoShare->deleteShare($folderId, $this->_user->uniqueId);
        }

        if (!$daoFolder->deleteFolder($this->_user->uniqueId, $folderId)) {
            return $this->json(false, $this->lang['folder_delete_failure']);
        }

        $this->json(true, $this->lang['folder_delete_success']);
    }

    /**
     *
     */
    public function listAction()
    {
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = $this->getDao('Dao_Td_Netdisk_File');

        /* @var $daoFile Dao_Td_Netdisk_Folder */
        $daoFolder = $this->getDao('Dao_Td_Netdisk_Folder');

        $files = $daoFile->getFiles(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId));

        $folders = $daoFolder->getFolders(array('uniqueid' => $this->_user->uniqueId))->toArray('folderid');

        // 创建系统目录
        $reloadFolder = false;
        if (!array_key_exists('^root', $folders)) {
            foreach ($this->_sysFolders as $fid => $item) {
                $item['uniqueid'] = $this->_user->uniqueId;
                $item['orgid']    = $this->_user->orgId;
                $item['createtime'] = $this->_timestamp;

                if ($fid == '^root') {
                    $item['maxquota'] = $this->_user->maxNdQuota;
                }

                $daoFolder->createFolder($item);
            }

            $reloadFolder = true;
        }

        if ($reloadFolder) {
            $folders = $daoFolder->getFolders(array('uniqueid' => $this->_user->uniqueId))->toArray('folderid');
        }

        foreach ($folders as $index => $folder) {
            if (array_key_exists($index, $this->_sysFolders) && $index != '^root' && isset($this->lang['sys_folder'][$folder['foldername']])) {
                $folders[$index]['foldername'] = $this->lang['sys_folder'][$folder['foldername']];
            }
        }

        return $this->json(true, null, array('files' => $files->toArray(), 'folders' => $folders));
    }

	/**
     * 附件保存到网盘
     */
    public function saveAttachAction()
    {
        $post = $this->_request->getPost();

        // 获取附件信息
        /* @var $daoAttachment Dao_Td_Attachment_File */
        $daoAttachment = $this->getDao('Dao_Td_Attachment_File');
        $file = $daoAttachment->getFile(array(
            'fileid' => $post['fileid']
        ));

        $fileId = Dao_Td_Netdisk_File::getFileId();
        $params = array(
            'uniqueid' => $this->_user->uniqueId,
            'orgid'    => $this->_user->orgId,
            'fileid'   => $fileId,
            'folderid' => $post['folderid'],
            'filename' => $file->fileName,
            'size'     => $file->size,
            'path'     => $file->path,
            'type'     => $file->type
        );

        // 创建网盘文件记录
        /* @var $daoFile Dao_Td_Netdisk_File */
        $daoFile = $this->getDao('Dao_Td_Netdisk_File');
        $existFile = $daoFile->existFile(array(
        	'uniqueid'     => $this->_user->uniqueId,
        	'folderid'     => $post['folderid'],
            'attachfileid' => $post['fileid']
        ));

        if ($existFile) {
            return $this->json(false, $this->lang['file_is_in_here']);
        }

        $ret = $daoFile->createFile($params);

        if ($ret == -1) {
            return $this->json(false, $this->lang['space_not_enough']);
        }

        if (!$ret) {
            return $this->json(false, $this->lang['save_failure']);
        }

        // 标记来自附件
        $daoFile->updateFile($this->_user->uniqueId, $fileId, array(
            'isfromattach' => 1,
            'attachfileid' => $post['fileid'],
            'fromuniqueid' => $file->uniqueId
        ));

        return $this->json(true, $this->lang['save_success']);
    }

    /**
     *
     * @param $fid
     * @param $act
     */
    public function getFileUrl($fid, $mod = 'netdisk', $act = null, $ownerId = null)
    {
        $sid  = $this->_sessionId;
        $auth = md5($sid . $fid . $this->session->auth['logintime']);

        $url = !empty($this->options['sites']['file']) ? $this->options['sites']['file'] : $this->options['sites']['www'];
        $url = $this->options['upload']['cgi']['download']
             . "?mod={$mod}&sid={$sid}&fid={$fid}&auth={$auth}";

        if ($act) {
            $url .= '&action=' . $act;
        }

        if ($ownerId) {
            $url .= '&ownerid=' . $ownerId;
        }

        return $url;
    }

    /**
     *
     * @param $size
     */
    public function formatFileSize($size, $base = 1024)
    {
        $units = array(pow($base, 3) => 'GB', pow($base, 2) => 'MB', $base => 'KB');

        foreach ($units as $step => $unit) {
            $val = $size / $step;
            if ($val >= 1) {
                return round($val, 2) . $unit;
            }
        }

        return $size . 'B';
    }
}