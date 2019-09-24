<?php if (!defined('VB_ENTRY')) die('Access denied.');
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

/**
 * @package vBulletin
 * @subpackage Search
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . '/vb/search/result.php');

/**
 * Enter description here...
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBForum_Search_Result_Announcement extends vB_Search_Result
{
	public static function create($id)
	{
		$items = self::create_array(array($id));
		if (count($items))
		{
			return array_shift($items);
		}
		else
		{
			return new vB_Search_Result_Null();
		}
	}

	public static function create_array($ids)
	{
		global $vbulletin;

		$set = $vbulletin->db->query_read_slave("
			SELECT announcementid, startdate, title, announcement.views, forumid,
				user.username, user.userid, user.usertitle, user.customtitle, user.usergroupid,
				IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
			FROM " . TABLE_PREFIX . "announcement AS announcement
			LEFT JOIN " . TABLE_PREFIX . "user AS user USING (userid)
			WHERE announcementid IN (" . implode(',', array_map('intval', $ids)) . ")
		");

		$items = array();
		while ($record = $vbulletin->db->fetch_array($set))
		{
			fetch_musername($record);
			$record['title'] = fetch_censored_text($record['title']);
			$record['postdate'] = vbdate($vbulletin->options['dateformat'], $record['startdate']);
			$record['statusicon'] = 'new';
			$record['views'] = vb_number_format($record['views']);
			$record['forumtitle'] = $vbulletin->forumcache["$record[forumid]"]['title'];
			$show['forumtitle'] = ($record['forumid'] == -1) ? false : true;

			$announcement = new vBForum_Search_Result_Announcement();
			$announcement->record = $record;
			$items[$record['announcementid']] = $announcement;
		}
		return $items;
	}

	protected function __construct() {}

	public function get_contenttype()
	{
		return vB_Search_Core::get_instance()->get_contenttypeid('vBForum', 'Announcement');
	}

	public function can_search($user)
	{
		if ($this->record['forumid'] == -1)
		{
			return true;
		}
		else
		{
			return !in_array($this->record['forumid'], $user->getUnsearchableForums());
		}
	}

	public function render($current_user, $criteria, $template = '')
	{
		$template = vB_Template::create('search_results_announcement');
		$template->register('announcecolspan', 6);
		$template->register('announcement', $this->record);
		$template->register('announcementidlink', '&amp;a=' . $this->record['announcementid']);

		//this is actually how the legacy search code does it, since the foruminfo
		//value it passes isn't set properly.  Its only used to set the forum id
		//on the link which is ignored if the announcementid is also set
		$template->register('foruminfo', array());
		return $template->render();
	}


	/*** Returns the primary id. Allows us to cache a result item.
	 *
	 * @result	integer
	 ***/
	public function get_id()
	{
		/* Changed for PHP 7, according to their compatability page.
		I cannot find how this class is ever used in vB4, but updated anyway */
		if (isset($this->$record) AND isset($this->{$record['announcementid']}) )
		{
			$this->{$record['announcementid']};
		}
		return false;
	}

	private $record;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
