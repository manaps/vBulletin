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

if (!$vbulletin->userinfo['userid'])
{
	// Ensure guests can't access
	print_no_permission();
}

// ######################### REQUIRE BACK-END ############################
require_once(DIR . '/includes/functions_user.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

// ############################### start display options ###############################
if ($_REQUEST['action'] == 'options')
{
	// Navigation bits
	$navbits[''] = $vbphrase['dbtech_thanks_settings'];
	
	// Grab all the bitfields we can
	require_once(DIR . '/includes/class_bitfield_builder.php');
	$bitfields = vB_Bitfield_Builder::return_data();
	
	// Begin the array of options
	$optionlist = array();
	
	if (!$vbulletin->options['dbtech_thanks_emaildefault'])
	{
		$vbphrase['dbtech_thanks_disableemails'] 		= $vbphrase['dbtech_thanks_enableemails'];
		$vbphrase['dbtech_thanks_disableemails_descr'] 	= $vbphrase['dbtech_thanks_enableemails_descr'];
	}
	
	foreach (array(
		'dbtech_thanks_notification_settings' 	=> $bitfields['nocache']['dbtech_thanks_notification_settings'],
	) as $settinggroup => $settings)
	{
		// Begin settings
		$optionlist[$settinggroup] = array();
		
		foreach ($settings as $settingname => $bit)
		{
			$optionlist[$settinggroup][] = array(
				'varname'		=> $settingname,
				'description' 	=> $vbphrase[$settingname . '_descr'],
				'checked'		=> ((intval($vbulletin->userinfo['dbtech_thanks_settings2']) & $bit) ? ' checked="checked"' : ''),
				'settingphrase'	=> $vbphrase[$settingname],
				'phrase'		=> $vbphrase[$settingname . '_short'],
			);
		}
	}	
	
	foreach (THANKS::$cache['button'] as $buttonid => $button)
	{
		if (!$button['active'])
		{
			// Inactive button
			continue;
		}
		
		$optionlist['dbtech_thanks_postbit_settings'][] = array(
			'varname'		=> $button['varname'],
			'description' 	=> $vbphrase['dbtech_thanks_enable_button_stats_descr'],
			'checked'		=> (((int)$vbulletin->userinfo['dbtech_thanks_settings'] & (int)$button['bitfield']) ? ' checked="checked"' : ''),
			'settingphrase'	=> $vbphrase['dbtech_thanks_enable_button_stats'],
			'phrase'		=> $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'],
		);
	}
	
	foreach ($optionlist as $headerphrase => $options)
	{
		$optionbits2 = '';
		foreach ($options as $option)
		{
			$templater = vB_Template::create('dbtech_thanks_options_bit_bit');
				$templater->register('option', $option);
			$optionbits2 .= $templater->render();	
		}
		
		$templater = vB_Template::create('dbtech_thanks_options_bit');
			$templater->register('headerphrase', $vbphrase[$headerphrase]);
			$templater->register('optionbits2', $optionbits2);
		$optionbits .= $templater->render();	
	}
	
	// Include the page template
	$page_templater = vB_Template::create('dbtech_thanks_options');
		$page_templater->register('optionbits', $optionbits);
}

// ############################### start save options ##################################
if ($_POST['action'] == 'updateoptions')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'options'        		=> TYPE_ARRAY_BOOL,
		'set_options'    		=> TYPE_ARRAY_BOOL,
	));
	
	$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_STANDARD);
	$userdata->set_existing($vbulletin->userinfo);
	
	$bitfields = array();
	foreach (THANKS::$cache['button'] as $buttonid => $button)
	{
		$bitfields[$button['varname']] = $button['bitfield'];
	}
	
	// Add to userdata
	$userdata->bitfields['dbtech_thanks_settings'] 	= $bitfields;
	
	// options bitfield
	foreach ($userdata->bitfields['dbtech_thanks_settings'] AS $key => $val)
	{
		if (isset($vbulletin->GPC['options'][$key]) OR isset($vbulletin->GPC['set_options'][$key]))
		{
			$value = $vbulletin->GPC['options'][$key];
			$userdata->set_bitfield('dbtech_thanks_settings', $key, $value);
		}
	}
	
	// Grab all the bitfields we can
	require_once(DIR . '/includes/class_bitfield_builder.php');
	$bitfields = vB_Bitfield_Builder::return_data();	
	
	// Add to userdata
	$userdata->bitfields['dbtech_thanks_settings2'] = $bitfields['nocache']['dbtech_thanks_notification_settings'];

	// options bitfield
	foreach ($userdata->bitfields['dbtech_thanks_settings2'] AS $key => $val)
	{
		if (isset($vbulletin->GPC['options'][$key]) OR isset($vbulletin->GPC['set_options'][$key]))
		{
			$value = $vbulletin->GPC['options'][$key];
			$userdata->set_bitfield('dbtech_thanks_settings2', $key, $value);
		}
	}
	
	// Save the userdata
	$userdata->save();	
	
	$vbulletin->url = 'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=profile&action=options';
	if (version_compare($vbulletin->versionnumber, '4.1.7') >= 0)
	{
		eval(print_standard_redirect(array('redirect_updatethanks', $vbulletin->userinfo['username'])));
	}
	else
	{
		eval(print_standard_redirect('redirect_updatethanks'));
	}
}

// #######################################################################
if (intval($vbulletin->versionnumber) == 3)
{
	// Create navbits
	$navbits = construct_navbits($navbits);	
	eval('$navbar = "' . fetch_template('navbar') . '";');
}
else
{
	$navbar = render_navbar_template(construct_navbits($navbits));	
}
construct_usercp_nav('dbtech_thanks_' . $_REQUEST['action']);

$templater = vB_Template::create('USERCP_SHELL');
	$templater->register_page_templates();
	$templater->register('cpnav', $cpnav);
	if (method_exists($page_templater, 'render'))
	{
		// Only run this if there's anything to render
		$templater->register('HTML', $page_templater->render());
	}
	$templater->register('clientscripts', $clientscripts);
	$templater->register('navbar', $navbar);
	$templater->register('navclass', $navclass);
	$templater->register('onload', $onload);
	$templater->register('pagetitle', $pagetitle);
	$templater->register('template_hook', $template_hook);
print_output($templater->render());