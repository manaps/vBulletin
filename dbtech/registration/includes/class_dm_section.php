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
class Registration_DataManager_Section extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'sectionid' 	=> array(TYPE_UINT, 		REQ_INCR),
		'title' 		=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'instances' 	=> array(TYPE_ARRAY_UINT,	REQ_NO,		VF_METHOD),
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_section';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the field names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('sectionid = \'%1$s\'', 'sectionid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_sectiondata_start')) ? eval($hook) : false;
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
		($hook = vBulletinHook::fetch_hook('dbtech_registration_sectiondata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_sectiondata_predelete')) ? eval($hook) : false;

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
					DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_section
					WHERE sectionid = " . $this->fetch_field('sectionid') . "
						AND instanceid NOT IN(" . implode(',', $this->info['instances']) . ")
				");

				$values = array();
				foreach ($this->info['instances'] AS $instanceid)
				{
					$values[] = '(' . $instanceid . ', ' . $this->fetch_field('sectionid') . ')';
				}

				// Insert new instances - don't replace as to preserve other columns
				$this->registry->db->query_write("
					INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_registration_instance_section
						(instanceid, sectionid)
					VALUES
						" . implode(',', $values)
				);

				unset($values);
			}
			else
			{
				// Remove all instances
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_section WHERE sectionid = " . $this->fetch_field('sectionid'));
			}
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_sectiondata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		REGISTRATION_CACHE::build('section');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_sectiondata_postdelete')) ? eval($hook) : false;

		// Remove all instances
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_section WHERE sectionid = " . $this->fetch_field('sectionid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('section');
		REGISTRATION_CACHE::build('field');

		return true;
	}
}