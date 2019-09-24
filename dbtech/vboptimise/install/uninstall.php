<?php
// Revert
if (self::$db_alter->fetch_table_info('administrator'))
{
	self::$db_alter->drop_field('dbtech_vboptimiseadminperms');
	self::report('Reverted Table', 'administrator');
}

// Deleted Tables

// Drop
$tables = array(
	'vboptimise',
	'vboptimise_cdn',
);
foreach ($tables as $table)
{
	self::$db->query_write("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "{$table}`");
	self::report('Deleted Table', $table);
}
?>