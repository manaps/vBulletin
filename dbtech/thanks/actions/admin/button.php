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

// #############################################################################
if ($_REQUEST['action'] == 'button' OR empty($_REQUEST['action']))
{
	print_cp_header($vbphrase['dbtech_thanks_button_management']);

	// Table header
	$headings = array();
	$headings[] = $vbphrase['title'];
	$headings[] = $vbphrase['varname'];
	$headings[] = $vbphrase['description'];
	$headings[] = $vbphrase['active'];
	$headings[] = $vbphrase['display_order'];
	$headings[] = preg_replace('/<dfn>.*<\/dfn>/isU', '', $vbphrase['dbtech_thanks_action_text']);
	/*DBTECH_PRO_START*/$headings[] = preg_replace('/<dfn>.*<\/dfn>/isU', '', $vbphrase['dbtech_thanks_undo_text']);/*DBTECH_PRO_END*/
	$headings[] = $vbphrase['reputation'];
	$headings[] = $vbphrase['edit'];
	/*DBTECH_PRO_START*/$headings[] = $vbphrase['delete'];/*DBTECH_PRO_END*/


	if (count(THANKS::$cache['button']))
	{
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'massupdate');
		print_table_header($vbphrase['dbtech_thanks_button_management'], count($headings));
		print_cells_row($headings, 0, 'thead');

		foreach (THANKS::$cache['button'] as $buttonid => $button)
		{
			// Table data
			$cell = array();
			$cell[] = $button['title'];
			$cell[] = $button['varname'];
			$cell[] = $button['description'];
			$cell[] = "<input type=\"hidden\" name=\"button[$buttonid][active]\" value=\"0\" /><input type=\"checkbox\" class=\"bginput\" name=\"button[$buttonid][active]\" value=\"1\"" . ($button['active'] ? ' checked="checked"' : '') . " tabindex=\"1\" title=\"" . $vbphrase['active'] . "\" />";
			$cell[] = "<input type=\"text\" class=\"bginput\" name=\"button[$buttonid][displayorder]\" value=\"$button[displayorder]\" tabindex=\"1\" size=\"3\" title=\"" . $vbphrase['edit_display_order'] . "\" />";
			$cell[] = $button['actiontext'];
			/*DBTECH_PRO_START*/$cell[] = $button['undotext'];/*DBTECH_PRO_END*/
			$cell[] = $button['reputation'];
			$cell[] = construct_link_code($vbphrase['edit'], 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=button&amp;action=modify&amp;buttonid=' . $buttonid);
			/*DBTECH_PRO_START*/ $cell[] = construct_link_code($vbphrase['delete'], 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=button&amp;action=delete&amp;buttonid=' . $buttonid);/*DBTECH_PRO_END*/

			// Print the data
			print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
		}

		/*DBTECH_PRO_START*/
		print_submit_row($vbphrase['save_display_order'], false, count($headings), false, construct_button_code($vbphrase['dbtech_thanks_add_new_button'], 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=button&action=modify'));
		/*DBTECH_PRO_END*/
		/*DBTECH_LITE_START
		print_table_footer();
		DBTECH_LITE_END*/
	}
	else
	{
		/*DBTECH_PRO_START*/
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'modify');
		print_table_header($vbphrase['dbtech_thanks_button_management'], count($headings));
		print_description_row($vbphrase['dbtech_thanks_no_buttons'], false, count($headings));
		print_submit_row($vbphrase['dbtech_thanks_add_new_button'], false, count($headings));
		/*DBTECH_PRO_END*/
		/*DBTECH_LITE_START
		print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		DBTECH_LITE_END*/
	}
}

// #############################################################################
if ($_REQUEST['action'] == 'modify')
{
	$buttonid = $vbulletin->input->clean_gpc('r', 'buttonid', TYPE_UINT);
	$button = ($buttonid ? THANKS::$cache['button']["$buttonid"] : false);

	if (!is_array($button))
	{
		// Non-existing button
		$buttonid = 0;
	}

	$defaults = array(
		'varname'		=> 'recommends',
		'title' 		=> 'Recommend',
		'description' 	=> '"Recommend" this post.',
		'displayorder' 	=> 10,
		'active' 		=> 1,
		'actiontext' 	=> 'Recommend this post',
		'listtext' 		=> 'recommended this post',
		'undotext' 		=> 'Unrecommend',
		'minposts' 		=> 0,
		'clicksperday' 	=> 0,
		'reputation'	=> 1,
		'postfont' 		=> array(
			1 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			2 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			3 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			4 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
			5 => array(
				'threshold' => '',
				'color' 	=> '',
				'settings' 	=> 12,
			),
		),
	);

	$colours = array(
		'' 			=> '',

		// sRGB colours
		'White' 	=> 'White',
		'Silver' 	=> 'Silver',
		'Gray' 		=> 'Gray',
		'Black' 	=> 'Black',
		'Red' 		=> 'Red',
		'Maroon' 	=> 'Maroon',
		'Yellow' 	=> 'Yellow',
		'Olive' 	=> 'Olive',
		'Lime' 		=> 'Lime',
		'Green' 	=> 'Green',
		'Aqua' 		=> 'Aqua',
		'Teal' 		=> 'Teal',
		'Blue' 		=> 'Blue',
		'Navy' 		=> 'Navy',
		'Fuchsia' 	=> 'Fuchsia',
		'Purple' 	=> 'Purple',
	);

	if ($buttonid)
	{
		// Edit
		print_cp_header(strip_tags(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $button['title'])));
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'update');
		construct_hidden_code('buttonid', $buttonid);
		print_table_header(construct_phrase($vbphrase['dbtech_thanks_editing_x_y'], $vbphrase['dbtech_thanks_button'], $button['title']));

		$vbphrase['dbtech_thanks_title']  		= $vbphrase['title'] . construct_phrase($vbphrase['dbtech_thanks_title_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_action_text']  = $vbphrase['dbtech_thanks_action_text'] . construct_phrase($vbphrase['dbtech_thanks_action_text_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_list_text']  	= $vbphrase['dbtech_thanks_list_text'] . construct_phrase($vbphrase['dbtech_thanks_list_text_translation'], $button['varname']);
		$vbphrase['dbtech_thanks_undo_text']  	= $vbphrase['dbtech_thanks_undo_text'] . construct_phrase($vbphrase['dbtech_thanks_undo_text_translation'], $button['varname']);
	}
	else
	{
		/*DBTECH_PRO_START*/
		// Add
		print_cp_header($vbphrase['dbtech_thanks_add_new_button']);
		print_form_header('thanks', 'button');
		construct_hidden_code('action', 'update');
		print_table_header($vbphrase['dbtech_thanks_add_new_button']);

		$button = $defaults;

		$vbphrase['dbtech_thanks_title']  		= $vbphrase['title'];
		/*DBTECH_PRO_END*/
		/*DBTECH_LITE_START
		print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		DBTECH_LITE_END*/
	}

	print_description_row($vbphrase['dbtech_thanks_main_settings'], false, 2, 'optiontitle');
	if ($buttonid)
	{
		construct_hidden_code('button[varname]', 																											$button['varname']);
		print_label_row($vbphrase['varname'], 																												$button['varname']);
	}
	else
	{
		print_input_row($vbphrase['varname'], 									'button[varname]', 															$button['varname']);
	}
	print_input_row($vbphrase['dbtech_thanks_title'], 							'button[title]', 															$button['title']);
	print_textarea_row($vbphrase['description'],								'button[description]',														$button['description']);
	print_input_row($vbphrase['display_order'], 								'button[displayorder]', 													$button['displayorder']);
	print_yes_no_row($vbphrase['active'],										'button[active]',															$button['active']);
	print_description_row($vbphrase['dbtech_thanks_button_settings'], false, 2, 'optiontitle');
	print_input_row($vbphrase['dbtech_thanks_image'], 							'button[image]', 															$button['image']);
	/*DBTECH_PRO_START*/
	print_input_row($vbphrase['dbtech_thanks_image_unclick'], 					'button[image_unclick]', 													$button['image_unclick']);
	/*DBTECH_PRO_END*/
	print_textarea_row($vbphrase['dbtech_thanks_action_text'], 					'button[actiontext]', 														$button['actiontext']);
	print_textarea_row($vbphrase['dbtech_thanks_list_text'], 					'button[listtext]', 														$button['listtext']);
	/*DBTECH_PRO_START*/
	print_textarea_row($vbphrase['dbtech_thanks_undo_text'], 					'button[undotext]', 														$button['undotext']);
	print_input_row($vbphrase['dbtech_thanks_min_posts'], 						'button[minposts]', 														$button['minposts']);
	print_input_row($vbphrase['dbtech_thanks_click_limit'], 					'button[clicksperday]', 													$button['clicksperday']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_notifs'],					'button[disablenotifs]',													$button['disablenotifs']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_emails'],					'button[disableemails]',													$button['disableemails']);
	print_yes_no_row($vbphrase['dbtech_thanks_enable_bump'],					'button[enablebump]',														$button['enablebump']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_click_count'],			'button[disableclickcount]',												$button['disableclickcount']);
	/*DBTECH_PRO_END*/
	print_input_row($vbphrase['reputation'], 									'button[reputation]', 														$button['reputation']);
	print_yes_no_row($vbphrase['dbtech_thanks_is_positive_rating'],				'button[ispositive]',														$button['ispositive']);
	print_yes_no_row($vbphrase['dbtech_thanks_default_button_attach'],			'button[defaultbutton_attach]',												$button['defaultbutton_attach']);
	print_yes_no_row($vbphrase['dbtech_thanks_default_button_content'],			'button[defaultbutton_content]',											$button['defaultbutton_content']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_stats_given'],			'button[disablestats_given]',												$button['disablestats_given']);
	print_yes_no_row($vbphrase['dbtech_thanks_disable_stats_received'],			'button[disablestats_received]',											$button['disablestats_received']);
	THANKS::bitfieldRow($vbphrase['dbtech_thanks_post_disabled_integration'], 	'button[disableintegration]', 'nocache|dbtech_thanks_disable_integration', 	$button['disableintegration']);
	/*DBTECH_PRO_START*/
	foreach ($defaults['postfont'] as $key => $value)
	{
		print_description_row($vbphrase['dbtech_thanks_colour_settings'], false, 2, 'optiontitle');
		// Stfu PHP
		$button['postfont'][$key] = ($button['postfont'][$key] ? $button['postfont'][$key] : $defaults['postfont'][$key]);

		print_label_row($vbphrase['dbtech_thanks_post_font'], 	'
			<table>
				<tr>
					<td class="smallfont">' . $vbphrase['dbtech_thanks_post_font_threshold'] . ':</td>
					<td><input size="8" type="text" class="bginput" name="button[postfont][' . $key . '][threshold]" value="' . intval($button['postfont'][$key]['threshold']) . '" tabindex="1" /></td>
				</tr>
				<tr>
					<td class="smallfont">' . $vbphrase['dbtech_thanks_post_font_color'] . ':</td>
					<td><select name="button[postfont][' . $key . '][color]" tabindex="1" class="bginput">' . construct_select_options($colours, $button['postfont'][$key]['color'], false) . '</select></td>
				</tr>
			</table>
		');
		if (intval($vbulletin->versionnumber) > 3) THANKS::bitfieldRow($vbphrase['dbtech_thanks_post_font_display_settings'], 'button[postfont][' . $key . '][settings]', 'nocache|dbtech_thanks_postfont_settings', $button['postfont'][$key]['settings']);
	}
	/*DBTECH_PRO_END*/

	print_table_break();

	// Table header
	$headings = array();
	$headings[] = '<label><input type="checkbox" rel="^-button[permissions]" />' . $vbphrase['usergroup'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[canclick]" />' . $vbphrase['dbtech_thanks_can_click'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[canreqclick]" />' . $vbphrase['dbtech_thanks_can_require_click'] . '</label>';
	/*DBTECH_PRO_START*/
	$headings[] = '<label><input type="checkbox" rel="*-[candisableclick]" />' . $vbphrase['dbtech_thanks_can_disable_click'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[canunclick]" />' . $vbphrase['dbtech_thanks_can_unclick'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[cannotseeclicks]" />' . $vbphrase['dbtech_thanks_cannot_see_clicks'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[immune]" />' . $vbphrase['dbtech_thanks_is_immune'] . '</label>';
	$headings[] = '<label><input type="checkbox" rel="*-[bypassreq]" />' . $vbphrase['dbtech_thanks_can_bypass_req'] . '</label>';
	/*DBTECH_PRO_END*/

	$cells = array();
	$cells[] = 'canclick';
	$cells[] = 'canreqclick';
	/*DBTECH_PRO_START*/
	$cells[] = 'candisableclick';
	$cells[] = 'canunclick';
	$cells[] = 'cannotseeclicks';
	$cells[] = 'immune';
	$cells[] = 'bypassreq';
	/*DBTECH_PRO_END*/

	print_table_header($vbphrase['dbtech_thanks_permissions'], count($headings));
	print_cells_row($headings, 0, 'thead');
	foreach ($vbulletin->usergroupcache as $usergroupid => $usergroup)
	{
		// Table data
		$cell = array();
		$cell[] = '<label><input type="checkbox" rel="^-button[permissions][' . $usergroupid . ']" />' . $usergroup['title'] . '</label>';
		foreach ($cells as $permtitle)
		{
			$cell[] = '<center>
				<input type="hidden" name="button[permissions][' . $usergroupid . '][' . $permtitle . ']" value="0" />
				<input type="checkbox" name="button[permissions][' . $usergroupid . '][' . $permtitle . ']" value="1"' . ($button['permissions'][$usergroupid][$permtitle] ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;button[permissions][' . $usergroupid . '][' . $permtitle . ']&quot;"' : '') . '/>
			</center>';
		}

		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_break();

	// Table header
	$headings = array();
	$headings[] = $vbphrase['dbtech_thanks_button'];
	$headings[] = $vbphrase['dbtech_thanks_is_exclusive'];

	print_table_header($vbphrase['dbtech_thanks_button_exclusivity'], count($headings));
	print_description_row($vbphrase['dbtech_thanks_button_exclusivity_descr'], false, count($headings));
	print_cells_row($headings, 0, 'thead');
	foreach (THANKS::$cache['button'] as $button_id => $button_info)
	{
		if ($button_id == $buttonid)
		{
			// Can't set to own button, lol
			continue;
		}

		// Table data
		$cell = array();
		$cell[] = $button_info['title'];
		$cell[] = '<center>
			<input type="hidden" name="button[exclusivity][' . $button_id . ']" value="0" />
			<input type="checkbox" name="button[exclusivity][' . $button_id . ']" value="' . $button_info['bitfield'] . '"' . (((int)$button['exclusivity'] & (int)$button_info['bitfield']) ? ' checked="checked"' : '') . ($vbulletin->debug ? ' title="name=&quot;button[exclusivity][' . $button_id . ']&quot;"' : '') . '/>
		</center>';

		// Print the data
		print_cells_row($cell, 0, 0, -5, 'middle', 0, 1);
	}
	print_table_break();
	print_submit_row(($buttonid ? $vbphrase['save'] : $vbphrase['dbtech_thanks_add_new_button']), $vbphrase['reset'], count($headings));
	echo '<script type="text/javascript"> window.jQuery || document.write(\'<script src="' . THANKS::jQueryPath() . '">\x3C/script>\'); </script>';
	THANKS::js('_admin');
}

// #############################################################################
if ($_POST['action'] == 'update')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'buttonid' 	=> TYPE_UINT,
		'button' 	=> TYPE_ARRAY,
	));

	/*DBTECH_PRO_START*/
	if (intval($vbulletin->versionnumber) > 3)
	{
		$postfont = $vbulletin->GPC['button']['postfont'];
		foreach ($postfont as $key => $arr)
		{
			$vbulletin->GPC['button']['postfont'][$key]['settings'] = 0;
			foreach ($arr['settings'] as $onoff)
			{
				// Convert to int
				$vbulletin->GPC['button']['postfont'][$key]['settings'] += $onoff;
			}
		}
	}

	$disableintegration = $vbulletin->GPC['button']['disableintegration'];
	$vbulletin->GPC['button']['disableintegration'] = 0;
	foreach ($disableintegration as $onoff)
	{
		// Convert to int
		$vbulletin->GPC['button']['disableintegration'] += $onoff;
	}
	/*DBTECH_PRO_END*/

	// init data manager
	$dm =& THANKS::initDataManager('Button', $vbulletin, ERRTYPE_CP);

	// set existing info if this is an update
	if ($vbulletin->GPC['buttonid'])
	{
		if (!$existing = THANKS::$cache['button'][$vbulletin->GPC['buttonid']])
		{
			// Couldn't find the button
			print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], $vbulletin->GPC['buttonid']);
		}

		// Set existing
		$dm->set_existing($existing);

		// Added
		$phrase = $vbphrase['dbtech_thanks_edited'];
	}
	else
	{
		/*DBTECH_PRO_START*/
		// Added
		$phrase = $vbphrase['dbtech_thanks_added'];
		/*DBTECH_PRO_END*/
		/*DBTECH_LITE_START
		print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], 0);
		DBTECH_LITE_END*/
	}

	// button fields
	foreach ($vbulletin->GPC['button'] AS $key => $val)
	{
		if (!$vbulletin->GPC['buttonid'] OR $existing["$key"] != $val)
		{
			// Only set changed values
			$dm->set($key, $val);
		}
	}

	// Save! Hopefully.
	$dm->save();

	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_button'], $phrase);
}

// #############################################################################
if ($_POST['action'] == 'massupdate')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'button' 		=> TYPE_ARRAY,
	));

	foreach ($vbulletin->GPC['button'] AS $buttonid => $data)
	{
		if (!$existing = THANKS::$cache['button'][$buttonid])
		{
			// Couldn't find the button
			continue;
		}

		// init data manager
		$dm =& THANKS::initDatamanager('Button', $vbulletin, ERRTYPE_CP);
			$dm->set_existing($existing);

		// button fields
		foreach ($data AS $key => $val)
		{
			if ($existing[$key] != $val)
			{
				// Only set changed values
				$dm->set($key, $val);
			}
		}

		// Save! Hopefully.
		$dm->save();
		unset($dm);
	}

	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_button'], $vbphrase['dbtech_thanks_edited']);
}

