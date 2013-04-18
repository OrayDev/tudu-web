INSERT INTO md_organization (
org_id, 
org_name, 
ts_id, 
status,
create_time,
password_level,
default_password,
is_https
) VALUES (
'testorg',
'测试专用组织',
1,
0,
UNIX_TIMESTAMP(),
1,
'123456',
0
);

INSERT INTO md_org_host (org_id, host) VALUES ('testorg', 'testorg.tudu.com');

INSERT INTO md_org_info (
org_id,
entire_name,
description
) VALUES (
'testorg',
'测试专用组织',
''
);

INSERT INTO md_department (
org_id,
dept_id,
dept_name,
order_num,
moderators
) VALUES (
'testorg',
'^root',
'^rootname',
0,
'admin'
);

INSERT INTO md_group (
org_id,
group_id,
group_name,
is_system,
admin_level,
order_num
) VALUES (
'testorg',
'^all',
'全体人员',
1,
0,
1
);

INSERT INTO md_role (
org_id,
role_id,
role_name,
is_system,
is_locked,
admin_level
) VALUES (
'testorg',
'^user',
'普通用户',
1,
0,
1
);
INSERT INTO md_role_access (org_id, role_id, access_id, value) VALUES 
('testorg', '^user', 101, 1),
('testorg', '^user', 102, 1),
('testorg', '^user', 301, 1),
('testorg', '^user', 302, 1),
('testorg', '^user', 303, 1),
('testorg', '^user', 304, 1),
('testorg', '^user', 306, 1),
('testorg', '^user', 501, 1),
('testorg', '^user', 502, 1),
('testorg', '^user', 503, 1),
('testorg', '^user', 504, 1),
('testorg', '^user', 401, 1),
('testorg', '^user', 402, 1);

INSERT INTO md_role (
org_id,
role_id,
role_name,
is_system,
is_locked,
admin_level
) VALUES (
'testorg',
'^advanced',
'高级用户',
1,
0,
2
);
INSERT INTO md_role_access (org_id, role_id, access_id, value) VALUES 
('testorg', '^advanced', 101, 1),
('testorg', '^advanced', 102, 1),
('testorg', '^advanced', 201, 1),
('testorg', '^advanced', 202, 1),
('testorg', '^advanced', 203, 1),
('testorg', '^advanced', 204, 1),
('testorg', '^advanced', 301, 1),
('testorg', '^advanced', 302, 1),
('testorg', '^advanced', 303, 1),
('testorg', '^advanced', 304, 1),
('testorg', '^advanced', 306, 1),
('testorg', '^advanced', 501, 1),
('testorg', '^advanced', 502, 1),
('testorg', '^advanced', 503, 1),
('testorg', '^advanced', 504, 1),
('testorg', '^advanced', 511, 1),
('testorg', '^advanced', 512, 1),
('testorg', '^advanced', 513, 1),
('testorg', '^advanced', 401, 1),
('testorg', '^advanced', 402, 1);

INSERT INTO md_role (
org_id,
role_id,
role_name,
is_system,
is_locked,
admin_level
) VALUES (
'testorg',
'^admin',
'高级管理员',
1,
0,
3
);
INSERT INTO md_role_access (org_id, role_id, access_id, value) VALUES 
('testorg', '^admin', 101, 1),
('testorg', '^admin', 102, 1),
('testorg', '^admin', 201, 1),
('testorg', '^admin', 202, 1),
('testorg', '^admin', 203, 1),
('testorg', '^admin', 204, 1),
('testorg', '^admin', 301, 1),
('testorg', '^admin', 302, 1),
('testorg', '^admin', 303, 1),
('testorg', '^admin', 304, 1),
('testorg', '^admin', 305, 1),
('testorg', '^admin', 306, 1),
('testorg', '^admin', 501, 1),
('testorg', '^admin', 502, 1),
('testorg', '^admin', 503, 1),
('testorg', '^admin', 504, 1),
('testorg', '^admin', 511, 1),
('testorg', '^admin', 512, 1),
('testorg', '^admin', 513, 1),
('testorg', '^admin', 401, 1),
('testorg', '^admin', 402, 1);


INSERT INTO md_user (
org_id,
user_id,
unique_id,
dept_id,
status,
max_nd_quota
) VALUES (
'testorg',
'admin',
'36e0c15dfd30d4e8',
null,
1,
10000000
);

INSERT INTO md_user_info (
org_id,
user_id,
true_name,
pinyin,
password
) VALUES (
'testorg',
'admin',
'管理员',
'gly',
'96e79218965eb72c92a549dd5a330112'
);

INSERT INTO md_user_data (org_id, user_id) VALUES ('testorg', 'admin');

INSERT INTO md_site_admin (org_id, user_id, admin_type, admin_level) VALUES ('testorg', 'admin', 'SA', 3);

INSERT INTO md_user_group (
org_id,
user_id,
group_id
) VALUES (
'testorg',
'admin',
'^all'
);

INSERT INTO md_user_role (org_id, user_id, role_id) VALUES ('testorg', 'admin', '^user'), ('testorg', 'admin', '^admin');