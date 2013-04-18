CREATE TABLE `td_board` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '版块ID',
  `type` enum('zone','board','system') NOT NULL DEFAULT 'zone' COMMENT '版块类型',
  `owner_id` varchar(60) NOT NULL DEFAULT '' COMMENT '版块所有者ID',
  `parent_board_id` varchar(36) DEFAULT NULL COMMENT '上级版块ID',
  `board_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '版块名称',
  `memo` text CHARACTER SET utf8 COMMENT '版块说明',
  `moderators` text CHARACTER SET utf8 COMMENT '版主',
  `groups` text COMMENT '允许用户组,email格式为用户,非email格式为群组ID',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '版块状态: 0 - 公开版块, 1 - 隐藏版块, 2 - 关闭板块',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `protect` tinyint(1) NOT NULL DEFAULT '0',
  `is_classify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '必须选择分类',
  `flow_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否只允许使用工作流',
  `need_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '图度是否需要确认',
  `last_post` varchar(250) CHARACTER SET utf8 DEFAULT NULL COMMENT '最后回复信息',
  `tudu_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总图度数',
  `post_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总回复数',
  `today_tudu_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '今天图度数',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序数值',
  `temp_is_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据过渡是否完成',
  `last_update_time` int(10) unsigned DEFAULT NULL COMMENT '版块最后更新时间',
  PRIMARY KEY (`org_id`,`board_id`),
  KEY `idx_order` (`type`,`order_num`),
  KEY `fk_parent_board` (`org_id`,`parent_board_id`),
  KEY `idx_temp_is_done` (`temp_is_done`),
  CONSTRAINT `fk_parent_board` FOREIGN KEY (`org_id`, `parent_board_id`) REFERENCES `td_board` (`org_id`, `board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度版块';

INSERT INTO td_board (org_id, board_id, type, owner_id, board_name, moderators, groups, privacy, order_num) VALUES ('testorg', '^zone', 'zone', 'admin', '默认分区', 'admin 管理员', '^all', 0, 1);
INSERT INTO td_board (org_id, board_id, type, owner_id, board_name, moderators, groups, privacy, parent_board_id, order_num) VALUES ('testorg', '^board', 'board', 'admin', '默认版块', 'admin 管理员', '^all', 1, '^zone', 1);


CREATE TABLE `td_board_user` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '板块ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `order_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`org_id`,`board_id`,`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户快捷板块表';


CREATE TABLE `td_board_favor` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '版块ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重（发送频率），手工调整 99999 - 98999，系统添加0 - 10000',
  `last_update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`unique_id`,`board_id`,`org_id`),
  KEY `idx_weight` (`weight`),
  KEY `idx_org_id_board_id` (`org_id`,`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户常用版块记录表';


CREATE TABLE `td_board_sort` (
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `sort` text NOT NULL COMMENT '板块排序',
  PRIMARY KEY (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户板块排序表';


CREATE TABLE `td_class` (
  `org_id` varchar(60) NOT NULL DEFAULT '',
  `class_id` varchar(36) NOT NULL DEFAULT '',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '板块ID',
  `class_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `order_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`org_id`,`class_id`),
  KEY `idx_board` (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='分类';


CREATE TABLE `td_label` (
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `label_id` varchar(36) NOT NULL DEFAULT '' COMMENT '标签ID',
  `label_alias` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '标签别名',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统标签',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示，0.隐藏，1.显示，2.自动',
  `color` char(7) DEFAULT NULL COMMENT '字体颜色',
  `bgcolor` char(7) DEFAULT NULL COMMENT '背景颜色',
  `display` tinyint(1) DEFAULT '1' COMMENT '示显范围 1.仅在Web中显示 2. 仅在API中输出',
  `total_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总图度数',
  `unread_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '未读图度数',
  `sync_time` int(10) unsigned DEFAULT NULL COMMENT '同布时间',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`unique_id`,`label_id`),
  UNIQUE KEY `idx_lable_alias` (`unique_id`,`label_alias`),
  KEY `idx_order_num` (`order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户标签';


CREATE TABLE `td_contact` (
  `contact_id` varchar(36) NOT NULL DEFAULT '',
  `unique_id` varchar(36) NOT NULL DEFAULT '',
  `from_user` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否来自用户',
  `true_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '姓名',
  `pinyin` varchar(255) DEFAULT NULL COMMENT '拼音音序',
  `email` varchar(255) DEFAULT NULL COMMENT 'email地址',
  `mobile` varchar(20) DEFAULT NULL COMMENT '手机号码',
  `properties` text CHARACTER SET utf8 COMMENT '用户其他属性集合,json格式',
  `memo` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '备注',
  `avatars` blob COMMENT '头像',
  `avatars_type` varchar(20) DEFAULT NULL COMMENT '头像MIME类型',
  `affinity` int(11) NOT NULL DEFAULT '0' COMMENT '亲密度',
  `last_contact_time` int(10) DEFAULT NULL COMMENT '最后联系时间',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `groups` text NOT NULL,
  `temp_is_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据过渡是否完成',
  PRIMARY KEY (`contact_id`,`unique_id`),
  KEY `idx_email` (`email`),
  KEY `idx_true_name` (`true_name`),
  KEY `idx_last_contact_time` (`last_contact_time`),
  KEY `idx_is_show` (`is_show`),
  KEY `idx_true_name_email` (`true_name`,`email`),
  KEY `idx_unique_id_show` (`unique_id`,`is_show`),
  KEY `idx_temp_is_done` (`temp_is_done`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_contact_group` (
  `group_id` varchar(36) NOT NULL DEFAULT '' COMMENT '群组ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所属用户唯一ID',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统群组',
  `name` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '群组名称',
  `bgcolor` varchar(10) DEFAULT NULL COMMENT '背景颜色',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序ID',
  PRIMARY KEY (`unique_id`,`group_id`),
  KEY `idx_is_system` (`is_system`),
  KEY `idx_order_num` (`order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='联系人群组';


CREATE TABLE `td_contact_group_member` (
  `contact_id` varchar(36) NOT NULL DEFAULT '' COMMENT '联系人ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `group_id` varchar(36) NOT NULL DEFAULT '' COMMENT '群组ID',
  PRIMARY KEY (`contact_id`,`group_id`,`unique_id`),
  KEY `fk_member_of_which_group` (`unique_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_tudu_cycle` (
  `cycle_id` varchar(36) NOT NULL DEFAULT '' COMMENT '周期ID',
  `mode` enum('day','week','month','year') NOT NULL DEFAULT 'day' COMMENT '定期模式',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '定期类型',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '天',
  `week` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '周',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '月',
  `weeks` varchar(14) NOT NULL DEFAULT '' COMMENT '多个周',
  `at` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '第几个， 0 为最后一个。与what组合',
  `what` enum('day','workday','weekend','sun','mon','tue','wed','thu','fri','sat') DEFAULT NULL COMMENT '什么日子，与at组合，代表如第二个星期三。',
  `period` int(11) NOT NULL DEFAULT '0' COMMENT '任务周期，单位天',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '已执行次数统计',
  `display_date` tinyint(1) NOT NULL DEFAULT '0' COMMENT '标题是否显示开始日期',
  `is_keep_attach` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否继承附件',
  `end_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '结束类型，0-无结束日期，1-重复次数，2-结束日期',
  `end_count` int(11) NOT NULL DEFAULT '0' COMMENT '结束的次数',
  `end_date` int(11) DEFAULT NULL COMMENT '结束日期',
  `is_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(删除周期任务时会标记为无效)',
  PRIMARY KEY (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='任务周期';


CREATE TABLE `td_tudu` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '版块ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `class_id` varchar(36) DEFAULT NULL COMMENT '主题分类ID',
  `cycle_id` varchar(36) DEFAULT NULL COMMENT '周期ID',
  `prev_tudu_id` varchar(36) DEFAULT NULL COMMENT '前置任务ID',
  `app_id` varchar(128) NOT NULL DEFAULT '^system' COMMENT '发送程序ID',
  `flow_id` varchar(36) DEFAULT NULL COMMENT '工作流ID',
  `step_id` varchar(36) DEFAULT NULL COMMENT '当前执行步骤ID',
  `type` enum('task','discuss','notice','meeting') NOT NULL DEFAULT 'task' COMMENT '图度类型',
  `subject` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '主题',
  `from` varchar(250) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '发图人',
  `to` text CHARACTER SET utf8 COMMENT '收图人',
  `cc` text CHARACTER SET utf8 COMMENT '抄送',
  `bcc` text CHARACTER SET utf8 COMMENT '密送',
  `priority` tinyint(1) NOT NULL DEFAULT '0' COMMENT '优先级',
  `privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '隐私',
  `is_draft` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否草稿',
  `is_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '图度完结 - 不允许再回复及修改',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除 - 未使用',
  `is_top` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `need_confirm` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要确认',
  `accep_mode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '执行方式：0.正常，1.认领',
  `last_post_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `last_poster` varchar(15) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '最后回复者（姓名）',
  `last_forward` varchar(255) DEFAULT NULL COMMENT '最后转发信息',
  `view_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `reply_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `log_num` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '日志数',
  `cycle_num` int(11) unsigned DEFAULT NULL COMMENT '周期任务循环序号',
  `step_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '步骤数量',
  `start_time` int(10) unsigned DEFAULT NULL COMMENT '开始时间',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '结束时间',
  `complete_time` int(10) unsigned DEFAULT NULL COMMENT '任务完成时间',
  `total_time` int(10) unsigned DEFAULT NULL COMMENT '预计总耗时',
  `elapsed_time` int(10) unsigned DEFAULT NULL COMMENT '已耗时',
  `accept_time` int(10) unsigned DEFAULT NULL COMMENT '接受时间',
  `percent` tinyint(3) unsigned DEFAULT NULL COMMENT '完成百分比',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态： 0-未开始，1-进行中，2-已完成，3-已拒绝， 4-已取消',
  `special` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1-循环任务 2-投票 4-图度组',
  `notify_all` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒所有参与人',
  `score` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '分数',
  `password` varchar(16) DEFAULT NULL COMMENT '访问密码',
  `is_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要验证码',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `tudu_index_num` int(10) unsigned DEFAULT NULL COMMENT '图度索引ID',
  `tudu_index_num2` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '全文索引所需的索引ID',
  PRIMARY KEY (`tudu_id`),
  UNIQUE KEY `tudu_index_num2` (`tudu_index_num2`),
  UNIQUE KEY `idx_tudu_index_num` (`tudu_index_num`),
  KEY `idx_org_board` (`org_id`,`board_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_last_post_time` (`last_post_time`),
  KEY `idx_from` (`from`),
  KEY `idx_subject` (`subject`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_to` (`to`(250)),
  KEY `idx_type_status_done` (`type`,`status`,`is_done`),
  KEY `idx_is_top` (`is_top`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_cycle_id` (`cycle_id`),
  KEY `idx_complete_time` (`complete_time`),
  KEY `idx_flow_id` (`flow_id`),
  CONSTRAINT `fk_tudu_of_which_board` FOREIGN KEY (`org_id`, `board_id`) REFERENCES `td_board` (`org_id`, `board_id`),
  CONSTRAINT `fk_use_which_cycle` FOREIGN KEY (`cycle_id`) REFERENCES `td_tudu_cycle` (`cycle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度主题表';


CREATE TABLE `td_tudu_group` (
  `tudu_id` varchar(36) NOT NULL DEFAULT '',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '创建用户唯一ID',
  `parent_tudu_id` varchar(36) DEFAULT NULL COMMENT '父tuduID',
  `root_tudu_id` varchar(36) DEFAULT NULL COMMENT '根图度ID',
  `type` enum('root','node','leaf') NOT NULL DEFAULT 'leaf' COMMENT '节点类型',
  PRIMARY KEY (`tudu_id`),
  KEY `idx_parent_tudu_id` (`parent_tudu_id`),
  CONSTRAINT `group_of _which_tudu` FOREIGN KEY (`tudu_id`) REFERENCES `td_tudu` (`tudu_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度组关系表';


CREATE TABLE `td_tudu_meeting` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `notify_time` int(11) DEFAULT NULL COMMENT '提醒时间（提前N分钟提醒）',
  `notify_type` int(11) NOT NULL DEFAULT '0',
  `location` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '会议地点',
  `is_allday` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否全天',
  `is_notified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已执行提醒',
  PRIMARY KEY (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='会议信息表';


CREATE TABLE `td_vote` (
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `vote_id` varchar(36) NOT NULL DEFAULT '' COMMENT '投票ID',
  `title` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '投票标题',
  `max_choices` tinyint(3) NOT NULL DEFAULT '0',
  `vote_count` int(11) NOT NULL DEFAULT '0' COMMENT '投票累积次数',
  `privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '私密（不记名）',
  `visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT '结果是否可见',
  `is_reset` tinyint(1) NOT NULL DEFAULT '0' COMMENT '更新时是否重置(清0)',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序ID',
  `expire_time` int(11) DEFAULT NULL,
  `anonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否匿名设置（发起人可见投票参与人）',
  PRIMARY KEY (`tudu_id`,`vote_id`),
  KEY `idx_tudu_id` (`tudu_id`),
  CONSTRAINT `fk_vote_of_which_tudu` FOREIGN KEY (`tudu_id`) REFERENCES `td_tudu` (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_vote_option` (
  `tudu_id` varchar(36) NOT NULL DEFAULT '',
  `vote_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所属投票ID',
  `option_id` varchar(36) NOT NULL DEFAULT '',
  `text` varchar(200) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '内容',
  `order_num` tinyint(3) NOT NULL DEFAULT '0',
  `vote_count` int(11) NOT NULL DEFAULT '0' COMMENT '得票数',
  `voters` text CHARACTER SET utf8,
  PRIMARY KEY (`option_id`),
  KEY `fk_option_of_which_vote` (`tudu_id`,`vote_id`),
  CONSTRAINT `fk_option_of_which_vote` FOREIGN KEY (`tudu_id`) REFERENCES `td_vote` (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_voter` (
  `unique_id` varchar(36) NOT NULL DEFAULT '',
  `tudu_id` varchar(36) NOT NULL DEFAULT '',
  `vote_id` varchar(255) NOT NULL DEFAULT '' COMMENT '投票ID',
  `options` text,
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tudu_id`,`vote_id`,`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_post` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '版块ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `email` varchar(128) DEFAULT NULL COMMENT '邮箱地址',
  `post_id` varchar(36) NOT NULL DEFAULT '' COMMENT '回复ID',
  `poster` varchar(15) CHARACTER SET utf8 DEFAULT NULL COMMENT '回复者姓名',
  `poster_info` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '回复者信息（如部门及职位信息）',
  `is_first` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否图度内容（最先回复的就是内容）',
  `is_log` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否更新日志',
  `is_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经发送',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否外部人员发送',
  `header` varchar(255) CHARACTER SET utf8 DEFAULT '0' COMMENT '回复头信息',
  `content` mediumtext CHARACTER SET utf8 NOT NULL COMMENT '回复内容',
  `last_modify` varchar(80) CHARACTER SET utf8 DEFAULT NULL COMMENT '最后修改信息',
  `attach_num` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `elapsed_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '耗时',
  `percent` tinyint(3) unsigned DEFAULT NULL COMMENT '完成率',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `temp_is_done` tinyint(1) NOT NULL DEFAULT '0' COMMENT '数据过渡是否完成',
  PRIMARY KEY (`tudu_id`,`post_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_unique_id` (`unique_id`),
  KEY `idx_org_board_id` (`org_id`,`board_id`),
  KEY `idx_is_first` (`is_first`,`is_log`),
  KEY `idx_temp_is_done` (`temp_is_done`),
  CONSTRAINT `fk_post_of_which_tudu` FOREIGN KEY (`tudu_id`) REFERENCES `td_tudu` (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='回复表';


CREATE TABLE `td_tudu_user` (
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `step_id` varchar(36) NOT NULL DEFAULT '^trunk' COMMENT '步骤ID，^trunk指向主干',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否外部人员',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  `is_forward` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否转发',
  `labels` text NOT NULL COMMENT '标签标识',
  `mark2` tinyint(3) NOT NULL DEFAULT '0' COMMENT '图度对用户标识',
  `mark` tinyint(3) NOT NULL DEFAULT '0',
  `role` enum('from','to','cc') DEFAULT NULL COMMENT '人员角色',
  `accepter_info` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '接收人信息 [email] [truename]',
  `percent` tinyint(3) unsigned DEFAULT NULL COMMENT '个人更新进度',
  `tudu_status` tinyint(3) unsigned DEFAULT NULL COMMENT '图度状态： 0-未开始，1-进行中，2-已完成，3-已拒绝',
  `accept_time` int(10) unsigned DEFAULT NULL COMMENT '接受时间',
  `complete_time` int(10) unsigned DEFAULT NULL COMMENT '任务完成时间',
  `forward_info` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '转发人信息 truename\\ntime',
  `auth_code` varchar(10) DEFAULT NULL COMMENT '外部用户验证码',
  PRIMARY KEY (`unique_id`,`tudu_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_tudu_id` (`tudu_id`),
  KEY `idx_role` (`role`),
  CONSTRAINT `fk_tudu_has_which_user` FOREIGN KEY (`tudu_id`) REFERENCES `td_tudu` (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度用户表';


CREATE TABLE `td_template` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '板块ID',
  `template_id` varchar(36) NOT NULL DEFAULT '' COMMENT '模板ID',
  `creator` varchar(255) NOT NULL DEFAULT '' COMMENT '创建人uniqueid',
  `name` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '模板名称',
  `content` text CHARACTER SET utf8 COMMENT '模板内容',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`board_id`,`template_id`),
  KEY `idx_creator` (`creator`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `td_tudu_label` (
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `label_id` varchar(36) NOT NULL DEFAULT '' COMMENT '标签ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  PRIMARY KEY (`unique_id`,`label_id`,`tudu_id`),
  KEY `idx_unique_tudu_id` (`unique_id`,`tudu_id`),
  CONSTRAINT `fk_tudu_has_which_label` FOREIGN KEY (`unique_id`, `tudu_id`) REFERENCES `td_tudu_user` (`unique_id`, `tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度标签表';


CREATE TABLE `td_attachment` (
  `file_id` varchar(36) NOT NULL DEFAULT '' COMMENT '文件ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `file_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '文件名称',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小（字节）',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'MIME类型',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '保存路径',
  `is_netdisk` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否来源于网盘',
  `unique_id` varchar(36) DEFAULT NULL COMMENT '用户唯一ID',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`file_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='附件信息表';


CREATE TABLE `td_attach_post` (
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `post_id` varchar(36) NOT NULL DEFAULT '' COMMENT '回复ID',
  `file_id` varchar(36) NOT NULL DEFAULT '' COMMENT '文件ID',
  `is_attach` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tudu_id`,`post_id`,`file_id`),
  KEY `idx_file_id` (`file_id`),
  KEY `idx_tudu_id` (`tudu_id`),
  CONSTRAINT `fk_attach_of_which_post` FOREIGN KEY (`tudu_id`, `post_id`) REFERENCES `td_post` (`tudu_id`, `post_id`),
  CONSTRAINT `fk_post_has_which_attach` FOREIGN KEY (`file_id`) REFERENCES `td_attachment` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_attach_flow` (
  `flow_id` varchar(36) NOT NULL DEFAULT '' COMMENT '流程ID',
  `file_id` varchar(36) NOT NULL DEFAULT '' COMMENT '文件ID',
  `is_attach` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否附件',
  PRIMARY KEY (`flow_id`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='工作流内容附件关联表';


CREATE TABLE `td_flow` (
  `flow_id` varchar(36) NOT NULL DEFAULT '' COMMENT '工作流ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `board_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所属版块ID',
  `class_id` varchar(36) DEFAULT NULL COMMENT '题主分类ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '创建人唯一ID',
  `subject` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '工作流名称',
  `description` varchar(30) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '描述',
  `avaliable` text NOT NULL COMMENT '可用人群，格式 userid@orgid\\ngroupid',
  `is_valid` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  `cc` text CHARACTER SET utf8 NOT NULL COMMENT '抄送人，格式同 td_tudu.cc',
  `elapsed_time` int(11) DEFAULT NULL COMMENT '所需时间(预计耗时)',
  `content` text CHARACTER SET utf8 COMMENT '内容模板',
  `steps` text CHARACTER SET utf8 NOT NULL COMMENT '流程，XML格式文本',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`flow_id`),
  KEY `idx_org_id_board_id` (`org_id`,`board_id`),
  KEY `idx_is_valid` (`is_valid`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='工作流定义表';


CREATE TABLE `td_flow_favor` (
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `flow_id` varchar(36) NOT NULL DEFAULT '' COMMENT '工作流ID',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`unique_id`,`flow_id`),
  KEY `idx_weight_update_time` (`weight`,`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='常用工作流';


CREATE TABLE `td_rule` (
  `rule_id` varchar(36) NOT NULL DEFAULT '',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `description` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '描述，程序生成',
  `operation` enum('ignore','starred','label','delete') NOT NULL DEFAULT 'ignore' COMMENT '执行操作',
  `mail_remind` text COMMENT '邮件提醒设置',
  `value` varchar(100) DEFAULT '' COMMENT '执行操作的值',
  `is_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可用',
  PRIMARY KEY (`rule_id`),
  KEY `idx_unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度规则';


CREATE TABLE `td_rule_filter` (
  `filter_id` varchar(36) NOT NULL DEFAULT '',
  `rule_id` varchar(36) NOT NULL DEFAULT '' COMMENT '规则ID',
  `what` enum('from','to','cc','subject') NOT NULL DEFAULT 'from' COMMENT '匹配对象',
  `type` enum('contain','exclusive','match') NOT NULL DEFAULT 'contain' COMMENT '匹配方式, 包含，不包含，完全匹配',
  `value` text CHARACTER SET utf8 COMMENT '过滤内容',
  `is_valid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可用',
  PRIMARY KEY (`filter_id`),
  KEY `idx_rule_id` (`rule_id`),
  CONSTRAINT `fk_filter_of_which_rule` FOREIGN KEY (`rule_id`) REFERENCES `td_rule` (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='图度规则过滤条件';


CREATE TABLE `nd_file` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所有者唯一ID',
  `file_id` varchar(36) NOT NULL DEFAULT '' COMMENT '文件ID',
  `folder_id` varchar(36) NOT NULL DEFAULT '' COMMENT '目录ID',
  `file_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '文件名',
  `size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `type` varchar(100) NOT NULL DEFAULT '' COMMENT 'MIME类型',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '路径',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '文件状态 1.正常 2.删除',
  `attach_file_id` varchar(36) DEFAULT NULL COMMENT '附件ID',
  `is_from_attach` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否保存自附件',
  `from_unique_id` varchar(36) DEFAULT NULL COMMENT '保存自用户(如果有)',
  `from_file_id` varchar(36) DEFAULT NULL COMMENT '源文件ID',
  `is_share` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否共享',
  PRIMARY KEY (`org_id`,`unique_id`,`file_id`),
  KEY `idx_folder_id` (`folder_id`),
  KEY `idx_file_name` (`file_name`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_is_from_attach` (`is_from_attach`),
  KEY `idx_file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='网盘文件';


CREATE TABLE `nd_folder` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所属这唯一ID',
  `folder_id` varchar(36) NOT NULL DEFAULT '' COMMENT '目录ID',
  `parent_folder_id` varchar(36) DEFAULT NULL COMMENT '父目录ID',
  `folder_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '名称',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统目录',
  `is_share` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否共享',
  `folder_size` int(11) NOT NULL DEFAULT '0' COMMENT '文件总大小',
  `max_quota` int(11) NOT NULL DEFAULT '0' COMMENT '可用空间（针对根目录设置）',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`unique_id`,`folder_id`),
  KEY `idx_folder_name` (`folder_name`),
  KEY `idx_is_system` (`is_system`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='网盘目录';


CREATE TABLE `nd_share` (
  `object_id` varchar(36) NOT NULL DEFAULT '' COMMENT '共享文件ID',
  `owner_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所属用户唯一ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `target_id` varchar(36) NOT NULL DEFAULT '' COMMENT '接受用户ID，用户email地址，群组使用群组ID',
  `object_type` enum('folder','file') NOT NULL DEFAULT 'file' COMMENT '共享对象类型',
  `owner_info` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '所有者信息 email\\ntruename',
  PRIMARY KEY (`object_id`,`owner_id`,`target_id`),
  KEY `idx_object_id_object_type` (`object_type`,`object_id`),
  KEY `idx_owner_id` (`owner_id`),
  KEY `idx_org_id_target_id` (`target_id`,`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='共享文件关系表';


CREATE TABLE `sph_index_label` (
  `index_id` varchar(255) NOT NULL DEFAULT '',
  `max_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`index_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_log` (
  `log_time` int(10) NOT NULL DEFAULT '0' COMMENT '日志时间',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `target_type` enum('tudu','board','post','cycle','vote') NOT NULL DEFAULT 'tudu' COMMENT '操作对象类型',
  `target_id` varchar(60) NOT NULL DEFAULT '' COMMENT '操作对象ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '操作人唯一ID,^system:系统操作',
  `operator` varchar(150) CHARACTER SET utf8 DEFAULT '' COMMENT '操作人信息 [email 姓名]',
  `privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0.公开 1.不公开',
  `action` varchar(50) DEFAULT '' COMMENT '操作类型',
  `detail` text CHARACTER SET utf8 COMMENT '操作详细',
  KEY `idx_object_id` (`org_id`,`target_type`,`target_id`),
  KEY `idx_unique_id` (`unique_id`),
  KEY `idx_privacy` (`privacy`),
  KEY `idx_action` (`action`(1)),
  KEY `idx_log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `td_note` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `note_id` varchar(36) NOT NULL DEFAULT '' COMMENT '便签ID',
  `tudu_id` varchar(36) DEFAULT '' COMMENT '关联的图度ID',
  `content` text CHARACTER SET utf8 NOT NULL COMMENT '内容',
  `color` int(11) NOT NULL DEFAULT '0' COMMENT '颜色，整型存储',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态 1.正常 2.删除',
  `is_notify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提醒',
  `notify_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '提醒类型',
  `notify_value` tinyint(11) NOT NULL DEFAULT '0' COMMENT '提醒类型对应的取值',
  `notify_time` varchar(10) DEFAULT NULL COMMENT '提醒时间',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`unique_id`,`note_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_update_time` (`update_time`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_tudu_id` (`tudu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='便签';




