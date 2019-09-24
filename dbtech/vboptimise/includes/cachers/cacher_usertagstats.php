<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}


$update = true;

if (($_usertagstatscache = vb_optimise::$cache->get('usertagstats')) !== false)
{
	if (is_array($_usertagstatscache) && TIMENOW < $_usertagstatscache['time'])
	{
		$update = false;
		vb_optimise::stat(1);
		vb_optimise::report('Fetched User Tagging Stats from cache successfully.');

		$argumentb = $_usertagstatscache['cache'];
	}
}

if ($update)
{
	// Fetch rewards
	$leaders_q = $vbulletin->db->query_read_slave(implode(' UNION ALL ', $argument));
			
	while ($leaders_r = $vbulletin->db->fetch_array($leaders_q))
	{
		// Grab the musername
		fetch_musername($leaders_r);
		
		// Store a cache of the leaders
		$argumentb[$leaders_r['typeid']][] = $leaders_r;
	}
	$vbulletin->db->free_result($leaders_q);
	unset($leaders_r);

	$_usertagstatscache = array(
		'time'	=> TIMENOW + ($vbulletin->options['vbo_cache_usertagstats'] * 3600),
		'cache'	=> $argumentb,
	);

	vb_optimise::$cache->set('usertagstats', $_usertagstatscache);
	vb_optimise::report('Cached User Tagging Stats successfully.');	
}