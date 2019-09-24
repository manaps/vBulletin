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
    print_cp_header($vbphrase['dbtech_registration_section_management']);

	if (empty(REGISTRATION::$cache['section']))
	{
		// No sections
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_sections'], strtolower($vbphrase['dbtech_registration_section']));
	}

	$headings = array(
		$vbphrase['title'],
		//$vbphrase['display_order'],
		$vbphrase['options']
	);
	
	print_form_header('registration', 'section');
	construct_hidden_code('action', 'process');
	print_table_header($vbphrase['dbtech_registration_sections'], count($headings));
	print_cells_row($headings, 'thead');

	foreach (REGISTRATION::$cache['section'] AS $sectionid => $section)
	{
		$cell = array();
		
		// Set title
		$cell[] = $section['title'];

		$cell[] = construct_link_code(
			$vbphrase['edit'],
			'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=section&amp;action=modify&amp;sectionid=' . $sectionid,
			false
		) . ' ' . construct_link_code(
			$vbphrase['delete'],
			'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=section&amp;action=delete&amp;sectionid=' . $sectionid,
			false
		);
		print_cells_row($cell, false, false, $headings_count);
	}

	print_submit_row($vbphrase['save_display_order'], false, count($headings), false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_section']) . '" tabindex="1" onclick="window.location=\'registration.php?do=section&amp;action=modify\'" />');
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$sectionid	= $vbulletin->input->clean_gpc('r', 'sectionid', TYPE_UINT);
	$section	= (!empty($sectionid) ? REGISTRATION::$cache['section'][$sectionid] : false);

	$defaults = array(
		'title' 		=> '',
		'active'		=> 1
	);
	
	if ($section)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_section'], $section['title'])));
		print_form_header('registration', 'section');
		construct_hidden_code('action', 'update');
		construct_hidden_code('sectionid', $sectionid);
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_section'], $section['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_section']));
		print_form_header('registration', 'section');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_section']));
		
		$section = $defaults;
		$phrase = $vbphrase['add'];
	}

	$instances = array();
	foreach ((array)REGISTRATION::$cache['instance'] AS $instanceid => $instance)
	{
		// Set title
		$instances[$instanceid] = $instance['title'];
	}

	$instance = REGISTRATION::$db->fetchAllSingleKeyed('
		SELECT instanceid FROM $dbtech_registration_instance_section
		WHERE sectionid = ' . $sectionid
		, 'nokey', 'instanceid'
	);
	
	print_input_row($vbphrase['title'], 						'section[title]', 					$section['title']);
	print_select_row($vbphrase['dbtech_registration_instances'],'section[instances][]', $instances,	$instance, true, 5, true);
	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'sectionid' 	=> TYPE_UINT,
		'section' 		=> TYPE_ARRAY
	));

	$dm =& REGISTRATION::initDataManager('section', $vbulletin, ERRTYPE_CP);

	$dm->set_info('instances', $vbulletin->GPC['section']['instances']);
	unset($vbulletin->GPC['section']['instances']);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['sectionid'])
	{
		if (!$existing = REGISTRATION::$cache['section'][$vbulletin->GPC['sectionid']])
		{
			// Couldn't find the section
			print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_section'], $vbulletin->GPC['sectionid']);
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

	// section fields
	foreach ($vbulletin->GPC['section'] AS $key => $val)
	{
		if (!isset($vbulletin->GPC['sectionid']) OR $existing[$key] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	$dm->save();

	define('CP_REDIRECT', 'registration.php?do=section');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_section'], $phrase);
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'sectionid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['section'][$vbulletin->GPC['sectionid']])
	{
		// couldn't find the section
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_section'], $vbulletin->GPC['sectionid']);
	}
	
	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_section']));
	print_delete_confirmation('dbtech_registration_section', $vbulletin->GPC['sectionid'], 'registration', 'section', 'dbtech_registration_section', array('action' => 'kill'), '', 'title', 'sectionid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'sectionid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['section'][$vbulletin->GPC['sectionid']])
	{
		// couldn't find the section
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_section'], $vbulletin->GPC['sectionid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('section', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=section');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_section'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}
?>