DELIMITER //

# Procedure "sp_nd_add_file" DDL
DROP PROCEDURE IF EXISTS `sp_nd_add_file`//
CREATE PROCEDURE  `sp_nd_add_file`(in_org_id varchar(60), in_unique_id varchar(36), in_file_id varchar(36), in_folder_id varchar(36), in_path varchar(255), in_file_name varchar(255) charset utf8, in_file_size int(11), in_file_type varchar(100))
    SQL SECURITY INVOKER
BEGIN

DECLARE use_quota int(11) DEFAULT 0;

DECLARE root_max_quota int(11) DEFAULT 0;

DECLARE to_folder_id varchar(36);



SET to_folder_id = in_folder_id;



# 已使用空间

SELECT IFNULL(SUM(size), 0) INTO use_quota FROM nd_file WHERE org_id = in_org_id AND unique_id = in_unique_id;



# 最大可用空间，根目录可用空间

SELECT max_quota INTO root_max_quota FROM nd_folder WHERE org_id = in_org_id AND unique_id = in_unique_id AND folder_id = '^root';



IF (root_max_quota <= 0 OR use_quota + in_file_size > root_max_quota) THEN

     SELECT -1;

ELSE

    IF NOT EXISTS (SELECT folder_id FROM nd_folder WHERE org_id = in_org_id AND unique_id = in_unique_id AND folder_id = to_folder_id) THEN

       SET to_folder_id = '^root';

    END IF;



    INSERT INTO nd_file (org_id, unique_id, file_id, folder_id, file_name, size, type, path, create_time) VALUES 

    (in_org_id, in_unique_id, in_file_id, to_folder_id, in_file_name, in_file_size, in_file_type, in_path, UNIX_TIMESTAMP());

    

    UPDATE nd_folder SET folder_size = use_quota + in_file_size WHERE org_id = in_org_id AND unique_id = in_unique_id AND folder_id = '^root';

    

    SELECT 1;

END IF;

END//


# Procedure "sp_nd_delete_file" DDL
DROP PROCEDURE IF EXISTS  `sp_nd_delete_file`//
CREATE PROCEDURE  `sp_nd_delete_file`(in_unique_id varchar(60), in_file_id varchar(60))
    SQL SECURITY INVOKER
BEGIN



DELETE FROM nd_file WHERE unique_id = in_unique_id AND file_id = in_file_id;



UPDATE nd_folder SET folder_size = (SELECT SUM(`size`) FROM nd_file WHERE unique_id = in_unique_id) WHERE unique_id = in_unique_id AND folder_id = '^root';



END//


# Procedure "sp_td_add_group_member" DDL
DROP PROCEDURE IF EXISTS  `sp_td_add_group_member`//
CREATE PROCEDURE  `sp_td_add_group_member`(in in_group_id varchar(36), in in_unique_id varchar(36), in in_contact_id varchar(36))
    SQL SECURITY INVOKER
BEGIN


# 增加标签

INSERT INTO td_contact_group_member(group_id, unique_id, contact_id) VALUES(in_group_id, in_unique_id, in_contact_id);


UPDATE td_contact SET groups = CONCAT(groups, ',', in_group_id) WHERE unique_id = in_unique_id AND contact_id = in_contact_id;


END//


