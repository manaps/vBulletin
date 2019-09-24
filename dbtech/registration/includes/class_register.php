<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2013 Jon Dickinson AKA Pandemikk					  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// #############################################################################

/**
* Handles (mostly) everything to do with the registering process of Advanced Registration
*
* @package	ContactUs
* @version	$ $Rev$ $
* @date		$ $Date$ $
*/
class REGISTRATION_REGISTER
{
	/**
	* Array of cached relevant user info
	*
	* @public	array
	*/
	public static $userinfo			= array();

	/**
	* Array of cached matched criterias
	*
	* @public	array
	*/
	public static $criterias		= array();

	/**
	* Array of cached matched instances
	*
	* @public	array
	*/
	public static $instances		= array();

	/**
	* Instance we're working with
	*
	* @public	null
	*/
	public static $instanceid		= 0;

	/**
	* Array of cached actions for the instance we're working with
	*
	* @public	array
	*/
	public static $actions		= array();

	/**
	* Returns an array of matched criteria
	*
	*/
	public static function cache_matched_criterias()
	{
		foreach (REGISTRATION::$cache['criteria'] AS $criteriaid => $criteria)
		{
			if (!$criteria['active'])
			{
				// Inactive criteria
				continue;
			}

			if (self::is_matched_criteria($criteria))
			{
				self::$criterias[$criteriaid] = $criteria;
			}
		}
	}

	/**
	* Fetches valid instances by matched cirteria
	*
	* @param	array	Array of criterias with complete information
	*/
	public static function fetch_instance_by_criterias()
	{
		if (!count(REGISTRATION::$cache['instance']))
		{
			// We have no instances, derp
			return false;
		}

		if (empty(self::$criterias))
		{
			self::cache_matched_criterias();

			if (empty(self::$criterias))
			{
				foreach (REGISTRATION::$cache['instance'] as $instanceid => $instance)
				{
					// Add to instances array
					self::$instances[$instanceid] = $instance['priority'];
				}

				// Remove false values
				array_filter(self::$instances);

				if (empty(self::$instances))
				{
					return false;
				}

				// Sort by priority
				asort(self::$instances, SORT_NUMERIC);

				// Make sure we're pointing to the first element
				reset(REGISTRATION_REGISTER::$instances);

				// Grab instance of highest priority
				self::$instanceid = (int)key(REGISTRATION_REGISTER::$instances);

				return true;
			}
		}

		// Fetch all instances with matched criteria
		$instance_criterias = REGISTRATION::$db->fetchAll('
			SELECT * FROM $dbtech_registration_instance_criteria
			WHERE instanceid IN
			(
				SELECT instanceid FROM $dbtech_registration_instance_criteria WHERE criteriaid IN(:criterias)
			)
		', array(
			':criterias' => implode(',', array_keys(self::$criterias))
		));

		foreach ($instance_criterias AS $instance_criteria)
		{
			if (!$instance_criteria['active'])
			{
				// Inactive per-instance criteria
				continue;
			}

			if ($instance_criteria['required'] AND empty(self::$criterias[$instance_criteria['criteriaid']]))
			{
				// Criteria is required (for this instance) but hasn't been met
				self::$instances[$instance_criteria['instanceid']] = false;

				continue;
			}

			if (isset(self::$instances[$instance_criteria['instanceid']]))
			{
				// Already exists
				continue;
			}

			// Add to instances array
			self::$instances[$instance_criteria['instanceid']] = REGISTRATION::$cache['instance'][$instance_criteria['instanceid']]['priority'];
		}

		// Remove false values
		array_filter(self::$instances);

		if (empty(self::$instances))
		{
			return false;
		}

		// Sort by priority
		sort(self::$instances, SORT_NUMERIC);

		// Make sure we're pointing to the first element
		reset(REGISTRATION_REGISTER::$instances);

		// Grab instance of highest priority
		self::$instanceid = (int)current(REGISTRATION_REGISTER::$instances);

