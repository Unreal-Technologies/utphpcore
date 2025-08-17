drop database if exists `utphpcore`;
create database `utphpcore`;
use `utphpcore`;

create table `options`
(
    `key` varchar(32) not null,
    `value` varchar(32) not null
)Engine=InnoDB;

insert into `options`(`key`, `value`)
values
('seed', md5(current_timestamp()));

delimiter ||

create function aes_key() returns blob
begin
    set @seed = null;
    select `value` into @seed from `options` where `key` = 'seed';

    return unhex(sha2(@seed, 512));
end||

create function user_password(password varchar(32)) returns blob
begin
    return aes_encrypt(md5(password), aes_key());
end||

delimiter ;

create table `instance`
(
    `id` int(11) not null auto_increment,
    `name` varchar(32) not null,
    primary key(`id`)
)Engine=InnoDB;

create table `user`
(
    `id` int(11) not null auto_increment,
    `username` varchar(32) not null,
    `password` blob not null,
    primary key(`id`)
)Engine=InnoDB;

create table `user-instance`
(
    `id` int(11) not null auto_increment,
    `user-id` int(11) not null,
    `instance-id` int(11) null,
    primary key(`id`),
    foreign key(`user-id`) references `user`(`id`) on delete cascade,
    foreign key(`instance-id`) references `instance`(`id`) on delete cascade
)Engine=InnoDB;

set @seed = null;
select `value` into @seed from `options` where `key` = 'seed';

insert into `user`(`username`, `password`)
values
('admin', user_password(@seed));

set @adminId = last_insert_id();

insert into `user-instance`(`user-id`,`instance-id`)
values(@adminId, null);

create table `route`
(
    `id` int(11) not null auto_increment,
    `instance-id` int(11) null,
    `default` enum('true', 'false') not null default('false'),
    `method` enum('get', 'post', 'modal') not null default('get'),
    `match` varchar(128) not null,
    `type` enum('file', 'function') not null default('file'),
    `mode` enum('page', 'data', 'modal') not null default('page'),
    `target` varchar(128) not null,
    `auth` enum('true', 'false') not null,
    primary key(`id`),
    foreign key(`instance-id`) references `instance`(`id`) on delete cascade
)Engine=InnoDB;

insert into `route`(`default`, `method`, `match`, `target`, `type`, `mode`, `auth`)
values
('true', 'get', 'index', 'index.php', 'file', 'page', 'false'),
('false', 'modal', 'login', 'login.php', 'file', 'modal', 'false'),
('false', 'post', 'login', 'login.php', 'file', 'data', 'false');