drop database if exists Gibil;

create database Gibil;

use Gibil;

drop table if exists Event;
create table Event
	/*(cid int auto_increment not null,*/
	(account int not null,
	alarmstate char(1) not null,
	alarmtimestamp varchar(32) not null,
	alarmwirestate char(1) not null,
	alarmwiretimestamp varchar(32) not null,
	supervisorystate char(1) not null,
	supervisorytimestamp varchar(32) not null,
	supervisorywirestate char(1) not null,
	supervisorywiretimestamp varchar(32) not null,
	troublestate char(1) not null,
	troubletimestamp varchar(32) not null,
	troublewirestate char(1) not null,
	troublewiretimestamp varchar(32) not null,
	powerstate char(1) not null,
	powertimestamp varchar(32) not null,
	powerwirestate char(1) not null,
	powerwiretimestamp varchar(32) not null,
	timestamp varchar(32) not null,
    	message varchar(32),
	primary key (account));

commit;
