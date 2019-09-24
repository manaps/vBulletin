<?php
if ($vbulletin->options['dbtech_registration_active'])
{
	if ($vbulletin->options['dbtech_registration_verify_email'] OR $vbulletin->options['dbtech_registration_invites'])
	{
		// additional checks on the email to verify registration
		REGISTRATION::verify_signup(true);
	}

	// Fetch the instance class
	require_once(DIR . '/dbtech/registration/includes/class_register.php');

	// Fetch our instance
	REGISTRATION_REGISTER::fetch_instance_by_criterias();

	if ($vbulletin->options['dbtech_registration_use_custom_page'])
	{
		if (REGISTRATION_REGISTER::$instanceid != $_POST['instanceid'])
		{
			// Invalid instance, send them back to the registration page
			$vbulletin->url = $vbulletin->options['bburl'] . '/register.php?' . $vbulletin->session->vars['sessionurl'];
			eval(print_standard_redirect('dbtech_registration_registration_form_changed', true, true));
		}
	}

	do
	{
		$data = @unserialize($vbulletin->options['dbtech_registration_usergroup_chooser']);
		if (!is_array($data) OR !$data[0])
		{
			break;
		}

		$vbulletin->input->clean_array_gpc('p', array(
			'usergroupid' => TYPE_UINT,
		));

		if (!in_array($vbulletin->GPC['usergroupid'], $data))
		{
			// Stop this
			break;
		}
		
		// set usergroupid
		$userdata->set('usergroupid', $vbulletin->GPC['usergroupid']);

		// set user title
		$userdata->set_usertitle('', false, $vbulletin->usergroupcache["{$vbulletin->GPC[usergroupid]}"], false, false);

	}
	while (false);
}
?>