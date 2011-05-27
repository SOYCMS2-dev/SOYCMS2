drop table soycms_siteaccess;
create table soycms_siteaccess(
	id integer primary key,
	url varchar,
	refer varchar,
	user_agent varchar,
	ip_address varchar,
	keyword varchar,
	page_id integer,
	entry_id integer,
	submit_date integer
);