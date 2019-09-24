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
    print_cp_header($vbphrase['dbtech_registration_field_management']);
	
	if (empty(REGISTRATION::$cache['field']))
	{
		// No fields
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_fields'], strtolower($vbphrase['dbtech_registration_field']));
	}
	
	$headings = array(
		$vbphrase['title'],
		$vbphrase['dbtech_registration_type'],
		$vbphrase['active'],
		$vbphrase['options']
	);
	
	$headings_count = count($headings);

	print_form_header('registration', 'field');
		construct_hidden_code('action', 'process');
		print_table_header($vbphrase['dbtech_registration_fields'], count($headings));
		print_cells_row($headings, true, false, $headings_count);
		
		foreach (REGISTRATION::$cache['field'] AS $fieldid => $field)
		{
			$cells = array();
				
			// set title
			$cells[] = $field['title'];
				
			// set type
			$cells[] = isset($vbphrase[$field['type']])
				? $vbphrase[$field['type']] : $vbphrase[$field['type'] . '_title'] . ' (' . $vbphrase['user_profile_field'] . ')';
			
			$cells[] = '	
				<input type="hidden" name="active[' . $fieldid . ']" value="0" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;active&quot;"') . iif($field['active'], ' checked="checked"') . ' />
				<input type="checkbox" name="active[' . $fieldid . ']" value="1" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;active&quot;"') . iif($field['active'], ' checked="checked"') . ' />';
			
			$cells[] = construct_link_code(
				$vbphrase['edit'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=field&amp;action=modify&amp;fieldid=' . $fieldid,
				false
			) . ' ' . construct_link_code(
				$vbphrase['delete'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=field&amp;action=delete&amp;fieldid=' . $fieldid,
				false
			);

			print_cells_row($cells, false, false, 2);
		}

	print_submit_row('', false, $headings_count, false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_field']) . '" tabindex="1" onclick="window.location=\'registration.php?do=field&action=modify\'" />');
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$fieldid	= $vbulletin->input->clean_gpc('r', 'fieldid', TYPE_UINT);
	$field		= (!empty($fieldid) ? REGISTRATION::$cache['field'][$fieldid] : false);

	$defaults = array(
		'title' 	=> '',
		'type'		=> 'username'
	);
	
	if ($field)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_field'], $field['title'])));
		print_form_header('registration', 'field');
		construct_hidden_code('action', 'update');
		construct_hidden_code('fieldid', $fieldid);
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_field'], $field['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_field']));
		print_form_header('registration', 'field');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_field']));
		
		$field = $defaults;
		$phrase = $vbphrase['add'];
	}

	// Valid types
	$validtypes = array();
	
	$profilefields = REGISTRATION::$db->fetchAll('
		SELECT profilefieldid FROM $profilefield
		WHERE editable > 0 
	');
	
	foreach ($profilefields AS $profilefield)
	{
		// Add custom profile field to validtypes array
		$validtypes['field' . $profilefield['profilefieldid']] = $vbphrase['field' . $profilefield['profilefieldid'] . '_title'] . ' (' . $vbphrase['user_profile_field'] . ')';
	}

	foreach (array(
		'username',
		'usergroup',
		'password',
		'email',
		'coppa',
		'human_verification',
		'birthday',
		'referrer',
		'avatar',
		'receive_email',
		'timezone'
	) AS $varname)
	{
		$validtypes[$varname] = $vbphrase[$varname];
	}
	
	$instances = array();
	foreach ((array)REGISTRATION::$cache['instance'] AS $instanceid => $instance)
	{
		// Set title
		$instances[$instanceid] = $instance['title'];
	}

	// Get our chosen instance ID
	$instance = REGISTRATION::$db->fetchOne('SELECT instanceid FROM $dbtech_registration_instance_field WHERE fieldid = ' . $fieldid);

	print_input_row($vbphrase['title'], 						'field[title]', 						$field['title']);
	print_select_row($vbphrase['type'],							'field[type]', 			$validtypes, 	$field['type']);
	print_yes_no_row($vbphrase['active'], 						'field[active]',						$field['active']);
	print_select_row($vbphrase['dbtech_registration_instances'],'field[instances][]', 	$instances, 	$instance, true, 5, true);
	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'fieldid'	 	=> TYPE_UINT,
		'field' 		=> TYPE_ARRAY
	));

	$dm =& REGISTRATION::initDataManager('field', $vbulletin, ERRTYPE_CP);
		$dm->set_info('instances', $vbulletin->GPC['field']['instances']);
		unset($vbulletin->GPC['field']['instances']);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['fieldid'])
	{
		if (!$existing = REGISTRATION::$cache['field'][$vbulletin->GPC['fieldid']])
		{
			// Couldn't find the action
			print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_field'], $vbulletin->GPC['fieldid']);
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

	// field... fields
	foreach ($vbulletin->GPC['field'] AS $key => $val)
	{
		if (!isset($vbulletin->GPC['fieldid']) OR $existing[$key] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	$dm->save();

	define('CP_REDIRECT', 'registration.php?do=field');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_field'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'fieldid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['field'][$vbulletin->GPC['fieldid']])
	{
		// couldn't find the field
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_field'], $vbulletin->GPC['fieldid']);
	}

	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_field']));
	print_delete_confirmation('dbtech_registration_field', $vbulletin->GPC['fieldid'], 'registration', 'field', 'dbtech_registration_field', array('action' => 'kill'), '', 'title', 'fieldid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'fieldid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['field'][$vbulletin->GPC['fieldid']])
	{
		// couldn't find the field
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_field'], $vbulletin->GPC['fieldid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('Field', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=field');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_field'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'process')
{
	$vbulletin->input->clean_gpc('p', 'active', TYPE_ARRAY_BOOL);

	if (is_array($vbulletin->GPC['active']))
	{
		foreach ($vbulletin->GPC['active'] AS $fieldid => $active)
		{
			// set existing info
			if (!$existing = REGISTRATION::$cache['field'][$fieldid])
			{
				// Couldn't find the field
				continue;
			}
			
			if ($existing['active'] == $active)
			{
				// We don't need to call the dm
				continue;
			}

			// init datamanager
			$dm =& REGISTRATION::initDataManager('field', $vbulletin, ERRTYPE_CP);
		
				// Set existing
				$dm->set_existing($existing);

				// Set active flag
				$dm->set('active', $active);

			$dm->save();
		}
	}

	define('CP_REDIRECT', 'registration.php?do=field');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_fields'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}
?>