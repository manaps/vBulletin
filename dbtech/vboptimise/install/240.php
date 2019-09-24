<?php
// New Tables
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "vboptimise_cdn`
	(
		`cdn_file` VARCHAR( 255 ) NOT NULL DEFAULT '',
		PRIMARY KEY ( `cdn_file` )
	) ENGINE=MyISAM CHARSET=latin1;
");
self::report('Created Table', 'vboptimise_cdn');
?>