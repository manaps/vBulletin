<?php

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_session` (
		`sessionhash` char( 32 ) NOT NULL DEFAULT '',
		`pageviews` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`threadviews` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		`firstactivity` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY (`sessionhash`)
	)
");
self::report('Created Table', 'dbtech_registration_session');