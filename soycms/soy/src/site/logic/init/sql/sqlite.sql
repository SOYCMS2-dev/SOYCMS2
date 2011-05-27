drop table soycms_site_entry;
create table soycms_site_entry(
	id integer primary key,
	parent_entry_id integer references soycms_site_entry(id),
	title varchar,
	title_section varchar,
	uri varchar,
	entry_publish integer default -1,
	entry_status varchar not null default 'draft',
	directory integer,
	display_order integer,
	content TEXT,
	sections TEXT,
	create_date date,
	update_date date,
	last_update_date date,
	open_from integer,
	open_until integer,
	memo varchar,
	author_id integer,
	author varchar,
	allow_comment inetegr not null default 0,
	allow_trackback inetegr not null default 0,
	feed_entry INTEGER default 1,
	unique(directory,uri)
);
create index entry_udate on soycms_site_entry(update_date desc);
create index entry_cdate on soycms_site_entry(create_date desc);
create index entry_order on soycms_site_entry(display_order desc);



drop table soycms_entry_attribute;
create table soycms_entry_attribute(
	id integer primary key,
	entry_id integer not null,
	class_name varchar,
	object_data text,
	unique(entry_id,class_name)
);



drop table soycms_entry_comment;
create table soycms_entry_comment(
	id integer primary key,
	entry_id integer references soycms_site_entry(id),
	title varchar,
	author varchar,
	mail varchar,
	url varchar,
	content varchar,
	submit_date integer not null,
	comment_status integer not null,
	comment_attributes varchar,
	comment_order integer not null default 0
);


drop table soycms_entry_trackback;
create table soycms_entry_trackback(
	id integer primary key,
	entry_id integer references soycms_site_entry(id),
	title varchar,
	blog_name varchar,
	url varchar,
	excerpt varchar,
	submit_date integer not null,
	trackback_status integer not null,
	trackback_attributes varchar
);



drop table soycms_site_label;
create table soycms_site_label(
	id integer primary key,
	name varchar(255),
	alias varchar(255),
	label_config TEXT,
	directory integer,
	label_type integer default 0,
	display_order integer default 0,
	unique(name,directory)
);

drop table soycms_site_entry_label;
create table soycms_site_entry_label(
	entry_id integer references soycms_site_entry(id),
	label_id integer references soycms_site_label(id),
	display_order integer default 2147483647,
	unique(entry_id,label_id)
);


drop table soycms_site_tag;
create table soycms_site_tag(
	entry_id integer references soycms_site_entry(id),
	tag_text varchar not null,
	hash_text varchar not null,
	display_order integer not null,
	unique(entry_id,hash_text)
);
create index tag_hash on soycms_site_tag(hash_text);

#drop table soycms_site_page;
create table soycms_site_page(
	id integer primary key,
	parent integer,
	name varchar,
	uri varchar unique,
	page_type varchar,
	page_config TEXT,
	template varchar,
	is_deleted integer default 0,
	create_date integer not null default 0,
	update_date integer not null default 0
);

drop table soycms_admin_role;
create table soycms_admin_role(
	id integer primary key,
	admin_id integer,
	role varchar,
	unique(admin_id,role)
);

drop table soycms_data_sets;
create table soycms_data_sets(
	id integer primary key,
	class_name varchar unique,
	object_data text
);


drop table soycms_group;
create table soycms_group(
	id integer primary key,
	group_id varchar unique,
	group_name varchar,
	group_description varchar,
	group_type varchar default "default",
	group_config text,
	create_date integer not null default 0,
	update_date integer not null default 0
);

drop table soycms_admin_group;
create table soycms_admin_group(
	id integer primary key,
	admin_id integer not null,
	group_id varchar not null,
	unique(admin_id,group_id)
);

drop table soycms_group_permission;
create table soycms_group_permission(
	id integer primary key,
	page_id integer not null,
	group_id varchar not null,
	readable integer not null default 1,
	writable integer not null default 1,
	unique(page_id,group_id)
);



drop tablesoycms_site_object_field;
create table soycms_site_object_field(
	id integer primary key,
	field_id varchar not null,
	field_type varchar not null,
	field_index integer,
	object_id integer not null,
	object varchar not null,
	object_text varchar,
	object_value ineteger,
	unique(field_id,object_id,field_index,object)
);