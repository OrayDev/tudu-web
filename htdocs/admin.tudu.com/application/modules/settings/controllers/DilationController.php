<?php
/**
 * 容量扩展
 *
 * @copyright  Copyright (c) 2009-2009 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: DilationController.php 2365 2012-11-09 05:59:17Z chenyongfa $
 */
class Settings_DilationController extends TuduX_Controller_Admin
{

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
     * 扩容页面
     */
    public function indexAction()
    {
        /* @var $daoReal Dao_Md_Org_Real */
        $daoInfo = $this->getDao('Dao_Md_Org_Info');
        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        /* @var $daoMobile Dao_Md_User_Mobile */
        $daoMobile = $this->getDao('Dao_Md_User_Mobile');

        // 判断实名认证是否已经通过
        $info  = $daoInfo->getOrgInfo(array('orgid' => $this->_orgId));
        if ($info !== null && $info->realNameStatus == 2) {
            $count = $this->countTudu();
            // 绑定手机号码
            $bind = $daoMobile->getBind(array(
                'orgid'  => $this->_orgId,
                'userid' => $this->_user->userId
            ));
            $weiboQuota = $daoOrg->getQuota(array('orgid' => $this->_orgId, 'method' => 0));
            $wbStatus = (boolean) null !== $weiboQuota && (int) $weiboQuota['status'] == 1;
            $this->view->count  = $count;
            $this->view->bind   = $bind;
            $this->view->weiboquota = $wbStatus;
        }

        $this->view->status = $info !== null ? $info->realNameStatus : 0;
    }

    /**
     * 验证微博
     */
    public function weiboQuotaAction()
    {
        $nickname = trim($this->_request->getPost('nickname'));
        if (!$nickname) {
            return $this->json(false, '请输入您的微博昵称！');
        }

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');

        // 本步骤是否已经扩容了
        $quoteMethod = $daoOrg->getQuota(array('orgid' => $this->_orgId, 'method' => 0));
        if (null !== $quoteMethod && (int) $quoteMethod['status'] == 1) {
            return $this->json(false, '对不起，您已经使用本方法扩容过了！');
        }

        // 取得原来空间大小
        $org = $daoOrg->getOrgById($this->_orgId);
        $params = array(
            'maxquota' => $org->maxQuota + 2000
        );

        $ret = $daoOrg->updateOrg($this->_orgId, $params);
        if (!$ret) {
            return $this->json(false, '更新图度空间容量失败，请联系客服！');
        }

        // 添加记录
        $daoOrg->createQuota(array(
            'orgid'      => $this->_orgId,
            'uniqueid'   => $this->_user->uniqueId,
            'method'     => 0,
            'nickname'   => $nickname,
            'status'     => 1,
            'createtime' => time()
        ));

        return $this->json(true, '恭喜您，微博分享后，即可获得2G的空间容量！');
    }

    /**
     * 发送手机验证码
     */
    public function sendCodeAction()
    {
        $mobile = trim($this->_request->getPost('mobile'));

        if (empty($mobile) || !Oray_Function::isMobile($mobile)) {
            return $this->json(false, '手机号码不正确');
        }

        if ($this->_session->lastSmsTime && time() - $this->_session->lastSmsTime < 55) {
            return $this->json(false, '短信发送过于频繁，请稍后再试');
        }

        /* @var $daoMobile Dao_Md_User_Mobile */
        $daoMobile = $this->getDao('Dao_Md_User_Mobile');

        $bind = $daoMobile->getBind(array(
            'orgid'  => $this->_orgId,
            'userid' => $this->_user->userId
        ));

        if (null !== $bind && strcasecmp($mobile, $bind['mobile']) === 0) {
            $this->json(false, '新手机不能跟旧的绑定手机相同');
        }

        // 该手机号码是否已经绑定过了
        if ($daoMobile->existBind($mobile)) {
            return $this->json(false, '手机已跟其它用户绑定，请先更改手机');
        }

        // 获取验证码
        $code = $daoMobile->getCode();
        // 创建验证记录
        $ret = $daoMobile->createCode(array(
            'orgid'  => $this->_orgId,
            'userid' => $this->_user->userId,
            'mobile' => $mobile,
            'code'   => $code,
            'status' => 0
        ), 600);

        if (!$ret) {
            return $this->json(false, '验证码生成失败，请稍候重试');
        }

        $config = $this->_options['sms'];
        $sms    = new Oray_Sms($config);
        $content= "您的手机验证码是：{$code}，验证码有效时长为10分钟，10分钟后请重新点击发送验证码进行验证。【tudu】";

        $ret = $sms->send($mobile, $content, 0);
        if (!$ret) {
            return $this->json(false, '验证码发送失败');
        }

        $this->_session->lastSmsTime = time();

        return $this->json(true, '验证码发送成功，请留意短信');
    }

