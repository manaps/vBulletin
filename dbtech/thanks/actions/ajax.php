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

if (!$_REQUEST['action'])
{
	print_output();
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// #######################################################################
if ($_POST['action'] == 'entry')
{
	// Init this
	$retval = array();

	// Grab these
	$contenttype = $vbulletin->input->clean_gpc('p', 'contenttype', TYPE_STR);
	$contenttype = ($contenttype ? preg_replace('/[^\w-]/i', '', $contenttype) : 'post');

	if (!file_exists(DIR . '/dbtech/thanks_pro/contenttypes/' . $contenttype . '/save.php'))
	{
		if (file_exists(DIR . '/dbtech/thanks/contenttypes/' . $contenttype . '/save.php'))
		{
			// We can do this
			require(DIR . '/dbtech/thanks/contenttypes/' . $contenttype . '/save.php');
		}
	}
	else
	{
		// We can do this
		require(DIR . '/dbtech/thanks_pro/contenttypes/' . $contenttype . '/save.php');
	}

	// Return the compiled list
	THANKS::outputXML($retval);
}

// #######################################################################
if ($_POST['action'] == 'whoclicked')
{
	// Grab these
	$contenttype = $vbulletin->input->clean_gpc('p', 'contenttype', TYPE_STR);
	$contenttype = ($contenttype ? preg_replace('/[^\w-]/i', '', $contenttype) : 'post');

	$varname = $vbulletin->input->clean_gpc('p', 'varname', TYPE_STR);

	foreach (THANKS::$cache['button'] as $button)
	{
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}

		if ($button['varname'] == $varname)
		{
			// Copy this
			break;
		}
	}

	if (empty($button))
	{
		// Invalid varname
		THANKS::outputJSON(array(
			'errors' => $vbphrase['dbtech_thanks_invalid_button']
		));
	}

	if (THANKS::checkPermissions($vbulletin->userinfo, $button['permissions'], 'cannotseeclicks'))
	{
		// Invalid varname
		THANKS::outputJSON(array(
			'errors' => $vbphrase['dbtech_thanks_invalid_button']
		));
	}

	if (!$users = THANKS::$db->fetchAll('
		SELECT
			user.*
			:avatarSelect
		FROM $dbtech_thanks_entry AS entry
		INNER JOIN $user AS user ON (user.userid = entry.userid)
		:avatarJoin
		WHERE entry.contentid = ?
			AND entry.varname = ?
		ORDER BY entry.entryid DESC
	', array(
		':avatarSelect' => ($vbulletin->options['avatarenabled'] ? ',avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight' : ''),
		':avatarJoin' 	=> ($vbulletin->options['avatarenabled'] ? 'LEFT JOIN $avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN $customavatar AS customavatar ON(customavatar.userid = user.userid)' : ''),
		$vbulletin->input->clean_gpc('p', 'postid', TYPE_UINT),
		$varname
	)))
	{
		// Invalid varname
		THANKS::outputJSON(array(
			'errors' => $vbphrase['dbtech_thanks_noone_clicked']
		));
	}

	if (!function_exists('fetch_avatar_from_userinfo'))
	{
		// Get the avatar function
		require_once(DIR . '/includes/functions_user.php');
	}

	$userArray = array();
	foreach ($users as &$user)
	{
		// grab avatar from userinfo
		fetch_avatar_from_userinfo($user, true);

		// get markup username
		fetch_musername($user, true);

		$userArray[] = array(
			'profileurl' 	=> 'member.php?u=' . $user['userid'],
			'avatarurl' 	=> $user['avatarurl'],
			'userid' 		=> $user['userid'],
			'username' 		=> $user['username'],
			'musername' 	=> $user['musername'],
			'usertitle' 	=> $user['usertitle']
		);
	}

	// Return the compiled list
	THANKS::outputJSON($userArray);
}