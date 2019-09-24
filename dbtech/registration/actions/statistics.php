<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2007-2009 Jon Dickinson AKA Pandemikk				  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

if (!REGISTRATION::$permissions['canview'])
{
	// Can't view
	print_no_permission();
}

// #############################################################################

if ($_REQUEST['action'] == 'statistics' OR empty($_REQUEST['action']))
{
	$vbulletin->input->clean_array_gpc('r', array(
		'filter'		=> TYPE_NOHTML,
		'pagenumber'  	=> TYPE_UINT,
		'perpage'     	=> TYPE_UINT,
		'where'     	=> TYPE_STR,
		'likeid'     	=> TYPE_UINT
	));	

	// Set the limit on number of stats to fetch
	$limit = (isset($vbulletin->options['dbtech_registration_statistics_limit']) ? $vbulletin->options['dbtech_registration_statistics_limit'] : 25);

	// Ensure there's no errors or out of bounds with the page variables
	if ($vbulletin->GPC['pagenumber'] < 1)
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$pagenumber = $vbulletin->GPC['pagenumber'];
	$perpage	= (!$vbulletin->GPC['perpage'] OR $vbulletin->GPC['perpage'] > $limit) ? $limit : $vbulletin->GPC['perpage'];	

	// Count number of stats
	$count = REGISTRATION::$db->fetchOne('
		SELECT COUNT(*)
		FROM $dbtech_registration_tracking AS type
	');
		
	// Ensure every result is as it should be
	sanitize_pageresults($count, $pagenumber, $perpage);
	
	// Find out where to start
	$startat = ($pagenumber - 1) * $perpage;
	
	// Constructs the page navigation
	$pagenav = construct_page_nav(
		$pagenumber,
		$perpage,
		$count,
		'registration.php?' . $vbulletin->session->vars['sessionurl'] . 'do=statistics',
		"&amp;perpage=$perpage"
	);

	// Fetch the stats
	$results = REGISTRATION::$db->fetchAll('
		SELECT
			type.*,
			email.verified,
			invite.inviteid
		FROM $dbtech_registration_tracking AS type
		LEFT JOIN $dbtech_registration_email AS email ON(type.email = email.email)
		LEFT JOIN $dbtech_registration_invite AS invite ON(email.email = invite.email)
		WHERE
			:type
				AND
			:filter
		ORDER BY type.dateline DESC
		LIMIT :limitStart, :limitEnd
	', array(
		':type' 		=> in_array($vbulletin->GPC['where'], array('ipaddress', 'email')) ? 'type.' . $vbulletin->GPC['where'] . ' = (SELECT ' . $vbulletin->GPC['where'] . ' FROM ' . TABLE_PREFIX . 'dbtech_registration_tracking WHERE trackingid = ' . $db->sql_prepare($vbulletin->GPC['likeid']) . ')' : '1 = 1',
		':filter'		=> !empty($vbulletin->GPC['filter']) ? 'reason = ' . $db->sql_prepare($vbulletin->GPC['filter']) : '1 = 1',
		':limitStart'	=> $startat,
		':limitEnd'		=> $perpage
	));
	
	if (!REGISTRATION::$permissions['ismanager'])
	{
		// Pass the salt
		$salt = '';

		for ($i = 0; $i < 10; ++$i)
		{
			$salt .= chr(rand(33, 126));
		}
	}

	$filters = array();
	foreach ((array)REGISTRATION::$cache['filters'] as $key => $val)
	{
		if ($key == $val)
		{
			$filters[$key] = $vbphrase[$key . '_title'];
		}
		else
		{
			$filters[$key] = $val;
		}
	}

	$bits = '';
	foreach ($results AS $data)
	{
		// set date
		$data['dateline']	= vbdate($vbulletin->options['dateformat'], $data['dateline']) . ' - '
							. vbdate($vbulletin->options['timeformat'], $data['dateline']);

		if (empty($data['verified']))
		{
			// placeholder
			$data['verified'] = false;
		}

		// set friendly title
		$data['title'] = $filters[$data['reason']];

		if (!empty($data['data']))
		{
			// unserialize
			$data['data']		= unserialize($data['data']);

			// construct query
			$data['query']		= '&amp;action=' . key($data['data']) . '&amp;' . key($data['data']) . 'id=' . reset($data['data']);
		}

		if (isset($salt))
		{
			$data['hashed_ipaddress'] = md5(md5($data['ipaddress']) . $salt);
		}
		
		// parse the template bits
		$templater = vB_Template::create('dbtech_registration_tracking_bits');
			$templater->register('data', $data);
		$bits .= $templater->render();	
	}

	// maybe this should be cached in the users table?
	if ($vbulletin->options['dbtech_registration_invites_statistics'])
	{
		$results = REGISTRATION::$db->fetchAll('
			SELECT
				COUNT(*) AS value,
				user.userid, user.username, user.usergroupid, user.membergroupids, infractiongroupid, displaygroupid :vbshop
				FROM $dbtech_registration_invite AS invite
				INNER JOIN $dbtech_registration_email AS email ON(invite.email = email.email)
					AND verified = \'1\'
				INNER JOIN $user AS user ON(invite.userid = user.userid)
				GROUP BY invite.userid
				ORDER BY value DESC
				LIMIT 5
		', array(
			':vbshop' 		=> $vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase" : ''
		));
	
		foreach ($results AS $data)
		{
			if ($data['value'] == 0)
			{
				// We don't want to count empty users
				continue;
			}
				
			// Grab the musername
			fetch_musername($data);				
			
			$templater = vB_Template::create('dbtech_registration_statistics_invites_bit');
				$templater->register('data', $data);
			$leaderboardbits .= $templater->render();
		}
	}

	foreach (array('invites', 'verify_emails') as $key)
	{
		REGISTRATION::$cache['total'][$key]['sent'] = intval(REGISTRATION::$cache['total'][$key]['sent']);
		REGISTRATION::$cache['total'][$key]['verified'] = intval(REGISTRATION::$cache['total'][$key]['verified']);
	}

	// Set page titles
	$pagetitle = $navbits[] = $vbphrase['dbtech_registration_statistics'];
	
	// Begin the page template
	$page_templater = vB_Template::create('dbtech_registration_statistics');
		$page_templater->register('pagenav', 		$pagenav);
		$page_templater->register('pagenumber', 	$pagenumber);
		$page_templater->register('perpage', 		$perpage);
		$page_templater->register('leaderboardbits',$leaderboardbits);
		$page_templater->register('bits', 			$bits);
		$page_templater->register('cache', 			REGISTRATION::$cache['total']);
		$page_templater->register('filter', 		REGISTRATION::createSelectOptions($filters, $vbulletin->GPC['filter']));
	$HTML = $page_templater->render();
}
?>