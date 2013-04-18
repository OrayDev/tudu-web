<?php
/**
 * Test Controller
 *
 * @author Hiro
 * @version $Id: TestController.php 1957 2012-07-02 06:54:25Z web_op $
 */

class TestController extends TuduX_Controller_Base
{
    public function init()
    {
        parent::init();

        //$this->_helper->viewRenderer->setNoRender();
        $this->getFrontController()->getDispatcher()->setParam('disableOutputBuffering', true);
    }

    public function indexAction()
    {}

    public function infoAction()
    {
    	phpinfo();
    }

    public function boardAction() {
        $boards = $this->getBoards(true);

        $user = $this->_user->toArray();

        $this->view->registModifier('format_board_list', array($this, 'formatBoardList'));

        $this->view->boards = $boards;
    }

    public function optionsAction()
    {
        print_r($this->options);
    }

    public function envAction()
    {
    	echo APPLICATION_ENV;
    }

    public function preDispatch()
    {
    }

    public function flushAction()
    {
        $this->_response->setHeader('Test', 'good');
        $this->_response->sendHeaders();

        for($i = 0; $i < 3; $i++) {
            echo "$i<br>";
			flush();
			ob_flush();
            sleep(1);
        }

        echo "end";

        $this->getFrontController()->returnResponse(true);
        //$this->_response->clearAllHeaders();
    }

    public function iframeAction()
    {
        $uuid = 47367;
        $sign = md5($uuid . '&DF77B11A9C0CBE37EE4281E6A1BE573E');

        $url = 'http://online-app.tudu.com/login-app/?from=UU&uu_id=' . $uuid . '&uu_sign=' . $sign;

        $this->view->url = $url;
    }

    /**
     *
     */
    public function uuAction()
    {
        $uuid = 47367;
        $sign = md5($uuid . '&DF77B11A9C0CBE37EE4281E6A1BE573E');

        $url = 'http://online-app.tudu.com/login-app/?from=UU&uu_id=' . $uuid . '&uu_sign=' . $sign;

        $this->view->url = $url;
    }

    /**
     * 欢迎公告
     */
    public function welcomeAction()
    {
    	$orgId = $this->_request->getQuery('orgid', 'oray');

    	$daoOrg = $this->getMdDao('Dao_Md_Org_Org');

    	$org = $daoOrg->getOrg(array('orgid' => $orgId));

    	if (null === $org) {
    		exit('org not exists');
    	}

        // 添加组织公告
        $content = file_get_contents($this->options['data']['path'] . '/tudu/template/welcome.tpl');
        if ($content) {
            $deliver = new Tudu_Deliver($this->getTsDb());

            $tudu = array(
                'orgid'  => $orgId,
                'tuduid' => md5($orgId . '-welcome'),
                'boardid' => '^system',
                'uniqueid' => '^system',
                'type' => 'notice',
                'subject' => '欢迎使用图度工作管理系统！！',
                'email' => '^system',
                'from' => '^system 图度',
                'to' => null,
                'cc' => null,
                'priority' => 0,
                'privacy' => 0,
                'status' => Dao_Td_Tudu_Tudu::STATUS_UNSTART,
                'content' => $content,
                'poster' => '图度',
                'posterinfo' => '',
                'lastposter' => '图度',
                'lastposttime' => time(),
                'createtime' => time(),
                'attachment' => array()
            );

            $ret = $deliver->createTudu($tudu);
            $deliver->sendTudu($tudu['tuduid'], array());

            exit($ret ? 'success' : 'error');
        }

        exit('tpl not exists or content is null');
    }


    public function getStatusAction()
    {
        //yezi@oray.com|yuyingbin@oray.com|lvrongheng@oray.com|luojiexia@oray.com|zhangcan@oray.com

        $email = $this->_request->getQuery('email', '');

        // 获取联系人的IM在线信息
        $config = $this->bootstrap->getOption('im');
        $im = new Oray_Im_Client($config['host'], $config['port']);
        $imStatus = $im->getUserStatus(explode(',', $email));

        var_dump($imStatus);

        echo "<pre>";
        echo $im->getRequest();
        echo "\n";
        echo htmlspecialchars($im->getResult());
    }

    public function httpsqsAction()
    {
        $config = $this->bootstrap->getOption('httpsqs');

        $httpsqs = new Oray_Httpsqs($config['host'], $config['port'], $config['chartset'], $config['name']);

        do {
            $data = $httpsqs->get();
            echo $data . "\n";

        } while ('HTTPSQS_GET_END' != $data);

        $httpsqs->closeConnection();

        echo "end";
    }

    /**
     *
     * @param array $boards
     */
    public function formatBoardList($boards)
    {
        foreach ($boards as &$zone) {
            if (!empty($zone['children'])) {
                foreach($zone['children'] as &$item) {
                    unset($item['memo'], $item['groups'], $item['moderators']);
                }
            }
        }

        return json_encode($boards);
    }
}