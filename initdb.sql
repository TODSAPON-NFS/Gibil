drop database if exists Gibil;

create database Gibil;

use Gibil;

drop table if exists Event;
create table Event
	/*(cid int auto_increment not null,*/
	(category int not null,
	zone int not null,
	panel int not null,
	timestamp varchar(64) not null,
    status varchar(64),
	primary key (zone, panel));

commit;
