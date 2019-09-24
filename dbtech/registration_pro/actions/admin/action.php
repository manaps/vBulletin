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

// Fetch the instance class
require_once(DIR . '/dbtech/registration/includes/class_register.php');

// #############################################################################
if ($_REQUEST['action'] == 'manage' OR empty($_REQUEST['action']))
{
    print_cp_header($vbphrase['dbtech_registration_action_management']);

	if (empty(REGISTRATION::$cache['action']))
	{
		// No actions
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_actions'], strtolower($vbphrase['dbtech_registration_action']));
	}
	
	$headings = array(
		$vbphrase['title'],
		$vbphrase['dbtech_registration_type'],
		$vbphrase['dbtech_registration_value'],
		$vbphrase['active'],
		$vbphrase['options']
	);
	
	$headings_count = count($headings);
	
	print_form_header('registration', 'action');
		construct_hidden_code('action', 'process');
		print_table_header($vbphrase['dbtech_registration_actions'], count($headings));
		print_cells_row($headings, true, false, $headings_count);

		foreach (REGISTRATION::$cache['action'] AS $actionid => $action)
		{
			$cell = array();
			$cell[] = $action['title'];
			$cell[] = isset($vbphrase[$action['type']]) ? $vbphrase[$action['type']] : $vbphrase['dbtech_registration_' . $action['type']];
			
			if (in_array($action['type'], array('new_thread', 'new_post')))
			{
				// Show in readable format
				$action['value'] = unserialize($action['value']);

				foreach ($action['value'] AS $key => &$value)
				{
					$value = $vbphrase[$key] . ': ' . (is_array($value) ? '<select multiple="multiple">' . construct_forum_chooser($value) . '</select>' : $value);
				}

				$action['value'] = implode('<br />', $action['value']);
			}
			
			$cell[] = $action['value'];
			$cell[] = '	
				<input type="hidden" name="active[' . $actionid . ']" value="0" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;$active&quot;"') . iif($action['active'], ' checked="checked"') . ' />
				<input type="checkbox" name="active[' . $actionid . ']" value="1" tabindex="1"' . iif($vbulletin->debug, ' title="name=&quot;$active&quot;"') . iif($action['active'], ' checked="checked"') . ' />';
			$cell[] = construct_link_code(
				$vbphrase['edit'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=action&amp;action=modify&amp;actionid=' . $actionid,
				false
			) . ' ' . construct_link_code(
				$vbphrase['delete'],
				'registration.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=action&amp;action=delete&amp;actionid=' . $actionid,
				false
			);
			print_cells_row($cell, false, false, $headings_count);
		}

		print_submit_row('', false, count($headings), false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_action']) . '" tabindex="1" onclick="window.location=\'registration.php?do=action&amp;action=modify\'" />');
	print_table_footer();
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$actionid	= $vbulletin->input->clean_gpc('r', 'actionid', TYPE_UINT);
	$action		= (!empty($actionid) ? REGISTRATION::$cache['action'][$actionid] : false);

	$defaults = array(
		'title' 	=> '',
		'active'	=> 1
	);
	
	$types = array(
		'usergroup'		=> $vbphrase['usergroup'],
		'displaygroup'	=> $vbphrase['displaygroup'],
		'new_thread'	=> $vbphrase['dbtech_registration_new_thread'],
		'new_post'		=> $vbphrase['dbtech_registration_new_post'] # i.e existing thread
	);
	
	// Maybe manage overrides? or include those in actions? For example override 'verifyemail' to yes/no - see onwards from line 465 of register.php
	if ($action)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_action'], $action['title'])));
		print_form_header('registration', 'action');
		construct_hidden_code('action', 'update');
		construct_hidden_code('actionid', $actionid);
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_action'], $action['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_action']));
		print_form_header('registration', 'action');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_action']));

		if (!($action['type'] = $vbulletin->input->clean_gpc('p', 'type', TYPE_STR)) OR empty($types[$action['type']]))
		{
			// Form to input action type
			construct_hidden_code('action', 'modify');
			print_select_row($vbphrase['dbtech_registration_type'], 'type', $types, $action['type']);
			print_submit_row($vbphrase['continue'], false);
			print_cp_footer();
		}

		// Construct hidden code here
		construct_hidden_code('action', 'update');
		
		// Set action type
		$defaults['type'] = $action['type'];
		
		$action = $defaults;
		$phrase = $vbphrase['add'];
	}

	$instances = array();
	foreach ((array)REGISTRATION::$cache['instance'] AS $instanceid => $instance)
	{
		// Set title
		$instances[$instanceid] = $instance['title'];
	}

	$instance = REGISTRATION::$db->fetchAllSingleKeyed('
		SELECT instanceid FROM $dbtech_registration_instance_action
		WHERE actionid = ' . $actionid
		, 'nokey', 'instanceid'
	);

	print_input_row($vbphrase['title'], 										'actions[title]',				$action['title']);
	print_yes_no_row($vbphrase['active'], 										'actions[active]',				$action['active']);
	print_select_row($vbphrase['dbtech_registration_instances'],				'actions[instances][]', 
		$instances,																								$instance, true, 5, true);	
	
	if (!empty($actionid))
	{
		// Friendly phrasing links
		print_description_row(construct_phrase($vbphrase['dbtech_registration_action_phrases'], $actionid));
	}
	
	switch ($action['type'])
	{
		case 'usergroup':
		case 'displaygroup':
			print_chooser_row($vbphrase[$action['type']], 						'actions[value]',
				'usergroup',																					(isset($action['value']) ? $action['value'] : 2));
			
			break;
		case 'new_thread':
		case 'new_post':
			$action['value'] = unserialize($action['value']);
			
			print_input_row($vbphrase['username'], 								'actions[value][username]', 	(isset($action['value']['username']) ? $action['value']['username'] : ''));
			
			if ($action['type'] == 'new_thread')
			{
				print_forum_chooser($vbphrase['forum'],							'actions[value][forumid][]', 	(isset($action['value']['forum']) ? $action['value']['forum'] : 1), '', false, true);
			}
			else
			{
				print_input_row($vbphrase['dbtech_registration_enter_threadid'],'actions[value][threadid]', 	(isset($action['value']['thread']) ? $action['value']['thread'] : ''), true, 10, 10);
			
				if (!empty($action['value']['threadid']))
				{
					$thread = REGISTRATION::$db->fetchRow('
						SELECT open, sticky FROM $thread
						WHERE threadid = ' . (int)$action['value']['threadid']
					);
				}
			}

			print_table_break();
	
			print_table_header($vbphrase['options']);
			print_checkbox_row($vbphrase['dbtech_registration_show_signature'],		'actions[options][signature]',		(isset($action['options']) AND $action['options'] & 1 ? true : false), 1, $vbphrase['dbtech_registration_show_signature_explain']);
			print_checkbox_row($vbphrase['disable_smilies_in_text'],				'actions[options][disablesmilies]',	(isset($action['options']) AND $action['options'] & 2 ? true : false), 1, $vbphrase['disable_smilies_in_text_explain']);
			
			// Redo this part to radio open/close, sticky/unsticky
			print_checkbox_row((empty($thread['sticky'])	? $vbphrase['stick_this_thread']	: $vbphrase['unstick_this_thread']),	'actions[options][stickunstick]',	(isset($action['options']) AND ($action['options'] & 4) ? true : false), 1, $vbphrase['yes']);
			print_checkbox_row((empty($thread['open']) 		? $vbphrase['open_thread'] 			: $vbphrase['close_this_thread']),		'actions[options][openclose]',		(isset($action['options']) AND ($action['options'] & 8) ? true : false), 1, $vbphrase['yes']);
			break;
	}
		
	construct_hidden_code('actions[type]', $action['type']);

	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'actionid' 	=> TYPE_UINT,
		'actions' 	=> TYPE_ARRAY # To avoid action=update conflict
	));

	$dm =& REGISTRATION::initDataManager('action', $vbulletin, ERRTYPE_CP);

	$dm->set_info('instances', $vbulletin->GPC['actions']['instances']);
	unset($vbulletin->GPC['actions']['instances']);
	
	// set existing info if this is an update
	if ($vbulletin->GPC['actionid'])
	{
		if (!$existing = REGISTRATION::$cache['action'][$vbulletin->GPC['actionid']])
		{
			// Couldn't find the action
			print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_action'], $vbulletin->GPC['actionid']);
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

	// action fields
	foreach ($vbulletin->GPC['actions'] AS $key => $val)
	{
		if (!isset($vbulletin->GPC['actionid']) OR $existing[$key] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	$dm->save();

	define('CP_REDIRECT', 'registration.php?do=action');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_action'], $phrase);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'process')
{
	$vbulletin->input->clean_gpc('p', 'active', TYPE_ARRAY_BOOL);

	if (is_array($vbulletin->GPC['active']))
	{
		foreach($vbulletin->GPC['active'] AS $actionid => $active)
		{
			// set existing info
			if (!$existing = REGISTRATION::$cache['action'][$actionid])
			{
				// Couldn't find the action
				continue;
			}
			
			if ($existing['active'] == $active)
			{
				// We don't need to call the dm
				continue;
			}

			// init datamanager
			$dm =& REGISTRATION::initDataManager('action', $vbulletin, ERRTYPE_CP);
		
				// Set existing
				$dm->set_existing($existing);

				// Set active flag
				$dm->set('active', $active);

			$dm->save();
		}	
	}

	define('CP_REDIRECT', 'registration.php?do=action');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_actions'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}


// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'actionid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['action'][$vbulletin->GPC['actionid']])
	{
		// couldn't find the action
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_action'], $vbulletin->GPC['actionid']);
	}

	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_action']));
	print_delete_confirmation('dbtech_registration_action', $vbulletin->GPC['actionid'], 'registration', 'action', 'dbtech_registration_action', array('action' => 'kill'), '', 'title', 'actionid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'actionid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['action'][$vbulletin->GPC['actionid']])
	{
		// couldn't find the action
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_action'], $vbulletin->GPC['actionid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('action', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=action');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_action'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}
?>