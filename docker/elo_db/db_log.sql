/* teams queries */
alter table users modify code varchar(3) not null;
alter table users modify name varchar(45) not null;
alter table users
	add team varchar(45) not null;
