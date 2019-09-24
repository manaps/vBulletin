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

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'registration');
define('IN_REGISTRATION', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array('dbtech_registration', 'cphome', 'logging', 'threadmanage',
'maintenance', 'banning', 'cpuser', 'cpoption', 'cppermission', 'user', 'cprank', 
'register', 'cprofilefield', 'forum', 'posting', 'showthread');

// get special data templates from the datastore
require('../dbtech/registration/includes/specialtemplates.php');
$specialtemplates = $extracache;

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS ######################
if (!can_administer('canadminregistration'))
{
	print_cp_no_permission();
}

// ############################# LOG ACTION ##############################
log_admin_action(iif($_REQUEST['action'] != '', 'action = ' . $_REQUEST['action']));

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

if (!file_exists(DIR . '/dbtech/registration/actions/admin/' . $action . '.php'))
{
	if (!file_exists(DIR . '/dbtech/registration_pro/actions/admin/' . $action . '.php'))
	{
		// Throw error from invalid action
		print_cp_message($vbphrase['dbtech_registration_invalid_action']);
	}
	else
	{
		// Include the selected file
		include_once(DIR . '/dbtech/registration_pro/actions/admin/' . $action . '.php');	
	}
}
else
{
	// Include the selected file
	include_once(DIR . '/dbtech/registration/actions/admin/' . $action . '.php');	
}

/*=======================================================================*\
|| ##################################################################### ||
|| # Created: 17:29, Sat Dec 27th 2008                                 # ||
|| # SVN: $RCSfile: registration.php,v $ - $Revision: $WCREV$ $
|| ##################################################################### ||
\*=======================================================================*/
?>