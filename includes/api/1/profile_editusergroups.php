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
		'HTML' => array(
			'displaygroupbits' => array(
				'*' => array(
					'checked',
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					)
				)
			),
			'joinrequestbits',
			'membergroupbits' => array(
				'*' => array(
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					),
					'show' => array(
						'isleader', 'canleave'
					)
				)
			),
			'nonmembergroupbits' => array(
				'*' => array(
					'groupleaders',
					'usergroup' => array(
						'opentag', 'title', 'closetag', 'description',
						'usertitle', 'usergroupid'
					),
					'ismoderated', 'joinrequest', 'joinrequested'
				)
			),
			'primarygroup' => array(
				'opentag', 'title', 'closetag', 'description',
				'usertitle', 'usergroupid'
			), 'primarygroupid'
		)
	),
	'show' => array(
		'joinrequests', 'nonmembergroups', 'isleader', 'canleave', 'membergroups',
		'displaygroups'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/