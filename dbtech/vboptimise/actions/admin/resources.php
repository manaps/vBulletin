<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

@set_time_limit(0);
ignore_user_abort(1);

// Make sure we have this
require_once(DIR . '/dbtech/vboptimise/includes/functions_chart.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

print_cp_header('vB Optimise: Resource Saving Statistics');

// #############################################################################
if ($_REQUEST['action'] == 'resources' OR empty($_REQUEST['action']))
{
	// Make sure we have this
	require_once(DIR . '/dbtech/vboptimise/includes/functions_chart.php');

	// #######################################################################
	vb_optimise::updatestats();

	$data = $db->query_read_slave("SELECT dateline, queries FROM " . TABLE_PREFIX . "vboptimise ORDER BY statid DESC LIMIT 30");

	$labels = $datasets = array();
	while ($row = $db->fetch_array($data))
	{
		// Sort out the label
		$labels[] = $row['dateline'];

		if (!isset($datasets[0]))
		{
			// Store this
			$datasets[0] = array(
				'labels' => array(),
				'data' => array(),
			);
		}

		// Set the dataset var
		$datasets[0]['label'] = 'Queries Saved';
		$datasets[0]['data'][] = $row['queries'];
		$datasets[0]['labels'][] = $row['dateline'];
	}
	unset($row);
	$db->free_result($data);

	print_table_start();
	print_table_header('vB Optimise: Resource Saving Statistic');
	print_line_chart($labels, $datasets);
	print_table_footer();
}

print_cp_footer();
?>