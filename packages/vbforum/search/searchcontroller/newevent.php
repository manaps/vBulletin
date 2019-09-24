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
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @copyright vBulletin Solutions Inc.
 */

require_once(DIR . '/vb/search/searchcontroller.php');

class vBForum_Search_SearchController_NewEvent extends vB_Search_SearchController
{
	public function get_results($user, $criteria)
	{
		global $vbulletin;
		$db = $vbulletin->db;

		$range_filters = $criteria->get_range_filters();
		//$equals_filters = $criteria->get_equals_filters();
		//$notequals_filter = $criteria->get_equals_filters();

		$results = array();
		//get date cut -- no marking, just use the datecut.
		if (isset($range_filters['datecut']))
		{
			//ignore any upper limit
			$datecut = $range_filters['datecut'][0];
		}
		else
		{
			return $results;
		}

		$this->process_orderby($criteria);

		$contenttypeid = vB_Search_Core::get_instance()->get_contenttypeid('vBForum', 'Event');
		$set = $db->query_read_slave($q = "
			SELECT event.eventid
			FROM " . TABLE_PREFIX . "event AS event " . implode(" ", $this->joins) . "
			WHERE
				event.dateline >= $datecut
			ORDER BY {$this->orderby}
			LIMIT " . intval($vbulletin->options['maxresults'])
		);

		while ($row = $db->fetch_array($set))
		{
			$results[] = array($contenttypeid, $row['eventid'], $row['eventid']);
		}
		return $results;
	}

	private function process_orderby($criteria)
	{
		$sort = $criteria->get_sort();
		$direction = strtolower($criteria->get_sort_direction()) == 'desc' ? 'desc' : 'asc';

		if ($sort == 'user')
		{
			$this->orderby = "user.username $direction, event.dateline DESC";
			$this->join['user'] = "JOIN " . TABLE_PREFIX . "user as user on event.userid = user.userid";
		}
		else
		{
			$this->orderby = "event.dateline $direction";
		}
	}

	private $orderby = '';
	private $joins = array();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
