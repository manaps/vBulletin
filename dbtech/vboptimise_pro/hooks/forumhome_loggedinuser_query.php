<?php
if (class_exists('vb_optimise'))
{
	$vars = array(
		'datecut'			=> $datecut,
		'hook_query_fields'	=> $hook_query_fields,
		'hook_query_joins'	=> $hook_query_joins,
		'hook_query_where'	=> $hook_query_where,
	);

	if (!$db AND vB::$db)
	{
		// Override this
		$db =& vB::$db;
	}	

	vb_optimise::cache('forumhomewol', $db, $vars);
}
?>