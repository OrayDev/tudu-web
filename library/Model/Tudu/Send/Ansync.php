<?php
/**
 * Tudu Library
 * 图度投递服务客户端对象
 *
 * LICENSE
 *
 *
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @category   Model
 * @package    Model_Tudu
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Model_Tudu_Send_Ansync implements Model_Tudu_Send_Interface
{

    const CODE_SUCCESS    = 0;
    const CODE_DBERROR    = 1;
    const CODE_BADREQUEST = 2;
    const CODE_ERROR      = 3;
    const CODE_TIMEOUT    = 4;

    /**
     *
     * @var Tudu_User
     */
    protected $_user = null;

    /**
     *
     * @var resource
     */
    protected $_connection = null;

    /**
     *
     * Constructor
     */
    public function __construct()
    {
        $this->_user = Tudu_User::getInstance();
    }

    /**
     * 发送图度操作
     *
     * @param Model_Tudu_Tudu $tudu
     */
    public function send(Model_Tudu_Tudu &$tudu)
    {

        $object = array(
            'tuduid'   => $tudu->tuduId,
            'uniqueid' => $this->_user->unqiueId,
            'orgid'    => $this->_user->orgId,
            'type'     => $tudu->type,
            'action'   => 'send',
            'operator' => array(
                'username' => $this->_user->userName,
                'truename' => $this->_user->trueName
            )
        );

        $receiver = array();

        if ($tudu->reviewer) {
            $reviewers = $tudu->reviewer[0];

            foreach ($reviewers as $reviewer) {
                $item = array(
                    'username' => $reviewer['username'],
                    'truename' => $reviewer['truename']
                );

                if (isset($reviewer['unqiueid'])) {
                    $item['uniqueid'] = $reviewer['uniqueid'];
                }

                $receiver['reviewer'][] = $item;
            }

        } elseif ($tudu->to) {
            $to = $tudu->to[0];
            foreach ($to as $u) {
                $item = array(
                    'username' => $u['username'],
                    'truename' => $u['truename'],
                    'role'     => 'to',
                    'percent'  => isset($u['percent']) ? $u['percent'] : 0,
                    'status'   => isset($u['status']) ? $u['status'] : (empty($u['percent']) || $u['percent'] < 0 ? 0 : ($u['percent'] >= 100 ? 2 : 1))
                );

                $receiver['to'][] = $item;
            }
        }

        if ($tudu->cc && ($tudu->type != 'notice' || !$tudu->reviewer)) {
            $cc = $tudu->cc;
            foreach ($cc as $u) {
                $receiver['cc'][] = array(
                    'username' => $u['username'],
                    'truename' => $u['truename']
                );
            }
        }

        if ($tudu->bcc) {
            $bcc = $tudu->bcc;
            foreach ($bcc as $u) {
                $receiver['bcc'][] = array(
                    'username' => $u['username'],
                    'truename' => $u['truename']
                );
            }
        }

        $object['receiver'] = $receiver;

        $error = null;
        do {
            $connection = $this->getConnection();

            $bytes = @fwrite(json_encode($object), $connection);

            if (!$bytes) {
                $error = 'Data transfer error, posting tudu data to deliver service failed.';
                break ;
            }

            $response = @fgets($connection);

            if (!$response || !($resposne = @json_decode($response, true))) {
                $error = 'Invalid response data.';
                break ;
            }

        } while (false);

        if ($error) {
            require_once 'Model/Tudu/Exception.php';
            throw new Model_Tudu_Exception($error);
        }

        return $response['code'] == self::CODE_SUCCESS;
    }

    /**
     *
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = fsockopen();
        }

        return $this->_connection;
    }

    /**
     *
     */
    public function closeConnection()
    {
        if ($this->_connection) {
            fclose($this->_connection);
        }
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->closeConnection();
    }
}