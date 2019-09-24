<?php
// Switch to instance_x instead of x_instance table names??
// im thinkin yes but later. add an s at the end (e.g dbtech_registration_instance_actions)?

// also change type to "varname"? for phrasing...
// Add types to fields. varname is the title and type is the actual type (referrer, password, email, etc)

/*
* Create Tables
*
*/
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_instance` (
		`instanceid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`title` VARCHAR( 255 ) NOT NULL ,
		`priority` INT( 10 ) UNSIGNED NOT NULL DEFAULT 10 ,
		PRIMARY KEY (`instanceid`)
	)  # permissions on who's allowed to see details on this instance?
");
self::report('Created Table', 'dbtech_registration_instance');

//right now the types are in their infancy: location, invited or not, preverified or not, and eventually ill figure out how to let users sending an invite to choose an instance to go with the invite
/*so the only way they could change instance is if they had a tab open, got invited in another which would put them in a separate instance
*/
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_criteria` (
		`criteriaid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`title` VARCHAR( 255 ) NOT NULL ,
		`active` tinyint(1) NOT NULL DEFAULT '1',
		`type` VARCHAR( 20 ) NOT NULL ,
		`operator` VARCHAR( 5 ) NOT NULL ,
		`value` VARCHAR( 50 ) NOT NULL ,
		PRIMARY KEY (`criteriaid`)
	)
");
self::report('Created Table', 'dbtech_registration_criteria');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_action` (
		`actionid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`title` VARCHAR( 255 ) NOT NULL ,
		`type` VARCHAR( 20 ) NOT NULL ,
		`value` MEDIUMTEXT NULL , # Serialized array in some cases.
		`options` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0', # Options bitfield
		`active` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`actionid`)
	)
