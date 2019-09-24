<?php
if (!isset($cache))
{
	$cache = array();
}

switch (THIS_SCRIPT)
{
	case 'register':
		$cache[] = 'dbtech_registration';
		if ($vbulletin->options['dbtech_registration_use_custom_page'])
		{
			$cache = array_merge($cache, array(
				'dbtech_registration_register',
				'dbtech_registration_custompage_email',
				'dbtech_registration_custompage_password',
				'dbtech_registration_custompage_receive_email',
				'dbtech_registration_custompage_referrer',
				'dbtech_registration_custompage_section',
				'dbtech_registration_custompage_username',
			));
		}
		$cache[] = 'dbtech_registration_verify_email';
		break;
}


if ($vbulletin->userinfo['permissions']['dbtech_registrationpermissions'] & $vbulletin->bf_ugp_dbtech_registrationpermissions['canview'])
{
	// Global templates
	$cache[] = 'dbtech_registration_navbar_link';
	
	if ($vbulletin->options['dbtech_registration_integration'] & 1 OR $vbulletin->options['dbtech_registration_integration'] & 2)
	{
		$cache[] = 'dbtech_registration_quicklinks_link';
	}
}

if (in_array('usercp_nav_folderbit', (array)$cache) OR in_array('usercp_nav_folderbit', (array)$globaltemplates))
{
	// UserCP templates
	$cache[] = 'dbtech_registration_usercp_nav_link';

}
/*
if (intval($vbulletin->versionnumber) == 3)
{
	$cache[] = 'dbtech_vbanalytics.css';
	
	$globaltemplates = array_merge($globaltemplates, $cache);
}
*/
?>