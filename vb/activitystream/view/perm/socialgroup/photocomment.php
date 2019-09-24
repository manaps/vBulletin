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

class vB_ActivityStream_View_Perm_Socialgroup_Photocomment extends vB_ActivityStream_View_Perm_Socialgroup_Base
{
	public function __construct(&$content, &$vbphrase)
	{
		$this->requireExist['vB_ActivityStream_View_Perm_Album_Photo'] = 1;
		$this->requireExist['vB_ActivityStream_View_Perm_Socialgroup_Photo'] = 1;
		return parent::__construct($content, $vbphrase);
	}

	public function group($activity)
	{
		if (!$this->fetchCanUseGroups() OR !vB::$vbulletin->options['pc_enabled'])
		{
			return;
		}

		if (!$this->content['socialgroup_picturecomment'][$activity['contentid']])
		{
			$this->content['picturecommentid'][$activity['contentid']] = 1;
		}
	}

	public function process()
	{
		return $this->processPicturecommentids();
	}

	public function fetchCanView($record)
	{
		$this->processUsers();
		return $this->fetchCanViewSocialgroupPhotoComment($record['contentid']);
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
		$commentinfo =& $this->content['socialgroup_picturecomment'][$activity['contentid']];
		$groupinfo =& $this->content['socialgroup'][$commentinfo['groupid']];
		$attachmentinfo =& $this->content['socialgroup_attachment'][$commentinfo['attachmentid']];

		$activity['postdate'] = vbdate(vB::$vbulletin->options['dateformat'], $activity['dateline'], true);
		$activity['posttime'] = vbdate(vB::$vbulletin->options['timeformat'], $activity['dateline']);

		$preview = strip_quotes($commentinfo['pagetext']);
		$commentinfo['preview'] = htmlspecialchars_uni(fetch_censored_text(
			fetch_trimmed_title(strip_bbcode($preview, false, true, true, true),
				vb::$vbulletin->options['as_snippet'])
		));
		$userinfo = $this->fetchUser($activity['userid'], $commentinfo['postusername']);
		$userinfo2 = $this->fetchUser($attachmentinfo['userid']);

		if ($fetchphrase)
		{
			if ($userinfo['userid'])
			{
				$phrase = construct_phrase($this->vbphrase['x_commented_on_a_photo_in_group_y'], fetch_seo_url('member', $userinfo), $userinfo['username'], fetch_seo_url('member', $userinfo2), $userinfo2['username'], vB::$vbulletin->session->vars['sessionurl'], $groupinfo['groupid'], $groupinfo['name']);
			}
			else
			{
				$phrase = construct_phrase($this->vbphrase['guest_x_commented_on_a_photo_in_group_y'], $userinfo['username'], fetch_seo_url('member', $userinfo2), $userinfo2['username'], vB::$vbulletin->session->vars['sessionurl'], $groupinfo['groupid'], $groupinfo['name']);
			}

			return array(
				'phrase'   => $phrase,
				'userinfo' => $userinfo,
				'activity' => $activity,
			);
		}
		else
		{
			$templater = vB_Template::create($templatename);
				$templater->register('userinfo', $userinfo);
				$templater->register('userinfo2', $userinfo2);
				$templater->register('activity', $activity);
				$templater->register('commentinfo', $commentinfo);
				$templater->register('groupinfo', $groupinfo);
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