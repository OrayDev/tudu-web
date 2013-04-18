<?php
/**
 * Upload Controller
 * 
 * @version $Id: AttachmentController.php 717 2011-04-25 05:14:31Z cutecube $
 */

/**
 * @see Dao_Td_Attachment_File
 */
require_once 'Dao/Td/Attachment/File.php';

class Foreign_AttachmentController extends TuduX_Controller_Foreign
{
    /**
     * 
     * @var Dao_Td_Attachment_File
     */
    private $_daoFile = null;
    
    private $_enableMimes = array(
        'image' => array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/wbmp')
    );
    
    /**
     * 
     */
    public function init()
    {
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
        
        if (null === $this->_tudu || null === $this->_user) {
            $this->jump('/foreign/index/invalid');
        }
        
        if (!$this->_isValid()) {
            $this->jump("/foreign/index/?tid={$this->_tudu->tuduId}&fid={$this->_user['uniqueid']}&ts={$this->_tsId}");
        }
        
        $this->_daoFile = $this->getDao('Dao_Td_Attachment_File');
    }
    
    /**
     * 输出临时图片文件(只输出未关联到任何回复的附件，主要用于编辑器新上传的图片显示)
     */
    public function imgAction()
    {
        $this->_helper->viewRenderer->setNeverRender();
        
        $fid = $this->_request->getQuery('aid');
        
        /* @var $file Dao_Td_Attachment_Record_File */
        $file = $this->getDao('Dao_Td_Attachment_File')->getFile(array('fileid' => $fid));
        
        if (null === $file || $file->tuduId || $file->uniqueId != $this->_user['uniqueid']) {
            return ;
        }
        
        $sid  = Zend_Session::getId();
        $auth = md5($sid . $fid . $this->_session->auth['logintime']);
        
        $url = $this->_options['sites']['file']
             . $this->_options['upload']['cgi']['download']
             . "?sid={$sid}&fid={$fid}&auth={$auth}&email={$this->_session->auth['address']}&action=view";
        
        $content = Oray_Function::httpRequest($url);
        
        $this->_response->setHeader('Content-Length', strlen($content));
        $this->_response->setHeader('Content-Type', $file->type);
        $this->_response->sendHeaders();
        
        echo $content;
        
        // 取消输出
        $this->getFrontController()->returnResponse(true);
    }
    
    /**
     * 下载全部
     */
    public function allAction()
    {
        $postId = $this->_request->getQuery('pid'); 
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