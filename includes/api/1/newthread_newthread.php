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
	'response' => array(
		'attachmentoption' => $VB_API_WHITELIST_COMMON['attachmentoption'],
		'disablesmiliesoption', 'emailchecked', 'explicitchecked',
		'folderbits',
		'foruminfo' => $VB_API_WHITELIST_COMMON['foruminfo'],
		'forumrules', 'human_verify', 'newpost',
		'podcastauthor', 'podcastkeywords', 'podcastsize', 'podcastsubtitle',
		'podcasturl', 'polloptions', 'posthash', 'posticons', 'postpreview',
		'poststarttime', 'prefix_options', 'selectedicon', 'subject',
		'tags_remain', 'tag_delimiters', 'htmloption'
	),
	'vboptions' => array(
		'postminchars', 'titlemaxchars', 'maxpolloptions'
	),
	'show' => array(
		'tag_option', 'posticons', 'smiliebox', 'attach', 'threadrating',
		'openclose', 'stickunstick', 'closethread', 'unstickthread',
		'subscribefolders', 'reviewmore', 'parseurl', 'misc_options',
		'additional_options', 'poll', 'podcasturl', 'tags_remain'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/