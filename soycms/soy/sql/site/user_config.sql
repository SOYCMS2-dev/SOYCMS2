drop table soycms_user_data;
create table soycms_user_data(
	id integer primary key,
	class_name varchar unique,
	object_data text
);