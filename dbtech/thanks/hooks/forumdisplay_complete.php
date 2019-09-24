<?php
do
{
	if ($vbulletin->options['dbtech_thanks_count_above_forum'])
	{
		$show['approvepost'] = (can_moderate($foruminfo['forumid'], 'canmoderateposts')) ? true : false;

		$noticeforuminfo = $foruminfo;
		/*DBTECH_LITE_START
		$parentlist = explode(',', $noticeforuminfo['parentlist']);
		if ($parentlist[0] == -1)
		{
			// This forum
			$noticeforum = $noticeforuminfo['forumid'];
		}
		else
		{
			$key = (count($parentlist) - 2);
			$noticeforum = $parentlist[$key];
		}
		DBTECH_LITE_END*/
		/*DBTECH_PRO_START*/
		// This forum
		$noticeforum = $noticeforuminfo['forumid'];
		/*DBTECH_PRO_END*/

		$SQL = $buttonsByVarname = array();
		foreach (THANKS::$cache['button'] as $buttonid => $button)
		{
			if (!$button['active'])
			{
				// Inactive button
				continue;
			}

			/*DBTECH_PRO_START*/
			if ($button['disableclickcount'])
			{
				continue;
			}
			/*DBTECH_PRO_END*/

			if ((int)$button['disableintegration'] & 64)
			{
				// Button was disabled for this thread
				continue;
			}

			if ((int)$vbulletin->forumcache[$noticeforum]['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
			{
				// Button was disabled for this forum
				continue;
			}

			// Init this
			THANKS::$entrycache['topclicks']['buttons'][$buttonid] = array(
				'total' => 0,
				'contentids' => array(),
			);

			// And this
			$buttonsByVarname[$button['varname']] = $buttonid;

			// Store the SQL query
			$SQL[] = '
				SELECT COUNT(*) AS count, post.threadid, entry.varname, thread.title
				FROM $dbtech_thanks_entry AS entry
				LEFT JOIN $post AS post ON(post.postid = entry.contentid)
				LEFT JOIN $thread AS thread ON(thread.threadid = post.threadid)
				WHERE entry.contenttype = \'post\'
					AND entry.varname = ' . $db->sql_prepare($button['varname']) . '
					AND entry.contentid IN(
						SELECT postid
						FROM $post
						WHERE threadid IN(
							SELECT threadid
							FROM $thread
							WHERE forumid = ' . intval($foruminfo['forumid']) . '
						)
							AND visible IN (1' . (!empty($deljoin) ? ",2" : "") . ($show['approvepost'] ? ",0" : "") . ')
					)
				GROUP BY threadid
				ORDER BY count DESC
				LIMIT 5
			';
		}

		if (count($SQL))
		{
			$topClicks = THANKS::$db->fetchAll('(' . implode(') UNION ALL (', $SQL) . ')');

			foreach ($topClicks as $click)
			{
				// Increment overall total count
				THANKS::$entrycache['topclicks']['total'] += $click['count'];

				// Increment per-button total count
				THANKS::$entrycache['topclicks']['buttons'][$buttonsByVarname[$click['varname']]]['total'] += $click['count'];

				// Set button/content info
				THANKS::$entrycache['topclicks']['buttons'][$buttonsByVarname[$click['varname']]]['contentids'][] = array('contentid' => $click['threadid'], 'title' => $click['title'], 'count' => $click['count']);
			}
		}

		$buttonBits = '';
		foreach (THANKS::$entrycache['topclicks']['buttons'] as $buttonId => $buttonInfo)
		{
			// Merge the info array into the normal thanks info, makes things easier
			$button = array_merge(THANKS::$cache['button'][$buttonId], $buttonInfo);

			// Prettify number
			$button['total'] = vb_number_format($button['total']);

			// This needs to be set
			$button['buttonimage'] = $button['image'] ? $button['image'] : ($vbulletin->options['bburl'] . '/dbtech/thanks/images/' . $button['varname'] . '.png');

			/*DBTECH_PRO_START*/
			$popupBits = '';
			foreach ($button['contentids'] as $content)
			{
				// Link to post
				$content['link'] = 'showthread.php?' . $vbulletin->session->vars['sessionurl'] . 't=' . $content['contentid'];

				// Title
				$content['title'] = construct_phrase($vbphrase['dbtech_thanks_thread_x_clicks_y'], $content['title'], vb_number_format($content['count']));

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
			// Add the template to the hook
			$templater = vB_Template::create('dbtech_thanks_clicks_perbutton');
				$templater->register('total', THANKS::$entrycache['topclicks']['total']);
				$templater->register('buttons', $buttonBits);
			$navbar .= $templater->render();
		}
	}

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
}
while (false);
?>