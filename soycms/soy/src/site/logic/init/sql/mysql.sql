drop table soycms_site_entry;
create table soycms_site_entry(
	id integer primary key auto_increment,
	parent_entry_id integer,
	title varchar(255),
	title_section varchar(255),
	uri varchar(255),
	entry_publish integer default -1,
	entry_status varchar(255) not null,
	directory integer,
	display_order integer,
	content TEXT,
	sections TEXT,
	create_date integer,
	update_date integer,
	last_update_date integer,
	open_from integer,
	open_until integer,
	memo varchar(255),
	author_id integer,
	author varchar(255),
	allow_comment INTEGER default 0,
	allow_trackback INTEGER default 0,
	feed_entry INTEGER default 1,
	unique(directory,uri)
) TYPE=InnoDB;
create index entry_udate on soycms_site_entry(update_date desc);
create index entry_cdate on soycms_site_entry(create_date desc);
create index entry_order on soycms_site_entry(display_order desc);
alter table soycms_site_entry add foreign key parent_entry_id references soycms_site_entry(id);
alter table soycms_site_entry add directory_uri varchar(255); 


drop table soycms_entry_attribute;
create table soycms_entry_attribute(
	id integer primary key auto_increment,
	entry_id integer not null,
	class_name varchar(255),
	object_data text,
	unique(entry_id,class_name)
) TYPE=InnoDB;



drop table soycms_entry_comment;
create table soycms_entry_comment(
	id integer primary key auto_increment,
	entry_id integer references soycms_site_entry(id),
	title varchar(255),
	author varchar(255),
	mail varchar(255),
	url varchar(255),
	content varchar(255),
	submit_date integer not null,
	comment_status integer not null,
	comment_attributes varchar(255),
	comment_order integer not null default 0
) TYPE=InnoDB;


drop table soycms_entry_trackback;
create table soycms_entry_trackback(
	id integer primary key auto_increment,
	entry_id integer references soycms_site_entry(id),
	title varchar(255),
	blog_name varchar(255),
	url varchar(255),
	excerpt varchar(255),
	submit_date integer not null,
	trackback_status integer not null,
	trackback_attributes varchar(255)
) TYPE=InnoDB;



drop table soycms_site_label;
create table soycms_site_label(
	id integer primary key auto_increment,
	name varchar(255),
	alias varchar(255),
	label_config TEXT,
	directory integer,
	label_type integer default 0,
	display_order integer default 0,
	unique(name,directory)
) TYPE=InnoDB;

drop table soycms_site_entry_label;
create table soycms_site_entry_label(
	entry_id integer references soycms_site_entry(id),
	label_id integer references soycms_site_label(id),
	display_order integer default 2147483647,
	unique(entry_id,label_id)
) TYPE=InnoDB;


drop table soycms_site_tag;
create table soycms_site_tag(
	entry_id integer references soycms_site_entry(id),
	tag_text varchar(255) not null,
	hash_text varchar(12) not null,
	display_order integer not null,
	unique(entry_id,hash_text)
) TYPE=InnoDB;
create index tag_hash on soycms_site_tag(hash_text);

drop table soycms_site_page;
create table soycms_site_page(
	id integer primary key auto_increment,
	parent integer,
	name varchar(255),
	uri varchar(255) unique,
	page_type varchar(255),
	page_config LONGTEXT,
	template varchar(255),
	is_deleted integer default 0,
	create_date integer not null,
	update_date integer not null
) TYPE=InnoDB;

drop table soycms_admin_role;
create table soycms_admin_role(
	id integer primary key auto_increment,
	admin_id integer,
	role varchar(255),
	unique(admin_id,role)
) TYPE=InnoDB;

drop table soycms_data_sets;
create table soycms_data_sets(
	id integer primary key auto_increment,
	class_name varchar(255) unique,
	object_data LONGTEXT
) TYPE=InnoDB;

drop table soycms_group;
create table soycms_group(
	id integer primary key auto_increment,
	group_id varchar(14) unique,
	group_name varchar(50),
	group_description varchar(255),
	group_type varchar(14) default "default",
	group_config LONGTEXT,
	create_date integer not null default 0,
	update_date integer not null default 0
) TYPE=InnoDB;

drop table soycms_admin_group;
create table soycms_admin_group(
	id integer primary key auto_increment,
	admin_id integer not null,
	group_id varchar(14) not null,
	unique(admin_id,group_id)
) TYPE=InnoDB;

drop table soycms_group_permission;
create table soycms_group_permission(
	id integer primary key auto_increment,
	page_id integer not null,
	group_id varchar(14) not null,
	readable integer not null default 1,
	writable integer not null default 1,
	unique(page_id,group_id)
) TYPE=InnoDB;


drop table soycms_site_object_field;
create table soycms_site_object_field(
	id integer primary key auto_increment,
	field_id varchar(32) not null,
	field_type varchar(12) not null,
	field_index integer not null default 0,
	object_id integer not null,
	object varchar(12) not null,
	object_text LONGTEXT,
	object_value LONGTEXT,
	unique(field_id,object_id,field_index,object)
) TYPE=InnoDB;


-- 2.0.8 20110710

drop table if exists soycms_site_object;
create table soycms_site_object(
	id integer primary key,
	object_title VARHCAR(512),
	object_content TEXT,
	object_type VARHCAR(12) not null,
	owner_id INTEGER not null,
	directory INTEGER not null,
	create_date INTEGER not null
) TYPE=InnoDB;


drop table if exists soycms_page_attribute;
create table soycms_page_attribute(
	id integer primary key auto_increment,
	page_id integer not null,
	class_name varchar(255),
	object_data text,
	unique(page_id,class_name)
) TYPE=InnoDB;