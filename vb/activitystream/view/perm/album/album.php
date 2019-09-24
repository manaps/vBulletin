<?php

/* ======================================================================*\
  || #################################################################### ||
  || # vBulletin 4.2.5 - Licence Number LC449E5B7C
  || # ---------------------------------------------------------------- # ||
  || # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
  || # This file may not be redistributed in whole or significant part. # ||
  || # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
  || #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
  || #################################################################### ||
  \*====================================================================== */

class vB_ActivityStream_View_Perm_Album_Album extends vB_ActivityStream_View_Perm_Album_Base
{
	public function __construct(&$content, &$vbphrase)
	{
		$this->requireFirst['vB_ActivityStream_View_Perm_Album_Photo'] = 1;
		$this->requireFirst['vB_ActivityStream_View_Perm_Album_Comment'] = 1;
		return parent::__construct($content, $vbphrase);
	}

	public function group($activity)
	{
		if (!$this->fetchCanViewAlbums())
		{
			return;
		}

		if (!$this->content['album'][$activity['contentid']])
		{
			$this->content['albumid'][$activity['contentid']] = 1;
		}
	}

	public function process()
	{
		if (!$this->content['albumid'])
		{
			return true;
		}

		$albums = vB::$db->query_read_slave("
			SELECT
				a.albumid, a.userid, a.createdate, a.title, a.state, a.coverattachmentid
				" . (vB::$vbulletin->userinfo['userid'] ? ", u.type AS buddy" : "") . "
			FROM " . TABLE_PREFIX . "album AS a
			" . (vB::$vbulletin->userinfo['userid'] ? "LEFT JOIN " . TABLE_PREFIX . "userlist AS u ON (a.userid = u.userid AND u.relationid = " . vB::$vbulletin->userinfo['userid'] . " AND u.type = 'buddy')" : "") . "
			WHERE a.albumid IN (" . implode(",", array_keys($this->content['albumid'])) . ")
		");
		while ($album = vB::$db->fetch_array($albums))
		{
			$this->content['album'][$album['albumid']] = $album;
			$this->content['userid'][$album['userid']] = 1;
		}

		$this->content['albumid'] = array();
	}

	public function fetchCanView($record)
	{
		$this->processUsers();
		return $this->fetchCanViewAlbum($record['contentid']);
	}

	/*
	 * Register Template
	 *
	 * @param	string	Template Name
	 * @param	array	Activity Record
	 *
	 * @return	string	Template
	 */
	public function fetchTemplate($templatename, $activity, $skipgroup = false, $fetchphrase = false)
	{
		$userinfo =& $this->content['user'][$activity['userid']];
		$albuminfo =& $this->content['album'][$activity['contentid']];

		$activity['postdate'] = vbdate(vB::$vbulletin->options['dateformat'], $activity['dateline'], true);
		$activity['posttime'] = vbdate(vB::$vbulletin->options['timeformat'], $activity['dateline']);

		if ($fetchphrase)
		{
			return array(
				'phrase' => construct_phrase($this->vbphrase['x_created_an_album_y'], fetch_seo_url('member', $userinfo), $userinfo['username'], vB::$vbulletin->session->vars['sessionurl'],  $albuminfo['albumid'], $albuminfo['title']),
				'userinfo' => $userinfo,
				'activity' => $activity,
			);
		}
		else
		{
			$templater = vB_Template::create($templatename);
				$templater->register('userinfo', $userinfo);
				$templater->register('activity', $activity);
				$templater->register('albuminfo', $albuminfo);
			return $templater->render();
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/