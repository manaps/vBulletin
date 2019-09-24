<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

if (version_compare($vbulletin->versionnumber, '4.1.5', '<'))
{
	require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_dboverride.php');

	$update = true;

	if (($_groupcache = vb_optimise::$cache->get('showgroups')) !== false)
	{
		if (is_array($_groupcache) && TIMENOW < $_groupcache['time'])
		{
			$update = false;
			vb_optimise::stat(1);
			vb_optimise::report('Fetched Showgroups $users from cache successfully.');
		}
	}

	if ($update)
	{
		$_groupcache = array();
		$users = $argumentb->query_read_slave("
			SELECT user.*,
				usergroup.usergroupid, usergroup.title,
				user.options, usertextfield.buddylist,
				" . ($argument['locationfield'] ? 'userfield.field2,' : '') . "
				IF(user.displaygroupid = 0, user.usergroupid, user.displaygroupid) AS displaygroupid
				" . ($vbulletin->options['avatarenabled'] ? ",avatar.avatarpath, NOT ISNULL(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,customavatar.width AS avwidth,customavatar.height AS avheight, customavatar.width_thumb AS avwidth_thumb, customavatar.height_thumb AS avheight_thumb, filedata_thumb, NOT ISNULL(customavatar.userid) AS hascustom" : "") . "
			FROM " . TABLE_PREFIX . "user AS user
			LEFT JOIN " . TABLE_PREFIX . "usergroup AS usergroup ON(usergroup.usergroupid = user.usergroupid OR FIND_IN_SET(usergroup.usergroupid, user.membergroupids))
			LEFT JOIN " . TABLE_PREFIX . "userfield AS userfield ON(userfield.userid = user.userid)
			LEFT JOIN " . TABLE_PREFIX . "usertextfield AS usertextfield ON(usertextfield.userid=user.userid)
			" . ($vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)" : "") . "
			WHERE (usergroup.genericoptions & " . $vbulletin->bf_ugp_genericoptions['showgroup'] . ")
		");
		while ($user = $argumentb->fetch_array($users))
		{
			$_groupcache[] = $user;
		}

		$_groupcache = array(
			'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_showgroups'] * 3600),
			'cache'	=> $_groupcache,
		);

		vb_optimise::$cache->set('showgroups', $_groupcache);
		vb_optimise::report('Cached Showgroups $users query.');
	}

	$argumentb = new vb_optimise_db($_groupcache['cache']);
}