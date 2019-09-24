<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

$VB_API_WHITELIST = array(
	'response' => array(
		'header' => array(
			'pmbox' => array('lastvisittime'),
			'notifications_menubits', 'notifications_total',
			'notices' => array(
				'*' => array(
					'notice_html', 'notice_plain', '_noticeid'
				)
			)
		),
		'activemembers', 'activeusers', 'birthdays',
		'forumbits' => array(
			'*' => $VB_API_WHITELIST_COMMON['forumbit']
		),
		'newuserinfo', 'numberguest', 'numbermembers', 'numberregistered',
		'recordtime', 'recordusers',
		'template_hook' => array(
			'forumhome_wgo_stats' => array(
				'blogstats',
				'latestentry' => array(
					'username', 'userid', 'title', 'blogid', 'postedby_username', 'postedby_userid', 'blogtitle'
				)
			)
		),
		'today', 'totalonline', 'totalposts', 'totalthreads', 'upcomingevents',
	),
	'show' => array(
		'birthdays', 'todaysevents', 'notices', 'dismiss_link', 'notifications',
		'loggedinusers', 'pmlink', 'homepage', 'addfriend', 'emaillink', 'activemembers'
	)
);
// format switch
if ($_REQUEST['apitextformat'])
{
	foreach ($VB_API_WHITELIST['response']['header']['notices']['*'] as $k => $v)
	{
		switch ($_REQUEST['apitextformat'])
		{
			case '1': // plain
				if ($v == 'notice_html')
				{
					unset($VB_API_WHITELIST['response']['header']['notices']['*'][$k]);
				}
				break;
			case '2': // html
				if ($v == 'notice_plain')
				{
					unset($VB_API_WHITELIST['response']['header']['notices']['*'][$k]);
				}
				break;
		}
	}
}

function api_result_prerender_2($t, &$r)
{
	global $vbulletin;
	switch ($t)
	{
		case 'FORUMHOME':
			$r['recordtime'] = $vbulletin->maxloggedin['maxonlinedate'];
			break;
		case 'header':
			$r['pmbox']['lastvisittime'] = $vbulletin->userinfo['lastvisit'];
			break;
		case 'navbar_noticebit':
			$r['notice_plain'] = strip_tags($r['notice_html']);
			break;
	}
}

vB_APICallback::instance()->add('result_prerender', 'api_result_prerender_2', 2);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/