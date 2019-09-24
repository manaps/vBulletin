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

$VB_API_WHITELIST = array(
	'response' => array(
		'attachkeybits' => array(
			'*' => array(
				'extension' => array(
					'extension', 'size', 'width', 'height'
				)
			)
		),
		'attachlimit',
		'attachments' => array(
			'*' => array(
				'attach' => array(
					'extension', 'attachmentid', 'dateline', 'filename',
					'filesize'
				)
			)
		),
		'attachsize',
		'attachsum', 'attach_username', 'contenttypeid',
		'editpost', 'errorlist', 'inimaxattach',
		'totallimit', 'totalsize', 'values'
	),
	'show' => array(
		'errors', 'attachmentlimits', 'currentsize', 'totalsize', 'attachoption',
		'attachfile', 'attachurl', 'attachmentlist'
	)
);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/