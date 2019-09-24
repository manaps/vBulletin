<?php
if ($vbulletin->options['dbtech_registration_active'] AND $vbulletin->options['verifyemail'] AND $userdata->pre_save() AND !$user['emailchange'])
{
	REGISTRATION::build_log(
		$userinfo['email'],
		'dbtech_registration_register',
		serialize(array('user' => $userinfo['userid']))
	);

	// Invite stuff
	if ($vbulletin->options['dbtech_registration_invites'] AND $invite = $db->query_first_slave("
		SELECT inviteid, userid FROM " . TABLE_PREFIX . "dbtech_registration_invite
		WHERE email = " . $db->sql_prepare($userinfo['email'])
	))
	{
		// Fetch inviter's userinfo
		//$inviter = fetch_userinfo($invite['userid']);

		if (class_exists('VBACTIVITY'))
		{
			// Insert points
			VBACTIVITY::insert_points('invite_registered', $invite['inviteid'], $invite['userid']);

			$vbactivity_typenames = array(
				'invite_registered',
			);
			$vbactivity_loc = 'invite_registered';

			($hook = vBulletinHook::fetch_hook('dbtech_vbactivity_data_submit')) ? eval($hook) : false;

			// Check achievements
			//VBACTIVITY::check_feature_by_typenames('achievement', $vbactivity_typenames, $userinfo); # Sender
			VBACTIVITY::check_feature_by_typenames('achievement', $vbactivity_typenames, $invite); # Recipient
		}

		if ($vbulletin->options['dbtech_registration_sendpm'])
		{
			REGISTRATION::sendPM($invite['userid'], $vbphrase['dbtech_registration_sendpm_invite_title'], construct_phrase(
				$vbphrase['dbtech_registration_sendpm_invite_body'], $userinfo['email'], $userinfo['userid'], $userinfo['username'], $vbulletin->options['bburl']
			), $vbulletin->options['dbtech_registration_sendpm_user']);
		}
	}
	
	// Actions

	// Fetch the instance class
	require_once(DIR . '/dbtech/registration/includes/class_register.php');

	// Set our instanceid
	REGISTRATION_REGISTER::fetch_instance_by_criterias();
	
	/*DBTECH_PRO_START*/
	// Execute instances actions
	REGISTRATION_REGISTER::exec_actions($userinfo, $vbulletin, $vbphrase);
	/*DBTECH_PRO_END*/
}
?>