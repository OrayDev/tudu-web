<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Push
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * 图度推送消息类
 * 根据苹果官方的叫法，这个类应该被命名为 Oray_Push_Apple_Notification
 *
 * @category   Oray
 * @package    Oray_Push
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Push_Apns_Message
{

    /**
     * 发送正文
     *
     * @var array
     */
    protected $_playload = array(
        'aps' => array()
    );

    /**
     * 发送到的deviceTOken
     *
     * @var array
     */
    protected $_deviceToken = array();

    /**
     *
     * @var Oray_Push_Apple
     */
    protected $_pushAdapter = null;

    /**
     * Constructor
     *
     * @param Oray_Push_Apple $pushAdapter
     */
    public function __construct(Oray_Push_Apple $pushAdapter = null)
    {
        if (null != $pushAdapter) {
            $this->setPushAdapter($pushAdapter);
        }
    }

    /**
     *
     * @param Oray_Push_Apple $pushAdapter
     * @return Oray_Push_Apple_Message
     */
    public function setPushAdapter(Oray_Push_Apple $pushAdapter)
    {
        $this->_pushAdapter = $pushAdapter;
        return $this;
    }

    /**
     * 添加发送目标的devicetoken
     *
     * @param string $deviceToken
     * @return Oray_Push_Apple_Message
     */
    public function addDeviceToken($deviceToken)
    {
        $this->_deviceToken[] = $deviceToken;
        return $this;
    }

    /**
     * 可指定的提醒数字，体现为图标右上角的消息数
     *
     * @param int $badge
     * @return Oray_Push_Apple_Message
     */
    public function setBadge($badge)
    {
        $this->_playload['aps']['badge'] = $badge;
        return $this;
    }

    /**
     * 可由服务器指定的提醒声音
     *
     * @param string $sound
     * @return Oray_Push_Apple_Message
     */
    public function setSound($sound)
    {
        $this->_playload['aps']['sound'] = $sound;
        return $this;
    }

    /**
     * 设置消息的弹出文字
     *
     * @param string $body
     * @return Oray_Push_Apple_Message
     */
    public function setBody($body)
    {
        if (isset($this->_playload['aps']['loc-key'])) {
            unset($this->_playload['aps']['loc-key']);

            if (isset($this->_playload['aps']['loc-args'])) {
                unset($this->_playload['aps']['loc-args']);
            }
        }

        $this->_playload['aps']['body'] = $body;

        return $this;
    }

    /**
     * 以loc-key的方式指定弹出消息文字
     *
     * @param string $key
     * @param array  $args
     * @return Oray_Push_Apple_Message
     */
    public function setLocKey($key, array $locArgs = null)
    {
        if (isset($this->_playload['aps']['body'])) {
            unset($this->_playload['aps']['body']);
        }

        $this->_playload['aps']['loc-key'] = $key;

        if (!empty($locArgs)) {
            $this->_playload['aps']['loc-args'] = $locArgs;
        }

        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed  $value
     * @return Oray_Push_Apple_Message
     */
    public function setCustomAttribute($name, $value)
    {
        if (!is_string($name)) {
            require_once 'Oray/Push/Exception.php';
            throw new Oray_Push_Exception('Invalid attribute name, it must be a string');
        }

        $this->_playload[$name] = $value;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPlayLoad()
    {
        return json_encode($this->_playload);
    }

    /**
     * 执行推送操作
     *
     * @param int $expires
     * @return Oray_Push_Apple_Message
     */
    public function push($expires = 0)
    {
        if (empty($this->_deviceToken)) {
            require_once 'Oray/Push/Exception.php';
            throw new Oray_Push_Exception('There is not any device to push');
        }

        if (empty($this->_playload['aps']) && count($this->_playload) <= 1) {
            require_once 'Oray/Push/Exception.php';
            throw new Oray_Push_Exception('Could not push an empty message to APNs');
        }

        if (!$this->_pushAdapter) {
            require_once 'Oray/Push/Exception.php';
            throw new Oray_Push_Exception('Did not specify an Oray_Push_Apple object for push');
        }

        $data = $this->getPlayLoad();
        foreach ($this->_deviceToken as $item) {
            $this->_pushAdapter->push($item, $data, $expires);
        }

        return $this;
    }
}