		return true;
	}

	/**
	* Checks if a criteria is matched
	*
	* @param	array	Criteria we're working with
	*/
	public static function is_matched_criteria($criteria)
	{
		if (empty(self::$userinfo[$criteria['type']]))
		{
			self::$userinfo[$criteria['type']] = self::cache_userinfo_criteria($criteria);
		}

		if (self::$userinfo[$criteria['type']] === null)
		{
			// Special exemption for unknown criteria conditions: Always return false.
			// e.g GeoIP module isn't installed: criteria "Location != China" and IP Address from China
			// Without a catch or the catch returns a conventional value (e.g false), the criteria would be matched.
			// tl;dr fail softly
			return false;
		}

		return ($criteria['operator'] == '=='	? self::$userinfo[$criteria['type']] == $criteria['value']
												: self::$userinfo[$criteria['type']] != $criteria['value']);
	}

	/**
	* Caches criteria types according to the user's info
	*
	* @param	array	criteria
	*/
	protected static function cache_userinfo_criteria($criteria)
	{
		switch ($criteria['type'])
		{
			case 'location':
				if (!function_exists('geoip_country_name_by_name'))
				{
					// GeoIP module isn't installed
					return null;
				}

				return geoip_country_name_by_name(IPADDRESS);
				break;
			case 'code':
				if (!empty($_COOKIE[COOKIE_PREFIX . 'dbtech_criteria_code']))
				{
					return null;
				}

				return $_COOKIE[COOKIE_PREFIX . 'dbtech_criteria_code'];
				break;
			case 'proxy':
				if (ALT_IP == IPADDRESS)
				{
					// We're no proxy
					return md5($criteria['value']);
				}

				return $criteria['value'];
				break;
			case 'invited':
			case 'verified':
				// These values are already cached in the main registration class
				$val = $criteria['type'];
				return REGISTRATION::$$val;
				break;
			default:
				return false;
		}
	}

	/**
	* Caches actions according to the instance we're working with
	*
	*/
	public static function cache_instance_actions()
	{
		if (empty(self::$instanceid))
		{
			return false;
		}

		// Fetch all instances with matched criteria
		self::$actions = REGISTRATION::$db->fetchAllKeyed('
			SELECT * FROM $dbtech_registration_instance_action
			WHERE instanceid = :instanceid
				AND active = 1
		', 'actionid', array(
			':instanceid' => self::$instanceid
		));

		foreach (self::$actions AS $actionid => $action)
		{
			if (!REGISTRATION::$cache['action'][$actionid]['active'])
			{
				// Inactive global action
				unset(self::$actions[$actionid]);
			}
		}
	}

	/*DBTECH_PRO_START*/
	/**
	* Executes cached actions
	*
	* @param	array	User info of the newly registered user
	* @param	object	vBulletin object (just to avoid global)
	* @param	array	Phrases (just to avoid global)
	*/
	public static function exec_actions($userinfo, $vbulletin, $vbphrase)
	{
		if (empty(self::$actions))
		{
			self::cache_instance_actions();

			if (empty(self::$actions))
			{
				return false;
			}
		}

		foreach (self::$actions AS $actionid => &$action)
		{
			// Set action
			$action = REGISTRATION::$cache['action'][$actionid];

			if (!file_exists(DIR . '/dbtech/registration/includes/actions/' . $action['type'] . '.php'))
			{
				if (!file_exists(DIR . '/dbtech/registration_pro/includes/actions/' . $action['type'] . '.php'))
				{
					// File doesn't exists
					continue;
				}
				else
				{
					require_once(DIR . '/dbtech/registration_pro/includes/actions/' . $action['type'] . '.php');
				}
			}
			else
			{
				require_once(DIR . '/dbtech/registration/includes/actions/' . $action['type'] . '.php');
			}

			if (!class_exists('Registration_' . $action['type']))
			{
				// Invalid classname
				continue;
			}

			// Attempt to execute action
			call_user_func(array('Registration_' . $action['type'], 'exec_action'), $userinfo, $action);
		}
	}
	/*DBTECH_PRO_END*/
}