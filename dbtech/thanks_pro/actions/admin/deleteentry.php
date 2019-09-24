<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
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
	'entryid' 	=> TYPE_UINT,
	'kill' 		=> TYPE_BOOL
));

if (!$vbulletin->GPC['kill'])
{
	print_cp_header(construct_phrase($vbphrase['dbtech_vbshout_delete_x'], $vbphrase['dbtech_thanks_entry']));
	print_delete_confirmation('dbtech_thanks_entry', $vbulletin->GPC['entryid'], 'thanks', 'deleteentry', 'dbtech_thanks_entry', array('kill' => true), '', 'contentid');
	print_cp_footer();
}
else
{
	// ###################### Start Kill #######################
	$entry = $db->query_first_slave("SELECT * FROM `" . TABLE_PREFIX . "dbtech_thanks_entry` AS entry WHERE `entryid` = " . $db->sql_prepare($vbulletin->GPC['entryid']));
	
	// init data manager
	$dm =& THANKS::initDataManager('Entry', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($entry);
	$dm->delete();	
		
	define('CP_REDIRECT', 'thanks.php?do=search');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_entry'], $vbphrase['dbtech_thanks_deleted']);
}