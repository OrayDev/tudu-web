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
     * @var array
     */
    protected $_options = array(
        'host' => '127.0.0.1',
        'port' => 16661,
        'timeout' => 30
    );

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
    public function __construct(array $options = null)
    {
        $this->_user = Tudu_User::getInstance();

        if (!empty($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
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
            'tsid'     => $this->_user->tsId,
            'uniqueid' => $this->_user->uniqueId,
            'orgid'    => $this->_user->orgId,
            'from'     => $this->_user->userName,
            'type'     => $tudu->type,
            'host'     => $_SERVER['HTTP_HOST'],
            'action'   => $tudu->operation,
            'iscreate' => !$tudu->fromTudu || $tudu->fromTudu->isDraft,
            'isflow'   => !!$tudu->flowId,
            'parentid' => $tudu->fromTudu ? $tudu->fromTudu->parentId : $tudu->parentId,
            'operator' => array(
                'uniqueid' => $this->_user->uniqueId,
                'username' => $this->_user->userName,
                'truename' => $this->_user->trueName
            )
        );

        if ($tudu->operation == 'review') {
            $sqsAction = 'review';
            $object['stepid'] = $tudu->fromTudu->stepId;
            $object['agree']  = $tudu->agree;

            if ($tudu->flowId) {
                $flow = $tudu->getExtension('Model_Tudu_Extension_Flow');

                if ($flow) {
                    $object['flow']['nstepid']     = $flow->currentStepId;
                    $object['flow']['flowid']      = $tudu->flowId;
                    $object['flow']['stepstatus']  = $flow->currentStepId != $tudu->fromTudu->stepId ? 1 : 0;
                }
            }

            if ($tudu->type == 'notice' && $tudu->stepId == '^end') {
                $sqsAction = 'create';
            }
        }

        $receiver = array();

        if ($tudu->reviewer) {
            $reviewers = $tudu->reviewer;

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
            $to = $tudu->to;

            foreach ($to as $u) {
                if (!Oray_Function::isEmail($u['username'])) {
                    $item = array(
                        'username' => $u['username'],
                        'truename' => $u['truename'],
                        'role'     => 'to',
                        'percent'  => isset($u['percent']) ? $u['percent'] : 0,
                        'status'   => isset($u['status']) ? $u['status'] : (empty($u['percent']) || $u['percent'] < 0 ? 0 : ($u['percent'] >= 100 ? 2 : 1))
                    );
                } else {
                    $item = array(
                        'email'    => $u['username'],
                        'truename' => $u['truename'],
                        'role'     => 'to',
                        'percent'  => isset($u['percent']) ? $u['percent'] : 0,
                        'status'   => isset($u['status']) ? $u['status'] : (empty($u['percent']) || $u['percent'] < 0 ? 0 : ($u['percent'] >= 100 ? 2 : 1))
                    );
                }

                $receiver['to'][] = $item;
            }
        }

        if ($tudu->cc && ($tudu->type != 'notice' || !$tudu->reviewer)) {
            $cc = $tudu->cc;
            foreach ($cc as $u) {
                if (!empty($u['groupid'])) {
                    $receiver['cc'][] = array(
                        'groupid' => $u['groupid']
                    );
                } else {
                    $receiver['cc'][] = array(
                        'username' => $u['username'],
                        'truename' => $u['truename']
                    );
                }
            }
        }

        if ($tudu->bcc) {
            $bcc = $tudu->bcc;
            foreach ($bcc as $u) {
                if ($u['groupid']) {
                    $receiver['bcc'][] = array(
                        'groupid' => $u['groupid']
                    );
                } else {
                    $receiver['bcc'][] = array(
                        'username' => $u['username'],
                        'truename' => $u['truename']
                    );
                }
            }
        }

        $object['receiver'] = $receiver;

        $error = null;
        do {
            $connection = $this->_getConnection();

            $bytes = @fwrite($connection, json_encode(array('type' => 'queue', 'data' => $object)));

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
            throw new Model_Tudu_Exception('连接发送服务器失败，请稍候重试');
        }

        // 标记发送
        Tudu_Dao_Manager::getDao('Dao_Td_Tudu_Tudu', Tudu_Dao_Manager::DB_TS)->sendTudu($tudu->tuduId);

        return $response['code'] == self::CODE_SUCCESS;
    }

    /**
     *
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $errno = $errstr = null;
            $this->_connection = fsockopen($this->_options['host'], $this->_options['port'], $errno, $errstr, $this->_options['timeout']);

            if ($errno) {
                require_once 'Model/Tudu/Exception.php';
                throw new Model_Tudu_Exception("Server connect error[{$errno}] with message: {$errstr}");
            }
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
            $this->_connection = null;
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