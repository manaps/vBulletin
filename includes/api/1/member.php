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

define('VB_API_LOADLANG', true);

loadCommonWhiteList();

$VB_API_WHITELIST = array(
	'response' => array(
		'blocks' => array(
			'visitors' => array(
				'html' => array(
					'block_data' => array(
						'visitorbits' => array(
							'*' => array(
								'user' => array(
									'userid', 'username', 'invisiblemark',
									'buddymark'
								)
							)
						)
					)
				)
			),
			'groups' => array(
				'html' => array(
					'block_data' => array(
						'socialgroupbits' => array(
							'*' => array(
								'showgrouplink',
								'socialgroup' => array(
									'groupid', 'shortdescription', 'iconurl', 'name',
									'name_html'
								)
							)
						),
						'membergroupbits' => array(
							'*' => array(
								'usergroup' => array(
									'usergroupid', 'opentag', 'title', 'closetag'
								)
							)
						)
					)
				)
			),
			'visitor_messaging' => array(
				'block_data' => array(
					'messagebits' => array(
						'*' => array(
							'message' => array(
								'vmid', 'avatarurl', 'postuserid', 'userid', 'username',
								'postuserid', 'profileusername', 'date',
								'time', 'message', 'hostuserid', 'guestuserid',
								'converse_description_phrase'
							),
							'show' => array(
								'profile', 'detailedtime', 'moderation', 'edit',
								'converse', 'reportlink'
							)
						)
					),
					'lastcomment',
					'pagenav', 'pagenumber', 'messagetotal'
				),
			),
			'stats' => array(
				'html' => array(
					'block_data'
				)
			),
			'aboutme' => array(
				'block_data' => array(
					'fields' => array(
						'*' => array(
							'category' => array(
								'title', 'description',
								'fields' => array(
									'*' => array(
										'profilefield' => array(
											'profilefieldid', 'title', 'value'
										)
									)
								)
							)
						)
					)
				)
			),
			'friends' => array(
				'block_data' => array(
					'start_friends', 'end_friends', 'showtotal', 'pagenav',
					'friendbits' => array(
						'*' => array(
							'remove' => array(
								'userid', 'return'
							),
							'user' => array(
								'userid', 'username',
								'onlinestatus' => array('onlinestatus'),
								'usertitle', 'showicq', 'showmsn', 'showaim', 'showyahoo',
								'showskype', 'icq', 'msn', 'aim', 'yahoo',
								'skype', 'avatarurl'
							),
							'show' => array(
								'breakfriendship'
							)
						)
					)
				)
			),
		),
		'prepared' => array(
			'birthday', 'age', 'signature', 'userid', 'username', 'displayemail',
			'homepage', 'profileurl', 'hasimdetails', 'usertitle', 'profilepicurl',
			'onlinestatus' => array('onlinestatus'),
			'canbefriend', 'homepage', 'usernotecount', 'joindate', 'action',
			'where', 'lastactivitydate', 'posts', 'avatarurl'
		), 'selected_tab',
		'userinfo' => array(
			'icq', 'msn', 'aim', 'yahoo', 'skype'
		)
	),
	'show' => array(
		'vcard', 'edit_profile', 'hasimicons', 'usernotes', 'usernotepost',
		'usernoteview', 'email', 'pm', 'addbuddylist', 'removebuddylist',
		'addignorelist', 'removeignorelist', 'userlists', 'messagelinks',
		'contactlinks', 'can_customize_profile', 'view_conversation',
		'post_visitor_message',	'vm_block', 'usernote_block', 'usernote_post',
		'usernote_data', 'album_block', 'simple_link', 'edit_link', 'profile_category_title',
		'profilefield_edit', 'extrainfo', 'lastentry', 'infractions', 'giveinfraction',
		'private', 'lastcomment', 'latestentry', 'avatar', 'profilepic',
		'subscribelink', 'emaillink', 'homepage', 'pmlink', 'gotonewcomment'
	),
	'vboptions' => array(
		'postminchars'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/