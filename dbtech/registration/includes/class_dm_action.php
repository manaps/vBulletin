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
class Registration_DataManager_Action extends vB_DataManager
{
	/**
	* Array of recognised and required fields for contenttypes, and their contenttypes
	*
	* @var	array
	*/
	var $validfields = array(
		'actionid' 		=> array(TYPE_UINT, 		REQ_INCR),
		'title' 		=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'type' 			=> array(TYPE_NOHTML,		REQ_YES,	VF_METHOD),
		'value' 		=> array(TYPE_NOCLEAN,		REQ_YES,	VF_METHOD),
		'options' 		=> array(TYPE_NOCLEAN,		REQ_YES,	VF_METHOD),
		'active' 		=> array(TYPE_BOOL,			REQ_YES,	VF_METHOD),
		'instances' 	=> array(TYPE_ARRAY_UINT,	REQ_NO,		VF_METHOD),
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'dbtech_registration_action';

	/**
	* Condition for update query
	* This is for use with sprintf(). First key is the where clause, further keys are the action names of the data to be used.
	*
	* @var	array
	*/
	var $condition_construct = array('actionid = \'%1$s\'', 'actionid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function __construct(&$registry, $errlocation = ERRTYPE_STANDARD)
	{
		parent::__construct($registry, $errlocation);

		($hook = vBulletinHook::fetch_hook('dbtech_registration_actiondata_start')) ? eval($hook) : false;
	}

	/**
	* Verifies that the title is valid
	*
	* @param	string	title of the action
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
	* @param	string	type of the action
	*
	* @return	boolean
	*/
	function verify_type(&$type)
	{
		if (!file_exists(DIR . '/dbtech/registration_pro/includes/actions/' . $type . '.php'))
		{
			if (!file_exists(DIR . '/dbtech/registration/includes/actions/' . $type . '.php'))
			{
				// Action file doesn't exists
				return false;
			}
			else
			{
				require_once(DIR . '/dbtech/registration/includes/actions/' . $type . '.php');
			}
		}
		else
		{
			require_once(DIR . '/dbtech/registration_pro/includes/actions/' . $type . '.php');
		}

		if (!class_exists('Registration_' . $type))
		{
			// Action class doesn't exist
			return false;
		}

		return true;
	}

	/**
	* Verifies that the value is valid
	*
	* @param	string	value of the action
	*
	* @return	boolean
	*/
	function verify_value(&$value)
	{
		if (is_array($value))
		{
			// Consistency!
			ksort($value);
		}

		return true;
	}

	/**
	* Verifies that the options bitfield is valid
	*
	* @param	array	options array
	*
	* @return	boolean
	*/
	function verify_options(&$options)
	{
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
		// boolean value for a pseudo-boolean field
		$active = (bool)$active;

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
		// Why oh why isn't this in scope?
		global $vbphrase;

		if ($this->registry->db->query_first_slave("
			SELECT actionid FROM " . TABLE_PREFIX . "dbtech_registration_action
			WHERE	type	= " . $this->registry->db->sql_prepare($this->fetch_field('type')) . "
				AND value	= " . $this->registry->db->sql_prepare(is_array($this->fetch_field('value')) ? serialize($this->fetch_field('value')) : $this->fetch_field('value')) . "
			" . (!empty($this->existing) ? ' AND actionid != ' . $this->existing['actionid'] : '')
		))
		{
			// Action with this type and value already exists
			$this->error('dbtech_registration_x_already_exists_y', $vbphrase['dbtech_registration_action'],
				$vbphrase['dbtech_registration_type'] . ': ' . (isset($vbphrase[$this->fetch_field('type')]) ? $vbphrase[$this->fetch_field('type')] : $vbphrase['dbtech_registration_' . $this->fetch_field('type')])  . ' &amp; ' .
				$vbphrase['dbtech_registration_value'] . ': ' . (is_array($this->fetch_field('value')) ? serialize($this->fetch_field('value')) : $this->fetch_field('value'))
			);

			return false;
		}

		switch ($this->fetch_field('type'))
		{
			case 'usergroup':
			case 'displaygroup':
				if (empty($this->registry->usergroupcache[$this->fetch_field('value')]))
				{
					$this->error('invalid_usergroup_specified');
					return false;
				}

				// No options for these guys
				$this->set('options', 0);

				break;
			case 'new_thread':
			case 'new_post':
				if (!is_array($this->fetch_field('value')))
				{
					return false;
				}

				// Lame.
				$value = $this->fetch_field('value');

				if ($this->fetch_field('type') == 'new_thread')
				{
					if (empty($value['forumid']))
					{
						$this->error('invalid_forum_specified');
						return false;
					}

					foreach ($value['forumid'] AS $forumid)
					{
						if (empty($this->registry->forumcache[$forumid]))
						{
							$this->error('invalid_forum_specified');
							return false;
						}
					}

					$type = 'forum';
				}
				else
				{
					if (empty($value['threadid']) OR !$this->registry->db->query_first_slave("SELECT threadid FROM " . TABLE_PREFIX . "thread WHERE threadid = " . $value['threadid']))
					{
						$this->error('dbtech_registration_invalid_x', $vbphrase['thread'], $value['threadid']);
						return false;
					}

					$type = 'thread';
				}

				// Set serialized values array
				$this->set('value', serialize(array($type => $value[$type . 'id'], 'username' => $value['username'])));

				// Bitfields
				$bitfields = array('signature' => 1, 'disablesmilies' => 2, 'stickunstick' => 4, 'openclose' => 8);

				$bitfield = 0;
				$options = $this->fetch_field('options'); $options = (array)$options;

				foreach ($options AS $option => $value)
				{
					if (isset($bitfields[$option]) AND $value)
					{
						$bitfield += $bitfields[$option];
					}
				}

				// Set options bitfield
				$this->do_set('options', $bitfield);

				break;
			default:
				// Should never happen
				return false;
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_actiondata_presave')) ? eval($hook) : false;

		return true;
	}

	/**
	* Additional data to update before a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function pre_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_actiondata_predelete')) ? eval($hook) : false;

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
		// Insert phrases
		$this->registry->db->query_write("
			INSERT IGNORE INTO " . TABLE_PREFIX . "phrase
				(languageid, varname, text, fieldname, product, username, dateline, version)
			VALUES
				(
					-1,
					'dbtech_registration_action_" . $this->fetch_field('actionid') . "_title',
					DEFAULT,
					'register',
					'dbtech_registration',
					'" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "',
					" . TIMENOW . ",
					'" . $this->registry->db->escape_string('2.0.7 Patch Level 2') . "'
				),
				(
					-1,
					'dbtech_registration_action_" . $this->fetch_field('actionid') . "_message',
					DEFAULT,
					'register',
					'dbtech_registration',
					'" . $this->registry->db->escape_string($this->registry->userinfo['username']) . "',
					" . TIMENOW . ",
					'" . $this->registry->db->escape_string('2.0.7 Patch Level 2') . "'
				)
		");

		if (isset($this->info['instances']))
		{
			// We only want to change instances if the array has been passed

			if (!empty($this->info['instances']))
			{
				// Remove old instances - don't delete all as to preserve other columns
				$this->registry->db->query_write("
					DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_action
					WHERE actionid = " . $this->fetch_field('actionid') . "
						AND instanceid NOT IN(" . implode(',', $this->info['instances']) . ")
				");

				$values = array();
				foreach ($this->info['instances'] AS $instanceid)
				{
					$values[] = '(' . $instanceid . ', ' . $this->fetch_field('actionid') . ')';
				}

				// Insert new instances - don't replace as to preserve other columns
				$this->registry->db->query_write("
					INSERT IGNORE INTO " . TABLE_PREFIX . "dbtech_registration_instance_action
						(instanceid, actionid)
					VALUES
						" . implode(',', $values)
				);

				unset($values);
			}
			else
			{
				// Remove all instances
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_action WHERE actionid = " . $this->fetch_field('actionid'));
			}
		}

		($hook = vBulletinHook::fetch_hook('dbtech_registration_actiondata_postsave')) ? eval($hook) : false;

		// Rebuild the cache
		REGISTRATION_CACHE::build('action');

		return true;
	}

	/**
	* Additional data to update after a delete call (such as denormalized values in other tables).
	*
	* @param	boolean	Do the query?
	*/
	function post_delete($doquery = true)
	{
		($hook = vBulletinHook::fetch_hook('dbtech_registration_actiondata_postdelete')) ? eval($hook) : false;

		// Remove old phrases
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "phrase WHERE varname IN('dbtech_registration_action_" . $this->fetch_field('actionid') . "_title', 'dbtech_registration_action_" . $this->fetch_field('actionid') . "_message') ");

		// Remove all instances
		$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "dbtech_registration_instance_action WHERE actionid = " . $this->fetch_field('actionid'));

		// Rebuild the cache
		REGISTRATION_CACHE::build('action');

		return true;
	}
}