<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

if (!is_object($vbulletin))
{
	die('Cannot access directly.');
}

if (intval($vbulletin->versionnumber) == 4 AND $vbulletin->options['vbo_online'] AND $vbulletin->options['vbo_vbcache'] AND defined('CMS_SCRIPT'))
{
	if (!defined('VB_ENTRY'))
	{
		define('VB_ENTRY', true);
	}
	
	require_once(DIR . '/vb/cache.php');
	require_once(DIR . '/vb/cache/observer.php');
	require_once(DIR . '/vb/cache/observer/vboptimise.php');
	require_once(DIR . '/vb/cache/vboptimise.php');
	
	vB_Cache_vBOptimise::overload();
}
?>