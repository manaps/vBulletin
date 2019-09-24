<?php
class vBActivity_Type_invite_registered extends vBActivity_Type_Core
{
	/**
	* Function to call before every action
	*/	
	public function action()
	{
		if (!parent::action())
		{
			// This type is inactive
			return false;
		}
		
		// We made it!
		return ($this->registry->products['dbtech_registration']);
	}
	
	/**
	* What happens on recalculate points
	*
	* @param	array	The user info
	*/	
	public function recalculate_points($user)
	{
		if (!$this->action())
		{
			// Disabled
			return false;
		}
		
		// Reset the points
		parent::reset_points($user);
		
		$tbl 				= 'dbtech_registration_invite AS invite';
		$idfield 			= 'inviteid';
		$datefield 			= 'dateline';
		#$hook_query_join	= 'INNER JOIN ' . TABLE_PREFIX . 'dbtech_registration_email AS email ON(invite.email = email.email) AND email.verified = \'1\'';
		$hook_query_where 	= 'invite.userid = ' . (int)$user['userid'];
		$multiplier 		= 1;
			
		if (VBACTIVITY::$isPro)
		{
			if ($this->registry->GPC['startdate'])
			{
				// Set dateline
				$hook_query_where .= " AND " . ($datefield ? $datefield : 'dateline') . " >= " . $this->registry->GPC['startdate'];
			}
		}

		$results = $this->registry->db->query_read_slave("
			SELECT *
			FROM " . TABLE_PREFIX . "$tbl
			#$hook_query_join
			WHERE $hook_query_where
		");

		while ($result = $this->registry->db->fetch_array($results))
		{
			// Insert points log
			VBACTIVITY::insert_points($this->config['typename'], $result[$idfield], $user['userid'], $multiplier, $result['dateline']);
		}
		$this->registry->db->free_result($results);
		unset($result);
	}
	
	/**
	* Checks whether we meet a certain criteria
	*
	* @param	integer	The criteria ID we are checking
	* @param	array	Information regarding the user we're checking
	* 
	* @return	boolean	Whether this criteria has been met
	*/	
	public function check_criteria($conditionid, &$userinfo)
	{
		if (!$this->action())
		{
			// Disabled
			return false;
		}

		// Ensure we've got points cached
		parent::check_criteria($conditionid, $userinfo);
		
		if (!$condition = VBACTIVITY::$cache['condition'][$conditionid])
		{
			// condition doesn't even exist
			return false;
		}
		
		// grab us the type name
		$typename = VBACTIVITY::$cache['type'][$condition['typeid']]['typename'];
		
		if ($condition['type'] == 'points' OR $userinfo[$typename])
		{
			// This has been cached
			return VBACTIVITY::check_criteria($conditionid, $userinfo);
		}
		
		// Copypaste shorthand
		$userid = $userinfo['userid'];		
		
		if (!$userinfo[$typename])
		{
			// We need more info
			$additionalinfo = $this->registry->db->query_first_slave("
				SELECT COUNT(inviteid) AS $typename
				FROM " . TABLE_PREFIX . "dbtech_registration_invite AS invite
				#INNER JOIN " . TABLE_PREFIX . "dbtech_registration_email AS email ON(invite.email = email.email) AND email.verified = '1'
				WHERE invite.userid = " . $userid
			);
			
			// We had this info
			$userinfo[$typename] = intval($additionalinfo[$typename]);
		}	
		
		// Pass criteria checking onwards
		return VBACTIVITY::check_criteria($conditionid, $userinfo);
	}	
}
?>