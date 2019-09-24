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

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of breadcrumbs_create
 */
class vB_APIMethod_breadcrumbs_create extends vBI_APIMethod
{
	public function output()
	{
		$data = array('response' => array('breadcrumbits' => $this->getBreadCrumbsBits()));
		return $data;
	}

	private function getBreadCrumbsBits()
	{
		global $vbulletin, $db;
		$arrayResponse = array();

		$vbulletin->input->clean_array_gpc('p',
			array(
				'type' => TYPE_STR,
				'conceptid' => TYPE_INT
			)
		);

		$vbulletin->GPC['type'] = convert_urlencoded_unicode($vbulletin->GPC['type']);
		$vbulletin->GPC['conceptid'] = convert_urlencoded_unicode($vbulletin->GPC['conceptid']);
		$conceptId = $vbulletin->GPC['conceptid'];
		$type = $vbulletin->GPC['type'];

		if($type == 't')
		{
			$threadInfo = $db->query_first("SELECT thread.forumid AS forumid FROM " . TABLE_PREFIX . "thread AS thread WHERE threadid=$conceptId");
			$conceptId = $threadInfo['forumid'];
			$parents = $db->query_first("SELECT forum.parentlist AS parentlist FROM " . TABLE_PREFIX . "forum AS forum WHERE forumid=$conceptId");
			$parentsArray = explode("," , $parents['parentlist']);
			$parentsArray = array_reverse($parentsArray);
			$parents = implode(",", $parentsArray);
		}

		if($type == 'f')
		{
			$parents = $db->query_first("SELECT forum.parentlist AS parentlist FROM " . TABLE_PREFIX . "forum AS forum WHERE forumid=$conceptId");
			$parentsArray = explode("," , $parents['parentlist']);
			array_shift($parentsArray);
			$parentsArray = array_reverse($parentsArray);
			$parents = implode(",", $parentsArray);
		}

		$query = "
			SELECT forum.forumid AS forumid, forum.title AS title, forum.threadcount AS threadcount
			FROM "  . TABLE_PREFIX . "forum AS forum
			WHERE forumid IN (" . $parents . ")";
		$forumInfo = $db->query_read_slave($query);

		$breadCrumbsBits = array();
		while($parentForumInfo = $db->fetch_array($forumInfo))
		{
			$separator = ",";
			$breadCrumbsBits[$parentForumInfo['forumid']] = array(
				'forumid' => $parentForumInfo['forumid'],
				'title' => $parentForumInfo['title'],
				'threadcount' => $parentForumInfo['threadcount']
			);
		}

		$arrayResponse = array();
		foreach($parentsArray as $parent)
		{
			if(in_array($breadCrumbsBits[$parent], $breadCrumbsBits))
			{
				$arrayResponse[] = $breadCrumbsBits[$parent];
			}
		}

		return $arrayResponse;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>
