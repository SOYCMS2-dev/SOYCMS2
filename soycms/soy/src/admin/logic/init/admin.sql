
CREATE TABLE soycms_site (
  id INTEGER primary key,
  site_id VARCHAR unique,
  site_type VARCHAR,
  site_name VARCHAR,
  site_url VARCHAR not null,
  site_path VARCHAR not null,
  site_config VARCHAR
);

CREATE TABLE soycms_user (
  id INTEGER,
  user_id VARCHAR NULL unique,
  user_password VARCHAR NULL,
  user_unique_id VARCHAR NOT NULL unique,
  user_name VARCHAR,
  user_mail_address VARCHAR,
  user_level integer default 0,
  user_config VARCHAR,
  PRIMARY KEY(id)
);

CREATE TABLE soycms_role (
  role_user_id INTEGER,
  role_site_id VARCHAR,
  role_app_id VARCHAR,
  role_user_level INTEGER,
  role_roles VARCHAR,
  unique(role_user_id,role_site_id,role_app_id)
);

CREATE TABLE admin_data_sets(
  id INTEGER primary key,
  class_name VARCHAR unique,
  object_data VARCHAR
);