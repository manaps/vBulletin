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

// #############################################################################
if ($_REQUEST['action'] == 'manage' OR empty($_REQUEST['action']))
{
    print_cp_header(construct_phrase($vbphrase['dbtech_registration_header_x'], construct_phrase($vbphrase['dbtech_registration_literal_x_y'], $vbphrase['manage'], $vbphrase['dbtech_registration_redirects'])));

	if (empty(REGISTRATION::$cache['redirect']))
	{
		// No redirects
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_redirects'], strtolower($vbphrase['dbtech_registration_redirect']));
	}
	
	$headings = array(
		$vbphrase['title'],
		$vbphrase['type'],
		$vbphrase['dbtech_registration_amount'],
		$vbphrase['dbtech_registration_persistent'],
		$vbphrase['active'],
		$vbphrase['options']
	);
	
	$headings_count = count($headings);
	
	print_form_header('registration', 'redirect');
		print_table_header($vbphrase['dbtech_registration_redirects'], count($headings));
		construct_hidden_code('action', 'update');
		print_cells_row($headings, true, false, $headings_count);

		foreach ((array)REGISTRATION::$cache['redirect'] AS $redirectid => $redirect)
		{
			$cell = array();
			$cell[] = $redirect['title'];
			$cell[] = $vbphrase['dbtech_registration_' . $redirect['type']];
			$cell[] = $redirect['amount'];
			$cell[] = '<input type="hidden" name="redirect[' . $redirectid . '][persistent]" 	value="0" />
			<input type="checkbox" name="redirect[' . $redirectid . '][persistent]" 	value="1"' . (!empty($redirect['persistent']) ? '	checked="checked"' : '') . ' />';
			$cell[] = '<input type="hidden" name="redirect[' . $redirectid . '][active]" 	value="0" />
			<input type="checkbox" name="redirect[' . $redirectid . '][active]" 		value="1"' . (!empty($redirect['active']) ? '		checked="checked"' : '') . ' />';
			$cell[] = construct_link_code(
				$vbphrase['edit'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=redirect&amp;action=modify&amp;redirectid=' . $redirectid,
				false
			) . ' ' . construct_link_code(
				$vbphrase['delete'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=redirect&amp;action=delete&amp;redirectid=' . $redirectid,
				false
			);
			print_cells_row($cell, false, false, $headings_count);
		}

		print_submit_row($vbphrase['save'], false, count($headings), false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_redirect']) . '" tabindex="1" onclick="window.location=\'registration.php?do=redirect&amp;action=modify\'" />');
	print_table_footer();
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$redirectid	= $vbulletin->input->clean_gpc('r', 'redirectid', TYPE_UINT);
	$redirect	= (!empty($redirectid) ? REGISTRATION::$cache['redirect'][$redirectid] : false);

	$defaults = array(
		'title' 		=> '',
		'type'			=> 1,
		'amount'		=> 30,
		'whitelist'		=> ''
	);
	
	if ($redirect)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_redirect'], $redirect['title'])));
		print_form_header('registration', 'redirect');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_redirect'], $redirect['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_redirect']));
		print_form_header('registration', 'redirect');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_redirect']));
		
		$redirect = $defaults;
		$phrase = $vbphrase['add'];
		
		// placeholder for the array
		$redirectid = 0;
	}
	
	// ini array
	$array = array(
		'pageviews'			=> $vbphrase['dbtech_registration_pageviews'],
		'threadviews'		=> $vbphrase['dbtech_registration_threadviews'],
		'firstactivity'		=> $vbphrase['dbtech_registration_firstactivity'],
	);
	
	// output some stuff
	print_input_row($vbphrase['title'],									'redirect[' . $redirectid . '][title]',				$redirect['title']);
	print_select_row($vbphrase['dbtech_registration_type'],				'redirect[' . $redirectid . '][type]',		$array,	$redirect['type']);
	print_input_row($vbphrase['dbtech_registration_amount'],			'redirect[' . $redirectid . '][amount]',			$redirect['amount']);
	print_yes_no_row($vbphrase['dbtech_registration_persistent'] .
					$vbphrase['dbtech_registration_persistent_desc'],	'redirect[' . $redirectid . '][persistent]',		$redirect['persistent']);
	print_yes_no_row($vbphrase['active'],								'redirect[' . $redirectid . '][active]',			$redirect['active']);
	print_textarea_row($vbphrase['dbtech_registration_whitelist'],		'redirect[' . $redirectid . '][whitelist]',			$redirect['whitelist']);

	
	print_table_break();
	
	print_table_header($vbphrase['options']);
	print_checkbox_row($vbphrase['dbtech_registration_exclude_bots'],				'redirect[' . $redirectid . '][options][exclude_bots]',	(isset($redirect['options']) AND $redirect['options'] & 1 ? true : false), 1);
	print_checkbox_row($vbphrase['dbtech_registration_exclude_matched_user_ips'],	'redirect[' . $redirectid . '][options][exclude_users]',(isset($redirect['options']) AND $redirect['options'] & 2 ? true : false), 1);
	
	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'redirect' 		=> TYPE_ARRAY_ARRAY
	));

	foreach ((array)$vbulletin->GPC['redirect'] AS $redirectid => $redirect)
	{
		if (empty($redirect))
		{
			// st00pid post data
			continue;
		}
	
		// sanitize
		$redirectid = (int)$redirectid;
		
		$dm =& REGISTRATION::initDataManager('redirect', $vbulletin, ERRTYPE_CP);
		$dm->set_info('whitelist', $vbulletin->GPC['redirect']['whitelist']);
		unset($vbulletin->GPC['redirect']['whitelist']);
		
		// set existing info if this is an update
		if (!empty($redirectid))
		{
			if (!$existing = REGISTRATION::$cache['redirect'][$redirectid])
			{
				// Couldn't find the redirect
				print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_redirect'], $redirectid);
			}

			// Set existing
			$dm->set_existing($existing);

			// edit
			$phrase = $vbphrase['dbtech_registration_edited'];
		}
		else
		{
			// add
			$phrase = $vbphrase['dbtech_registration_added'];
		}

		$bit = 0;
		foreach (array('exclude_bots' => 1, 'exclude_users' => 2) as $key => $value)
		{
			if (isset($redirect['options'][$key]) AND $redirect['options'][$key])
			{
				$bit += $value;
			}
		}
		$redirect['options'] = $bit;

		// redirect fields
		foreach ($redirect AS $key => $val)
		{
			if (empty($redirectid) OR $existing[$key] != $val)
			{
				// Only set changed values
				$dm->set($key, $val);
			}
		}

		$dm->save();
		
		// free memory
		unset($dm);
	}

	define('CP_REDIRECT', 'registration.php?do=redirect');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_redirect'], $phrase);
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'redirectid', TYPE_UINT);

	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_redirect']));
	print_delete_confirmation('dbtech_registration_redirect', $vbulletin->GPC['redirectid'], 'registration', 'redirect', 'dbtech_registration_redirect', array('action' => 'kill'), '', 'title', 'redirectid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'redirectid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['redirect'][$vbulletin->GPC['redirectid']])
	{
		// couldn't find the redirect
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_redirect'], $vbulletin->GPC['redirectid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('redirect', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=redirect');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_redirect'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}
?>