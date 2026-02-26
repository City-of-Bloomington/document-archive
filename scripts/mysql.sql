create table files (
	id               int unsigned not null primary key auto_increment,
	internalFilename varchar(50)  not null,
	filename         varchar(128) not null,
	md5              varchar(32)  not null,
	uploaded         datetime     not null default CURRENT_TIMESTAMP,
	mime_type        varchar(128) not null,
	origin           varchar(32)  not null
);


create table onboard (
	id               int unsigned not null primary key auto_increment,
	file_id          int unsigned not null,
	committee        varchar(128) not null,
	type             varchar(64)  not null,
	date             datetime,
	title            varchar(128),
	description      text,
	foreign key (file_id) references files(id)
);

create table drupal (
	id               int unsigned not null primary key auto_increment,
	file_id          int unsigned not null,
);
