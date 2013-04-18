<?php

class TestController extends Zend_Controller_Action
{
    public function uploadAction()
    {
        $html = '<form id="theform" action="//upload-test.tudu.com/orayfile/upload" method="post" enctype="multipart/form-data">'
              . 'sid: <input type="text" id="sid" /><br />'
              . 'email: <input type="text" id="email" /s<br />'
              . 'file: <input type="file" name="filedata" /><br />'
              . '<input type="button" id="btn-upload" value="Upload" onclick="btnClick()" /><br />'
              . '</form>'
              . '<script type="text/javascript">'
              . 'function btnClick() {'
              . 'var f = document.getElementById("theform");'
              . 'f.action = "https://upload-test.tudu.com/orayfile/upload?sid=" + document.getElementById("sid").value + "&email=" + document.getElementById("email").value;'
              . 'f.submit();}</script>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function postAction()
    {
        $html = '<form id="theform" action="//openapi.tudu.com/post/send" method="post">'
              . 'access_token: <input type="text" name="access_token" /><br />'
              . 'postid: <input type="text" name="postid" /><br />'
              . 'tuduid: <input type="text" name="tuduid" /><br />'
              . 'content: <textarea name="content"></textarea><br />'
              . 'percent: <input type="text" name="percent" /><br />'
              . 'ref: <input type="text" name="reference" /><br />'
              . 'reply: <input type="text" name="reply" /><br />'
              . 'elapsedtime: <input type="text" name="elapsedtime" /><br />'
              . 'image1: <input type="text" name="image[]" /><br />'
              . '<input type="submit" id="btn-upload" value="Send" />'
              . '</form>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function suggestAction()
    {
        $html = '<form id="theform" action="//openapi.tudu.com/suggest/send" method="post">'
        . 'access_token: <input type="text" name="access_token" /><br />'
        . 'subject: <input type="text" name="subject" /><br />'
        . 'content: <textarea name="content"></textarea><br />'
        . '<input type="submit" id="btn-upload" value="Send" />'
        . '</form>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function sendAction()
    {
        $html = '<form id="theform" action="//openapi.tudu.com/compose/send" method="post">'
        . 'access_token: <input type="text" name="access_token" /><br />'
        . 'tuduid: <input type="text" name="tuduid" /><br />'
        . 'subject: <input type="text" name="subject" /><br />'
        . 'type: <input type="text" name="type" /><br />'
        . 'boardid: <input type="text" name="boardid" /><br />'
        . 'classid: <input type="text" name="classid" /><br />'
        . 'location: <input type="text" name="location" /><br />'
        . 'to: <textarea name="to"></textarea><br />'
        . 'starttime: <input type="text" name="starttime" /><br />'
        . 'endtime: <input type="text" name="endtime" /><br />'
        . 'cc: <textarea name="cc"></textarea><br />'
        . 'content: <textarea name="content"></textarea><br />'
        . 'image: <input type="text" name="image" /><br />'
        . '<input type="submit" id="btn-upload" value="Send" />'
        . '</form>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function forwardAction()
    {
        $html = '<form id="theform" action="//openapi.tudu.com/compose/forward" method="post">'
        . 'access_token: <input type="text" name="access_token" /><br />'
        . 'tuduid: <input type="text" name="tuduid" /><br />'
        . 'to: <textarea name="to"></textarea><br />'
        . 'starttime: <input type="text" name="starttime" /><br />'
        . 'endtime: <input type="text" name="endtime" /><br />'
        . 'cc: <textarea name="cc"></textarea><br />'
        . 'content: <textarea name="content"></textarea><br />'
        . 'image: <input type="text" name="image" /><br />'
        . '<input type="submit" id="btn-upload" value="Send" />'
        . '</form>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function reviewAction()
    {
        $html = '<form id="theform" action="//openapi.tudu.com/compose/review" method="post">'
              . 'access_token: <input type="text" name="access_token" /><br />'
              . 'tuduid: <input type="text" name="tuduid" /><br />'
              . 'content: <textarea name="content" row="3"></textarea><br />'
              . 'agree: <select name="agree"><option value="1">同意</option><option value="0">不同意</option></select><br />'
              . '<input type="submit" id="btn-submit" value="Review" />'
              . '</form>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function pushAction()
    {

        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'
              . '<form id="theform" action="//openapi.tudu.com/test/push.do" method="post">'
              . 'devicetoken: <input type="text" name="devicetoken" /><br />'
              . 'text: <input type="text" name="body" /><br />'
              . 'loc-key: <select name="loc-key"><option value="">---</option>'
              . '<option value="NEW_TASK">NEW_TASK</option><option value="NEW_DISCUSS">NEW_DISCUSS</option><option value="NEW_NOTICE">NEW_NOTICE</option><option value="NEW_MEETING">NEW_MEETING</option></select><br />'
              //. 'loc-args: <input type="text" name="loc-args" value="" />&nbsp;&nbsp; *separate by ","<br />'
              . 'badge: <input type="text" name="badge" /><br />'
              . 'update: <label for="c1"><input id="c1" type="checkbox" name="ud[]" value="1" /> contacts</label><label for="c2" style="margin-left: 15px"><input id="c2" type="checkbox" name="ud[]" value="2" /> groups</label><label for="c3" style="margin-left: 15px"><input id="c3" type="checkbox" name="ud[]" value="4" /> labels</label><label for="c4"><input id="c4" type="checkbox" name="ud[]" value="8" /> boards</label><br />'
              . 'username: <input type="text" name="u" /><br />'
              . 'tuduid: <input type="text" name="tid" /><br />'
              . 'boardid: <input type="text" name="bid" /><br />'
              . '<input type="submit" id="btn-submit" value="Send" />'
              . '</form>'
              . '</body></html>';

        $this->_response->setHeader('content-Type', 'text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function pushDoAction()
    {
        $options = $this->getInvokeArg('bootstrap')->getOptions();
        $post = $this->_request->getPost();

        $deviceToken = str_replace(array('<', '>'), array('', ''), str_replace(' ', '', $post['devicetoken']));
        $playload    = array(
            'aps' => array()
        );

        if (!empty($post['badge']) && is_numeric($post['badge'])) {
            $playload['aps']['badge'] = (int) $post['badge'];
        }

        if (!empty($post['body'])) {
            $playload['aps']['alert']['body'] = $post['body'];
        } else {
            if (!empty($post['loc-key'])) {
                $playload['aps']['alert']['loc-key']  = $post['loc-key'];
            }

            if (!empty($post['loc-args'])) {
                $playload['aps']['alert']['loc-args'] = $post['loc-args'];
            }
        }

        if (!empty($post['u'])) {
            $playload['u'] = $post['u'];
        }

        if (!empty($post['ud']) && is_array($post['ud'])) {
            $playload['ud'] = 0;
            foreach ($post['ud'] as $item) {
                $playload['ud'] = ($playload['ud'] | (int) $item);
            }
        }

        if (!empty($post['tid'])) {
            $playload['tid'] = $post['tid'];
        }

        if (!empty($post['bid'])) {
            $playload['bid'] = $post['bid'];
        }

        require_once 'Oray/Httpsqs.php';
        $httpsqs = new Oray_Httpsqs($options['httpsqs']['host'], $options['httpsqs']['port'], $options['httpsqs']['chartset'], 'notify');

        $playload = json_encode($playload);

        $httpsqs->put(implode(' ', array(
            'playload',
            '',
            '',
            http_build_query(array(
                'devicetoken' => $deviceToken,
                'playload'    => $playload
            ))
        )), 'notify');

        echo 'success';
        exit;
    }
}
