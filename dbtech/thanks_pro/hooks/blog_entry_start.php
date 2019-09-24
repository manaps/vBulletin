<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 1)
	{
		// Disabled integration
		break;
	}

	if (!$ids = THANKS::$db->fetchAllSingleKeyed('
		SELECT blogtextid
		FROM $blog_text
		WHERE blogid = :blogId
	', 'blogtextid', 'blogtextid', array(
		':blogId' 	=> $bloginfo['blogid']
	)))
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($ids, 'blog');

	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);
?>