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

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'registration');
define('IN_REGISTRATION', true);
define('CSRF_PROTECTION', true);

if ($_REQUEST['do'] == 'ajax')
{
	define('LOCATION_BYPASS', 1);
	define('NOPMPOPUP', 1);
	define('VB_ENTRY', 'ajax.php');
	define('VB_ENTRY_TIME', microtime(true));
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('dbtech_registration', 'register', 'user', 'album');

// get templates used by all actions
$globaltemplates = array(
	'dbtech_registration',
);

// ############################### default do value ######################
// move it up here to cache those templates
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = $_GET['do'] = 'statistics';
}

// pre-cache templates used by specific actions
$actiontemplates = array(
	'profile' => array(
		'USERCP_SHELL',
		'usercp_nav_folderbit',
		'dbtech_registration_invites',
		'dbtech_registration_invites_bit',
		'dbtech_registration_usercp_nav_link'
	),
	'statistics' => array(
		'dbtech_registration_statistics',
		'dbtech_registration_tracking_bits',
		'dbtech_registration_statistics_invites_bit'
	),
	'details' => array(
		'dbtech_registration_details'
	)
);

// get special data templates from the datastore
require('./dbtech/registration/includes/specialtemplates.php');
$specialtemplates = $extracache;

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

if (!class_exists('vB_Template'))
{
	// Ensure we have this
	require_once(DIR . '/dbtech/registration/includes/class_template.php');
}

if (!class_exists('REGISTRATION'))
{
	eval(standard_error($vbphrase['dbtech_registration_deactivated']));
}

if ($_REQUEST['do'] == 'devinfo' AND $_REQUEST['devkey'] == 'dbtech')
{
	$_info = array(
		'version' 		=> '2.0.7 Patch Level 2',
		'versionnumber' => '207pl2',
		'pro'			=> REGISTRATION::$isPro,
		'vbversion'		=> $vbulletin->versionnumber
	);
	$_content = array();
	foreach ($_info as $key => $val)
	{
		$_content[] = '"' . $key . '":"' . $val . '"';
	}
	echo '{' . implode(',', $_content) . '}';
	die();
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

if (!empty($_POST['do']))
{
	// $_POST requests take priority
	$action = $_POST['do'];
}
else if (!empty($_GET['do']))
{
	// We had a GET request instead
	$action = $_GET['do'];
}
else
{
	// No request
	$action = 'main';
}

// Strip non-valid characters
$action = preg_replace('/[^\w-]/i', '', $action);

if (!$vbulletin->options['dbtech_registration_active'])
{
	// Reg is shut off
	eval(standard_error($vbulletin->options['dbtech_registration_closedreason']));
}

if (!REGISTRATION::$permissions['canview'] AND $action != 'profile')
{
	// Can't view
	print_no_permission();
}

// begin navbits
$navbits = array('registration.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['dbtech_registration_friendly'] /* OR? $vbphrase['dbtech_registration_registration']*/);

// Core page template
$page_template = 'dbtech_registration';

if (!file_exists(DIR . '/dbtech/registration/actions/' . $action . '.php'))
{
	if (!file_exists(DIR . '/dbtech/registration_pro/actions/' . $action . '.php'))
	{
		// Throw error from invalid action
		eval(standard_error(fetch_error('dbtech_registration_error_x', $vbphrase['dbtech_registration_invalid_action'])));
	}
	else
	{
		// Include the selected file
		include_once(DIR . '/dbtech/registration_pro/actions/' . $action . '.php');	
	}
}
else
{
	// Include the selected file
	include_once(DIR . '/dbtech/registration/actions/' . $action . '.php');	
}

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

if (intval($vbulletin->versionnumber) == 3)
{
	// Begin the monster template
	$headinclude .= vB_Template::create('dbtech_registration.css')->render();
}

// Show branding or not
$show['dbtech_registration_producttype'] = (REGISTRATION::$isPro ? ' (Pro)' : ' (Lite)');

// Finish the main template
$templater = vB_Template::create($page_template);
	$templater->register_page_templates();
	$templater->register('navclass', 		$navclass);
	$templater->register('HTML', 			$HTML);
	$templater->register('navbar', 			$navbar);
	$templater->register('pagetitle', 		$pagetitle);
	$templater->register('pagedescription', $pagedescription);
	$templater->register('template_hook', 	$template_hook);
	$templater->register('includecss', 		$includecss);
	$templater->register('year',			date('Y'));
	$templater->register('jQueryVersion',	REGISTRATION::$jQueryVersion);
	$templater->register('version',			'2.0.7 Patch Level 2');
	$templater->register('versionnumber', 	'207pl2');
	$templater->register('headinclude', 	$headinclude);
print_output($templater->render());

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: registration.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>