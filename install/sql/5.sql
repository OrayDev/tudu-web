
-- app
CREATE TABLE `app_app` (
  `app_id` varchar(128) NOT NULL DEFAULT '' COMMENT '应用ID，JAVA包格式',
  `app_name` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '应用名称',
  `version` varchar(10) NOT NULL DEFAULT '' COMMENT '版本号',
  `type` enum('inner','outer') NOT NULL DEFAULT 'inner' COMMENT '应用类型',
  `open_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '开放形式 0.全部开放 1.仅对关联的用户开放',
  `url` varchar(255) DEFAULT NULL COMMENT '访问URL，仅针对外部应用',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='应用数据表';


-- Table "app_info" DDL

CREATE TABLE `app_info` (
  `app_id` varchar(50) NOT NULL DEFAULT '' COMMENT '应用ID',
  `author` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '作者',
  `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标地址',
  `description` varchar(200) CHARACTER SET utf8 DEFAULT NULL COMMENT '描述',
  `content` text CHARACTER SET utf8 NOT NULL COMMENT '应用详细',
  `languages` tinyint(1) DEFAULT NULL COMMENT '支持语言 1.简中 2.繁体 4.英',
  `score` tinyint(3) NOT NULL DEFAULT '0' COMMENT '评分',
  `comment_num` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `last_update_time` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='应用扩展信息表，记录与业务无关的数据，应用说明等';

-- Table "app_info_attach" DDL

CREATE TABLE `app_info_attach` (
  `app_id` varchar(50) NOT NULL DEFAULT '' COMMENT '应用ID',
  `type` enum('photo','video','audio') NOT NULL DEFAULT 'photo' COMMENT '附件类型',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '附件URL',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序ID',
  KEY `idx_type` (`type`),
  KEY `idx_order_num` (`order_num`),
  KEY `idx_app_id` (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='应用介绍附件数据表，应用详细页面可嵌入图片，音，视频等';



CREATE TABLE `app_org` (
  `app_id` varchar(120) NOT NULL DEFAULT '' COMMENT '应用ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用状态 0.未初始化 1.启用 2.停用',
  `settings` text CHARACTER SET utf8 COMMENT '应用的其他设置',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `expire_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `active_time` int(11) unsigned DEFAULT NULL COMMENT '激活时间',
  PRIMARY KEY (`app_id`,`org_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_active_time` (`active_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='应用安装记录表';



-- Table "app_user" DDL

CREATE TABLE `app_user` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `app_id` varchar(50) NOT NULL DEFAULT '' COMMENT '应用ID',
  `item_id` varchar(120) NOT NULL DEFAULT '' COMMENT '应用人员帐号 / 群组ID',
  `role` varchar(20) NOT NULL DEFAULT '' COMMENT '应用角色（值由各应用定义）',
  PRIMARY KEY (`org_id`,`item_id`,`app_id`,`role`),
  KEY `idx_app_id` (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='应用到组织关联数据表';



-- Table "attend_user" DDL

CREATE TABLE `attend_user` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `dept_id` varchar(36) DEFAULT NULL COMMENT '部门ID',
  `true_name` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '用户真实姓名',
  `dept_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '部门名称',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`unique_id`),
  KEY `idx_attend_user_org_id` (`org_id`),
  KEY `idx_attend_user_dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤用户信息表';



-- Table "attend_total" DDL

CREATE TABLE `attend_total` (
  `category_id` varchar(36) NOT NULL DEFAULT '' COMMENT '考勤分类ID',
  `org_id` varchar(60) DEFAULT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `date` int(10) NOT NULL DEFAULT '0' COMMENT '统计的年月',
  `total` float NOT NULL DEFAULT '0' COMMENT '统计结果',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`category_id`,`unique_id`,`date`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤分类';



-- Table "attend_apply" DDL

CREATE TABLE `attend_apply` (
  `apply_id` varchar(36) NOT NULL DEFAULT '' COMMENT '申请ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '所属组织ID',
  `tudu_id` varchar(36) NOT NULL DEFAULT '' COMMENT '图度ID',
  `category_id` varchar(36) NOT NULL DEFAULT '' COMMENT '申请类型',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '指向用户唯一ID',
  `sender_id` varchar(36) NOT NULL DEFAULT '' COMMENT '发送用户唯一ID',
  `user_info` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '申请人用户信息 email truename',
  `checkin_type` tinyint(1) unsigned DEFAULT NULL COMMENT '补签类型（0签到，1签退）',
  `is_allday` tinyint(1) unsigned DEFAULT NULL COMMENT '时间类型（天、小时）',
  `start_time` int(11) unsigned DEFAULT NULL COMMENT '起始时间',
  `end_time` int(11) unsigned DEFAULT NULL COMMENT '截止时间',
  `period` float(10,1) DEFAULT NULL COMMENT '时长(小时)',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '申请状态 0. 新发送 1. 审批中 2. 已通过 3. 已拒绝 4. 已取消',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`apply_id`),
  UNIQUE KEY `idx_tudu_id` (`tudu_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_unique_id` (`unique_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤申请记录表';



-- Table "attend_apply_reviewer" DDL

CREATE TABLE `attend_apply_reviewer` (
  `apply_id` varchar(36) NOT NULL DEFAULT '' COMMENT '申请记录ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '审批人ID',
  `review_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批状态 0.未审批 1.已通过 2. 已拒绝',
  PRIMARY KEY (`apply_id`,`unique_id`),
  KEY `idx_review_status` (`review_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤审批审批人关联表';



-- Table "attend_category" DDL

CREATE TABLE `attend_category` (
  `category_id` varchar(36) NOT NULL COMMENT '考勤分类ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `category_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '考勤分类名称',
  `flow_steps` text CHARACTER SET utf8 COMMENT '批审流程',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '考勤分类状态（0：停用，1：正常）',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统考勤分类',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`category_id`,`org_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idex_category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤分类';



-- Table "attend_checkin" DDL

CREATE TABLE `attend_checkin` (
  `checkin_id` varchar(36) NOT NULL COMMENT '签到ID',
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '签到用户唯一ID',
  `date` int(10) unsigned NOT NULL COMMENT '日期',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '登记类型（0：上班签到，1：下班签退）',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '考勤状况（0：正常，1：迟到，2：早退，3：旷工）',
  `ip` int(11) unsigned NOT NULL COMMENT '签到IP地址',
  `address` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '地理位置',
  `create_time` int(10) unsigned NOT NULL COMMENT '签到时间',
  PRIMARY KEY (`checkin_id`),
  KEY `idx_unique_id_date` (`unique_id`,`date`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤登记(签到签退)';



-- Table "attend_date" DDL

CREATE TABLE `attend_date` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `date` int(10) NOT NULL COMMENT '统计的日期',
  `is_late` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '迟到',
  `is_leave` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '早退',
  `is_work` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '旷工',
  `is_abnormal_ip` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否IP异常',
  `checkin_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '签到/退状态',
  `work_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '工作时长',
  `update_time` int(10) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`unique_id`,`date`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤统计（天）';



-- Table "attend_date_apply" DDL

CREATE TABLE `attend_date_apply` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '唯一ID',
  `date` int(10) unsigned NOT NULL COMMENT '日期',
  `category_id` varchar(36) NOT NULL COMMENT '申请类型ID',
  `memo` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '说明',
  KEY `idx_org_id` (`org_id`),
  KEY `idx_unique_id` (`unique_id`),
  KEY `idx_date_id` (`date`),
  KEY `idx_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



-- Table "attend_month" DDL

CREATE TABLE `attend_month` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `date` int(10) unsigned NOT NULL COMMENT '日期',
  `late` int(2) DEFAULT '0' COMMENT '迟到（单位：次）',
  `leave` int(2) DEFAULT '0' COMMENT '早退（单位：次）',
  `unwork` int(2) DEFAULT '0' COMMENT '旷工（单位：次）',
  `is_abnormal_ip` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '当前月份是否存在IP异常',
  `update_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`unique_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='考勤月统计表（只统计：迟到、早退、旷工的次数）';



-- Table "attend_schedule" DDL

CREATE TABLE `attend_schedule` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `schedule_id` varchar(36) NOT NULL COMMENT '排班方案ID',
  `unique_id` varchar(36) NOT NULL COMMENT '排班方案创建人唯一ID',
  `name` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '排班方案名称',
  `bgcolor` char(7) DEFAULT NULL COMMENT '颜色',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否系统排班方案',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `is_valid` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  PRIMARY KEY (`schedule_id`,`org_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_is_system` (`is_system`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_bgcolor` (`bgcolor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='排班方案数据表';

-- Table "attend_schedule_adjust" DDL

CREATE TABLE `attend_schedule_adjust` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `adjust_id` varchar(36) NOT NULL COMMENT '调整记录ID',
  `subject` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '标题',
  `start_time` int(10) unsigned NOT NULL COMMENT '调整起始时间',
  `end_time` int(10) unsigned NOT NULL COMMENT '调整截至时间',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '调整类型 0.非工作日 1.工作日',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`adjust_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='排班工作日调整';



-- Table "attend_schedule_adjust_user" DDL

CREATE TABLE `attend_schedule_adjust_user` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `adjust_id` varchar(36) NOT NULL COMMENT '调整记录ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`adjust_id`,`unique_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='工作日调整与用户关联记录';



-- Table "attend_schedule_plan" DDL

CREATE TABLE `attend_schedule_plan` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `plan_id` varchar(36) NOT NULL COMMENT '排班计划ID',
  `type` tinyint(1) NOT NULL COMMENT '排班计划类型',
  `memo` varchar(300) CHARACTER SET utf8 DEFAULT NULL COMMENT '计划备注',
  `cycle_num` tinyint(3) NOT NULL DEFAULT '1',
  `create_time` int(10) unsigned NOT NULL COMMENT '记录创建时间',
  PRIMARY KEY (`plan_id`,`org_id`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='排班计划数据表';



-- Table "attend_schedule_plan_detail" DDL

CREATE TABLE `attend_schedule_plan_detail` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `plan_id` varchar(36) NOT NULL COMMENT '排班计划ID',
  `schedule_id` varchar(36) NOT NULL COMMENT '排班方案ID',
  `value` int(10) unsigned NOT NULL COMMENT '记录排班类型对应的值，排班计划的第几天',
  KEY `idx_org_id` (`org_id`),
  KEY `idx_plan_id` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='排班计划详细数据表';


-- Table "attend_schedule_plan_month" DDL

CREATE TABLE `attend_schedule_plan_month` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `date` int(6) unsigned NOT NULL COMMENT '排班计划年月',
  `plan` text NOT NULL COMMENT '月排班计划，json格式',
  `memo` varchar(300) CHARACTER SET utf8 DEFAULT NULL COMMENT '计划备注',
  `update_time` int(10) unsigned NOT NULL COMMENT '记录更新时间',
  PRIMARY KEY (`unique_id`,`date`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='月排班计划数据表';



-- Table "attend_schedule_plan_user" DDL

CREATE TABLE `attend_schedule_plan_user` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `plan_id` varchar(36) NOT NULL COMMENT '使用排班计划ID',
  `start_time` int(10) unsigned NOT NULL COMMENT '计划执行开始时间',
  `end_time` int(10) unsigned DEFAULT NULL COMMENT '计划执行截至时间',
  KEY `idx_org_id` (`org_id`),
  KEY `idx_unique_id` (`unique_id`),
  KEY `idx_plan_id` (`plan_id`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户排班计划关联表';



-- Table "attend_schedule_plan_week" DDL

CREATE TABLE `attend_schedule_plan_week` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL COMMENT '用户唯一ID',
  `plan` text NOT NULL COMMENT '周排班计划，json格式',
  `cycle_num` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '周循环次数',
  `memo` varchar(300) CHARACTER SET utf8 DEFAULT NULL COMMENT '备注',
  `effect_date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '生效时间',
  PRIMARY KEY (`unique_id`),
  KEY `idx_org_id` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='周排班计划数据表（一般只为生成月排班）';



-- Table "attend_schedule_rule" DDL

CREATE TABLE `attend_schedule_rule` (
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `schedule_id` varchar(36) NOT NULL COMMENT '排班方案ID',
  `rule_id` varchar(36) NOT NULL COMMENT '规则ID',
  `week` tinyint(1) unsigned DEFAULT NULL COMMENT '周几(0-6)',
  `checkin_time` int(5) unsigned DEFAULT NULL COMMENT '签到时间(单位:s)',
  `checkout_time` int(5) unsigned DEFAULT NULL COMMENT '下班签退时间',
  `late_standard` int(4) unsigned DEFAULT NULL COMMENT '迟到标准',
  `late_checkin` int(4) unsigned DEFAULT NULL COMMENT '旷工标准',
  `leave_standard` int(4) unsigned DEFAULT NULL COMMENT '早退标准',
  `leave_checkout` int(4) unsigned DEFAULT NULL COMMENT '签退旷工标准',
  `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`rule_id`,`schedule_id`,`org_id`),
  KEY `idx_org_id` (`org_id`),
  KEY `idx_schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='排班方案规则表';


INSERT INTO `app_app` VALUES ('attend', '考勤应用', '1.0', 'inner', '0', '/app/attend');
INSERT INTO `app_info` VALUES ('attend', 'www.tudu.com', '/admin/img/icon_attend.gif', '图度考勤应用，是图度OA办公软件推出的一款免费的考勤管理应用，旨在为用户提供准确、完善和高效的考勤管理服务。', '1、可以根据不同的工作人群，灵活的进行排班设置<br />2、实时记录考勤签到情况<br />3、方便用户异地考勤，解决了用户地域考勤的局限性<br />4、简单完善的考勤申请，方便快捷的考勤审批<br />5、准确高效的考勤统计，方便用户详细查询考勤记录<br />', '1', '0', '0', '1341300583', '1341300583');
INSERT INTO `app_info_attach` VALUES ('attend', 'photo', '/admin/img/attend/intro_1.jpg', '0');
INSERT INTO `app_info_attach` VALUES ('attend', 'photo', '/admin/img/attend/intro_2.jpg', '1');
INSERT INTO `app_info_attach` VALUES ('attend', 'photo', '/admin/img/attend/intro_3.jpg', '2');
INSERT INTO `app_info_attach` VALUES ('attend', 'photo', '/admin/img/attend/intro_4.jpg', '3');
