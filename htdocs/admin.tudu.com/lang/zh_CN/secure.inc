<?php
return array(
    'admin_log' => '后台操作日志',
    'operator' => '操作人',
    'opera_time' => '操作时间',
    'operator_ip' => '来源IP',
    'operatioin_type' => '操作类型',
    'description' => '描述',

    'truename' => '用户名：',
    'account' => '帐号：',

    'logtype' => array(
        'create' => '创建',
        'update' => '更新',
        'delete' => '删除',
        'login'  => '登入',
        'logout' => '登出'
    ),

    'logmodule' => array(
        'dept' => '组织架构',
        'user' => '帐号',
        'login' => '登录',
        'role' => '权限',
        'group' => '群组',
        'secure' => '系统安全',
        'org' => '基本设置'
    ),

    'logdescription' => array(
        'user' => array(
            'create' => '创建用户 ID:"%s"',
            'update' => '更新用户 ID:"%s"',
            'delete' => '删除用户 ID:"%s"'
        ),

        'dept' => array(
            'create' => '创建部门 ID:"%s"',
            'update' => '更新部门 ID:"%s"',
            'delete' => '删除部门 ID:"%s"'
        ),

        'cast' => array(
            'create' => '创建架构配置 ID:"%s"',
            'update' => '更新架构配置 ID:"%s"',
            'delete' => '删除架构配置 ID:"%s"'
        ),

        'login' => array(
            'login' => '管理员登入 ID:"%s"',
            'logout' => '管理员登出 ID:"%s"'
        ),

        'secure' => array(
            'update' => array(
                'ip' => '修改IP过滤规则',
                'login' => '更改登录安全设置'
             )
        ),

        'org' => array(
            'update' => array(
                'skin' => '修改默认皮肤',
                'base' => '修改常规设置',
                'page' => '修改登录页'
            )
        )
    ),

    'newlogdescription' => array(
        'user' => array(
            'create' => '创建用户',
            'update' => '更新用户',
            'delete' => '删除用户'
        ),

        'dept' => array(
            'create' => '创建部门',
            'update' => '更新部门',
            'delete' => '删除部门',
            'update_user' => '更新部门成员',
            'update_moderator' => '更新部门负责人',
        ),

        'login' => array(
            'login' => '登入后台管理',
            'logout' => '登出后台管理'
        ),

        'role' => array(
           'create' => '创建权限组',
           'update' => '更新权限组',
           'delete' => '删除权限组',
           'update_member' => '更新权限组成员',
           'update_access' => '修改权限设置'
        ),

        'group' => array(
           'create' => '创建群组',
           'update' => '更新群组',
           'delete' => '删除群组',
           'update_member' => '更新群组成员'
        ),

        'secure' => array(
           'update' => '更新群组',
           'login' => '更改登录安全设置',
           'ip'    => '修改IP过滤规则',
        ),

        'org' => array(
            'update' => '更新基本信息',
            'skin' => '修改默认皮肤',
            'base' => '修改常规设置',
            'page' => '修改登录页'
        )
    ),

    'org_update_failure' => '设置失败',
    'org_update_success' => '设置成功',

    'password_safe' => '帐号登录',

    'templet' => '模板：',
    'sys_default' => '默认',
    'sys_58789e' => '湖蓝',
    'sys_c57592' => '玫红',
    'sys_99ac71' => '腾绿',
    'sys_bg01' => '春叶',
    'sys_bg02' => '余晖',
    'sys_bg03' => '晨曦',
    'sys_bg04' => '秋麦',
    'custom_color' => '自定义颜色',
    'custom_pic' => '自定义图片',
    'templet_color' => '模板颜色：',
    'templet_pic' => '模板图片：'
);