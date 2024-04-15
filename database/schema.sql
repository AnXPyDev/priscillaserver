drop table if exists `message`;
drop table if exists `response`;
drop table if exists `request`;
drop table if exists `room_event`;
drop table if exists `client_event`;
drop table if exists `client`;
drop table if exists `room`;
drop table if exists `config`;
drop table if exists `session`;
drop table if exists `user`;

create table `user` (
    `id` int primary key auto_increment,
    `username` varchar(16) unique not null,
    `displayname` varchar(32) not null,

    `password_hash` varchar(64) not null,
    `password_salt` varchar(32) not null
);

create table `session` (
    `id` varchar(32) primary key,
    `user_id` int not null,

    foreign key (`user_id`) references `user`(`id`)
);

create table `room` (
    `id` int primary key auto_increment,
    `name` varchar(32) not null,
    `join_code` varchar(16) not null,
    `watch_code` varchar(16) not null,
    `config` json not null,
    
    `owner_id` int not null,
    foreign key (`owner_id`) references `user`(`id`)
);

create table `config` (
    `name` varchar(32) primary key,
    `data` json not null
);

create table `client` (
    `id` int primary key auto_increment,
    `ip_adress` varchar(32) not null,
    `name` varchar(32) not null,
    `secret` varchar(32) not null,
    `state` json default '{}',

    `room_id` int not null,
    foreign key (`room_id`) references `room`(`id`)
);

create table `client_event` (
    `id` int primary key auto_increment,
    `created` datetime not null default now(),
    `data` json not null,
    
    `client_id` int not null,
    `room_id` int not null,

    foreign key (`client_id`) references `client`(`id`),
    foreign key (`room_id`) references `room`(`id`)

);

create table `room_event` (
    `id` int primary key auto_increment,
    `created` datetime not null default now(),
    `data` json not null,

    `client_id` int,
    `room_id` int not null,

    foreign key (`client_id`) references `client`(`id`),
    foreign key (`room_id`) references `room`(`id`)
);

create table `request` (
    `id` int primary key auto_increment,
    `data` json not null,

    `client_id` int not null ,
    foreign key (`client_id`) references `client`(`id`)
);

create table `response` (
    `request_id` int primary key,
    `data` json not null,

    foreign key (`request_id`) references `request`(`id`)
);

create table `message` (
    `id` int primary key auto_increment,
    `data` json not null,

    `client_id` int not null,

    foreign key (`client_id`) references `client`(`id`)
)