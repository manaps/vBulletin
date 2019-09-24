<?php
if (empty($vbulletin->userinfo['userid']) AND !defined('SKIP_SESSIONCREATE'))
{
	if (!$session = REGISTRATION::$db->fetchRow('SELECT * FROM $dbtech_registration_session WHERE sessionhash = ?', $vbulletin->session->vars['dbsessionhash']))
	{
		// Init the default session
		$session = array(
			'sessionhash' 	=> $vbulletin->session->vars['dbsessionhash'],
			'firstactivity' => TIMENOW,
			'pageviews' 	=> 1,
			'threadviews' 	=> (defined('THIS_SCRIPT') AND THIS_SCRIPT == 'showthread') ? 1 : 0,
		);

		// Set the session data in the db
		REGISTRATION::$db->insert('dbtech_registration_session', $session, array(), false);
	}
	else
	{
		REGISTRATION::$db->query('
			UPDATE $dbtech_registration_session
			SET 
				pageviews = pageviews + 1
				:threadViews
			WHERE sessionhash = ?
		', array(
			$vbulletin->session->vars['dbsessionhash'],
			':threadViews' => (defined('THIS_SCRIPT') AND THIS_SCRIPT == 'showthread') ? ', threadviews = threadviews + 1' : ''
		), 'query_write');
		$session['pageviews']++;
	}
	// would a redirect here make sense?
	if (defined('THIS_SCRIPT')
			AND
		!in_array(THIS_SCRIPT, array('register', 'login'))
	)
	{

		foreach ((array)REGISTRATION::$cache['redirect'] AS $redirectid => $redirect)
		{
			if (REGISTRATION::verify_redirect($redirect, $session))
			{
				// GTFO lurker
				$vbulletin->url = $vbulletin->options['bburl'] . '/register.php' . $vbulletin->session->vars['sessionurl_q'];
				eval(print_standard_redirect('dbtech_registration_please_register', true, true));
			}
		}
	}

	if (empty($vbulletin->session->vars['dbtech_registration_firstactivity']))
	{
		// set first activity
		$vbulletin->session->db_fields['dbtech_registration_firstactivity'] = TYPE_UINT;
		$vbulletin->session->set('dbtech_registration_firstactivity', TIMENOW);
	}

	if (!empty($vbulletin->session->vars['dbtech_registration_pageviews']))
	{
		// set pageviews
		$vbulletin->session->db_fields['dbtech_registration_pageviews'] = TYPE_UINT;
		$vbulletin->session->set('dbtech_registration_pageviews', intval($vbulletin->session->vars['dbtech_registration_pageviews']) + 1);
	}
	
	// pro option - let members lurk?
	
}
?>