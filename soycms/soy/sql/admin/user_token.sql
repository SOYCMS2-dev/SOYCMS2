drop table soycms_user_token;
create table soycms_user_token(
	id integer primary key,
	user_id integer not null,
	token varchar(255) not null,
	config text,
	time_limit integer not null
);