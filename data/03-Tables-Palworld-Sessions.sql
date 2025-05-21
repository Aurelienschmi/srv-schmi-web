CREATE TABLE `palworld_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player` varchar(64) NOT NULL,
  `join_time` datetime NOT NULL,
  `leave_time` datetime DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


