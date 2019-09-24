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
class Registration_DataManager_Criteria extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'criteriaid' 	=> array(TYPE_UINT, 		REQ_INCR),
		'title' 		=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'type' 			=> array(TYPE_NOHTML,		REQ_YES/*,	VF_METHOD*/),
		'operator' 		=> array(TYPE_STR,			REQ_YES,	VF_METHOD),
		'value' 		=> array(TYPE_STR,			REQ_YES,	VF_METHOD),
		'active' 		=> array(TYPE_BOOL,			REQ_YES,	VF_METHOD),
		'instances' 	=> array(TYPE_ARRAY_UINT,	REQ_NO,		VF_METHOD),
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_criteria';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the criteria names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('criteriaid = \'%1$s\'', 'criteriaid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_criteriadata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the title is valid
	*
	* @param	string	title of the criteria
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
	* @param	string	type of the criteria
	*
	* @return	boolean
	*/
	function verify_type(&$type)
	{
		if (!in_array($type, array('location', 'code', 'invited', 'verified')))
		{
			// Invalid
			return false;
		}

		return true;
	}

	/**
	* Verifies that the operator is valid
	*
	* @param	string	operator of the criteria
	*
	* @return	boolean
	*/
	function verify_operator(&$operator)
	{
		if (!in_array($operator, array('==', '!=', '>', '<', '>=', '<=')))
		{
			// Invalid
			return false;
		}

		return true;
	}

	/**
	* Verifies that the value is valid
	*
	* @param	string	value of the criteria
	*
	* @return	boolean
	*/
	function verify_value(&$value)
	{
		$value = strval($value);
		if ($value === '')
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
	* @param	array	instances the criteria is applied to
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
					unset($instances[$key]);
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
		if ($this->registry->db->query_first_slave("
			SELECT criteriaid FROM " . TABLE_PREFIX . "dbtech_registration_criteria
			WHERE	type		= " . $this->registry->db->sql_prepare($this->fetch_field('type')) . "
				AND operator	= " . $this->registry->db->sql_prepare($this->fetch_field('value')) . "
				AND value		= " . $this->registry->db->sql_prepare($this->fetch_field('operator')) . "
			" . (!empty($this->existing) ? ' AND criteriaid != ' . $this->existing['criteriaid'] : '')
		))
		{
			global $vbphrase;

			// Criteria with this type and value already exists
			$this->error('dbtech_registration_x_already_exists_y', $vbphrase['dbtech_registration_criteria'],
				$vbphrase['dbtech_registration_type'] . ': ' . $vbphrase[$this->fetch_field('type')] . ' &amp; ' .
				$vbphrase['dbtech_registration_operator'] . ': ' . $this->fetch_field('value') . ' &amp; ' .
				$vbphrase['dbtech_registration_value'] . ': ' . $this->fetch_field('value')
			);
			return false;
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_criteriadata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_criteriadata_predelete')) ? eval($hook) : false;

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
					DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_criteria
					WHERE criteriaid = " . $this->fetch_field('criteriaid') . "
						AND instanceid NOT IN(" . implode(',', $this->info['instances']) . ")
				");

				$values = array();
				foreach ($this->info['instances'] AS $instanceid)
				{
					$values[] = '(' . $instanceid . ', ' . $this->fetch_field('criteriaid') . ')';
				}

				// Insert new instances - don't replace as to preserve other columns
				$this->registry->db->query_write("
					INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_registration_instance_criteria
						(instanceid, criteriaid)
					VALUES
						" . implode(',', $values)
				);

				unset($values);
			}
			else
			{
				// Remove all instances
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_criteria WHERE criteriaid = " . $this->fetch_field('criteriaid'));
			}
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_criteriadata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		REGISTRATION_CACHE::build('criteria');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_criteriadata_postdelete')) ? eval($hook) : false;

		// Remove all instances
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_criteria WHERE criteriaid = " . $this->fetch_field('criteriaid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('criteria');

		return true;
	}
}