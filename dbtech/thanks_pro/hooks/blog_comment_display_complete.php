<?php
do
{
	if ($this->registry->options['dbtech_thanks_disabledintegration'] & 1)
	{
		// Disabled integration
		break;
	}

	if (!$this->registry->userinfo['userid'] AND $this->registry->options['dbtech_thanks_hideguests'])
	{
		// Guests can't see this
		break;
	}

	if ($this->registry->userinfo['dbtech_thanks_excluded'])
	{
		// We're excluded
		break;
	}

	if ($response['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}

	if (!THANKS::$processed OR empty(THANKS::$entrycache['data']))
	{
		// This probably means we're on the "blog entries by user" page
		if (!$ids = THANKS::$db->fetchAllSingleKeyed('
			SELECT blogtextid
			FROM $blog_text
			WHERE blogid = :blogId
		', 'blogtextid', 'blogtextid', array(
			':blogId' 	=> $response['blogid']
		)))
		{
			// We're done here
			break;
		}

		// Grab our entries from the cache
		THANKS::fetchEntriesByContent($ids, 'blog');

		// Prepare entry cache
		THANKS::processEntryCache();
	}

	if ($response['userid'] == $this->registry->userinfo['userid'] AND empty(THANKS::$entrycache['data'][$response['blogtextid']]))
	{
		// Can't click own posts
		break;
	}

	$post = array_merge($response, array('postid' => $response['blogtextid'], 'dbtech_thanks_disabledbuttons' => 0));
	$response['dbtech_thanks_disabledbuttons'] = 0;

	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);

	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, $response, 'blog');

	if ($actions)
	{
		$templater = vB_Template::create('dbtech_thanks_postbit_entries_actions');
			$templater->register('post', 	$post);
			$templater->register('actions', $actions);
		$actions = $templater->render();
	}

	// Whether we're showing these areas
	$show['dbtech_thanks_area'] = ($actions OR $entries);

	$extrainfo = array();
	if ($this->registry->options['dbtech_thanks_displayextrainfo'])
	{
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Skip this button
				continue;
			}

			// Store buttons by varname
			$extrainfo[] = intval(THANKS::$entrycache['count'][$response['blogtextid']][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}

	$templater = vB_Template::create('dbtech_thanks_postbit_entries_blog');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$response['message'] .= $templater->render();
}
while (false);
?>