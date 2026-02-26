create table departments (
    id       int unsigned not null primary key auto_increment,
    name     varchar(32)  not null unique,
    title    varchar(64),
    dn       varchar(255) not null unique
);
insert into departments(name, title, dn) values
('Clerk'         , 'City Clerk'                           , 'OU=City Clerk,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('CFRD'          , 'Community and Family Resources'       , 'OU=Community and Family Resources,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Controller'    , 'Controller'                           , 'OU=Controller,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Council'       , 'Common Council'                       , 'OU=Council Office,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('ESD'           , 'Economic and Sustainable Development' , 'OU=Economic & Sustainable Development,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Engineering'   , 'Engineering'                          , 'OU=Engineering,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('HAND'          , 'Housing and Neighborhood Development' , 'OU=HAND,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('HR'            , 'Human Resources'                      , 'OU=Human Resources,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('ITS'           , 'Information & Technology Services'    , 'OU=ITS,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Legal'         , 'Legal'                                , 'OU=Legal,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('OOTM'          , 'Office Of The Mayor'                  , 'OU=Office of the Mayor,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Parks'         , 'Parks & Recreation Department'        , 'OU=Parks and Recreation,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Planning'      , 'Planning and Transportation'          , 'OU=Planning and Transportation,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Animal Shelter', NULL                                   ,     'OU=Animal Shelter,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Facilities'    , NULL                                   ,         'OU=Facilities,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Fleet'         , NULL                                   ,  'OU=Fleet Maintenance,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Parking'       , 'Parking Services'                     ,   'OU=Parking Services,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Sanitation'    , NULL                                   ,         'OU=Sanitation,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Street'        , 'Street Division'                      , 'OU=Street and Traffic,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Public Works'  , 'Public Works'                         ,                       'OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Fire'          , 'Fire Department'                      , 'OU=Fire,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Police'        , 'Police Department'                    , 'OU=Police,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'),
('Utilities'     , 'Utilities'                            , 'OU=Utilities,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov')
;

create table files (
	id               int unsigned not null primary key auto_increment,
	internalFilename varchar(50)  not null,
	filename         varchar(128) not null,
	mime_type        varchar(128) not null,
	md5              varchar(32)  not null,
	uploaded         datetime     not null default CURRENT_TIMESTAMP,
	username         varchar(32)  not null,
	department       varchar(32)  not null,
	origin           varchar(32)  not null,
	origin_id        int unsigned,
	foreign key (department) references departments(name)
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
