<?php
class Registration_New_post extends REGISTRATION_REGISTER
{
	protected static function can_exec($threadinfo)
	{
		if (!$threadinfo['threadid'])
		{
			// Thread doesn't exist
			return false;
		}

		return true;
	}

	public static function exec_action($userinfo, $action)
	{
		global $vbulletin, $vbphrase;#Hey

		// Unserialize this
		$action['value'] = unserialize($action['value']);

		// We need some more data
		$threadinfo = $vbulletin->db->query_first_slave("SELECT * FROM " . TABLE_PREFIX . "thread WHERE threadid = " . (int)$action['value']['thread']); # Yes it is thread not threadid

		if (!self::can_exec($threadinfo))
		{
			// Action can't be executed (should be handled in parent class - note for v3)
			return;
		}

		if (isset($action['value']['username']))
		{
			if (!$newpost = $vbulletin->db->query_first_slave("SELECT userid, username, ipaddress FROM " . TABLE_PREFIX . "user WHERE username = " .$vbulletin->db->sql_prepare($action['value']['username'])))
			{
				$newpost = $vbulletin->userinfo;
			}
		}

		// Set some stuff
		$newpost['title']			= construct_phrase((!empty($vbphrase['dbtech_registration_action_' . $actionid . '_title']) ? $vbphrase['dbtech_registration_action_' . $actionid . '_title'] : $vbphrase['dbtech_registration_action_default_title']),
			$userinfo['username']
		);
		$newpost['message']			= construct_phrase((!empty($vbphrase['dbtech_registration_action_' . $actionid . '_message']) ? $vbphrase['dbtech_registration_action_' . $actionid . '_message'] : $vbphrase['dbtech_registration_action_default_message']),
			$userinfo['username'],
			fetch_seo_url('member|bburl|nosession', $userinfo)
		);
		$newpost['signature']		= ($action['options'] & 1);
		$newpost['disablesmilies'] 	= ($action['options'] & 2);
		$newpost['open'] 			= ($action['options'] & 4);
		$newpost['sticky']			= ($action['options'] & 8);

		// Create post
		$dm =& datamanager_init('Post', $vbulletin, ERRTYPE_SILENT, 'threadpost');
			$dm->set_info('forum', $vbulletin->forumcache[$threadinfo['forumid']]);
			$dm->set_info('thread', $threadinfo);
			$dm->set_info('is_automated', true);
			$dm->set('pagetext', $newpost['message']);
			$dm->set('threadid', $threadinfo['threadid']);
			$dm->set('userid', $newpost['userid'], true, false);
			$dm->set('username', $newpost['username'], true, false);
			$dm->set('visible', 1);
			$dm->set('allowsmilie', $newpost['allowsmilie']);
			$dm->set('showsignature', $newpost['signature']);
			$dm->set('ipaddress', $newpost['ipaddress']);
		$dm->save();
		unset($dm);

		// Shouldn't this be done in the dm?
		require_once(DIR . '/includes/functions_databuild.php');
		build_forum_counters($threadinfo['forumid']);
	}
}
?>