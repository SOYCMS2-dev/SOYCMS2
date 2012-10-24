drop table plus_user_user;
create table plus_user_user(
	id INTEGER primary key AUTO_INCREMENT,
	name VARCHAR(400) default 'no name',
	login_id VARCHAR(127) unique not null,
	password VARCHAR(512) not null, 
	mail_address VARCHAR(400),
	profile_url VARCHAR(512),
	profile_image_url VARCHAR(512),
	user_status INTEGER default 0,
	group_ids TEXT,
	language VARCHAR(5) default 'ja',
	configure LONGTEXT,
	create_date INTEGER,
	update_date INTEGER
) engine=InnoDB;
-- 20110712 alter table plus_user_user add profile_url varchar(512);
-- 20110712 alter table plus_user_user add profile_image_url varchar(512);
-- 20110720 alter table plus_user_user add language varchar(5) default "ja";


drop table plus_user_user_config;
CREATE TABLE plus_user_user_config(
	user_id INTEGER,
	class_name VARCHAR(255),
	object_data LONGTEXT,
	UNIQUE(user_id,class_name)
) engine=InnoDB;

drop table plus_user_user_profile;
CREATE TABLE plus_user_user_profile(
	id INTEGER primary key AUTO_INCREMENT,
	user_id INTEGER,
	profile_item VARCHAR(255),
	profile_value LONGTEXT,
	UNIQUE(user_id,profile_item)
) engine=InnoDB;

drop table plus_user_user_token;
create table plus_user_user_token(
	id integer primary key AUTO_INCREMENT,
	user_id integer not null,
	token varchar(255) not null,
	config LONGTEXT,
	time_limit integer not null
) engine=InnoDB;


drop table if exists plus_user_join;
create table plus_user_join(
	id integer primary key AUTO_INCREMENT,
	user_id integer not null,
	join_key VARCHAR(255),
	join_value VARCHAR(255),
	join_type VARCHAR(255),
	create_date INTEGER,
	join_config TEXT,
	unique(user_id,join_key,join_value)
) engine=InnoDB;




-- group

drop table plus_user_group;
CREATE TABLE plus_user_group(
	id INTEGER primary key AUTO_INCREMENT,
	group_id VARCHAR(255) UNIQUE,
	group_name VARCHAR(255),
	parent INTEGER,
	configure LONGTEXT
) engine=InnoDB;

drop table plus_user_user_group;
CREATE TABLE plus_user_user_group(
	user_id INTEGER,
	group_id INTEGER,
	UNIQUE(user_id, group_id)
) engine=InnoDB;


-- notification
drop table plus_user_notification;
CREATE TABLE plus_user_notification(
	id INTEGER primary key AUTO_INCREMENT,
	user_id INTEGER not null,
	notification_link TEXT,
	notification_status INTEGER not null,
	notification_mail_status INTEGER not null,
	notification_type VARCHAR(128),
	notitication_title TEXT,
	notification_text TEXT,
	notification_attributes TEXT,
	submit_date INTEGER not null
) engine=InnoDB;

--activity
drop table plus_user_activity;
CREATE TABLE plus_user_activity(
	id INTEGER primary key AUTO_INCREMENT,
	user_id INTEGER not null,
	activity_link TEXT,
	activity_type INTEGER not null,
	activity_title VARCHAR(128),
	activity_text TEXT,
	activity_attributes TEXT,
	activity_key1 INTEGER,
	activity_key2 INTEGER,
	activity_key3 INTEGER,
	submit_date INTEGER not null
) engine=InnoDB;
drop index plus_user_activity_key on plus_user_activity;
CREATE INDEX plus_user_activity_key ON plus_user_activity (activity_key1,activity_key2,activity_key3);
