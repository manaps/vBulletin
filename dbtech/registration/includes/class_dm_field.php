<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Jon Dickinson AKA Pandemikk					  # ||
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
class Registration_DataManager_Field extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'fieldid' 		=> array(TYPE_UINT, 		REQ_INCR),
		'title' 		=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'type' 			=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'active' 		=> array(TYPE_BOOL,			REQ_YES,	VF_METHOD),
		'instances' 	=> array(TYPE_ARRAY_UINT,	REQ_NO,		VF_METHOD)
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_field';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the field names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('fieldid = \'%1$s\'', 'fieldid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_fielddata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the title is valid
	*
	* @param	string	title of the field
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
	* Verifies that the type is valid
	*
	* @param	string	title of the field
	*
	* @return	boolean
	*/
	function verify_type(&$type)
	{
		$type = strval($type);
		if ($type === '')
		{
			// Invalid
			return false;
		}

		return true;
	}

	/**
	* Verifies that the active flag is valid
	*
	* @param	bool	active or not
	*
	* @return	boolean
	*/
	function verify_active(&$active)
	{
		// Enums must be strings
		$active = (string)$active;

		return true;
	}

	/**
	* Verifies that the instance is valid
	*
	* @param	array	instances the action is applied to
	*
	* @return	boolean
	*/
	function verify_instances(&$instances)
	{
		if (!empty($instances))
		{
			foreach ($instances AS $key => $instanceid)
			{
				if (empty(REGISTRATION::$cache['instance'][$instanceid]))
				{
					// Instance doesn't exist
					unset($instance[$key]);
				}
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
		// Insert type checks here when finished revamping manage fields
		($hook = vBulletinHook::fetch_hook('dbtech_registration_fielddata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		/*
		if ($this->registry->db->query_first_slave("
			SELECT fieldid FROM " . TABLE_PREFIX . "dbtech_registration_field
			WHERE type = '" . $this->registry->db->escape_string($this->fetch_field('type')) . "'
		"))
		{
			$this->error('dbtech_registration_x_already_exists_y', $vbphrase['dbtech_registration_field'], $this->fetch_field('type'));
		}
		*/

		($hook = vBulletinHook::fetch_hook('dbtech_registration_fielddata_predelete')) ? eval($hook) : false;

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
		if (isset($this->info['instances']))
		{
			// We only want to change instances if the array has been passed

			if (!empty($this->info['instances']))
			{
				// Remove old instances - don't delete all as to preserve other columns
				$this->registry->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_field
					WHERE fieldid = " . $this->fetch_field('fieldid') . "
						AND instanceid NOT IN(" . implode(',', $this->info['instances']) . ")
				");

				$values = array();
				foreach ($this->info['instances'] AS $instanceid)
				{
					$values[] = '(' . $instanceid . ', ' . $this->fetch_field('fieldid') . ')';
				}

				// Insert new instances - don't replace as to preserve other columns
				$this->registry->db->query_write("
					INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_registration_instance_field
						(instanceid, fieldid)
					VALUES
						" . implode(',', $values)
				);

				unset($values);
			}
			else
			{
				// Remove all instances
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_field WHERE fieldid = " . $this->fetch_field('fieldid'));
			}
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_fielddata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		REGISTRATION_CACHE::build('field');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_fielddata_postdelete')) ? eval($hook) : false;

		// Remove all instances
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_field WHERE fieldid = " . $this->fetch_field('fieldid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('field');

		return true;
	}
}