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

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'albuminfo' => array(
			'albumid', 'title', 'description'
		),
		'picturecomment_commentarea' => array(
			'messagestats',
			'pagenav' => $VB_API_WHITELIST_COMMON['pagenav'],
			'picturecommentbits' => array(
				'*' => array(
					'message' => array(
						'commentid', 'userid', 'username', 'avatarurl',
						'date', 'time', 'message'
					),
					'show' => array(
						'edit', 'inlinemod', 'delete', 'undelete', 'approve',
						'pagenav', ''
					)
				)
			)
		),
		'pictureinfo' => array(
			'attachmentid', 'albumid', 'groupid', 'dateline', 'caption_censored',
			'pictureurl', 'caption_html', 'adddate', 'addtime'
		),
		'pic_location',
		'userinfo' => array(
			'userid', 'username'
		)
	),
	'show' => array(
		'picture_owner', 'edit_picture_option', 'add_group_link', 'reportlink',
		'picture_nav', 'moderation', 'picturecomment_options'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/