<?php
return array(
    // 界面文字
    'moderators' => 'Moderator', //负责人
    'zone_moderators' => 'Moderator',
    'join_members' => 'Members', //参与人
    'belong_zone' => 'Zone', //所属分区
    'select_zone' => '--Select zone--', //选择分区
    'board_name'  => 'Name', //板块名称
    'board_search' => 'Board search', //板块搜索
    'add_attention' => 'Add to quicks', //'加入快捷',
    'remove_attention' => 'Remove from quick board', //'移除快捷',
    'clear_board' => 'Clear Board',
    'delete_board' => 'Delete Board',
    'close_board' => 'Close Board',
    'open_board' => 'Open Board',
    'move_tudu' => 'Move Tudu',
    'move_to' => 'Move To',
    'collapse_zone' => 'Collapse Zones',
    'expand_zone' => 'Expand Zones',

    'status_timeover' => 'Expired', //已接收
    'status_needaccept' => 'Not accepted', //未接收
    'status_waitforconfirm' => 'Need confirm', //需确认

    'board_search' => 'Board search', //板块搜索
    'board_home'   => 'Board homepage', //板块首页

    'search_option' => 'Search option', //搜索选项
    'tudu_type' => 'Tudu type', //主题类型
    'search_time' => 'Search time', //搜索时间
    'search_area' => 'Search in', //搜索范围
    'sort_type' => 'Sort type', //排序类型
    'all_time' => 'All time', // 所有时间
    'reply_time' => 'Reply time', //回复时间
    'end_time' => 'Deadline',
    'between' => 'Between', //之内
    'sort_desc' => 'Descending', //降序排列
    'sort_asc'  => 'Ascending', //升序排列
    're_search' => 'Search again', //重新搜索

    'create_tudu' => 'New tudu', //发布图度
    'create_discuss' => 'New discuss', //发布讨论
    'create_notice' => 'New notice', //发布公告
    'create_board' => 'New board', //创建板块

    'missing_search_condition' => 'Search condition missing', //请输入搜索条件

    // 参数
    'params_invalid_name'    => 'Board name is not valid', //请输入正确的板块名称
    'params_invalid_parentid'=> 'Please select a zone of the selected board', //请选择板块所属的分区
    'params_invalid_boardid' => 'Please select a board', //请选择进行操作的板块
    'params_invalid_boardid' => 'Please select destination board', //请选择目标板块
    'params_invalid_groups'  => 'Please specify a valid tudu recipient', //请指定图度的执行人
    'board_not_exists'       => 'Board not exist or has been removed', //板块不存在或已被删除
    'parent_zone_not_exists' => 'Zone not exist or has been removed', //选择的所属分区不存在或已被删除
    'moderators_user_not_exists'  => 'Board moderator %s not exit', //指定板块负责人 “%s”不存在或已被删除
    'groups_user_not_exists' => 'Board member %s not exit', //板块参与人员 “%s”不存在或已被删除

    // 权限
    'perm_deny_visit'  => 'You have no permission to visit this board', //你没有该板块的访问权限
    'perm_deny_delete' => 'You have no permission to delete this board',//'你没有删除该版块的权限',
    'perm_deny_clear'  => 'You have no permission to clear this board',//'你没有清空该版块的权限',
    'perm_deny_create' => 'You have no permission to create board',//'你没有创建版块的权限',
    'perm_deny_update' => 'You have no permission to update this board',
    'perm_deny_close'  => 'You have no permission to close this board',//'你没有打开或关闭该版块的权限',

    'tudu_to_user_not_exists' => 'Tudu user not exit', //指定的图度执行用户不存在或已被删除
    'privacy_tudu_subject' => 'Privacy tudu', //私密图度

    // 操作
    'board_create_failure' => 'Board create failed', //板块创建失败
    'board_create_success' => 'Board created', //板块创建成功

    'board_update_failure' => 'Board update failed', //板块更新失败
    'board_update_success' => 'Board updated', //板块更新成功

    'board_delete_failure' => 'Board delete failed', //'版块删除失败',
    'board_delete_success' => 'Board deleted',

    'board_close_failure' => 'Board close failed',
    'board_close_success' => 'Board closed',
    'board_open_failure' => 'Board open failed',
    'board_open_success' => 'Board opened',

    'board_clear_failure' => 'Board clear failed',
    'board_clear_success' => 'Board clear success',

    'add_board_attention_success' => 'Add quick board success',//'添加快捷板块成功',
    'add_board_attention_failure' => 'Add quick board failed', //添加快捷版块失败
	'remove_board_attention_failure' => 'Remove quick board failed',//移除快捷版块失败
    'remove_board_attention_success' => 'Remove quick board success',//'移除快捷板块成功',

    'subject_class' => 'Subject Class',//'主题分类',
    'classname' => 'Class name',
    'confirm_delete_class' => 'Confirm delete this class?',
    'rename' => 'Rename',
    'input_new_name' => 'Please input the new name of class',
    'invalid_class_name' => 'Invalid class name',
    'board_privacy' => 'Privacy', //'私密板块',
    'disable_edit' => 'Disable Edit',

    'add_subject_class' => 'Add Subject Classes',//'添加主题分类',
    'subject_class_tips' => 'Classes above Only effect to current board',//'此主题类别只对此版块生效，不影响其他版块',
    'delete_not_null_board' => 'Please delete all tudu of this board before delete it',
    'delete_sys_board' => 'Could not delete system board',
    'add_class' => 'Add Class', //'增加类别',
    'force_classify_tips' => 'If mark this check, Tudus of current board must has a class when it be created', //'选择此项后，本版块下所有未选择分类的主题将不能成功发布',
    'force_classify' => 'Force to select class', //'版块下所有主题必须归类'

	'set_tpl' => 'Set template', //模板设置
	'tpl_tips' => 'You can append templates in this board', //可在此处为该版块添加多个模板
	'tpl_name' => 'Template name', //模板主题
	'tpl_content' => 'Template content', //模板内容
	'add_tpl' => 'Add template', //添加模板

    'board_tudu_need_confirm_tips' => '', //勾选后，在本版块创建图度时，默认为完成任务后，需发起人确认
    'perm_board_visit' => 'You can not visit other board in this zone', //该分区下还不存在你可以访问的版块

	'board_classes_tips' => 'Add subject classes in this board',//可为本版块设置多个主题分类
    'board_secrecy_tips' => '',//本版块下发送的图度均默认为私密图度
    'board_disedit_tips' => '',//勾选后，本版块下的图度均不可进行二次修改
    'board_tpl_tips' => 'Add templates in this board',//可为本版块设置多个模板

	'manage_tudu_board' => 'Board manage', //管理图度版块
    'create_board_quick' => 'Create quick for board', //创建版块快捷方式
    'board_sum' => 'Counted board',//版块汇总
    'already_add_board_quick' => 'Already quicked',//已添加快捷

	'invalid_boardid' => 'invalid[bid]',//缺少参数[bid]
	'create_board_class_success' => 'Create classes success',//新建主题分类成功
	'create_board_class_failure' => 'Create classes failed',//新建主题分类失败

    'flow_only' => 'Flow only',//仅用于工作流
    'flow_only_tips' => '',//勾选后，在该版块下只能发起具有工作流的图度任务

    'attribute' => 'Attribute'//属性
);