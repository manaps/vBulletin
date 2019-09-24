<?php
class Registration_New_thread extends REGISTRATION_REGISTER
{
	protected static function can_exec($forumid)
	{
		global $vbulletin;
		
		if (empty($vbulletin->forumcache[$forumid]))
		{
			// Forum doesn't exist
			return false;
		}

		return true;
	}
	
	public static function exec_action($userinfo, $action)
	{
		global $vbulletin, $vbphrase;

		// Unserialize this
		$action['value'] = unserialize($action['value']);

		if (isset($action['value']['username']))
		{
			if (!$newpost = $vbulletin->db->query_first_slave("SELECT userid, username, ipaddress FROM " . TABLE_PREFIX . "user WHERE username = " . $vbulletin->db->sql_prepare($action['value']['username'])))
			{
				$newpost = $vbulletin->userinfo;
			}
		}

		// Set some shit
		$newpost['title']			= construct_phrase((!empty($vbphrase['dbtech_registration_action_' . $actionid . '_title']) ? $vbphrase['dbtech_registration_action_' . $actionid . '_title'] : $vbphrase['dbtech_registration_action_default_title']),
				$userinfo['username']
		);
		$newpost['message']			= construct_phrase((!empty($vbphrase['dbtech_registration_action_' . $actionid . '_message']) ? $vbphrase['dbtech_registration_action_' . $actionid . '_message'] : $vbphrase['dbtech_registration_action_default_message']),
			$userinfo['username'], 
			fetch_seo_url('member|bburl|nosession', $userinfo)
		);
		$newpost['signature']		= ($action['options'] & 1);
		$newpost['disablesmilies'] 	= ($action['options'] & 2);
		$newpost['sticky'] 			= ($action['options'] & 4);
		$newpost['open']			= ($action['options'] & 8);
		
		foreach ($action['value']['forum'] AS $forumid)
		{
			if (!self::can_exec($forumid))
			{
				// Action can't be executed
				continue;
			}

			// Create thread
			$dm =& datamanager_init('Thread_FirstPost', $vbulletin, ERRTYPE_SILENT, 'threadpost');
				$dm->set_info('forum', $vbulletin->forumcache[$forumid]);
				$dm->set_info('is_automated', true);  
				$dm->set('forumid', $forumid);
				$dm->do_set('postuserid', $userids);
				$dm->set('userid', $newpost['userid']);
				$dm->set('title', $newpost['title']);
				$dm->set('pagetext', $newpost['message']);
				$dm->set('ipaddress', $newpost['ipaddress']);
				$dm->set('visible', 1);
				$dm->set('open', $newpost['open']);
				$dm->set('sticky', $newpost['sticky']);
				$dm->set('allowsmilie', $newpost['allowsmilie']);
				$dm->set('showsignature', $newpost['signature']);
			$dm->save();
			unset($dm);
							
			// Shouldn't this be done in the dm?
			require_once(DIR . '/includes/functions_databuild.php');
			build_forum_counters($forumid);
		}
	}
}
?>