<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_dboverride.php');

$result = $argument->query_first_slave("
	SELECT COUNT(*) AS threads,
	(
		SELECT COUNT(*) AS newthread
		FROM " . TABLE_PREFIX . "thread AS thread
		WHERE forumid = $argumentb[forumid]
		AND lastpost > $argumentb[lastread]
		AND open <> 10
		AND sticky = 0
		$argumentb[prefix_filter]
		$argumentb[visiblethreads]
		$argumentb[globalignore]
		$argumentb[limitothers]
		$argumentb[datecut]
		$argumentb[hook_query_where]
	) AS newthread
	$argumentb[hook_query_fields]
	FROM " . TABLE_PREFIX . "thread AS thread
	$argumentb[tachyjoin]
	$argumentb[hook_query_joins]
	WHERE forumid = $argumentb[forumid]
	AND sticky = 0
	$argumentb[prefix_filter]
	$argumentb[visiblethreads]
	$argumentb[globalignore]
	$argumentb[limitothers]
	$argumentb[datecut]
	$argumentb[hook_query_where]
");

$argument = new vb_optimise_db($result);

vb_optimise::report('Optimised Forum Display Thread Count SQL');