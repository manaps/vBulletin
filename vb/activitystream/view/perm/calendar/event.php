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

class vB_ActivityStream_View_Perm_Calendar_Event extends vB_ActivityStream_View_Perm_Calendar_Base
{
	public function group($activity)
	{
		if (!$this->content['event'][$activity['contentid']])
		{
			$this->content['eventid'][$activity['contentid']] = 1;
		}
	}

	public function process()
	{
		if (!$this->content['eventid'])
		{
			return true;
		}

		$events = vB::$db->query_read_slave("
			SELECT
				e.eventid AS e_eventid, e.userid AS e_userid, e.dateline AS e_dateline, e.title AS e_title, e.calendarid AS e_calendarid, e.event AS e_event,
				c.title AS c_title, c.calendarid AS c_calendarid
			FROM " . TABLE_PREFIX . "event AS e
			INNER JOIN " . TABLE_PREFIX . "calendar AS c ON (e.calendarid = c.calendarid)
			WHERE
				e.eventid IN (" . implode(",", array_keys($this->content['eventid'])) . ")
					AND
				e.visible = 1
		");
		while ($event = vB::$db->fetch_array($events))
		{
			$this->content['event'][$event['e_eventid']] = $this->parse_array($event, 'e_');
			$this->content['userid'][$event['e_userid']] = 1;
			if (!$this->content['calendar'][$event['c_calendarid']])
			{
				$this->content['calendar'][$event['c_calendarid']] = $this->parse_array($event, 'c_');
			}
		}

		$this->content['eventid'] = array();
	}

	public function fetchCanView($record)
	{
		$this->processUsers();
		return $this->fetchCanViewCalendarEvent($record['contentid']);
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
		$eventinfo =& $this->content['event'][$activity['contentid']];
		$calendarinfo =& $this->content['calendar'][$eventinfo['calendarid']];
		$userinfo =& $this->content['user'][$activity['userid']];

		$activity['postdate'] = vbdate(vB::$vbulletin->options['dateformat'], $activity['dateline'], true);
		$activity['posttime'] = vbdate(vB::$vbulletin->options['timeformat'], $activity['dateline']);

		$preview = strip_quotes($eventinfo['event']);
		$eventinfo['preview'] = htmlspecialchars_uni(fetch_censored_text(
			fetch_trimmed_title(strip_bbcode($preview, false, true, true, true),
				vb::$vbulletin->options['as_snippet'])
		));

		if ($fetchphrase)
		{
			return array(
				'phrase' => construct_phrase($this->vbphrase['x_created_an_event_y_in_z'], fetch_seo_url('member', $userinfo), $userinfo['username'], vB::$vbulletin->session->vars['sessionurl'], $eventinfo['eventid'], $eventinfo['title'], $calendarinfo['calendarid'], $calendarinfo['title']),
				'userinfo' => $userinfo,
				'activity' => $activity,
			);
		}
		else
		{
			$templater = vB_Template::create($templatename);
				$templater->register('userinfo', $userinfo);
				$templater->register('activity', $activity);
				$templater->register('eventinfo', $eventinfo);
				$templater->register('calendarinfo', $calendarinfo);
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