CREATE DATABASE weather;
USE weather;

CREATE TABLE `results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `latitude` decimal(7,4) NOT NULL,
  `longitude` decimal(7,4) NOT NULL,
  `summary` varchar(50) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `units` enum('auto','ca','uk2','us','si') NOT NULL,
  `icon` enum('clear-day','clear-night','rain','snow','sleet','wind','fog','cloudy','partly-cloudy-day','partly-cloudy-night') NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `latitude` (`latitude`,`longitude`,`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;