");
self::report('Created Table', 'dbtech_registration_action');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_instance_criteria` (
		`instanceid` INT( 10 ) UNSIGNED NOT NULL ,
		`criteriaid` INT( 10 ) UNSIGNED NOT NULL ,
		`required` ENUM('1', '0') NOT NULL DEFAULT '0',
		`active` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`instanceid`, `criteriaid`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_criteria');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_instance_section` (
		`instanceid` INT( 10 ) UNSIGNED NOT NULL ,
		`sectionid` INT( 10 ) UNSIGNED NOT NULL ,
		`displayorder` INT( 10 ) UNSIGNED NOT NULL ,
		`active` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`instanceid`, `sectionid`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_section');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_instance_field` (
		`instanceid` INT( 10 ) UNSIGNED NOT NULL ,
		`fieldid` INT( 10 ) UNSIGNED NOT NULL ,
		`sectionid` INT( 10 ) UNSIGNED NOT NULL ,
		`displayorder` INT( 10 ) UNSIGNED NOT NULL ,
		`active` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`instanceid`, `fieldid`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_field');

self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_instance_action` (
		`instanceid` INT( 10 ) UNSIGNED NOT NULL ,
		`actionid` INT( 10 ) UNSIGNED NOT NULL ,
		`active` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`instanceid`, `actionid`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_action');


self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_redirect_whitelist` (
		`redirectid` INT( 10 ) UNSIGNED NOT NULL ,
		`ipaddress` CHAR( 15 ) NOT NULL ,
		PRIMARY KEY (`redirectid`, `ipaddress`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_action');

// Snapshots of what happened each registration (expand on this later. i think its a genius idea. possibly be branched from "registered" detail in tracking table)
self::$db->query_write("
	CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX  . "dbtech_registration_snapshot` (
		`snapshotid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`userid` INT( 10 ) UNSIGNED NOT NULL , # 0 = failed
		`criterias_met` mediumtext NOT NULL , # Array of information for each criteria met
		`actions_met` mediumtext NOT NULL , # Array of information for each action met
		`dateline` INT( 10 ) NOT NULL,
		PRIMARY KEY (`snapshotid`)
	)
");
self::report('Created Table', 'dbtech_registration_instance_action');

/*
* Cache Existing Tables
*
*/

// ini fields array
$fields = array();

// Fetch fields
$results = self::$db->query_read_slave("
	SELECT * FROM `" . TABLE_PREFIX  . "dbtech_registration_field`
	ORDER BY fieldid ASC
");
while ($result = self::$db->fetch_array($results))
{
	// Set varname
	$fields[(int)$result['fieldid']] = $result;
}

// Fetch profilefields
$results = self::$db->query_read_slave("
	SELECT 
		field.*, profilefield.* 
	FROM `" . TABLE_PREFIX  . "dbtech_registration_field_profilefield` AS profilefield
	LEFT JOIN `" . TABLE_PREFIX  . "dbtech_registration_field` AS field USING(fieldid)
	ORDER BY field.fieldid ASC
");
while ($result = self::$db->fetch_array($results))
{
	// Set varname
	$fields[(int)$result['fieldid']] = $result;	
	$fields[(int)$result['fieldid']]['title'] = 'field' . $result['profilefieldid'] . '_title';
}

// Fetch nonprofilefields
$results = self::$db->query_read_slave("
	SELECT 
		field.*, nonprofilefield.* 
	FROM `" . TABLE_PREFIX  . "dbtech_registration_field_nonprofilefield` AS nonprofilefield
	LEFT JOIN `" . TABLE_PREFIX  . "dbtech_registration_field` AS field USING(fieldid)
	ORDER BY field.fieldid ASC
");
while ($result = self::$db->fetch_array($results))
{
	// Set varname
	$fields[(int)$result['fieldid']] = $result;		
	$fields[(int)$result['fieldid']]['title'] = $result['varname'];
}

/*
* Alter Existing Tables
*
*/
if (self::$db_alter->fetch_table_info('dbtech_registration_field'))
{
	self::$db_alter->add_field(array(
		'name'		=> 'title',
		'type'		=> 'varchar',
		'length'	=> '255',
		'null'		=> false	// True = NULL, false = NOT NULL
	));

	self::$db_alter->add_field(array(
		'name'		=> 'type',
		'type'		=> 'varchar',
		'length'	=> '255',
		'null'		=> false	// True = NULL, false = NOT NULL
	));
	
	self::$db_alter->add_field(array(
		'name'		=> 'active',
		'type'		=> 'tinyint',
		'length'	=> '1',
		'default'	=> '1',
		'null'		=> false	// True = NULL, false = NOT NULL
	));

	self::$db_alter->drop_field(array(
		'sectionid',
		'displayorder'
	));
	self::report('Altered Table', 'dbtech_registration_field');
}

if (self::$db_alter->fetch_table_info('dbtech_registration_section'))
{
	self::$db_alter->add_field(array(
		'name'		=> 'active',
		'type'		=> 'tinyint',
		'length'	=> '1',
		'default'	=> '1',
		'null'		=> false	// True = NULL, false = NOT NULL
	));
	
	self::$db_alter->drop_field(array(
		'displayorder'
	));
	self::report('Altered Table', 'dbtech_registration_section');
}

if (self::$db_alter->fetch_table_info('dbtech_registration_redirect'))
{
	self::$db_alter->add_field(array(
		'name'		=> 'options',
		'type'		=> 'int',
		'length'	=> '10',
		'default'	=> '0',
		'null'		=> false	// True = NULL, false = NOT NULL
	));

	// Whitelisted IP's
	self::$db_alter->add_field(array(
		'name'		=> 'whitelist',
		'type'		=> 'mediumtext',
		'default'	=> null,
		'null'		=> true	// True = NULL, false = NOT NULL
	));
	
	self::report('Altered Table', 'dbtech_registration_redirect');
}

if (self::$db_alter->fetch_table_info('dbtech_registration_redirect_log'))
{
	self::$db_alter->add_field(array(
		'name'		=> 'options',
		'type'		=> 'int',
		'length'	=> '10',
		'default'	=> '0',
		'null'		=> false	// True = NULL, false = NOT NULL
	));

	// oops
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_registration_redirect_log
		CHANGE dateline dateline INT(10) NOT NULL
	");

	self::report('Altered Table', 'dbtech_registration_redirect_log');
}

if (self::$db_alter->fetch_table_info('dbtech_registration_tracking'))
{
	// oops
	self::$db->query_write("ALTER TABLE " . TABLE_PREFIX . "dbtech_registration_tracking
		CHANGE dateline dateline INT(10) NOT NULL
	");

	self::report('Altered Table', 'dbtech_registration_redirect_log');
}


/*
* Populate Tables
*
*/
foreach ($fields AS $fieldid => $field)
{
	// Update fields [is $vbphrase available?)
	self::$db->query_write("
		UPDATE `" . TABLE_PREFIX  . "dbtech_registration_field` SET
			title = " . self::$db->sql_prepare(ucwords(str_replace('_', ' ', $fields[$fieldid]['title']))) . ",
			type = " . self::$db->sql_prepare($fields[$fieldid]['title']) . "
		WHERE fieldid = " . $fieldid
	);

	// For default instance to field relationship
	$fields[$fieldid] = '(1, ' . $fieldid . ', ' . $field['sectionid'] . ', ' . $field['displayorder'] . ')';
}

// Redo this to install as much default data as you can. default sections, field relationships, etc.
self::$db->query_write("
	REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_instance`
		(instanceid, title, priority)
	VALUES
		(1, 'Default Instance', 1)
");
self::report('Populated Table', 'dbtech_registration_instance');

self::$db->query_write("
	REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_instance_section`
		(instanceid, sectionid, displayorder)
	VALUES
		(1, 1, 10)
");
self::report('Populated Table', 'dbtech_registration_instance_sections');

self::$db->query_write("
	REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_instance_field`
		(instanceid, fieldid, sectionid, displayorder)
	VALUES
		" . implode(',', $fields)
);
self::report('Populated Table', 'dbtech_registration_instance_fields');

/*
* Drop Existing Tables
*
*/
foreach (array(
	'field_profilefield',
	'field_nonprofilefield'
) AS $table)
{
	self::$db->query_write("DROP TABLE IF EXISTS `" . TABLE_PREFIX  . "dbtech_registration_{$table}`");
}
self::report('Dropped Table', 'dbtech_registration_' . $table);