# Procedure "sp_td_add_tudu_label" DDL
DROP PROCEDURE IF EXISTS  `sp_td_add_tudu_label`//
CREATE PROCEDURE  `sp_td_add_tudu_label`(in in_tudu_id varchar(36), in in_unique_id varchar(36), in in_label_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

# 增加图度标签



DECLARE unread int;



# 获取未读的数量，并可根据此数值判读用户关联记录存在

SELECT IF(is_read=0,1,0) INTO unread FROM td_tudu_user WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



#  关联记录存在时才进行更新

IF NOT unread IS NULL THEN



	# 增加标签

	INSERT INTO td_tudu_label(unique_id, label_id, tudu_id) VALUES(in_unique_id, in_label_id, in_tudu_id);



	# 计数自增

	UPDATE td_label SET total_num = total_num + 1, unread_num = unread_num + unread, sync_time = UNIX_TIMESTAMP() 

		WHERE unique_id = in_unique_id AND label_id = in_label_id;



	# 更新标识

	UPDATE td_tudu_user SET labels = CONCAT(labels, ',', in_label_id) WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



END IF;



END//


# Procedure "sp_td_calculate_label" DDL
DROP PROCEDURE IF EXISTS  `sp_td_calculate_label`//
CREATE PROCEDURE  `sp_td_calculate_label`(in in_unique_id varchar(36), in in_label_id varchar(32))
    SQL SECURITY INVOKER
BEGIN



DECLARE total int(11) unsigned;

DECLARE unread int(11) unsigned;



SET total = (SELECT COUNT(tu.unique_id) FROM td_tudu_label tl LEFT JOIN td_tudu_user tu ON tl.unique_id = tu.unique_id AND tl.tudu_id = tu.tudu_id WHERE tl.unique_id = in_unique_id AND tl.label_id = in_label_id),

    unread = (SELECT COUNT(tu.unique_id) FROM td_tudu_label tl LEFT JOIN td_tudu_user tu ON tl.unique_id = tu.unique_id AND tl.tudu_id = tu.tudu_id WHERE tl.unique_id = in_unique_id AND tl.label_id = in_label_id AND is_read = 0);



UPDATE td_label SET total_num = total, unread_num = unread WHERE unique_id = in_unique_id AND label_id = in_label_id;



END//



# Procedure "sp_td_calculate_parents_progress" DDL
DROP PROCEDURE IF EXISTS `sp_td_calculate_parents_progress`//
CREATE PROCEDURE `sp_td_calculate_parents_progress`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 更新父级图度组进度

#

#



DECLARE curr_tudu_id varchar(36);

DECLARE temp_percent int(11);

DECLARE temp_elapsed_time int(11);

DECLARE accepter_eof tinyint(1) DEFAULT 0;

DECLARE accepter_percent int(11);

DECLARE accepter_elapsed_time int(11);

DECLARE temp_unique_id varchar(36);

DECLARE temp_tudu_id varchar(36);



DECLARE accepter_cur CURSOR FOR SELECT unique_id FROM td_tudu_user WHERE tudu_id = curr_tudu_id AND `role` = 'to';

DECLARE CONTINUE HANDLER FOR NOT FOUND SET accepter_eof = 1;



SET curr_tudu_id = in_tudu_id;



REPEAT



## 统计各执行人进度

SET accepter_eof = 0;



OPEN accepter_cur;



REPEAT

      

      FETCH accepter_cur INTO temp_unique_id;

      

      IF EXISTS (SELECT tudu_id FROM td_tudu_group WHERE unique_id = temp_unique_id AND parent_tudu_id = curr_tudu_id) THEN

         

         SELECT AVG(IFNULL(percent, 0)) INTO accepter_percent

         FROM td_tudu AS t LEFT JOIN td_tudu_group AS tg ON t.tudu_id = tg.tudu_id 

         WHERE tg.parent_tudu_id = curr_tudu_id AND t.`status` <= 2 AND tg.unique_id = temp_unique_id;

         

         UPDATE td_tudu_user SET percent = accepter_percent,

         tudu_status = IF(accepter_percent >= 100, 2, IF(accepter_percent > 0, 1, 0))

         WHERE tudu_id = curr_tudu_id AND unique_id = temp_unique_id;

         

      END IF;

      

UNTIL accepter_eof END REPEAT;



CLOSE accepter_cur;



## 统计执行人跟进进度

IF EXISTS (SELECT tudu_id FROM td_tudu_user WHERE tudu_id = curr_tudu_id AND `role` = 'to') THEN

   SELECT AVG(IFNULL(percent, 0)) INTO temp_percent FROM td_tudu_user WHERE tudu_id = curr_tudu_id AND `role` = 'to' AND tudu_status <= 2;

ELSE

    SELECT AVG(IFNULL(percent, 0)) INTO temp_percent FROM td_tudu AS t LEFT JOIN td_tudu_group AS g ON t.tudu_id = g.tudu_id

    WHERE g.parent_tudu_id = curr_tudu_id AND t.`status` <= 2;

END IF;



## 统计子级总

SELECT SUM(elapsed_time) INTO temp_elapsed_time FROM td_tudu AS t 

LEFT JOIN td_tudu_group AS tg ON t.tudu_id = tg.tudu_id 

WHERE tg.parent_tudu_id = curr_tudu_id;



SET temp_elapsed_time = temp_elapsed_time + (

    SELECT IFNULL(SUM(elapsed_time), 0) FROM td_post WHERE tudu_id = curr_tudu_id AND is_log = 1

);



## 更新父级图度

UPDATE td_tudu SET 

percent = temp_percent,

elapsed_time = temp_elapsed_time,

`status`= IF(temp_percent >= 100, 2, IF(temp_percent > 0, 1, 0)),

complete_time = IF(temp_percent >= 100, UNIX_TIMESTAMP(), NULL)

WHERE tudu_id = curr_tudu_id;



## 更新未读状态

call sp_td_mark_all_unread(curr_tudu_id);



## 是否存在上级图度

SELECT parent_tudu_id INTO temp_tudu_id FROM td_tudu_group WHERE tudu_id = curr_tudu_id AND parent_tudu_id <> curr_tudu_id;

IF temp_tudu_id = curr_tudu_id OR temp_tudu_id IS NULL OR temp_tudu_id = '' THEN

   ## 跳出循环

   SET curr_tudu_id = NULL;

ELSE

    ## 标识下一轮

    SET curr_tudu_id = temp_tudu_id;

END IF;



UNTIL curr_tudu_id IS NULL END REPEAT;



END//



# Procedure "sp_td_calculate_tudu_elapsed_time" DDL
DROP PROCEDURE IF EXISTS `sp_td_calculate_tudu_elapsed_time`//
CREATE PROCEDURE `sp_td_calculate_tudu_elapsed_time`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE in_elapsed_time int(11);



START TRANSACTION;



SET in_elapsed_time = (SELECT IFNULL(SUM(elapsed_time), 0) FROM td_post WHERE tudu_id = in_tudu_id AND is_first = 0 AND is_log = 1 AND is_send = 1 FOR UPDATE);

SET in_elapsed_time = in_elapsed_time + (

    SELECT IFNULL(SUM(elapsed_time), 0) FROM td_tudu AS t LEFT JOIN td_tudu_group AS g ON t.tudu_id = g.tudu_id

    WHERE parent_tudu_id = in_tudu_id FOR UPDATE

);



UPDATE td_tudu SET elapsed_time = in_elapsed_time

WHERE tudu_id = in_tudu_id;



COMMIT;



END//



# Procedure "sp_td_calculate_tudu_reply" DDL
DROP PROCEDURE IF EXISTS `sp_td_calculate_tudu_reply`//
CREATE PROCEDURE `sp_td_calculate_tudu_reply`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE reply int(11);

DECLARE log int(11);



SET reply = (SELECT COUNT(*) FROM td_post WHERE tudu_id = in_tudu_id AND is_first = 0),

       log = (SELECT COUNT(*) FROM td_post WHERE tudu_id = in_tudu_id AND is_log = 1 AND is_first = 0);



UPDATE td_tudu SET reply_num = reply, log_num = log WHERE tudu_id = in_tudu_id;



END//



# Procedure "sp_td_clear_board" DDL
DROP PROCEDURE IF EXISTS `sp_td_clear_board`//
CREATE PROCEDURE `sp_td_clear_board`(oid VARCHAR(60), bid VARCHAR(60))
BEGIN

DECLARE unid varchar(36);

DECLARE eof tinyint(1) DEFAULT 0;

DECLARE user_cur CURSOR FOR SELECT DISTINCT u.unique_id FROM td_tudu_user u LEFT JOIN td_tudu t ON t.tudu_id = u.tudu_id WHERE t.board_id = bid;

DECLARE CONTINUE HANDLER FOR NOT FOUND SET eof = 1;



# 重新统计图度关联人员的标签

OPEN user_cur;



REPEAT 



       FETCH user_cur INTO unid;

       UPDATE td_label SET total_num = (

              SELECT COUNT(0) FROM td_tudu_label l 

              LEFT JOIN td_tudu t ON l.tudu_id = t.tudu_id 

              WHERE l.unique_id = unid AND l.label_id = td_label.label_id AND t.board_id <> bid

       ),

       unread_num = (

              SELECT COUNT(0) FROM td_tudu_label l 

              LEFT JOIN td_tudu t ON l.tudu_id = t.tudu_id 

              LEFT JOIN td_tudu_user tu ON l.tudu_id = tu.tudu_id

              WHERE l.unique_id = unid AND l.label_id = td_label.label_id AND t.board_id <> bid AND tu.is_read = 0

       ),

       sync_time = UNIX_TIMESTAMP()

       WHERE unique_id = unid;

       

UNTIL eof END REPEAT;



CLOSE user_cur;



# 删除附件

DELETE FROM td_attachment WHERE file_id IN (SELECT file_id FROM td_attach_post ap LEFT JOIN td_post p ON ap.post_id = p.post_id WHERE p.org_id = oid AND p.board_id = bid);

DELETE FROM td_attach_post WHERE post_id IN (SELECT post_id FROM td_post WHERE org_id = oid AND board_id = bid);



# 删除回复

DELETE FROM td_post WHERE org_id = oid AND board_id = bid;



# 删除投票

DELETE FROM td_vote_option WHERE tudu_id IN (SELECT tudu_id FROM td_tudu WHERE org_id = oid AND board_id = bid);

DELETE FROM td_voter WHERE tudu_id IN (SELECT tudu_id FROM td_tudu WHERE org_id = oid AND board_id = bid);

DELETE FROM td_vote WHERE tudu_id IN (SELECT tudu_id FROM td_tudu WHERE org_id = oid AND board_id = bid);



# 删除版块所有图度

DELETE FROM td_tudu_label WHERE tudu_id IN (SELECT tudu_id FROM td_tudu WHERE org_id = oid AND board_id = bid);

DELETE FROM td_tudu_user WHERE tudu_id IN (SELECT tudu_id FROM td_tudu WHERE org_id = oid AND board_id = bid);

DELETE FROM td_tudu WHERE org_id = oid AND board_id = bid;



# 统计板块信息

UPDATE td_board SET tudu_num = 0, post_num = 0, today_tudu_num = 0, last_post = NULL WHERE org_id = oid AND board_id = bid;



END//



# Procedure "sp_td_delete_contact_group" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_contact_group`//
CREATE PROCEDURE `sp_td_delete_contact_group`(in in_group_id varchar(36), in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 删除群组成员

UPDATE td_contact AS c 

LEFT JOIN td_contact_group_member AS gm ON c.contact_id = gm.contact_id AND c.unique_id = gm.unique_id

SET c.groups = TRIM(TRAILING ',' FROM REPLACE(CONCAT(groups, ','), CONCAT(',', in_group_id, ','), ',')) 

WHERE c.unique_id = in_unique_id AND gm.group_id = in_group_id;



DELETE FROM td_contact_group_member WHERE group_id = in_group_id AND unique_id = in_unique_id;

# 删除群组

DELETE FROM td_contact_group WHERE group_id = in_group_id AND unique_id = in_unique_id;



END//



# Procedure "sp_td_delete_cycle" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_cycle`//
CREATE PROCEDURE `sp_td_delete_cycle`(in in_cycle_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 取消图度关联

#UPDATE td_tudu SET cycle_id = null WHERE cycle_id = in_cycle_id;



# 删除周期

#DELETE FROM td_tudu_cycle WHERE cycle_id = in_cycle_id;

UPDATE td_tudu_cycle SET is_valid = 0 WHERE cycle_id = in_cycle_id;



END//



# Procedure "sp_td_delete_group_member" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_group_member`//
CREATE PROCEDURE `sp_td_delete_group_member`(in in_group_id varchar(36), in in_unique_id varchar(36), in in_contact_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 增加标签

DELETE FROM td_contact_group_member WHERE group_id = in_group_id AND unique_id = in_unique_id AND contact_id = in_contact_id;



# 更新标识

UPDATE td_contact SET groups = TRIM(TRAILING ',' FROM REPLACE(CONCAT(groups, ','), CONCAT(',', in_group_id, ','), ',')) 

WHERE unique_id = in_unique_id AND contact_id = in_contact_id;



END//



# Procedure "sp_td_delete_post" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_post`//
CREATE PROCEDURE `sp_td_delete_post`(in in_tudu_id varchar(36), in in_post_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 删除回复

#

# is_first的记录不通过此方式删除



DECLARE `in_log_num` tinyint(1);

DECLARE `in_is_send` tinyint(1);



# 获取相关数据，由于需要知道是否更新日志类回复，必须select一次，否则可以不需要

SELECT IF(`is_log` = 1, 1, 0), IF(`is_send` = 1, 1, 0) INTO `in_log_num`, `in_is_send` FROM td_post

WHERE tudu_id = in_tudu_id AND post_id = in_post_id AND is_first = 0;



# 数据存在时才进行更新

IF NOT in_log_num IS NULL THEN



# 删除附件

DELETE FROM td_attach_post WHERE tudu_id = in_tudu_id AND post_id = in_post_id;



# 删除回复

DELETE FROM td_post WHERE tudu_id = in_tudu_id AND post_id = in_post_id;



# 删除成功时才更新统计

IF ROW_COUNT() > 0 AND `in_is_send` = 1 THEN



   # 更新版块回复数统计

   UPDATE td_board, td_tudu

   SET td_board.post_num = td_board.post_num - 1,

   reply_num = reply_num - 1,

   log_num = log_num - in_log_num

   WHERE td_board.org_id = td_tudu.org_id AND td_board.board_id = td_tudu.board_id

   AND td_tudu.tudu_id = in_tudu_id;

   

   # 更新任务已耗时统计

   IF `in_log_num` = 1 THEN

      call sp_td_calculate_tudu_elapsed_time(`in_tudu_id`);

   END IF;

END IF;



END IF;



END//



# Procedure "sp_td_delete_tudu" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_tudu`//
CREATE PROCEDURE `sp_td_delete_tudu`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



#

# 删除任务

#

# 1 删除关联用户

#   1.1 批量更新标签统计

#   1.2 批量删除标签

#   1.3 删除任务用户

# 2 删除回复

# 3 删除周期

# 4 删除任务

# 5 更新版块统计

#



# 必须先删除td_tudu_label, td_tudu_user, td_tudu_cycle相关数据（受约束）。

# 成功返回 1，失败返回 0



DECLARE in_is_draft tinyint;

DECLARE in_post_num int;

DECLARE in_org_id varchar(36);

DECLARE in_board_id varchar(36);

DECLARE in_unique_id varchar(36);

DECLARE in_cycle_id varchar(36);

DECLARE in_cycle_num int;





# 出现异常时rollback

#DECLARE EXIT HANDLER FOR SQLEXCEPTION,SQLWARNING BEGIN

#ROLLBACK;

#SELECT 0;

#END;



SELECT is_draft, reply_num, org_id, board_id, cycle_id, cycle_num

INTO in_is_draft, in_post_num, in_org_id, in_board_id, in_cycle_id, in_cycle_num

FROM td_tudu WHERE tudu_id = in_tudu_id;



# 启用事务

START TRANSACTION;

##########################

### 删除任务关联用户







BEGIN















DECLARE in_unread_num tinyint;

DECLARE no_more_user tinyint default 0;







# 定义游标







DECLARE cur_users CURSOR FOR



SELECT unique_id, IF(is_read=0,1,0) FROM td_tudu_user WHERE tudu_id = in_tudu_id;



# 定义记录获取不到时操作



DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_user = 1;



# 打开游标

OPEN cur_users;



# 循环所有的行

REPEAT FETCH cur_users INTO in_unique_id, in_unread_num;



IF NOT in_unique_id IS NULL THEN



   # 批量更新标签统计

   UPDATE td_label, td_tudu_label

   SET td_label.total_num = td_label.total_num - 1, td_label.unread_num = td_label.unread_num - in_unread_num,

   td_label.sync_time = UNIX_TIMESTAMP()

   WHERE td_label.unique_id = td_tudu_label.unique_id

   AND td_label.label_id = td_tudu_label.label_id

   AND td_tudu_label.unique_id = in_unique_id

   AND td_tudu_label.tudu_id = in_tudu_id;





   # 批量删除任务标签

   DELETE FROM td_tudu_label WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



   # 删除任务用户

   DELETE FROM td_tudu_user WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



END IF;



# 循环结束

UNTIL no_more_user END REPEAT;



# 关闭游标

CLOSE cur_users;



END;







### 删除任务关联用户结束



##########################

# 删除附件

DELETE FROM td_attach_post WHERE tudu_id = in_tudu_id;



# 删除回复

DELETE FROM td_post WHERE tudu_id = in_tudu_id;



# 删除投票

DELETE FROM td_voter WHERE tudu_id = in_tudu_id;

DELETE FROM td_vote_option WHERE tudu_id = in_tudu_id;

DELETE FROM td_vote WHERE tudu_id = in_tudu_id;



#删除会议

DELETE FROM td_tudu_meeting WHERE tudu_id = in_tudu_id;



#前置任务

UPDATE td_tudu SET prev_tudu_id = null WHERE prev_tudu_id = in_tudu_id;



# 步骤

DELETE FROM td_tudu_step_user WHERE tudu_id = in_tudu_id;

DELETE FROM td_tudu_step WHERE tudu_id = in_tudu_id;

DELETE FROM td_tudu_flow WHERE tudu_id = in_tudu_id;

# 删除图度

DELETE FROM td_tudu WHERE tudu_id = in_tudu_id;



# 删除周期

IF NOT in_cycle_id IS NULL AND NOT EXISTS (SELECT cycle_id FROM td_tudu WHERE cycle_id = in_cycle_id AND tudu_id <> in_tudu_id) THEN



   DELETE FROM td_tudu_cycle WHERE cycle_id = in_cycle_id AND `count` = in_cycle_num;



END IF;





# 非草稿的记录，才需要更新版块统计

IF in_is_draft = 0 THEN



   UPDATE td_board SET tudu_num = IF(tudu_num - 1 < 0, 0, tudu_num - 1)

   WHERE org_id = in_org_id AND board_id = in_board_id;

END IF;





# 列新版块回复数

IF in_post_num > 0 THEN

   UPDATE td_board SET post_num = IF(post_num - in_post_num < 0, 0, post_num - in_post_num)

   WHERE org_id = in_org_id AND board_id = in_board_id;

END IF;



COMMIT;



SELECT 1;



END//



# Procedure "sp_td_delete_tudu_label" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_tudu_label`//
CREATE PROCEDURE `sp_td_delete_tudu_label`(in in_tudu_id varchar(36), in in_unique_id varchar(36), in in_label_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

# 删除图度标签



DECLARE unread int;



# 获取未读的数量，并可根据此数值判读用户关联记录存在

SET unread = (SELECT IF(is_read=0,1,0) FROM td_tudu_user WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id);



#  关联记录存在时才进行更新

IF NOT unread IS NULL THEN



	# 删除标签

	DELETE FROM td_tudu_label WHERE unique_id = in_unique_id AND label_id = in_label_id AND tudu_id= in_tudu_id;



	# 删除成功时更新统计数

	IF ROW_COUNT() > 0 THEN



		# 计数自减

		UPDATE td_label SET total_num = total_num - 1, unread_num = unread_num - unread, sync_time = UNIX_TIMESTAMP() WHERE unique_id = in_unique_id AND label_id = in_label_id;

	END IF;



	# 更新图度标识

	UPDATE td_tudu_user SET labels = TRIM(TRAILING ',' FROM REPLACE(CONCAT(labels, ','), CONCAT(',', in_label_id, ','), ','))

    WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



END IF;



END//



# Procedure "sp_td_delete_tudu_user" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_tudu_user`//
CREATE PROCEDURE `sp_td_delete_tudu_user`(in in_tudu_id varchar(36), in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 删除任务关联用户

#

# 1.批量更新标签统计

# 2.批量删除标签

# 3.删除任务用户

#



DECLARE unread int;



# 获取未读的数量，并可根据此数值判读用户关联记录存在

SET unread = (SELECT IF(is_read=0,1,0) FROM td_tudu_user WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id);



#  关联记录存在时才进行更新

IF NOT unread IS NULL THEN



   # 批量更新标签统计

   UPDATE td_label, td_tudu_label

   SET td_label.total_num = td_label.total_num - 1, td_label.unread_num = td_label.unread_num - unread

   WHERE td_label.unique_id = td_tudu_label.unique_id

   AND td_label.label_id = td_tudu_label.label_id

   AND td_tudu_label.unique_id = in_unique_id

   AND td_tudu_label.tudu_id = in_tudu_id;



   # 批量删除任务标签

   DELETE FROM td_tudu_label WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;

   

   # 删除任务用户

   DELETE FROM td_tudu_user WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



END IF;



END//



# Procedure "sp_td_delete_user_tudu" DDL
DROP PROCEDURE IF EXISTS `sp_td_delete_user_tudu`//
CREATE PROCEDURE `sp_td_delete_user_tudu`(in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 删除用户的图度数据

#

# 1.批量删除标签

# 2.删除图度用户

# 3.删除用户标签

#





# 批量删除图度标签

DELETE FROM td_tudu_label WHERE unique_id = in_unique_id;



# 删除图度用户

DELETE FROM td_tudu_user WHERE unique_id = in_unique_id;



# 删除用户标签

DELETE FROM td_label WHERE unique_id = in_unique_id;



END//



# Procedure "sp_td_mark_all_unread" DDL
DROP PROCEDURE IF EXISTS `sp_td_mark_all_unread`//
CREATE PROCEDURE `sp_td_mark_all_unread`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE in_unique_id varchar(36);

DECLARE no_more_user tinyint default 0;



# 定义游标

DECLARE cur_users CURSOR FOR

SELECT unique_id FROM td_tudu_user WHERE tudu_id = in_tudu_id AND is_read = 1 FOR UPDATE;



# 定义记录获取不到时操作

DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_user = 1;



# 打开游标

OPEN cur_users;



# 循环所有的行

REPEAT FETCH cur_users INTO in_unique_id;



# 设置为未读状态

UPDATE td_tudu_user SET is_read = 0 WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



# 有更新数据时操作，避免重复设置已读

IF ROW_COUNT() > 0 THEN



   # 批量更新标签统计，设置未读数 + 1

   UPDATE td_label, td_tudu_label

   SET td_label.unread_num = td_label.unread_num + 1, 

  td_label.sync_time = UNIX_TIMESTAMP() 

   WHERE td_label.unique_id = td_tudu_label.unique_id

   AND td_label.label_id = td_tudu_label.label_id

   AND td_tudu_label.unique_id = in_unique_id

   AND td_tudu_label.tudu_id = in_tudu_id;



END IF;



# 循环结束

UNTIL no_more_user END REPEAT;



# 关闭游标

CLOSE cur_users;



END//



# Procedure "sp_td_mark_read" DDL
DROP PROCEDURE IF EXISTS `sp_td_mark_read`//
CREATE PROCEDURE `sp_td_mark_read`(in in_tudu_id varchar(36), in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 设置为未读状态

UPDATE td_tudu_user SET is_read = 1 WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



# 有更新数据时操作，避免重复设置已读

IF ROW_COUNT() > 0 THEN



	# 关联标签未读数 - 1

	UPDATE td_label, td_tudu_label SET td_label.unread_num = td_label.unread_num - 1,

		td_label.sync_time = UNIX_TIMESTAMP() 

		WHERE td_label.unique_id = td_tudu_label.unique_id

		AND td_label.label_id = td_tudu_label.label_id

		AND td_tudu_label.unique_id = in_unique_id

		AND td_tudu_label.tudu_id = in_tudu_id;



END IF;



END//



# Procedure "sp_td_mark_unread" DDL
DROP PROCEDURE IF EXISTS `sp_td_mark_unread`//
CREATE PROCEDURE `sp_td_mark_unread`(in in_tudu_id varchar(36), in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 设置为未读状态

UPDATE td_tudu_user SET is_read = 0 WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



# 有更新数据时操作，避免重复设置已读

IF ROW_COUNT() > 0 THEN



	# 关联标签未读数 + 1

	UPDATE td_label, td_tudu_label SET td_label.unread_num = td_label.unread_num + 1,

		td_label.sync_time = UNIX_TIMESTAMP() 

		WHERE td_label.unique_id = td_tudu_label.unique_id

		AND td_label.label_id = td_tudu_label.label_id

		AND td_tudu_label.unique_id = in_unique_id

		AND td_tudu_label.tudu_id = in_tudu_id;



END IF;



END//



# Procedure "sp_td_move_tudu" DDL
DROP PROCEDURE IF EXISTS `sp_td_move_tudu`//
CREATE PROCEDURE `sp_td_move_tudu`(in_tudu_id varchar(36), in_board_id varchar(36), in_class_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE tudu_reply_num int(11);

DECLARE from_board_id varchar(36);

DECLARE in_org_id varchar(60);



# 查询图度信息

SELECT board_id, reply_num, org_id INTO from_board_id, tudu_reply_num, in_org_id FROM td_tudu WHERE tudu_id = in_tudu_id;



# 更新图度信息

UPDATE td_tudu SET board_id = in_board_id, class_id = in_class_id WHERE tudu_id = in_tudu_id;



# 更新版块图度数，回复数

UPDATE td_board SET

tudu_num = tudu_num + 1,

post_num = post_num + tudu_reply_num

WHERE board_id = in_board_id AND org_id = in_org_id;



# 更新原版块图度数，回复数

UPDATE td_board SET

tudu_num = tudu_num - 1,

post_num = post_num - tudu_reply_num

WHERE board_id = from_board_id AND org_id = in_org_id;



END//



# Procedure "sp_td_send_post" DDL
DROP PROCEDURE IF EXISTS `sp_td_send_post`//
CREATE PROCEDURE `sp_td_send_post`(in in_tudu_id varchar(36), in in_post_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE in_org_id varchar(60);

DECLARE in_board_id varchar(36);

DECLARE `in_is_send` tinyint;

DECLARE in_log_num int;

DECLARE in_poster varchar(15) character set utf8;

DECLARE in_post_time int;

DECLARE in_board_privacy tinyint(1);

DECLARE in_tudu_privacy tinyint(1);



# 获取回复相关信息

SELECT p.org_id, p.board_id, p.is_send, IF(p.is_log = 1, 1, 0), p.poster, p.create_time, b.privacy, t.privacy 

INTO in_org_id, in_board_id, in_is_send, in_log_num, in_poster, in_post_time, in_board_privacy, in_tudu_privacy

FROM td_post AS p

LEFT JOIN td_tudu AS t ON p.tudu_id = t.tudu_id AND p.org_id = t.org_id

LEFT JOIN td_board AS b ON p.board_id = b.board_id AND p.org_id = b.org_id

WHERE p.tudu_id = in_tudu_id AND p.post_id = in_post_id AND p.is_first = 0;



# 未发送过

IF `in_is_send` <> 1 THEN



    # 设置为已发送

    UPDATE td_post SET `is_send` = 1 WHERE tudu_id = in_tudu_id AND post_id = in_post_id;



    /*

    # 更新图度统计及最后回复信息

    UPDATE td_tudu

    SET reply_num = reply_num + 1, log_num = log_num + in_log_num, last_post_time = in_post_time, last_poster = in_poster

    WHERE tudu_id = in_tudu_id;

    

    # 更新版块统计及最后回复信息

    UPDATE td_board, td_tudu

    SET td_board.post_num = td_board.post_num + 1,

        td_board.last_post = CONCAT(td_tudu.tudu_id, char(9), td_tudu.subject, char(9), td_tudu.last_post_time, char(9), td_tudu.last_poster)

    WHERE td_board.org_id = td_tudu.org_id

    AND td_board.board_id = td_tudu.board_id

    AND td_tudu.tudu_id = in_tudu_id;

    */

    

    

    # 更新版块统计，图度统计及最后回复信息

    UPDATE td_board, td_tudu

    SET td_board.post_num = td_board.post_num + 1,

        td_board.last_post = IF (in_board_privacy <> 0 OR in_tudu_privacy <> 0, td_board.last_post , CONCAT(in_tudu_id, char(9), td_tudu.subject, char(9), in_post_time, char(9), in_poster)),

        td_tudu.reply_num = td_tudu.reply_num + 1,

        td_tudu.log_num = td_tudu.log_num + in_log_num,

        td_tudu.last_post_time = in_post_time,

        td_tudu.last_poster = in_poster

    WHERE td_board.org_id = td_tudu.org_id

    AND td_board.board_id = td_tudu.board_id

    AND td_tudu.tudu_id = in_tudu_id;

    

END IF;



END//



# Procedure "sp_td_send_tudu" DDL
DROP PROCEDURE IF EXISTS  `sp_td_send_tudu`//
CREATE PROCEDURE  `sp_td_send_tudu`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 发送图度

#

# 1.设置为非草稿状态

# 2.更新版块统计

#

# 创建图度跟发送图度是两个过程，只有发送过的图度，才会影响到版块的统计数

# 及最后回复信息

DECLARE in_tudu_privacy tinyint(1);

DECLARE in_board_privacy tinyint(1);

DECLARE in_org_id varchar(36);

DECLARE in_board_id varchar(36);

DECLARE in_last_post varchar(200) charset utf8; # max >= 36 + 1 + 50 + 1 + 11 + 1 + 50



UPDATE td_tudu SET is_draft = 0 WHERE tudu_id = in_tudu_id;

IF ROW_COUNT() > 0 THEN



    SELECT t.privacy, b.privacy, t.org_id, t.board_id, CONCAT(t.tudu_id, char(9), t.subject, char(9), t.last_post_time, char(9), t.last_poster)

    INTO in_tudu_privacy, in_board_privacy, in_org_id, in_board_id, in_last_post

    FROM td_tudu AS t

    LEFT JOIN td_board as b ON b.org_id = t.org_id AND b.board_id = t.board_id

    WHERE t.tudu_id = in_tudu_id;



    IF (in_tudu_privacy = 1 OR in_board_privacy = 1) THEN

        UPDATE td_board SET tudu_num = tudu_num + 1 WHERE org_id = in_org_id AND board_id = in_board_id;

    ELSE

        UPDATE td_board SET tudu_num = tudu_num + 1, last_post = in_last_post WHERE org_id = in_org_id AND board_id = in_board_id;

    END IF;


END IF;



END//



# Procedure "sp_td_update_tudu_labels" DDL
DROP PROCEDURE IF EXISTS  `sp_td_update_tudu_labels`//
CREATE PROCEDURE  `sp_td_update_tudu_labels`(in in_tudu_id varchar(36), in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN

#

# 更新图度的标签标识

#



UPDATE td_tudu_user SET labels = (SELECT GROUP_CONCAT(label_id) FROM td_tudu_label WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id)

WHERE unique_id = in_unique_id AND tudu_id =  in_tudu_id;



END//



# Procedure "sp_td_update_tudu_progress" DDL
DROP PROCEDURE IF EXISTS  `sp_td_update_tudu_progress`//
CREATE PROCEDURE  `sp_td_update_tudu_progress`(in in_tudu_id varchar(36), in_unique_id varchar(36), in_percent tinyint(3))
    SQL SECURITY INVOKER
BEGIN

#

# 更新图度进度

#

# 1.更新当前执行人进度

# 2.更新主任务进度

#



DECLARE total_percent tinyint(3);



IF (in_unique_id IS NOT NULL AND in_percent IS NOT NULL) THEN



   # 更新当前执行人进度及状态

    UPDATE td_tudu_user SET 

    percent = IFNULL(in_percent, IFNULL(percent, 0)), 

    tudu_status = IF(in_percent >= 100, 2, IF(in_percent > 0, 1, 0)), 

    complete_time = IF(in_percent >= 100, UNIX_TIMESTAMP(), NULL)

    WHERE tudu_id = in_tudu_id AND unique_id = in_unique_id;



END IF;



# 统计当前任务总进度

SELECT AVG(percent) INTO total_percent FROM td_tudu_user WHERE tudu_id = in_tudu_id AND role = 'to' AND (tudu_status IS NULL OR tudu_status < 3);



# 更新主任务进度及状态

UPDATE td_tudu SET 

percent = total_percent, 

`status` = IF(total_percent >= 100, 2, IF(total_percent > 0, 1, 0)),

complete_time = IF(total_percent >= 100, UNIX_TIMESTAMP(), NULL)

WHERE tudu_id = in_tudu_id;



# 返回当前任务总进度

SELECT total_percent AS percent;



END//




-- Procedure "sp_td_mark_all_unread" DDL
DROP PROCEDURE IF EXISTS `sp_td_mark_all_unread`//
CREATE PROCEDURE `sp_td_mark_all_unread`(in in_tudu_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



DECLARE in_unique_id varchar(36);

DECLARE no_more_user tinyint default 0;



# 定义游标

DECLARE cur_users CURSOR FOR

SELECT unique_id FROM td_tudu_user WHERE tudu_id = in_tudu_id AND is_read = 1 FOR UPDATE;



# 定义记录获取不到时操作

DECLARE CONTINUE HANDLER FOR NOT FOUND SET no_more_user = 1;


# 打开游标

OPEN cur_users;


# 循环所有的行

REPEAT FETCH cur_users INTO in_unique_id;


# 设置为未读状态

UPDATE td_tudu_user SET is_read = 0 WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;


# 有更新数据时操作，避免重复设置已读

IF ROW_COUNT() > 0 THEN



   # 批量更新标签统计，设置未读数 + 1

   UPDATE td_label, td_tudu_label

   SET td_label.unread_num = td_label.unread_num + 1, 

  td_label.sync_time = UNIX_TIMESTAMP() 

   WHERE td_label.unique_id = td_tudu_label.unique_id

   AND td_label.label_id = td_tudu_label.label_id

   AND td_tudu_label.unique_id = in_unique_id

   AND td_tudu_label.tudu_id = in_tudu_id;



END IF;

# 循环结束

UNTIL no_more_user END REPEAT;

# 关闭游标

CLOSE cur_users;


END//


-- Procedure "sp_td_mark_read" DDL
DROP PROCEDURE IF EXISTS `sp_td_mark_read`//
CREATE PROCEDURE `sp_td_mark_read`(in in_tudu_id varchar(36), in in_unique_id varchar(36))
    SQL SECURITY INVOKER
BEGIN



# 设置为未读状态

UPDATE td_tudu_user SET is_read = 1 WHERE unique_id = in_unique_id AND tudu_id = in_tudu_id;



# 有更新数据时操作，避免重复设置已读

IF ROW_COUNT() > 0 THEN



	# 关联标签未读数 - 1

	UPDATE td_label, td_tudu_label SET td_label.unread_num = td_label.unread_num - 1,

		td_label.sync_time = UNIX_TIMESTAMP() 

		WHERE td_label.unique_id = td_tudu_label.unique_id

		AND td_label.label_id = td_tudu_label.label_id

		AND td_tudu_label.unique_id = in_unique_id

		AND td_tudu_label.tudu_id = in_tudu_id;



END IF;



END//



DELIMITER ;

