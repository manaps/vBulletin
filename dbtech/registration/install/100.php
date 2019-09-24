<?php

// Add the administrator field
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registrationadminperms',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'administrator');
}

if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registrationpermissions',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_invites',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '3'
	));
	self::report('Altered Table', 'usergroup');
}

if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_invites_sent_total',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_invites_sent',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
	self::report('Altered Table', 'user');
}


if (self::$db_alter->fetch_table_info('session'))
{

	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_firstactivity',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false	// True = NULL, false = NOT NULL
	));
	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_pageviews',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '1'
	));
	
	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_threadviews',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));

	self::$db_alter->add_field(array(
		'name'       => 'dbtech_registration_tracking',
		'type'       => 'VARCHAR',
		'length'     => '255',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => 'a:0:{}'
	));
	self::report('Altered Table', 'session');
}

/*
* Create Tables
*
*/
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_field` (
		`fieldid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`sectionid` INT( 10 ) UNSIGNED NULL DEFAULT '1',
		`displayorder` INT( 10 ) UNSIGNED NULL DEFAULT '1',
		PRIMARY KEY ( `fieldid` )
	) ENGINE = MYISAM ;
");
self::report('Created Table', 'dbtech_registration_field');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_field_profilefield` (
		`fieldid` INT( 10 ) UNSIGNED NOT NULL,
		`profilefieldid` INT( 10 ) UNSIGNED NOT NULL,
		PRIMARY KEY ( `fieldid` ),
		UNIQUE ( `profilefieldid` )
	) ENGINE = MYISAM ;
");
self::report('Created Table', 'dbtech_registration_field_profilefield');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_field_nonprofilefield` (
		`fieldid` INT( 10 ) UNSIGNED NOT NULL,
		`varname` VARCHAR( 255 ) NOT NULL,
		PRIMARY KEY ( `fieldid` ),
		UNIQUE ( `varname` )
	) ENGINE = MYISAM CHARSET=latin1;
