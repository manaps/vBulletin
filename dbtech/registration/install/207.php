<?php
/*
* Add Field
*
*/
if (self::$db_alter->fetch_table_info('dbtech_registration_instance'))
{
	self::$db_alter->add_field(array(
		'name'       => 'daily_max',
		'type'       => 'int',
		'length'     => '10',
		'attributes' => 'unsigned',
		'null'       => false,	// True = NULL, false = NOT NULL
		'default'    => '0'
	));
}
self::report('Altered Table', 'dbtech_registration_instance');