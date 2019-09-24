<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013-2012 Jon Dickinson AKA Pandemikk				  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/
@set_time_limit(0);
ignore_user_abort(1);

// #############################################################################
if ($_REQUEST['action'] == 'maintenance' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_registration_maintenance']);
	
	print_form_header('registration', 'maintenance');
	construct_hidden_code('action', 'recalctotals');
	print_table_header($vbphrase['dbtech_registration_recalculate_totals'], 2, 0);
	print_description_row($vbphrase['dbtech_registration_recalculate_totals_descr']);
	print_yes_no_row($vbphrase['dbtech_registration_are_you_sure_recalc'], 'recalctotals', 0);
	#print_input_row($vbphrase['number_of_users_to_process_per_cycle'], 'perpage', 50);
	#print_description_row($vbphrase['note_server_intensive']);
	print_submit_row($vbphrase['dbtech_registration_recalculate_totals']);
	
	#($hook = vBulletinHook::fetch_hook('dbtech_registration_maintenance')) ? eval($hook) : false;
}

// #############################################################################
if ($_REQUEST['action'] == 'recalctotals')
{
	print_cp_header($vbphrase['dbtech_registration_maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'recalctotals'		=> TYPE_BOOL,
		'perpage'			=> TYPE_UINT,
	));
	
	if (!$vbulletin->GPC['recalctotals'])
	{
		// Nothing to do
		print_stop_message('nothing_to_do');
	}

	#echo '<p>' . $vbphrase['dbtech_registration_recalculating_points'] . '</p>';
	
	$totals = array(
		'invites' 		=> array(
			'sent'			=> REGISTRATION::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_registration_invite AS invite
			'),
			'verified'		=> REGISTRATION::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_registration_invite AS invite
				INNER JOIN $dbtech_registration_email AS email ON(invite.email = email.email)
					AND email.verified = \'1\'
			'),
		),
		'verify_emails'	=> array(
			'sent'			=> REGISTRATION::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_registration_email AS email
				INNER JOIN $dbtech_registration_invite AS invite ON(invite.email = email.email)
				WHERE invite.email IS NULL
			'),
			'verified'		=> REGISTRATION::$db->fetchOne('
				SELECT COUNT(*)
				FROM $dbtech_registration_email AS email
				INNER JOIN $dbtech_registration_invite AS invite ON(invite.email = email.email)
				WHERE invite.email IS NULL AND email.verified = \'1\'
			'),
		)
	);

	// Update datastore
	build_datastore('dbtech_registration_total', serialize($totals), 1);
		
	define('CP_REDIRECT', 'registration.php?do=maintenance');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_totals'], $vbphrase['dbtech_registration_recalculated']);
}

// #############################################################################
if ($_REQUEST['action'] == 'resetpoints')
{
	print_cp_header($vbphrase['dbtech_registration_maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'doresetpoints' => TYPE_BOOL,
		'alsocurrency' 	=> TYPE_BOOL,
		'perpage' 		=> TYPE_UINT,
		'startat' 		=> TYPE_UINT
	));
	
	if (!$vbulletin->GPC['doresetpoints'])
	{
		// Nothing to do
		print_stop_message('nothing_to_do');
	}
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_registration_resetting_points'] . '</p>';
	
	$users = $db->query_read_slave("
		SELECT userid
		FROM " . TABLE_PREFIX . "user
		WHERE userid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY userid
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		// Shorthand
		$userid = intval($user['userid']);
			
		// Update poinst
		$db->query_write("
			UPDATE " . TABLE_PREFIX . "user
			SET dbtech_registration_points = 0,
			" . ($vbulletin->GPC['alsocurrency'] ? " dbtech_registration_pointscache = 0, " : '') . "
			dbtech_registration_pointscache_day = 0,
			dbtech_registration_pointscache_week = 0,
			dbtech_registration_pointscache_month = 0
			WHERE userid = '$userid'
		");
		
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_registration_points
			WHERE userid = '$userid'
		");
			
		$db->query_write("
			INSERT INTO " . TABLE_PREFIX . "dbtech_registration_points
				(userid)
			VALUES (
				'$userid'
			)
		");
			
		$db->query_write("
			DELETE FROM " . TABLE_PREFIX . "dbtech_registration_pointslog
			WHERE userid = '$userid'
		");
			
			
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = $db->query_first_slave("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=resetpoints&startat=$finishat&pp=" . $vbulletin->GPC['perpage'] . "&doresetpoints=" . $vbulletin->GPC['doresetpoints'] . "&alsocurrency=" . $vbulletin->GPC['alsocurrency']);
		echo "<p><a href=\"registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=resetpoints&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "&amp;doresetpoints=" . $vbulletin->GPC['doresetpoints'] . "&amp;alsocurrency=" . $vbulletin->GPC['alsocurrency'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{
		define('CP_REDIRECT', 'registration.php?do=maintenance');
		print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_points'], $vbphrase['dbtech_registration_reset']);
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'recalcachievements')
{
	print_cp_header($vbphrase['dbtech_registration_maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	if (empty(REGISTRATION::$cache['achievement']))
	{
		// Nothing to do
		print_stop_message('nothing_to_do');	
	}
	
	echo '<p>' . $vbphrase['dbtech_registration_recalculating_achievements'] . '</p>';
	
	$users = $db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "user AS user
		WHERE userid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY userid
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		// Ensure the cache is valid
		REGISTRATION::verify_rewards_cache($user, false);
		
		foreach (REGISTRATION::$cache['achievement'] as $achievementid => $achievement)
		{
			// Check the achievement
			$allconditions = REGISTRATION::check_feature('achievement', $achievementid, $user);
			if (!$allconditions)
			{
				if (!is_array($user['dbtech_registration_rewardscache']))
				{
					// Ensure the cache is valid
					REGISTRATION::verify_rewards_cache($user, false);				
				}
				
				// We didn't meet the criteria. saedfaec
				foreach ((array)$user['dbtech_registration_rewardscache'] as $rewardid => $reward)
				{
					if ($reward['feature'] == 'achievement' AND $reward['featureid'] == $achievementid)
					{
						// we had this reward, let's kill it
						$db->query_first_slave("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_rewards WHERE rewardid = " . intval($rewardid));
					}
				}
				
				continue;
			}
			
			// Add the reward
			REGISTRATION::add_reward('achievement', $achievementid, $user);
		}
		
		// Build the rewards cache
		REGISTRATION::build_rewards_cache($user);
		
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = $db->query_first_slave("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=recalcachievements&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=recalcachievements&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'registration.php?do=maintenance');
		print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_achievement'], $vbphrase['dbtech_registration_recalculated']);
	}	
}

// #############################################################################
if ($_REQUEST['action'] == 'recalcpromotions')
{
	print_cp_header($vbphrase['dbtech_registration_maintenance']);
	
	$vbulletin->input->clean_array_gpc('r', array(
		'perpage' => TYPE_UINT,
		'startat' => TYPE_UINT
	));
	
	if (empty($vbulletin->GPC['perpage']))
	{
		$vbulletin->GPC['perpage'] = 1000;
	}
	
	echo '<p>' . $vbphrase['dbtech_registration_recalculating_promotions'] . '</p>';
	
	$users = $db->query_read_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "user AS user
		WHERE userid >= " . $vbulletin->GPC['startat'] . "
		ORDER BY userid
		LIMIT " . $vbulletin->GPC['perpage']
	);
	
	$finishat = $vbulletin->GPC['startat'];
	
	while ($user = $db->fetch_array($users))
	{
		// Shorthand
		$userid = intval($user['userid']);
		
		foreach (REGISTRATION::$cache['promotion'] as $promotionid => $promotion)
		{
			// Check the promotion
			$allconditions = REGISTRATION::check_feature('promotion', $promotionid, $user);
			
			if (!$allconditions)
			{
				// We didn't meet the criteria. saedfaec
				continue;
			}
			
			// Add the reward
			REGISTRATION::add_reward('promotion', $promotionid, $user);
		}
	
		echo construct_phrase($vbphrase['processing_x'], $user['userid']) . "<br />\n";
		vbflush();
	
		$finishat = ($user['userid'] > $finishat ? $user['userid'] : $finishat);
	}
	
	$finishat++;
	
	if ($checkmore = $db->query_first_slave("SELECT userid FROM " . TABLE_PREFIX . "user WHERE userid >= $finishat LIMIT 1"))
	{
		print_cp_redirect("registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&action=recalcpromotions&startat=$finishat&pp=" . $vbulletin->GPC['perpage']);
		echo "<p><a href=\"registration.php?" . $vbulletin->session->vars['sessionurl'] . "do=maintenance&amp;action=recalcpromotions&amp;startat=$finishat&amp;pp=" . $vbulletin->GPC['perpage'] . "\">" . $vbphrase['click_here_to_continue_processing'] . "</a></p>";
	}
	else
	{	
		define('CP_REDIRECT', 'registration.php?do=maintenance');
		print_stop_message('dbtech_vbshout_promotions_recalculated');
	}
}

print_cp_footer();

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: vbshout.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>