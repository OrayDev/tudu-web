<?php
/**
 * Upload Controller
 *
 * @version $Id: AttachmentController.php 2835 2013-04-18 09:03:12Z chenyongfa $
 */

/**
 * @see Dao_Td_Attachment_File
 */
require_once 'Dao/Td/Attachment/File.php';

class AttachmentController extends TuduX_Controller_Base
{
    const UPLOAD_ATTACHMENT = 'attachment';
    const UPLOAD_NETDISK    = 'netdisk';

    private $_enableMimes = array(
        'image' => array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/wbmp')
    );

    /**
     * 支持的上传文件类型（附件，网盘）
     *
     * @var array
     */
    private $_supportMod = array(self::UPLOAD_ATTACHMENT, self::UPLOAD_NETDISK);

    /**
     *
     */
    public function init()
    {
        if ($this->_request->getQuery('sid')) {
            $_COOKIE[session_name()] = $this->_request->getQuery('sid');
        }
        if ($this->_request->getQuery('email')) {
            $_COOKIE['email'] = $this->_request->getQuery('email');
            $_COOKIE['username'] = $this->_request->getQuery('email');
        }

        if ($cookies = $this->_request->getParam('cookies')) {
            if ($cookies = @unserialize($cookies)) {
                foreach ($cookies as $key => $val) {
                    $_COOKIE[$key] = $val;
                }
            }
        }

        parent::init();

        // 取消缓冲区
        $this->getFrontController()->getDispatcher()->setParam('disableOutputBuffering', true);
    }

    /**
     *
     */
    public function preDispatch()
    {
        $this->lang = Tudu_Lang::getInstance()->load(array('common'));

        if (!$this->_user->isLogined()) {
            return $this->json(false, $this->lang['login_timeout']);
        }
    }

    /**
     * 下载附件
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $act    = $this->_request->getParam('act', $this->_request->getQuery('action'));
        $fileId = $this->_request->getQuery('fid', $this->_request->getQuery('aid'));
        $mod    = $this->_request->getParam('mod', self::UPLOAD_ATTACHMENT);

        if ($mod == self::UPLOAD_ATTACHMENT) {

            /* @var $file Dao_Td_Attachment_Record_File */
            $file = $this->getDao('Dao_Td_Attachment_File')->getFile(array('fileid' => $fileId));

            if (null === $file
               || (!$file->tuduId && $file->uniqueId != $this->_user->uniqueId))
            {
                Oray_Function::alert($this->lang['file_not_exists']);
                return false;
            }

