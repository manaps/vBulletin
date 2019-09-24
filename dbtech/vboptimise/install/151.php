<?php
// New Tables
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "vboptimise`
	(
		`statid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`queries` int(11) NOT NULL DEFAULT '0',
		`dateline` varchar(30) DEFAULT NULL,
		PRIMARY KEY (`statid`),
		UNIQUE KEY `dateline` (`dateline`)
	) ENGINE=MyISAM;
");
self::report('Created Table', 'vboptimise');

// Populated tables
self::$db->query_write("
	REPLACE INTO " . TABLE_PREFIX . "vboptimise
		(statid, dateline)
	VALUES (
		'1',
		'Installation'
	)
");
self::report('Populated Table', 'vboptimise');

?>