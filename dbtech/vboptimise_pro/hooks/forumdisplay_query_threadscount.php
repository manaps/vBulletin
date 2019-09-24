<?php
if (class_exists('vb_optimise'))
{
	$vars = array(
		'lastread'			=> $lastread,
		'hook_query_fields'	=> $hook_query_fields,
		'tachyjoin'			=> $tachyjoin,
		'hook_query_joins'	=> $hook_query_joins,
		'forumid'			=> $foruminfo['forumid'],
		'prefix_filter'		=> $prefix_filter,
		'visiblethreads'	=> $visiblethreads,
		'globalignore'		=> $globalignore,
		'limitothers'		=> $limitothers,
		'datecut'			=> $datecut,
		'hook_query_where'	=> $hook_query_where,
	);

	vb_optimise::cache('forumdisplaysub', $db, $vars);
}
?>