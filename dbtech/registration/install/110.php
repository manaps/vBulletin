<?php

/*
* Modify Table
*
*/
self::$db->query_write("
	UPDATE " . TABLE_PREFIX . "dbtech_registration_tracking AS tracking
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = REPLACE(REPLACE(data, 'a:1:{s:4:\"user\";i:', ''), ';}', ''))
	SET tracking.email = user.email
	WHERE tracking.email IS NULL AND data LIKE '%user%'
");
self::report('Altered Table', 'dbtech_registration_tracking');

/*
* Drop Fields
*
*/
if (self::$db_alter->fetch_table_info('dbtech_registration_redirect_log'))
{
	self::$db_alter->drop_field('hashed_ipaddress');
	self::report('Altered Table', 'dbtech_registration_redirect_log');
}

if (self::$db_alter->fetch_table_info('dbtech_registration_tracking'))
{
	self::$db_alter->drop_field('hashed_ipaddress');
	self::report('Altered Table', 'dbtech_registration_tracking');
}

/*
* vBActivity Integration
*
*/
if (class_exists('VBACTIVITY'))
{
	$doaddquiz = VBACTIVITY::add_type('invite_registered', 'Invites Registered', 'dbtech_registration', '/dbtech/registration/vbactivity_type/invite.php');
}
self::report('Added', 'Invites - vBActivity Integration');