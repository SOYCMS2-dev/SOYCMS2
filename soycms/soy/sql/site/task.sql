drop table soycms_task;
create table soycms_task(
	id integer primary key,
	owner integer not null,
	parent_id integer,
	root_id integer,
	title varchar,
	description varchar,
	task_order integer default 0,
	task_depth integer default 0,
	task_start integer,
	task_end inetger,
	refer_url varchar,
	task_status integer,
	config varchar,
	submit_date integer,
	close_date integer
);