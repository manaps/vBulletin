<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 128)
	{
		// Disabled integration
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($download['downloadid'], 'vbdownloads_download');

	// Prepare entry cache
	THANKS::processEntryCache();
}
while (false);

do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 128)
	{
		// Disabled integration
		break;
	}

	if (!$vbulletin->userinfo['userid'] AND $vbulletin->options['dbtech_thanks_hideguests'])
	{
		// Guests can't see this
		break;
	}

	if ($vbulletin->userinfo['dbtech_thanks_excluded'])
	{
		// We're excluded
		break;
	}

	if ($download['dbtech_thanks_excluded'])
	{
		// User is excluded
		break;
	}

	if (!THANKS::$processed)
	{
		// Haven't processed anything
		break;
	}

	if ($download['userid'] == $vbulletin->userinfo['userid'] AND !THANKS::$entrycache['data'][$download['downloadid']])
	{
		// Can't click own posts
		break;
	}

	$post = array_merge($download, $download, array('postid' => $download['downloadid'], 'dbtech_thanks_disabledbuttons' => 0));
	$download['dbtech_thanks_disabledbuttons'] = 0;

	// Refresh AJAX post data
	$excluded = THANKS::doButtonExclusive($post);

	// Extract the variables from the display processer
	list($entries, $actions) = THANKS::processDisplay($noticeforum, $excluded, $post, $download, 'vbdownloads_download');

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
	if ($vbulletin->options['dbtech_thanks_displayextrainfo'])
	{
		foreach ((array)THANKS::$cache['button'] as $button)
		{
			if (!$button['active'])
			{
				// Skip this button
				continue;
			}

			// Store buttons by varname
			$extrainfo[] = intval(THANKS::$entrycache['count'][$download['downloadid']][$button['varname']]) . ' ' . $vbphrase['dbtech_thanks_button_' . $button['varname'] . '_title'];
		}
	}

	$templater = vB_Template::create('dbtech_thanks_postbit_entries_vbdownloads_download');
		$templater->register('post', 		$post);
		$templater->register('show', 		$show);
		$templater->register('entries', 	$entries);
		$templater->register('actions', 	$actions);
		$templater->register('extrainfo', 	implode(', ', $extrainfo));
	$template_hook['dbtech_downloads_belowlinks'] .= $templater->render();

	if ($download['userid'] == $vbulletin->userinfo['userid'])
	{
		// Can't click own posts
		break;
	}

	// Extract the variables from the entry processer
	list($colorOptions, $thanksEntries) = THANKS::processEntries();

	// Begin list of JS phrases
	$jsphrases = array(
		'dbtech_thanks_must_wait_x_seconds'	=> $vbphrase['dbtech_thanks_must_wait_x_seconds'],
		'dbtech_thanks_people_who_clicked'	=> $vbphrase['dbtech_thanks_people_who_clicked'],
		'dbtech_thanks_loading'				=> $vbphrase['dbtech_thanks_loading'],
		'dbtech_thanks_noone_clicked'		=> $vbphrase['dbtech_thanks_noone_clicked'],
	);

	// Escape them
	THANKS::jsEscapeString($jsphrases);

	$escapedJsPhrases = '';
	foreach ($jsphrases as $varname => $value)
	{
		// Replace phrases with safe values
		$escapedJsPhrases .= "vbphrase['$varname'] = \"$value\"\n\t\t\t\t\t";
	}

	$footer .= THANKS::js($escapedJsPhrases . '
		var thanksOptions = ' . THANKS::encodeJSON(array(
			'threadId' 		=> $download['downloadid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'thanksEntries' => $thanksEntries,
			'contenttype' 	=> 'vbdownloads_download',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
		)) . ';
	', false, false);
	$footer .= THANKS::js('.version', true, false);
	$footer .= '<script type="text/javascript"> (window.jQuery && __versionCompare(window.jQuery.fn.jquery, "' . THANKS::$jQueryVersion . '", ">=")) || document.write(\'<script src="' . THANKS::jQueryPath() . '">\x3C/script>\'); </script>';
	$footer .= '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/thanks/clientscript/jquery.qtip.min.js"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);
?>