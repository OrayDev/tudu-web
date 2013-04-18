<?php
/**
 * Tudu OpenApi
 *
 * LICENSE
 *
 *
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class FlowController extends TuduX_Controller_OpenApi
{

    public function preDispatch()
    {
        // 用户未登录
        if (!$this->_user->isLogined()) {
            throw new TuduX_OpenApi_Exception("Access protected API without a valided access token", TuduX_OpenApi_ResponseCode::MISSING_AUTHORIZE, 403);
        }
    }

    /**
     * 获取图度工作流列表
     */
    public function listAction()
    {
        $boardId = $this->_request->getQuery('boardid');

        /* @var $daoFlow Dao_Td_Board_Board */
        $daoFlow = Tudu_Dao_Manager::getDao('Dao_Td_Flow_Flow', Tudu_Dao_Manager::DB_TS);

        $conditions = array(
            'orgid' => $this->_user->orgId,
        );

        if (!empty($boardId)) {
            $conditions['boardid'] = $boardId;
        }

        $flows = $daoFlow->getFlows(array('orgid' => $this->_user->orgId, 'uniqueid' => $this->_user->uniqueId))->toArray();

        $ret = array();
        foreach ($flows as $item) {
            if (!$item['avaliable']) {
                continue ;
            }

            $ret[] = array(
                'orgid'      => $item['orgid'],
                'boardid'    => $item['boardid'],
                'flowid'     => $item['flowid'],
                'subject'    => $item['subject'],
                'createtime' => $item['createtime']
            );
        }

        $this->view->code   = TuduX_OpenApi_ResponseCode::SUCCESS;
        $this->view->flows  = $ret;
    }
}