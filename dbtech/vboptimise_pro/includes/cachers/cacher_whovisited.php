<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_dboverride.php');

$update = true;

if (($_whovisitedcache = vb_optimise::$cache->get('whovisited')) !== false)
{
	if (is_array($_whovisitedcache) AND TIMENOW < $_whovisitedcache['time'] AND $_whovisitedcache['param'] == $vbulletin->options['wgo_members_names'])
	{
		$update = false;
		vb_optimise::stat(1);
		vb_optimise::report('Fetched Who Has Visited from cache successfully.');
	}
}

if ($update)
{
	$_whovisitedcache = array();
	if ($vbulletin->options['wgo_members_names']) 
	{
		$users = $argumentb->query_read_slave("
			SELECT userid, options, usergroupid, 
			displaygroupid, lastactivity, username
			FROM " . TABLE_PREFIX . "user 
			WHERE lastactivity > $argument[vbo_cutoff]
			ORDER BY username
		");
		while ($user = $argumentb->fetch_array($users))
		{
			$_whovisitedcache[] = $user;
		}
	}
	else
	{
		$_whovisitedcache = $argumentb->query_first_slave("
			SELECT COUNT(userid) AS whotoday 
			FROM " . TABLE_PREFIX . "user
			WHERE lastactivity > $argument[vbo_cutoff]
		");
	}

	$_whovisitedcache = array(
		'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_whovisited'] * 3600),
		'cache'	=> $_whovisitedcache,
		'param' => $vbulletin->options['wgo_members_names']
	);

	vb_optimise::$cache->set('whovisited', $_whovisitedcache);
	vb_optimise::report('Cached Who Has Visited query.');
}

$argumentb = new vb_optimise_db($_whovisitedcache['cache']);