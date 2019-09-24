<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'vboptions' => array('usecoppa', 'webmasteremail'),
	'session' => array('sessionhash'),
	'response' => array(
		'birthdayfields', 'checkedoff',
		'customfields_option' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'customfields_other' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'customfields_profile' => array(
			'*' => $VB_API_WHITELIST_COMMON['customfield']
		),
		'day', 'email',
		'emailconfirm', 'errorlist', 'human_verify',
		'month', 'parentemail', 'password', 'passwordconfirm', 'referrername',
		'timezoneoptions', 'url', 'year'
	),
	'vbphrase' => array(
		'coppa_rules_description', 'forum_rules_registration', 'forum_rules_description'
	),
	'show' => array(
		'coppa', 'birthday', 'referrer', 'customfields_profile', 'customfields_option',
		'noemptyoption', 'customfields_other', 'email'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/