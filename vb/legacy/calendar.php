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
 * @subpackage Legacy
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . "/vb/legacy/dataobject.php");

/**
 * Legacy calendar wrapper
 *
 */
class vB_Legacy_Calendar extends vB_Legacy_Dataobject
{

	/**
	 * Create object from and existing record
	 *
	 * @param int $calendarinfo
	 * @return vB_Legacy_Calendar
	 */
	public static function create_from_record($calendarinfo)
	{
		$calendar = new vB_Legacy_Calendar();
		$calendar->set_record($calendarinfo);
		return $calendar;
	}

	/**
	 * Load object from an id
	 *
	 * @param int $id
	 * @return vB_Legacy_Calendar
	 */
	public static function create_from_id($id)
	{
		global $_CALENDAROPTIONS, $_CALENDARHOLIDAYS;
		$calendarinfo = verify_id('calendar', $id, false, true);
		$getoptions = convert_bits_to_array($calendarinfo['options'], $_CALENDAROPTIONS);
		$calendarinfo = array_merge($calendarinfo, $getoptions);
		$geteaster = convert_bits_to_array($calendarinfo['holidays'], $_CALENDARHOLIDAYS);
		$calendarinfo = array_merge($calendarinfo, $geteaster);

		if ($calendarinfo)
		{
			return self::create_from_record($calendarinfo);
		}
		else
		{
			return null;
		}
	}

	public static function create_from_id_cached($id)
	{
		if (!isset(self::$calendar_cache[$id]))
		{
			self::$calendar_cache[$id] = self::create_from_id($id);
		}

		return self::$calendar_cache[$id];
	}

	private static $calendar_cache = array();

	/**
	 * constructor -- protectd to force use of factory methods.
	 */
	protected function __construct() {}

	//*********************************************************************************
	// Derived Getters



	//*********************************************************************************
	//	High level permissions
	/*
	//not used so not implemented.
	public function can_view($user)
	{
		return false;
	}

	public function can_search($user)
	{
		return false; 
	}
	*/

	//*********************************************************************************
	//	Data operation functions


}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
