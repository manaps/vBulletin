<?php
if (self::$db_alter->fetch_table_info('dbtech_downloads_download'))
{
	self::$db_alter->drop_field('dbtech_thanks_disabledbuttons');	
	self::report('Altered Table', 'dbtech_downloads_download');
}