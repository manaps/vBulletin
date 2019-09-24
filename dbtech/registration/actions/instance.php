<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Jon Dickinson AKA Pandemikk				  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/
/*should this even be a feature?
if (empty($_REQUEST['action']))
{
	$_REQUEST['action'] = 'view';
}

if ($_REQUEST['action'] == 'view')
{
	$vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
	
	if (!$instance = REGISTRATION::$cache['instance'][$vbulletin->GPC['instanceid']])
	{
		// Couldn't find the instance
		eval(standard_error(fetch_error('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_instance'], $vbulletin->GPC['instanceid'])));
	}
}

// Include the page template
$page_templater = vB_Template::create('dbtech_registration_instance');
	$page_templater->register('instance', REGISTRATION::$cache['instance'][$vbulletin->GPC['instanceid']]);
$codebits .= $templater->render();	*/
?>