");
self::report('Created Table', 'dbtech_registration_field_nonprofilefield');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_section` (
		`sectionid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` VARCHAR( 250 ) NOT NULL ,
		`description` MEDIUMTEXT NULL DEFAULT NULL ,
		`displayorder` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0',
		PRIMARY KEY ( `sectionid` )
	) ENGINE = MYISAM ;
");
self::report('Created Table', 'dbtech_registration_section');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_invite` (
		`inviteid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`userid` INT( 10 ) UNSIGNED NULL,
		`email` VARCHAR ( 255 ) NOT NULL,
		`dateline` INT( 10 ) UNSIGNED NOT NULL,
		PRIMARY KEY ( `inviteid` ),
		UNIQUE KEY ( `email` ),
		KEY ( `dateline` )
	) ENGINE = MYISAM CHARSET=latin1;
");
self::report('Created Table', 'dbtech_registration_invites_log');

// for invites and verify email before registering as they both follow the same process
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_email` (
		`email` VARCHAR ( 255 ) NOT NULL,
		`verified` ENUM('0', '1') DEFAULT '0',
		`verifyhash` CHAR( 32 )  NOT NULL DEFAULT '' ,
		PRIMARY KEY ( `email` ),
		KEY ( `verified` )
	) ENGINE = MYISAM CHARSET=latin1;
");
self::report('Created Table', 'dbtech_registration_email');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_redirect` (
		`redirectid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` VARCHAR( 250 ) NOT NULL ,
		`type` ENUM('pageviews', 'threadviews', 'firstactivity'),
		`amount` INT ( 10 ) , # e.g: 300 seconds on the site
		`persistent` ENUM('0', '1') DEFAULT '0',
		`active` ENUM('0', '1') DEFAULT '0',
		PRIMARY KEY ( `redirectid` )
	) ENGINE = MYISAM ;
");
self::report('Created Table', 'dbtech_registration_redirect');

// holds information pertaining to each redirect
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_redirect_log` (
		`redirect_logid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`ipaddress` CHAR ( 15 ) NOT NULL,
		`hashed_ipaddress` CHAR ( 32 ) NOT NULL,
		`type` ENUM('pageviews', 'threadviews', 'firstactivity'),
		`amount` INT ( 10 ) , # e.g: 300 seconds on the site
		`persistent` ENUM('0', '1') DEFAULT '0',
		`dateline` CHAR( 32 )  NOT NULL DEFAULT '' ,
		PRIMARY KEY ( `redirect_logid` ),
		KEY ( `hashed_ipaddress` )
	) ENGINE = INNODB ;
");
self::report('Created Table', 'dbtech_registration_redirect_logs');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_tracking` (
		`trackingid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
		`ipaddress` CHAR ( 15 ) NOT NULL,
		`hashed_ipaddress` CHAR ( 32 ) NOT NULL,
		`email` VARCHAR ( 255 ) NULL DEFAULT NULL,
		`reason` VARCHAR ( 255 ) DEFAULT '0', # Banned Email, Form Faster than Possible
		`data` VARCHAR ( 255 ) NOT NULL, # Serialized array of extra data such as being redirected here from a redirect
		`dateline` CHAR( 32 )  NOT NULL DEFAULT '' ,
		PRIMARY KEY ( `trackingid` ),
		KEY ( `dateline` ),
		KEY ( `hashed_ipaddress` )
	) ENGINE = MYISAM ;
");
self::report('Created Table', 'dbtech_registration_tracking');

/*
* Populate Tables
*
*/
self::$db->query_write("
	REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_section`
		(sectionid, title, description, displayorder)
	VALUES
		(1, 'Required Information', 'Required Fields', 1)
");
self::report('Populated Table', 'dbtech_registration_section');

// fetch existing user profile fields
$fields_q = self::$db->query_read_slave("
	SELECT profilefieldid
	FROM `" . TABLE_PREFIX  . "profilefield`
	WHERE required != 0
");

if (self::$db->num_rows($fields_q))
{
	while ($fields_r = self::$db->fetch_array($fields_q))
	{
		self::$db->query_write("
			INSERT INTO `" . TABLE_PREFIX  . "dbtech_registration_field`
			VALUES()
		");

		self::$db->query_write("
			REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_field_profilefield`
				(fieldid, profilefieldid) 
			VALUES
				(" . self::$db->insert_id() . ", " . (int)$fields_r['profilefieldid'] . ")
		");
	}
	self::report('Populated Table', 'dbtech_registration_field');
	self::report('Populated Table', 'dbtech_registration_field_profilefield');
}

// set up displayorder for them
$i = 0;

foreach (array(
	'username',
	'password',
	'email',
	'coppa',
	'human_verification',
	'birthday',
	'referrer',
	'avatar',
	'receive_email',
	'timezone',
) AS $varname)
{
	$i += 5;

	self::$db->query_write("
		REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_field`
			(displayorder)
		VALUES
			($i)
	");

	self::$db->query_write("
		REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_field_nonprofilefield`
			(fieldid, varname)
		VALUES
			(" . self::$db->insert_id() . ", " . self::$db->sql_prepare($varname) . ")
	");
}
self::report('Populated Table', 'dbtech_registration_field');
self::report('Populated Table', 'dbtech_registration_field_nonprofilefield');

/*
* Datastore
*
*/
self::$db->query_write("
	REPLACE INTO `" . TABLE_PREFIX . "datastore`
		(title, data, unserialize)
	VALUES
		(
			'dbtech_registration_total',
			" . self::$db->sql_prepare(serialize(array(
				'invites'		=> array(
					'sent'			=> 0,
					'verified'		=> 0
				),
				'verify_emails'	=> array(
					'sent'			=> 0,
					'verified'		=> 0
				)
			))) . ",
			1
		)
");

define('CP_REDIRECT', 'registration.php?do=repaircache');
define('DISABLE_PRODUCT_REDIRECT', true);
?>