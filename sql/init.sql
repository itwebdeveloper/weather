CREATE DATABASE weather;
USE weather;

CREATE TABLE `results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `latitude` decimal(11,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `summary` varchar(50) NOT NULL,
  `temperature` decimal(3,2) NOT NULL,
  `units` varchar(5) NOT NULL,
  `icon` enum('clear-day','clear-night','rain','snow','sleet','wind','fog','cloudy','partly-cloudy-day','or partly-cloudy-night') NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;