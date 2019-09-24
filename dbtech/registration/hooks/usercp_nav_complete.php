<?php
if ($vbulletin->options['dbtech_registration_invites'])
{
	if (method_exists($dbtech_registration_nav, 'register'))
	{
		// Register important variables
		$dbtech_registration_nav->register('navclass', 		$navclass);
		$dbtech_registration_nav->register('template_hook', $template_hook);
		$dbtech_registration_nav->register('invites_left',	($vbulletin->userinfo['permissions']['dbtech_registration_invites'] == '0' ? $vbphrase['dbtech_registration_unlimited'] : $vbulletin->userinfo['permissions']['dbtech_registration_invites'] - (int)$vbulletin->userinfo['dbtech_registration_invites_sent']));
		
		$template_hook['usercp_navbar_bottom'] .= $dbtech_registration_nav->render();
	}
}
?>