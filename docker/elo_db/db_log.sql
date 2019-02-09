/* teams queries */
alter table users modify code varchar(3) not null;
alter table users modify name varchar(45) not null;
alter table users
	add team varchar(45) not null;

/* password in db */
CREATE TABLE `password` (
  `password` varchar(40) NOT NULL,
  PRIMARY KEY (`password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `password` (`password`) VALUES
('cd798c453817754e7fab67c9572fc9d3182a2ee2');
