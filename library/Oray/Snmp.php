<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Snmp.php 7327 2011-12-28 11:41:58Z yiqinfei $
 */

/**
 * @category   Oray
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Snmp
{
    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = array(
        'ip'             => null,            //服务器IP
        'port'           => 161,             //端口
        'version'        => 3,               // snmp 版本 0:v1(暂时不用) 1: v2c 3:v3
        'retry_times'    => 3,               // 每个udp请求重试次数
        'time_out'       => 8000,           // 每个udp请求到有回应时所等待的最大时间 (毫秒)
        'security_level' => 2,               // 加密级别 1: 不加密 2：需要验证用户名和密码 3：需要验证用户名和密码并且发送的数据加密
        'user'           => null,            // snmp用户名
        'password'       => null,            // snmp密码
        'user_encrypted_algorithm' => 'MD5', // 用户名密码加密协议 MD5 SHA
        'community'      => 'public',        // 取决于服务器设置的值
    );

    /**
     * 等待请求的时间
     *
     * @var int
     */
    protected $waitForResponseTime = 60000;

    /**
     * SNMP引擎
     *
     * @var object
     */
    protected $_snmpEngine = null;

    /**
     * 错误信息
     *
     * @var string
     */
    protected $_error = null;

    /**
     * construct
     *
     */
    public function __construct(array $options)
    {
        // 加载扩展
        if(!extension_loaded('snmp_plugin')) {
            dl('snmp_plugin.' . PHP_SHLIB_SUFFIX);
        }

        // 检测需要的类
        $classes = array(
            'PHPSnmpEngine', 'SnmpServerInfo', 'PHPSystemDescRequest',
            'PHPEnumCPUUtilizationRateRequest', 'PHPEnumNetworkRequest',
            'PHPEnumStorageRequest', 'PHPEnumDiskIORequest',
        );
        foreach ($classes as $class) {
            if(!class_exists($class)){
                throw new Oray_Exception("Class {$class} not exist");
            }
        }

        // 初始化配置
        while (list($name, $value) = each($options)) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }

        // 设置配置到SnmpServerInfo类
        $ServerInfo = new SnmpServerInfo();
        foreach ($this->_options as $name => $value) {
            $ServerInfo->$name = $value;
        }

        // 设置连接超时
        $ServerInfo->connect_server_timeout = 20000;

        // 实例化引擎、连接服务器
        $this->_snmpEngine = new PHPSnmpEngine();
        $this->_snmpEngine->ConnectSnmpServer($ServerInfo);

    }

    /**
     *
     * 校验SNMP配置是否正确
     * @param array $serverConfig
     */
    public function verify()
    {
        // 获取系统描述请求，由于系统描述每个平台都有，所以可以用来验证snmp用户名和密码
        $SystemDescRequest = new PHPSystemDescRequest;

        // 发送请求
        $this->_snmpEngine->SendRequest($SystemDescRequest);

        // 由于操作是异步的，所以要用这个函数等待结果
        $SystemDescRequest->WaitForResponse($this->waitForResponseTime);

        // 取系统描述
        $desc = $SystemDescRequest->GetSystemDescription();

        if ('' == $desc) {
            // 如果系统描述是空，则取错误，判断是哪种错误：
            // -35:用户名密码错误
            // -58:不存在该结点
            $error = $SystemDescRequest->GetLastError();
            $this->setError($error);
            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * 获取设备
     * @param string $typeId (cpu, netio, diskstore, diskio)
     * @return array
     */
    public function getDevices($typeId)
    {
        // 各任务类型对应的类名
        $types = array(
            'cpu' => 'PHPEnumCPUUtilizationRateRequest',
            'netio' => 'PHPEnumNetworkRequest',
            'diskstore' => 'PHPEnumStorageRequest',
            'diskio' => 'PHPEnumDiskIORequest',
        );

        if (!isset($types[$typeId])) {
            return false;
        }

        $devices = array();
        $class = $types[$typeId];
        $request = new $class;
        $this->_snmpEngine->SendRequest($request);
        $request->WaitForResponse($this->waitForResponseTime);

        // 获取错误
        $error = $request->GetLastError();
        if ($error) {
            $this->setError($error);
            return false;
        }

        $items = $request->php_items();

        while (list($k, $item) = each($items)) {

            // 磁盘需要过滤一下数据   硬盘： 2  内存：3 虚拟内存：5
            if ($typeId == 'diskstore' && $item->enType != 2) {
                continue;
            }

            // 过滤掉不工作的网卡
            if ($typeId == 'netio' && $item->nOperStatus != 1) {
                continue;
            }

            $devices[$k]['type_id'] = $typeId;
            $devices[$k]['device_id'] = $item->sIndex;
            $devices[$k]['info'] = isset($item->sDesc) ? $item->sDesc : '';
        }

        return $devices;
    }

    /**
     *
     * 获取错误信息
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     *
     * 设置错误信息
     * @param sring $error
     */
    protected function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * 释放内存
     */
    public function __destruct()
    {
        $this->_snmpEngine->SetAutoClose();
        $this->_snmpEngine->CloseStream();

    }

}