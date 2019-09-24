<?php
if (isset($vbulletin->session->vars['dbtech_registration_threadviews']))
{
	// set thread views
	$vbulletin->session->db_fields['dbtech_registration_threadviews'] = TYPE_UINT;
	$vbulletin->session->set('dbtech_registration_threadviews', intval($vbulletin->session->vars['dbtech_registration_threadviews']) + 1);
}
?>