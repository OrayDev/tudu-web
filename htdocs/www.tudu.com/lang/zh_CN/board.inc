<?php
return array(
    // 界面文字
    'moderators' => '负责人',
    'zone_moderators' => '分区负责人',
    'join_members' => '参与人',
    'belong_zone' => '所属分区',
    'select_zone' => '--选择分区--',
    'board_name'  => '版块名称',
    'board_search' => '版块搜索',
    'add_attention' => '加入快捷',
    'remove_attention' => '移除快捷',
    'clear_board' => '清空版块',
    'delete_board' => '删除版块',
    'close_board' => '关闭版块',
    'open_board'  => '开放版块',
    'move_tudu' => '移动图度',
    'move_to' => '移动到',
    'collapse_zone' => '收起分区',
    'expand_zone' => '展开分区',

    'status_timeover' => '已超期',
    'status_needaccept' => '未接收',
    'status_waitforconfirm' => '需确认',

    'board_search' => '版块搜索',
    'board_home'   => '版块首页',

    'search_option' => '搜索选项',
    'tudu_type' => '主题类型',
    'search_time' => '搜索时间',
    'search_area' => '搜索范围',
    'sort_type' => '排序类型',
    'all_time' => '所有时间',
    'reply_time' => '更新时间',
    'end_time' => '截至时间',
    'between' => '之内',
    'sort_desc' => '降序排列',
    'sort_asc'  => '升序排列',
    're_search' => '重新搜索',

    'create_tudu' => '创建图度',
    'create_discuss' => '发起讨论',
    'create_notice' => '发布公告',
    'create_board' => '创建版块',

    'missing_search_condition' => '请输入搜索条件',

    // 参数
    'params_invalid_name'    => '请输入正确的版块名称',
    'params_invalid_parentid'=> '请选择版块所属的分区',
    'params_invalid_boardid' => '请选择进行操作的版块',
    'params_invalid_boardid' => '请选择目标版块',
    'params_invalid_groups'  => '请指定图度的执行人',
    'board_not_exists'       => '版块不存在或已被删除',
    'board_is_closed'        => '版块已被关闭',
    'parent_zone_not_exists' => '选择的所属分区不存在或已被删除',
    'moderators_user_not_exists'  => '指定版块负责人 “%s”不存在或已被删除',
    'groups_user_not_exists' => '版块参与人员 “%s”不存在或已被删除',

    // 权限
    'perm_deny_visit'  => '你没有该版块的访问权限',
    'perm_deny_delete' => '你没有删除该版块的权限',
    'perm_deny_clear'  => '你没有清空该版块的权限',
    'perm_deny_create' => '你没有创建版块的权限',
    'perm_deny_update' => '你没有更新该版块的权限',
    'perm_deny_close'  => '你没有打开或关闭该版块的权限',

    'tudu_to_user_not_exists' => '指定的图度执行用户不存在或已被删除',
    'privacy_tudu_subject' => '私密图度',

    // 操作
    'board_create_failure' => '版块创建失败',
    'board_create_success' => '版块创建成功',

    'board_update_failure' => '版块更新失败',
    'board_update_success' => '版块更新成功',

    'board_delete_failure' => '版块删除失败',
    'board_delete_success' => '版块删除成功',

    'board_close_failure' => '版块关闭失败',
    'board_close_success' => '版块关闭成功',
    'board_open_failure' => '版块开放失败',
    'board_open_success' => '版块开放成功',

    'board_clear_failure' => '版块清空失败',
    'board_clear_success' => '版块清空成功',

    'add_board_attention_success' => '添加快捷版块成功',
	'add_board_attention_failure' => '添加快捷版块失败',
	'remove_board_attention_failure' => '移除快捷版块失败',
    'remove_board_attention_success' => '移除快捷版块成功',

    'classname' => '分类名称',
    'rename' => '重命名',
    'confirm_delete_class' => '确认要删除该主题分类？',
    'input_new_name' => '请输入新的分类名称',
    'invalid_class_name' => '请输入正确的主题分类名称',
    'board_privacy' => '私密版块',
    'disable_edit' => '不允许修改',

    'add_subject_class' => '添加主题分类',
    'subject_class_tips' => '此主题类别只对此版块生效，不影响其他版块',
    'delete_not_null_board' => '不能删除非空版块，请先清空版块再进行删除',
    'delete_sys_board' => '不能删除初始的系统版块',
    'add_class' => '增加类别',
    'force_classify_tips' => '选择此项后，本版块下所有未选择分类的主题将不能成功发布',
    'force_classify' => '版块下所有主题必须归类',

	'set_tpl' => '模板设置',
	'tpl_tips' => '可在此处为该版块添加多个模板',
	'tpl_name' => '模板主题',
	'tpl_content' => '模板内容',
	'add_tpl' => '添加模板',

    'board_tudu_need_confirm_tips' => '勾选后，在本版块创建图度时，默认为完成任务后，需发起人确认',
    'perm_board_visit' => '该分区下还不存在你可以访问的版块',

	'board_classes_tips' => '可为本版块设置多个主题分类',
    'board_secrecy_tips' => '本版块下发送的图度均默认为私密图度',
    'board_disedit_tips' => '勾选后，本版块下的图度均不可进行二次修改',
    'board_tpl_tips' => '可为本版块设置多个模板',

    'manage_tudu_board' => '管理图度版块',
    'create_board_quick' => '创建版块快捷方式',
    'board_sum' => '版块汇总',
    'already_add_board_quick' => '已添加快捷',
    
    'invalid_boardid' => '缺少参数[bid]',
	'create_board_class_success' => '新建主题分类成功',
	'create_board_class_failure' => '新建主题分类失败',
        
    'flow_only' => '仅用于工作流',
    'flow_only_tips' => '勾选后，在该版块下只能发起具有工作流的图度任务',

    'attribute' => '属性'
);