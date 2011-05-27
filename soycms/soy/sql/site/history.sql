drop table soycms_history;
create table soycms_history(
	id integer primary key,
	admin_id integer,
	object_type varchar,
	object_id integer not null,
	type_text varchar,
	comment varchar,
	name varchar,
	content varchar,
	config varchar,
	submit_time integer not null,
	submit_date integer not null
);