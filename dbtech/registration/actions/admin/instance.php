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
    print_cp_header($vbphrase['dbtech_registration_instance_management']);

	if (empty(REGISTRATION::$cache['instance']))
	{
		// No instances
		print_stop_message('dbtech_registration_no_x_add_y', $vbphrase['dbtech_registration_instances'], strtolower($vbphrase['dbtech_registration_instance']));
	}
	
	$headings = array(
		$vbphrase['title'],
		$vbphrase['dbtech_registration_priority'],
		$vbphrase['dbtech_registration_criterias'],
		$vbphrase['dbtech_registration_sections'] . ' / ' . $vbphrase['dbtech_registration_fields'],
		$vbphrase['dbtech_registration_actions'],
		$vbphrase['options']
	);
	
	$headings_count = count($headings);
	
	print_form_header('registration', 'instance');
		construct_hidden_code('action', 'process');
		print_table_header($vbphrase['dbtech_registration_instances'], count($headings));
		print_cells_row($headings, true, false, 0);

		$types = array();
		// Redo this entire shit doesn't respect displayorders
		foreach (array(
			'criteria',
			'field',
			'action'
		) AS $type)
		{
			// Ini the types array
			$types[$type] = array();

			// Fetch types
			$results = REGISTRATION::$db->fetchAll('
				SELECT * FROM $dbtech_registration_instance_' . $type . '
				# Display orders? ' . (in_array($type, array('field', 'section')) ? 'ORDER BY displayorder ASC' : '')
			);
			
			if (empty($results))
			{
				// No results for this type
				continue;
			}
			
			foreach ($results AS $result)
			{
				// Set title
				if ($type == 'field')
				{
					// There's a  problem with multiple sections with same title
					
					$types[$type][(int)$result['instanceid']]
						[isset(REGISTRATION::$cache['section'][$result['sectionid']]['title']) ? REGISTRATION::$cache['section'][$result['sectionid']]['title'] : $vbphrase['n_a']]
																[$result[$type . 'id']] = REGISTRATION::$cache[$type][$result[$type . 'id']]['title'];
				}
				else
				{
					$types[$type][(int)$result['instanceid']][$result[$type . 'id']] = REGISTRATION::$cache[$type][$result[$type . 'id']]['title'];
				}
			}
		}

		// Get rid of old data
		unset($results);

		?>
		<script type="text/javascript">
		function js_jump(id, val)
		{
			var value = eval("document.cpform." + id + val + ".options[document.cpform." + id + val + ".selectedIndex].value");
			if (value != "")
			{
				window.location = "registration.php?<?php echo $vbulletin->session->vars['sessionurl_js']; ?>do=" + id +"&action=modify&" + id +"id=" + value;
			}
		}
		
		function js_jump_two(instanceid)
		{
			var action = eval("document.cpform.edit" + instanceid + ".options[document.cpform.edit" + instanceid + ".selectedIndex].value");
			if (action != "")
			{
				window.location = "registration.php?<?php echo $vbulletin->session->vars['sessionurl_js']; ?>do=instance&action=" + action + "&instanceid=" + instanceid;
			}
		}
		</script>
		<?php

		foreach (REGISTRATION::$cache['instance'] AS $instanceid => $instance)
		{
			$cells = array();

			$cells[] = $instance['title'];
			$cells[] = '<input type="text" class="bginput" name="priority[' . $instanceid . ']" value="' . $instance['priority'] . '" tabindex="1" size="3" title="' . $vbphrase['edit_display_order'] . '" />';	

			foreach ($types AS $type => $instances)
			{
				if (empty($instances[$instanceid]))
				{
					// This instance doesn't have any associated types
					$cells[] = $vbphrase['n_a'];
					
					continue;
				}

				$cells[] = '
					<select id="' . $type . $instanceid . '" onchange="js_jump(\'' . $type . '\', ' . $instanceid . ');" class="bginput">
						' . construct_select_options($instances[$instanceid], '', true) . '
					</select>
					<input type="button" class="button" value="' . $vbphrase['go'] . '" onclick="js_jump(\'' . $type . '\', ' . $instanceid . ');" />
				';
			}
			
			$cells[] = '
				<select id="edit' . $instanceid . '" onchange="js_jump_two(' . $instanceid . ');" class="bginput">
					' . construct_select_options(array(
						'modify'			=> $vbphrase['view'] . ' / ' .$vbphrase['edit'],
						'delete'			=> $vbphrase['delete'],
					), '', true) . '
				</select>
				<input type="button" class="button" value="' . $vbphrase['go'] . '" onclick="js_jump_two(' . $instanceid . ');" />
			';

			print_cells_row($cells, false, false, 0);
		}

	print_submit_row($vbphrase['dbtech_registration_save_priority'], false, $headings_count, false, '<input type="button" id="addnew" class="button" value="' . construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_instance']) . '" tabindex="1" onclick="window.location=\'registration.php?do=instance&amp;action=modify\'" />');
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$instanceid	= $vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);
	$instance	= (!empty($instanceid) ? REGISTRATION::$cache['instance'][$instanceid] : false);

	$defaults = array(
		'title' 	=> '',
		'priority'	=> '10',
		'daily_max' => 0
	);
	
	$instance_defaults = array(
		'criteria'	=> array('active' 		=> true, 'required'		=> false),
		'section'	=> array('active' 		=> true, 'displayorder'	=> 0),
		'field'		=> array('active' 		=> true, 'sectionid' 	=> 1, 'displayorder'	=> 0),
		'action'	=> array('active' 		=> true)
	);
	
	if ($instance)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_instance'], $instance['title'])));
		print_form_header('registration', 'instance');
		construct_hidden_code('action', 'update');
		construct_hidden_code('instanceid', $instanceid);
		print_table_header(construct_phrase($vbphrase['dbtech_registration_editing_x_y'], $vbphrase['dbtech_registration_instance'], $instance['title']));
		
		$phrase = $vbphrase['save'];
	}
	else
	{
		// Add
		print_cp_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_instance']));
		print_form_header('registration', 'instance');
		construct_hidden_code('action', 'update');
		print_table_header(construct_phrase($vbphrase['dbtech_registration_add_new_x'], $vbphrase['dbtech_registration_instance']));
		
		$phrase = $vbphrase['add'];

		$instance = $defaults;
	}
	
	print_input_row($vbphrase['title'], 										'instance[title]', 		$instance['title']);
	print_input_row($vbphrase['dbtech_registration_priority'], 					'instance[priority]', 	$instance['priority']);
	print_input_row($vbphrase['dbtech_registration_daily_max_registrations'],	'instance[daily_max]', 	$instance['daily_max']);
																
	print_table_break();
	
	$types = array(
		'criteria'		=> 'criteriaid',
		'section'		=> 'displayorder',
		'field'			=> 'displayorder',
		'action'		=> 'actionid'
	);
	
	foreach ($types AS $type => $order)
	{
		if ($instance)
		{
			$instances[$type] = REGISTRATION::$db->fetchAllKeyed('
				SELECT *, 1 AS used FROM $dbtech_registration_instance_' . $type . '
				WHERE instanceid = ' . $instanceid . '
				ORDER BY ' . $order . ' ASC'
				, $type . 'id'
			);
		}

		// Define headings
		switch ($type)
		{
			case 'criteria':
				$headings = array(
					$vbphrase['title'],
					$vbphrase['active'],
					$vbphrase['dbtech_registration_required'],
					$vbphrase['dbtech_registration_use']
				);
				break;
			case 'section':
				$headings = array(
					$vbphrase['title'],
					$vbphrase['display_order'],
					$vbphrase['active'],
					$vbphrase['dbtech_registration_use']
				);
				break;
			case 'field':
				$headings = array(
					$vbphrase['title'],
					$vbphrase['dbtech_registration_section'],
					$vbphrase['display_order'],
					$vbphrase['active'],
					$vbphrase['dbtech_registration_use']
				);
				
				$sections = array();
				foreach ((array)REGISTRATION::$cache['section'] AS $sectionid => $section)
				{
					// Set title for use in construct_select
					$sections[$sectionid] = $section['title'];
				}
				break;
			case 'action':
				$headings = array(
					$vbphrase['title'],
					$vbphrase['active'],
					$vbphrase['dbtech_registration_use']
				);
				break;
		}

		// Add unused types, preserve order by, ???, PROFIT!!!
		$instances[$type] = $instances[$type] + REGISTRATION::$cache[$type];

		print_table_header($vbphrase['dbtech_registration_' . $type . 's'], count($headings));
		print_cells_row($headings, true, false, 0);
		foreach ($instances[$type] AS $key => $fields)
		{
			if (!is_array(REGISTRATION::$cache[$type][$key]))
			{
				// Deleted field
				continue;
			}

			// Populate the fields with values unchanged per-instance
			$fields = $fields + REGISTRATION::$cache[$type][$key];

			if (empty($fields['used']))
			{
				// Assign defaults
				$fields = $instance_defaults[$type] + $fields;

				// Since this doesn't exist clarify that it isn't used
				$fields['used'] = false;
			}
		
			$cells = array();

			// Set title [normalizing fields 'varname' to title. It's not phrased so it's a title. Go back later and phrase everything and change to a varname if you want]
			$cells[] = $fields['title'];

			// Select boxes
			if (isset($instance_defaults[$type]['sectionid']))
			{
				// Set section select
				$cells[] = '
				<select class="bginput" name="instance[types][' . $type . '][' . $key . '][sectionid]"' . ($vbulletin->debug ? ' title="name=&quot;instance[types][' . $type . '][' . $key . '][sectionid]&quot;"' : '') . '>
					' . construct_select_options($sections, $fields['sectionid'], true) . '
				</select>';
			}
			
			if (isset($instance_defaults[$type]['displayorder']))
			{
				$cells[] = '<input type="text" size="3" class="bginput" value="' . $fields['displayorder'] . '" name="instance[types][' . $type . '][' . $key . '][displayorder]"' . ($vbulletin->debug ? ' title="name=&quot;instance[types][' . $type . '][' . $key . '][sectionid]&quot;"' : '') . '/>';
			}
			
			// Checkboxes
			foreach (array(
				'active'	=> $fields['active'],
				'required'	=> $fields['required'],
				'used'		=> $fields['used']
			) AS $permtitle => $value)
			{
				if (!isset($value))
				{
					// Key doesn't exist for this type
					continue;
				}
			
				$cells[] = '	
				<input type="hidden" name="instance[types][' . $type . '][' . $key . '][' . $permtitle . ']" value="0" />
				<input type="checkbox" name="instance[types][' . $type . '][' . $key . '][' . $permtitle . ']" value="1"' . ($value ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;instance[types][' . $type . '][' . $key . '][' . $permtitle . ']&quot;"' : '') . '/>';
			}

			print_cells_row($cells, false, false, 0);
		}

		print_table_break();
	}

	print_submit_row($phrase, false);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	// Grab stuff
	$vbulletin->input->clean_array_gpc('p', array(
		'instanceid'	=> TYPE_UINT,
		'instance' 		=> TYPE_ARRAY
	));

	if (empty($vbulletin->GPC['instance']['value']))
	{
		// dub data manager doesn't allow emptry strings
		$vbulletin->GPC['instance']['value'] = 0;
	}
	
	$dm =& REGISTRATION::initDataManager('instance', $vbulletin, ERRTYPE_CP);

	$dm->set_info('types', $vbulletin->GPC['instance']['types']);
	unset($vbulletin->GPC['instance']['types']);

	// set existing info if this is an update
	if ($vbulletin->GPC['instanceid'])
	{
		if (!$existing = REGISTRATION::$cache['instance'][$vbulletin->GPC['instanceid']])
		{
			// Couldn't find the instanceid
			print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_instance'], $vbulletin->GPC['criteriaid']);
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
	foreach ($vbulletin->GPC['instance'] AS $key => $val)
	{
		if (!isset($vbulletin->GPC['instanceid']) OR $existing[$key] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	$dm->save();

	define('CP_REDIRECT', 'registration.php?do=instance');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_instance'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'instanceid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['instance'][$vbulletin->GPC['instanceid']])
	{
		// couldn't find the instance
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_instance'], $vbulletin->GPC['instanceid']);
	}

	print_cp_header(construct_phrase($vbphrase['dbtech_registration_x_y'], $vbphrase['delete'], $vbphrase['dbtech_registration_instance']));
	print_delete_confirmation('dbtech_registration_instance', $vbulletin->GPC['instanceid'], 'registration', 'instance', 'dbtech_registration_instance', array('action' => 'kill'), '', 'title', 'instanceid');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_gpc('p', 'instanceid', TYPE_UINT);

	if (!$existing = REGISTRATION::$cache['instance'][$vbulletin->GPC['instanceid']])
	{
		// couldn't find the instance
		print_stop_message('dbtech_registration_invalid_x', $vbphrase['dbtech_registration_instance'], $vbulletin->GPC['instanceid']);
	}
	
	// init data manager
	$dm =& REGISTRATION::initDataManager('instance', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();
	
	define('CP_REDIRECT', 'registration.php?do=instance');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_instance'], $vbphrase['dbtech_registration_deleted']);
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'process')
{
	$vbulletin->input->clean_gpc('p', 'priority', TYPE_ARRAY_UINT);

	if (is_array($vbulletin->GPC['priority']))
	{
		foreach($vbulletin->GPC['priority'] AS $instanceid => $priority)
		{
			// set existing info
			if (!$existing = REGISTRATION::$cache['instance'][$instanceid])
			{
				// Couldn't find the instance
				continue;
			}
			
			if ($existing['priority'] == $priority)
			{
				// We don't need to call the dm
				continue;
			}

			// init datamanager
			$dm =& REGISTRATION::initDataManager('instance', $vbulletin, ERRTYPE_CP);
		
				// Set existing
				$dm->set_existing($existing);

				// Set priority
				$dm->set('priority', $priority);

			$dm->save();
		}	
	}

	define('CP_REDIRECT', 'registration.php?do=instance');
	print_stop_message('dbtech_registration_x_y', $vbphrase['dbtech_registration_instances'], $vbphrase['dbtech_registration_edited']);
	print_cp_footer();
}
?>