    /**
     * 绑定手机
     */
    public function bindMobileAction()
    {
        $mobile = trim($this->_request->getPost('mobile'));
        $code   = trim($this->_request->getPost('seccode'));
        $success= array();

        if (empty($mobile) || !Oray_Function::isMobile($mobile)) {
            return $this->json(false, '手机号码不正确');
        }

        if (empty($code)) {
            return $this->json(false, '验证码不能为空');
        }

        /* @var $daoMobile Dao_Md_User_Mobile */
        $daoMobile = $this->getDao('Dao_Md_User_Mobile');

        $bind = $daoMobile->getBind(array(
            'orgid'  => $this->_orgId,
            'userid' => $this->_user->userId
        ));

        if (null !== $bind && strcasecmp($mobile, $bind['mobile']) === 0) {
            $this->json(false, '新手机不能跟旧的绑定手机相同');
        }

        // 该手机号码是否已经绑定过了
        if ($daoMobile->existBind($mobile)) {
            return $this->json(false, '手机已跟其它用户绑定，请先更改手机');
        }

        if (!$daoMobile->checkCode($this->_orgId, $this->_user->userId, $mobile, $code)) {
            return $this->json(false, '验证码不正确或验证码已过期');
        }

        // 验证码为已用的
        $daoMobile->updateCode($this->_orgId, $this->_user->userId, $mobile, Dao_Md_User_Mobile::STATUS_USED);
        // 删除绑定
        $daoMobile->deleteBind($this->_orgId, $this->_user->userId);

        //添加用户绑定手机
        $ret = $daoMobile->createBind(array(
            'orgid'  => $this->_orgId,
            'userid' => $this->_user->userId,
            'mobile' => $mobile
        ));

        if (!$ret) {
            return $this->json(false, '绑定手机失败，请重新');
        }
        $success['bind'] = true;

        /* @var $daoOrg Dao_Md_Org_Org */
        $daoOrg = $this->getDao('Dao_Md_Org_Org');
        // 本步骤是否已经扩容了
        $quoteMethod = $daoOrg->getQuota(array('orgid' => $this->_orgId, 'method' => 1));
        if (null === $quoteMethod || (null !== $quoteMethod && (int) $quoteMethod['status'] != 1)) {
            // 取得原来空间大小
            $org = $daoOrg->getOrgById($this->_orgId);
            $params = array(
                'maxquota' => $org->maxQuota + 2000
            );

            $ret = $daoOrg->updateOrg($this->_orgId, $params);
            if (!$ret) {
                return $this->json(false, '更新图度空间容量失败，请联系客服！');
            }

            // 添加记录
            $daoOrg->createQuota(array(
                'orgid'      => $this->_orgId,
                'uniqueid'   => $this->_user->uniqueId,
                'method'     => 1,
                'status'     => 1,
                'createtime' => time()
            ));
            $success['quota'] = true;
        }
        return $this->json(true, '绑定手机成功', $success);
    }

    /**
     * 验证图度数增长量
     */
    public function validTuduAction()
    {
        $count = $this->countTudu();

        if ($count >= 888) {
            /* @var $daoOrg Dao_Md_Org_Org */
            $daoOrg = $this->getDao('Dao_Md_Org_Org');

            // 本步骤是否已经扩容了
            $quoteMethod = $daoOrg->getQuota(array('orgid' => $this->_orgId, 'method' => 2));
            if (null !== $quoteMethod && (int) $quoteMethod['status'] == 1) {
                return $this->json(false, '对不起，您已经使用本方法扩容过了！');
            }

            // 取得原来空间大小
            $org = $daoOrg->getOrgById($this->_orgId);
            $params = array(
                'maxquota' => $org->maxQuota + 4000
            );

            $ret = $daoOrg->updateOrg($this->_orgId, $params);
            if (!$ret) {
                return $this->json(false, '更新图度空间容量失败，请联系客服！');
            }

            // 添加记录
            $daoOrg->createQuota(array(
                'orgid'      => $this->_orgId,
                'uniqueid'   => $this->_user->uniqueId,
                'method'     => 2,
                'status'     => 1,
                'createtime' => time()
            ));
            return $this->json(true);
        }

        return $this->json(false, '对不起，图度数量没有达到888！');
    }

    /**
     * 统计图度数量
     */
    public function countTudu()
    {
        $ts = $this->getDb('ts' . $this->_user->tsid);

        $sql = "SELECT COUNT(0) FROM td_tudu WHERE org_id = '{$this->_orgId}' AND board_id != '^system'";

        try {
            return (int) $ts->fetchOne($sql);
        } catch (Zend_Db_Exception $e) {
            $this->_catchException($e, __METHOD__);
            return 0;
        }
    }
}