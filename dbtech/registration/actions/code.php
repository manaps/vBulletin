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

if ($_REQUEST['action'] == 'verify')
{
	$vbulletin->input->clean_gpc('r', 'code', TYPE_NOHTML);
	
	if (!empty($vbulletin->GPC['code']))
	{
		vbsetcookie('dbtech_criteria_code', $vbulletin->GPC['code'], true, true, true);
	}
	
	// fire off "successfully cookied hash"
	$vbulletin->url = $vbulletin->options['bburl'] . '/forum.php' . $vbulletin->session->vars['sessionurl_q'];
	eval(print_standard_redirect('dbtech_registration_code_cookied', true, true));
}
?>