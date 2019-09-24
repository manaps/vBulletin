<?php

foreach (array(
	'usergroup',
) AS $varname)
{
	self::$db->query_write("
		REPLACE INTO `" . TABLE_PREFIX  . "dbtech_registration_field`
			(title, type)
		VALUES
			('" . ucfirst($varname) . "', '$varname')
	");
}
self::report('Populated Table', 'dbtech_registration_field');

define('CP_REDIRECT', 'registration.php?do=repaircache');
define('DISABLE_PRODUCT_REDIRECT', true);
?>