<?php
return array(
    // 介面文字
    'moderators' => '負責人',
    'zone_moderators' => '分區負責人',
    'join_members' => '參與人',
    'belong_zone' => '所屬分區',
    'select_zone' => '--選擇分區--',
    'board_name'  => '版塊名稱',
    'board_search' => '版塊搜索',
    'add_attention' => '加入快捷',
    'remove_attention' => '移除快捷',
    'clear_board' => '清空版塊',
    'delete_board' => '删除版塊',
    'close_board' => '关闭版塊',
    'open_board'  => '开放版塊',
    'move_tudu' => '移動圖度',
    'move_to' => '移動到',
    'collapse_zone' => '收起分區',
    'expand_zone' => '展開分區',

    'status_timeover' => '已超期',
    'status_needaccept' => '未接收',
    'status_waitforconfirm' => '需確認',

    'board_search' => '版塊搜索',
    'board_home'   => '版塊首頁',

    'search_option' => '搜索選項',
    'tudu_type' => '主題類型',
    'search_time' => '搜索時間',
    'search_area' => '搜索範圍',
    'sort_type' => '排序類型',
    'all_time' => '所有時間',
    'reply_time' => '更新時間',
    'end_time' => '截至時間',
    'between' => '之內',
    'sort_desc' => '降冪排列',
    'sort_asc'  => '昇冪排列',
    're_search' => '重新搜索',

    'create_tudu' => '創建圖度',
    'create_discuss' => '發起討論',
    'create_notice' => '發佈公告',
    'create_board' => '創建版塊',

    'missing_search_condition' => '請輸入搜索條件',

    // 參數
    'params_invalid_name'    => '請輸入正確的版塊名稱',
    'params_invalid_parentid'=> '請選擇版塊所屬的分區',
    'params_invalid_boardid' => '請選擇進行操作的版塊',
    'params_invalid_boardid' => '請選擇目標版塊',
    'params_invalid_groups'  => '請指定圖度的執行人',
    'board_not_exists'       => '版塊不存在或已被刪除',
    'parent_zone_not_exists' => '選擇的所屬分區不存在或已被刪除',
    'moderators_user_not_exists'  => '指定版塊負責人 "%s"不存在或已被刪除',
    'groups_user_not_exists' => '版塊參與人員 "%s"不存在或已被刪除',

    // 許可權
    'perm_deny_visit' => '你沒有該版塊的存取權限',
    'perm_deny_delete' => '你沒有刪除該版塊的許可權',
    'perm_deny_clear'  => '你沒有清空該版塊的許可權',
    'perm_deny_create' => '你沒有創建版塊的許可權',
    'perm_deny_update' => '你沒有更新該版塊的許可權',
    'perm_deny_close'  => '你沒有打開或關閉該版塊的許可權',

    'tudu_to_user_not_exists' => '指定的圖度執行用戶不存在或已被刪除',
    'privacy_tudu_subject' => '私密圖度',

    // 操作
    'board_create_failure' => '版塊創建失敗',
    'board_create_success' => '版塊創建成功',

    'board_update_failure' => '版塊更新失敗',
    'board_update_success' => '版塊更新成功',

    'board_delete_failure' => '版塊刪除失敗',
    'board_delete_success' => '版塊刪除成功',

    'board_close_failure' => '版塊關閉失敗',
    'board_close_success' => '版塊關閉成功',
    'board_open_failure' => '版塊開放失敗',
    'board_open_success' => '版塊開放成功',

    'board_clear_failure' => '版塊清空失敗',
    'board_clear_success' => '版塊清空成功',

    'add_board_attention_success' => '添加快捷版塊成功',
	'add_board_attention_failure' => '添加快捷版塊失敗',
	'remove_board_attention_failure' => '移除快捷版塊失敗',
    'remove_board_attention_success' => '移除快捷版塊成功',

    'subject_class' => '主題分類',
    'classname' => '分類名稱',
    'rename' => '重命名',
    'confirm_delete_class' => '確認要刪除該主題分類？',
    'input_new_name' => '請輸入新的分類名稱',
    'invalid_class_name' => '請輸入正確的主題分類名稱',
    'board_privacy' => '私密版塊',
    'disable_edit' => '不允許修改',

    'add_subject_class' => '添加主題分類',
    'subject_class_tips' => '此主題類別只對此版塊生效，不影響其他版塊',
    'delete_not_null_board' => '不能刪除非空版塊，請先清空版塊再進行刪除',
    'delete_sys_board' => '不能刪除系統初始的版塊',
    'add_class' => '增加類別',
    'force_classify_tips' => '選擇此項後，本版塊下所有未選擇分類的主題將不能成功發佈',
    'force_classify' => '版塊下所有主題必須歸類',

	'set_tpl' => '範本設置',
	'tpl_tips' => '可在此處為該版塊添加多個範本',
	'tpl_name' => '範本主題',
	'tpl_content' => '範本內容',
	'add_tpl' => '添加範本',

    'board_tudu_need_confirm_tips' => '勾選後，在本版塊創建圖度時，默認為完成任務後，需發起人確認',
    'perm_board_visit' => '該分區下還不存在你可以訪問的版塊',

	'board_classes_tips' => '可為本版塊設置多個主題分類',
    'board_secrecy_tips' => '本版塊下發送的圖度均默認為私密圖度',
    'board_disedit_tips' => '勾選後，本版塊下的圖度均不可進行二次修改',
    'board_tpl_tips' => '可為本版塊設置多個範本',

	'manage_tudu_board' => '管理圖度版塊',
    'create_board_quick' => '創建版塊快捷方式',
    'board_sum' => '版塊匯總',
    'already_add_board_quick' => '已添加快捷',

	'invalid_boardid' => '缺少參數[bid]',
	'create_board_class_success' => '新建主題分類成功',
	'create_board_class_failure' => '新建主題分類失敗',

    'flow_only' => '僅用於工作流',
    'flow_only_tips' => '勾選後，在該版塊下只能發起具有工作流的圖度任務',

    'attribute' => '屬性'
);