            if ($file->tuduId) {
                $tudu = $this->getDao('Dao_Td_Tudu_Tudu')->getTuduById($this->_user->uniqueId, $file->tuduId);

                $boards = $this->getBoards(false);

                $board = $boards[$tudu->boardId];

                $isModerators = array_key_exists($this->_user->userId, $board['moderators']);
                $inGroups     = (boolean) sizeof(array_uintersect($this->_user->groups, $board['groups'], "strcasecmp"));

                $isSuperModerator = (!empty($board['parentid'])
                                     && array_key_exists($this->_user->userId, $boards[$board['parentid']]['moderators']));

                // 禁止访问
                if (null === $tudu
                   || (!$tudu->uniqueId && !$isModerators && !$isSuperModerator && !$inGroups))
                {
                    Oray_Function::alert($this->lang['file_deny_access']);
                    return false;
                }
            }
        } elseif ($mod == self::UPLOAD_NETDISK) {
            /* @var $daoFile Dao_Td_Netdisk_File */
            $daoFile = $this->getDao('Dao_Td_Netdisk_File');
            $ownerId = $this->_request->getQuery('ownerid');

            $file = $daoFile->getFile(array('uniqueid' => $ownerId, 'fileid' => $fileId));
            if (null === $file) {
                Oray_Function::alert($this->lang['file_not_exists']);
                return false;
            }

            /* @var $daoShare Dao_Td_Netdisk_Share */
            $daoShare = $this->getDao('Dao_Td_Netdisk_Share');
            $share = $daoShare->getShare(array('objectid' => $fileId, 'targetid' => $this->_user->userName, 'ownerid' => $ownerId));
            if (null === $file) {
                Oray_Function::alert($this->lang['file_not_exists']);
                return false;
            }
        } else {
            Oray_Function::alert($this->lang['file_not_exists']);
            return false;
        }

        $option = $this->bootstrap->getOption('upload');

        $path = $option['path'] . '/' . $file->path. '/' . $file->fileId;

        if (!file_exists($path)) {
            return Oray_Function::alert($this->lang['file_not_exists']);
        }

        $this->_response->setHeader('Content-Type', $file->type . ', charset=utf-8');
        $this->_response->setHeader('Content-Length', $file->size);

        $type = $act == 'view' ? 'inline' : 'attachment';
        // FF Only
        if (false !== strpos(strtolower($this->_request->getServer('HTTP_USER_AGENT')), 'firefox')) {
            $this->_response->setHeader('Content-Disposition', $type . ';filename*=UTF-8\'\'' . urlencode($file->fileName));

        // Other
        } else {
            $this->_response->setHeader('Content-Disposition', $type . ';filename=' . urlencode($file->fileName));
        }

        $this->_response->sendHeaders();

        $fp = fopen($path, 'rb');
        while (!feof($fp)) {
            echo fread($fp, 4096);
            @flush();
            @ob_flush();
        }

        fclose($fp);

        // 取消输出 - 主要避免再次输出文件头，两种方式，第一种比较直接
        $this->getFrontController()->returnResponse(true);
        //$this->_response->clearAllHeaders();
    }

    /**
     * 输出临时图片文件(只输出未关联到任何回复的附件，主要用于编辑器新上传的图片显示)
     */
    public function imgAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $fid = $this->_request->getQuery('fid', $this->_request->getQuery('aid'));

        /* @var $file Dao_Td_Attachment_Record_File */
        $file = $this->getDao('Dao_Td_Attachment_File')->getFile(array('fileid' => $fid));
        if (null === $file || $file->uniqueId != $this->_user->uniqueId) {
            return ;
        }

        $this->_request->setParam('act', 'view');

        return $this->downloadAction();
    }

    /**
     *
     * @return boolean
     */
    public function downloadAction()
    {
        return $this->indexAction();
    }

    /**
     * 上传
     */
    public function uploadAction()
    {
        $file = $_FILES['filedata'];
        $data = array();

        if (empty($file['name'])) {
            return $this->json(false, $this->lang['file_upload_null'], false, false);
        }

        if (is_uploaded_file($file['tmp_name'])) {

            if ($file['size'] > $this->options['upload']['sizelimit']) {
                return $this->json(false, sprintf($this->lang['file_too_large'], $file['name'], $file['size']), false, false);
            }

            $mime = $this->_getMime($file);
            if ($enables = $this->_request->getParam('enables')) {
                if (!in_array($mime, $this->_enableMimes[$enables])) {
                    return $this->json(false, $this->lang['invalid_file_type'], false, false);
                }
            }

            $mod = $this->_request->getParam('mod', self::UPLOAD_ATTACHMENT);
            if (!in_array($mod, $this->_supportMod)) {
                $mod = self::UPLOAD_ATTACHMENT;
            }

            $fileId = $mod == self::UPLOAD_ATTACHMENT ? Dao_Td_Attachment_File::getFileId() : Dao_Td_Netdisk_File::getFileId();
            $path = $mod . '/' . substr($fileId, 0, 1);

            $filePath = $this->options['upload']['path'] . '/' . $path;
            if (!is_dir($filePath)) {
                mkdir($filePath, 0777, true);
            }

            $params = array(
                'fileid' => $fileId,
                'filename' => $file['name'],
                'size' => $file['size'],
                'type' => $mime,
                'path' => $path,
                'uniqueid' => $this->_user->uniqueId,
                'orgid' => $this->_user->orgId
            );

            if ($mod == self::UPLOAD_NETDISK) {
                $params['folderid'] = $this->_request->getParam('folderid', '^root');

                /* @var $daoFile Dao_Td_Netdisk_File */
                $daoFile = $this->getDao('Dao_Td_Netdisk_File');
            } else {

                /* @var $daoFile Dao_Td_Attachment_File */
                $daoFile = $this->getDao('Dao_Td_Attachment_File');
            }
            $ret = $daoFile->createFile($params);

            if ($mod == self::UPLOAD_NETDISK && $ret == -1) {
                return $this->json(false, $this->lang['space_not_enough'], false, false);
            }

            if (!$ret) {
                return $this->json(false, sprintf($this->lang['file_upload_failure'], $file['name']), false, false);
            }

            $ret = @move_uploaded_file($file['tmp_name'], $filePath . '/' . $fileId);

            if (!$ret) {
                return $this->json(false, sprintf($this->lang['file_upload_failure'], $file['name']), false, false);
            }

            $data['fileid'] = $fileId;

            $format = $this->_request->getParam('format', 'json');
            if ($format == 'script') {
                $data['success'] = true;
                $data['message'] = 'success';
                return $this->script($data);
            } else {
                return $this->json(true, $this->lang['file_upload_success'], $data, false);
            }
        }

        $this->json(false, sprintf($this->lang['file_upload_failure'], $file['name']), false, false);
    }

    /**
     * 下载全部
     */
    public function allAction()
    {
        $postId = $this->_request->getQuery('pid');
    }

    /**
     * 代理上传文件
     */
    public function proxyAction()
    {
        $this->_helper->viewRenderer->setNeverRender();

        $data  = $this->_request->getPost('data');
        $label = $this->_request->getPost('label');

        if (null === $data) {
            return $this->json(false, $this->lang['file_upload_null']);
        }

        $sid  = Zend_Session::getId();
        $url  = $this->options['upload']['cgi']['upload']
              . "?sid={$sid}&email={$this->_user->address}";
        $data = base64_decode($data);
        $path = parse_url($this->options['sites']['file'] . '/');

        $host = $path['host'];

        $boundary = substr(md5(time()), 0, 12);

        $body   = implode("\r\n", array(
           '-----------------------------' . $boundary, // boundary Start
           'Content-Disposition: from-data; name="filedata"; filename="/tmp/img"',
           "Content-Type: application/octet-stream\r\n",
           $data,
           '-----------------------------' . $boundary  // boundary End
        )) . "\r\n";
        $header = array(
           "POST {$url} HTTP/1.1",
           "Host: {$host}",
           'User-Agent: Mozilla/4.0',
           "Content-Type: multipart/form-data; boundary=---------------------------{$boundary}",
           'Connection: close',
           'Content-Length: ' . strlen($body),
           'Cache-Control: no-cache'
        );

        $errorno  = null;
        $errorstr = null;

        $post = implode("\r\n", $header);
        $post .= "\r\n\r\n" . $body;

        $fp = fsockopen($path['host'], 80, $errorno, $errorstr);

        if ($fp) {
            fwrite($fp, $post);

            $response = '';
            $i = 0;
            while ($row = fread($fp, 1024)) {
                $response .= $row;
            }

            fclose($fp);

            $ret = preg_match('/\{[^\}]+\}/', $response, $matches);

            $data = array('label' => $label);
            if ($ret) {
                $this->_response->setHeader('Content-Type', 'application/json');

                $json = $matches[0];
                $json = substr($json, 1, strlen($json) - 2);

                $arr = explode(',', $json);
                $success = false;
                foreach ($arr as $item) {
                    list($k, $v) = explode(':', $item);
                    $k = trim($k, '"');
                    $v = trim($v, '"');

                    if ($k == 'success') {
                        $success = (boolean) $v;
                    } elseif ($k == 'fileid') {
                        $data['fileid'] = $v;
                    }
                }

                if ($success) {
                    return $this->json(true, null, $data);
                } else {
                    return $this->json(false, sprintf($this->lang['file_upload_failure'], 's'), $data);
                }
            } else {
                return $this->json(false, sprintf($this->lang['file_upload_failure'], ''), $data);
            }

        } else {
            return $this->json(false, sprintf($this->lang['file_upload_failure'], ''), $data);
        }
    }

    /**
     * 返回脚本
     *
     * @param mixed $params
     * @param boolean $sendHeader
     */
    public function script($params = null, $sendHeader = true)
    {
        $data = '{}';
        if (!empty($params)) {
            $data = json_encode($params);
        }

        $html = <<<HTML
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<script type="text/javascript">
if (typeof parent.uploadCallback== 'function'){
parent.uploadCallback.call(parent, $data
);
}
</script>
</body>
</html>
HTML;

        $response = $this->getResponse();
        if ($sendHeader) {
            $response->setHeader('Content-Type', 'text/html; charset=utf-8');
        }
        $response->setBody($html);
        $response->sendResponse();

        exit;
    }

    /**
     * 获取上传文件 mime 信息
     *
     * @param array $file
     */
    private function _getMime(array $file)
    {
        if (function_exists('mime_content_type')) {
            return mime_content_type($file['tmp_name']);
        }

        $ext = strtolower(array_pop(explode('.', $file['name'])));

        $ret = 'application/octet-stream';

        switch ($ext) {
            case 'jpeg': case 'jpg':
                $ret = 'image/jpeg';
                break;
            case 'gif': case 'bmp': case 'png': case 'tiff':
                $ret = 'image/' . $ext;
                break;
            case 'css': case 'html': case 'xml':
                $ret = 'text/' . $ext;
                break;
            case 'htm':
                $ret = 'text/html';
                break;
            case 'php': case 'jsp': case 'asp': case 'js': case 'java': case 'pl': case 'txt':
                $ret = 'text/plain';
                break;
            case 'doc': case 'docx':
                $ret = 'application/msword';
                break;
            case 'xls': case 'xlt': case 'xltx': case 'csv':
                $ret = 'application/vnd.ms-excel';
                break;
            case 'ppt': case 'pptx': case 'pps':
                $ret = 'application/vnd.ms-powerpoint';
                break;
            case 'zip': case 'rtf': case 'pdf':
                $ret = 'application/' . $ext;
                break;
            case 'swf':
                $ret = 'application/x-shockwave-flash';
                break;
        }

        return $ret;
    }
}