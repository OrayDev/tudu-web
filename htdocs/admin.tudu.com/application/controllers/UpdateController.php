<?php
/**
 * Update Controller
 * 升级图度
 *
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @author     Oray-Yongfa
 * @version    $Id: UpdateController.php 2831 2013-04-18 01:32:35Z chenyongfa $
 */
class UpdateController extends TuduX_Controller_Admin
{
    /**
     * 下载更新文件的存放目录
     *
     * @var string
     */
    protected $_updatePath;

    /**
     * 检查更新的地址
     *
     * @var string
     */
    protected $_upgradeUrl = 'http://www.tudu.com/upgrade/lastest?product=web';

    /**
     * (non-PHPdoc)
     * @see TuduX_Controller_Admin::init()
     */
    public function init()
    {
        parent::init();

        $this->_updatePath    = WWW_ROOT . '/upgrade';
        $this->view->options  = $this->_options;
        $this->view->basepath = $this->_request->getBasePath();
    }

    /**
     * 登录验证
     */
    public function preDispatch()
    {
        if (!$this->_user->isAdminLogined()) {
            $this->destroySession();
            $this->referer($this->_request->getBasePath() . '/login/');
        }
    }

    /**
     * 在线升级
     */
    public function indexAction()
    {
        $operation = $this->_request->getParam('operation');
        $this->view->operation   = $operation;
        $this->view->tuduversion = TUDU_VERSION;

        if (empty($operation)) {
            return $this->render('index');
        }

        if ($operation == 'check') {
            // 检查是否有新版本
            $check   = Oray_Function::httpRequest($this->_upgradeUrl);
            $upgrade = json_decode($check, true);
            if (empty($upgrade['lastest'])) {
                $this->view->unupgrade = true;
                return $this->render('index');
            }

            $lastest = $upgrade['lastest'];
            if (TUDU_VERSION == $lastest['version'] && TUDU_RELEASE == $lastest['release']) {
                $this->view->islastest = true;
                return $this->render('index');
            }

            // WWW_ROOT目录是否可写
            if (!$this->dirWriteable(WWW_ROOT)) {
                $this->view->diswriteable = true;
                $this->view->rootpath = WWW_ROOT;
                return $this->render('index');
            }

            $this->view->checkfinsh = true;
            $this->view->lastest    = $lastest;
        } elseif ($operation == 'upgrade') {
            $downloadUrl = $this->_request->getParam('fileurl');
            $lastestMd5  = $this->_request->getParam('filemd5');
            $upgradeFile = $this->downloadFile($downloadUrl, 120);

            if ($upgradeFile == 'empty_url' || $upgradeFile == 'timeout' || $upgradeFile == 'mkdir_error') {
                return $this->showMsg($upgradeFile);
            }

            // 验证MD5
            if (md5_file($upgradeFile) != $lastestMd5) {
                return $this->showMsg('md5_error');
            }

            // 解压缩
            $ret = $this->unZip($upgradeFile);
            if (!$ret['success']) {
                return $this->showMsg('unzip_error');
            }

            // 判断目录文件是否有相应的文件
            $dirs      = $this->getDirs($this->_updatePath, true);
            $errorPerm = array();
            foreach ($dirs as $item) {
                if ($item['path'] == $upgradeFile) {
                    continue;
                }

                $destDir = WWW_ROOT . DIRECTORY_SEPARATOR . $item['entry'];
                if (!$this->checkPerm($destDir, $item['type'])) {
                    $errorPerm[] = $destDir;
                }
            }

            if (!empty($errorPerm)) {
                return $this->showMsg('error_perm', $errorPerm);
            }

            // 复制替换目录文件
            $dirs = $this->getDirs($this->_updatePath);
            foreach ($dirs as $item) {
                $this->copyDir($item['path'], WWW_ROOT . DIRECTORY_SEPARATOR . $item['entry']);
            }

            // 完成,删除更新的目录；清除smarty模板编译缓存
            $this->rmdirs($this->_updatePath);
            $this->clearSmartyCache();
            return $this->showMsg('upgrade_finish');
        }
    }

    /**
     * 处理出错提示
     *
     * @param string $method
     * @param array $error
     */
    public function showMsg($method, array $error = null)
    {
        if ($method == 'md5_error') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">无法完成更新，下载的更新包md5校验码与官网要求的校验码不一致！</h4>');
</script>
JS;
        } elseif ($method == 'timeout') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">下载更新文件包超时，请重试！</h4>');
</script>
JS;
        } elseif ($method == 'mkdir_error') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">创建保存更新文件包的目录失败，请检查站点跟目录权限！</h4>');
</script>
JS;
        } elseif ($method == 'empty_url') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">下载更新地址出错，请联系图度客服！</h4>');
