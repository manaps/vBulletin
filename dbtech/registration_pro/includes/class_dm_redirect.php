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

if (!class_exists('vB_DataManager', false))
{
	exit;
}

/**
* Class to do data save/delete operations for locations
*
* @package	vBNotes
*/
class Registration_DataManager_Redirect extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'redirectid' 		=> array(TYPE_UINT, 	REQ_INCR),
		'title' 			=> array(TYPE_NOHTML,	REQ_YES,	VF_METHOD),
		'type' 				=> array(TYPE_STR,		REQ_YES,	VF_METHOD),
		'amount' 			=> array(TYPE_UINT,		REQ_YES),
		'persistent' 		=> array(TYPE_BOOL,		REQ_YES,	VF_METHOD,	'verify_onoff'),
		'active' 			=> array(TYPE_BOOL,		REQ_YES,	VF_METHOD,	'verify_onoff'),
		'options' 			=> array(TYPE_ARRAY,	REQ_YES,	VF_METHOD),
		'whitelist' 		=> array(TYPE_ARRAY,	REQ_NO,		VF_METHOD)
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_redirect';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the redirect names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('redirectid = \'%1$s\'', 'redirectid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_redirectdata_start')) ? eval($hook) : false;
	}


	/**
	* Verifies that the title is valid
	*
	* @param	string	title of the section
	*
	* @return	boolean
	*/
	function verify_title(&$title)
	{
		$title = strval($title);
		if ($title === '')
		{
			// Invalid
			return false;
		}

		return true;
	}

	/**
	* Verifies the type is valid
	*
	* @param	string	title of the redirect
	*
	* @return	boolean
	*/
	function verify_type(&$type)
	{
		if (!in_array($type, array('pageviews', 'threadviews', 'firstactivity')))
		{
			// type doesnt exist homie
			$this->error('dbtech_registration_invalid_x', $vbphrase['type'], $type);

			return false;
		}

		return true;
	}

	/**
	* Verifies onoff state for booleans
	*
	* @param	bool	On-off value
	*
	* @return	boolean
	*/
	function verify_onoff(&$bool)
	{
		$bool = (bool)$bool;

		return true;
	}

	/**
	* Verifies options
	*
	* @param	array	Options
	*
	* @return	boolean
	*/
	function verify_options(&$options)
	{
		$bitfields = array('exclude_bots' => 1, 'exlude_users');

		$bitfield = 0;
		foreach ($this->fetch_field('options') AS $option => $value)
		{
			if (isset($bitfields[$option]) AND $value != 0)
			{
				$bitfield += $bitfields[$option];
			}
		}

		// Set options bitfield
		$this->set('options', $bitfield);

		return true;
	}

	/**
	* Verifies whitelisted IP's
	*
	* @param	array	IP's
	*
	* @return	boolean
	*/
	function verify_whitelist(&$whitelist)
	{
		// Make into array
		$whitelist = explode(' ', $whitelist);

		foreach ($whitelist AS $ipaddress)
		{
			if (!filter_var($ipaddress, FILTER_VALIDATE_IP))
			{
				// Invalid IP Address
				unset($ip[$ipaddress]);
				continue;
			}
		}

		return true;
	}

	/**
	* Any checks to run immediately before saving. If returning false, the save will not take place.
	*
	* @param	boolean	Do the query?
	*
	* @return	boolean	True on success; false if an error occurred
	*/
	function pre_save($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_redirectdata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_redirectdata_predelete')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update after a save call (such as denormalized values in other tables).
	* In batch updates, is executed for each record updated.
	*
	* @param	boolean	Do the query?
	*/
	function post_save_each($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_redirectdata_postsave')) ? eval($hook) : false;

		foreach ($this->info['whitelist'] AS $ipaddress)
		{
			$values[] = '(' . $this->fetch_field('redirectid') . ', ' . $this->registry->db->sql_prepare($ipaddress) . ')';
		}

		// Insert new whitelisted ips  - don't replace as to preserve other columns
		$this->registry->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_registration_redirect_whitelist
				(redirectid, ipaddress)
			VALUES
			" . implode(',', $values)
		);

		// Rebuild the cache
		REGISTRATION_CACHE::build('redirect');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_redirectdata_postdelete')) ? eval($hook) : false;

		// Remove all whitelists
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_redirect_whitelist WHERE redirectid = " . $this->fetch_field('redirectid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('redirect');

		return true;
	}
}