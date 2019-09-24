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

	// Shorthand
	$message = $this->fetch_field('pagetext');

	if (!$message)
	{
		// Report stuff
		break;
	}

	if (strpos($message, '[post]') !== false)
	{
		// Replace post BBCode with full URL
		$message = preg_replace(
			'#\[post\](\d+)\[\/post\]#',
			'[url]' . DBSEO::$config['_bburl'] . '/showthread.php?p=$1#post$1[/url]',
			$message
		);
	}

	// Store some caches
	DBSEO::$cache['thread'][$this->info['thread']['threadid']] = $this->info['thread'];

	// Prepare replacements
	$message = DBSEO::replaceIds($message);

	// Force text URL rewrite
	DBSEO::$config['dbtech_dbseo_rewrite_texturls'] = true;

	// Process the content
	$message = DBSEO::processContent($message);

	if (!$message)
	{
		// Report stuff
		break;
	}

	// Link external titles
	$message = DBSEO::linkExternalTitles($message, false);

	if (!$message)
	{
		// Report stuff
		break;
	}

	// Revert this
	DBSEO::$config['dbtech_dbseo_rewrite_texturls'] = false;

	// And finally set the message back
	$this->do_set('pagetext', $message);
}
while (false);
?>