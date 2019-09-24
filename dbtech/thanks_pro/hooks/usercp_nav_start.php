<?php
if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/thanks/includes/class_template.php');
}

// Create our nav template
$dbtech_thanks_nav = vB_Template::create('dbtech_thanks_usercp_nav_link');

//if (!$vbulletin->userinfo['dbtech_vbshout_banned'] AND $vbulletin->options['dbtech_vbshout_active'])
//{
	// We're not banned and shoutbox is active
	$cells[] = 'dbtech_thanks_options';
//}
?>