// #############################################################################
if ($_REQUEST['action'] == 'delete')
{
	$vbulletin->input->clean_gpc('r', 'buttonid', TYPE_UINT);

	print_cp_header(construct_phrase($vbphrase['dbtech_thanks_delete_x'], $vbphrase['dbtech_thanks_button']));
	print_delete_confirmation('dbtech_thanks_button', $vbulletin->GPC['buttonid'], 'thanks', 'button', 'dbtech_thanks_button', array('action' => 'kill'), '', 'title');
	print_cp_footer();
}

// #############################################################################
if ($_POST['action'] == 'kill')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'buttonid' 	=> TYPE_UINT,
		'kill' 		=> TYPE_BOOL
	));

	if (!$existing = THANKS::$cache['button'][$vbulletin->GPC['buttonid']])
	{
		// Couldn't find the button
		print_stop_message('dbtech_thanks_invalid_x', $vbphrase['dbtech_thanks_button'], $vbulletin->GPC['buttonid']);
	}

	// init data manager
	$dm =& THANKS::initDataManager('Button', $vbulletin, ERRTYPE_CP);
		$dm->set_existing($existing);
	$dm->delete();

	define('CP_REDIRECT', 'thanks.php?do=button');
	print_stop_message('dbtech_thanks_x_y', $vbphrase['dbtech_thanks_button'], $vbphrase['dbtech_thanks_deleted']);
}


print_cp_footer();