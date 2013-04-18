<?php

/**
 * 开放API返回代码集合
 *
 * 返回代码规则
 * 四五位整数，万位数字表示错误级别
 * 1.系统级别，站点、服务本身或资源连接或系统业务部分原因产生的错误
 * 2.模块级别，模块业务流程原因产生的错误
 * 百位和千位为模块标识
 * 0: 系统内部
 * 1: 系统授权
 *
 * @author CuTe_CuBe
 *
 */
class TuduX_OpenApi_ResponseCode
{
    // 操作成功
    const SUCCESS      = 0;

    // 系统错误，默认
    const SYSTEM_ERROR = 10000;
    // 无效的请求
    const BAD_REQUEST  = 10001;

    // 验证授权失败
    const AUTHORIZE_FAILED  = 10100;
    // 验证授权已过期
    const AUTHORIZE_EXPIRED = 10101;
    // 无效的访问授权
    const INVALID_AUTHORIZE = 10102;
    // 访问需要授权的资源，但没有提供合法的访问令牌
    const MISSING_AUTHORIZE = 10103;
    // 用户帐号被停用
    const INVALID_USER      = 10104;
    // 用户不存在
    const USER_NOT_EXISTS   = 10105;
    // 被锁定
    const USER_IN_LOCKED    = 10106;

    // 访问的资源不存在
    const RESOURCE_NOT_EXISTS = 20001;
    // 没有权限
    const ACCESS_DENIED       = 20002;
    // 缺少必须参数
    const MISSING_PARAMETER   = 20003;

    // 程序操作失败
    const OPERATE_FAILED      = 20101;
    // 讨论已是关闭状态\图度已是确认状态
    const TUDU_CLOSED         = 20102;
    // 提交删除回复不能是图度内容
    const CONTENT_POST_FIRST  = 20103;

    // 图度保存失败
    const TUDU_SAVE_FAILED    = 20131;
    // 图度发送失败
    const TUDU_SEND_FAILED    = 20132;
    // 回复保存失败
    const POST_SAVE_FAILED    = 20133;
    // 回复发送失败
    const POST_SEND_FAILED    = 20134;

    // 删除失败
    const TUDU_DELETE_FAILED  = 20140;
    // 尝试删除图度组
    const TUDU_DELETE_GROUP   = 20141;
    // 标签操作失败
    const TUDU_LABEL_FAILED   = 20201;

    // 发送建议失败
    const SUGGEST_SEND_FAILED = 20501;
}