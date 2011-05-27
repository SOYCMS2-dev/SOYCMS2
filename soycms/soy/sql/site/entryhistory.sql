drop table soycms_entry_history;
create table soycms_entry_history(
	id integer primary key,
	entry_id integer not null,
	admin_id integer not null,
	type_text varchar,
	comment varchar,
	title varchar,
	sections varchar,
	entry_status varchar,
	submit_time integer not null,
	submit_date integer not null
);