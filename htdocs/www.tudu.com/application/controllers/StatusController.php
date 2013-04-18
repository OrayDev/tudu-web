<?php
/**
 * Status Controller
 *
 * @author Hiro
 * @version $Id: StatusController.php 2008 2012-07-20 09:56:23Z cutecube $
 */

class StatusController extends TuduX_Controller_Base
{
    // KEY
    private $_key = 'webstatus';

    public function init()
    {
        parent::init();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function preDispatch()
    {
        if ($this->_request->getParam('key') !== $this->_key) {
            throw new Zend_Controller_Action_Exception("404 - Not Found", 404);
        }
    }

    public function infoAction()
    {
    	phpinfo();
    }

    public function deleteTestAction()
    {
        @unlink('TestController.php');
    }

    public function envAction()
    {
    	echo APPLICATION_ENV;
    }

    public function checkApiAction()
    {

        $config = $this->bootstrap->getOption('im');
        $email = $this->_request->getQuery('email', '');
        $url = "http://" . $config['host'] . ":" . $config['port'] . '/getuserstatus?jids=' . urlencode(implode('|', $email));

        echo "<pre>";
        echo $url . "\n" . file_get_contents($url);
    }

    public function cacheAction()
    {
        // cache of tips
        $this->cache->delete('TUDU-TIPS-zh_CN');
        $this->cache->delete('TUDU-TIPS-zh_TW');
        $this->cache->delete('TUDU-TIPS-en_US');
        $this->cache->delete('TUDU-USER-LIST-oray');

        echo 'success';
    }

    public function getStatusAction()
    {
        $email = $this->_request->getQuery('email', '');

        // 获取联系人的IM在线信息
        $config = $this->bootstrap->getOption('im');
        $im = new Oray_Im_Client($config['host'], $config['port']);
        $imStatus = $im->getUserStatus(explode(',', $email));

        header('Accept-Ranges: bytes');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: inline;filename=response.txt');

        echo $im->getResult();

    }

    /**
     * 把当前时间戳写入Session所在的memcache服务器
     */
    public function sessMarkAction()
    {
        $time = time();

        $optSession = $this->options['resources']['session'];
        if ($optSession['save_handler'] == 'memcache') {
            $sessionUrl = parse_url($optSession['save_path']);
            $sessionCache = new Memcache();
            $sessionCache->connect($sessionUrl['host'], $sessionUrl['port']);

            if ($sessionCache->set('TUDU-SESS-MARK', time())) {
                echo $time;
                exit();
            }
        }

        echo 'ERROR';
        exit();
    }

    /**
     * 连接资源监控
     */
    public function checkConfigAction()
    {
        //
        $message = array();
        $ts      = time();

        $count   = array(
            'warning' => 0,
            'error'   => 0
        );

        $queueName   = 'check';
        $memcacheKey = 'TUDU-CHECK-MARK';

        if (defined('WWW_ROOT')) {

            $sites = array(
                'www.tudu.com',
                'api.tudu.com',
                'admin.tudu.com',
                'web.tudu.com'
            );

            // 先写入数据，遍历各站点时读取
            // memcache
            $ret = $this->cache->set($memcacheKey, $ts, null);
            if (!$ret) {
                $message[] = "[ERROR] Memcache prepare failure";
                $count['error']++;
            } else {
                $message[] = "[SUCCESS] Memcache prepare success";
            }
            $this->cache->close();

            // httpsqs
            $cfg  = $this->options['httpsqs'];
            $httpsqs = new Oray_Httpsqs($cfg['host'], $cfg['port'], $cfg['chartset'], $queueName);
            $ret = $httpsqs->put($ts, $queueName);

            if (!$ret) {
                $count['error']++;
                $message[] = "[ERROR] Httpsqs prepare failure";
            } else {
                $message[] = "[SUCCESS] Httpsqs prepare success";
            }
            $queueStatus = $httpsqs->status($queueName);

            if ($count['error'] <= 0) {
                foreach ($sites as $site) {
                    $configFile = WWW_ROOT . '/htdocs/' . $site . '/application/configs/application.ini';
                    $message[] = '-=' . $site . '=-';
                    try {
                        $config = new Zend_Config_Ini($configFile, 'production');
                    } catch (Zend_Config_Exception $e) {
                        $message[] = '[ERROR] Config read failure';
                        continue ;
                    }

                    $config = $config->toArray();

                    if (!empty($config['resources']['multidb'])) {
                        // 遍历数据库配置
                        foreach ($config['resources']['multidb'] as $k => $opt) {
                            $dbParams    = array();
                            $adapterName = '';

                            foreach ($opt as $n => $v) {
                                if ($n == 'adapter') {
                                    $adapterName = $v;
                                } else {
                                    $dbParams[$n] = $v;
                                }
                            }

                            try {
                                $db = Zend_Db::factory($adapterName, $dbParams);

                                if (false !== strpos($adapterName, 'oci')) {
                                    $db->query("SELECT 1 FROM TUDU_LOG");
                                } else {
                                    $db->query("SELECT 1");
                                }
                                $message[] = "[SUCCESS] Database \"{$k}\" success";
                            } catch (Zend_Db_Exception $e) {
                                $message[] = "[ERROR] Database \"{$k}\" failure: " . $e->getMessage();
                                $count['error']++;
                            }
                        }
                    }

                    // 匹配memcache，获取指定key，匹配当前检测插入的值
                    if (!empty($config['resources']['memcache'])) {
                        $memcache = new Oray_Memcache($config['resources']['memcache']);

                        $mv = $memcache->get($memcacheKey);

                        if ($mv != $ts) {
                            $message[] = "[WARNING] memcache get failure or value not match:{$mv}";
                            $count['warning'] ++;
                        } else {
                            $message[] = "[SUCCESS] memcache match success";
                        }

                        $memcache->close();
                    }

                    // 匹配httpsqs
                    if (!empty($config['httpsqs'])) {
                        $cfg     = $config['httpsqs'];
                        $charset = isset($cfg['chartset']) ? $cfg['chartset'] : $cfg['charset'];
                        $httpsqs = new Oray_Httpsqs($cfg['host'], $cfg['port'], $charset, $queueName);

                        $status = $httpsqs->status($queueName);
                        if (!$status || $status['put'] != $queueStatus['put']
                            || $status['get'] != $queueStatus['get'] || $status['unread'] != $queueStatus['unread'])
                        {
                            $message[] = "[WARNING] httpsqs status not match";
                            $count['warning'] ++;
                        } else {
                            $message[] = "[SUCCESS] httpsqs status match success";
                        }
                    }
                }

                // 检测脚本配置
                $scriptConfig = WWW_ROOT . '/scripts/task/configs/config.ini';
                $message[]    = "-=task=-";
                $config       = null;
                try {
                    $config = new Zend_Config_Ini($scriptConfig, 'production');
                } catch (Zend_Config_Exception $e) {
                    $message[] = '[ERROR] Config read failure';
                }

                if ($config) {
                    $config = $config->toArray();

                    if (!empty($config['multidb'])) {
                        foreach ($config['multidb'] as $k => $opt) {
                            try {
                                $db = Zend_Db::factory($opt['adapter'], $opt['params']);

                                if (false !== strpos($opt['adapter'], 'oci')) {
                                    $db->query("SELECT 1 FROM TUDU_LOG");
                                } else {
                                    $db->query("SELECT 1");
                                }

                                $message[] = "[SUCCESS] Database \"{$k}\" success";
                            } catch (Zend_Db_Exception $e) {
                                $message[] = "[WARNING] Database \"{$k}\" failure: " . $e->getMessage();
                                $count['warning']++;
                            }
                        }
                    }

                    if (!empty($config['memcache'])) {
                        $cfg = $config['memcache'];
                        $cfg['servers'] = array(
                            'host' => $cfg['host'],
                            'port' => $cfg['port'],
                            'timeout' => $cfg['timeout']
                        );

                        $memcache = new Oray_Memcache($cfg);

                        $mv = $memcache->get($memcacheKey);

                        if ($mv != $ts) {
                            $message[] = "[WARNING] memcache get failure or value not match: {$mv}";
                            $count['warning'] ++;
                        } else {
                            $message[] = "[SUCCESS] memcache match success";
                        }
                    }

                    // 匹配httpsqs
                    if (!empty($config['httpsqs'])) {
                        $cfg     = $config['httpsqs'];
                        $charset = isset($cfg['chartset']) ? $cfg['chartset'] : $cfg['charset'];
                        $httpsqs = new Oray_Httpsqs($cfg['host'], $cfg['port'], $charset, $queueName);

                        $status = $httpsqs->status($queueName);
                        if (!$status || $status['put'] != $queueStatus['put']
                            || $status['get'] != $queueStatus['get'] || $status['unread'] != $queueStatus['unread'])
                        {
                            $message[] = "[WARNING] httpsqs status not match";
                            $count['warning'] ++;
                        } else {
                            $message[] = "[SUCCESS] httpsqs status match success";
                        }

                        $memcache->close();
                    }
                }

            } else {
                $message[] = "[ERROR] Undefined \"WWW_ROOT\"";
                $count['error']++;
            }
        }

        $message[] = '';
        $message[] = "Check complete, {$count['warning']} warnings, {$count['error']} errors";

        $message = implode("\n", $message);

        header('Accept-Ranges: bytes');
        header('Content-Type', 'text/plain; charset=utf-8');
        //header('Content-Length', strlen($message));
        echo $message;
        exit();
    }

    /**
     * 数据库备份监控
     */
    public function backupCheckAction()
    {
        // 备份数据库配置(s)
        $configs = new Zend_Config_Ini(APPLICATION_PATH . '/configs/status.ini');

        $backupDbs = $configs->backdb;

        $offsetLimit = 900; // 偏移最大值（秒）限制，超过此值则报警

        $options = $this->options['resources']['multidb'];

        $message = array();
        // 遍历当前使用的数据库
        foreach ($options as $dbKey => $param) {
            if (empty($backupDbs->{$dbKey})) {
                continue ;
            }

            $configs = $backupDbs->{$dbKey};

            $mainDb = $this->multidb->getDb($dbKey);
            $backDb = Zend_Db::factory($configs);

            if ($dbKey == 'md') {
                $sql = 'SELECT UNIX_TIMESTAMP(create_time) FROM md_user ORDER BY create_time DESC LIMIT 1';
            } else {
                $sql = 'SELECT create_time FROM td_post ORDER BY create_time DESC LIMIT 1';
            }

            try {
                $mainRec = (int) $mainDb->fetchOne($sql);
                $backRec = (int) $backDb->fetchOne($sql);

                $offset = abs($mainRec - $backRec);

                // 时间差超过限界时发出警报
                $message[$dbKey] = "Database \"{$dbKey}[{$configs->params->host}]\" offset:{$offset} - "
                                 . ($offset > $offsetLimit
                                    ? '[FAILURE]'
                                    : '[PASS]');
            } catch (Zend_Db_Exception $e) {
                $message[$dbKey] = "Database \"{$dbKey}\" - [ERROR]";
            }
        }

        $message = implode("\n", $message);

        header('Content-Type', 'text/plain; charset=utf-8');
        header('Content-Length', strlen($message));
        echo $message;
    }

    public function errorLogAction()
    {
        $content = file_get_contents(WWW_ROOT . "/logs/error.log");

        header('Accept-Ranges: bytes');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: inline;filename=error.log');
        echo $content;
    }

    public function logAction()
    {
        if (isset($this->options['resources']['log']['file']['writerParams']['stream'])) {
            $content = @file_get_contents($this->options['resources']['log']['file']['writerParams']['stream']);
        } else {
            $content = 'empty';
        }

        header('Accept-Ranges: bytes');
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: inline;filename=www-tudu-com.log');
        echo $content;
    }

}