</script>
JS;
        } elseif ($method == 'unzip_error') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">解压缩更新文件包失败，请重试！</h4>');
</script>
JS;
        } elseif ($method == 'upgrade_finish') {
            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle2">恭喜您，图度系统已更新到最新版本了！</h4>');
</script>
JS;
        } elseif ($method == 'error_perm') {
            $errorHtml = array();
            if (!empty($error)) {
                $i = 1;
                $sum = count($error);
                foreach($error as $item) {
                    if ($sum == $i) {
                        $errorHtml[] = '<p>' . $i . '：' . $item . '</p>';
                    } else {
                        $errorHtml[] = '<p>' . $i . '：' . $item . ',</p>';
                    }
                    $i ++;
                }
            }
            $errorHtml = implode('', $errorHtml);

            $html = <<<JS
<script type="text/javascript">
$('#operation-box').empty();
$('#operation-box').append('<h4 class="infotitle3">无法完成更新，以下目录或文件没有可写权限！</h4>{$errorHtml}');
</script>
JS;
        }

        echo $html;
    }

    /**
     * 获取下载解压后需要升级的目录
     * $isDeep = true, 递归目录跟文件
     *
     * @return array
     */
    public function getDirs($path = null, $isDeep = false, $suffix = '')
    {
        if (empty($path)) {
            $path = $this->_updatePath;
        }

        $dir = @opendir($path);
        $arr = array();

        while($entry = @readdir($dir)) {
            if($entry != '.' && $entry != '..') {
                $file = $path . DIRECTORY_SEPARATOR . $entry;
                if ($isDeep) {
                    if (is_dir($file)) {
                        $arr[] = array('type' => 'dir', 'path' => $file, 'entry' => $suffix . $entry);
                        $deep  = self::getDirs($file, true, $suffix . $entry . DIRECTORY_SEPARATOR);
                        $arr   = array_merge($arr, $deep);
                    } elseif (is_file($file)) {
                        $arr[] = array('type' => 'file', 'path' => $file, 'entry' => $suffix . $entry);
                    }
                } else {
                    if (is_dir($file)) {
                        $arr[] = array('type' => 'dir', 'path' => $file, 'entry' => $suffix . $entry);
                    }
                }
            }
        }

        closedir($dir);
        return $arr;
    }

    /**
     * 循环递归目录，复制文件到目标目录
     *
     * @param string $srcDir
     * @param string $destDir
     */
    public function copyDir($srcDir, $destDir)
    {
        if (!file_exists($srcDir)) {
            return false;
        }

        if (!file_exists($destDir)) {
            $this->mkdirs($destDir);
        }

        $handle = @opendir($srcDir);
        while($file = @readdir($handle)) {
            if($file != '.' && $file != '..') {
                $srcFile = $srcDir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcFile)) {
                    self::copyDir($srcFile, $destDir . DIRECTORY_SEPARATOR . $file);
                } elseif (is_file($srcFile)) {
                    @copy($srcFile, $destDir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        closedir($handle);
    }

    /**
     * 解压文件
     *
     * @param string $file
     * @return array
     */
    public function unZip($file)
    {
        if (!is_file($file)) {
            return array('success' => false, 'error' => 9, 'errormsg' => 'update zip file exists', 'file' => $file);
        }

        require_once 'Oray/Unzip.php';
        /* @var $unzip Oray_Unzip */
        $unzip  = Oray_Unzip::getInstance();
        $zip    = $unzip->readFile($file);
        $return = array('success' => true);

        foreach ($zip as $item) {
            if ($item->error != 0) {
                $return = array('success' => false, 'error' => $item->error, 'errormsg' => $item->errormsg, 'file' => $item->path . '/' . $item->name);
                break;
            }
            $dir = $this->_updatePath . DIRECTORY_SEPARATOR . $item->path;
            if (!$this->mkdirs($dir)) {
                $return = array('success' => false, 'error' => 10, 'errormsg' => 'mkdir failed', 'path' => $item->path);
                break;
            }
            $fp = @fopen($dir . DIRECTORY_SEPARATOR . $item->name, 'wb');
            if (!$fp) {
                $return = array('success' => false, 'error' => 8, 'errormsg' => 'fopen file failed', 'file' => $dir . '/' . $item->name);
                break;
            }
            @fwrite($fp, $item->data);
            @fclose($fp);
        }

        $return['count'] = $unzip->count();
        return $return;
    }

    /**
     * 下载更新包
     */
    public function downloadFile($downloadUrl, $timeout = 30)
    {
        if (empty($downloadUrl)) {
            return 'empty_url';
        }

        $matches = parse_url($downloadUrl);
        $dir     = $this->_updatePath;
        $file    = !empty($matches['path']) ? $matches['path'] : '/';

        // 创建文件夹
        $ret = $this->mkdirs(dirname($dir . $file));
        if (!$ret) {
            return 'mkdir_error';
        }

        $_fp = @fopen($downloadUrl, 'rb');
        if (!$_fp) {
            return false;
        }

        $df = '';

        stream_set_blocking($_fp, true);
        stream_set_timeout($_fp, $timeout);
        $status = stream_get_meta_data($_fp);
        if(!$status['timed_out']) {
            $df = stream_get_contents($_fp);
            if ($df) {
                $fp = @fopen($dir . $file, 'wb');
                if (!$fp) {
                    return false;
                }
                @fwrite($fp, $df);
                @fclose($fp);
            }
        } else {
            return 'timeout';
        }

        @fclose($_fp);

        if (!is_file($dir . $file)) {
            return false;
        }

        return $dir . $file;
    }

    /**
     * 创建文件夹
     *
     * @param string $dir
     * @return boolean
     */
    public function mkdirs($dir)
    {
        if (empty($dir)) {
            return false;
        }

        if(!is_dir($dir)) {
            if(!self::mkdirs(dirname($dir))) {
                return false;
            }
            if(!@mkdir($dir, 0777)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 递归删除文件夹（含文件）
     *
     * @param string $srcdir
     */
    public function rmdirs($srcdir, $delDir = true) {
        $dir = @opendir($srcdir);

        while($entry = @readdir($dir)) {
            if($entry != '.' && $entry != '..') {
                $file = $srcdir . DIRECTORY_SEPARATOR . $entry;
                if(is_dir($file)) {
                    self::rmdirs($file . '/', $delDir);
                } elseif (is_file($file)) {
                    @unlink($file);
                }
            }
        }

        closedir($dir);
        if ($delDir) {
            rmdir($srcdir);
        }
    }

    /**
     * 清楚Smarty模板缓存
     */
    public function clearSmartyCache()
    {
        // 后台
        $this->rmdirs($this->getCachePath('admin'), false);
        // 前台
        $this->rmdirs($this->getCachePath('www'), false);
    }

    /**
     * 获取Smarty模板路径
     *
     * @param stirng $key
     * @return string
     */
    public function getCachePath($key)
    {
        $path = '';
        switch ($key) {
            case 'www':
                $iniFile = WWW_ROOT . '/htdocs/www.tudu.com/application/configs/application.ini';
                if (file_exists($iniFile)) {
                    require_once 'Zend/Config/Ini.php';
                    $cfg = new Zend_Config_Ini($iniFile, 'production');
                    $cfg = $cfg->toArray();

                    if (!empty($cfg['smarty']['compile_dir'])) {
                        $path = $cfg['smarty']['compile_dir'];
                    }
                }

                if (empty($path)) {
                    $path = WWW_ROOT . '/data/caches/tpl/www.tudu.com';
                }
                break;
            case 'admin':
                if (!empty($this->_options['smarty']['compile_dir'])) {
                    $path = $this->_options['smarty']['compile_dir'];
                } else {
                    $iniFile = WWW_ROOT . '/htdocs/admin.tudu.com/application/configs/application.ini';
                    if (file_exists($iniFile)) {
                        require_once 'Zend/Config/Ini.php';
                        $cfg = new Zend_Config_Ini($iniFile, 'production');
                        $cfg = $cfg->toArray();

                        if (!empty($cfg['smarty']['compile_dir'])) {
                            $path = $cfg['smarty']['compile_dir'];
                        }
                    }
                }

                if (empty($path)) {
                    $path = WWW_ROOT . '/data/caches/tpl/admin.tudu.com';
                }
                break;
        }

        return $path;
    }

    /**
     * 检查权限（目录，文件）
     * 不能写的目录或文件，中断升级
     */
    public function checkPerm($path, $type = 'file')
    {
        if (empty($path)) {
            return false;
        }

        $isWrite = false;
        if (file_exists($path)) {
            if ($type == 'file') {
                $isWrite = is_writable($path);
            } else {
                $isWrite = $this->dirWriteable($path);
            }
        } else {
            $isWrite = true;
        }

        return $isWrite;
    }

    /**
     * 目录是否可以写
     *
     * @param string $dir
     */
    public function dirWriteable($dir) {
        $writeable = false;
        if(!is_dir($dir)) {
            @mkdir($dir, 0777);
        }

        if(is_dir($dir)) {
            $fp = @fopen("$dir/test.txt", 'w');
            if($fp) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = true;
            } else {
                $writeable = false;
            }
        }

        return $writeable;
    }
}
