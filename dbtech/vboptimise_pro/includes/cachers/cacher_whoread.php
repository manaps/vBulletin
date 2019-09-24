<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_dboverride.php');

$update = true;

if (($_whoreadcache = vb_optimise::$cache->get('whoread.' . $argument['vbo_threadid'])) !== false)
{
	if (is_array($_whoreadcache) && TIMENOW < $_whoreadcache['time'])
	{
		$update = false;
		vb_optimise::stat(1);
		vb_optimise::report('Fetched Who Has Read Thread from cache successfully.');
	}
}

if ($update)
{
	$_whoreadcache = array();
	$users = $argumentb->query_read_slave("
		SELECT user.userid, user.options, user.username, user.usergroupid,
		user.displaygroupid, whoread.dateline #, ipdata.ip AS ipaddress, ipdata.altip
		FROM " . TABLE_PREFIX . "contentread as whoread
		INNER JOIN " . TABLE_PREFIX . "user as user USING (userid)
#			LEFT JOIN " . TABLE_PREFIX . "ipdata as ipdata USING (ipid)
		WHERE whoread.readtype = 'view'
		AND whoread.contentid = $argument[vbo_threadid]
		AND whoread.contenttypeid = $argument[vbo_contenttypeid]
		AND whoread.dateline > $argument[vbo_cutoff]
	");
	while ($user = $argumentb->fetch_array($users))
	{
		$_whoreadcache[] = $user;
	}

	$_whoreadcache = array(
		'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_whoread'] * 3600),
		'cache'	=> $_whoreadcache,
	);

	vb_optimise::$cache->set('whoread.' . $argument['vbo_threadid'], $_whoreadcache);
	vb_optimise::report('Cached Who Has Read Thread query.');
}

$argumentb = new vb_optimise_db($_whoreadcache['cache']);