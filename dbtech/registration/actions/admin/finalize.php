<?php
// Fetch filters
$filters_q = $db->query_read_slave("SELECT DISTINCT(reason) AS reason FROM " . TABLE_PREFIX . "dbtech_registration_tracking");

$filters = array();
if ($db->num_rows($filters_q))
{
	while ($filter = $db->fetch_array($filters_q))
	{
		if ($filter['reason'] == 'emailtaken')
		{
			// Special case
			$vbphrase[$filter['reason'] . '_title'] = $vbphrase['dbtech_registration_' . $filter['reason'] . '_title'];
		}

		$filters[$filter['reason']] = $vbphrase[$filter['reason'] . '_title'];
	}
}

// Finally update the datastore with the new value
build_datastore('dbtech_registration_filters', serialize($filters), 1);