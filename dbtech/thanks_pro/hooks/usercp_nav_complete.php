<?php
if (method_exists($dbtech_thanks_nav, 'register'))
{
	// Register important variables
	$dbtech_thanks_nav->register('navclass', 		$navclass);
	$dbtech_thanks_nav->register('template_hook', 	$template_hook);
	
	$template_hook['usercp_navbar_bottom'] .= $dbtech_thanks_nav->render();
}
?>