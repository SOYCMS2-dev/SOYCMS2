create table soycms_site_entry(
	id integer primary key,
	title varchar,
	name varchar,
	description varchar,
	directory integer,
	sections TEXT,
	create_date date,
	update_date date
);