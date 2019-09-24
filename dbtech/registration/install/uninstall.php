<?php

// Revert
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->drop_field('dbtech_registrationadminperms');
	self::report('Reverted Table', 'administrator');
}

// Clean up
self::$db->query_write("DELETE FROM `" . TABLE_PREFIX . "datastore` WHERE `title` LIKE 'dbtech_registration_%'");
self::report('Reverted Table', 'datastore');

// Revert
if (self::$db_alter->fetch_table_info('usergroup'))
{
	self::$db_alter->drop_field('dbtech_registrationermissions');
	self::$db_alter->drop_field('dbtech_registration_invites');
	self::report('Reverted Table', 'usergroup');
}

// Revert
if (self::$db_alter->fetch_table_info('user'))
{
	self::$db_alter->drop_field('dbtech_registration_invites_sent_total');
	self::$db_alter->drop_field('dbtech_registration_invites_sent');
	self::report('Reverted Table', 'user');
}

// Revert
if (self::$db_alter->fetch_table_info('session'))
{
	self::$db_alter->drop_field('dbtech_registration_firstactivity');
	self::$db_alter->drop_field('dbtech_registration_pageviews');
	self::$db_alter->drop_field('dbtech_registration_threadviews');
	self::$db_alter->drop_field('dbtech_registration_tracking');
	self::report('Reverted Table', 'session');
}

// Drop
$tables = array(
	'field',
	'section',
	'invite',
	'email',
	'redirect',
	'redirect_log',
	'tracking',
	'instance',
	'criteria',
	'action',
	'instance_criteria',
	'instance_action',
	'instance_field',
	'instance_action',
	'redirect_whitelist',
	'snapshot',
	'instance_section',
	'registration_session'
);
foreach ($tables as $table)
{
	self::$db->query_write("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "dbtech_registration_{$table}`");
	self::report('Deleted Table', 'dbtech_registration_' . $table);
}
?>