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
		'attachmentoption' => $VB_API_WHITELIST_COMMON['attachmentoption'],
		'disablesmiliesoption', 'emailchecked',
		'folderbits', 'checked', 'multiquote_empty', 'rate',
		'forumrules', 'human_verify', 'posthash', 'posticons', 'postpreview',
		'poststarttime', 'prefix_options', 'selectedicon', 'title',
		'htmloption', 'specifiedpost',
		'threadreviewbits' => array(
			'*' => array(
				'posttime', 'reviewmessage', 'reviewtitle',
				'post' => array(
					'postid', 'threadid', 'username', 'userid'
				)
			)
		),
		'unquoted_post_count', 'return_node',
		'threadinfo' => $VB_API_WHITELIST_COMMON['threadinfo']
	),
	'vboptions' => array(
		'postminchars', 'titlemaxchars', 'maxposts'
	),
	'show' => array(
		'posticons', 'smiliebox', 'attach', 'threadrating', 'openclose', 'stickunstick',
		'closethread', 'unstickthread', 'subscribefolders', 'reviewmore',
		'parseurl', 'misc_options', 'additional_options'
	)
);

function api_result_prerender_2($t, &$r)
{
	switch ($t)
	{
		case 'newreply_reviewbit':
			$r['posttime'] = $r['post']['dateline'];
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