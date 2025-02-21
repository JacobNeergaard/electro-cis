CREATE TABLE `footprint` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(40) NOT NULL,
	`data` text NOT NULL,
	`exclude` tinyint(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `item` (
	`item` int(6) unsigned zerofill NOT NULL,
	`description` varchar(60) NOT NULL,
	`value` varchar(20) NOT NULL,
	`symbol_id` int(10) unsigned DEFAULT NULL,
	`footprint_id` int(10) unsigned DEFAULT NULL,
	`maxtemp` int(3) unsigned NOT NULL DEFAULT 0,
	`datasheet` longblob DEFAULT NULL,
	`manufacturer` varchar(30) NOT NULL DEFAULT '',
	`partnumber` varchar(30) NOT NULL DEFAULT '',
	`mouser` varchar(20) NOT NULL DEFAULT '',
	PRIMARY KEY (`item`),
	FOREIGN KEY (`symbol_id`) REFERENCES `symbol` (`id`),
	FOREIGN KEY (`footprint_id`) REFERENCES `footprint` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `structure` (
	`group` varchar(6) NOT NULL,
	`name` varchar(30) NOT NULL,
	`description_template` varchar(30) NOT NULL,
	PRIMARY KEY (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `structure_e24` (
	`number` int(3) unsigned NOT NULL,
	`value` varchar(10) NOT NULL,
	PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `symbol` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(30) NOT NULL,
	`description` varchar(100) NOT NULL,
	`data` text NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
