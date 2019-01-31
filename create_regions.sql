CREATE TABLE `regions` (
  `name` varchar(50) NOT NULL,
  `min_lat` double(18,14) NOT NULL,
  `min_lon` double(18,14) NOT NULL,
  `max_lat` double(18,14) NOT NULL,
  `max_lon` double(18,14) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

