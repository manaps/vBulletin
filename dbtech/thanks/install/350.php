<?php
if (self::$db_alter->fetch_table_info('dbtech_thanks_button'))
{
	self::$db_alter->add_field(array(
		'name'       => 'ispositive',
		'type'       => 'tinyint',
		'length'     => '1',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '1'
	));
	self::report('Altered Table', 'dbtech_thanks_button');

	self::$db->query_write("UPDATE `" . TABLE_PREFIX . "dbtech_thanks_button` SET `ispositive` = '0' WHERE `varname` = 'dislikes'");
	self::report('Updated Buttons', 'dbtech_thanks_entrycache');
}

if (class_exists('THANKS_CACHE'))
{
	THANKS_CACHE::build('button');
}