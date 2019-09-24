<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_dboverride.php');

$update = true;

if (($_wolcache = vb_optimise::$cache->get('forumhomewol')) !== false)
{
	if (is_array($_wolcache) && TIMENOW < $_wolcache['time'])
	{
		$update = false;
		vb_optimise::stat(1);
		vb_optimise::report('Fetched WOL from cache successfully.');
	}
}

if ($update)
{
	$_wolcache = array();

	$forumusers = $argument->query_read_slave("
		SELECT
			user.username, (user.options & " . $vbulletin->bf_misc_useroptions['invisible'] . ") AS invisible, user.usergroupid, user.lastvisit,
			session.userid, session.inforum, session.lastactivity, session.badlocation,
			IF(displaygroupid=0, user.usergroupid, displaygroupid) AS displaygroupid, infractiongroupid
			$argumentb[hook_query_fields]
		FROM " . TABLE_PREFIX . "session AS session
		LEFT JOIN " . TABLE_PREFIX . "user AS user ON(user.userid = session.userid)
		$argumentb[hook_query_joins]
		WHERE session.lastactivity > $argumentb[datecut]
			$argumentb[hook_query_where]
		" . iif($vbulletin->options['displayloggedin'] == 1 OR $vbulletin->options['displayloggedin'] == 3, "ORDER BY username ASC") . "
	");

	while ($user = $argument->fetch_array($forumusers))
	{
		$_wolcache[] = $user;
	}

	$_wolcache = array(
		'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_forumhomewol'] * 60),
		'cache'	=> $_wolcache,
	);

	vb_optimise::$cache->set('forumhomewol', $_wolcache);
	vb_optimise::report('Cached WOL query.');
}

$argument = new vb_optimise_db($_wolcache['cache']);
unset($_wolcache);