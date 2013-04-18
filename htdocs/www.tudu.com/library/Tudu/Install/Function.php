<?php
/**
 * Tudu_Install_Function
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @author     Oray-Yongfa
 * @version    $Id: Function.php 2814 2013-04-09 06:02:19Z chenyongfa $
 *
 */
Class Tudu_Install_Function
{
    /**
     * 当前进行的步骤
     *
     * @var int
     */
    protected $_step = 0;

    /**
     *
     * @var array
     */
    protected $_stepMethod = array('show_license', 'env_check', 'set_config', 'install', 'finish');

    /**
     *
     * @var array
     */
    protected $_needFunc = array('mysql_connect', 'pfsockopen', 'file_get_contents', 'file_exists', 'fopen', 'simplexml_load_string', 'xml_parser_create');

    /**
     * 
     * @var string
     */
    protected $_tplPath;

    /**
     *
     * @var string
     */
    protected $_rootPath;

    /**
     * 环境最低要求
     *
     * @var array
     */
    public $_defaultEnv = array('os_version' => 'Linux', 'php_version' => '5.2', 'gdversion' => '1.0', 'attachmentupload' => '2M', 'diskspace' => '100M');

    /**
     * PHP模块
     *
     * @var array
     */
    protected $_needExts = array('gd', 'pdo_mysql', 'session', 'memcache');
    /**
     *
     * @var array
     */
    protected $_needDirFile = array(
        'welcome_tpl' => array('type' => 'file', 'variable' => '+r', 'path' => './data/templates/tudu/welcome.tpl'),
        'www_dist' => array('type' => 'file', 'variable' => '+r', 'path' => './htdocs/www.tudu.com/application/configs/application.ini.dist'),
        'admin_dist' => array('type' => 'file', 'variable' => '+r', 'path' => './htdocs/admin.tudu.com/application/configs/application.ini.dist'),
        'scripts_dist' => array('type' => 'file', 'variable' => '+r', 'path' => './scripts/task/configs/config.ini.dist'),
        'config_www' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './htdocs/www.tudu.com/application/configs'),
        'config_admin' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './htdocs/admin.tudu.com/application/configs'),
        'config_scripts' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './scripts/task/configs'),
        'data' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './data'),
        'install_tpl' => array('type' => 'dir', 'variable' => '+r', 'path' => './data/install/templates'),
        'seccode' => array('type' => 'dir', 'variable' => '+r', 'path' => './data/seccode'),
        'fonts' => array('type' => 'dir', 'variable' => '+r', 'path' => './data/fonts'),
        'caches_www' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './data/caches/tpl/www.tudu.com'),
        'caches_admin' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './data/caches/tpl/admin.tudu.com'),
        'logs' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './logs'),
        'scripts' => array('type' => 'dir', 'variable' => '+r', 'path' => './scripts'),
        'upload' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './upload'),
        'upload_attach' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './upload/attachment'),
        'upload_netdisk' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './upload/netdisk'),
        'upload_loginpic' => array('type' => 'dir', 'variable' => '+r+w', 'path' => './upload/loginpic'),
    );

    /**
     *
     * @var array
     */
    protected $_tables = array(
        'md_access', 'md_cast_disable_dept', 'md_cast_disable_user', 'md_department', 'md_email', 'md_group', 'md_ip_data',
        'md_login_log', 'md_op_log', 'md_org_host', 'md_org_info', 'md_org_iprule', 'md_organization', 'md_role',
        'md_role_access', 'md_site_admin', 'md_user', 'md_user_access', 'md_user_data', 'md_user_email', 'md_user_group',
        'md_user_info', 'md_user_role', 'md_user_session', 'md_user_tips', 'nd_file', 'nd_folder', 'nd_share', 'sph_index_label',
        'td_attach_flow', 'td_attach_post', 'td_attachment', 'td_board', 'td_board_favor', 'td_board_sort', 'td_board_user',
        'td_class', 'td_contact', 'td_contact_group', 'td_contact_group_member', 'td_flow', 'td_flow_favor', 'td_label',
        'td_log', 'td_note', 'td_post', 'td_rule', 'td_rule_filter', 'td_template', 'td_tudu', 'td_tudu_cycle', 'td_tudu_group',
        'td_tudu_label', 'td_tudu_meeting', 'td_tudu_flow', 'td_tudu_user', 'td_vote', 'td_vote_option',
        'td_voter', 'app_app', 'app_info', 'app_info_attach', 'app_org', 'app_user', 'attend_apply', 'attend_apply_reviewer',
        'attend_category', 'attend_checkin', 'attend_date', 'attend_date_apply', 'attend_month', 'attend_schedule',
        'attend_schedule_adjust', 'attend_schedule_adjust_user', 'attend_schedule_rule', 'attend_schedule_plan_month',
        'attend_schedule_plan_week', 'attend_total'
    );

    /**
     *
     * @var Tudu_Install_Function
     */
    protected static $_instance;

    /**
     * 单例模式，隐藏构造函数
     */
    protected function __construct()
    {}

    /**
     * 获取对象实例
     *
     * @return Tudu_Install_Function
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @return Tudu_Install_Function
     */
    public static function newInstance()
    {
        return new self();
    }

    /**
     *
     * @param int $step
     */
    public function setStep($step)
    {
        $this->_step = (int) $step;
    }

    /**
     *
     * @param string $path
     */
    public function setTplPath($path)
    {
        $this->_tplPath = $path;
    }

    /**
     *
     * @param string $path
     */
    public function setRootPath($path)
    {
        $this->_rootPath = $path;
    }

    /**
     * 输出安装错误提示页面
     *
     * @param array $error
     * @throws Tudu_Install_Exception
     */
    public function error($error)
    {
        $tpl = $this->_tplPath . '/error.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("Tpl file:\"{$tpl}\" is not exists");
        }

        $errorHtml = array();
        if (!empty($error['locked'])) {
            $errorHtml[] = '<div style="margin-top:20px;color:#666;"><p>安装锁定，已经安装过了，如果您确定要重新安装，请到服务器上删除</p>';
            $errorHtml[] = "<p>./data/install.lock</p></div>";
            $errorHtml[] = '<div style="margin-top:20px;color:#c00;">您必须解决以上问题，安装才可以继续</div>';
        } else {
            $errorHtml[] = '<div style="margin-top:20px;color:#c00;">' . $error['message'] . '</div>';
        }

        $common = array('error' => implode('', $errorHtml));
        if (!empty($error['url'])) {
            $common['url'] = $error['url'];
        }

        $template = $this->_assignTpl(@file_get_contents($tpl), $common);
        echo $template;
        die;
    }

    /**
     * 处理Json输出
     *
     * @param boolean $success    操作是否成功
     * @param mixed   $params     附加参数
     * @param mixed   $data       返回数据
     * @param boolean $sendHeader 是否发送json文件头
     */
    public function json($success = false, $params = null, $data = false, $sendHeader = true)
    {
        if (is_string($params) || is_numeric($params)) {
            $params = array('message' => $params);
        }

        $json = array('success' => (boolean) $success);

        if (is_array($params)) {
            unset($params['success']);
            $json = array_merge($json, $params); // 可以让success优化显示
        }

        if (false !== $data) {
            $json['data'] = $data;
        }

        $content = json_encode($json);

        header('Content-Type: application/x-javascript; charset=utf-8');

        echo $content;

        exit;
    }

    /**
     * 输出页面模板
     *
     * @throws Tudu_Install_Exception
     */
    public function sendTemplate(array $options = null)
    {
        $tpl = $this->_tplPath . '/step_' . $this->_step . '.tpl';

        if (!file_exists($tpl) || !is_readable($tpl)) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("Tpl file:\"{$tpl}\" is not exists");
        }

        $common = array('step' => $this->_step + 1);
        if (!empty($options)) {
            $common = array_merge($common, $options);
        }

        $template = $this->_assignTpl(@file_get_contents($tpl), $common);
        echo $template;
    }

    /**
     * 返回步骤
     *
     * @param int $step
     */
    public function getMethod($step = null)
    {
        if (empty($step)) {
            $step = $this->_step;
        }

        $method = $this->_stepMethod[$step];
        if (!in_array($method, $this->_stepMethod)) {
            $method = null;
        }

        return $method;
    }

    /**
     * 测试数据库连接
     *
     * @param array  $config
     * @param boolean $checkTables
     */
    public function checkMysqlConnect($config, $checkTables = false)
    {
        if(!function_exists('mysql_connect')) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("undefine function 'mysql_connect'");
        }

        if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("empty 'mysql_connect' params");
        }

        @ini_set("mysql.connect_timeout", "30");
        $linkMysql = @mysql_connect($config['host'] . ':' . $config['port'], $config['username'], $config['password']);
        if (!$linkMysql) {
            $errno = mysql_errno();
            $error = mysql_error();
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception($error, $errno);
        }

        $databases = @mysql_query('show databases');
        $arr = array();
        while($row = mysql_fetch_row($databases)) {
            $arr[] = $row[0];
        }

        if (!in_array($config['dbname'], $arr)) {
            @mysql_close($linkMysql);
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('Exists database (' . $config['dbname'] . ')');
        }

        if ($checkTables) {
            @mysql_select_db($config['dbname'], $linkMysql);
            $this->checkTables($linkMysql);
        }

        @mysql_close($linkMysql);
    }

    /**
     * 检查数据表的完整性
     *
     * @param array $config
     */
    public function checkTables($link)
    {
        $result = @mysql_query('show tables');
        $tables = array();
        while($row = mysql_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $diff = array_diff($this->_tables, $tables);
        if (!empty($diff)) {
            @mysql_close($link);

            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception('Exists Tables (' . implode(',', $diff) . ')');
        }
    }

    /**
     * 测试HttpSQS连接
     *
     * @param array $config
     */
    public function checkHttpsqsConnect($config)
    {
        if(!function_exists('pfsockopen')) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("undefine function 'pfsockopen'");
        }

        if (empty($config['host']) || empty($config['port'])) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("empty params");
        }

        $link = @pfsockopen($config['host'], $config['port'], $errno, $errstr, 5);
        if (!$link) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception($errstr);
        }
    }

    /**
     * 测试Memcache连接
     *
     * @param array $config
     */
    public function checkMemcacheConnect($config)
    {
        if(!class_exists('Memcache')) {
            require_once 'Tudu/Install/Exception.php';
            throw new Tudu_Install_Exception("undefine class 'Memcache'");
        }

        $memcache = new Memcache;
        $link = @$memcache->connect($config['host'], $config['port'], 5);
        if (!$link) {
            return false;
        }

        $memcache->close();
        return true;
    }

    /**
     * 函数依赖性检查
     */
    public function checkFunction()
    {
        $ret = array();
        foreach ($this->_needFunc as $func) {
            $ret[$func] = function_exists($func) ? true : false;
        }
 
        return $ret;
    }

    /**
     *
     * @param string $type
     * @param array  $data
     */
    public function formatHtml($type, $data)
    {
        $html = array();
        if ($type == 'func') {
            foreach($data as $key => $value) {
                $item = "&nbsp";
                if ($value) {
                    $item = "<span class=\"icon-big ib-succeed\"></span>支持";
                } else {
                    $item = "<span class=\"icon-big ib-fail\"></span>不支持";
                }
                $html[] = "<tr><td>{$key}()</td><td>{$item}</td></tr>";
            }
        } elseif ($type == 'dirfile') {
            foreach($data as $key => $item) {
                $current = "-";
                $variable = "-";
                if ($item['variable'] == '+r') {
                    $variable = "可读";
                } elseif ($item['variable'] == '+r+w') {
                    $variable = "可读写";
                }

                if (1 == $item['status']) {
                    $current = "<span class=\"icon-big ib-succeed\"></span>可读写";
                } elseif (-1 == $item['status']) {
                    if ($item['current'] == 'nodir') {
                        $current = "<span class=\"icon-big ib-fail\"></span>目录不存在";
                    } elseif ($item['current'] == 'nofile') {
                        $current = "<span class=\"icon-big ib-fail\"></span>文件不存在";
                    }
                } else {
                    if ($item['variable'] == '+r' && 0 == $item['status']) {
                        $current = "<span class=\"icon-big ib-succeed\"></span>可读";
                    } else {
                        $current = "<span class=\"icon-big ib-fail\"></span>可读不可写";
                    }
                }

                $html[] = "<tr><td>{$item['path']}</td><td>{$variable}</td><td>{$current}</td></tr>";
            }
        } elseif ($type == 'ext') {
            foreach($data as $key => $value) {
                $item = "&nbsp";
                if ($value) {
                    $item = "<span class=\"icon-big ib-succeed\"></span>支持";
                } else {
                    $item = "<span class=\"icon-big ib-fail\"></span>不支持";
                }
                $html[] = "<tr><td>{$key}</td><td>{$item}</td></tr>";
            }
        }

        return implode('', $html);
    }

    /**
     * 检查PHP模板
     */
    public function checkExtensions()
    {
        $ret      = array();
        $exts     = get_loaded_extensions();
        $needExts = $this->_needExts;
        foreach ($needExts as $item) {
            if (!in_array($item, $exts)) {
                $ret[$item] = false;
                continue;
            }

            $ret[$item] = true;
        }

        return $ret;
    }

    /**
     * 目录、文件权限检查
     */
    public function checkDirOrFile()
    {
        $items = array();
        $rootPath = $this->_rootPath;
        foreach($this->_needDirFile as $key => $item) {
            $path = $rootPath . DIRECTORY_SEPARATOR . $item['path'];
            $items[$key]['type'] = $item['type'];
            $items[$key]['path'] = $item['path'];
            $items[$key]['variable'] = $item['variable'];
            if ($item['type'] == 'dir') {
                if(!$this->_dirWriteable($path)) {
                    if(is_dir($path)) {
                        $items[$key]['status'] = 0;
                        $items[$key]['current'] = '+r';
                    } else {
                        $items[$key]['status'] = -1;
                        $items[$key]['current'] = 'nodir';
                    }
                } else {
                    $items[$key]['status'] = 1;
                    $items[$key]['current'] = '+r+w';
                }
            } else {
                if(file_exists($path)) {
                    if(is_writable($path)) {
                        $items[$key]['status'] = 1;
                        $items[$key]['current'] = '+r+w';
                    } else {
                        $items[$key]['status'] = 0;
                        $items[$key]['current'] = '+r';
                    }
                } else {
                    if($this->_dirWriteable(dirname($path))) {
                        $items[$key]['status'] = 1;
                        $items[$key]['current'] = '+r+w';
                    } else {
                        $items[$key]['status'] = -1;
                        $items[$key]['current'] = 'nofile';
                    }
                }
            }
        }

        return $items;
    }

    /**
     * 获取PHP环境
     */
    public function getBaseEnv()
    {
        $ret = array(
            'os_version' => array('current' => PHP_OS, 'success' => 'true'), 
            'php_version' => array('current' => PHP_VERSION, 'success' => 'true'),
            'attachmentupload' => array('current' => @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow', 'success' => 'true')
        );
        // GD Version
        $gdVersion = function_exists('gd_info') ? gd_info() : array();
        $ret['gdversion'] = array('current' => empty($gdVersion['GD Version']) ? 'noext' : $gdVersion['GD Version'], 'success' => 'true');
        // diskspace
        $ret['diskspace'] = array('current' => function_exists('disk_free_space') && !empty($this->_rootPath) ? floor(disk_free_space($this->_rootPath) / (1024*1024)).'M' : 'unknow', 'success' => 'true');

        foreach ($ret as $key => $item) {
            if ($key == 'gdversion') {
                preg_match('/[0-9.]+/',$item['current'],$r);
                $item['current'] = $r[0];
            }
            if ($key == 'php_version' || $key == 'os_version') {
                if(strcmp($item['current'], $this->_defaultEnv[$key]) < 0) {
                        $ret[$key]['success'] = 'false';
                }
            } elseif ($key == 'attachmentupload' || $key == 'diskspace' || $key == 'gdversion') {
                if(intval($item['current']) < intval($this->_defaultEnv[$key])) {
                    $ret[$key]['success'] = 'false';
                }
            }
        }
        return $ret;
    }

    /**
     *
     * @param string $k
     * @param string $t
     */
    public function getgpc($k, $t='GP') {
        $t = strtoupper($t);

        switch($t) {
            case 'GP' : isset($_POST[$k]) ? $var = &$_POST : $var = &$_GET; break;
            case 'G': $var = &$_GET; break;
            case 'P': $var = &$_POST; break;
            case 'C': $var = &$_COOKIE; break;
            case 'R': $var = &$_REQUEST; break;
        }

        return isset($var[$k]) ? $var[$k] : null;
    }

    /**
     * 目录是否可以写
     *
     * @param string $dir
     */
    private function _dirWriteable($dir) {
        $writeable = 0;
        if(!is_dir($dir)) {
            @mkdir($dir, 0777);
        }

        if(is_dir($dir)) {
            if($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }

        return $writeable;
    }

    /**
     * 替换模板的数据
     *
     * @param string $tpl
     * @param array $data
     */
    private function _assignTpl($tpl, $data, $prefix = '')
    {
        $ret = $tpl;

        if (!empty($prefix)) {
            $prefix = $prefix . '.';
        }

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $ret = $this->_assignTpl($ret, $val, $prefix . $key);
            } else {
                $ret = str_replace('{$' . $prefix . $key . '}', $val, $ret);
            }
        }

        return $ret;
    }
}