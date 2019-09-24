<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Jon Dickinson AKA Pandemikk					  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

$vbulletin->input->clean_array_gpc('r', array(
	'inviteid' 	=> TYPE_UINT,
	'kill' 		=> TYPE_BOOL
));

if (!$vbulletin->GPC['kill'])
{
	print_cp_header(construct_phrase($vbphrase['dbtech_registration_delete_x'], $vbphrase['dbtech_registration_invite']));
	print_delete_confirmation('dbtech_registration_invite', $vbulletin->GPC['inviteid'], 'registration', 'deleteinvite', 'dbtech_registration_invite', array('kill' => true), '', 'inviteid', 'inviteid');
	print_cp_footer();
}
else
{
	// ###################### Start Kill #######################
	if ($invite = REGISTRATION::$db->fetchRow('
		SELECT email
		FROM $dbtech_registration_invite
		WHERE inviteid = ?
	', array(
		$vbulletin->GPC['inviteid']
	)))
	{
		// This invite existsted
		REGISTRATION::$db->delete('dbtech_registration_invite', array($vbulletin->GPC['inviteid']), 'WHERE inviteid = ?');
	}
	
	// update cache
	--REGISTRATION::$cache['total']['invites']['sent'];
	
	if ($exists = REGISTRATION::$db->fetchRow('
		SELECT verified
		FROM $dbtech_registration_email
		WHERE email = ?
	', array(
		$invite['email']
	)))
	{
		if ($exists['verified'])
		{
			// update cache
			--REGISTRATION::$cache['total']['invites']['verified'];
		}

		REGISTRATION::$db->delete('dbtech_registration_email', array($invite['email']), 'WHERE email = ?');
	}
	
	// update datastore
	build_datastore('dbtech_registration_total', serialize(REGISTRATION::$cache['total']), 1);
		
	define('CP_REDIRECT', 'registration.php?do=search');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_invite'], $vbphrase['dbtech_registration_deleted']);
}

/*======================================================================*\
|| #################################################################### ||
|| # Created: 16:52, Thu Sep 18th 2008								  # ||
|| # SVN: $Rev$									 					  # ||
|| #################################################################### ||
\*======================================================================*/
?>