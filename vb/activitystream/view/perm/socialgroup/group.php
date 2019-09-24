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

class vB_ActivityStream_View_Perm_Socialgroup_Group extends vB_ActivityStream_View_Perm_Socialgroup_Base
{
	public function __construct(&$content, &$vbphrase)
	{
		$this->requireFirst['vB_ActivityStream_View_Perm_Socialgroup_Groupmessage'] = 1;
		$this->requireFirst['vB_ActivityStream_View_Perm_Socialgroup_Discussion'] = 1;
		$this->requireFirst['vB_ActivityStream_View_Perm_Socialgroup_Photo'] = 1;
		$this->requireFirst['vB_ActivityStream_View_Perm_Socialgroup_Photocomment'] = 1;
		return parent::__construct($content, $vbphrase);
	}

	public function group($activity)
	{
		if (!$this->fetchCanUseGroups())
		{
			return;
		}

		if (!$this->content['socialgroup'][$activity['contentid']])
		{
			$this->content['groupid'][$activity['contentid']] = 1;
		}
	}

	public function process()
	{
		if (!$this->content['groupid'])
		{
			return true;
		}

		$groups = vB::$db->query_read_slave("
			SELECT sg.options, sg.groupid, sg.name, sg.creatoruserid, sg.creatoruserid AS userid, sg.dateline, sg.type
				" . (vB::$vbulletin->userinfo['userid'] ? ", sgm.type AS membertype" : "") . "
			FROM " . TABLE_PREFIX . "socialgroup AS sg
			" . (vB::$vbulletin->userinfo['userid'] ? "LEFT JOIN " . TABLE_PREFIX . "socialgroupmember AS sgm ON (sgm.userid = " . vB::$vbulletin->userinfo['userid'] . " AND sgm.groupid = sg.groupid)" : "") . "
			WHERE sg.groupid IN (" . implode(",", array_keys($this->content['groupid'])) . ")
		");
		while ($group = vB::$db->fetch_array($groups))
		{
			$group['is_owner'] = ($group['creatoruserid'] == vB::$vbulletin->userinfo['userid']);
			$this->content['socialgroup'][$group['groupid']] = $group;
			$this->content['userid'][$group['creatoruserid']] = 1;
		}

		$this->content['groupid'] = array();
	}

	public function fetchCanView($group)
	{
		$this->processUsers();
		return $this->fetchCanUseGroups();
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
		$groupinfo =& $this->content['socialgroup'][$activity['contentid']];
		$userinfo =& $this->content['user'][$activity['userid']];

		$activity['postdate'] = vbdate(vB::$vbulletin->options['dateformat'], $activity['dateline'], true);
		$activity['posttime'] = vbdate(vB::$vbulletin->options['timeformat'], $activity['dateline']);

		if ($fetchphrase)
		{
			return array(
				'phrase' => construct_phrase($this->vbphrase['x_created_a_group_y'], fetch_seo_url('member', $userinfo), $userinfo['username'], vB::$vbulletin->session->vars['sessionurl'], $groupinfo['groupid'], $groupinfo['name']),
				'userinfo' => $userinfo,
				'activity' => $activity,
			);
		}
		else
		{
			$templater = vB_Template::create($templatename);
				$templater->register('userinfo', $userinfo);
				$templater->register('activity', $activity);
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