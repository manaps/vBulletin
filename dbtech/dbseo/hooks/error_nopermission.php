<?php
do
{
	if (!class_exists('DBSEO'))
	{
		// Set important constants
		define('DBSEO_CWD', 	DIR);
		define('DBSEO_TIMENOW', TIMENOW);
		define('IN_DBSEO', 		true);

		// Make sure we nab this class
		require_once(DBSEO_CWD . '/dbtech/dbseo/includes/class_core.php');

		// Init DBSEO
		DBSEO::init(true);
	}

	if (!DBSEO::$config['dbtech_dbseo_active'])
	{
		// Mod is disabled
		break;
	}

	// Set 403 header
	http_response_code(isset(DBSEO::$config['dbtech_dbseo_nopermission_http']) ? DBSEO::$config['dbtech_dbseo_nopermission_http'] : 403);
	DBSEO::sendResponseCode(isset(DBSEO::$config['dbtech_dbseo_nopermission_http']) ? DBSEO::$config['dbtech_dbseo_nopermission_http'] : 403);
}
while (false);
?>