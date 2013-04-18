<?php
/**
 * TuduX Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Push
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Access.php 2121 2012-09-19 05:15:22Z web_op $
 */

/**
 * @category   Tudu
 * @package    Tudu_Push
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Push_Apple
{

    /**
     *
     * @var unknown_type
     */
    protected $_options = array(
        'host'     => 'gateway.sandbox.push.apple.com',
        'protocol' => 'ssl:',
        'port'     => 2195,
        'cert'     => '',
        'pass'     => 'oray.com',
        'timeout'  => 300,

        'feedback' => array(
            'host' => 'feedback.sandbox.push.apple.com',
            'port' => 2196
        )
    );

    /**
     *
     * @var resource
     */
    protected $_fd;

    /**
     *
     * @var context
     */
    protected $_context;

    /**
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($this->_options as $key => $item) {
            if (isset($options[$key])) {
                $this->_options[$key] = $options[$key];
            }
        }

        return $this;
    }

    /**
     *
     */
    public function connect()
    {
        $this->_context = stream_context_create();
        stream_context_set_option($this->_context, 'ssl', 'local_cert', $this->_options['cert']);
        stream_context_set_option($this->_context, 'ssl', 'passphrase', $this->_options['pass']);

        $err = $errstr = null;

        $this->_fd = stream_socket_client(
            $this->_options['protocol'] . '//' . $this->_options['host'] . ':' . $this->_options['port'],
            $err,
            $errstr,
            $this->_options['timeout'],
            STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
            $this->_context
        );

        if ($err) {
            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception("Connect to apple push server failed with  error: CODE[{$err}] {$errstr};");
        }

        return $this;
    }

    /**
     *
     * @param string $deviceToken
     * @param array  $playload
     * @param int    $expires
     * @throws Tudu_Push_Exception
     * @return Tudu_Push_Apple
     */
    public function push($deviceToken, array $playload, $expires = 0)
    {
        $content = json_encode($playload);
        $pid     = rand(0, pow(2, 16));

        // simple
        //$msg = chr(0) . pack("n", 32) . pack("H*", $deviceToken) . pack("n", strlen($content)) . $content;
        // full
        $msg    = chr(1) . pack("N", $pid) . pack("N", $expires) . pack("n", 32) . pack("H*", $deviceToken) . pack("n", strlen($content)) . $content;
        $result = fwrite($this->_fd, $msg, strlen($msg));

        if (!$result) {
            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception("Connect to apple push server failed", -1);
        }

        $sr = array($this->_fd);
        $sw = null; // Temporaries. "Only variables can be passed as reference."
        $numChanged = stream_select($sr, $sw, $sw, 1, null);
        if (false === $numChanged) {
            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception("Failed selecting stream to read.", -1);
        }

        // 获取返回数据
        if ($numChanged > 0) {
            $command = ord(fread($this->_fd, 1));
            $status  = ord(fread($this->_fd, 1));
            $identifier = implode('', unpack("N", fread($this->_fd, 4)));

            $statusDesc = array(
                0 => 'No errors encountered',
                1 => 'Processing error',
                2 => 'Missing device token',
                3 => 'Missing topic',
                4 => 'Missing payload',
                5 => 'Invalid token size',
                6 => 'Invalid topic size',
                7 => 'Invalid payload size',
                8 => 'Invalid token',
                255 => 'None (unknown)',
            );

            if($status>0) {
                $desc = isset($statusDesc[$status])?$statusDesc[$status]: 'Unknown';

                require_once 'Tudu/Push/Exception.php';
                throw new Tudu_Push_Exception("APNS responded with error for pid($identifier). status($status: $desc)", $status);

                $this->close();
            }

            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception("APNS responded with command($command) status($status) pid($identifier).", $status);
        }

        return $this;
    }

    /**
     *
     * @param string $deviceToken
     */
    public function feedback()
    {
        if (empty($this->_options['feedback'])) {
            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception('Undefined host for feedback service');
        }

        $err = null;
        $errStr = null;

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->_options['cert']);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $this->_options['pass']);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        $fp = stream_socket_client(
            $this->_options['protocol'] . '//' . $this->_options['feedback']['host'] . ':' . $this->_options['feedback']['port'],
            $error,
            $errStr,
            100,
            (STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT),
            $ctx
        );

        if (!$fp) {
            require_once 'Tudu/Push/Exception.php';
            throw new Tudu_Push_Exception("Failed to connect to feedback service. with error: {$errStr}, code: {$err}");
        }

        $ret = array();
        while ($devcon = fread($fp, 38)){
            $arr    = unpack("H*", $devcon);
            $rawhex = trim(implode("", $arr));
            $token  = substr($rawhex, 12, 64);

            $ret[] = $token;
        }

        fclose($fp);
        return $ret;
    }

    /**
     *
     */
    public function alert($deviceToken, $message)
    {
        $object = array(
            'aps' => array(
                'alert' => $message,
                'sound' => 'default'
            )
        );

        return $this->push($deviceToken, $object);
    }

    /**
     *
     */
    public function close()
    {
        fclose($this->_fd);
        $this->_context = null;

        return $this;
    }
}