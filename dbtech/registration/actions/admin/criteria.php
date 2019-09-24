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
    print_cp_header($vbphrase['dbtech_registration_criteria_management']);

	if (empty(REGISTRATION::$cache['criteria']))
	{
		// No criterias
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_criterias'], strtolower($vbphrase['dbtech_registration_criteria']));
	}

	$headings = array(
		$vbphrase['title'],
		$vbphrase['dbtech_registration_type'],
		$vbphrase['dbtech_registration_operator'],
		$vbphrase['dbtech_registration_value'],
		$vbphrase['active'],
		$vbphrase['options']
	);
	
	$headings_count = count($headings);
	
	print_form_header('registration', 'criteria');
	construct_hidden_code('action', 'process');
	print_table_header($vbphrase['dbtech_registration_criterias'], count($headings));
	print_cells_row($headings, true, false, $headings_count);

	foreach ((array)REGISTRATION::$cache['criteria'] AS $criteriaid => $criteria)
	{
		$cell = array();
		$cell[] = $criteria['title'];
		$cell[] = $vbphrase['dbtech_registration_' . $criteria['type']];
		$cell[] = $criteria['operator'];
		$cell[] = $criteria['value'];
		$cell[] = '	
			<input type="hidden" name="active[' . $criteriaid . ']" value="0" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;active&quot;"') . iif($criteria['active'], ' checked="checked"') . ' />
			<input type="checkbox" name="active[' . $criteriaid . ']" value="1" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;active&quot;"') . iif($criteria['active'], ' checked="checked"') . ' />';
		$cell[] = construct_link_code(
			$vbphrase['edit'],
			'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=criteria&amp;action=modify&amp;criteriaid=' . $criteriaid,
			false
		) . ' ' . construct_link_code(
			$vbphrase['delete'],
			'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=criteria&amp;action=delete&amp;criteriaid=' . $criteriaid,
			false
		);
		print_cells_row($cell, false, false, 2);
	}

	print_submit_row('', false, count($headings), false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_criteria']) . '" tabindex="1" onclick="window.location=\'registration.php?do=criteria&amp;action=modify\'" />');
	print_table_footer();
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$criteriaid	= $vbulletin->input->clean_gpc('r', 'criteriaid', TYPE_UINT);
	$criteria	= (!empty($criteriaid) ? REGISTRATION::$cache['criteria'][$criteriaid] : false);

	$defaults = array(
		'title' 	=> '',
		'type'		=> 'location',
		'operator'	=> '==',
		'value'		=> 1
	);
	
	if ($criteria)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_criteria'], $criteria['title'])));
		print_form_header('registration', 'criteria');
		construct_hidden_code('action', 'update');
		construct_hidden_code('criteriaid', $criteriaid);
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_criteria'], $criteria['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_criteria']));
		print_form_header('registration', 'criteria');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_criteria']));
		
		$criteria = $defaults;
		$phrase = $vbphrase['add'];
	}

	$instances = array();
	foreach ((array)REGISTRATION::$cache['instance'] AS $instanceid => $instance)
	{
		// Set title
		$instances[$instanceid] = $instance['title'];
	}

	$instance = REGISTRATION::$db->fetchAllSingleKeyed('
		SELECT instanceid FROM $dbtech_registration_instance_criteria
		WHERE criteriaid = ' . $criteriaid
	, 'nokey', 'instanceid');

	print_input_row($vbphrase['title'], 						'criteria[title]', 		$criteria['title']);
	print_select_row($vbphrase['type'], 						'criteria[type]',
		array(
			'location'	=> $vbphrase['dbtech_registration_location'],
			'code'		=> $vbphrase['dbtech_registration_code'],
			'invited'	=> $vbphrase['dbtech_registration_invited'],
			'verified'	=> $vbphrase['dbtech_registration_verified'],
			'proxy'		=> $vbphrase['dbtech_registration_proxy'],
		),
																						$criteria['type']);
	print_select_row($vbphrase['dbtech_registration_operator'], 'criteria[operator]',
		array('==' => '==', '!=' => '!='/*, '>' => '>', '<' => '<', '>=' => '>=', '<=' => '<='*/),
																						$criteria['operator']);
	print_input_row($vbphrase['dbtech_registration_value'], 	'criteria[value]', 		$criteria['value']);
	print_yes_no_row($vbphrase['active'], 						'criteria[active]',		$criteria['active']);
	print_select_row($vbphrase['dbtech_registration_instances'],'criteria[instances][]', 
		$instances,
																						$instance, true, 5, true);

	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'criteriaid' 	=> TYPE_UINT,
		'criteria' 		=> TYPE_ARRAY
	));

	$dm =& REGISTRATION::initDataManager('criteria', $vbulletin, ERRTYPE_CP);

	$dm->set_info('instances', $vbulletin->GPC['criteria']['instances']);
	unset($vbulletin->GPC['criteria']['instances']);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['criteriaid'])
	{
		if (!$existing = REGISTRATION::$cache['criteria'][$vbulletin->GPC['criteriaid']])
		{
			// Couldn't find the criteria
			print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_criteria'], $vbulletin->GPC['criteriaid']);
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

	// criteria fields
	foreach ($vbulletin->GPC['criteria'] AS $key => $val)
	{
		if (!isset($vbulletin->GPC['criteriaid']) OR $existing[$key] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	$dm->save();

	define('CP_REDIRECT', 'registration.php?do=criteria');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_criteria'], $phrase);
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'criteriaid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['criteria'][$vbulletin->GPC['criteriaid']])
	{
		// couldn't find the criteria
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_criteria'], $vbulletin->GPC['criteriaid']);
	}

	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_criteria']));
	print_delete_confirmation('dbtech_registration_criteria', $vbulletin->GPC['criteriaid'], 'registration', 'criteria', 'dbtech_registration_criteria', array('action' => 'kill'), '', 'title', 'criteriaid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'criteriaid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['criteria'][$vbulletin->GPC['criteriaid']])
	{
		// couldn't find the criteria
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_criteria'], $vbulletin->GPC['criteriaid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('criteria', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=criteria');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_criteria'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'process')
{
	$vbulletin->input->clean_gpc('p', 'active', TYPE_ARRAY_BOOL);

	if (is_array($vbulletin->GPC['active']))
	{
		foreach ($vbulletin->GPC['active'] AS $criteriaid => $active)
		{
			// set existing info
			if (!$existing = REGISTRATION::$cache['criteria'][$criteriaid])
			{
				// Couldn't find the criteria
				continue;
			}
			
			if ($existing['active'] == $active)
			{
				// We don't need to call the dm
				continue;
			}

			// init datamanager
			$dm =& REGISTRATION::initDataManager('criteria', $vbulletin, ERRTYPE_CP);
		
				// Set existing
				$dm->set_existing($existing);

				// Set active flag
				$dm->set('active', $active);

			$dm->save();
		}
	}

	define('CP_REDIRECT', 'registration.php?do=criteria');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_criterias'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}
?>