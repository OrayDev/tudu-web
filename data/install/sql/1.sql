CREATE TABLE `md_organization` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `cos_id` int(11) NOT NULL DEFAULT '0' COMMENT '服务等级ID',
  `org_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '组织名称',
  `ts_id` int(11) NOT NULL DEFAULT '0' COMMENT 'TsID',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '状态: 0 - 正常，1 - 禁止，2 - 锁定',
  `is_active` tinyint(3) NOT NULL DEFAULT 1 COMMENT '是否可用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `expire_date` datetime DEFAULT NULL COMMENT '过期时间',
  `logo` blob COMMENT 'LOGO',
  `slogan` varchar(100) DEFAULT NULL COMMENT '企业口号',
  `intro` text CHARACTER SET utf8 COMMENT '企业介绍',
  `footer` text CHARACTER SET utf8,
  `password_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '密码安全级别',
  `lock_time` int(11) NOT NULL DEFAULT '0' COMMENT '锁定次数',
  `timezone` varchar(20) DEFAULT NULL COMMENT '默认时区',
  `date_format` varchar(50) DEFAULT NULL COMMENT '日期格式',
  `skin` varchar(10) DEFAULT NULL COMMENT '默认皮肤',
  `login_skin` varchar(255) DEFAULT NULL COMMENT '登录页设置',
  `default_password` varchar(16) DEFAULT NULL,
  `time_limit` varchar(48) DEFAULT NULL COMMENT '登录时间限制，格式24bit，16进制整形序列FFFFFF,FFEFF',
  `is_https` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否全程使用https',
  `is_ip_rule` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启IP过滤',
  `memo` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '备注，后台查看使用',
  PRIMARY KEY (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC COMMENT='组织信息表';


CREATE TABLE `md_org_info` (
  `org_id` varchar(60) NOT NULL,
  `entire_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '组织全名（与实名认证相关）',
  `industry` varchar(10) DEFAULT NULL,
  `contact` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL COMMENT '传真号码',
  `province` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '城市',
  `address` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `postcode` varchar(10) DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `realname_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '实名状态 0.未通过 1.已提交 2. 已通过',
  PRIMARY KEY (`org_id`),
  KEY `idx_entire_name` (`entire_name`),
  CONSTRAINT `info_of_which_org` FOREIGN KEY (`org_id`) REFERENCES `md_organization` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='组织信息表';


CREATE TABLE `md_access` (
  `access_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  `access_name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '权限名称',
  `value_type` char(1) NOT NULL DEFAULT '' COMMENT '值的类型',
  `form_type` char(1) NOT NULL DEFAULT '' COMMENT '表单的类型',
  `default_value` varchar(250) DEFAULT NULL COMMENT '默认值',
  PRIMARY KEY (`access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='权限定义表';
INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (101, '允许移动办公', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (102, '允许自定义皮肤', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (201, '允许新增版块', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (202, '允许编辑版块', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (203, '允许删除版块', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (204, '允许关闭板块', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (301, '允许发新图度', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (302, '允许发表回复', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (303, '允许编辑图度', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (304, '允许编辑回复', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (305, '允许删除图度', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (306, '允许删除回复', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (307, '允许转发图度', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (308, '允许添加到图度组', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (401, '允许下载/查看附件', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (402, '允许发布附件', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (403, '最大附件尺寸(单位K 1M=1024K)', 'I', 'I', '1024');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (404, '每天最大附件数量', 'I', 'I', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (501, '允许发起讨论', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (502, '允许发起公告', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (503, '允许发起投票', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (504, '允许发起会议', 'B', 'R', '1');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (511, '允许创建工作流', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (512, '允许修改工作流', 'B', 'R', '0');

INSERT INTO md_access
   (`access_id`, `access_name`, `value_type`, `form_type`, `default_value`)
VALUES
   (513, '允许删除工作流', 'B', 'R', '0');


CREATE TABLE `md_org_host` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `host` varchar(128) NOT NULL DEFAULT '' COMMENT '主机名',
  PRIMARY KEY (`org_id`,`host`),
  UNIQUE KEY `idx_host` (`host`),
  KEY `fk_host_of_which_org` (`org_id`),
  CONSTRAINT `fk_host_of_which_org` FOREIGN KEY (`org_id`) REFERENCES `md_organization` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='组织绑定主机表';


CREATE TABLE `md_org_iprule` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型（0.允许，1.禁止）',
  `rule` text COMMENT 'IP规则',
  `is_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可用',
  `exception` text COMMENT '例外用户/群组 address + "\\\\n" + groupid',
  PRIMARY KEY (`org_id`,`type`),
  KEY `idx_is_valid` (`is_valid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='系统IP限制列表';


CREATE TABLE `md_department` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `dept_id` varchar(36) NOT NULL DEFAULT '' COMMENT '部门ID',
  `dept_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '部门名称',
  `parent_dept_id` varchar(60) DEFAULT NULL COMMENT '上级部门ID',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '列表排序数值',
  `moderators` text CHARACTER SET utf8 NOT NULL COMMENT '负责人姓名',
  PRIMARY KEY (`org_id`,`dept_id`),
  KEY `idx_dept_list_rank` (`org_id`,`parent_dept_id`,`order_num`),
  CONSTRAINT `fk_dept_of_which_org` FOREIGN KEY (`org_id`) REFERENCES `md_organization` (`org_id`),
  CONSTRAINT `fk_parent_dept` FOREIGN KEY (`org_id`, `parent_dept_id`) REFERENCES `md_department` (`org_id`, `dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='部门表';


CREATE TABLE `md_group` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `group_id` varchar(60) NOT NULL DEFAULT '' COMMENT '群组ID',
  `group_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '群组名称',
  `is_system` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否系统群组',
  `is_locked` tinyint(3) NOT NULL DEFAULT '0',
  `admin_level` int(11) NOT NULL DEFAULT '0' COMMENT '管理级别',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '排序索引',
  PRIMARY KEY (`org_id`,`group_id`),
  KEY `idx_order_num` (`order_num`),
  CONSTRAINT `fk_group_of_which_org` FOREIGN KEY (`org_id`) REFERENCES `md_organization` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户群组表';


CREATE TABLE `md_role` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `role_id` varchar(60) NOT NULL DEFAULT '' COMMENT '群组ID',
  `role_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '群组名称',
  `is_system` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否系统群组',
  `is_locked` tinyint(3) NOT NULL DEFAULT '0',
  `admin_level` int(11) NOT NULL DEFAULT '0' COMMENT '管理级别',
  PRIMARY KEY (`org_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='用户群组表';


CREATE TABLE `md_role_access` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `role_id` varchar(60) NOT NULL DEFAULT '' COMMENT '权限组ID',
  `access_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  `value` varchar(250) DEFAULT NULL COMMENT '权限值',
  PRIMARY KEY (`org_id`,`role_id`,`access_id`),
  KEY `fk_group_has_which_access` (`access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='群组权限表';


CREATE TABLE `md_user` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '唯一标识ID',
  `dept_id` varchar(36) DEFAULT NULL COMMENT '部门ID',
  `cast_id` varchar(36) NOT NULL DEFAULT '^default' COMMENT '组织架构ID',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `expire_date` datetime DEFAULT NULL COMMENT '过期时间',
  `is_show` tinyint(3) NOT NULL DEFAULT '0' COMMENT '通讯录中显示',
  `order_num` int(11) NOT NULL DEFAULT '0' COMMENT '列表排序数值',
  `unlock_time` int(11) DEFAULT NULL COMMENT '解锁时间',
  `login_retry` tinyint(3) DEFAULT '0',
  `max_nd_quota` int(11) NOT NULL DEFAULT '0' COMMENT '网盘最大空间',
  `init_password` tinyint(1) NOT NULL DEFAULT '0' COMMENT '初始化密码标识',
  `last_update_time` int(10) unsigned DEFAULT NULL COMMENT '最后更新时间',
  `last_update_time2` int(10) unsigned DEFAULT NULL COMMENT '用户最后更新时间',
  PRIMARY KEY (`org_id`,`user_id`),
  UNIQUE KEY `ak_unique_id` (`unique_id`),
  KEY `idx_order_num` (`org_id`,`dept_id`,`order_num`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_user_of_which_dept` FOREIGN KEY (`org_id`, `dept_id`) REFERENCES `md_department` (`org_id`, `dept_id`),
  CONSTRAINT `fk_user_of_which_org` FOREIGN KEY (`org_id`) REFERENCES `md_organization` (`org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户表';


CREATE TABLE `md_user_access` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `access_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限ID',
  `value` varchar(250) DEFAULT NULL COMMENT '权限值',
  PRIMARY KEY (`org_id`,`user_id`,`access_id`),
  KEY `fk_user_has_which_access` (`access_id`),
  CONSTRAINT `fk_access_of_which_user` FOREIGN KEY (`org_id`, `user_id`) REFERENCES `md_user` (`org_id`, `user_id`),
  CONSTRAINT `fk_user_has_which_access` FOREIGN KEY (`access_id`) REFERENCES `md_access` (`access_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户自定义权限表';


CREATE TABLE `md_user_info` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `true_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '真实姓名',
  `pinyin` varchar(50) DEFAULT NULL COMMENT '拼音缩写',
  `password` varchar(80) DEFAULT NULL COMMENT '用户密码',
  `position` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '职位',
  `industry` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '行业（公众版）',
  `nick` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '昵称',
  `avatars` blob COMMENT '头像',
  `avatars_type` varchar(30) DEFAULT NULL COMMENT '头像格式',
  `gender` tinyint(3) DEFAULT NULL COMMENT '性别',
  `id_number` varchar(50) DEFAULT NULL COMMENT '证件号码',
  `birthday` datetime DEFAULT NULL COMMENT '出生日期',
  `mobile` varchar(30) DEFAULT NULL COMMENT '手机号码',
  `tel` varchar(50) DEFAULT NULL COMMENT '固定电话',
  `email` varchar(128) DEFAULT NULL COMMENT '用户邮箱',
  `office_location` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '工作地点',
  `sign` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '签名',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '个人信息更新时间',
  `province` varchar(20) CHARACTER SET utf8 DEFAULT '' COMMENT '省份',
  `city` varchar(20) CHARACTER SET utf8 DEFAULT NULL COMMENT '城市',
  PRIMARY KEY (`org_id`,`user_id`),
  CONSTRAINT `fk_user_info` FOREIGN KEY (`org_id`, `user_id`) REFERENCES `md_user` (`org_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户信息表';


CREATE TABLE `md_user_data` (
  `org_id` varchar(60) NOT NULL DEFAULT '',
  `user_id` varchar(60) NOT NULL DEFAULT '',
  `im` blob,
  `skin` varchar(10) DEFAULT NULL,
  `timezone` varchar(20) DEFAULT NULL COMMENT '时区',
  `language` varchar(20) DEFAULT NULL COMMENT '语言',
  `pagesize` int(11) NOT NULL DEFAULT '25' COMMENT '每页显示图度数',
  `date_format` varchar(24) CHARACTER SET utf8 DEFAULT NULL COMMENT '日期格式',
  `profile_mode` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'tudu回复用户信息显示模式',
  `expired_filter` int(11) NOT NULL DEFAULT '0' COMMENT '过期x天tudu不再显示',
  `post_sort` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回复查看顺序0. 顺序,1. 倒序',
  `settings` text COMMENT '户用设置',
  `enable_search` tinyint(1) NOT NULL DEFAULT '3' COMMENT '允许查找 1.enable uid, 2.enable nick, 1|2 both',
  `enable_buddy` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否允许添加好友',
  `usual_local` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '常用地址',
  PRIMARY KEY (`org_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户数据';


CREATE TABLE `md_user_email` (
  `org_id` varchar(60) NOT NULL,
  `user_id` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  PRIMARY KEY (`org_id`,`user_id`),
  UNIQUE KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户绑定邮箱';


CREATE TABLE `md_user_group` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `group_id` varchar(36) NOT NULL DEFAULT '' COMMENT '群组ID',
  PRIMARY KEY (`org_id`,`user_id`,`group_id`),
  KEY `fk_user_has_which_group` (`org_id`,`group_id`),
  CONSTRAINT `fk_group_of_which_user` FOREIGN KEY (`org_id`, `user_id`) REFERENCES `md_user` (`org_id`, `user_id`),
  CONSTRAINT `fk_user_has_which_group` FOREIGN KEY (`org_id`, `group_id`) REFERENCES `md_group` (`org_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户对应群组表';


CREATE TABLE `md_user_role` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `role_id` varchar(36) NOT NULL DEFAULT '' COMMENT '群组ID',
  PRIMARY KEY (`org_id`,`user_id`,`role_id`),
  KEY `fk_user_has_which_group` (`org_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='用户对应群组表';


CREATE TABLE `md_user_session` (
  `session_id` varchar(32) NOT NULL COMMENT '会话ID',
  `org_id` varchar(60) NOT NULL COMMENT '组织ID',
  `user_id` varchar(16) NOT NULL COMMENT '用户ID',
  `login_ip` varchar(20) DEFAULT NULL COMMENT '登录时的IP地址',
  `login_time` int(11) unsigned NOT NULL COMMENT '登录时间',
  `expire_time` int(11) unsigned DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户持久登录验证信息记录';


CREATE TABLE `md_user_tips` (
  `unique_id` varchar(36) NOT NULL DEFAULT '',
  `tips_id` varchar(36) NOT NULL DEFAULT '' COMMENT '提示信息标识',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0.未读 1.已读',
  PRIMARY KEY (`unique_id`,`tips_id`),
  KEY `idx_md_user_tips_unique_id` (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='用户提示信息';


CREATE TABLE `md_site_admin` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '用户ID',
  `admin_type` char(3) NOT NULL DEFAULT '' COMMENT '管理类型',
  `admin_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '管理级别',
  PRIMARY KEY (`org_id`,`user_id`),
  CONSTRAINT `fk_who_is_admin` FOREIGN KEY (`org_id`, `user_id`) REFERENCES `md_user` (`org_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='站点管理员表';


CREATE TABLE `md_cast_disable_dept` (
  `org_id` varchar(60) NOT NULL DEFAULT '',
  `owner_id` varchar(36) NOT NULL DEFAULT '' COMMENT '所有者ID',
  `dept_id` varchar(36) NOT NULL DEFAULT '' COMMENT '不可见部门ID',
  PRIMARY KEY (`org_id`,`owner_id`,`dept_id`),
  KEY `idx_owner_id` (`org_id`,`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `md_cast_disable_user` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `owner_id` varchar(255) NOT NULL DEFAULT '' COMMENT '所有者ID',
  `user_id` varchar(255) NOT NULL DEFAULT '' COMMENT '不可见用户名',
  PRIMARY KEY (`org_id`,`owner_id`,`user_id`),
  KEY `idx_owner_id` (`org_id`,`owner_id`),
  KEY `idx_org_id_user_id` (`org_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `md_email` (
  `org_id` varchar(60) NOT NULL DEFAULT '',
  `user_id` varchar(36) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '邮件地址',
  `password` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱密码',
  `protocol` enum('imap','pop3') NOT NULL DEFAULT 'imap' COMMENT '协议类型',
  `imap_host` varchar(200) NOT NULL DEFAULT '' COMMENT '主机名',
  `port` int(6) DEFAULT NULL COMMENT '连接端口',
  `is_ssl` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用SSL连接',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱类型 0.公共邮箱 1.橄榄邮 2.其它企业邮箱',
  `is_notify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启提醒',
  `last_check_info` varchar(200) DEFAULT NULL COMMENT '最后检测记录信息 未读数\\n最新MID\\n最新主题',
  `last_check_time` int(10) DEFAULT NULL COMMENT '最后检测时间',
  `order_num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`org_id`,`user_id`,`address`),
  KEY `idx_org_id_user_id` (`org_id`,`user_id`),
  KEY `idx_order_num` (`order_num`),
  CONSTRAINT `fk_email_of_which_user` FOREIGN KEY (`org_id`, `user_id`) REFERENCES `md_user` (`org_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `md_login_log` (
  `login_log_id` varchar(36) NOT NULL DEFAULT '' COMMENT '日志ID',
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `unique_id` varchar(36) NOT NULL DEFAULT '' COMMENT '用户唯一ID',
  `address` varchar(120) NOT NULL DEFAULT '' COMMENT '帐号(email地址格式)',
  `truename` varchar(60) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '姓名',
  `ip` varchar(15) DEFAULT NULL COMMENT 'ip地址',
  `local` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '地理位置',
  `isp` varchar(50) DEFAULT NULL COMMENT '运营商',
  `clientkey` varchar(50) DEFAULT '' COMMENT '客户端登录标识',
  `client_info` varchar(255) NOT NULL DEFAULT '' COMMENT '客户端信息：BROWSER\\\\\\\\\\\\\\\\nSYSTEM',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '登录时间',
  PRIMARY KEY (`login_log_id`),
  KEY `idx_org_id_unique_id` (`org_id`,`unique_id`),
  KEY `idx_address` (`address`),
  KEY `idx_truename` (`truename`),
  KEY `idx_ip` (`ip`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_org_id_unique_id_create_time` (`org_id`,`unique_id`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='前台用户登录日志';


CREATE TABLE `md_op_log` (
  `org_id` varchar(60) NOT NULL DEFAULT '' COMMENT '组织ID',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  `user_id` varchar(60) NOT NULL DEFAULT '' COMMENT '操作用户',
  `ip` varchar(50) DEFAULT NULL COMMENT '操作IP',
  `local` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT '所在地',
  `module` enum('user','group','dept','cast','board','login','role','secure','org') DEFAULT NULL,
  `action` enum('create','update','delete','login','logout') DEFAULT NULL,
  `sub_action` varchar(255) DEFAULT NULL,
  `target` varchar(255) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `detail` varchar(255) CHARACTER SET utf8 DEFAULT '' COMMENT '更新内容，PHP serialize 格式',
  KEY `idx_org_id` (`org_id`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `md_ip_data` (
  `start_ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '起始IP',
  `end_ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '终止IP',
  `province` varchar(100) CHARACTER SET utf8 DEFAULT NULL COMMENT '省份',
  `city` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `isp` varchar(50) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ISP名称',
  KEY `idx_start_ip` (`start_ip`),
  KEY `idx_end_ip` (`end_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='IP数据地址库';

