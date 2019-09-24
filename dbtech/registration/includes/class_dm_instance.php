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
class Registration_DataManager_Instance extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'instanceid' 	=> array(TYPE_UINT, 		REQ_INCR),
		'title' 		=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'daily_max' 	=> array(TYPE_UINT,			REQ_YES),
		'priority' 		=> array(TYPE_UINT,			REQ_YES),
		'types' 		=> array(TYPE_ARRAY,		REQ_NO,		VF_METHOD)
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_instance';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the instance names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('instanceid = \'%1$s\'', 'instanceid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_instancedata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the title is valid
	*
	* @param	string	title of the instance
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
	* Verifies that the types' values are valid
	*
	* @param	array	values of the type we're applying this instance to
	*
	* @return	boolean
	*/
	function verify_types(&$types)
	{
		foreach ($types AS $type => &$typeids)
		{
			switch ($type)
			{
				case 'criteria':
					$valid = array(
						'active'		=> 'ENUM',
						'required'		=> 'ENUM'
					);
					break;
				case 'section':
					$valid = array(
						'displayorder'	=> 'UINT',
						'active'		=> 'ENUM'
					);
					break;
				case 'field':
					$valid = array(
						'sectionid'		=> 'section',
						'displayorder'	=> 'UINT',
						'active'		=> 'ENUM'
					);
					break;
				case 'action':
					$valid = array(
						'displayorder'	=> 'UINT',
						'active'		=> 'ENUM'
					);
					break;
				default:
					// Invalid type
					unset($types[$type]);

					continue 2;
			}

			foreach ($typeids AS $typeid => &$fields)
			{
				if (empty(REGISTRATION::$cache[$type][$typeid]))
				{
					// Doesn't exist
					unset($typeids[$typeid]);

					continue;
				}

				if (empty($fields['used']))
				{
					// We're not using this typeid
					unset($typeids[$typeid]);

					continue;
				}

				// We don't want this field
				unset($fields['used']);

				foreach ($fields AS $fieldname => &$value)
				{
					if (empty($valid[$fieldname]))
					{
						// Field doesn't exist
						unset($fields[$fieldname]);

						continue;
					}

					// Maybe not the best way to sanitize stuff?
					switch ($valid[$fieldname])
					{
						case 'UINT':
							$value = (int)$value;

							if ($value < 0)
							{
								$value = 0;
							}

							break;
						case 'ENUM':
							$value = $this->registry->db->sql_prepare(((bool)$value ? '1' : '0'));

							break;
						case 'STR':
							$value = $this->registry->db->sql_prepare((string)$value);

							break;
						case 'section':
							if (empty(REGISTRATION::$cache['section'][$value]))
							{
								// Section doesn't exist - just get rid of this entire field
								unset($typeids[$typeid]);
							}

							if (empty($types['section'][$value]))
							{
								// Section isn't added to this instance
								$value = 0;
							}

							break;
					}
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
		($hook = vBulletinHook::fetch_hook('dbtech_registration_instancedata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_instancedata_predelete')) ? eval($hook) : false;

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
		foreach ((array)$this->info['types'] AS $type => $results)
		{
			// Delete old shit
			$this->registry->db->query_write("
				DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_" . $type . "
				WHERE instanceid = " . $this->fetch_field('instanceid')
			);

			if (empty($results))
			{
				// No values of this type
				continue;
			}

			$values = array();
			foreach ($results AS $key => $result)
			{
				$values[] = '(' . $this->fetch_field('instanceid') . ', ' . $key . ', ' . implode(', ', $result) . ')';
			}

			$this->registry->db->query_write("
				REPLACE INTO " . TABLE_PREFIX . "dbtech_registration_instance_" . $type . "
					(instanceid, " . $type . "id, " . implode(', ', array_keys($result)) . ")
				VALUES
					" . implode(',', $values)
			);
		}

		// Remove this in case it's used in hook
		unset($values);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_instancedata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		REGISTRATION_CACHE::build('instance');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_instancedata_postdelete')) ? eval($hook) : false;

		// Delete this crap
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_criteria WHERE instanceid = " . $this->fetch_field('instanceid'));
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_section WHERE instanceid = " . $this->fetch_field('instanceid'));
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_field WHERE instanceid = " . $this->fetch_field('instanceid'));
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_action WHERE instanceid = " . $this->fetch_field('instanceid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('instance');

		return true;
	}
}