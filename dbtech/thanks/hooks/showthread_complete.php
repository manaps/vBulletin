<?php
do
{
	if ($vbulletin->options['dbtech_thanks_count_above_thread'] AND isset(THANKS::$entrycache['topclicks']['buttons']) AND is_array(THANKS::$entrycache['topclicks']['buttons']))
	{
		$buttonBits = '';
		$clicksByType = array('positive' => 0, 'negative' => 0);
		foreach (THANKS::$entrycache['topclicks']['buttons'] as $buttonId => $buttonInfo)
		{
			// Merge the info array into the normal thanks info, makes things easier
			$button = array_merge(THANKS::$cache['button'][$buttonId], $buttonInfo);

			// Add to this
			$clicksByType[($button['ispositive'] ? 'positive' : 'negative')] += $button['total'];

			// Prettify number
			$button['total'] = vb_number_format($button['total']);

			// This needs to be set
			$button['buttonimage'] = $button['image'] ? $button['image'] : ($vbulletin->options['bburl'] . '/dbtech/thanks/images/' . $button['varname'] . '.png');

			/*DBTECH_PRO_START*/
			$popupBits = '';
			foreach ($button['contentids'] as $content)
			{
				// Link to post
				$content['link'] = 'showpost.php?' . $vbulletin->session->vars['sessionurl'] . 'p=' . $content['contentid'];

				// Title
				$content['title'] = construct_phrase($vbphrase['dbtech_thanks_post_x_clicks_y'], $content['contentid'], vb_number_format($content['count']));

				// Add the template to the hook
				$templater = vB_Template::create('dbtech_thanks_clicks_perbutton_entrybit');
					$templater->register('content', $content);
				$popupBits .= $templater->render();
			}
			/*DBTECH_PRO_END*/

			// Add the template to the hook
			$templater = vB_Template::create('dbtech_thanks_clicks_perbutton_buttonbit');
				$templater->register('button', $button);
				$templater->register('entries', $entryBits);
				/*DBTECH_PRO_START*/
				$templater->register('popupbits', $popupBits);
				/*DBTECH_PRO_END*/
			$buttonBits .= $templater->render();
		}

		if ($buttonBits)
		{
			/*DBTECH_PRO_START*/
			if (THANKS::$entrycache['topclicks']['total'] AND $vbulletin->options['dbtech_thanks_threadweight_enable'])
			{
				$headinclude .= '<script type="application/ld+json">
					{
						"@context": "http://schema.org/",
						"@type": "DiscussionForumPosting",
						"name": "' . str_replace('"', '\"', $threadinfo['title']) . '",
						"headline": "' . str_replace('"', '\"', $threadinfo['title']) . '",
						"image": "' . $vbulletin->options['bburl'] . '/' . $vbulletin->options['cleargifurl'] . '",
						"datePublished": "' . vbdate('c', $threadinfo['dateline']) . '",
						"aggregateRating": {
							"@type": "AggregateRating",
							"ratingValue": "' . round((($clicksByType['positive'] * 5) + $clicksByType['negative']) / THANKS::$entrycache['topclicks']['total'], 1) . '",
							"bestRating": "5",
							"worstRating": "1",
							"ratingCount": "' . THANKS::$entrycache['topclicks']['total'] . '"
						}
					}
					</script>
				';
			}

			/*DBTECH_PRO_END*/
			// Add the template to the hook
			$templater = vB_Template::create('dbtech_thanks_clicks_perbutton');
				$templater->register('total', THANKS::$entrycache['topclicks']['total']);
				$templater->register('buttons', $buttonBits);
			if (intval($vbulletin->versionnumber) > 3)
			{
				$template_hook['showthread_above_posts'] .= $templater->render();
			}
			else
			{
				$poll .= $templater->render();
			}
		}
	}

	// Extract the variables from the entry processer
	list($colorOptions, $thanksEntries) = THANKS::processEntries();

	if (intval($vbulletin->versionnumber) == 3)
	{
		$headinclude .= '<style type="text/css">' . vB_Template::create('dbtech_thanks.css')->render() . '</style>';
	}
	else
	{
		// Sneak the CSS into the headinclude
		$templater = vB_Template::create('dbtech_thanks_css');
			$templater->register('versionnumber', '363');
		$headinclude .= $templater->render();
	}

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
			'threadId' 		=> $thread['threadid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'postCount' 	=> ($thread['replycount'] + 1),
			'thanksEntries' => $thanksEntries,
			'colorOptions' 	=> $colorOptions,
			'contenttype' 	=> 'post',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
			'noRefresh' 	=> (int)$vbulletin->options['dbtech_thanks_disable_refresh'],
		)) . ';
	', false, false);
	$footer .= THANKS::js('.version', true, false);
	$footer .= '<script type="text/javascript"> (window.jQuery && __versionCompare(window.jQuery.fn.jquery, "' . THANKS::$jQueryVersion . '", ">=")) || document.write(\'<script src="' . THANKS::jQueryPath() . '">\x3C/script>\'); </script>';
	$footer .= '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/thanks/clientscript/jquery.qtip.min.js"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);
?>