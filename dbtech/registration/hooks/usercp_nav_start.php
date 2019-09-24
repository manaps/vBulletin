<?php
if ($vbulletin->options['dbtech_registration_invites'])
{
	if (!class_exists('vB_Template'))
	{
		// Ensure we have this
		require_once(DIR . '/dbtech/registration/includes/class_template.php');
	}

	// Create our nav template
	$dbtech_registration_nav = vB_Template::create('dbtech_registration_usercp_nav_link');

	// We're not banned and invites is active
	$cells[] = 'dbtech_registration_invite';
}
?>