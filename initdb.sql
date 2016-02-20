drop database if exists Gibil;

create database Gibil;

use Gibil;

drop table if exists Event;
create table Event
	/*(cid int auto_increment not null,*/
	(account int not null,
	alarmzone char(1) not null,
	alarmstate char(1) not null,
	supervisoryzone char(1) not null,
	supervisorystate char(1) not null,
	troublezone char(1) not null,
	troublestate char(1) not null,
	powerzone char(1) not null,
	powerstate char(1) not null,
	timestamp varchar(64) not null,
    	message varchar(64),
	primary key (account));

commit;
