<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}


$update = true;

if (($_thanksstatscache = vb_optimise::$cache->get('thanksstats')) !== false)
{
	if (is_array($_thanksstatscache) && TIMENOW < $_thanksstatscache['time'])
	{
		$update = false;
		vb_optimise::stat(1);
		vb_optimise::report('Fetched Thanks Stats from cache successfully.');

		$argumentb = $_thanksstatscache['cache'];
	}
}

if ($update)
{
	foreach ($argument as $type)
	{
		// Fetch entries
		$argumentb[$type] = THANKS::$db->fetchAllKeyed('
			SELECT 
				:type AS value,
				user.userid,
				user.username,
				user.usergroupid,
				user.membergroupids,
				user.infractiongroupid,
				user.displaygroupid
				:vBShop
			FROM $dbtech_thanks_statistics AS entry
			LEFT JOIN $user AS user USING(userid)
			LEFT JOIN $usergroup AS usergroup ON (usergroup.usergroupid = user.usergroupid)
			ORDER BY value DESC
			LIMIT :limit
		', 'userid', array(
			':type' 	=> $type,
			':limit' 	=> $argumentc,
			':vBShop' 	=> ($vbulletin->products['dbtech_vbshop'] ? ", dbtech_vbshop_purchase" : ''),
		));
	}

	$_thanksstatscache = array(
		'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_thanksstats'] * 3600),
		'cache'	=> $argumentb,
	);

	vb_optimise::$cache->set('thanksstats', $_thanksstatscache);
	vb_optimise::report('Cached Thanks Stats successfully.');	
}