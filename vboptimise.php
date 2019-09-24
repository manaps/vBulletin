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
define('THIS_SCRIPT', 'vboptimise');
define('IN_VBOPTIMISE', true);

if (isset($_REQUEST['do']) AND $_REQUEST['do'] == 'ajax')
{
	define('CSRF_PROTECTION', true);
	define('LOCATION_BYPASS', 1);
	define('NOPMPOPUP', 1);
	define('VB_ENTRY', 'ajax.php');
	define('SESSION_BYPASS', true);
	define('VB_ENTRY_TIME', microtime(true));
}

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array();

// pre-cache templates used by specific actions
$actiontemplates = array();

// ############################### default do value ######################
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = $_GET['do'] = 'statistics';
}

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');
if (!class_exists('vb_optimise'))
{
	eval(standard_error($vbphrase['vboptimise_deactivated']));
}

if ($_REQUEST['do'] == 'devinfo' AND $_REQUEST['devkey'] == 'dbtech')
{
	$isPro = false;
	/*DBTECH_PRO_START*/
	$isPro = true;
	/*DBTECH_PRO_END*/
	
	vb_optimise::outputJSON(array(
		'version' 		=> '2.7.1',
		'versionnumber' => '271',
		'pro'			=> $isPro,
		'vbversion'		=> $vbulletin->versionnumber
	));
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
	$action = 'statistics';
}

// Strip non-valid characters
$action = preg_replace('/[^\w-]/i', '', $action);

if (!file_exists(DIR . '/dbtech/vboptimise/actions/' . $action . '.php'))
{
	if (!file_exists(DIR . '/dbtech/vboptimise_pro/actions/' . $action . '.php'))
	{
		// Throw error from invalid action
		eval(standard_error(fetch_error('vboptimise_error_x', $vbphrase['vboptimise_invalid_action'])));
	}
	else
	{
		// Include the selected file
		include_once(DIR . '/dbtech/vboptimise_pro/actions/' . $action . '.php');	
	}
}
else
{
	// Include the selected file
	include_once(DIR . '/dbtech/vboptimise/actions/' . $action . '.php');	
}
?>