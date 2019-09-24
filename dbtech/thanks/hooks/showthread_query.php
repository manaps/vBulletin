<?php
do
{
	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 64)
	{
		// Disabled integration
		break;
	}

	if ($threadedmode != 0)
	{
		if (!is_array($cache_postids))
		{
			// $cache_postids
			$post_ids = preg_split('#\s*,\s*#si', $cache_postids, -1, PREG_SPLIT_NO_EMPTY);
		}
		else
		{
			// $cache_postids
			$post_ids = $cache_postids;
		}
	}
	else
	{
		if (!is_array($ids))
		{
			// $ids
			$post_ids = preg_split('#\s*,\s*#si', $ids, -1, PREG_SPLIT_NO_EMPTY);
		}
		else
		{
			// $ids
			$post_ids = $ids;
		}
	}

	if (!$post_ids)
	{
		// We're done here
		THANKS::$processed = true;
		break;
	}

	// Grab our entries from the cache
	THANKS::fetchEntriesByContent($post_ids, 'post');

	// Prepare entry cache
	THANKS::processEntryCache();

	if ($vbulletin->options['dbtech_thanks_count_above_thread'])
	{
		$noticeforuminfo = $vbulletin->forumcache[$thread['forumid']];
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

		$SQL = array(
			'total' => array(),
			'top' => array(),
		);
		$buttonsByVarname = array();
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

			if ((int)$thread['dbtech_thanks_disabledbuttons'] & (int)$button['bitfield'])
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
			$SQL['top'][] = '
				SELECT COUNT(*) AS count, contentid, varname
				FROM $dbtech_thanks_entry AS entry,
				   (
				   	SELECT postid
					FROM $post
					WHERE threadid = ' . intval($threadid) . '
						AND visible IN (1' . (!empty($deljoin) ? ",2" : "") . ($show['approvepost'] ? ",0" : "") . ')
				   ) AS tmp
				WHERE entry.contenttype = \'post\'
					AND entry.varname = ' . $db->sql_prepare($button['varname']) . '
					AND contentid = tmp.postid
				GROUP BY contentid
				ORDER BY count DESC
				LIMIT 5
			';

			// Set total count
			$SQL['total'][] = '
				SELECT COUNT(*) AS count, varname
				FROM $dbtech_thanks_entry AS entry,
				   (
				   	SELECT postid
					FROM $post
					WHERE threadid = ' . intval($threadid) . '
						AND visible IN (1' . (!empty($deljoin) ? ",2" : "") . ($show['approvepost'] ? ",0" : "") . ')
				   ) AS tmp
				WHERE entry.contenttype = \'post\'
					AND entry.varname = ' . $db->sql_prepare($button['varname']) . '
					AND contentid = tmp.postid
			';
		}

		if (count($SQL['top']))
		{
			$topClicks = THANKS::$db->fetchAll('(' . implode(') UNION ALL (', $SQL['top']) . ')');
			foreach ($topClicks as $click)
			{
				if (!$click['varname'])
				{
					// For some reason this new performing query includes NULL
					continue;
				}

				// Set button/content info
				THANKS::$entrycache['topclicks']['buttons'][$buttonsByVarname[$click['varname']]]['contentids'][] = array('contentid' => $click['contentid'], 'count' => $click['count']);
			}

			$totalCount = THANKS::$db->fetchAll('(' . implode(') UNION ALL (', $SQL['total']) . ')');
			foreach ($totalCount as $click)
			{
				if (!$click['varname'])
				{
					// For some reason this new performing query includes NULL
					continue;
				}

				// Increment per-button total count
				THANKS::$entrycache['topclicks']['buttons'][$buttonsByVarname[$click['varname']]]['total'] += $click['count'];

				// Increment total count
				THANKS::$entrycache['topclicks']['total'] += $click['count'];
			}

		}

		unset($SQL, $topClicks, $totalCount);
	}
}
while (false);


/*
$cacheResult = THANKS_CACHE::read('showthread', 'thread.' . $thread['threadid'] . '.' . hash('crc32b', $post_ids));

if (!is_array($cacheResult))
{
}
else
{
	// Set the entry cache
	THANKS::$entrycache = $cacheResult;

	// Set processed
	THANKS::$processed = true;
}
*/

// Grab the statistics stuff
require(DIR . '/dbtech/thanks/hooks/statistics.php');
?>