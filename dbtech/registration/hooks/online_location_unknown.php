<?php
if (strpos($userinfo['activity'], 'dbtech_vbanalytics_') === 0)
{
	$handled = true;	
	switch ($userinfo['activity'])
	{
		case 'dbtech_vbshop_':
			// Archive HO
			$userinfo['action'] = $vbphrase['dbtech_vbanalytics_viewing_support'];
			$userinfo['where'] = '<a href="registration.php' . $vbulletin->session->vars['sessionurl_q'] . '">' . $vbphrase['dbtech_vbanalytics_wol_support'] . '</a>';			
			break;
			
		default:
			$handled = false;
			break;
	}
}
?>